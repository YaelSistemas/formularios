// resources/js/offline/sync.js
import { db } from "./db";

/**
 * =========================================
 * Helpers
 * =========================================
 */
let isSyncing = false;

function getToken() {
  return localStorage.getItem("token") || "";
}

function safeUUID() {
  if (typeof crypto !== "undefined" && crypto.randomUUID) return crypto.randomUUID();
  // fallback simple (por si algún browser viejo)
  return "uuid_" + Math.random().toString(16).slice(2) + "_" + Date.now().toString(16);
}

function nowIso() {
  return new Date().toISOString();
}

function addSecondsIso(seconds) {
  return new Date(Date.now() + seconds * 1000).toISOString();
}

// Backoff: 3s, 8s, 20s, 60s, 180s, 600s...
function getBackoffSeconds(retryCount) {
  const steps = [3, 8, 20, 60, 180, 600];
  return steps[Math.min(retryCount, steps.length - 1)];
}

function isDue(nextRetryAt) {
  if (!nextRetryAt) return true;
  const t = Date.parse(nextRetryAt);
  if (Number.isNaN(t)) return true;
  return Date.now() >= t;
}

async function postJson(url, body) {
  const token = getToken();

  const res = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(body),
  });

  // intenta leer respuesta (json o texto)
  let data = null;
  const text = await res.text();
  try {
    data = text ? JSON.parse(text) : null;
  } catch {
    data = text || null;
  }

  if (!res.ok) {
    const msg =
      (data && typeof data === "object" && data.message) ||
      (typeof data === "string" ? data : "") ||
      `HTTP ${res.status}`;

    const err = new Error(msg);
    err.status = res.status;
    err.payload = data;
    throw err;
  }

  return data;
}

/**
 * =========================================
 * API: enqueue
 * =========================================
 * type: "form_submission"
 * payload: { form_id, answers, meta? }
 */
export async function enqueue(type, payload) {
  const uuid = safeUUID();
  const now = nowIso();

  await db.records.add({
    uuid,
    type,
    ...payload,
    created_at: now,
    synced: false,
  });

  await db.outbox.add({
    uuid,
    type,
    created_at: now,
    status: "pending",
    last_error: null,
    retry_count: 0,
    next_retry_at: null,
  });

  return uuid;
}

/**
 * =========================================
 * Procesamiento por type (mapea a endpoints reales)
 * =========================================
 */
async function processOutboxItem(item) {
  const rec = await db.records.where("uuid").equals(item.uuid).first();
  if (!rec) throw new Error("No existe record para este uuid.");

  // ✅ Mapea types a endpoints
  if (item.type === "form_submission") {
    const formId = rec.form_id;
    if (!formId) throw new Error("record.form_id faltante");

    // ✅ Tu endpoint real en routes/api.php:
    // POST /api/forms/{form}/submit
    await postJson(`/api/forms/${formId}/submit`, {
      answers: rec.answers || {},
      ...(rec.meta ? { meta: rec.meta } : {}),
    });

    return true;
  }

  throw new Error(`Type no soportado: ${item.type}`);
}

/**
 * =========================================
 * syncNow: sincroniza pending + error (si ya toca reintento)
 * =========================================
 */
export async function syncNow() {
  if (isSyncing) return { ok: true, synced: 0, skipped: 0, reason: "already_syncing" };
  if (!navigator.onLine) return { ok: false, reason: "offline" };

  isSyncing = true;

  try {
    // Traemos pendientes y errores
    const items = await db.outbox.where("status").anyOf(["pending", "error"]).toArray();
    if (!items.length) return { ok: true, synced: 0, skipped: 0 };

    let syncedCount = 0;
    let skippedCount = 0;

    // Orden por created_at (si existe) para respetar orden
    items.sort((a, b) => String(a.created_at || "").localeCompare(String(b.created_at || "")));

    for (const item of items) {
      // Si es error pero aún no toca reintento, lo saltamos
      if (item.status === "error" && !isDue(item.next_retry_at)) {
        skippedCount++;
        continue;
      }

      try {
        await db.outbox.update(item.id, { status: "syncing", last_error: null });

        await processOutboxItem(item);

        await db.outbox.update(item.id, {
          status: "synced",
          last_error: null,
          next_retry_at: null,
        });

        await db.records.where("uuid").equals(item.uuid).modify({ synced: true });

        syncedCount++;
      } catch (e) {
        const status = e?.status;

        // Si es auth (401/403), no reintentar hasta re-login
        if (status === 401 || status === 403) {
          await db.outbox.update(item.id, {
            status: "error",
            last_error: `AUTH ${status}: ${String(e?.message || e)}`,
            next_retry_at: null,
          });

          return { ok: false, reason: "auth", synced: syncedCount, skipped: skippedCount };
        }

        // Reintento con backoff
        const prevRetry = Number(item.retry_count || 0);
        const nextRetry = prevRetry + 1;
        const waitSeconds = getBackoffSeconds(prevRetry);

        await db.outbox.update(item.id, {
          status: "error",
          last_error: String(e?.message || e),
          retry_count: nextRetry,
          next_retry_at: addSecondsIso(waitSeconds),
        });
      }
    }

    return { ok: true, synced: syncedCount, skipped: skippedCount };
  } finally {
    isSyncing = false;
  }
}

/**
 * =========================================
 * Auto-sync
 * - al volver online
 * - y un "tick" cada X segundos (opcional)
 * =========================================
 */
export function setupAutoSync({
  intervalMs = 15000, // 15s (ajústalo)
  runOnStart = true,
} = {}) {
  // evita duplicar listeners si lo llamas 2 veces
  if (setupAutoSync.__installed) return;
  setupAutoSync.__installed = true;

  const onOnline = () => {
    syncNow().catch(() => null);
  };

  window.addEventListener("online", onOnline);

  let timer = null;
  if (intervalMs && intervalMs > 0) {
    timer = window.setInterval(() => {
      if (!navigator.onLine) return;
      syncNow().catch(() => null);
    }, intervalMs);
  }

  if (runOnStart) {
    if (navigator.onLine) syncNow().catch(() => null);
  }

  // Retorna un "unsubscribe" por si algún día lo ocupas
  return () => {
    window.removeEventListener("online", onOnline);
    if (timer) window.clearInterval(timer);
    setupAutoSync.__installed = false;
  };
}
setupAutoSync.__installed = false;