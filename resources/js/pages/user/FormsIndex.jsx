import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet } from "../../services/api";
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
    clearSearch();
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
  };

  const goToResponses = async () => {
    if (!selectedId) {
      goToTable();
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

  const kickToLogin = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
  };

  const setAuthError = (e, fallback = "Error") => {
    const msg = String(e?.message || "");
    if (msg.includes("401") || msg.toLowerCase().includes("no autorizado")) {
      return kickToLogin();
    }
    if (msg.includes("403") || msg.toLowerCase().includes("forbidden")) {
      return kickToLogin();
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
      clearSearch();

      if (nextMode === "table") {
        setDetail(null);
        setSubs([]);
        return;
      }

      if (nextSelectedId) {
        await loadDetail(nextSelectedId);

        if (nextMode === "responses") {
          await loadSubmissions(nextSelectedId);
        }
      }
    };

    window.addEventListener("popstate", onPopState);
    return () => window.removeEventListener("popstate", onPopState);
  }, []);

  const onFill = async (id) => {
    pushFormsState("fill", { selectedId: id, selectedSub: null });

    setSelectedId(id);
    setSelectedSub(null);
    setMode("fill");
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
    setErr("");
    clearSearch();
    await loadDetail(id);
  };

  const onResponses = async (id) => {
    pushFormsState("responses", { selectedId: id, selectedSub: null });

    setSelectedId(id);
    setSelectedSub(null);
    setMode("responses");
    setMobileActions({ open: false, form: null });
    setMobileSubmissionActions({ open: false, submission: null });
    setErr("");
    clearSearch();
    await loadDetail(id);
    await loadSubmissions(id);
  };

  const onOpenSavedResponse = async (submission) => {
    pushFormsState("response_view", {
      selectedId,
      selectedSub: submission,
    });

    setSelectedSub(submission);
    setMode("response_view");
    setMobileSubmissionActions({ open: false, submission: null });
    setErr("");

    if (!detail && selectedId) {
      await loadDetail(selectedId);
    }
  };

  const onEditSavedResponse = async (submission) => {
    pushFormsState("response_edit", {
      selectedId,
      selectedSub: submission,
    });

    setSelectedSub(submission);
    setMode("response_edit");
    setMobileSubmissionActions({ open: false, submission: null });
    setErr("");

    if (!detail && selectedId) {
      await loadDetail(selectedId);
    }
  };

  const onOpenSubmissionPdf = async (submission) => {
    setMobileSubmissionActions({ open: false, submission: null });

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
        setErr("No tienes permisos para ver este PDF.");
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
          // ignore
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
          // ignore
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
          // ignore
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

  const filteredSubs = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return subs;

    return subs.filter((s) => {
      const allText = [
        String(s.id || ""),
        String(s.user_name || s.user?.name || s.user_id || ""),
        String(s.created_at || ""),
        JSON.stringify(s.answers || {}),
      ]
        .join(" ")
        .toLowerCase();

      return allText.includes(q);
    });
  }, [subs, search]);

  const formatDate = (value) => {
    if (!value) return "—";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);
    return d.toLocaleString();
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
      ans.taller ??
      ans.id_taller ??
      ans.taller_nombre ??
      ans.workshop ??
      "";

    const inspector =
      ans.nombre_inspector ??
      ans.inspector ??
      ans.inspector_name ??
      "";

    const parts = [];

    if (taller) parts.push(`Taller: ${taller}`);
    if (inspector) parts.push(`Inspector: ${inspector}`);

    return parts.join(" | ") || "—";
  };

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
                      ? "Buscar registro..."
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
                    ? "Buscar registro..."
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
                      <IconBtn
                        type="button"
                        title="Ver registros"
                        onClick={() => onResponses(f.id)}
                        variant="primary"
                      >
                        <i className="fa-solid fa-comments" />
                      </IconBtn>

                      <IconBtn
                        type="button"
                        title="Crear registro"
                        onClick={() => onFill(f.id)}
                        variant="success"
                      >
                        <i className="fa-solid fa-circle-plus" />
                      </IconBtn>
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
                    minWidth: 1020,
                  }}
                >
                  <thead>
                    <tr>
                      <th
                        style={{
                          textAlign: "left",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                        }}
                      >
                        ID
                      </th>
                      <th
                        style={{
                          textAlign: "left",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                        }}
                      >
                        Usuario
                      </th>
                      <th
                        style={{
                          textAlign: "left",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                        }}
                      >
                        Fecha
                      </th>
                      <th
                        style={{
                          textAlign: "left",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                        }}
                      >
                        Resumen
                      </th>
                      <th
                        style={{
                          textAlign: "right",
                          padding: "12px 10px",
                          borderBottom: "1px solid #e4e7eb",
                          fontSize: 12,
                          color: "#475569",
                          width: 180,
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
                          }}
                        >
                          Registro {s.id}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                          }}
                        >
                          {getSubmissionUserName(s)}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            fontSize: 12,
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
                          }}
                        >
                          {getSubmissionSummary(s)}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            textAlign: "right",
                          }}
                        >
                          <div
                            style={{
                              display: "inline-flex",
                              gap: 8,
                              flexWrap: "nowrap",
                            }}
                          >
                            <IconBtn
                              type="button"
                              title="Ver registro"
                              onClick={() => onOpenSavedResponse(s)}
                              variant="primary"
                            >
                              <i className="fa-solid fa-eye" />
                            </IconBtn>

                            <IconBtn
                              type="button"
                              title="Editar registro"
                              onClick={() => onEditSavedResponse(s)}
                              variant="success"
                            >
                              <i className="fa-solid fa-pen" />
                            </IconBtn>

                            <IconBtn
                              type="button"
                              title="Ver/Descargar PDF"
                              onClick={() => onOpenSubmissionPdf(s)}
                              variant="danger"
                            >
                              <PdfIcon size={18} />
                            </IconBtn>
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
              <Btn
                type="button"
                variant="success"
                onClick={() => onFill(mobileActions.form.id)}
                style={{ justifyContent: "center", width: "100%" }}
              >
                <i className="fa-solid fa-circle-plus" />
                Crear registro
              </Btn>

              <Btn
                type="button"
                variant="primary"
                onClick={() => onResponses(mobileActions.form.id)}
                style={{ justifyContent: "center", width: "100%" }}
              >
                <i className="fa-solid fa-comments" />
                Ver registros
              </Btn>

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
    </div>
  );
}