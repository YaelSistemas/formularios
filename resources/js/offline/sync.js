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
 * Normalizador (compatibilidad)
 * =========================================
 * Acepta:
 *  - enqueue("form_submission", { form_id, answers, meta })
 *  - enqueue("form_submission", { payload: { form_id, answers, meta } })
 */
function normalizeRecord(type, rec) {
  if (!rec) return { type, form_id: null, answers: {}, meta: null };

  // formato recomendado
  if (rec.form_id || rec.answers || rec.meta) {
    return {
      type,
      form_id: rec.form_id ?? null,
      answers: rec.answers ?? {},
      meta: rec.meta ?? null,
    };
  }

  // formato compat: { payload: {...} }
  if (rec.payload && typeof rec.payload === "object") {
    return {
      type,
      form_id: rec.payload.form_id ?? null,
      answers: rec.payload.answers ?? {},
      meta: rec.payload.meta ?? null,
    };
  }

  // último fallback
  return { type, form_id: null, answers: {}, meta: null };
}

/**
 * =========================================
 * API: enqueue
 * =========================================
 * type: "form_submission"
 * payload:
 *  - recomendado: { form_id, answers, meta? }
 *  - compat: { payload: { form_id, answers, meta? } }
 */
export async function enqueue(type, payload) {
  const uuid = safeUUID();
  const now = nowIso();

  // guardamos el record completo para tenerlo offline
  await db.records.add({
    uuid,
    type,
    ...payload,
    created_at: now,
    synced: false,
  });

  // outbox controla el estado de sincronización
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
    const normalized = normalizeRecord(item.type, rec);

    const formId = normalized.form_id;
    if (!formId) throw new Error("record.form_id faltante");

    // answers puede ser objeto; si viene null, mandamos {}
    const answers = normalized.answers && typeof normalized.answers === "object" ? normalized.answers : {};

    // ✅ Endpoint real:
    // POST /api/forms/{form}/submit
    await postJson(`/api/forms/${formId}/submit`, {
      answers,
      ...(normalized.meta ? { meta: normalized.meta } : {}),
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
    const items = await db.outbox.where("status").anyOf(["pending", "error"]).toArray();
    if (!items.length) return { ok: true, synced: 0, skipped: 0 };

    let syncedCount = 0;
    let skippedCount = 0;

    items.sort((a, b) => String(a.created_at || "").localeCompare(String(b.created_at || "")));

    for (const item of items) {
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

        // Si es auth (401/403), detenemos reintentos hasta re-login
        if (status === 401 || status === 403) {
          await db.outbox.update(item.id, {
            status: "error",
            last_error: `AUTH ${status}: ${String(e?.message || e)}`,
            next_retry_at: null,
          });

          return { ok: false, reason: "auth", synced: syncedCount, skipped: skippedCount };
        }

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
export function setupAutoSync({ intervalMs = 15000, runOnStart = true } = {}) {
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

  return () => {
    window.removeEventListener("online", onOnline);
    if (timer) window.clearInterval(timer);
    setupAutoSync.__installed = false;
  };
}
setupAutoSync.__installed = false;