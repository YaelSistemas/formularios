import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiDelete, apiGet } from "../../services/api";
import FormFill from "./FormFill";

function Card({ children, style }) {
  return (
    <div
      style={{
        background: "#fff",
        border: "1px solid #e4e7eb",
        borderRadius: 14,
        padding: 14,
        ...style,
      }}
    >
      {children}
    </div>
  );
}

function Btn({ children, style, variant = "default", ...props }) {
  const variants = {
    default: { border: "#e4e7eb", bg: "#fff", fg: "#0f172a" },
    success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
    primary: { border: "#bfdbfe", bg: "#eff6ff", fg: "#1d4ed8" },
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
        fontWeight: 800,
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
}

function IconBtn({ children, title, style, variant = "default", ...props }) {
  const variants = {
    default: { border: "#e4e7eb", bg: "#fff", fg: "#0f172a" },
    success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
    primary: { border: "#bfdbfe", bg: "#eff6ff", fg: "#1d4ed8" },
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
}

function PdfIcon({ size = 18, color = "currentColor" }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      aria-hidden="true"
    >
      <path
        d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7l-5-5Z"
        stroke={color}
        strokeWidth="1.8"
        strokeLinejoin="round"
      />
      <path
        d="M14 2v5h5"
        stroke={color}
        strokeWidth="1.8"
        strokeLinejoin="round"
      />
      <text
        x="12"
        y="17"
        textAnchor="middle"
        fontSize="6"
        fontWeight="700"
        fill={color}
        fontFamily="Arial, sans-serif"
      >
        PDF
      </text>
    </svg>
  );
}

function MobileSearchBox({
  resetSignal = 0,
  onSearchChange,
  placeholder = "Buscar formulario...",
}) {
  const inputRef = useRef(null);
  const timerRef = useRef(null);

  useEffect(() => {
    if (inputRef.current) {
      inputRef.current.value = "";
    }
    onSearchChange("");

    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [resetSignal, onSearchChange]);

  const handleInput = (e) => {
    const value = e.currentTarget.value;

    if (timerRef.current) clearTimeout(timerRef.current);

    timerRef.current = setTimeout(() => {
      onSearchChange(value);
    }, 120);
  };

  return (
    <div
      style={{
        position: "relative",
        minWidth: "100%",
        flex: "1 1 100%",
      }}
    >
      <i
        className="fa-solid fa-magnifying-glass"
        style={{
          position: "absolute",
          left: 12,
          top: "50%",
          transform: "translateY(-50%)",
          color: "#64748b",
          fontSize: 14,
          pointerEvents: "none",
        }}
      />
      <input
        ref={inputRef}
        defaultValue=""
        onInput={handleInput}
        placeholder={placeholder}
        autoCorrect="off"
        autoCapitalize="none"
        spellCheck={false}
        style={{
          width: "100%",
          borderRadius: 10,
          border: "1px solid #e4e7eb",
          background: "#fff",
          padding: "10px 12px 10px 36px",
          outline: "none",
          fontSize: 14,
          color: "#0f172a",
        }}
      />
    </div>
  );
}

export default function FormsIndex() {
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");
  const [successMsg, setSuccessMsg] = useState("");

  const [forms, setForms] = useState([]);
  const [loadingForms, setLoadingForms] = useState(false);

  const [selectedId, setSelectedId] = useState(null);
  const [detail, setDetail] = useState(null);
  const [loadingDetail, setLoadingDetail] = useState(false);

  const [subs, setSubs] = useState([]);
  const [loadingSubs, setLoadingSubs] = useState(false);
  const [selectedSub, setSelectedSub] = useState(null);

  const [search, setSearch] = useState("");
  const [mobileSearchResetKey, setMobileSearchResetKey] = useState(0);
  const searchInputRef = useRef(null);

  const [mobileActions, setMobileActions] = useState({
    open: false,
    form: null,
  });

  const [mobileSubmissionActions, setMobileSubmissionActions] = useState({
    open: false,
    submission: null,
  });

  const [historyModal, setHistoryModal] = useState({
    open: false,
    submission: null,
  });

  const [historyLoading, setHistoryLoading] = useState(false);
  const [historyError, setHistoryError] = useState("");
  const [historyData, setHistoryData] = useState([]);

  const [isMobile, setIsMobile] = useState(() => {
    if (typeof window === "undefined") return false;
    return window.innerWidth < 1024;
  });

  const [mode, setMode] = useState("table");

  const [me, setMe] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const token = useMemo(() => localStorage.getItem("token"), []);

  const permissionSet = useMemo(() => {
    const raw = me?.permissions;

    if (!Array.isArray(raw)) return new Set();

    return new Set(
      raw
        .map((p) => (typeof p === "string" ? p : p?.name))
        .filter(Boolean)
    );
  }, [me]);

  const canCreateRecord = permissionSet.has("formularios.create");
  const canSubmitForm = permissionSet.has("formularios.submit");
  const canViewSubmissions = permissionSet.has("formularios.submissions.view");
  const canEditSubmission = permissionSet.has("formularios.edit");
  const canDeleteSubmission = permissionSet.has("formularios.delete");

  const isAdmin =
    !!me?.is_admin ||
    (Array.isArray(me?.roles) && me.roles.includes("Administrador"));

  const noPermissionMessage = (actionText) =>
    `No cuentas con los permisos necesarios para ${actionText}. Contacta a tu administrador o al equipo de Sistemas.`;

  const pushFormsState = (nextMode, extra = {}) => {
    const state = {
      page: "forms-index",
      mode: nextMode,
      selectedId: extra.selectedId ?? selectedId ?? null,
      selectedSub: extra.selectedSub ?? selectedSub ?? null,
    };

    window.history.pushState(state, "");
  };

  const clearSearch = () => {
    setSearch("");
    if (searchInputRef.current) {
      searchInputRef.current.value = "";
    }
    setMobileSearchResetKey((k) => k + 1);
  };

  const goToTable = () => {
    const nextState = {
      page: "forms-index",
      mode: "table",
      selectedId: null,
      selectedSub: null,
    };

    window.history.pushState(nextState, "");

    setMode("table");
    setSelectedId(null);
    setSelectedSub(null);
    setDetail(null);
    setSubs([]);
    setErr("");
    setSuccessMsg("");
    clearSearch();
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);
  };

  const goToResponses = async () => {
    if (!selectedId) {
      goToTable();
      return;
    }

    if (!canViewSubmissions) {
      setErr(noPermissionMessage("ver los registros de este formulario"));
      return;
    }

    const nextState = {
      page: "forms-index",
      mode: "responses",
      selectedId,
      selectedSub: null,
    };

    window.history.pushState(nextState, "");

    setMode("responses");
    setSelectedSub(null);
    setErr("");
    clearSearch();
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);

    if (!detail) {
      await loadDetail(selectedId);
    }

    await loadSubmissions(selectedId);
  };

  useEffect(() => {
    function onResize() {
      setIsMobile(window.innerWidth < 1024);
    }

    window.addEventListener("resize", onResize);
    return () => window.removeEventListener("resize", onResize);
  }, []);

  useEffect(() => {
    if (!successMsg) return;

    const t = setTimeout(() => {
      setSuccessMsg("");
    }, 3000);

    return () => clearTimeout(t);
  }, [successMsg]);

  const kickToLogin = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
  };

  const setAuthError = (e, fallback = "Error") => {
    const msg = String(e?.message || "").toLowerCase();

    if (msg.includes("401") || msg.includes("no autorizado")) {
      return kickToLogin();
    }

    if (msg.includes("403") || msg.includes("forbidden")) {
      setErr(
        "No cuentas con los permisos necesarios para realizar esta acción. Contacta a tu administrador o al equipo de Sistemas."
      );
      return;
    }

    setErr(e?.message || fallback);
  };

  const loadForms = async () => {
    setErr("");
    setLoadingForms(true);
    try {
      const data = await apiGet("/forms");
      setForms(Array.isArray(data?.forms) ? data.forms : []);
    } catch (e) {
      setAuthError(e, "Error cargando formularios");
    } finally {
      setLoadingForms(false);
    }
  };

  const loadDetail = async (id) => {
    setErr("");
    setLoadingDetail(true);
    setDetail(null);
    try {
      const data = await apiGet(`/forms/${id}`);
      setDetail(data?.form || null);
    } catch (e) {
      setAuthError(e, "Error cargando detalle");
    } finally {
      setLoadingDetail(false);
    }
  };

  const loadSubmissions = async (id) => {
    setErr("");
    setLoadingSubs(true);
    setSubs([]);
    try {
      const data = await apiGet(`/forms/${id}/submissions`);
      setSubs(Array.isArray(data?.submissions) ? data.submissions : []);
    } catch (e) {
      setAuthError(e, "Error cargando registros");
    } finally {
      setLoadingSubs(false);
    }
  };

  useEffect(() => {
    if (!token) {
      setLoading(false);
      return;
    }

    (async () => {
      setLoading(true);

      try {
        const u = JSON.parse(localStorage.getItem("user") || "null");
        setMe(u);
      } catch {
        setMe(null);
      }

      await loadForms();
      setLoading(false);
    })();
  }, []);

  useEffect(() => {
    const initialState = {
      page: "forms-index",
      mode: "table",
      selectedId: null,
      selectedSub: null,
    };

    if (
      !window.history.state?.page ||
      window.history.state.page !== "forms-index"
    ) {
      window.history.replaceState(initialState, "");
    }

    const onPopState = async (event) => {
      const state = event.state;

      if (!state || state.page !== "forms-index") {
        return;
      }

      const nextMode = state.mode || "table";
      const nextSelectedId = state.selectedId ?? null;
      const nextSelectedSub = state.selectedSub ?? null;

      setMode(nextMode);
      setSelectedId(nextSelectedId);
      setSelectedSub(nextSelectedSub);
      setErr("");
      setSuccessMsg("");
      clearSearch();
      setHistoryModal({ open: false, submission: null });
      setHistoryLoading(false);
      setHistoryError("");
      setHistoryData([]);

      if (nextMode === "table") {
        setDetail(null);
        setSubs([]);
        return;
      }

      if (nextSelectedId) {
        await loadDetail(nextSelectedId);

        if (nextMode === "responses" && canViewSubmissions) {
          await loadSubmissions(nextSelectedId);
        }
      }
    };

    window.addEventListener("popstate", onPopState);
    return () => window.removeEventListener("popstate", onPopState);
  }, [canViewSubmissions]);

  const onFill = async (id) => {
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);

    if (!canCreateRecord) {
      setErr(noPermissionMessage("crear un registro"));
      return;
    }

    if (!canSubmitForm) {
      setErr(noPermissionMessage("contestar y guardar este formulario"));
      return;
    }

    pushFormsState("fill", { selectedId: id, selectedSub: null });

    setSelectedId(id);
    setSelectedSub(null);
    setMode("fill");
    setErr("");
    setSuccessMsg("");
    clearSearch();
    await loadDetail(id);
  };

  const onResponses = async (id) => {
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);

    if (!canViewSubmissions) {
      setErr(noPermissionMessage("ver los registros de este formulario"));
      return;
    }

    pushFormsState("responses", { selectedId: id, selectedSub: null });

    setSelectedId(id);
    setSelectedSub(null);
    setMode("responses");
    setErr("");
    setSuccessMsg("");
    clearSearch();
    await loadDetail(id);
    await loadSubmissions(id);
  };

  const onOpenSavedResponse = async (submission) => {
    if (!canViewSubmissions) {
      setErr(noPermissionMessage("ver este registro"));
      return;
    }

    pushFormsState("response_view", {
      selectedId,
      selectedSub: submission,
    });

    setSelectedSub(submission);
    setMode("response_view");
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);
    setErr("");
    setSuccessMsg("");

    if (!detail && selectedId) {
      await loadDetail(selectedId);
    }
  };

  const onEditSavedResponse = async (submission) => {
    if (!canEditSubmission) {
      setErr(noPermissionMessage("editar este registro"));
      return;
    }

    pushFormsState("response_edit", {
      selectedId,
      selectedSub: submission,
    });

    setSelectedSub(submission);
    setMode("response_edit");
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);
    setErr("");
    setSuccessMsg("");

    if (!detail && selectedId) {
      await loadDetail(selectedId);
    }
  };

  const onDeleteSavedResponse = async (submission) => {
    setMobileSubmissionActions({ open: false, submission: null });
    setHistoryModal({ open: false, submission: null });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);

    if (!canDeleteSubmission) {
      setErr(noPermissionMessage("eliminar este registro"));
      return;
    }

    if (!selectedId || !submission?.id) {
      setErr("No se pudo identificar el registro a eliminar.");
      return;
    }

    const ok = window.confirm(
      `¿Seguro que deseas eliminar el registro ${submission.id}? Esta acción no se puede deshacer.`
    );

    if (!ok) return;

    try {
      setErr("");
      setSuccessMsg("");

      await apiDelete(`/forms/${selectedId}/submissions/${submission.id}`);

      if (selectedSub?.id === submission.id) {
        setSelectedSub(null);
      }

      await loadSubmissions(selectedId);
      setSuccessMsg(`El registro ${submission.id} se eliminó correctamente.`);
    } catch (e) {
      setAuthError(e, "Error eliminando registro");
    }
  };

  const onOpenSubmissionPdf = async (submission) => {
    setMobileSubmissionActions({ open: false, submission: null });

    if (!canViewSubmissions) {
      setErr(noPermissionMessage("ver o descargar el PDF de este registro"));
      return;
    }

    if (!selectedId || !submission?.id) {
      setErr("No se pudo generar el PDF del registro.");
      return;
    }

    const isStandalone =
      window.matchMedia?.("(display-mode: standalone)")?.matches ||
      window.navigator.standalone === true;

    const isTouchMobile =
      /Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(
        navigator.userAgent
      );

    const shouldUseSameTabOnMobile = isMobile || isTouchMobile || isStandalone;

    let pdfWindow = null;

    try {
      setErr("");
      setSuccessMsg("");

      const authToken = localStorage.getItem("token");
      if (!authToken) {
        kickToLogin();
        return;
      }

      if (!shouldUseSameTabOnMobile) {
        pdfWindow = window.open("about:blank", "_blank");
      }

      const response = await fetch(
        `/api/forms/${selectedId}/submissions/${submission.id}/pdf`,
        {
          method: "GET",
          headers: {
            Authorization: `Bearer ${authToken}`,
            Accept: "application/pdf",
          },
        }
      );

      if (response.status === 401) {
        if (pdfWindow) pdfWindow.close();
        kickToLogin();
        return;
      }

      if (response.status === 403) {
        if (pdfWindow) pdfWindow.close();
        setErr(
          "No cuentas con los permisos necesarios para ver o descargar este PDF. Contacta a tu administrador o al equipo de Sistemas."
        );
        return;
      }

      if (!response.ok) {
        if (pdfWindow) pdfWindow.close();

        let message = "No se pudo abrir el PDF.";
        try {
          const contentType = response.headers.get("content-type") || "";
          if (contentType.includes("application/json")) {
            const data = await response.json();
            message = data?.message || message;
          } else {
            const text = await response.text();
            if (text) message = text;
          }
        } catch {
          //
        }

        throw new Error(message);
      }

      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);

      if (shouldUseSameTabOnMobile) {
        window.location.href = blobUrl;
      } else if (pdfWindow && !pdfWindow.closed) {
        pdfWindow.location.replace(blobUrl);
      } else {
        window.open(blobUrl, "_blank");
      }

      setTimeout(() => {
        window.URL.revokeObjectURL(blobUrl);
      }, 60000);
    } catch (e) {
      if (pdfWindow && !pdfWindow.closed) {
        try {
          pdfWindow.close();
        } catch {
          //
        }
      }

      setErr(e?.message || "Error al abrir el PDF.");
    }
  };

  const handleDesktopSearchChange = (e) => {
    const value = e.target.value;
    setSearch(value);

    requestAnimationFrame(() => {
      if (searchInputRef.current) {
        searchInputRef.current.focus();
        const len = value.length;
        try {
          searchInputRef.current.setSelectionRange(len, len);
        } catch {
          //
        }
      }
    });
  };

  const filteredForms = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return forms;

    return forms.filter((f) => {
      const title = String(f?.title || "").toLowerCase();
      return title.includes(q);
    });
  }, [forms, search]);

  const formatDate = (value) => {
    if (!value) return "—";

    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);

    return d.toLocaleString("es-MX", {
      timeZone: "America/Mexico_City",
      year: "numeric",
      month: "numeric",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });
  };

  const getSubmissionUserName = (submission) => {
    return (
      submission?.user_name ||
      submission?.user?.name ||
      submission?.usuario ||
      submission?.name ||
      `Usuario ${submission?.user_id ?? "—"}`
    );
  };

  const getSubmissionSummary = (submission) => {
    const ans = submission?.answers || {};

    const taller =
      ans.taller ?? ans.id_taller ?? ans.taller_nombre ?? ans.workshop ?? "";

    const inspector =
      ans.nombre_inspector ?? ans.inspector ?? ans.inspector_name ?? "";

    const parts = [];

    if (taller) parts.push(`Taller: ${taller}`);
    if (inspector) parts.push(`Inspector: ${inspector}`);

    return parts.join(" | ") || "—";
  };

  const fieldLabelMap = {
    taller: "Taller",
    nombre_inspector: "Nombre del inspector",
    firma_inspector: "Firma del inspector",
    tabla_herramientas: "Tabla de herramientas",
    tipo_herramienta: "Tipo de herramienta",
    serie: "Serie",
    conexiones_electricas: "Conexiones eléctricas",
    interruptores: "Interruptores",
    condiciones_fisicas: "Condiciones físicas",
    mango_sujecion: "Mango de sujeción",
    aditamientos: "Aditamientos",
    prueba_funcionamiento: "Prueba de funcionamiento",
    acciones: "Acciones",
    observaciones: "Observaciones",
  };

  const getPrettyFieldLabel = (key) => {
    return fieldLabelMap[key] || key?.replaceAll("_", " ") || "Campo";
  };

  const renderHistoryValue = (value, fieldKey = "") => {
    if (
      value === null ||
      value === undefined ||
      value === "" ||
      (Array.isArray(value) && value.length === 0)
    ) {
      return <span style={{ color: "#64748b" }}>—</span>;
    }

    if (typeof value === "boolean") {
      return value ? "Sí" : "No";
    }

    if (typeof value === "string") {
      const isImagePath =
        fieldKey?.includes("firma") ||
        value.includes("forms/signatures/") ||
        value.match(/\.(png|jpg|jpeg|webp)$/i);

      if (isImagePath) {
        const src = value.startsWith("http")
          ? value
          : `/storage/${String(value).replace(/^\/+/, "")}`;

        return (
          <div style={{ display: "grid", gap: 8 }}>
            <img
              src={src}
              alt={getPrettyFieldLabel(fieldKey)}
              style={{
                maxWidth: "100%",
                width: 260,
                border: "1px solid #e4e7eb",
                borderRadius: 10,
                background: "#fff",
              }}
            />
            <div
              style={{
                fontSize: 12,
                color: "#64748b",
                wordBreak: "break-all",
              }}
            >
              {value}
            </div>
          </div>
        );
      }

      return value;
    }

    if (Array.isArray(value)) {
      return (
        <div style={{ display: "grid", gap: 10 }}>
          {value.map((row, rowIndex) => (
            <div
              key={rowIndex}
              style={{
                border: "1px solid #e4e7eb",
                borderRadius: 10,
                padding: 10,
                background: "#fff",
              }}
            >
              <div
                style={{
                  fontWeight: 800,
                  fontSize: 13,
                  color: "#0f172a",
                  marginBottom: 8,
                }}
              >
                Fila {rowIndex + 1}
              </div>

              {row && typeof row === "object" && !Array.isArray(row) ? (
                <div style={{ display: "grid", gap: 6 }}>
                  {Object.entries(row).map(([k, v]) => (
                    <div
                      key={k}
                      style={{
                        display: "grid",
                        gridTemplateColumns: "160px 1fr",
                        gap: 8,
                        alignItems: "start",
                      }}
                    >
                      <div
                        style={{
                          fontWeight: 700,
                          color: "#334155",
                          fontSize: 13,
                        }}
                      >
                        {getPrettyFieldLabel(k)}
                      </div>
                      <div
                        style={{
                          color: "#0f172a",
                          fontSize: 13,
                          wordBreak: "break-word",
                        }}
                      >
                        {renderHistoryValue(v, k)}
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{ fontSize: 13, color: "#334155" }}>
                  {String(row)}
                </div>
              )}
            </div>
          ))}
        </div>
      );
    }

    if (typeof value === "object") {
      return (
        <div style={{ display: "grid", gap: 6 }}>
          {Object.entries(value).map(([k, v]) => (
            <div
              key={k}
              style={{
                display: "grid",
                gridTemplateColumns: "160px 1fr",
                gap: 8,
                alignItems: "start",
              }}
            >
              <div
                style={{
                  fontWeight: 700,
                  color: "#334155",
                  fontSize: 13,
                }}
              >
                {getPrettyFieldLabel(k)}
              </div>
              <div
                style={{
                  color: "#0f172a",
                  fontSize: 13,
                  wordBreak: "break-word",
                }}
              >
                {renderHistoryValue(v, k)}
              </div>
            </div>
          ))}
        </div>
      );
    }

    return String(value);
  };

  const openHistoryModal = async (submission) => {
    if (!isAdmin) {
      return;
    }

    if (!selectedId || !submission?.id) {
      setErr("No se pudo identificar el historial del registro.");
      return;
    }

    try {
      setHistoryModal({
        open: true,
        submission,
      });
      setHistoryLoading(true);
      setHistoryError("");
      setHistoryData([]);

      const data = await apiGet(
        `/forms/${selectedId}/submissions/${submission.id}/history`
      );

      setHistoryData(Array.isArray(data?.history) ? data.history : []);
    } catch (e) {
      setHistoryError(e?.message || "Error cargando historial.");
    } finally {
      setHistoryLoading(false);
    }
  };

  const closeHistoryModal = () => {
    setHistoryModal({
      open: false,
      submission: null,
    });
    setHistoryLoading(false);
    setHistoryError("");
    setHistoryData([]);
  };

  const filteredSubs = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return subs;

    return subs.filter((s) => {
      const registroLabel = `registro ${s.id}`.toLowerCase();
      const registroId = String(s.id || "").toLowerCase();
      const userName = String(getSubmissionUserName(s)).toLowerCase();
      const summary = String(getSubmissionSummary(s)).toLowerCase();

      return (
        registroLabel.includes(q) ||
        registroId.includes(q) ||
        userName.includes(q) ||
        summary.includes(q)
      );
    });
  }, [subs, search]);

  const createdHistory = useMemo(() => {
    return historyData.find((item) => item.action === "created") || null;
  }, [historyData]);

  const updatedHistory = useMemo(() => {
    return historyData.filter((item) => item.action === "updated");
  }, [historyData]);

  if (loading) {
    return <div style={{ padding: 16 }}>Cargando formularios...</div>;
  }

  if (mode === "fill" && detail) {
    return <FormFill form={detail} onBack={goToTable} />;
  }

  if (mode === "response_view" && detail && selectedSub) {
    return (
      <FormFill
        form={detail}
        onBack={goToResponses}
        readOnly={true}
        initialAnswers={selectedSub.answers || {}}
        responseMeta={{
          id: selectedSub.id,
          user_id: selectedSub.user_id,
          created_at: selectedSub.created_at,
        }}
      />
    );
  }

  if (mode === "response_edit" && detail && selectedSub) {
    return (
      <FormFill
        form={detail}
        onBack={goToResponses}
        readOnly={false}
        initialAnswers={selectedSub.answers || {}}
        responseMeta={{
          id: selectedSub.id,
          user_id: selectedSub.user_id,
          created_at: selectedSub.created_at,
        }}
        editSubmissionId={selectedSub.id}
        isEditing={true}
        onSaved={goToResponses}
      />
    );
  }

  return (
    <div>
      <Card style={{ marginBottom: 14 }}>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            gap: 12,
            flexWrap: "wrap",
            alignItems: "center",
          }}
        >
          <div>
            <h2 style={{ margin: 0 }}>
              {mode === "responses" ? "Registros" : "Formularios"}
            </h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              {mode === "responses"
                ? `Registros del formulario${
                    detail?.title ? `: ${detail.title}` : ""
                  }`
                : "Selecciona un formulario para capturarlo o consultar sus registros."}
            </div>
          </div>

          <div
            style={{
              display: "flex",
              gap: 8,
              flexWrap: "wrap",
              alignItems: "center",
              width: "100%",
              maxWidth: isMobile ? "100%" : 420,
              marginLeft: "auto",
            }}
          >
            {mode !== "table" ? (
              <Btn
                type="button"
                onClick={() => {
                  if (mode === "responses") {
                    goToTable();
                  } else if (
                    mode === "response_view" ||
                    mode === "response_edit"
                  ) {
                    goToResponses();
                  } else if (mode === "fill") {
                    goToTable();
                  } else {
                    goToTable();
                  }
                }}
              >
                Volver
              </Btn>
            ) : null}

            {!isMobile ? (
              <div
                style={{
                  position: "relative",
                  minWidth: 240,
                  flex: "1 1 280px",
                }}
              >
                <i
                  className="fa-solid fa-magnifying-glass"
                  style={{
                    position: "absolute",
                    left: 12,
                    top: "50%",
                    transform: "translateY(-50%)",
                    color: "#64748b",
                    fontSize: 14,
                    pointerEvents: "none",
                  }}
                />
                <input
                  ref={searchInputRef}
                  value={search}
                  onChange={handleDesktopSearchChange}
                  placeholder={
                    mode === "responses"
                      ? "Buscar registro o usuario..."
                      : "Buscar formulario..."
                  }
                  style={{
                    width: "100%",
                    borderRadius: 10,
                    border: "1px solid #e4e7eb",
                    background: "#fff",
                    padding: "10px 12px 10px 36px",
                    outline: "none",
                    fontSize: 14,
                    color: "#0f172a",
                  }}
                />
              </div>
            ) : (
              <MobileSearchBox
                resetSignal={mobileSearchResetKey}
                onSearchChange={setSearch}
                placeholder={
                  mode === "responses"
                    ? "Buscar registro o usuario..."
                    : "Buscar formulario..."
                }
              />
            )}
          </div>
        </div>

        {err ? (
          <div style={{ marginTop: 10, color: "#b91c1c", fontWeight: 800 }}>
            {err}
          </div>
        ) : null}

        {successMsg ? (
          <div
            style={{
              marginTop: 10,
              padding: "10px 12px",
              borderRadius: 12,
              border: "1px solid #fecaca",
              background: "#fef2f2",
              color: "#b91c1c",
              fontWeight: 800,
              boxShadow: "0 4px 10px rgba(185, 28, 28, 0.08)",
            }}
          >
            {successMsg}
          </div>
        ) : null}
      </Card>

      {mode === "table" ? (
        isMobile ? (
          <div style={{ display: "grid", gap: 12 }}>
            {filteredForms.length ? (
              filteredForms.map((f) => (
                <Card
                  key={f.id}
                  style={{
                    padding: 0,
                    overflow: "hidden",
                    borderRadius: 16,
                    boxShadow: "0 6px 18px rgba(15,23,42,0.05)",
                  }}
                >
                  <button
                    type="button"
                    onClick={() => setMobileActions({ open: true, form: f })}
                    style={{
                      width: "100%",
                      border: "none",
                      background: "#fff",
                      padding: "16px 16px",
                      textAlign: "left",
                      cursor: "pointer",
                    }}
                  >
                    <div
                      style={{
                        display: "flex",
                        alignItems: "flex-start",
                        justifyContent: "space-between",
                        gap: 12,
                      }}
                    >
                      <div style={{ minWidth: 0 }}>
                        <div
                          style={{
                            fontWeight: 800,
                            fontSize: 15,
                            lineHeight: 1.45,
                            color: "#0f172a",
                            marginBottom: 6,
                          }}
                        >
                          {f.title}
                        </div>

                        <div style={{ fontSize: 12, color: "#64748b" }}>
                          Toca para ver opciones
                        </div>
                      </div>

                      <div
                        style={{
                          width: 34,
                          height: 34,
                          borderRadius: 999,
                          border: "1px solid #e4e7eb",
                          display: "grid",
                          placeItems: "center",
                          flex: "0 0 auto",
                          color: "#64748b",
                          background: "#fff",
                        }}
                      >
                        <i className="fa-solid fa-ellipsis-vertical" />
                      </div>
                    </div>
                  </button>
                </Card>
              ))
            ) : (
              <Card>
                <div style={{ padding: "4px 0", color: "#64748b" }}>
                  {search.trim()
                    ? "No se encontraron formularios con esa búsqueda."
                    : "Sin formularios."}
                </div>
              </Card>
            )}
          </div>
        ) : (
          <div style={{ display: "grid", gap: 12 }}>
            {filteredForms.length ? (
              filteredForms.map((f) => (
                <Card
                  key={f.id}
                  style={{
                    padding: 0,
                    overflow: "hidden",
                    borderRadius: 16,
                    boxShadow: "0 6px 18px rgba(15,23,42,0.05)",
                  }}
                >
                  <div
                    style={{
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "space-between",
                      gap: 16,
                      padding: "16px 18px",
                    }}
                  >
                    <div style={{ minWidth: 0 }}>
                      <div
                        style={{
                          fontWeight: 800,
                          fontSize: 16,
                          color: "#0f172a",
                          lineHeight: 1.45,
                        }}
                      >
                        {f.title}
                      </div>
                    </div>

                    <div
                      style={{
                        display: "inline-flex",
                        gap: 8,
                        flexWrap: "nowrap",
                        flex: "0 0 auto",
                      }}
                    >
                      {canViewSubmissions ? (
                        <IconBtn
                          type="button"
                          title="Ver registros"
                          onClick={() => onResponses(f.id)}
                          variant="primary"
                        >
                          <i className="fa-solid fa-comments" />
                        </IconBtn>
                      ) : null}

                      {canCreateRecord ? (
                        <IconBtn
                          type="button"
                          title="Crear registro"
                          onClick={() => onFill(f.id)}
                          variant="success"
                        >
                          <i className="fa-solid fa-circle-plus" />
                        </IconBtn>
                      ) : null}
                    </div>
                  </div>
                </Card>
              ))
            ) : (
              <Card>
                <div style={{ padding: "4px 0", color: "#64748b" }}>
                  {search.trim()
                    ? "No se encontraron formularios con esa búsqueda."
                    : "Sin formularios."}
                </div>
              </Card>
            )}
          </div>
        )
      ) : null}

      {mode === "responses" ? (
        isMobile ? (
          <div style={{ display: "grid", gap: 12 }}>
            {loadingDetail || loadingSubs ? (
              <Card>
                <div style={{ color: "#64748b" }}>Cargando registros...</div>
              </Card>
            ) : filteredSubs.length ? (
              filteredSubs.map((s) => (
                <Card
                  key={s.id}
                  style={{
                    borderRadius: 16,
                    boxShadow: "0 6px 18px rgba(15,23,42,0.05)",
                    padding: 0,
                    overflow: "hidden",
                  }}
                >
                  <button
                    type="button"
                    onClick={() =>
                      setMobileSubmissionActions({
                        open: true,
                        submission: s,
                      })
                    }
                    style={{
                      width: "100%",
                      border: "none",
                      background: "#fff",
                      textAlign: "left",
                      padding: "16px",
                      cursor: "pointer",
                    }}
                  >
                    <div style={{ display: "grid", gap: 8 }}>
                      <div
                        style={{
                          display: "flex",
                          justifyContent: "space-between",
                          gap: 10,
                          alignItems: "center",
                          flexWrap: "wrap",
                        }}
                      >
                        <div style={{ fontWeight: 800, color: "#0f172a" }}>
                          Registro {s.id}
                        </div>
                        <div style={{ fontSize: 12, color: "#64748b" }}>
                          {formatDate(s.created_at)}
                        </div>
                      </div>

                      <div style={{ fontSize: 13, color: "#334155" }}>
                        <b>Usuario:</b> {getSubmissionUserName(s)}
                      </div>

                      <div
                        style={{
                          fontSize: 13,
                          color: "#334155",
                          lineHeight: 1.5,
                        }}
                      >
                        <b>Resumen:</b> {getSubmissionSummary(s)}
                      </div>

                      {isAdmin ? (
                        <div>
                          <Btn
                            type="button"
                            variant="default"
                            onClick={(e) => {
                              e.stopPropagation();
                              openHistoryModal(s);
                            }}
                            style={{
                              justifyContent: "center",
                              width: "100%",
                              padding: "9px 12px",
                              fontSize: 13,
                            }}
                          >
                            <i className="fa-solid fa-clock-rotate-left" />
                            Ver historial
                          </Btn>
                        </div>
                      ) : null}
                    </div>
                  </button>
                </Card>
              ))
            ) : (
              <Card>
                <div style={{ color: "#64748b" }}>
                  {search.trim()
                    ? "No se encontraron registros con esa búsqueda."
                    : "Aún no hay registros para este formulario."}
                </div>
              </Card>
            )}
          </div>
        ) : (
          <Card>
            {loadingDetail || loadingSubs ? (
              <div style={{ color: "#64748b" }}>Cargando registros...</div>
            ) : filteredSubs.length ? (
              <div style={{ overflowX: "auto" }}>
                <table
                  style={{
                    borderCollapse: "collapse",
                    width: "100%",
                    minWidth: isAdmin ? 1200 : 1060,
                  }}
                >
                  <thead>
                    <tr>
                      <th
                        style={{
                          textAlign: "center",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          verticalAlign: "middle",
                        }}
                      >
                        Registro
                      </th>
                      <th
                        style={{
                          textAlign: "center",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          verticalAlign: "middle",
                        }}
                      >
                        Usuario
                      </th>
                      <th
                        style={{
                          textAlign: "center",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          verticalAlign: "middle",
                        }}
                      >
                        Fecha
                      </th>
                      <th
                        style={{
                          textAlign: "center",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          verticalAlign: "middle",
                        }}
                      >
                        Resumen
                      </th>

                      {isAdmin ? (
                        <th
                          style={{
                            textAlign: "center",
                            padding: "12px 10px",
                            borderBottom: "1px solid #e4e7eb",
                            fontSize: 12,
                            color: "#475569",
                            verticalAlign: "middle",
                            width: 130,
                          }}
                        >
                          Historial
                        </th>
                      ) : null}

                      <th
                        style={{
                          textAlign: "center",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          width: 230,
                          verticalAlign: "middle",
                        }}
                      >
                        Acciones
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {filteredSubs.map((s) => (
                      <tr key={s.id}>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            fontWeight: 700,
                            color: "#0f172a",
                            textAlign: "center",
                            verticalAlign: "middle",
                          }}
                        >
                          Registro {s.id}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            textAlign: "center",
                            verticalAlign: "middle",
                          }}
                        >
                          {getSubmissionUserName(s)}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            fontSize: 12,
                            textAlign: "center",
                            verticalAlign: "middle",
                          }}
                        >
                          {formatDate(s.created_at)}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            fontSize: 12,
                            color: "#334155",
                            textAlign: "center",
                            verticalAlign: "middle",
                          }}
                        >
                          {getSubmissionSummary(s)}
                        </td>

                        {isAdmin ? (
                          <td
                            style={{
                              padding: "12px 10px",
                              borderBottom: "1px solid #f1f5f9",
                              textAlign: "center",
                              verticalAlign: "middle",
                            }}
                          >
                            <Btn
                              type="button"
                              variant="default"
                              onClick={() => openHistoryModal(s)}
                              style={{
                                justifyContent: "center",
                                minWidth: 96,
                                padding: "8px 12px",
                                fontSize: 13,
                              }}
                            >
                              <i className="fa-solid fa-clock-rotate-left" />
                              Historial
                            </Btn>
                          </td>
                        ) : null}

                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            textAlign: "center",
                            verticalAlign: "middle",
                          }}
                        >
                          <div
                            style={{
                              display: "inline-flex",
                              gap: 8,
                              flexWrap: "nowrap",
                              justifyContent: "center",
                              alignItems: "center",
                            }}
                          >
                            {canViewSubmissions ? (
                              <IconBtn
                                type="button"
                                title="Ver registro"
                                onClick={() => onOpenSavedResponse(s)}
                                variant="primary"
                              >
                                <i className="fa-solid fa-eye" />
                              </IconBtn>
                            ) : null}

                            {canViewSubmissions ? (
                              <IconBtn
                                type="button"
                                title="Ver/Descargar PDF"
                                onClick={() => onOpenSubmissionPdf(s)}
                                variant="danger"
                              >
                                <PdfIcon size={18} />
                              </IconBtn>
                            ) : null}

                            {canEditSubmission ? (
                              <IconBtn
                                type="button"
                                title="Editar registro"
                                onClick={() => onEditSavedResponse(s)}
                                variant="success"
                              >
                                <i className="fa-solid fa-pen" />
                              </IconBtn>
                            ) : null}

                            {canDeleteSubmission ? (
                              <IconBtn
                                type="button"
                                title="Eliminar registro"
                                onClick={() => onDeleteSavedResponse(s)}
                                variant="danger"
                              >
                                <i className="fa-solid fa-trash" />
                              </IconBtn>
                            ) : null}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <div style={{ color: "#64748b" }}>
                {search.trim()
                  ? "No se encontraron registros con esa búsqueda."
                  : "Aún no hay registros para este formulario."}
              </div>
            )}
          </Card>
        )
      ) : null}

      {isMobile && mobileActions.open && mobileActions.form ? (
        <div
          onClick={() => setMobileActions({ open: false, form: null })}
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(15,23,42,0.45)",
            zIndex: 1000,
            display: "flex",
            alignItems: "flex-end",
            justifyContent: "center",
            padding: 12,
          }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: "100%",
              maxWidth: 420,
              background: "#fff",
              borderRadius: 18,
              border: "1px solid #e4e7eb",
              boxShadow: "0 20px 40px rgba(15,23,42,0.18)",
              overflow: "hidden",
            }}
          >
            <div
              style={{
                padding: "16px 16px 10px",
                borderBottom: "1px solid #f1f5f9",
              }}
            >
              <div
                style={{
                  fontWeight: 800,
                  fontSize: 16,
                  color: "#0f172a",
                  lineHeight: 1.4,
                }}
              >
                {mobileActions.form.title}
              </div>
              <div
                style={{
                  marginTop: 4,
                  fontSize: 12,
                  color: "#64748b",
                }}
              >
                Selecciona una acción
              </div>
            </div>

            <div style={{ padding: 14, display: "grid", gap: 10 }}>
              {canCreateRecord ? (
                <Btn
                  type="button"
                  variant="success"
                  onClick={() => onFill(mobileActions.form.id)}
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-circle-plus" />
                  Crear registro
                </Btn>
              ) : null}

              {canViewSubmissions ? (
                <Btn
                  type="button"
                  variant="primary"
                  onClick={() => onResponses(mobileActions.form.id)}
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-comments" />
                  Ver registros
                </Btn>
              ) : null}

              {!canCreateRecord && !canViewSubmissions ? (
                <div
                  style={{
                    fontSize: 13,
                    color: "#64748b",
                    textAlign: "center",
                    padding: "6px 8px",
                  }}
                >
                  No cuentas con acciones disponibles para este formulario.
                </div>
              ) : null}

              <Btn
                type="button"
                onClick={() => setMobileActions({ open: false, form: null })}
                style={{ justifyContent: "center", width: "100%" }}
              >
                Cancelar
              </Btn>
            </div>
          </div>
        </div>
      ) : null}

      {isMobile &&
      mobileSubmissionActions.open &&
      mobileSubmissionActions.submission ? (
        <div
          onClick={() =>
            setMobileSubmissionActions({ open: false, submission: null })
          }
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(15,23,42,0.45)",
            zIndex: 1100,
            display: "flex",
            alignItems: "flex-end",
            justifyContent: "center",
            padding: 12,
          }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: "100%",
              maxWidth: 420,
              background: "#fff",
              borderRadius: 18,
              border: "1px solid #e4e7eb",
              boxShadow: "0 20px 40px rgba(15,23,42,0.18)",
              overflow: "hidden",
            }}
          >
            <div
              style={{
                padding: "16px 16px 10px",
                borderBottom: "1px solid #f1f5f9",
              }}
            >
              <div
                style={{
                  fontWeight: 800,
                  fontSize: 16,
                  color: "#0f172a",
                  lineHeight: 1.4,
                }}
              >
                Registro {mobileSubmissionActions.submission.id}
              </div>
              <div
                style={{
                  marginTop: 4,
                  fontSize: 12,
                  color: "#64748b",
                }}
              >
                Selecciona una acción
              </div>
            </div>

            <div style={{ padding: 14, display: "grid", gap: 10 }}>
              {isAdmin ? (
                <Btn
                  type="button"
                  onClick={() => {
                    const submission = mobileSubmissionActions.submission;
                    setMobileSubmissionActions({
                      open: false,
                      submission: null,
                    });
                    openHistoryModal(submission);
                  }}
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-clock-rotate-left" />
                  Ver historial
                </Btn>
              ) : null}

              {canViewSubmissions ? (
                <Btn
                  type="button"
                  variant="primary"
                  onClick={() =>
                    onOpenSavedResponse(mobileSubmissionActions.submission)
                  }
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-eye" />
                  Ver registro
                </Btn>
              ) : null}

              {canViewSubmissions ? (
                <Btn
                  type="button"
                  variant="danger"
                  onClick={() =>
                    onOpenSubmissionPdf(mobileSubmissionActions.submission)
                  }
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <PdfIcon size={20} />
                  Ver / Descargar PDF
                </Btn>
              ) : null}

              {canEditSubmission ? (
                <Btn
                  type="button"
                  variant="success"
                  onClick={() =>
                    onEditSavedResponse(mobileSubmissionActions.submission)
                  }
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-pen" />
                  Editar registro
                </Btn>
              ) : null}

              {canDeleteSubmission ? (
                <Btn
                  type="button"
                  variant="danger"
                  onClick={() =>
                    onDeleteSavedResponse(mobileSubmissionActions.submission)
                  }
                  style={{ justifyContent: "center", width: "100%" }}
                >
                  <i className="fa-solid fa-trash" />
                  Eliminar registro
                </Btn>
              ) : null}

              {!canViewSubmissions &&
              !canEditSubmission &&
              !canDeleteSubmission ? (
                <div
                  style={{
                    fontSize: 13,
                    color: "#64748b",
                    textAlign: "center",
                    padding: "6px 8px",
                  }}
                >
                  No cuentas con acciones disponibles para este registro.
                </div>
              ) : null}

              <Btn
                type="button"
                onClick={() =>
                  setMobileSubmissionActions({ open: false, submission: null })
                }
                style={{ justifyContent: "center", width: "100%" }}
              >
                Cancelar
              </Btn>
            </div>
          </div>
        </div>
      ) : null}

      {historyModal.open && historyModal.submission ? (
        <div
          onClick={closeHistoryModal}
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(15,23,42,0.45)",
            zIndex: 1200,
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
          }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: "100%",
              maxWidth: 900,
              maxHeight: "90vh",
              overflowY: "auto",
              background: "#fff",
              borderRadius: 18,
              border: "1px solid #e4e7eb",
              boxShadow: "0 20px 40px rgba(15,23,42,0.18)",
            }}
          >
            <div
              style={{
                padding: "18px 18px 12px",
                borderBottom: "1px solid #f1f5f9",
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                gap: 12,
                flexWrap: "wrap",
              }}
            >
              <div>
                <div
                  style={{
                    fontWeight: 800,
                    fontSize: 18,
                    color: "#0f172a",
                    lineHeight: 1.3,
                  }}
                >
                  Historial del registro {historyModal.submission.id}
                </div>
                <div
                  style={{
                    marginTop: 4,
                    fontSize: 12,
                    color: "#64748b",
                  }}
                >
                  Creación y cambios realizados sobre el formulario
                </div>
              </div>

              <IconBtn type="button" title="Cerrar" onClick={closeHistoryModal}>
                <i className="fa-solid fa-xmark" />
              </IconBtn>
            </div>

            <div
              style={{
                padding: 18,
                display: "grid",
                gap: 16,
              }}
            >
              {historyLoading ? (
                <div style={{ color: "#64748b" }}>Cargando historial...</div>
              ) : historyError ? (
                <div
                  style={{
                    border: "1px solid #fecaca",
                    background: "#fef2f2",
                    color: "#b91c1c",
                    borderRadius: 12,
                    padding: 12,
                    fontWeight: 700,
                  }}
                >
                  {historyError}
                </div>
              ) : (
                <>
                  <div
                    style={{
                      border: "1px solid #e4e7eb",
                      borderRadius: 14,
                      padding: 14,
                      background: "#f8fafc",
                    }}
                  >
                    <div
                      style={{
                        fontSize: 15,
                        fontWeight: 800,
                        color: "#0f172a",
                        marginBottom: 10,
                      }}
                    >
                      Creación
                    </div>

                    {createdHistory ? (
                      <div style={{ display: "grid", gap: 12 }}>
                        <div
                          style={{
                            display: "grid",
                            gridTemplateColumns: isMobile ? "1fr" : "180px 1fr",
                            gap: 8,
                          }}
                        >
                          <div style={{ fontWeight: 700, color: "#334155" }}>
                            Usuario que creó
                          </div>
                          <div style={{ color: "#0f172a" }}>
                            {createdHistory.user_name || "—"}
                          </div>
                        </div>

                        <div
                          style={{
                            display: "grid",
                            gridTemplateColumns: isMobile ? "1fr" : "180px 1fr",
                            gap: 8,
                          }}
                        >
                          <div style={{ fontWeight: 700, color: "#334155" }}>
                            Fecha de creación
                          </div>
                          <div style={{ color: "#0f172a" }}>
                            {formatDate(createdHistory.created_at)}
                          </div>
                        </div>

                        <div
                          style={{
                            marginTop: 4,
                            borderTop: "1px solid #e2e8f0",
                            paddingTop: 12,
                            display: "grid",
                            gap: 12,
                          }}
                        >
                          <div
                            style={{
                              fontWeight: 800,
                              fontSize: 14,
                              color: "#0f172a",
                            }}
                          >
                            Datos capturados al crear
                          </div>

                          {createdHistory.snapshot &&
                          typeof createdHistory.snapshot === "object" ? (
                            Object.entries(createdHistory.snapshot).map(
                              ([key, value]) => (
                                <div
                                  key={key}
                                  style={{
                                    display: "grid",
                                    gridTemplateColumns: isMobile
                                      ? "1fr"
                                      : "180px 1fr",
                                    gap: 8,
                                    alignItems: "start",
                                  }}
                                >
                                  <div
                                    style={{
                                      fontWeight: 700,
                                      color: "#334155",
                                      fontSize: 13,
                                    }}
                                  >
                                    {getPrettyFieldLabel(key)}
                                  </div>
                                  <div
                                    style={{
                                      color: "#0f172a",
                                      fontSize: 13,
                                      wordBreak: "break-word",
                                    }}
                                  >
                                    {renderHistoryValue(value, key)}
                                  </div>
                                </div>
                              )
                            )
                          ) : (
                            <div style={{ color: "#64748b" }}>
                              No hay snapshot de creación.
                            </div>
                          )}
                        </div>
                      </div>
                    ) : (
                      <div style={{ color: "#64748b" }}>
                        No se encontró la creación del registro.
                      </div>
                    )}
                  </div>

                  <div
                    style={{
                      border: "1px solid #e4e7eb",
                      borderRadius: 14,
                      padding: 14,
                      background: "#f8fafc",
                    }}
                  >
                    <div
                      style={{
                        fontSize: 15,
                        fontWeight: 800,
                        color: "#0f172a",
                        marginBottom: 10,
                      }}
                    >
                      Actualizaciones
                    </div>

                    {updatedHistory.length ? (
                      <div style={{ display: "grid", gap: 14 }}>
                        {updatedHistory.map((item) => (
                          <div
                            key={item.id}
                            style={{
                              border: "1px solid #dbeafe",
                              background: "#fff",
                              borderRadius: 12,
                              padding: 12,
                              display: "grid",
                              gap: 10,
                            }}
                          >
                            <div
                              style={{
                                display: "grid",
                                gridTemplateColumns: isMobile
                                  ? "1fr"
                                  : "180px 1fr",
                                gap: 8,
                              }}
                            >
                              <div
                                style={{ fontWeight: 700, color: "#334155" }}
                              >
                                Usuario que editó
                              </div>
                              <div style={{ color: "#0f172a" }}>
                                {item.user_name || "—"}
                              </div>
                            </div>

                            <div
                              style={{
                                display: "grid",
                                gridTemplateColumns: isMobile
                                  ? "1fr"
                                  : "180px 1fr",
                                gap: 8,
                              }}
                            >
                              <div
                                style={{ fontWeight: 700, color: "#334155" }}
                              >
                                Fecha de edición
                              </div>
                              <div style={{ color: "#0f172a" }}>
                                {formatDate(item.created_at)}
                              </div>
                            </div>

                            <div
                              style={{
                                marginTop: 4,
                                borderTop: "1px solid #e2e8f0",
                                paddingTop: 12,
                                display: "grid",
                                gap: 12,
                              }}
                            >
                              <div
                                style={{
                                  fontWeight: 800,
                                  fontSize: 14,
                                  color: "#0f172a",
                                }}
                              >
                                Cambios realizados
                              </div>

                              {Array.isArray(item.changes) && item.changes.length ? (
                                item.changes.map((change, idx) => (
                                  <div
                                    key={`${item.id}-${idx}`}
                                    style={{
                                      border: "1px solid #e5e7eb",
                                      borderRadius: 10,
                                      padding: 10,
                                      background: "#f8fafc",
                                      display: "grid",
                                      gap: 10,
                                    }}
                                  >
                                    <div
                                      style={{
                                        fontWeight: 800,
                                        color: "#0f172a",
                                        fontSize: 13,
                                      }}
                                    >
                                      {change.label ||
                                        getPrettyFieldLabel(change.field)}
                                    </div>

                                    <div
                                      style={{
                                        display: "grid",
                                        gridTemplateColumns: isMobile
                                          ? "1fr"
                                          : "140px 1fr",
                                        gap: 8,
                                        alignItems: "start",
                                      }}
                                    >
                                      <div
                                        style={{
                                          fontWeight: 700,
                                          color: "#334155",
                                          fontSize: 13,
                                        }}
                                      >
                                        Valor anterior
                                      </div>
                                      <div
                                        style={{
                                          color: "#0f172a",
                                          fontSize: 13,
                                        }}
                                      >
                                        {renderHistoryValue(
                                          change.old_value,
                                          change.field
                                        )}
                                      </div>
                                    </div>

                                    <div
                                      style={{
                                        display: "grid",
                                        gridTemplateColumns: isMobile
                                          ? "1fr"
                                          : "140px 1fr",
                                        gap: 8,
                                        alignItems: "start",
                                      }}
                                    >
                                      <div
                                        style={{
                                          fontWeight: 700,
                                          color: "#334155",
                                          fontSize: 13,
                                        }}
                                      >
                                        Valor nuevo
                                      </div>
                                      <div
                                        style={{
                                          color: "#0f172a",
                                          fontSize: 13,
                                        }}
                                      >
                                        {renderHistoryValue(
                                          change.new_value,
                                          change.field
                                        )}
                                      </div>
                                    </div>
                                  </div>
                                ))
                              ) : (
                                <div style={{ color: "#64748b" }}>
                                  No se detectaron cambios detallados.
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div style={{ color: "#64748b" }}>
                        Este registro todavía no tiene actualizaciones.
                      </div>
                    )}
                  </div>
                </>
              )}
            </div>

            <div
              style={{
                padding: "0 18px 18px",
                display: "flex",
                justifyContent: "flex-end",
              }}
            >
              <Btn type="button" onClick={closeHistoryModal}>
                Cerrar
              </Btn>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}