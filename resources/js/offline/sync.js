import { db } from "./db";
import { apiPost } from "../services/api";

/*
|--------------------------------------------------------------------------
| Sincronizaciones actualmente en ejecución
|--------------------------------------------------------------------------
|
| Evita que el intervalo, la reconexión o el regreso a primer plano
| intenten subir los mismos registros simultáneamente.
|
*/

const syncRuns = new Map();

function nowIso() {
  return new Date().toISOString();
}

function getCurrentUserId() {
  try {
    const user = JSON.parse(
      localStorage.getItem("user") || "null"
    );

    return Number(user?.id || 0);
  } catch {
    return 0;
  }
}

function emitSyncEvent(name, detail = {}) {
  if (typeof window === "undefined") {
    return;
  }

  window.dispatchEvent(
    new CustomEvent(name, {
      detail,
    })
  );
}

/*
|--------------------------------------------------------------------------
| Agregar un registro a la cola offline
|--------------------------------------------------------------------------
*/

export async function enqueue(type, payload) {
  const uuid =
    payload?.uuid ||
    `local_${Date.now()}`;

  const now = nowIso();
  const userId = getCurrentUserId();

  if (!userId) {
    throw new Error(
      "No hay usuario para cola offline"
    );
  }

  await db.records.add({
    uuid,
    user_id: userId,
    type,
    ...payload,
    created_at: now,
    synced: false,
  });

  await db.outbox.add({
    uuid,
    user_id: userId,
    type,
    created_at: now,
    status: "pending",
    last_error: null,
    retry_count: 0,
    next_retry_at: null,
  });

  return uuid;
}

/*
|--------------------------------------------------------------------------
| Marcar una captura local como sincronizada
|--------------------------------------------------------------------------
*/

async function markLocalSubmissionAsSynced(
  userId,
  uuid,
  serverSubmission,
  rec
) {
  const localRow =
    await db.form_submissions
      .where("[user_id+local_uuid]")
      .equals([
        Number(userId),
        uuid,
      ])
      .first();

  if (!localRow?.id) {
    return;
  }

  await db.form_submissions.update(
    localRow.id,
    {
      remote_id:
        serverSubmission?.id ?? null,

      consecutive:
        serverSubmission?.consecutive ??
        localRow.consecutive ??
        null,

      created_at:
        serverSubmission?.created_at ||
        localRow.created_at ||
        nowIso(),

      submission: {
        id:
          serverSubmission?.id ??
          uuid,

        form_id:
          serverSubmission?.form_id ??
          rec.form_id,

        consecutive:
          serverSubmission?.consecutive ??
          localRow.consecutive ??
          "Pendiente",

        user_id:
          serverSubmission?.user_id ??
          Number(userId),

        user_name:
          serverSubmission?.user_name ??
          null,

        answers:
          serverSubmission?.answers ??
          rec.answers ??
          {},

        created_at:
          serverSubmission?.created_at ||
          localRow.created_at ||
          nowIso(),

        offline_pending: false,
        pending_sync: false,
        synced: true,
      },

      synced: true,
      pending_sync: false,
    }
  );
}

/*
|--------------------------------------------------------------------------
| Procesar un elemento de la cola
|--------------------------------------------------------------------------
*/

async function processOutboxItem(item) {
  const rec = await db.records
    .where("[user_id+uuid]")
    .equals([
      Number(item.user_id),
      item.uuid,
    ])
    .first();

  if (!rec) {
    await db.outbox.delete(item.id);

    return {
      ok: true,
      skipped: true,
    };
  }

  try {
    if (rec.type === "form_submission") {
      const response = await apiPost(
        `/forms/${rec.form_id}/submit`,
        {
          answers: rec.answers,
        }
      );

      const serverSubmission =
        response?.submission || null;

      await markLocalSubmissionAsSynced(
        item.user_id,
        item.uuid,
        serverSubmission,
        rec
      );
    }

    await db.records.update(
      rec.id,
      {
        synced: true,
      }
    );

    await db.outbox.delete(item.id);

    return {
      ok: true,
      uuid: item.uuid,
      type: rec.type,
    };
  } catch (error) {
    await db.outbox.update(
      item.id,
      {
        status: "error",

        last_error:
          error?.message ||
          "error",

        retry_count:
          (item.retry_count || 0) + 1,

        next_retry_at: nowIso(),
      }
    );

    return {
      ok: false,
      uuid: item.uuid,
      type: rec.type,

      error:
        error?.message ||
        "error",
    };
  }
}

