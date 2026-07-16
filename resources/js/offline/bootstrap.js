import { apiGet } from "../services/api";
import {
  cacheFormsCatalog,
  cacheFormDetail,
  cacheFormSubmissions,
  cleanupOfflineScope,
} from "./forms-cache";
import { preloadVisibleSubmissionPdfs } from "./pdf-cache";

/*
|--------------------------------------------------------------------------
| Claves del almacenamiento local
|--------------------------------------------------------------------------
|
| El meta se guarda por usuario para evitar que la información de un
| usuario afecte la comprobación de otro usuario en el mismo dispositivo.
|
*/

const META_KEY_PREFIX = "offline_bootstrap_meta_v2";
const LEGACY_META_KEY = "offline_bootstrap_meta_v1";

/*
|--------------------------------------------------------------------------
| Bootstrap actualmente en ejecución
|--------------------------------------------------------------------------
|
| Evita que el intervalo, una navegación, el evento online o el regreso
| de segundo plano inicien varias descargas al mismo tiempo.
|
*/

const bootstrapRuns = new Map();

function resolveUserId(userId) {
  const directUserId = Number(userId);

  if (directUserId > 0) {
    return directUserId;
  }

  try {
    const storedUser = JSON.parse(
      localStorage.getItem("user") || "null"
    );

    return Number(storedUser?.id || 0);
  } catch {
    return 0;
  }
}

function getMetaKey(userId) {
  return `${META_KEY_PREFIX}:${Number(userId)}`;
}

function getStoredMeta(userId) {
  const resolvedUserId = resolveUserId(userId);

  if (!resolvedUserId) {
    return null;
  }

  try {
    return JSON.parse(
      localStorage.getItem(
        getMetaKey(resolvedUserId)
      ) || "null"
    );
  } catch {
    return null;
  }
}

function setStoredMeta(userId, meta) {
  const resolvedUserId = resolveUserId(userId);

  if (!resolvedUserId) {
    return;
  }

  localStorage.setItem(
    getMetaKey(resolvedUserId),
    JSON.stringify(meta)
  );

  /*
   * Eliminamos la clave anterior que no estaba separada por usuario.
   */
  localStorage.removeItem(LEGACY_META_KEY);
}

function normalizeMeta(meta) {
  return {
    forms_count: Number(
      meta?.forms_count || 0
    ),

    submissions_count: Number(
      meta?.submissions_count || 0
    ),

    pdfs_count: Number(
      meta?.pdfs_count || 0
    ),

    last_change_at: String(
      meta?.last_change_at || ""
    ),

    unit_scope_hash: String(
      meta?.unit_scope_hash || ""
    ),
  };
}

function metaChanged(previousMeta, nextMeta) {
  if (!previousMeta) {
    return true;
  }

  return (
    Number(previousMeta.forms_count || 0) !==
      Number(nextMeta.forms_count || 0) ||

    Number(previousMeta.submissions_count || 0) !==
      Number(nextMeta.submissions_count || 0) ||

    Number(previousMeta.pdfs_count || 0) !==
      Number(nextMeta.pdfs_count || 0) ||

    String(previousMeta.last_change_at || "") !==
      String(nextMeta.last_change_at || "") ||

    String(previousMeta.unit_scope_hash || "") !==
      String(nextMeta.unit_scope_hash || "")
  );
}

function getRowsForForm(
  submissionsByForm,
  formId
) {
  const rows =
    submissionsByForm?.[formId] ??
    submissionsByForm?.[String(formId)];

  return Array.isArray(rows) ? rows : [];
}

function dispatchBootstrapComplete({
  userId,
  reason,
  mode,
  meta,
  formsTotal,
  recordsTotal,
  pdfsTotal,
}) {
  if (typeof window === "undefined") {
    return;
  }

  window.dispatchEvent(
    new CustomEvent(
      "offline-bootstrap-complete",
      {
        detail: {
          userId,
          reason,
          mode,
          silent: mode === "silent",
          meta,
          formsTotal,
          recordsTotal,
          pdfsTotal,
        },
      }
    )
  );
}

/*
|--------------------------------------------------------------------------
| Comprobar si existen cambios
|--------------------------------------------------------------------------
|
| Esta función únicamente consulta bootstrap-meta.
| No descarga formularios, registros ni PDFs.
|
*/

export async function shouldRunOfflineBootstrap({
  userId,
} = {}) {
  const resolvedUserId =
    resolveUserId(userId);

  if (!resolvedUserId) {
    throw new Error(
      "No se pudo identificar al usuario para comprobar los datos offline."
    );
  }

  if (!navigator.onLine) {
    return {
      shouldRun: false,
      reason: "offline",
    };
  }

  const remoteMetaRaw = await apiGet(
    "/offline/bootstrap-meta"
  );

  const remoteMeta =
    normalizeMeta(remoteMetaRaw);

  const localMeta =
    getStoredMeta(resolvedUserId);

  return {
    shouldRun: metaChanged(
      localMeta,
      remoteMeta
    ),

    remoteMeta,
    localMeta,
  };
}

/*
|--------------------------------------------------------------------------
| Ejecutar la descarga offline
|--------------------------------------------------------------------------
*/

