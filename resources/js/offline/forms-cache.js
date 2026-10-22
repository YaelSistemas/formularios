import { db } from "./db";

function nowIso() {
  return new Date().toISOString();
}

function normalizeUserId(userId) {
  return Number(userId || 0);
}

// ==========================
// CATÁLOGO
// ==========================
export async function cacheFormsCatalog(userId, forms = []) {
  const uid = normalizeUserId(userId);
  if (!uid) return;

  const rows = Array.isArray(forms) ? forms : [];

  await db.transaction("rw", db.forms_catalog, async () => {
    const existing = await db.forms_catalog
      .where("user_id")
      .equals(uid)
      .toArray();

    for (const row of existing) {
      await db.forms_catalog.delete(row.id);
    }

    for (const form of rows) {
      if (!form?.id) continue;

      await db.forms_catalog.add({
        user_id: uid,
        form_id: Number(form.id),
        name: form.title || form.name || `Formulario ${form.id}`,
        form,
        updated_at: nowIso(),
      });
    }
  });
}

export async function getCachedFormsCatalog(userId) {
  const uid = normalizeUserId(userId);
  if (!uid) return [];

  const rows = await db.forms_catalog
    .where("user_id")
    .equals(uid)
    .toArray();

  return rows
    .map((r) => r.form)
    .filter(Boolean)
    .sort((a, b) =>
      String(a.title || "").localeCompare(String(b.title || ""))
    );
}

// ==========================
// DETALLE
// ==========================
export async function cacheFormDetail(userId, form) {
  const uid = normalizeUserId(userId);
  if (!uid || !form?.id) return;

  const existing = await db.form_details
    .where("[user_id+form_id]")
    .equals([uid, Number(form.id)])
    .first();

  if (existing?.id) {
    await db.form_details.update(existing.id, {
      form,
      updated_at: nowIso(),
    });
    return;
  }

  await db.form_details.add({
    user_id: uid,
    form_id: Number(form.id),
    form,
    updated_at: nowIso(),
  });
}

export async function getCachedFormDetail(userId, formId) {
  const uid = normalizeUserId(userId);
  if (!uid) return null;

  const row = await db.form_details
    .where("[user_id+form_id]")
    .equals([uid, Number(formId)])
    .first();

  return row?.form || null;
}

// ==========================
// VERSIONES DE FORMULARIOS
// ==========================
export async function cacheFormVersions(
  userId,
  versions = {}
) {
  const uid = normalizeUserId(userId);

  if (!uid) return;

  const rows = Array.isArray(versions)
    ? versions
    : versions &&
        typeof versions === "object"
      ? Object.values(versions)
      : [];

  await db.transaction(
    "rw",
    db.form_versions,
    async () => {
      for (const version of rows) {
        const versionId = Number(
          version?.id || 0
        );

        const formId = Number(
          version?.form_id || 0
        );

        if (!versionId || !formId) {
          continue;
        }

        const existing =
          await db.form_versions
            .where(
              "[user_id+form_version_id]"
            )
            .equals([
              uid,
              versionId,
            ])
            .first();

        const data = {
          user_id: uid,

          form_version_id:
            versionId,

          form_id:
            formId,

          version:
            Number(
              version?.version || 0
            ),

          version_key:
            String(
              version?.version_key || ""
            ),

          form_version:
            version,

          updated_at:
            nowIso(),
        };

        if (existing?.id) {
          await db.form_versions.update(
            existing.id,
            data
          );

          continue;
        }

        await db.form_versions.add(
          data
        );
      }
    }
  );
}

export async function getCachedFormVersions(
  userId,
  formId = null
) {
  const uid = normalizeUserId(userId);

  if (!uid) {
    return {};
  }

  const fid = Number(formId || 0);

  const rows = fid
    ? await db.form_versions
        .where("[user_id+form_id]")
        .equals([uid, fid])
        .toArray()
    : await db.form_versions
        .where("user_id")
        .equals(uid)
        .toArray();

  return rows.reduce(
    (result, row) => {
      const version =
        row?.form_version;

      const versionId = Number(
        version?.id ||
          row?.form_version_id ||
          0
      );

      if (
        versionId &&
        version
      ) {
        result[String(versionId)] =
          version;
      }

      return result;
    },
    {}
  );
}

