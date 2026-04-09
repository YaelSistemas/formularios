import { apiGet } from "../services/api";
import {
  cacheFormsCatalog,
  cacheFormDetail,
  cacheFormSubmissions,
} from "./forms-cache";
import { preloadVisibleSubmissionPdfs } from "./pdf-cache";

const META_KEY = "offline_bootstrap_meta_v1";

function getStoredMeta() {
  try {
    return JSON.parse(localStorage.getItem(META_KEY) || "null");
  } catch {
    return null;
  }
}

function setStoredMeta(meta) {
  localStorage.setItem(META_KEY, JSON.stringify(meta));
}

function normalizeMeta(meta) {
  return {
    forms_count: Number(meta?.forms_count || 0),
    submissions_count: Number(meta?.submissions_count || 0),
    pdfs_count: Number(meta?.pdfs_count || 0),
    last_change_at: String(meta?.last_change_at || ""),
    unit_scope_hash: String(meta?.unit_scope_hash || ""),
  };
}

function metaChanged(prev, next) {
  if (!prev) return true;

  return (
    Number(prev.forms_count || 0) !== Number(next.forms_count || 0) ||
    Number(prev.submissions_count || 0) !== Number(next.submissions_count || 0) ||
    Number(prev.pdfs_count || 0) !== Number(next.pdfs_count || 0) ||
    String(prev.last_change_at || "") !== String(next.last_change_at || "") ||
    String(prev.unit_scope_hash || "") !== String(next.unit_scope_hash || "")
  );
}

export async function shouldRunOfflineBootstrap() {
  if (!navigator.onLine) {
    return { shouldRun: false, reason: "offline" };
  }

  const remoteMetaRaw = await apiGet("/offline/bootstrap-meta");
  const remoteMeta = normalizeMeta(remoteMetaRaw);
  const localMeta = getStoredMeta();

  return {
    shouldRun: metaChanged(localMeta, remoteMeta),
    remoteMeta,
    localMeta,
  };
}

export async function runOfflineBootstrap({
  userId,
  token,
  onProgress,
}) {
  if (!userId) {
    throw new Error("No se pudo identificar al usuario.");
  }

  if (!navigator.onLine) {
    throw new Error("No hay conexión para preparar datos offline.");
  }

  const metaRaw = await apiGet("/offline/bootstrap-meta");
  const meta = normalizeMeta(metaRaw);

  const boot = await apiGet("/offline/bootstrap");
  const forms = Array.isArray(boot?.forms) ? boot.forms : [];
  const submissionsByForm =
    boot?.submissions_by_form && typeof boot.submissions_by_form === "object"
      ? boot.submissions_by_form
      : {};

  let formsDone = 0;
  let recordsDone = 0;
  let pdfsDone = 0;

  const formsTotal = Number(meta.forms_count || forms.length || 0);
  const recordsTotal = Number(meta.submissions_count || 0);
  const pdfsTotal = Number(meta.pdfs_count || 0);

  const notify = () => {
    onProgress?.({
      stage: "syncing",
      formsDone,
      formsTotal,
      recordsDone,
      recordsTotal,
      pdfsDone,
      pdfsTotal,
      message: "Preparando datos offline...",
    });
  };

  onProgress?.({
    stage: "starting",
    formsDone: 0,
    formsTotal,
    recordsDone: 0,
    recordsTotal,
    pdfsDone: 0,
    pdfsTotal,
    message: "Iniciando sincronización...",
  });

  await cacheFormsCatalog(userId, forms);

  for (const form of forms) {
    await cacheFormDetail(userId, form);
    formsDone += 1;
    notify();
  }

  for (const form of forms) {
    const rows = Array.isArray(submissionsByForm?.[form.id])
      ? submissionsByForm[form.id]
      : [];

    await cacheFormSubmissions(userId, form.id, rows);

    recordsDone += rows.length;
    notify();
  }

  for (const form of forms) {
    const rows = Array.isArray(submissionsByForm?.[form.id])
      ? submissionsByForm[form.id]
      : [];

    let formPdfDone = 0;

    await preloadVisibleSubmissionPdfs({
      userId,
      formId: form.id,
      submissions: rows,
      token,
      onItemDone: () => {
        formPdfDone += 1;
        pdfsDone += 1;
        notify();
      },
    });
  }

  setStoredMeta(meta);

  onProgress?.({
    stage: "done",
    formsDone: formsTotal,
    formsTotal,
    recordsDone: recordsTotal,
    recordsTotal,
    pdfsDone: pdfsTotal,
    pdfsTotal,
    message: "Datos offline actualizados.",
  });

  return {
    ok: true,
    meta,
  };
}