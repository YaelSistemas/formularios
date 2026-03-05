// resources/js/pages/user/FormsIndex.jsx
import React, { useEffect, useMemo, useState } from "react";
import { apiGet, apiPost } from "../../services/api";
import FormFill from "./FormFill";

function uid() {
  return "f_" + Math.random().toString(16).slice(2) + "_" + Date.now().toString(16);
}

const FIELD_TYPES = [
  { value: "text", label: "Texto" },
  { value: "textarea", label: "Texto largo" },
  { value: "number", label: "Número" },
  { value: "date", label: "Fecha" },
  { value: "select", label: "Select" },
  { value: "checkbox", label: "Checkbox (Sí/No)" },
];

export default function FormsIndex() {
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  const [forms, setForms] = useState([]);
  const [loadingForms, setLoadingForms] = useState(false);

  // Crear form (builder)
  const [creating, setCreating] = useState(false);
  const [title, setTitle] = useState("Nuevo formulario");
  const [fields, setFields] = useState([
    { id: uid(), label: "Nombre", type: "text", required: true, optionsText: "" },
  ]);

  // Ver detalle
  const [selectedId, setSelectedId] = useState(null);
  const [detail, setDetail] = useState(null);
  const [loadingDetail, setLoadingDetail] = useState(false);

  // Submissions (últimas respuestas)
  const [subs, setSubs] = useState([]);
  const [loadingSubs, setLoadingSubs] = useState(false);

  // Selección de una submission para ver detalle
  const [selectedSub, setSelectedSub] = useState(null);

  // Modo: dashboard | fill
  const [mode, setMode] = useState("dashboard");

  // Sesión (localStorage)
  const [me, setMe] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const token = useMemo(() => localStorage.getItem("token"), []);
  const isAdmin =
    !!me?.is_admin ||
    (Array.isArray(me?.roles) && me.roles.includes("Administrador"));

  const kickToLogin = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
  };

  const setAuthError = (e, fallback = "Error") => {
    const msg = String(e?.message || "");
    if (msg.includes("401") || msg.toLowerCase().includes("no autorizado"))
      return kickToLogin();
    if (msg.includes("403") || msg.toLowerCase().includes("forbidden"))
      return kickToLogin();
    setErr(e?.message || fallback);
  };

  const loadForms = async () => {
    setErr("");
    setLoadingForms(true);
    try {
      const data = await apiGet("/forms");
      setForms(data.forms || []);
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
      setDetail(data.form || null);
    } catch (e) {
      setAuthError(e, "Error cargando detalle");
    } finally {
      setLoadingDetail(false);
    }
  };

  const loadSubmissions = async (id) => {
    setLoadingSubs(true);
    try {
      const data = await apiGet(`/forms/${id}/submissions`);
      setSubs(data.submissions || []);
    } catch {
      setSubs([]);
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

  const addField = () => {
    setFields((prev) => [
      ...prev,
      { id: uid(), label: "", type: "text", required: false, optionsText: "" },
    ]);
  };

  const removeField = (id) => {
    setFields((prev) => prev.filter((f) => f.id !== id));
  };

  const updateField = (id, patch) => {
    setFields((prev) =>
      prev.map((f) => (f.id === id ? { ...f, ...patch } : f))
    );
  };

  const buildPayload = () => {
    const normalized = fields
      .map((f) => {
        const label = String(f.label || "").trim();
        const type = f.type;
        const required = !!f.required;

        if (!label) return null;

        if (type === "select") {
          const options = String(f.optionsText || "")
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean);

          return { id: f.id, label, type, required, options };
        }

        return { id: f.id, label, type, required };
      })
      .filter(Boolean);

    return { fields: normalized };
  };

  const validateBuilder = () => {
    const cleanTitle = title.trim();
    if (!cleanTitle) return "Escribe un título.";

    const payload = buildPayload();
    if (!payload.fields.length) return "Agrega al menos 1 campo con etiqueta.";

    for (const f of payload.fields) {
      if (f.type === "select" && (!Array.isArray(f.options) || f.options.length < 2)) {
        return `El campo "${f.label}" (select) debe tener al menos 2 opciones.`;
      }
    }

    return null;
  };

  const onCreate = async (e) => {
    e.preventDefault();
    setErr("");

    if (!isAdmin) {
      setErr("Solo un Administrador puede crear formularios.");
      return;
    }

    const builderErr = validateBuilder();
    if (builderErr) {
      setErr(builderErr);
      return;
    }

    setCreating(true);
    try {
      const payload = buildPayload();

      // ✅ crea por /admin/forms
      const res = await apiPost("/admin/forms", {
        title: title.trim(),
        payload,
      });

      const created = res?.form;
      await loadForms();

      if (created?.id) {
        setSelectedId(created.id);
        await loadDetail(created.id);

        // ✅ solo admin carga submissions
        await loadSubmissions(created.id);

        setSelectedSub(null);
      }

      setTitle("Nuevo formulario");
      setFields([{ id: uid(), label: "Nombre", type: "text", required: true, optionsText: "" }]);
    } catch (e2) {
      setAuthError(e2, "Error creando formulario");
    } finally {
      setCreating(false);
    }
  };

  const onSelect = async (id) => {
    setSelectedId(id);
    await loadDetail(id);

    // ✅ usar isAdmin (no me?.is_admin)
    if (isAdmin) {
      await loadSubmissions(id);
    } else {
      setSubs([]);
    }

    setSelectedSub(null);
    setMode("dashboard");
  };

  // ---------------- UI styles (inline) ----------------
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

  const Btn = ({ children, style, ...props }) => (
    <button
      {...props}
      style={{
        borderRadius: 10,
        border: "1px solid #e4e4e7",
        background: "#fff",
        padding: "10px 12px",
        cursor: props.disabled ? "not-allowed" : "pointer",
        fontWeight: 800,
        opacity: props.disabled ? 0.7 : 1,
        ...style,
      }}
    >
      {children}
    </button>
  );

  if (loading) return <div style={{ padding: 16 }}>Cargando formularios...</div>;

  if (mode === "fill" && detail) {
    return <FormFill form={detail} onBack={() => setMode("dashboard")} />;
  }

  return (
    <div>
      <Card style={{ marginBottom: 14 }}>
        <div style={{ display: "flex", justifyContent: "space-between", gap: 10, flexWrap: "wrap" }}>
          <div>
            <h2 style={{ margin: 0 }}>Formularios</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Aquí puedes ver y llenar formularios. {isAdmin ? "Como admin, también puedes crear y ver respuestas." : ""}
            </div>
          </div>

          <div style={{ display: "flex", gap: 8, flexWrap: "wrap", alignItems: "center" }}>
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

      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(360px, 1fr))",
          gap: 14,
        }}
      >
        {/* LISTA */}
        <Card>
          <h3 style={{ marginTop: 0 }}>Mis formularios</h3>

          {loadingForms ? (
            <div>Cargando...</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table
                border="1"
                cellPadding="8"
                style={{ borderCollapse: "collapse", width: "100%", minWidth: 520 }}
              >
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Fecha</th>
                    <th style={{ width: 120 }}>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  {forms.length ? (
                    forms.map((f) => (
                      <tr
                        key={f.id}
                        style={{
                          background:
                            selectedId === f.id ? "rgba(2,6,23,0.06)" : "transparent",
                        }}
                      >
                        <td>{f.id}</td>
                        <td>{f.title}</td>
                        <td>{f.status}</td>
                        <td style={{ fontSize: 12 }}>{String(f.created_at || "")}</td>
                        <td>
                          <Btn type="button" onClick={() => onSelect(f.id)} style={{ padding: "8px 10px" }}>
                            Ver
                          </Btn>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="5">Sin formularios</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          )}
        </Card>

        {/* BUILDER + DETALLE */}
        <Card>
          <h3 style={{ marginTop: 0 }}>Crear formulario (Builder)</h3>

          {!isAdmin ? (
            <div
              style={{
                fontSize: 13,
                color: "#475569",
                border: "1px solid #e4e4e7",
                padding: 10,
                borderRadius: 12,
                background: "#f8fafc",
              }}
            >
              Tu usuario no es admin, por eso aquí solo puedes <b>ver y llenar</b>{" "}
              formularios publicados.
            </div>
          ) : (
            <form onSubmit={onCreate}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 900, color: "#334155" }}>
                  Título
                </label>
                <br />
                <input
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  style={{
                    width: "100%",
                    padding: "10px 12px",
                    borderRadius: 10,
                    border: "1px solid #e4e4e7",
                    background: "#f8fafc",
                  }}
                  placeholder="Ej. Checklist Laptop"
                />
              </div>

              <div style={{ marginTop: 12 }}>
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    gap: 10,
                    alignItems: "center",
                  }}
                >
                  <label>
                    <b>Campos</b>
                  </label>
                  <Btn type="button" onClick={addField} style={{ padding: "8px 10px" }}>
                    + Campo
                  </Btn>
                </div>

                <div style={{ marginTop: 10, display: "flex", flexDirection: "column", gap: 10 }}>
                  {fields.map((f, idx) => (
                    <div
                      key={f.id}
                      style={{
                        border: "1px solid #e4e4e7",
                        borderRadius: 12,
                        padding: 12,
                        background: "#fff",
                      }}
                    >
                      <div style={{ display: "flex", justifyContent: "space-between", gap: 10 }}>
                        <div style={{ fontSize: 12, color: "#64748b" }}>Campo #{idx + 1}</div>
                        <Btn
                          type="button"
                          onClick={() => removeField(f.id)}
                          disabled={fields.length <= 1}
                          style={{ width: "auto", marginTop: 0, padding: "8px 10px" }}
                        >
                          Eliminar
                        </Btn>
                      </div>

                      <div style={{ marginTop: 10 }}>
                        <label style={{ fontSize: 12, fontWeight: 900, color: "#334155" }}>
                          Etiqueta
                        </label>
                        <br />
                        <input
                          value={f.label}
                          onChange={(e) => updateField(f.id, { label: e.target.value })}
                          style={{
                            width: "100%",
                            padding: "10px 12px",
                            borderRadius: 10,
                            border: "1px solid #e4e4e7",
                            background: "#f8fafc",
                          }}
                          placeholder="Ej. Nombre completo"
                        />
                      </div>

                      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10, marginTop: 10 }}>
                        <div>
                          <label style={{ fontSize: 12, fontWeight: 900, color: "#334155" }}>
                            Tipo
                          </label>
                          <br />
                          <select
                            value={f.type}
                            onChange={(e) => updateField(f.id, { type: e.target.value })}
                            style={{
                              width: "100%",
                              padding: "10px 12px",
                              borderRadius: 10,
                              border: "1px solid #e4e4e7",
                              background: "#f8fafc",
                            }}
                          >
                            {FIELD_TYPES.map((t) => (
                              <option key={t.value} value={t.value}>
                                {t.label}
                              </option>
                            ))}
                          </select>
                        </div>

                        <div style={{ display: "flex", alignItems: "flex-end", gap: 8 }}>
                          <label style={{ display: "inline-flex", gap: 8, alignItems: "center", fontSize: 13 }}>
                            <input
                              type="checkbox"
                              checked={!!f.required}
                              onChange={(e) => updateField(f.id, { required: e.target.checked })}
                            />
                            Requerido
                          </label>
                        </div>
                      </div>

                      {f.type === "select" ? (
                        <div style={{ marginTop: 10 }}>
                          <label style={{ fontSize: 12, fontWeight: 900, color: "#334155" }}>
                            Opciones (separadas por coma)
                          </label>
                          <br />
                          <input
                            value={f.optionsText || ""}
                            onChange={(e) => updateField(f.id, { optionsText: e.target.value })}
                            style={{
                              width: "100%",
                              padding: "10px 12px",
                              borderRadius: 10,
                              border: "1px solid #e4e4e7",
                              background: "#f8fafc",
                            }}
                            placeholder="Ej. TI, RH, Operaciones"
                          />
                        </div>
                      ) : null}
                    </div>
                  ))}
                </div>

                <div style={{ marginTop: 10, fontSize: 12, color: "#64748b" }}>
                  Guardaremos esto como <code>payload.fields</code>.
                </div>
              </div>

              <div style={{ display: "flex", gap: 8, justifyContent: "flex-end", marginTop: 12 }}>
                <Btn type="submit" disabled={creating} style={{ width: "auto", fontWeight: 900 }}>
                  {creating ? "Guardando..." : "Guardar formulario"}
                </Btn>
              </div>
            </form>
          )}

          <hr style={{ margin: "14px 0", borderColor: "#e4e4e7" }} />

          <h3 style={{ marginTop: 0 }}>Detalle</h3>

          {!selectedId ? (
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Selecciona un formulario de la lista.
            </div>
          ) : loadingDetail ? (
            <div>Cargando detalle...</div>
          ) : detail ? (
            <div style={{ fontSize: 13 }}>
              <div>
                <b>ID:</b> {detail.id}
              </div>
              <div>
                <b>Título:</b> {detail.title}
              </div>
              <div>
                <b>Status:</b> {detail.status}
              </div>

              {/* ✅ Botones */}
              <div style={{ marginTop: 10, display: "flex", gap: 8, flexWrap: "wrap" }}>
                <Btn
                  type="button"
                  onClick={() => setMode("fill")}
                  disabled={!detail?.payload?.fields?.length}
                >
                  Llenar formulario
                </Btn>

                {/* ✅ SOLO ADMIN VE "REFRESCAR RESPUESTAS" */}
                {isAdmin ? (
                  <Btn
                    type="button"
                    onClick={() => {
                      setSelectedSub(null);
                      loadSubmissions(detail.id);
                    }}
                    disabled={!selectedId || loadingSubs}
                  >
                    {loadingSubs ? "Actualizando..." : "Refrescar respuestas"}
                  </Btn>
                ) : null}
              </div>

              {/* ✅ SOLO ADMIN VE "RESPUESTAS RECIENTES" */}
              {isAdmin ? (
                <>
                  <hr style={{ margin: "14px 0", borderColor: "#e4e4e7" }} />

                  <h4 style={{ margin: "0 0 8px" }}>Respuestas recientes</h4>

                  {loadingSubs ? (
                    <div style={{ fontSize: 13 }}>Cargando respuestas...</div>
                  ) : subs.length ? (
                    <div style={{ overflowX: "auto" }}>
                      <table
                        border="1"
                        cellPadding="8"
                        style={{ borderCollapse: "collapse", width: "100%", minWidth: 520 }}
                      >
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Fecha</th>
                            <th>Resumen</th>
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
                                if (typeof value === "boolean")
                                  return `${label}: ${value ? "Sí" : "No"}`;
                                return `${label}: ${String(value)}`;
                              })
                              .join(" | ");

                            return (
                              <tr
                                key={s.id}
                                onClick={() => setSelectedSub(s)}
                                style={{
                                  cursor: "pointer",
                                  background:
                                    selectedSub?.id === s.id
                                      ? "rgba(2,6,23,0.06)"
                                      : "transparent",
                                }}
                                title="Ver detalle de esta respuesta"
                              >
                                <td>{s.id}</td>
                                <td>{s.user_id}</td>
                                <td style={{ fontSize: 12 }}>
                                  {String(s.created_at || "")}
                                </td>
                                <td style={{ fontSize: 12 }}>
                                  {preview || "—"}
                                </td>
                              </tr>
                            );
                          })}
                        </tbody>
                      </table>

                      {/* ✅ Detalle de submission seleccionada */}
                      {selectedSub ? (
                        <div
                          style={{
                            marginTop: 12,
                            border: "1px solid #e4e4e7",
                            borderRadius: 12,
                            padding: 12,
                            background: "#fff",
                          }}
                        >
                          <div
                            style={{
                              display: "flex",
                              justifyContent: "space-between",
                              gap: 10,
                              flexWrap: "wrap",
                            }}
                          >
                            <div>
                              <b>Detalle respuesta #{selectedSub.id}</b>{" "}
                              <span style={{ fontSize: 12, color: "#64748b" }}>
                                (user {selectedSub.user_id} ·{" "}
                                {String(selectedSub.created_at || "")})
                              </span>
                            </div>
                            <Btn
                              type="button"
                              onClick={() => setSelectedSub(null)}
                              style={{ width: "auto", marginTop: 0 }}
                            >
                              Cerrar
                            </Btn>
                          </div>

                          <div
                            style={{
                              marginTop: 10,
                              display: "flex",
                              flexDirection: "column",
                              gap: 8,
                            }}
                          >
                            {(detail?.payload?.fields || []).map((f) => {
                              const v = (selectedSub.answers || {})[f.id];

                              let shown = v;
                              if (f.type === "checkbox") shown = v ? "Sí" : "No";
                              if (v === undefined || v === null || v === "")
                                shown = "—";

                              return (
                                <div
                                  key={f.id}
                                  style={{
                                    padding: 10,
                                    border: "1px solid #e4e4e7",
                                    borderRadius: 12,
                                    background: "#f8fafc",
                                  }}
                                >
                                  <div style={{ fontSize: 12, color: "#64748b" }}>
                                    {f.label}
                                  </div>
                                  <div style={{ marginTop: 4, fontWeight: 900 }}>
                                    {String(shown)}
                                  </div>
                                </div>
                              );
                            })}
                          </div>

                          <details style={{ marginTop: 10 }}>
                            <summary style={{ cursor: "pointer" }}>
                              Ver answers raw (debug)
                            </summary>
                            <pre
                              style={{
                                marginTop: 8,
                                background: "rgba(2,6,23,0.06)",
                                padding: 10,
                                borderRadius: 12,
                                overflowX: "auto",
                              }}
                            >
                              {JSON.stringify(selectedSub.answers ?? {}, null, 2)}
                            </pre>
                          </details>
                        </div>
                      ) : null}
                    </div>
                  ) : (
                    <div style={{ fontSize: 13, color: "#64748b" }}>
                      Aún no hay respuestas para este formulario.
                    </div>
                  )}
                </>
              ) : null}
            </div>
          ) : (
            <div style={{ fontSize: 13, color: "#64748b" }}>
              No se encontró el formulario.
            </div>
          )}
        </Card>
      </div>
    </div>
  );
}