// ==========================
// SUBMISSIONS
// ==========================
export async function cacheFormSubmissions(userId, formId, submissions = []) {
  const uid = normalizeUserId(userId);
  const fid = Number(formId);
  if (!uid || !fid) return;

  const rows = Array.isArray(submissions) ? submissions : [];

  await db.transaction("rw", db.form_submissions, async () => {
    const existing = await db.form_submissions
      .where("[user_id+form_id]")
      .equals([uid, fid])
      .toArray();

    for (const row of existing) {
      if (row.pending_sync) continue;
      await db.form_submissions.delete(row.id);
    }

    for (const sub of rows) {
      await db.form_submissions.add({
        user_id: uid,
        form_id: fid,
      
        form_version_id:
          Number(
            sub?.form_version_id || 0
          ) || null,
      
        remote_id: sub.id ?? null,
        local_uuid: null,
        consecutive: sub.consecutive ?? null,
        answers: sub.answers ?? {},
        submission: sub,
        created_at: sub.created_at || nowIso(),
        synced: true,
        pending_sync: false,
      });
    }
  });
}

export async function getCachedFormSubmissions(userId, formId) {
  const uid = normalizeUserId(userId);
  if (!uid) return [];

  const rows = await db.form_submissions
    .where("[user_id+form_id]")
    .equals([uid, Number(formId)])
    .toArray();

  return rows
    .sort((a, b) =>
      String(b.created_at || "").localeCompare(String(a.created_at || ""))
    )
    .map((row) => {
      if (row.submission) return row.submission;

      return {
        id:
          row.remote_id ??
          row.local_uuid,
      
        form_id:
          row.form_id,
      
        form_version_id:
          Number(
            row.form_version_id || 0
          ) || null,
      
        consecutive:
          row.consecutive ??
          "Pendiente",
      
        answers:
          row.answers ?? {},
      
        created_at:
          row.created_at,
      
        offline_pending:
          !!row.pending_sync,
      
        pending_sync:
          !!row.pending_sync,
      
        synced:
          !!row.synced,
      };
    });
}

// ==========================
// GUARDAR OFFLINE
// ==========================
export async function saveOfflineSubmission(
  userId,
  form,
  answers,
  localUuid = null,
  createdAt = null
) {
  const uid =
    normalizeUserId(userId);

  const formId =
    Number(form?.id);

  const formVersionId =
    Number(
      form?.form_version_id ||
        form?.form_version?.id ||
        0
    );

  if (!uid || !formId) {
    return;
  }

  if (!formVersionId) {
    throw new Error(
      "No se pudo identificar la versión del formulario para guardar el registro offline."
    );
  }

  const uuid =
    localUuid ||
    `local_${Date.now()}`;

  /*
   * Conservamos exactamente la misma
   * fecha generada en FormFill.jsx.
   */
  const offlineCreatedAt =
    createdAt || nowIso();

  await db.form_submissions.add({
    user_id:
      uid,

    form_id:
      formId,

    form_version_id:
      formVersionId,

    remote_id:
      null,

    local_uuid:
      uuid,

    consecutive:
      "Pendiente",

    answers: {
      ...(answers || {}),
    },

    offline_created_at:
      offlineCreatedAt,

    submission: {
      id:
        uuid,

      form_id:
        formId,

      form_version_id:
        formVersionId,

      consecutive:
        "Pendiente",

      answers: {
        ...(answers || {}),
      },

      created_at:
        offlineCreatedAt,

      offline_created_at:
        offlineCreatedAt,

      offline_pending:
        true,

      pending_sync:
        true,

      synced:
        false,
    },

    created_at:
      offlineCreatedAt,

    synced:
      false,

    pending_sync:
      true,
  });
}

