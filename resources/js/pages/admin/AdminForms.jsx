import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

function uid() {
  return "f_" + Math.random().toString(16).slice(2) + "_" + Date.now().toString(16);
}

// ✅ Componentes tipo Kizeo (builder)
const FIELD_TYPES = [
  // básicos
  { value: "text", label: "Campo de entrada" },
  { value: "textarea", label: "Cuadro de texto" },
  { value: "checkbox", label: "Checkbox" },

  // listas
  { value: "select", label: "Selección" },
  { value: "list", label: "Lista" },
  { value: "radio", label: "Selección única (Radio)" },

  // info / fijo
  { value: "static_text", label: "Texto fijo" },
  { value: "separator", label: "Separador" },
  { value: "fixed_image", label: "Imagen fija" },
  { value: "fixed_file", label: "Archivo fijo" },

  // captura
  { value: "number", label: "Número" },
  { value: "date", label: "Fecha" },
  { value: "datetime", label: "Fecha y hora" },

  // especiales
  { value: "contact", label: "Contacto" },
  { value: "address", label: "Dirección" },
  { value: "photo", label: "Foto" },
  { value: "file", label: "Archivo adjunto" },
  { value: "signature", label: "Firma" },
  { value: "table", label: "Tabla" },
];

const TYPE_ICON = {
  text: "fa-solid fa-i-cursor",
  textarea: "fa-solid fa-align-left",
  checkbox: "fa-regular fa-square-check",

  select: "fa-solid fa-list",
  list: "fa-solid fa-list-ul",
  radio: "fa-solid fa-circle-dot",

  static_text: "fa-solid fa-font",
  separator: "fa-solid fa-grip-lines",
  fixed_image: "fa-regular fa-image",
  fixed_file: "fa-regular fa-file-lines",

  number: "fa-solid fa-hashtag",
  date: "fa-solid fa-calendar-days",
  datetime: "fa-solid fa-clock",

  contact: "fa-solid fa-address-card",
  address: "fa-solid fa-location-dot",
  photo: "fa-solid fa-camera",
  file: "fa-solid fa-paperclip",
  signature: "fa-solid fa-signature",
  table: "fa-solid fa-table",
};

