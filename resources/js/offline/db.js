// resources/js/offline/db.js
import Dexie from "dexie";

export const db = new Dexie("formularios_pwa");

/**
 * v1 (legacy)
 * - Si ya se creó BD en algún equipo, NO borres esta versión.
 * - Mantén los índices básicos que ya existían.
 */
db.version(1).stores({
  // Guarda la “carga” (answers, form_id, etc.)
  // Nota: aquí solo declaramos índices, no "columnas"
  records: "++id, uuid, type, created_at, synced",

  // Cola de sincronización
  outbox: "++id, uuid, type, created_at, status, last_error", // pending|syncing|synced|error
});

/**
 * v2 (actual)
 * - Agrega retry_count y next_retry_at
 * - Importante: conservamos índices previos + agregamos los nuevos
 */
db.version(2).stores({
  records: "++id, uuid, type, created_at, synced",

  // ✅ IMPORTANTE:
  // - indexamos status porque syncNow() filtra por status
  // - indexamos uuid porque hacemos búsquedas por uuid frecuentemente
  // - añadimos retry_count y next_retry_at para backoff
  outbox: "++id, uuid, type, created_at, status, last_error, retry_count, next_retry_at",
});

/**
 * Opcional (solo corre en BD NUEVA)
 */
db.on("populate", async () => {
  // nada por ahora
});

/**
 * Normaliza campos faltantes al iniciar (por si venías de v1)
 */
db.on("ready", async () => {
  try {
    await db.outbox.toCollection().modify((o) => {
      if (o.retry_count === undefined || o.retry_count === null) o.retry_count = 0;
      if (o.next_retry_at === undefined) o.next_retry_at = null;
      if (o.last_error === undefined) o.last_error = null;

      // si por algún motivo no existe status (muy raro), lo marcamos pending
      if (!o.status) o.status = "pending";
    });
  } catch {
    // ignore
  }
});