import { db } from "./db";
import { apiPost } from "../services/api";

function nowIso() {
  return new Date().toISOString();
}

function getCurrentUserId() {
  try {
    const user = JSON.parse(localStorage.getItem("user") || "null");
    return Number(user?.id || 0);
  } catch {
    return 0;
  }
}

function emitSyncEvent(name, detail = {}) {
  window.dispatchEvent(new CustomEvent(name, { detail }));
}

export async function enqueue(type, payload) {
  const uuid = payload?.uuid || `local_${Date.now()}`;
  const now = nowIso();
  const userId = getCurrentUserId();

  if (!userId) {
    throw new Error("No hay usuario para cola offline");
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

async function markLocalSubmissionAsSynced(userId, uuid, serverSubmission, rec) {
  const localRow = await db.form_submissions
    .where("[user_id+local_uuid]")
    .equals([Number(userId), uuid])
    .first();

  if (!localRow?.id) return;

  await db.form_submissions.update(localRow.id, {
    remote_id: serverSubmission?.id ?? null,
    consecutive: serverSubmission?.consecutive ?? localRow.consecutive ?? null,
    created_at: serverSubmission?.created_at || localRow.created_at || nowIso(),
    submission: {
      id: serverSubmission?.id ?? uuid,
      form_id: serverSubmission?.form_id ?? rec.form_id,
      consecutive: serverSubmission?.consecutive ?? localRow.consecutive ?? "Pendiente",
      user_id: serverSubmission?.user_id ?? Number(userId),
      user_name: serverSubmission?.user_name ?? null,
      answers: serverSubmission?.answers ?? rec.answers ?? {},
      created_at: serverSubmission?.created_at || localRow.created_at || nowIso(),
      offline_pending: false,
      pending_sync: false,
      synced: true,
    },
    synced: true,
    pending_sync: false,
  });
}

async function processOutboxItem(item) {
  const rec = await db.records
    .where("[user_id+uuid]")
    .equals([Number(item.user_id), item.uuid])
    .first();

  if (!rec) {
    await db.outbox.delete(item.id);
    return { ok: true, skipped: true };
  }

  try {
    if (rec.type === "form_submission") {
      const resp = await apiPost(`/forms/${rec.form_id}/submit`, {
        answers: rec.answers,
      });

      const serverSubmission = resp?.submission || null;

      await markLocalSubmissionAsSynced(
        item.user_id,
        item.uuid,
        serverSubmission,
        rec
      );
    }

    await db.records.update(rec.id, { synced: true });
    await db.outbox.delete(item.id);

    return { ok: true, uuid: item.uuid, type: rec.type };
  } catch (err) {
    await db.outbox.update(item.id, {
      status: "error",
      last_error: err?.message || "error",
      retry_count: (item.retry_count || 0) + 1,
      next_retry_at: nowIso(),
    });

    return {
      ok: false,
      uuid: item.uuid,
      type: rec.type,
      error: err?.message || "error",
    };
  }
}

export async function syncNow() {
  if (!navigator.onLine) {
    return { ok: false, reason: "offline" };
  }

  const userId = getCurrentUserId();
  if (!userId) {
    return { ok: false, reason: "no_user" };
  }

  const items = await db.outbox
    .where("[user_id+status]")
    .anyOf(
      [userId, "pending"],
      [userId, "error"],
      [userId, "syncing"]
    )
    .toArray();

  let successCount = 0;
  let errorCount = 0;

  for (const item of items) {
    await db.outbox.update(item.id, { status: "syncing" });

    const result = await processOutboxItem(item);

    if (result.ok) successCount += 1;
    else errorCount += 1;
  }

  emitSyncEvent("offline-sync-complete", {
    ok: errorCount === 0,
    successCount,
    errorCount,
  });

  return {
    ok: errorCount === 0,
    successCount,
    errorCount,
  };
}

export function setupAutoSync() {
  window.addEventListener("online", () => {
    syncNow().catch(() => null);
  });

  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible" && navigator.onLine) {
      syncNow().catch(() => null);
    }
  });

  if (navigator.onLine) {
    syncNow().catch(() => null);
  }
}