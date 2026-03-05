// resources/js/offline/db.js
import Dexie from "dexie";

export const db = new Dexie("formularios_pwa");

/**
 * v1 (legacy)
 * - Si ya llegaste a crear DB con v1 en algún equipo, esta versión debe quedarse
 *   para que Dexie pueda migrar correctamente a v2.
 */
db.version(1).stores({
  // Guarda la “carga” (answers, form_id, etc.)
  records: "++id, uuid, type, created_at, synced",

  // Cola de sincronización
  outbox: "++id, uuid, type, created_at, status, last_error", // pending|syncing|synced|error
});

/**
 * v2 (actual)
 * - Agregamos retry_count y next_retry_at al outbox
 * - IMPORTANTE: al cambiar el schema hay que subir la versión
 */
db.version(2).stores({
  records: "++id, uuid, type, created_at, synced",
  outbox: "++id, uuid, type, created_at, status, last_error, retry_count, next_retry_at",
});

/**
 * (Opcional pero recomendado)
 * Asegura valores por defecto al crear/actualizar DB en v2.
 * - No rompe si ya existe data.
 */
db.on("populate", async () => {
  // se ejecuta solo en BD nueva
});

db.on("ready", async () => {
  // Normaliza campos faltantes (por si venías de v1)
  try {
    await db.outbox
      .toCollection()
      .modify((o) => {
        if (o.retry_count === undefined || o.retry_count === null) o.retry_count = 0;
        if (o.next_retry_at === undefined) o.next_retry_at = null;
        if (o.last_error === undefined) o.last_error = null;
      });
  } catch {
    // ignore
  }
});