import { db } from "./db";

function nowIso() {
  return new Date().toISOString();
}

export async function getCachedSubmissionPdf(userId, formId, submissionId) {
  if (!userId || !formId || !submissionId) return null;

  return (
    (await db.submission_pdfs
      .where("[user_id+form_id+submission_id]")
      .equals([Number(userId), Number(formId), Number(submissionId)])
      .first()) || null
  );
}

export async function saveSubmissionPdf(userId, formId, submissionId, blob) {
  if (!userId || !formId || !submissionId || !blob) return;

  const existing = await getCachedSubmissionPdf(userId, formId, submissionId);

  const payload = {
    user_id: Number(userId),
    form_id: Number(formId),
    submission_id: Number(submissionId),
    blob,
    mime_type: blob.type || "application/pdf",
    size: Number(blob.size || 0),
    cached_at: existing?.cached_at || nowIso(),
    updated_at: nowIso(),
  };

  if (existing?.id) {
    await db.submission_pdfs.update(existing.id, payload);
    return existing.id;
  }

  return db.submission_pdfs.add(payload);
}

export async function openCachedSubmissionPdf(userId, formId, submissionId) {
  const row = await getCachedSubmissionPdf(userId, formId, submissionId);

  if (!row?.blob) return false;

  const blobUrl = window.URL.createObjectURL(row.blob);
  window.location.href = blobUrl;

  setTimeout(() => {
    window.URL.revokeObjectURL(blobUrl);
  }, 60000);

  return true;
}

export async function cacheSubmissionPdfFromServer({
  userId,
  formId,
  submissionId,
  token,
}) {
  if (!userId || !formId || !submissionId || !token) return null;

  const response = await fetch(
    `/api/forms/${formId}/submissions/${submissionId}/pdf`,
    {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/pdf",
      },
    }
  );

  if (!response.ok) {
    throw new Error("No se pudo descargar el PDF.");
  }

  const blob = await response.blob();
  await saveSubmissionPdf(userId, formId, submissionId, blob);

  return blob;
}

export async function preloadVisibleSubmissionPdfs({
  userId,
  formId,
  submissions,
  token,
}) {
  if (!navigator.onLine) return;
  if (!userId || !formId || !token) return;
  if (!Array.isArray(submissions) || submissions.length === 0) return;

  for (const submission of submissions) {
    const submissionId = Number(submission?.id || 0);

    if (!submissionId) continue;
    if (submission?.offline_pending || submission?.pending_sync) continue;

    const exists = await getCachedSubmissionPdf(userId, formId, submissionId);
    if (exists?.blob) continue;

    try {
      await cacheSubmissionPdfFromServer({
        userId,
        formId,
        submissionId,
        token,
      });
    } catch {
      // ignore individual pdf cache failures
    }
  }
}