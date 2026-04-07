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
        id: row.remote_id ?? row.local_uuid,
        consecutive: row.consecutive ?? "Pendiente",
        answers: row.answers ?? {},
        created_at: row.created_at,
        offline_pending: !!row.pending_sync,
        synced: !!row.synced,
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
  localUuid = null
) {
  const uid = normalizeUserId(userId);
  const formId = Number(form?.id);

  if (!uid || !formId) return;

  const uuid = localUuid || `local_${Date.now()}`;

  await db.form_submissions.add({
    user_id: uid,
    form_id: formId,
    remote_id: null,
    local_uuid: uuid,
    consecutive: "Pendiente",
    answers: { ...(answers || {}) },
    submission: {
      id: uuid,
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