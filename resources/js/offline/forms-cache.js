import { db } from "./db";

function nowIso() {
  return new Date().toISOString();
}

export async function cacheFormsCatalog(forms = []) {
  const rows = Array.isArray(forms) ? forms : [];

  await db.transaction("rw", db.forms_catalog, async () => {
    await db.forms_catalog.clear();

    for (const form of rows) {
      if (!form?.id) continue;

      await db.forms_catalog.put({
        ...form,
        updated_at: nowIso(),
      });
    }
  });
}

export async function getCachedFormsCatalog() {
  return db.forms_catalog.orderBy("name").toArray();
}

export async function cacheFormDetail(form) {
  if (!form?.id) return;

  await db.form_details.put({
    id: form.id,
    form,
    updated_at: nowIso(),
  });
}

export async function getCachedFormDetail(id) {
  const row = await db.form_details.get(Number(id));
  return row?.form || null;
}

export async function cacheFormSubmissions(formId, submissions = []) {
  const numericFormId = Number(formId);
  const rows = Array.isArray(submissions) ? submissions : [];

  await db.transaction("rw", db.form_submissions, async () => {
    const existing = await db.form_submissions.where("form_id").equals(numericFormId).toArray();

    for (const row of existing) {
      if (row.pending_sync) continue;
      await db.form_submissions.delete(row.id);
    }

    for (const sub of rows) {
      await db.form_submissions.put({
        form_id: numericFormId,
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

export async function getCachedFormSubmissions(formId) {
  const rows = await db.form_submissions
    .where("form_id")
    .equals(Number(formId))
    .reverse()
    .sortBy("created_at");

  return rows.map((row) => {
    if (row.submission) return row.submission;

    return {
      id: row.remote_id ?? row.local_uuid,
      consecutive: row.consecutive ?? "Pendiente",
      answers: row.answers ?? {},
      created_at: row.created_at,
      offline_pending: !!row.pending_sync,
      synced: !!row.synced,
    };
  });
}

export async function saveOfflineSubmission(form, answers, localUuid = null) {
  const formId = Number(form?.id);
  if (!formId) return;

  await db.form_submissions.put({
    form_id: formId,
    remote_id: null,
    local_uuid: localUuid || `local_${Date.now()}`,
    consecutive: "Pendiente",
    answers: { ...(answers || {}) },
    submission: {
      id: localUuid || `local_${Date.now()}`,
      consecutive: "Pendiente",
      answers: { ...(answers || {}) },
      created_at: nowIso(),
      offline_pending: true,
      synced: false,
    },
    created_at: nowIso(),
    synced: false,
    pending_sync: true,
  });
}