async function executeOfflineBootstrap({
  userId,
  token,
  onProgress,
  remoteMeta = null,
  reason = "manual",
  mode = "silent",
}) {
  const resolvedUserId =
    resolveUserId(userId);

  if (!resolvedUserId) {
    throw new Error(
      "No se pudo identificar al usuario."
    );
  }

  if (!navigator.onLine) {
    throw new Error(
      "No hay conexión para preparar datos offline."
    );
  }

  /*
   * Si shouldRunOfflineBootstrap ya consultó el meta,
   * lo reutilizamos para no volver a solicitarlo.
   */
  const metaRaw =
    remoteMeta ??
    (await apiGet(
      "/offline/bootstrap-meta"
    ));

  const meta = normalizeMeta(metaRaw);

  const boot = await apiGet(
    "/offline/bootstrap"
  );

  const forms = Array.isArray(boot?.forms)
    ? boot.forms
    : [];

  const submissionsByForm =
    boot?.submissions_by_form &&
    typeof boot.submissions_by_form ===
      "object"
      ? boot.submissions_by_form
      : {};

  let formsDone = 0;
  let recordsDone = 0;
  let pdfsDone = 0;

  /*
   * Los totales representan exactamente lo que contiene
   * la respuesta del bootstrap y lo que se descargará.
   */
  const formsTotal = forms.length;

  const recordsTotal = forms.reduce(
    (total, form) => {
      const rows = getRowsForForm(
        submissionsByForm,
        form.id
      );

      return total + rows.length;
    },
    0
  );

  const pdfsTotal = recordsTotal;

  const notify = () => {
    onProgress?.({
      stage: "syncing",

      formsDone,
      formsTotal,

      recordsDone,
      recordsTotal,

      pdfsDone,
      pdfsTotal,

      message:
        "Preparando datos offline...",
    });
  };

  onProgress?.({
    stage: "starting",

    formsDone: 0,
    formsTotal,

    recordsDone: 0,
    recordsTotal,

    pdfsDone: 0,
    pdfsTotal,

    message:
      "Iniciando sincronización...",
  });

  /*
  |--------------------------------------------------------------------------
  | Guardar catálogo de formularios
  |--------------------------------------------------------------------------
  */

  await cacheFormsCatalog(
    resolvedUserId,
    forms
  );

  /*
   * Elimina detalles, registros remotos y PDFs de formularios
   * que ya no forman parte del alcance del usuario.
   *
   * Las capturas locales pendientes se conservan.
   */
  await cleanupOfflineScope(
    resolvedUserId,
    forms
  );

  /*
  |--------------------------------------------------------------------------
  | Guardar detalle de cada formulario
  |--------------------------------------------------------------------------
  */

  for (const form of forms) {
    await cacheFormDetail(
      resolvedUserId,
      form
    );

    formsDone += 1;
    notify();
  }

  /*
  |--------------------------------------------------------------------------
  | Guardar registros por formulario
  |--------------------------------------------------------------------------
  */

  for (const form of forms) {
    const rows = getRowsForForm(
      submissionsByForm,
      form.id
    );

    await cacheFormSubmissions(
      resolvedUserId,
      form.id,
      rows
    );

    recordsDone += rows.length;
    notify();
  }

  /*
  |--------------------------------------------------------------------------
  | Descargar PDFs
  |--------------------------------------------------------------------------
  */

  for (const form of forms) {
    const rows = getRowsForForm(
      submissionsByForm,
      form.id
    );

    await preloadVisibleSubmissionPdfs({
      userId: resolvedUserId,
      formId: form.id,
      submissions: rows,
      token,

      onItemDone: () => {
        pdfsDone += 1;
        notify();
      },
    });
  }

  /*
  |--------------------------------------------------------------------------
  | Guardar meta únicamente al terminar correctamente
  |--------------------------------------------------------------------------
  */

  setStoredMeta(
    resolvedUserId,
    meta
  );

  onProgress?.({
    stage: "done",

    formsDone: formsTotal,
    formsTotal,

    recordsDone: recordsTotal,
    recordsTotal,

    pdfsDone: pdfsTotal,
    pdfsTotal,

    message:
      "Datos offline actualizados.",
  });

  /*
   * Notifica a FormsIndex y otras pantallas que IndexedDB
   * ya tiene la información nueva.
   */
  dispatchBootstrapComplete({
    userId: resolvedUserId,
    reason,
    mode,
    meta,
    formsTotal,
    recordsTotal,
    pdfsTotal,
  });

  return {
    ok: true,
    meta,
    reason,
    mode,
    formsTotal,
    recordsTotal,
    pdfsTotal,
  };
}

/*
|--------------------------------------------------------------------------
| Función pública con bloqueo de ejecuciones duplicadas
|--------------------------------------------------------------------------
*/

export async function runOfflineBootstrap(
  options = {}
) {
  const resolvedUserId =
    resolveUserId(options?.userId);

  if (!resolvedUserId) {
    throw new Error(
      "No se pudo identificar al usuario."
    );
  }

  /*
   * Si ya existe una actualización para este usuario,
   * esperamos esa misma actualización y no iniciamos otra.
   */
  const existingRun =
    bootstrapRuns.get(resolvedUserId);

  if (existingRun) {
    return existingRun;
  }

  const currentRun =
    executeOfflineBootstrap({
      ...options,
      userId: resolvedUserId,
    }).finally(() => {
      if (
        bootstrapRuns.get(
          resolvedUserId
        ) === currentRun
      ) {
        bootstrapRuns.delete(
          resolvedUserId
        );
      }
    });

  bootstrapRuns.set(
    resolvedUserId,
    currentRun
  );

  return currentRun;
}