import Dexie from "dexie";

export const db = new Dexie("formularios_pwa");

db.version(1).stores({
  records: "++id, uuid, type, created_at, synced",
  outbox: "++id, uuid, type, created_at, status, last_error",
});

db.version(2).stores({
  records: "++id, uuid, type, created_at, synced",
  outbox: "++id, uuid, type, created_at, status, last_error, retry_count, next_retry_at",
});

db.version(3).stores({
  records: "++id, uuid, type, created_at, synced",
  outbox: "++id, uuid, type, created_at, status, last_error, retry_count, next_retry_at",

  forms_catalog: "id, name, updated_at",
  form_details: "id, updated_at",
  form_submissions: "++id, form_id, local_uuid, remote_id, created_at, synced, pending_sync",
});

db.on("populate", async () => {
  // sin datos iniciales
});

db.on("ready", async () => {
  try {
    await db.outbox.toCollection().modify((o) => {
      if (o.retry_count === undefined || o.retry_count === null) o.retry_count = 0;
      if (o.next_retry_at === undefined) o.next_retry_at = null;
      if (o.last_error === undefined) o.last_error = null;
      if (!o.status) o.status = "pending";
    });
  } catch {
    // ignore
  }
});