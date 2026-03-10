import React, { useEffect, useMemo, useState } from "react";
import { apiGet } from "../../services/api";
import FormFill from "./FormFill";

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

  // mode: table | preview | responses | fill | response_view
  const [mode, setMode] = useState("table");

  const [me, setMe] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const token = useMemo(() => localStorage.getItem("token"), []);

  const roles = Array.isArray(me?.roles) ? me.roles : [];
  const permissions = Array.isArray(me?.permissions) ? me.permissions : [];

  const isAdmin =
    !!me?.is_admin ||
    roles.includes("Administrador") ||
    roles.includes("admin");

  const canViewResponses =
    isAdmin ||
    permissions.includes("formularios.submissions.view") ||
    permissions.includes("formularios.view.submissions") ||
    permissions.includes("forms.submissions.view");

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
      setAuthError(e, "Error cargando respuestas");
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onView = async (id) => {
    setSelectedId(id);
    setSelectedSub(null);
    setMode("preview");
    await loadDetail(id);
  };

  const onFill = async (id) => {
    setSelectedId(id);
    setSelectedSub(null);
    setMode("fill");
    await loadDetail(id);
  };

  const onResponses = async (id) => {
    setSelectedId(id);
    setSelectedSub(null);
    setMode("responses");
    await loadDetail(id);
    await loadSubmissions(id);
  };

  const onOpenSavedResponse = (submission) => {
    setSelectedSub(submission);
    setMode("response_view");
  };

  const formatDate = (value) => {
    if (!value) return "—";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);
    return d.toLocaleString();
  };

  const Card = ({ children, style }) => (
    <div
      style={{
        background: "#fff",
        border: "1px solid #e4e4e7",
        borderRadius: 12,
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
      success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
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
  };

  const IconBtn = ({ children, title, style, variant = "default", ...props }) => {
    const variants = {
      default: { border: "#e4e4e7", bg: "#fff", fg: "#0f172a" },
      primary: { border: "#c7d2fe", bg: "#eef2ff", fg: "#1e40af" },
      success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
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

  if (loading) {
    return <div style={{ padding: 16 }}>Cargando formularios...</div>;
  }

  if (mode === "fill" && detail) {
    return (
      <FormFill
        form={detail}
        onBack={() => setMode("table")}
      />
    );
  }

  if (mode === "preview" && detail) {
    return (
      <FormFill
        form={detail}
        onBack={() => setMode("table")}
        readOnly={true}
        initialAnswers={{}}
        responseMeta={{
          id: null,
          user_id: null,
          created_at: detail?.created_at || null,
          preview_mode: true,
        }}
      />
    );
  }

  if (mode === "response_view" && detail && selectedSub) {
    return (
      <FormFill
        form={detail}
        onBack={() => setMode("responses")}
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

  return (
    <div>
      <Card style={{ marginBottom: 14 }}>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            gap: 10,
            flexWrap: "wrap",
            alignItems: "center",
          }}
        >
          <div>
            <h2 style={{ margin: 0 }}>Formularios</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Lista de formularios disponibles.
            </div>
          </div>

          <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
            {mode !== "table" ? (
              <Btn
                type="button"
                onClick={() => {
                  setMode("table");
                  setSelectedSub(null);
                }}
              >
                Volver a tabla
              </Btn>
            ) : null}

            <Btn type="button" onClick={loadForms} disabled={loadingForms}>
              {loadingForms ? "Actualizando..." : "Refrescar"}
            </Btn>
          </div>
        </div>

        {err ? (
          <div style={{ marginTop: 10, color: "#b91c1c", fontWeight: 800 }}>
            {err}
          </div>
        ) : null}
      </Card>

      {mode === "table" ? (
        <Card>
          <div style={{ overflowX: "auto" }}>
            <table
              style={{
                borderCollapse: "collapse",
                width: "100%",
                minWidth: 700,
              }}
            >
              <thead>
                <tr>
                  <th
                    style={{
                      textAlign: "left",
                      padding: "12px 10px",
                      borderBottom: "1px solid #e4e4e7",
                      fontSize: 12,
                      color: "#475569",
                    }}
                  >
                    Título
                  </th>
                  <th
                    style={{
                      textAlign: "left",
                      padding: "12px 10px",
                      borderBottom: "1px solid #e4e4e7",
                      fontSize: 12,
                      color: "#475569",
                      width: 220,
                    }}
                  >
                    Fecha
                  </th>
                  <th
                    style={{
                      textAlign: "right",
                      padding: "12px 10px",
                      borderBottom: "1px solid #e4e4e7",
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
                {forms.length ? (
                  forms.map((f) => (
                    <tr key={f.id}>
                      <td
                        style={{
                          padding: "12px 10px",
                          borderBottom: "1px solid #f1f5f9",
                          fontWeight: 700,
                        }}
                      >
                        {f.title}
                      </td>
                      <td
                        style={{
                          padding: "12px 10px",
                          borderBottom: "1px solid #f1f5f9",
                          fontSize: 12,
                          color: "#334155",
                        }}
                      >
                        {formatDate(f.created_at)}
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
                            title="Ver"
                            onClick={() => onView(f.id)}
                          >
                            <i className="fa-solid fa-eye"></i>
                          </IconBtn>

                          {canViewResponses ? (
                            <IconBtn
                              type="button"
                              title="Respuestas"
                              onClick={() => onResponses(f.id)}
                              variant="primary"
                            >
                              <i className="fa-solid fa-comments"></i>
                            </IconBtn>
                          ) : null}

                          <IconBtn
                            type="button"
                            title="Llenar formulario"
                            onClick={() => onFill(f.id)}
                            variant="success"
                          >
                            <i className="fa-solid fa-circle-plus"></i>
                          </IconBtn>
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td
                      colSpan={3}
                      style={{
                        padding: "14px 10px",
                        color: "#64748b",
                      }}
                    >
                      Sin formularios.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </Card>
      ) : null}

      {mode === "responses" ? (
        <Card>
          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              gap: 10,
              flexWrap: "wrap",
              alignItems: "center",
            }}
          >
            <h3 style={{ margin: 0 }}>
              Respuestas {detail?.title ? `- ${detail.title}` : ""}
            </h3>

            <Btn
              type="button"
              onClick={() => selectedId && onResponses(selectedId)}
              disabled={loadingSubs}
            >
              {loadingSubs ? "Actualizando..." : "Refrescar respuestas"}
            </Btn>
          </div>

          {loadingDetail || loadingSubs ? (
            <div style={{ marginTop: 12 }}>Cargando respuestas...</div>
          ) : subs.length ? (
            <div style={{ marginTop: 12, overflowX: "auto" }}>
              <table
                style={{
                  borderCollapse: "collapse",
                  width: "100%",
                  minWidth: 700,
                }}
              >
                <thead>
                  <tr>
                    <th
                      style={{
                        textAlign: "left",
                        padding: "12px 10px",
                        borderBottom: "1px solid #e4e4e7",
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
                        borderBottom: "1px solid #e4e4e7",
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
                        borderBottom: "1px solid #e4e4e7",
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
                        borderBottom: "1px solid #e4e4e7",
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
                        borderBottom: "1px solid #e4e4e7",
                        fontSize: 12,
                        color: "#475569",
                        width: 90,
                      }}
                    >
                      Ver
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {subs.map((s) => {
                    const ans = s.answers || {};
                    const idToLabel = new Map(
                      (detail?.payload?.fields || []).map((f) => [f.id, f.label])
                    );

                    const keys = Object.keys(ans);
                    const preview = keys
                      .slice(0, 3)
                      .map((k) => {
                        const label = idToLabel.get(k) || k;
                        const value = ans[k];
                        if (typeof value === "boolean") {
                          return `${label}: ${value ? "Sí" : "No"}`;
                        }
                        return `${label}: ${String(value)}`;
                      })
                      .join(" | ");

                    return (
                      <tr key={s.id}>
                        <td style={{ padding: "12px 10px", borderBottom: "1px solid #f1f5f9" }}>
                          {s.id}
                        </td>
                        <td style={{ padding: "12px 10px", borderBottom: "1px solid #f1f5f9" }}>
                          {s.user_id}
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
                          }}
                        >
                          {preview || "—"}
                        </td>
                        <td
                          style={{
                            padding: "12px 10px",
                            borderBottom: "1px solid #f1f5f9",
                            textAlign: "right",
                          }}
                        >
                          <IconBtn
                            type="button"
                            title="Ver respuesta"
                            onClick={() => onOpenSavedResponse(s)}
                            variant="primary"
                          >
                            <i className="fa-solid fa-eye"></i>
                          </IconBtn>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : (
            <div style={{ marginTop: 12, color: "#64748b" }}>
              Aún no hay respuestas para este formulario.
            </div>
          )}
        </Card>
      ) : null}
    </div>
  );
}