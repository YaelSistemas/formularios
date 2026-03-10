import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost } from "../../services/api";

export default function AdminForms() {
  const [err, setErr] = useState("");
  const [toast, setToast] = useState(null);
  const toastTimer = useRef(null);

  const [forms, setForms] = useState([]);
  const [loadingForms, setLoadingForms] = useState(false);

  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  const [openPreview, setOpenPreview] = useState(false);
  const [previewForm, setPreviewForm] = useState(null);

  const [publishingFormId, setPublishingFormId] = useState(null);
  const [unpublishingFormId, setUnpublishingFormId] = useState(null);

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

  const loadAdminForms = async () => {
    setErr("");
    setLoadingForms(true);
    try {
      const data = await apiGet("/admin/forms");
      setForms(Array.isArray(data?.forms) ? data.forms : []);
    } catch (e) {
      setErr(e?.message || "Error cargando formularios (admin)");
    } finally {
      setLoadingForms(false);
    }
  };

  useEffect(() => {
    loadAdminForms();
  }, []);

  useEffect(() => {
    const t = setTimeout(() => setQ(qDraft.trim().toLowerCase()), 250);
    return () => clearTimeout(t);
  }, [qDraft]);

  useEffect(() => {
    restoreFocusIfNeeded();
  }, [qDraft]);

  const filteredForms = useMemo(() => {
    if (!q) return forms;
    return forms.filter((f) =>
      String(f?.title || "").toLowerCase().includes(q)
    );
  }, [forms, q]);

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
      setPreviewForm(data?.form || null);
    } catch (e) {
      setPreviewForm(f || null);
    }
  };

  const closePreviewModal = () => {
    setOpenPreview(false);
    setPreviewForm(null);
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

  const statusBadgeVariant = (status) => {
    if (status === "PUBLICADO") return "success";
    if (status === "BORRADOR") return "warn";
    if (status === "INACTIVO") return "danger";
    return "default";
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
      maxWidth: 1180,
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
  };

  const formatDate = (d) => {
    if (!d) return "—";
    const s = String(d);
    if (s.includes("T")) return s.replace("T", " ").slice(0, 16);
    return s;
  };

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
              <div key={f.id} style={{ padding: 12, border: "1px dashed #e4e4e7", borderRadius: 12, background: "#fff" }}>
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
                  <div style={{ padding: 10, color: "#64748b", fontSize: 12 }}>Imagen fija: {f.url || "—"}</div>
                </div>
              </div>
            );
          }

          if (type === "fixed_file") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>{label}</div>
                <div style={{ padding: 10, border: "1px solid #e4e4e7", borderRadius: 12, background: "#f8fafc" }}>
                  Archivo fijo: {f.url || "—"}
                </div>
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

          if (type === "select") {
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

          const htmlType =
            type === "number" ? "number" :
            type === "date" ? "date" :
            type === "datetime" ? "datetime-local" :
            "text";

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

  return (
    <div>
      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Formularios (Admin)</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Formularios definidos por código. Solo puedes ver y publicar/despublicar.
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }}>
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
              />
            </div>

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
                    <th style={{ ...S.th, width: 160, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredForms.length ? (
                    filteredForms.map((f) => (
                      <tr key={f.id}>
                        <td style={S.td}>{f.id}</td>
                        <td style={S.td}>
                          <div style={{ fontWeight: 900 }}>{f.title}</div>
                        </td>
                        <td style={S.td}>
                          <Badge variant={statusBadgeVariant(f.status)}>{f.status}</Badge>
                        </td>
                        <td style={{ ...S.td, fontSize: 12, color: "#334155" }}>
                          {formatDate(f.created_at)}
                        </td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openPreviewModal(f)} title="Ver">
                              <i className="fa-solid fa-eye" />
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
      </Card>

      {openPreview && (
        <div style={S.modalOverlay} onClick={closePreviewModal}>
          <div style={S.modal} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                Vista del formulario {previewForm?.id ? `#${previewForm.id}` : ""}
              </h3>
              <button type="button" style={S.xBtn} onClick={closePreviewModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {!previewForm ? (
                <div style={{ color: "#64748b", fontWeight: 900 }}>Cargando vista…</div>
              ) : (
                <div style={S.previewWrap}>
                  <div style={S.previewLeft}>
                    <div style={{ display: "flex", justifyContent: "space-between", gap: 10, alignItems: "center" }}>
                      <div>
                        <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Formulario</div>
                        <div style={{ fontSize: 18, fontWeight: 900, marginTop: 2 }}>
                          {previewForm.title || "—"}
                        </div>
                      </div>
                      <Badge variant={statusBadgeVariant(previewForm.status)}>
                        {previewForm.status || "—"}
                      </Badge>
                    </div>

                    <div style={{ marginTop: 12, borderTop: "1px solid #e4e4e7", paddingTop: 12 }}>
                      <div style={{ display: "grid", gap: 10 }}>
                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Creado</div>
                          <div style={{ fontWeight: 900 }}>{formatDate(previewForm.created_at)}</div>
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

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 900 }}>Clave código</div>
                          <div style={{ fontWeight: 900 }}>
                            {previewForm?.payload?._code_key || "—"}
                          </div>
                        </div>
                      </div>

                      <div style={{ marginTop: 14, fontSize: 12, color: "#64748b" }}>
                        Esta vista es <b>solo lectura</b>.
                      </div>
                    </div>
                  </div>

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