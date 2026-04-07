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

async function processOutboxItem(item) {
  const rec = await db.records
    .where("[user_id+uuid]")
    .equals([Number(item.user_id), item.uuid])
    .first();

  if (!rec) {
    await db.outbox.delete(item.id);
    return { ok: true };
  }

  try {
    if (rec.type === "form_submission") {
      await apiPost(`/forms/${rec.form_id}/submit`, {
        answers: rec.answers,
      });
    }

    await db.records.update(rec.id, { synced: true });
    await db.outbox.delete(item.id);

    return { ok: true };
  } catch (err) {
    await db.outbox.update(item.id, {
      status: "error",
      last_error: err?.message || "error",
      retry_count: (item.retry_count || 0) + 1,
      next_retry_at: nowIso(),
    });

    return { ok: false };
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

  for (const item of items) {
    await db.outbox.update(item.id, { status: "syncing" });
    await processOutboxItem(item);
  }

  return { ok: true };
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
}