/*
|--------------------------------------------------------------------------
| Ejecutar sincronización para un usuario
|--------------------------------------------------------------------------
*/

async function executeSync(userId) {
  const items = await db.outbox
    .where("[user_id+status]")
    .anyOf(
      [userId, "pending"],
      [userId, "error"],
      [userId, "syncing"]
    )
    .toArray();

  /*
   * Si no hay registros pendientes, no emitimos el evento.
   * Esto evita refrescar las vistas cada 15 segundos sin necesidad.
   */
  if (items.length === 0) {
    return {
      ok: true,
      successCount: 0,
      errorCount: 0,
      skipped: true,
    };
  }

  let successCount = 0;
  let errorCount = 0;

  for (const item of items) {
    await db.outbox.update(
      item.id,
      {
        status: "syncing",
      }
    );

    const result =
      await processOutboxItem(item);

    if (result.ok) {
      successCount += 1;
    } else {
      errorCount += 1;
    }
  }

  emitSyncEvent(
    "offline-sync-complete",
    {
      userId,
      ok: errorCount === 0,
      successCount,
      errorCount,
    }
  );

  return {
    ok: errorCount === 0,
    successCount,
    errorCount,
  };
}

/*
|--------------------------------------------------------------------------
| Sincronización pública
|--------------------------------------------------------------------------
*/

export async function syncNow() {
  if (!navigator.onLine) {
    return {
      ok: false,
      reason: "offline",
    };
  }

  const userId = getCurrentUserId();

  if (!userId) {
    return {
      ok: false,
      reason: "no_user",
    };
  }

  /*
   * Si este usuario ya está sincronizando,
   * esperamos la misma ejecución.
   */
  const existingRun =
    syncRuns.get(userId);

  if (existingRun) {
    return existingRun;
  }

  const currentRun =
    executeSync(userId).finally(() => {
      if (
        syncRuns.get(userId) ===
        currentRun
      ) {
        syncRuns.delete(userId);
      }
    });

  syncRuns.set(
    userId,
    currentRun
  );

  return currentRun;
}

/*
|--------------------------------------------------------------------------
| Sincronización automática periódica
|--------------------------------------------------------------------------
|
| Esta función solamente procesa la cola local.
|
| Los eventos:
| - online
| - visibilitychange
|
| serán coordinados desde app.jsx para respetar el orden:
|
| 1. Subir capturas pendientes.
| 2. Consultar cambios remotos.
| 3. Descargar formularios, registros y PDFs.
|
*/

export function setupAutoSync({
  intervalMs = 15000,
  runOnStart = true,
} = {}) {
  const parsedInterval =
    Number(intervalMs);

  const safeInterval =
    Number.isFinite(parsedInterval) &&
    parsedInterval >= 5000
      ? parsedInterval
      : 15000;

  let stopped = false;

  const run = () => {
    if (stopped) {
      return;
    }

    if (!navigator.onLine) {
      return;
    }

    /*
     * Cuando la PWA está en segundo plano,
     * evitamos trabajar innecesariamente.
     */
    if (
      typeof document !== "undefined" &&
      document.visibilityState !== "visible"
    ) {
      return;
    }

    syncNow().catch(() => null);
  };

  if (runOnStart) {
    run();
  }

  const intervalId =
    window.setInterval(
      run,
      safeInterval
    );

  /*
   * Devuelve una función de limpieza.
   * La utilizaremos dentro del useEffect de app.jsx.
   */
  return () => {
    stopped = true;

    window.clearInterval(
      intervalId
    );
  };
}