// ==========================
// LIMPIAR DATOS FUERA DEL ALCANCE
// ==========================
export async function cleanupOfflineScope(userId, forms = []) {
  const uid = normalizeUserId(userId);

  if (!uid) {
    return {
      deletedDetails: 0,
      deletedSubmissions: 0,
      deletedPdfs: 0,
      deletedVersions: 0,
    };
  }

  const allowedFormIds = new Set(
    (Array.isArray(forms) ? forms : [])
      .map((form) => Number(form?.id || 0))
      .filter(Boolean)
  );

  return db.transaction(
    "rw",
    db.form_details,
    db.form_submissions,
    db.submission_pdfs,
    db.form_versions,
    async () => {
      /*
       * Detalles de formularios almacenados para este usuario.
       */
      const existingDetails = await db.form_details
        .where("user_id")
        .equals(uid)
        .toArray();

      /*
       * Registros almacenados para este usuario.
       */
      const existingSubmissions = await db.form_submissions
        .where("user_id")
        .equals(uid)
        .toArray();

      /*
       * PDFs almacenados para este usuario.
       */
      const existingPdfs = await db.submission_pdfs
        .where("user_id")
        .equals(uid)
        .toArray();

      /*
       * Versiones de formularios almacenadas
       * para este usuario.
       */
      const existingVersions =
        await db.form_versions
          .where("user_id")
          .equals(uid)
          .toArray();

      /*
       * Eliminamos detalles de formularios que ya no
       * están dentro del alcance del usuario.
       */
      const detailIdsToDelete = existingDetails
        .filter(
          (row) =>
            !allowedFormIds.has(
              Number(row.form_id)
            )
        )
        .map((row) => row.id)
        .filter(Boolean);

      /*
       * Eliminamos únicamente registros remotos pertenecientes
       * a formularios que ya no están asignados.
       *
       * Los registros pendientes de sincronizar se conservan.
       */
      const submissionIdsToDelete =
        existingSubmissions
          .filter((row) => {
            const outsideScope =
              !allowedFormIds.has(
                Number(row.form_id)
              );

            const isPending =
              Boolean(row.pending_sync) ||
              row.synced === false;

            return (
              outsideScope &&
              !isPending
            );
          })
          .map((row) => row.id)
          .filter(Boolean);

      /*
       * Eliminamos todos los PDFs de formularios
       * que ya no están permitidos.
       */
      const pdfIdsToDelete = existingPdfs
        .filter(
          (row) =>
            !allowedFormIds.has(
              Number(row.form_id)
            )
        )
        .map((row) => row.id)
        .filter(Boolean);

      /*
       * Eliminamos versiones de formularios
       * que ya no pertenecen al alcance.
       */
      const versionIdsToDelete =
        existingVersions
          .filter(
            (row) =>
              !allowedFormIds.has(
                Number(row.form_id)
              )
          )
          .map((row) => row.id)
          .filter(Boolean);

      if (detailIdsToDelete.length > 0) {
        await db.form_details.bulkDelete(
          detailIdsToDelete
        );
      }

      if (
        submissionIdsToDelete.length > 0
      ) {
        await db.form_submissions.bulkDelete(
          submissionIdsToDelete
        );
      }

      if (pdfIdsToDelete.length > 0) {
        await db.submission_pdfs.bulkDelete(
          pdfIdsToDelete
        );
      }

      if (
        versionIdsToDelete.length > 0
      ) {
        await db.form_versions.bulkDelete(
          versionIdsToDelete
        );
      }

      return {
        deletedDetails:
          detailIdsToDelete.length,
      
        deletedSubmissions:
          submissionIdsToDelete.length,
      
        deletedPdfs:
          pdfIdsToDelete.length,
      
        deletedVersions:
          versionIdsToDelete.length,
      };
    }
  );
}