export default function AdminForms() {
  const [err, setErr] = useState("");

  // ✅ toast (3s)
  const [toast, setToast] = useState(null); // { type: 'success'|'info'|'danger', text: string }
  const toastTimer = useRef(null);

  const showToast = (type, text) => {
    setToast({ type, text });
    if (toastTimer.current) clearTimeout(toastTimer.current);
    toastTimer.current = setTimeout(() => setToast(null), 3000);
  };

  useEffect(() => {
    return () => {
      if (toastTimer.current) clearTimeout(toastTimer.current);
    };
  }, []);

  const [forms, setForms] = useState([]);
  const [loadingForms, setLoadingForms] = useState(false);

  // ✅ buscador: draft + debounce + focus keeper
  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  // ✅ por página: permitir 30
  const [perPage, setPerPage] = useState(30);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const canPrev = useMemo(() => page > 1, [page]);
  const canNext = useMemo(() => page < (meta.last_page || 1), [page, meta.last_page]);

  // ✅ Focus keeper (soluciona el "letra por letra")
  const searchRef = useRef(null);
  const searchWasFocusedRef = useRef(false);

  const rememberFocus = () => {
    searchWasFocusedRef.current = true;
  };

  const restoreFocusIfNeeded = () => {
    const el = searchRef.current;
    if (!el) return;
    if (!searchWasFocusedRef.current) return;

    el.focus({ preventScroll: true });
    try {
      const len = el.value?.length ?? 0;
      el.setSelectionRange(len, len);
    } catch {
      // ignore
    }
  };

  // --------- MODAL BUILDER ----------
  const [openFormModal, setOpenFormModal] = useState(false);
  const [formsMode, setFormsMode] = useState("create"); // create | edit
  const [editingFormId, setEditingFormId] = useState(null);

  const [formTitle, setFormTitle] = useState("Nuevo formulario");
  const [formStatus, setFormStatus] = useState("BORRADOR"); // BORRADOR | PUBLICADO | INACTIVO

  // ✅ fields (builder)
  const [formFields, setFormFields] = useState([
    {
      id: uid(),
      label: "Nombre",
      type: "text",
      required: true,
      optionsText: "",
      staticText: "",
      fileUrl: "",
      accept: "",
      columnsText: "",
    },
  ]);
  const [selectedFieldId, setSelectedFieldId] = useState(null);

  const [savingForm, setSavingForm] = useState(false);
  const [publishingFormId, setPublishingFormId] = useState(null);
  const [unpublishingFormId, setUnpublishingFormId] = useState(null);
  const [deletingFormId, setDeletingFormId] = useState(null);

  // ✅ PREVIEW (👁)
  const [openPreview, setOpenPreview] = useState(false);
  const [previewForm, setPreviewForm] = useState(null);

  const openPreviewModal = async (f) => {
    setErr("");
    setPreviewForm(null);
    setOpenPreview(true);

    try {
      if (f?.payload?.fields?.length) {
        setPreviewForm(f);
        return;
      }
      const data = await apiGet(`/admin/forms/${f.id}`);
      setPreviewForm(data?.form || data);
    } catch {
      setPreviewForm(f);
    }
  };

  const closePreviewModal = () => {
    setOpenPreview(false);
    setPreviewForm(null);
  };

  const getCreatorName = (f) => {
    return (
      f?.created_by_name ||
      f?.created_by?.name ||
      f?.creator?.name ||
      f?.user?.name ||
      f?.created_user?.name ||
      f?.author?.name ||
      f?.created_by ||
      "—"
    );
  };

  const formatDate = (d) => {
    if (!d) return "—";
    const s = String(d);
    if (s.includes("T")) return s.replace("T", " ").slice(0, 16);
    return s;
  };

  const loadAdminForms = async () => {
    setErr("");
    setLoadingForms(true);
    try {
      const data = await apiGet(`/admin/forms?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`);

      // ✅ soporte dual: paginado tipo Laravel o arreglo simple
      if (Array.isArray(data.forms)) {
        setForms(data.forms);
        setMeta({ last_page: 1, total: data.forms.length });
      } else if (Array.isArray(data.data)) {
        setForms(data.data);
        setMeta({
          last_page: data.last_page || 1,
          total: data.total || 0,
        });
      } else {
        setForms([]);
        setMeta({ last_page: 1, total: 0 });
      }
    } catch (e) {
      setErr(e?.message || "Error cargando formularios (admin)");
    } finally {
      setLoadingForms(false);
    }
  };

  // ✅ debounce del buscador
  useEffect(() => {
    const t = setTimeout(() => {
      setPage(1);
      setQ(qDraft);
    }, 350);
    return () => clearTimeout(t);
  }, [qDraft]);

  useEffect(() => {
    loadAdminForms();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    loadAdminForms();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, perPage, page]);

  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openFormModal || openPreview) return;
    if (!searchWasFocusedRef.current) return;

    const el = searchRef.current;
    if (!el) return;
    if (document.activeElement === el) return;

    el.focus({ preventScroll: true });
    try {
      const len = el.value?.length ?? 0;
      el.setSelectionRange(len, len);
    } catch {
      // ignore
    }
  }, [loadingForms, forms, meta.last_page, meta.total, openFormModal, openPreview]);

  // ---------- BUILDER LOGIC ----------
  const selectedField = useMemo(
    () => (selectedFieldId ? formFields.find((f) => f.id === selectedFieldId) : null),
    [selectedFieldId, formFields]
  );

  const ensureSelected = (fields) => {
    if (!fields.length) {
      setSelectedFieldId(null);
      return;
    }
    const exists = selectedFieldId && fields.some((x) => x.id === selectedFieldId);
    if (!exists) setSelectedFieldId(fields[0].id);
  };

  const addFieldOfType = (type) => {
    const base = {
      id: uid(),
      type,
      required: false,
      label: "Campo",
      optionsText: "",
      staticText: "",
      fileUrl: "",
      accept: "",
      columnsText: "",
    };

    // defaults por tipo
    if (type === "checkbox") base.label = "Checkbox";
    if (type === "text") base.label = "Campo de entrada";
    if (type === "textarea") base.label = "Cuadro de texto";
    if (type === "number") base.label = "Número";
    if (type === "date") base.label = "Fecha";
    if (type === "datetime") base.label = "Fecha y hora";

    if (type === "select" || type === "list" || type === "radio") {
      base.label = type === "radio" ? "Selección" : "Lista";
      base.optionsText = "Opción 1, Opción 2";
    }

    if (type === "static_text") {
      base.label = "Texto fijo";
      base.staticText = "Texto informativo...";
      base.required = false;
    }

    if (type === "separator") {
      base.label = "Separador";
      base.required = false;
    }

    if (type === "fixed_image") {
      base.label = "Imagen fija";
      base.fileUrl = "https://...";
      base.required = false;
    }

    if (type === "fixed_file") {
      base.label = "Archivo fijo";
      base.fileUrl = "https://...";
      base.required = false;
    }

    if (type === "file") {
      base.label = "Archivo adjunto";
      base.accept = ".pdf,.jpg,.png,.doc,.docx,.xlsx";
    }

    if (type === "photo") {
      base.label = "Foto";
      base.accept = "image/*";
    }

    if (type === "signature") {
      base.label = "Firma";
    }

    if (type === "contact") {
      base.label = "Contacto";
    }

    if (type === "address") {
      base.label = "Dirección";
    }

    if (type === "table") {
      base.label = "Tabla";
      base.columnsText = "Columna 1, Columna 2";
      base.required = false;
    }

    setFormFields((prev) => {
      const next = [...prev, base];
      setSelectedFieldId(base.id);
      return next;
    });
  };

  const updateSelectedField = (patch) => {
    if (!selectedFieldId) return;
    setFormFields((prev) => prev.map((f) => (f.id === selectedFieldId ? { ...f, ...patch } : f)));
  };

  const removeField = (id) => {
    setFormFields((prev) => {
      const next = prev.filter((f) => f.id !== id);
      if (selectedFieldId === id) {
        setSelectedFieldId(next[0]?.id || null);
      }
      return next;
    });
  };

  const moveField = (id, dir) => {
    setFormFields((prev) => {
      const i = prev.findIndex((x) => x.id === id);
      if (i < 0) return prev;

      const j = dir === "up" ? i - 1 : i + 1;
      if (j < 0 || j >= prev.length) return prev;

      const next = [...prev];
      const tmp = next[i];
      next[i] = next[j];
      next[j] = tmp;
      return next;
    });
  };

  // ---------- PAYLOAD ----------
  const buildFormPayload = () => {
    const normalized = (formFields || [])
      .map((f) => {
        const label = String(f.label || "").trim();
        const type = String(f.type || "text");
        const required = !!f.required;

        // separador puede ir sin label si quieres
        if (!label && !["separator"].includes(type)) return null;

        // listas (select/list/radio)
        if (type === "select" || type === "list" || type === "radio") {
          const options = String(f.optionsText || "")
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean);

          return { id: f.id, label, type, required, options };
        }

        // texto fijo
        if (type === "static_text") {
          return { id: f.id, label, type, required: false, text: String(f.staticText || "").trim() };
        }

        // separador
        if (type === "separator") {
          return { id: f.id, label: label || "Separador", type, required: false };
        }

        // imagen fija / archivo fijo
        if (type === "fixed_image" || type === "fixed_file") {
          return { id: f.id, label, type, required: false, url: String(f.fileUrl || "").trim() };
        }

        // archivo adjunto / foto
        if (type === "file" || type === "photo") {
          return { id: f.id, label, type, required, accept: String(f.accept || "").trim() };
        }

        // tabla
        if (type === "table") {
          const columns = String(f.columnsText || "")
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean);

          return { id: f.id, label, type, required, columns };
        }

        // default
        return { id: f.id, label, type, required };
      })
      .filter(Boolean);

    return { fields: normalized };
  };

  const validateFormsBuilder = () => {
    const t = formTitle.trim();
    if (!t) return "Escribe un título.";

    const payload = buildFormPayload();
    if (!payload.fields.length) return "Agrega al menos 1 campo con etiqueta.";

    for (const f of payload.fields) {
      if ((f.type === "select" || f.type === "list" || f.type === "radio") && (!Array.isArray(f.options) || f.options.length < 2)) {
        return `El campo "${f.label}" (${f.type}) debe tener al menos 2 opciones.`;
      }
      if (f.type === "table" && (!Array.isArray(f.columns) || f.columns.length < 1)) {
        return `El campo "${f.label}" (tabla) debe tener al menos 1 columna.`;
      }
      if ((f.type === "fixed_file" || f.type === "fixed_image") && (!f.url || String(f.url).trim() === "")) {
        return `El campo "${f.label}" (${f.type}) requiere una URL.`;
      }
    }
    return null;
  };

  const resetFormsModal = () => {
    setEditingFormId(null);
    setFormsMode("create");
    setFormTitle("Nuevo formulario");
    setFormStatus("BORRADOR");

    const first = {
      id: uid(),
      label: "Nombre",
      type: "text",
      required: true,
      optionsText: "",
      staticText: "",
      fileUrl: "",
      accept: "",
      columnsText: "",
    };
    setFormFields([first]);
    setSelectedFieldId(first.id);

    setSavingForm(false);
  };

  const openCreateFormModal = () => {
    setErr("");
    resetFormsModal();
    setOpenFormModal(true);
  };

  const openEditFormModal = (f) => {
    setErr("");
    setFormsMode("edit");
    setEditingFormId(f.id);

    setFormTitle(f.title || "");
    setFormStatus(f.status || "BORRADOR");

    const fields = Array.isArray(f?.payload?.fields) ? f.payload.fields : [];
    const mapped =
      fields.length
        ? fields.map((ff) => ({
            id: ff.id,
            label: ff.label,
            type: ff.type || "text",
            required: !!ff.required,

            optionsText:
              ff.type === "select" || ff.type === "list" || ff.type === "radio"
                ? (ff.options || []).join(", ")
                : "",

            staticText: ff.type === "static_text" ? ff.text || "" : "",
            fileUrl: ff.type === "fixed_image" || ff.type === "fixed_file" ? ff.url || "" : "",
            accept: ff.type === "file" || ff.type === "photo" ? ff.accept || "" : "",
            columnsText: ff.type === "table" ? (ff.columns || []).join(", ") : "",
          }))
        : [
            {
              id: uid(),
              label: "Nombre",
              type: "text",
              required: true,
              optionsText: "",
              staticText: "",
              fileUrl: "",
              accept: "",
              columnsText: "",
            },
          ];

    setFormFields(mapped);
    setSelectedFieldId(mapped[0]?.id || null);

    setOpenFormModal(true);
  };

  const closeFormsModal = () => {
    setOpenFormModal(false);
    setSavingForm(false);
    setErr("");
  };

  const submitFormsModal = async (e) => {
    e.preventDefault();
    setErr("");

    const bErr = validateFormsBuilder();
    if (bErr) {
      setErr(bErr);
      return;
    }

    setSavingForm(true);
    try {
      const payload = buildFormPayload();

      if (formsMode === "create") {
        await apiPost("/admin/forms", {
          title: formTitle.trim(),
          status: formStatus,
          payload,
        });
        showToast("success", "✅ Formulario creado");
      } else {
        await apiPut(`/admin/forms/${editingFormId}`, {
          title: formTitle.trim(),
          status: formStatus,
          payload,
        });
        showToast("info", "✏️ Formulario actualizado");
      }

      closeFormsModal();
      await loadAdminForms();
    } catch (e2) {
      setErr(e2?.message || "Error guardando formulario");
    } finally {
      setSavingForm(false);
    }
  };

  const deleteForm = async (f) => {
    const ok = window.confirm(`¿Eliminar el formulario "${f.title}"?`);
    if (!ok) return;

    setErr("");
    setDeletingFormId(f.id);
    try {
      await apiDelete(`/admin/forms/${f.id}`);
      showToast("danger", "🗑️ Formulario eliminado");
      await loadAdminForms();
    } catch (e) {
      setErr(e?.message || "Error eliminando formulario");
    } finally {
      setDeletingFormId(null);
    }
  };

  const publishForm = async (f) => {
    setErr("");
    setPublishingFormId(f.id);
    try {
      await apiPost(`/admin/forms/${f.id}/publish`, {});
      showToast("success", "✅ Formulario publicado");
      await loadAdminForms();
    } catch (e) {
      setErr(e?.message || "Error publicando formulario");
    } finally {
      setPublishingFormId(null);
    }
  };

  const unpublishForm = async (f) => {
    setErr("");
    setUnpublishingFormId(f.id);
    try {
      await apiPost(`/admin/forms/${f.id}/unpublish`, {});
      showToast("info", "📤 Formulario despublicado");
      await loadAdminForms();
    } catch (e) {
      setErr(e?.message || "Error despublicando formulario");
    } finally {
      setUnpublishingFormId(null);
    }
  };

  // ---------- UI helpers ----------
  const Card = ({ children, style }) => (
    <div
      style={{
        background: "#fff",
        border: "1px solid #e4e4e7",
        borderRadius: 14,
        padding: 14,
        ...style,
      }}
    >
      {children}
    </div>
  );

  const Btn = ({ children, style, variant = "default", ...props }) => {
    const variants = {
      default: { border: "#e4e4e7", bg: "#fff", fg: "#0f172a" },
      primary: { border: "#c7d2fe", bg: "#eef2ff", fg: "#1e40af" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
    };
    const v = variants[variant] || variants.default;

    return (
      <button
        {...props}
        style={{
          borderRadius: 10,
          border: `1px solid ${v.border}`,
          background: v.bg,
          color: v.fg,
          padding: "10px 12px",
          cursor: props.disabled ? "not-allowed" : "pointer",
          fontWeight: 900,
          opacity: props.disabled ? 0.7 : 1,
          display: "inline-flex",
          alignItems: "center",
          gap: 8,
          ...style,
        }}
      >
        {children}
      </button>
    );
  };

  const IconBtn = ({ children, variant = "default", title, style, ...props }) => {
    const variants = {
      default: { border: "#e4e4e7", bg: "#fff", fg: "#0f172a" },
      primary: { border: "#c7d2fe", bg: "#eef2ff", fg: "#1e40af" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
    };
    const v = variants[variant] || variants.default;

    return (
      <button
        {...props}
        title={title}
        aria-label={title}
        style={{
          width: 38,
          height: 38,
          borderRadius: 10,
          border: `1px solid ${v.border}`,
          background: v.bg,
          color: v.fg,
          display: "grid",
          placeItems: "center",
          cursor: props.disabled ? "not-allowed" : "pointer",
          opacity: props.disabled ? 0.7 : 1,
          ...style,
        }}
      >
        {children}
      </button>
    );
  };

  const Badge = ({ children, variant = "default" }) => {
    const variants = {
      default: { border: "#e4e4e7", bg: "#f8fafc", fg: "#0f172a" },
      success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
      info: { border: "#93c5fd", bg: "#eff6ff", fg: "#1e40af" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
      warn: { border: "#fde68a", bg: "#fffbeb", fg: "#92400e" },
    };
    const v = variants[variant] || variants.default;

    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${v.border}`,
          background: v.bg,
          fontSize: 12,
          fontWeight: 900,
          color: v.fg,
          whiteSpace: "nowrap",
        }}
      >
        {children}
      </span>
    );
  };

  const toastStyle = (() => {
    if (!toast) return {};
    const map = {
      success: { bg: "#ecfdf5", border: "#86efac", fg: "#166534" },
      info: { bg: "#eff6ff", border: "#93c5fd", fg: "#1e40af" },
      danger: { bg: "#fef2f2", border: "#fecaca", fg: "#b91c1c" },
    };
    return map[toast.type] || map.info;
  })();

  const S = {
    toolbar: {
      display: "flex",
      justifyContent: "space-between",
      gap: 12,
      flexWrap: "wrap",
      alignItems: "flex-end",
    },
    inputsRow: {
      display: "flex",
      gap: 10,
      flexWrap: "wrap",
      alignItems: "flex-end",
    },
    label: { fontSize: 12, color: "#64748b", fontWeight: 900 },
    input: {
      padding: "10px 12px",
      borderRadius: 10,
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      minWidth: 220,
      outline: "none",
    },
    select: {
      padding: "10px 12px",
      borderRadius: 10,
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      outline: "none",
      minWidth: 92,
    },

    tableOuter: { display: "flex", justifyContent: "center" },
    tableWrap: { overflowX: "auto", width: "100%", maxWidth: 1100 },
    table: { borderCollapse: "separate", borderSpacing: 0, width: "100%", minWidth: 900 },
    th: {
      textAlign: "left",
      fontSize: 12,
      color: "#475569",
      padding: "12px 10px",
      borderBottom: "1px solid #e4e4e7",
      background: "#fff",
      position: "sticky",
      top: 0,
      zIndex: 1,
      whiteSpace: "nowrap",
    },
    td: {
      padding: "12px 10px",
      borderBottom: "1px solid #f1f5f9",
      verticalAlign: "middle",
      fontSize: 13,
      color: "#0f172a",
    },

    modalOverlay: {
      position: "fixed",
      inset: 0,
      background: "rgba(2,6,23,0.45)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      padding: 16,
      zIndex: 100,
    },
    modal: {
      width: "100%",
      maxWidth: 1200,
      background: "#fff",
      borderRadius: 16,
      border: "1px solid #e4e4e7",
      boxShadow: "0 20px 45px rgba(0,0,0,.18)",
      overflow: "hidden",
    },
    modalHeader: {
      padding: "14px 16px",
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 12,
    },
    modalTitle: { margin: 0, fontSize: 16 },
    modalBody: { padding: 16, display: "flex", flexDirection: "column", gap: 12 },
    modalFooter: {
      padding: 16,
      borderTop: "1px solid #e4e4e7",
      display: "flex",
      gap: 10,
      justifyContent: "flex-end",
      flexWrap: "wrap",
    },
    xBtn: {
      border: "1px solid #e4e4e7",
      background: "#fff",
      borderRadius: 10,
      width: 36,
      height: 36,
      display: "grid",
      placeItems: "center",
      cursor: "pointer",
      fontWeight: 900,
    },

    // ✅ Builder layout tipo Kizeo
    builderWrap: {
      display: "grid",
      gridTemplateColumns: "1fr 420px",
      gap: 14,
      alignItems: "start",
    },
    canvas: {
      border: "1px solid #e4e4e7",
      borderRadius: 14,
      background: "#fff",
      minHeight: 560,
      overflow: "hidden",
      display: "flex",
      flexDirection: "column",
    },
    canvasHeader: {
      padding: "10px 12px",
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      background: "#0b1a33",
      color: "#fff",
      fontWeight: 900,
    },
    canvasBody: {
      padding: 12,
      overflowY: "auto",
      maxHeight: 560,
      background: "#fff",
    },
    fieldCard: (active) => ({
      border: `1px solid ${active ? "#93c5fd" : "#e4e4e7"}`,
      background: active ? "#eff6ff" : "#fff",
      borderRadius: 14,
      padding: 12,
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      cursor: "pointer",
    }),
    fieldLeft: { display: "flex", gap: 10, alignItems: "center", minWidth: 0 },
    fieldTitle: { fontWeight: 900, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" },
    fieldMeta: { fontSize: 12, color: "#64748b", fontWeight: 900 },

    side: {
      border: "1px solid #e4e4e7",
      borderRadius: 14,
      background: "#fff",
      minHeight: 560,
      overflow: "hidden",
      display: "flex",
      flexDirection: "column",
    },
    sideHeader: {
      padding: "10px 12px",
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      background: "#fff",
      fontWeight: 900,
    },
    sideBody: { padding: 12, overflowY: "auto", maxHeight: 560, display: "flex", flexDirection: "column", gap: 12 },

    paletteGrid: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 },
    paletteBtn: {
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      borderRadius: 12,
      padding: "10px 10px",
      cursor: "pointer",
      display: "flex",
      gap: 10,
      alignItems: "center",
      fontWeight: 900,
    },

    inputFull: {
      width: "100%",
      padding: "10px 12px",
      borderRadius: 12,
      border: "1px solid #e4e4e7",
      background: "#fff",
      outline: "none",
    },
    helper: { fontSize: 12, color: "#64748b" },

    // ✅ Preview (split left/right)
    previewWrap: {
      display: "grid",
      gridTemplateColumns: "1.05fr 0.95fr",
      gap: 14,
      alignItems: "start",
    },
    previewLeft: {
      border: "1px solid #e4e4e7",
      borderRadius: 14,
      padding: 14,
      background: "#fff",
      minHeight: 520,
    },
    previewRight: {
      border: "1px solid #e4e4e7",
      borderRadius: 14,
      background: "#fff",
      overflow: "hidden",
      minHeight: 520,
      display: "flex",
      flexDirection: "column",
    },
    previewRightTop: {
      padding: "10px 12px",
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      background: "#0b1a33",
      color: "#fff",
      fontWeight: 900,
    },
    previewRightBody: {
      padding: 12,
      overflowY: "auto",
      maxHeight: 520,
      background: "#fff",
    },
    roInput: {
      width: "100%",
      padding: "10px 12px",
      borderRadius: 12,
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      outline: "none",
    },

    responsiveStyleTag: `
      @media (max-width: 980px) {
        .forms-toolbar-input { min-width: 100% !important; width: 100% !important; }
        .forms-preview-grid { grid-template-columns: 1fr !important; }
        .forms-builder-grid { grid-template-columns: 1fr !important; }
      }
    `,
  };

  const statusBadgeVariant = (status) => {
    if (status === "PUBLICADO") return "success";
    if (status === "BORRADOR") return "warn";
    if (status === "INACTIVO") return "danger";
    return "default";
  };

  // ✅ Render read-only del formulario (preview)
  const PreviewRenderer = ({ form }) => {
    const fields = Array.isArray(form?.payload?.fields) ? form.payload.fields : [];
    if (!fields.length) {
      return <div style={{ color: "#64748b", fontSize: 13 }}>Este formulario no tiene campos.</div>;
    }

    return (
      <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        {fields.map((f) => {
          const label = f?.label || "Campo";
          const type = f?.type || "text";
          const required = !!f?.required;

          if (type === "static_text") {
            return (
              <div
                key={f.id}
                style={{ padding: 12, border: "1px dashed #e4e4e7", borderRadius: 12, background: "#fff" }}
              >
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>{label}</div>
                <div style={{ color: "#334155", fontSize: 13 }}>{f.text || "—"}</div>
              </div>
            );
          }

          if (type === "separator") {
            return (
              <div key={f.id} style={{ padding: "6px 0" }}>
                <div style={{ borderTop: "2px solid #e4e4e7" }} />
              </div>
            );
          }

          if (type === "fixed_image") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>{label}</div>
                <div style={{ border: "1px solid #e4e4e7", borderRadius: 12, overflow: "hidden", background: "#f8fafc" }}>
                  <div style={{ padding: 10, color: "#64748b", fontSize: 12 }}>Imagen fija: {f.url ? f.url : "—"}</div>
                </div>
              </div>
            );
          }

          if (type === "fixed_file") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>{label}</div>
                <div style={{ padding: 10, border: "1px solid #e4e4e7", borderRadius: 12, background: "#f8fafc" }}>
                  Archivo fijo: {f.url ? f.url : "—"}
                </div>
              </div>
            );
          }

          if (type === "file" || type === "photo") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <input disabled type="file" style={S.roInput} />
                <div style={{ fontSize: 12, color: "#64748b" }}>accept: {f.accept || "—"}</div>
              </div>
            );
          }

          if (type === "radio") {
            const opts = Array.isArray(f?.options) ? f.options : [];
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                  {opts.map((o) => (
                    <label key={o} style={{ display: "flex", gap: 10, alignItems: "center", color: "#0f172a" }}>
                      <input disabled type="radio" />
                      {o}
                    </label>
                  ))}
                </div>
              </div>
            );
          }

          if (type === "contact") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <input disabled type="text" style={S.roInput} placeholder="Nombre" />
                <div style={{ height: 8 }} />
                <input disabled type="text" style={S.roInput} placeholder="Teléfono" />
                <div style={{ height: 8 }} />
                <input disabled type="email" style={S.roInput} placeholder="Correo" />
              </div>
            );
          }

          if (type === "address") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <input disabled type="text" style={S.roInput} placeholder="Calle y número" />
                <div style={{ height: 8 }} />
                <input disabled type="text" style={S.roInput} placeholder="Colonia / Ciudad" />
                <div style={{ height: 8 }} />
                <input disabled type="text" style={S.roInput} placeholder="Estado / CP" />
              </div>
            );
          }

          if (type === "signature") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <div style={{ height: 140, borderRadius: 12, border: "1px solid #e4e4e7", background: "#f8fafc" }} />
                <div style={{ fontSize: 12, color: "#64748b" }}>Área de firma (solo lectura)</div>
              </div>
            );
          }

          if (type === "table") {
            const cols = Array.isArray(f?.columns) ? f.columns : [];
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <div style={{ border: "1px solid #e4e4e7", borderRadius: 12, overflow: "hidden" }}>
                  <div style={{ display: "grid", gridTemplateColumns: `repeat(${Math.max(cols.length, 1)}, 1fr)` }}>
                    {(cols.length ? cols : ["Columna"]).map((c) => (
                      <div
                        key={c}
                        style={{
                          padding: 10,
                          fontWeight: 900,
                          background: "#f8fafc",
                          borderBottom: "1px solid #e4e4e7",
                        }}
                      >
                        {c}
                      </div>
                    ))}
                  </div>
                  <div style={{ padding: 10, color: "#64748b" }}>Tabla (solo lectura)</div>
                </div>
              </div>
            );
          }

          if (type === "datetime") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <input disabled type="datetime-local" style={S.roInput} />
              </div>
            );
          }

          if (type === "textarea") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <textarea disabled rows={3} style={S.roInput} placeholder="—" />
              </div>
            );
          }

          if (type === "select" || type === "list") {
            const opts = Array.isArray(f?.options) ? f.options : [];
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <select disabled style={S.roInput}>
                  <option value="">Selecciona…</option>
                  {opts.map((o) => (
                    <option key={o} value={o}>
                      {o}
                    </option>
                  ))}
                </select>
              </div>
            );
          }

          if (type === "checkbox") {
            return (
              <div key={f.id} style={{ display: "flex", gap: 10, alignItems: "center" }}>
                <input disabled type="checkbox" />
                <div style={{ fontSize: 13, fontWeight: 900 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
              </div>
            );
          }

          const htmlType =
            type === "number" ? "number" : type === "date" ? "date" : "text";

          return (
            <div key={f.id}>
              <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>
                {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
              </div>
              <input disabled type={htmlType} style={S.roInput} placeholder="—" />
            </div>
          );
        })}
      </div>
    );
  };

  // ✅ Render del canvas (builder) a partir de formFields
  const BuilderCanvasPreview = () => {
    if (!formFields.length) {
      return <div style={{ color: "#64748b", fontWeight: 900 }}>Agrega un campo desde la derecha.</div>;
    }

    return (
      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
        {formFields.map((f, idx) => {
          const active = f.id === selectedFieldId;
          const typeLabel = FIELD_TYPES.find((t) => t.value === f.type)?.label || f.type;

          return (
            <div
              key={f.id}
              style={S.fieldCard(active)}
              onClick={() => setSelectedFieldId(f.id)}
              role="button"
              tabIndex={0}
            >
              <div style={S.fieldLeft}>
                <div
                  style={{
                    width: 28,
                    height: 28,
                    borderRadius: 10,
                    border: "1px solid #e4e4e7",
                    display: "grid",
                    placeItems: "center",
                    background: "#fff",
                  }}
                >
                  <i className={TYPE_ICON[f.type] || "fa-regular fa-square"} />
                </div>

                <div style={{ minWidth: 0 }}>
                  <div style={S.fieldTitle}>
                    {idx + 1}. {String(f.label || "").trim() || "Sin etiqueta"}
                    {f.required ? <span style={{ color: "#dc2626" }}> *</span> : null}
                  </div>
                  <div style={S.fieldMeta}>{typeLabel}</div>
                </div>
              </div>

              <div style={{ display: "inline-flex", gap: 8, alignItems: "center" }}>
                <IconBtn
                  type="button"
                  title="Subir"
                  onClick={(e) => {
                    e.stopPropagation();
                    moveField(f.id, "up");
                  }}
                  disabled={idx === 0}
                >
                  <i className="fa-solid fa-arrow-up" />
                </IconBtn>

                <IconBtn
                  type="button"
                  title="Bajar"
                  onClick={(e) => {
                    e.stopPropagation();
                    moveField(f.id, "down");
                  }}
                  disabled={idx === formFields.length - 1}
                >
                  <i className="fa-solid fa-arrow-down" />
                </IconBtn>

                <IconBtn
                  type="button"
                  variant="danger"
                  title="Eliminar"
                  onClick={(e) => {
                    e.stopPropagation();
                    removeField(f.id);
                  }}
                  disabled={formFields.length <= 1}
                >
                  <i className="fa-solid fa-trash" />
                </IconBtn>
              </div>
            </div>
          );
        })}
      </div>
    );
  };

  const statusHint = formsMode === "create" ? "Nuevo" : `Editando #${editingFormId}`;

  return (
    <div>
      <style>{S.responsiveStyleTag}</style>

      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Formularios (Admin)</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Total: <b>{meta.total}</b>
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }} className="forms-toolbar-input">
              <div style={S.label}>Buscar</div>
              <input
                ref={searchRef}
                value={qDraft}
                onFocus={rememberFocus}
                onClick={rememberFocus}
                onBlur={() => (searchWasFocusedRef.current = false)}
                onChange={(e) => {
                  rememberFocus();
                  setQDraft(e.target.value);
                }}
                placeholder="Título"
                style={{ ...S.input, width: "100%" }}
                className="forms-toolbar-input"
              />
            </div>

            <div>
              <div style={S.label}>Por página</div>
              <select
                value={perPage}
                onChange={(e) => {
                  setPage(1);
                  setPerPage(Number(e.target.value));
                }}
                style={S.select}
              >
                <option value={10}>10</option>
                <option value={20}>20</option>
                <option value={30}>30</option>
                <option value={50}>50</option>
              </select>
            </div>

            <Btn variant="primary" onClick={openCreateFormModal}>
              <i className="fa-solid fa-plus" />
              Nuevo
            </Btn>

            <Btn type="button" onClick={loadAdminForms} disabled={loadingForms}>
              {loadingForms ? "Actualizando..." : "Refrescar"}
            </Btn>
          </div>
        </div>

        {toast ? (
          <div
            style={{
              marginTop: 12,
              padding: "10px 12px",
              borderRadius: 12,
              border: `1px solid ${toastStyle.border}`,
              background: toastStyle.bg,
              color: toastStyle.fg,
              fontWeight: 900,
            }}
          >
            {toast.text}
          </div>
        ) : null}

        {err ? <div style={{ marginTop: 10, color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
      </Card>

      <Card>
        {loadingForms ? (
          <div>Cargando formularios...</div>
        ) : (
          <div style={S.tableOuter}>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>ID</th>
                    <th style={S.th}>Título</th>
                    <th style={S.th}>Status</th>
                    <th style={S.th}>Fecha</th>
                    <th style={{ ...S.th, width: 200, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {forms.length ? (
                    forms.map((f) => (
                      <tr key={f.id}>
                        <td style={S.td}>{f.id}</td>
                        <td style={S.td}>
                          <div style={{ fontWeight: 900 }}>{f.title}</div>
                        </td>
                        <td style={S.td}>
                          <Badge variant={statusBadgeVariant(f.status)}>{f.status}</Badge>
                        </td>
                        <td style={{ ...S.td, fontSize: 12, color: "#334155" }}>{String(f.created_at || "")}</td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openPreviewModal(f)} title="Ver (solo vista)">
                              <i className="fa-solid fa-eye" />
                            </IconBtn>

                            <IconBtn onClick={() => openEditFormModal(f)} title="Editar">
                              <i className="fa-solid fa-pen" />
                            </IconBtn>

                            {f.status !== "PUBLICADO" ? (
                              <IconBtn
                                onClick={() => publishForm(f)}
                                disabled={publishingFormId === f.id}
                                variant="primary"
                                title="Publicar"
                              >
                                <i className="fa-solid fa-cloud-arrow-up" />
                              </IconBtn>
                            ) : (
                              <IconBtn
                                onClick={() => unpublishForm(f)}
                                disabled={unpublishingFormId === f.id}
                                title="Despublicar"
                              >
                                <i className="fa-solid fa-cloud-arrow-down" />
                              </IconBtn>
                            )}

                            <IconBtn
                              onClick={() => deleteForm(f)}
                              disabled={deletingFormId === f.id}
                              variant="danger"
                              title="Eliminar"
                            >
                              <i className="fa-solid fa-trash" />
                            </IconBtn>
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td style={S.td} colSpan={5}>
                        Sin formularios
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>

              <div style={{ marginTop: 8, fontSize: 12, color: "#64748b" }}>
                Tip: los usuarios normales solo verán formularios con status <b>PUBLICADO</b>.
              </div>
            </div>
          </div>
        )}

        <div
          style={{
            display: "flex",
            gap: 10,
            alignItems: "center",
            marginTop: 12,
            flexWrap: "wrap",
            justifyContent: "center",
          }}
        >
          <Btn
            disabled={!canPrev}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            style={{ padding: "8px 10px" }}
          >
            Anterior
          </Btn>
          <div style={{ fontSize: 12 }}>
            Página <b>{page}</b> de <b>{meta.last_page}</b>
          </div>
          <Btn disabled={!canNext} onClick={() => setPage((p) => p + 1)} style={{ padding: "8px 10px" }}>
            Siguiente
          </Btn>
        </div>
      </Card>

      {/* ✅ MODAL BUILDER (tipo Kizeo) */}
      {openFormModal && (
        <div style={S.modalOverlay} onClick={closeFormsModal}>
          <div style={{ ...S.modal }} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <div style={{ display: "flex", flexDirection: "column", gap: 2 }}>
                <h3 style={S.modalTitle}>{formsMode === "create" ? "Crear formulario" : `Editar formulario #${editingFormId}`}</h3>
                <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>{statusHint}</div>
              </div>

              <button type="button" style={S.xBtn} onClick={closeFormsModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitFormsModal}>
              <div style={S.modalBody}>
                {/* Header settings */}
                <div style={{ display: "grid", gridTemplateColumns: "1fr 220px", gap: 12 }}>
                  <div>
                    <div style={S.label}>Título</div>
                    <input
                      value={formTitle}
                      onChange={(e) => setFormTitle(e.target.value)}
                      style={S.inputFull}
                      placeholder="Ej. Checklist de herramienta"
                    />
                  </div>

                  <div>
                    <div style={S.label}>Status</div>
                    <select value={formStatus} onChange={(e) => setFormStatus(e.target.value)} style={S.inputFull}>
                      <option value="BORRADOR">BORRADOR</option>
                      <option value="PUBLICADO">PUBLICADO</option>
                      <option value="INACTIVO">INACTIVO</option>
                    </select>
                  </div>
                </div>

                {/* Builder main */}
                <div className="forms-builder-grid" style={S.builderWrap}>
                  {/* LEFT Canvas */}
                  <div style={S.canvas}>
                    <div style={S.canvasHeader}>
                      <div style={{ display: "flex", gap: 10, alignItems: "center", minWidth: 0 }}>
                        <i className="fa-solid fa-file-lines" />
                        <div style={{ whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                          {formTitle || "Formulario"}
                        </div>
                      </div>
                      <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
                        <Badge variant={statusBadgeVariant(formStatus)}>{formStatus}</Badge>
                      </div>
                    </div>
                    <div style={S.canvasBody}>
                      <BuilderCanvasPreview />
                    </div>
                  </div>

                  {/* RIGHT Sidebar */}
                  <div style={S.side}>
                    <div style={S.sideHeader}>
                      <div>Componentes</div>
                      <i className="fa-solid fa-sliders" />
                    </div>

                    <div style={S.sideBody}>
                      {/* Palette */}
                      <div>
                        <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900, marginBottom: 8 }}>
                          Agregar campo
                        </div>

                        <div style={S.paletteGrid}>
                          {FIELD_TYPES.map((t) => (
                            <button
                              key={t.value}
                              type="button"
                              style={S.paletteBtn}
                              onClick={() => {
                                addFieldOfType(t.value);
                                setTimeout(() => ensureSelected([...formFields]), 0);
                              }}
                            >
                              <i className={TYPE_ICON[t.value] || "fa-regular fa-square"} />
                              <span style={{ fontWeight: 900 }}>{t.label}</span>
                            </button>
                          ))}
                        </div>
                      </div>

                      {/* Selected field editor */}
                      <div style={{ borderTop: "1px solid #e4e4e7", paddingTop: 12 }}>
                        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: 10 }}>
                          <div style={{ fontWeight: 900 }}>Editar</div>
                          {selectedField ? (
                            <Badge variant="info">{FIELD_TYPES.find((x) => x.value === selectedField.type)?.label || selectedField.type}</Badge>
                          ) : (
                            <Badge>—</Badge>
                          )}
                        </div>

                        {!selectedField ? (
                          <div style={{ marginTop: 10, color: "#64748b", fontWeight: 900 }}>
                            Selecciona un campo del lado izquierdo.
                          </div>
                        ) : (
                          <div style={{ marginTop: 10, display: "flex", flexDirection: "column", gap: 10 }}>
                            <div>
                              <div style={S.label}>Nombre / Etiqueta</div>
                              <input
                                value={selectedField.label}
                                onChange={(e) => updateSelectedField({ label: e.target.value })}
                                style={S.inputFull}
                                placeholder="Ej. Nombre del inspector"
                              />
                            </div>

                            <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                              <label style={{ display: "inline-flex", gap: 8, alignItems: "center", fontWeight: 900 }}>
                                <input
                                  type="checkbox"
                                  checked={!!selectedField.required}
                                  onChange={(e) => updateSelectedField({ required: e.target.checked })}
                                />
                                Campo obligatorio
                              </label>
                            </div>

                            {/* Opciones */}
                            {selectedField.type === "select" || selectedField.type === "list" || selectedField.type === "radio" ? (
                              <div>
                                <div style={S.label}>Opciones (separadas por coma)</div>
                                <input
                                  value={selectedField.optionsText || ""}
                                  onChange={(e) => updateSelectedField({ optionsText: e.target.value })}
                                  style={S.inputFull}
                                  placeholder="Ej. TI, RH, Operaciones"
                                />
                                <div style={S.helper}>Mínimo 2 opciones.</div>
                              </div>
                            ) : null}

                            {/* Texto fijo */}
                            {selectedField.type === "static_text" ? (
                              <div>
                                <div style={S.label}>Contenido (texto fijo)</div>
                                <textarea
                                  rows={3}
                                  value={selectedField.staticText || ""}
                                  onChange={(e) => updateSelectedField({ staticText: e.target.value })}
                                  style={S.inputFull}
                                  placeholder="Texto informativo..."
                                />
                              </div>
                            ) : null}

                            {/* Imagen fija / archivo fijo */}
                            {selectedField.type === "fixed_image" || selectedField.type === "fixed_file" ? (
                              <div>
                                <div style={S.label}>URL del recurso (fijo)</div>
                                <input
                                  value={selectedField.fileUrl || ""}
                                  onChange={(e) => updateSelectedField({ fileUrl: e.target.value })}
                                  style={S.inputFull}
                                  placeholder="https://..."
                                />
                                <div style={S.helper}>
                                  {selectedField.type === "fixed_image"
                                    ? "Se mostrará como imagen (preview)."
                                    : "Se mostrará como link de descarga."}
                                </div>
                              </div>
                            ) : null}

                            {/* Archivo adjunto / foto */}
                            {selectedField.type === "file" || selectedField.type === "photo" ? (
                              <div>
                                <div style={S.label}>Tipos permitidos (accept)</div>
                                <input
                                  value={selectedField.accept || (selectedField.type === "photo" ? "image/*" : "")}
                                  onChange={(e) => updateSelectedField({ accept: e.target.value })}
                                  style={S.inputFull}
                                  placeholder={selectedField.type === "photo" ? "image/*" : ".pdf,.jpg,.png"}
                                />
                              </div>
                            ) : null}

                            {/* Tabla */}
                            {selectedField.type === "table" ? (
                              <div>
                                <div style={S.label}>Columnas (separadas por coma)</div>
                                <input
                                  value={selectedField.columnsText || ""}
                                  onChange={(e) => updateSelectedField({ columnsText: e.target.value })}
                                  style={S.inputFull}
                                  placeholder="Columna 1, Columna 2"
                                />
                              </div>
                            ) : null}

                            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                              <Btn type="button" onClick={() => moveField(selectedField.id, "up")} style={{ padding: "8px 10px" }}>
                                <i className="fa-solid fa-arrow-up" /> Subir
                              </Btn>
                              <Btn type="button" onClick={() => moveField(selectedField.id, "down")} style={{ padding: "8px 10px" }}>
                                <i className="fa-solid fa-arrow-down" /> Bajar
                              </Btn>
                              <Btn
                                type="button"
                                variant="danger"
                                onClick={() => removeField(selectedField.id)}
                                disabled={formFields.length <= 1}
                                style={{ padding: "8px 10px" }}
                                title={formFields.length <= 1 ? "Debe existir al menos 1 campo" : "Eliminar"}
                              >
                                <i className="fa-solid fa-trash" /> Eliminar
                              </Btn>
                            </div>

                            <div style={{ fontSize: 12, color: "#64748b" }}>
                              Esto se guardará en <code>payload.fields</code>.
                            </div>
                          </div>
                        )}
                      </div>

                      {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
                    </div>
                  </div>
                </div>
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closeFormsModal}>
                  Cancelar
                </Btn>
                <Btn type="submit" disabled={savingForm} variant="primary">
                  {savingForm ? "Guardando..." : "Guardar"}
                </Btn>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ✅ Modal Preview (👁) */}
      {openPreview && (
        <div style={S.modalOverlay} onClick={closePreviewModal}>
          <div style={{ ...S.modal, maxWidth: 1180 }} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>Vista del formulario {previewForm?.id ? `#${previewForm.id}` : ""}</h3>
              <button type="button" style={S.xBtn} onClick={closePreviewModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {!previewForm ? (
                <div style={{ color: "#64748b", fontWeight: 900 }}>Cargando vista…</div>
              ) : (
                <div className="forms-preview-grid" style={S.previewWrap}>
                  {/* LEFT: meta */}
                  <div style={S.previewLeft}>
                    <div style={{ display: "flex", justifyContent: "space-between", gap: 10, alignItems: "center" }}>
                      <div>
                        <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Formulario</div>
                        <div style={{ fontSize: 18, fontWeight: 900, marginTop: 2 }}>{previewForm.title || "—"}</div>
                      </div>
                      <Badge variant={statusBadgeVariant(previewForm.status)}>{previewForm.status || "—"}</Badge>
                    </div>

                    <div style={{ marginTop: 12, borderTop: "1px solid #e4e4e7", paddingTop: 12 }}>
                      <div style={{ display: "grid", gap: 10 }}>
                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Creado</div>
                          <div style={{ fontWeight: 900 }}>{formatDate(previewForm.created_at)}</div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Por</div>
                          <div style={{ fontWeight: 900 }}>{getCreatorName(previewForm)}</div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>ID</div>
                          <div style={{ fontWeight: 900 }}>{previewForm.id}</div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Campos</div>
                          <div style={{ fontWeight: 900 }}>
                            {Array.isArray(previewForm?.payload?.fields) ? previewForm.payload.fields.length : 0}
                          </div>
                        </div>
                      </div>

                      <div style={{ marginTop: 14, display: "flex", gap: 10, flexWrap: "wrap" }}>
                        <Btn type="button" onClick={closePreviewModal}>
                          <i className="fa-solid fa-arrow-left" /> Volver
                        </Btn>

                        <Btn
                          type="button"
                          variant="primary"
                          onClick={() => {
                            closePreviewModal();
                            openEditFormModal(previewForm);
                          }}
                        >
                          <i className="fa-solid fa-pen" /> Editar
                        </Btn>
                      </div>

                      <div style={{ marginTop: 14, fontSize: 12, color: "#64748b" }}>
                        Esta vista es <b>solo lectura</b>. No permite registrar.
                      </div>
                    </div>
                  </div>

                  {/* RIGHT: preview render */}
                  <div style={S.previewRight}>
                    <div style={S.previewRightTop}>
                      <div style={{ display: "flex", gap: 10, alignItems: "center", minWidth: 0 }}>
                        <i className="fa-solid fa-file-lines" />
                        <div style={{ whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                          {previewForm.title || "Formulario"}
                        </div>
                      </div>
                      <i className="fa-solid fa-ellipsis-vertical" />
                    </div>
                    <div style={S.previewRightBody}>
                      <PreviewRenderer form={previewForm} />
                    </div>
                  </div>
                </div>
              )}
            </div>

            <div style={S.modalFooter}>
              <Btn type="button" onClick={closePreviewModal}>
                Cerrar
              </Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}