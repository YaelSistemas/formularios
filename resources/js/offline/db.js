import Dexie from "dexie";

export const db = new Dexie("formularios_pwa");

db.version(5).stores({
  // Registros creados offline
  records:
    "++id, uuid, user_id, type, created_at, synced, [user_id+uuid]",

  // Cola de sincronización
  outbox:
    "++id, uuid, user_id, type, created_at, status, last_error, retry_count, next_retry_at, [user_id+status], [user_id+uuid]",

  // Catálogo de formularios por usuario
  forms_catalog:
    "++id, user_id, form_id, name, updated_at, [user_id+form_id], [user_id+name]",

  // Detalle de formularios
  form_details:
    "++id, user_id, form_id, updated_at, [user_id+form_id]",

  // Submissions (respuestas)
  form_submissions:
    "++id, user_id, form_id, local_uuid, remote_id, created_at, synced, pending_sync, [user_id+form_id], [user_id+remote_id], [user_id+local_uuid]",

  // PDFs cacheados offline por usuario / formulario / registro
  submission_pdfs:
    "++id, user_id, form_id, submission_id, cached_at, updated_at, [user_id+form_id], [user_id+submission_id], [user_id+form_id+submission_id]",
});