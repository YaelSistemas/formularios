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

  // asignaciones
  const [openAssignments, setOpenAssignments] = useState(false);
  const [assignmentForm, setAssignmentForm] = useState(null);
  const [users, setUsers] = useState([]);
  const [loadingUsers, setLoadingUsers] = useState(false);
  const [loadingAssignments, setLoadingAssignments] = useState(false);
  const [savingAssignments, setSavingAssignments] = useState(false);
  const [assignmentSearch, setAssignmentSearch] = useState("");
  const [selectedUserIds, setSelectedUserIds] = useState([]);

  const searchRef = useRef(null);
  const searchWasFocusedRef = useRef(false);

  const rememberFocus = () => {
    searchWasFocusedRef.current = true;
  };

  const restoreFocusIfNeeded = () => {
    const el = searchRef.current;
    if (!el) return;
    if (!searchWasFocusedRef.current) return;

    requestAnimationFrame(() => {
      el.focus({ preventScroll: true });
      try {
        const len = el.value?.length ?? 0;
        el.setSelectionRange(len, len);
      } catch {
        //
      }
    });
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  const filteredForms = useMemo(() => {
    if (!q) return forms;
    return forms.filter((f) => {
      const title = String(f?.title || "").toLowerCase();
      const codeKey = String(f?.payload?._code_key || "").toLowerCase();
      return title.includes(q) || codeKey.includes(q);
    });
  }, [forms, q]);

  useEffect(() => {
    if (openPreview || openAssignments) return;
    if (!searchWasFocusedRef.current) return;

    const el = searchRef.current;
    if (!el) return;
    if (document.activeElement === el) return;

    el.focus({ preventScroll: true });
    try {
      const len = el.value?.length ?? 0;
      el.setSelectionRange(len, len);
    } catch {
      //
    }
  }, [loadingForms, forms, q, qDraft, filteredForms.length, openPreview, openAssignments]);

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
    } catch {
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

  const openAssignmentsModal = async (f) => {
    setErr("");
    setAssignmentForm(f);
    setOpenAssignments(true);
    setAssignmentSearch("");
    setSelectedUserIds([]);
    setUsers([]);

    try {
      setLoadingUsers(true);
      setLoadingAssignments(true);

      const [usersResp, assignmentsResp] = await Promise.all([
        apiGet("/admin/users"),
        apiGet(`/admin/forms/${f.id}/assignments`),
      ]);
      
      console.log("usersResp", usersResp);
      console.log("assignmentsResp", assignmentsResp);
      
      const usersList = Array.isArray(usersResp)
        ? usersResp
        : Array.isArray(usersResp?.users)
        ? usersResp.users
        : Array.isArray(usersResp?.data)
        ? usersResp.data
        : [];
      
      const assignedIds = Array.isArray(assignmentsResp?.user_ids)
        ? assignmentsResp.user_ids.map((id) => Number(id))
        : [];

      setUsers(usersList);
      setSelectedUserIds(assignedIds);
    } catch (e) {
      setErr(e?.message || "Error cargando asignaciones");
    } finally {
      setLoadingUsers(false);
      setLoadingAssignments(false);
    }
  };

  const closeAssignmentsModal = () => {
    setOpenAssignments(false);
    setAssignmentForm(null);
    setUsers([]);
    setSelectedUserIds([]);
    setAssignmentSearch("");
  };

  const toggleUserSelection = (userId) => {
    const nId = Number(userId);
    setSelectedUserIds((prev) =>
      prev.includes(nId) ? prev.filter((id) => id !== nId) : [...prev, nId]
    );
  };

  const selectAllFilteredUsers = () => {
    const filteredIds = filteredUsers.map((u) => Number(u.id));
    setSelectedUserIds((prev) => {
      const merged = new Set([...prev, ...filteredIds]);
      return Array.from(merged);
    });
  };

  const clearAllFilteredUsers = () => {
    const filteredIds = filteredUsers.map((u) => Number(u.id));
    setSelectedUserIds((prev) => prev.filter((id) => !filteredIds.includes(Number(id))));
  };

  const saveAssignments = async () => {
    if (!assignmentForm?.id) return;

    setErr("");
    setSavingAssignments(true);

    try {
      await apiPost(`/admin/forms/${assignmentForm.id}/assignments`, {
        user_ids: selectedUserIds,
      });

      setForms((prev) =>
        prev.map((f) =>
          f.id === assignmentForm.id
            ? {
                ...f,
                assignments_count: selectedUserIds.length,
              }
            : f
        )
      );

      showToast("success", "✅ Asignaciones guardadas");
      closeAssignmentsModal();
    } catch (e) {
      setErr(e?.message || "Error guardando asignaciones");
    } finally {
      setSavingAssignments(false);
    }
  };

  const Badge = ({ children, variant = "default" }) => {
    const variants = {
      default: { border: "#e2e8f0", bg: "#f8fafc", fg: "#0f172a" },
      success: { border: "#86efac", bg: "#ecfdf5", fg: "#166534" },
      info: { border: "#93c5fd", bg: "#eff6ff", fg: "#1e40af" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
      warn: { border: "#fde68a", bg: "#fffbeb", fg: "#92400e" },
      violet: { border: "#c4b5fd", bg: "#f5f3ff", fg: "#6d28d9" },
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
          fontWeight: 800,
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
        border: "1px solid #e2e8f0",
        borderRadius: 18,
        padding: 16,
        boxShadow: "0 8px 24px rgba(15, 23, 42, 0.05)",
        ...style,
      }}
    >
      {children}
    </div>
  );

  const Btn = ({ children, style, variant = "default", ...props }) => {
    const variants = {
      default: { border: "#cbd5e1", bg: "#fff", fg: "#0f172a" },
      primary: { border: "#bfdbfe", bg: "#eff6ff", fg: "#1d4ed8" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
      violet: { border: "#c4b5fd", bg: "#f5f3ff", fg: "#6d28d9" },
    };
    const v = variants[variant] || variants.default;

    return (
      <button
        {...props}
        style={{
          borderRadius: 12,
          border: `1px solid ${v.border}`,
          background: v.bg,
          color: v.fg,
          padding: "10px 14px",
          cursor: props.disabled ? "not-allowed" : "pointer",
          fontWeight: 800,
          opacity: props.disabled ? 0.7 : 1,
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          gap: 8,
          transition: "0.2s ease",
          ...style,
        }}
      >
        {children}
      </button>
    );
  };

  const IconBtn = ({ children, variant = "default", title, style, ...props }) => {
    const variants = {
      default: { border: "#e2e8f0", bg: "#fff", fg: "#0f172a" },
      primary: { border: "#bfdbfe", bg: "#eff6ff", fg: "#1d4ed8" },
      danger: { border: "#fecaca", bg: "#fef2f2", fg: "#b91c1c" },
      violet: { border: "#c4b5fd", bg: "#f5f3ff", fg: "#6d28d9" },
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
          borderRadius: 12,
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
    page: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
    },
    headerTop: {
      display: "flex",
      justifyContent: "space-between",
      gap: 12,
      flexWrap: "wrap",
      alignItems: "center",
    },
    titleBlock: {
      display: "flex",
      flexDirection: "column",
      gap: 4,
    },
    filterRow: {
      display: "grid",
      gridTemplateColumns: "minmax(220px, 1fr)",
      gap: 12,
      alignItems: "end",
      marginTop: 14,
    },
    label: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 800,
      marginBottom: 6,
    },
    input: {
      width: "100%",
      padding: "11px 12px",
      borderRadius: 12,
      border: "1px solid #dbeafe",
      background: "#f8fafc",
      outline: "none",
      minHeight: 44,
    },
    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border: "1px solid #e2e8f0",
      borderRadius: 16,
    },
    table: {
      borderCollapse: "separate",
      borderSpacing: 0,
      width: "100%",
      minWidth: 1020,
      background: "#fff",
    },
    th: {
      textAlign: "left",
      fontSize: 12,
      color: "#475569",
      padding: "14px 12px",
      borderBottom: "1px solid #e2e8f0",
      background: "#f8fafc",
      whiteSpace: "nowrap",
      fontWeight: 800,
      position: "sticky",
      top: 0,
      zIndex: 1,
    },
    td: {
      padding: "14px 12px",
      borderBottom: "1px solid #f1f5f9",
      verticalAlign: "middle",
      fontSize: 13,
      color: "#0f172a",
    },
    modalOverlay: {
      position: "fixed",
      inset: 0,
      background: "rgba(2, 6, 23, 0.55)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      padding: 14,
      zIndex: 100,
    },
    modal: {
      width: "100%",
      maxWidth: 1180,
      maxHeight: "90vh",
      overflowY: "auto",
      background: "#fff",
      borderRadius: 18,
      border: "1px solid #e2e8f0",
      boxShadow: "0 25px 60px rgba(0,0,0,.18)",
    },
    assignmentsModal: {
      width: "100%",
      maxWidth: 980,
      maxHeight: "90vh",
      overflowY: "auto",
      background: "#fff",
      borderRadius: 18,
      border: "1px solid #e2e8f0",
      boxShadow: "0 25px 60px rgba(0,0,0,.18)",
    },
    modalHeader: {
      padding: "16px 18px",
      borderBottom: "1px solid #e2e8f0",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 12,
      position: "sticky",
      top: 0,
      background: "#fff",
      zIndex: 1,
    },
    modalTitle: {
      margin: 0,
      fontSize: 18,
      fontWeight: 800,
      color: "#0f172a",
    },
    modalBody: {
      padding: 18,
      display: "flex",
      flexDirection: "column",
      gap: 14,
    },
    modalFooter: {
      padding: 18,
      borderTop: "1px solid #e2e8f0",
      display: "flex",
      gap: 10,
      justifyContent: "flex-end",
      flexWrap: "wrap",
      position: "sticky",
      bottom: 0,
      background: "#fff",
    },
    xBtn: {
      border: "1px solid #e2e8f0",
      background: "#fff",
      borderRadius: 10,
      width: 38,
      height: 38,
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
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      padding: 16,
      background: "#fff",
      minHeight: 520,
    },
    previewRight: {
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      background: "#fff",
      overflow: "hidden",
      minHeight: 520,
      display: "flex",
      flexDirection: "column",
    },
    previewRightTop: {
      padding: "12px 14px",
      borderBottom: "1px solid #e2e8f0",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      gap: 10,
      background: "#0f172a",
      color: "#fff",
      fontWeight: 800,
    },
    previewRightBody: {
      padding: 14,
      overflowY: "auto",
      maxHeight: 520,
      background: "#fff",
    },
    roInput: {
      width: "100%",
      padding: "10px 12px",
      borderRadius: 12,
      border: "1px solid #e2e8f0",
      background: "#f8fafc",
      outline: "none",
    },
    assignmentsGrid: {
      display: "grid",
      gridTemplateColumns: "minmax(280px, 1fr) 1.1fr",
      gap: 14,
      alignItems: "start",
    },
    usersList: {
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      overflow: "hidden",
      background: "#fff",
    },
    usersListBody: {
      maxHeight: 430,
      overflowY: "auto",
      display: "flex",
      flexDirection: "column",
    },
    userRow: {
      display: "flex",
      alignItems: "flex-start",
      gap: 12,
      padding: 12,
      borderBottom: "1px solid #f1f5f9",
    },
    summaryBox: {
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      padding: 16,
      background: "#f8fafc",
      display: "flex",
      flexDirection: "column",
      gap: 12,
    },
    responsiveStyleTag: `
      @media (max-width: 980px) {
        .forms-preview-wrap {
          grid-template-columns: 1fr !important;
        }
        .forms-modal-full {
          max-width: 100% !important;
        }
        .forms-assignments-grid {
          grid-template-columns: 1fr !important;
        }
      }

      @media (max-width: 560px) {
        .forms-header-mobile {
          align-items: stretch !important;
        }
        .forms-header-mobile button {
          width: 100%;
        }
      }
    `,
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
              <div
                key={f.id}
                style={{
                  padding: 12,
                  border: "1px dashed #e2e8f0",
                  borderRadius: 12,
                  background: "#fff",
                }}
              >
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>{label}</div>
                <div style={{ color: "#334155", fontSize: 13 }}>{f.text || "—"}</div>
              </div>
            );
          }

          if (type === "separator") {
            return (
              <div key={f.id} style={{ padding: "6px 0" }}>
                <div style={{ borderTop: "2px solid #e2e8f0" }} />
              </div>
            );
          }

          if (type === "fixed_image") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>{label}</div>
                <div
                  style={{
                    border: "1px solid #e2e8f0",
                    borderRadius: 12,
                    overflow: "hidden",
                    background: "#f8fafc",
                  }}
                >
                  <div style={{ padding: 10, color: "#64748b", fontSize: 12 }}>
                    Imagen fija: {f.url || "—"}
                  </div>
                </div>
              </div>
            );
          }

          if (type === "fixed_file") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>{label}</div>
                <div
                  style={{
                    padding: 10,
                    border: "1px solid #e2e8f0",
                    borderRadius: 12,
                    background: "#f8fafc",
                  }}
                >
                  Archivo fijo: {f.url || "—"}
                </div>
              </div>
            );
          }

          if (type === "radio") {
            const opts = Array.isArray(f?.options) ? f.options : [];
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                  {opts.map((o) => (
                    <label
                      key={o}
                      style={{ display: "flex", gap: 10, alignItems: "center", color: "#0f172a" }}
                    >
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
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>
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
                <div style={{ fontSize: 13, fontWeight: 800 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
              </div>
            );
          }

          if (type === "textarea") {
            return (
              <div key={f.id}>
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>
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
                <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>
                  {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <div style={{ border: "1px solid #e2e8f0", borderRadius: 12, overflow: "hidden" }}>
                  <div
                    style={{
                      display: "grid",
                      gridTemplateColumns: `repeat(${Math.max(cols.length, 1)}, 1fr)`,
                    }}
                  >
                    {(cols.length ? cols : ["Columna"]).map((c) => (
                      <div
                        key={c}
                        style={{
                          padding: 10,
                          fontWeight: 800,
                          background: "#f8fafc",
                          borderBottom: "1px solid #e2e8f0",
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
            type === "number"
              ? "number"
              : type === "date"
              ? "date"
              : type === "datetime"
              ? "datetime-local"
              : "text";

          return (
            <div key={f.id}>
              <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 6 }}>
                {label} {required ? <span style={{ color: "#dc2626" }}>*</span> : null}
              </div>
              <input disabled type={htmlType} style={S.roInput} placeholder="—" />
            </div>
          );
        })}
      </div>
    );
  };

  const filteredUsers = useMemo(() => {
    const term = assignmentSearch.trim().toLowerCase();
    if (!term) return users;

    return users.filter((u) => {
      const name = String(u?.name || "").toLowerCase();
      const email = String(u?.email || "").toLowerCase();
      const role = String(u?.role || u?.role_name || "").toLowerCase();
      const enterprise = String(u?.enterprise || u?.enterprise_name || "").toLowerCase();
      const group = String(u?.group || u?.group_name || "").toLowerCase();
      const unit = String(u?.service_unit || u?.service_unit_name || u?.unidad_servicio || "").toLowerCase();

      return (
        name.includes(term) ||
        email.includes(term) ||
        role.includes(term) ||
        enterprise.includes(term) ||
        group.includes(term) ||
        unit.includes(term)
      );
    });
  }, [users, assignmentSearch]);

  const selectedUsersPreview = useMemo(() => {
    const selectedSet = new Set(selectedUserIds.map((id) => Number(id)));
    return users.filter((u) => selectedSet.has(Number(u.id)));
  }, [users, selectedUserIds]);

  return (
    <div style={S.page}>
      <style>{S.responsiveStyleTag}</style>

      <Card>
        <div style={S.headerTop} className="forms-header-mobile">
          <div style={S.titleBlock}>
            <h2 style={{ margin: 0, fontSize: 24, color: "#0f172a" }}>Formularios</h2>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Formularios definidos por código. Solo puedes ver, publicar y asignar acceso.
            </div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Total registrados: <b>{filteredForms.length}</b>
            </div>
          </div>
        </div>

        <div style={S.filterRow}>
          <div>
            <div style={S.label}>Buscar formulario</div>
            <input
              ref={searchRef}
              value={qDraft}
              onFocus={rememberFocus}
              onClick={rememberFocus}
              onChange={(e) => {
                rememberFocus();
                setQDraft(e.target.value);
              }}
              placeholder="Buscar por título o clave"
              style={S.input}
            />
          </div>
        </div>

        {toast ? (
          <div
            style={{
              marginTop: 14,
              padding: "10px 12px",
              borderRadius: 12,
              border: `1px solid ${toastStyle.border}`,
              background: toastStyle.bg,
              color: toastStyle.fg,
              fontWeight: 800,
            }}
          >
            {toast.text}
          </div>
        ) : null}

        {err ? (
          <div style={{ marginTop: 12, color: "#b91c1c", fontWeight: 800 }}>{err}</div>
        ) : null}
      </Card>

      <Card>
        {loadingForms ? (
          <div style={{ color: "#475569", fontWeight: 700 }}>Cargando formularios...</div>
        ) : (
          <>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>ID</th>
                    <th style={S.th}>Título</th>
                    <th style={S.th}>Status</th>
                    <th style={S.th}>Asignaciones</th>
                    <th style={S.th}>Fecha</th>
                    <th style={{ ...S.th, width: 190, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredForms.length ? (
                    filteredForms.map((f) => (
                      <tr key={f.id}>
                        <td style={S.td}>{f.id}</td>

                        <td style={S.td}>
                          <div style={{ fontWeight: 800 }}>{f.title}</div>
                          <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
                            {f?.payload?._code_key || "Sin clave"}
                          </div>
                        </td>

                        <td style={S.td}>
                          <Badge variant={statusBadgeVariant(f.status)}>{f.status}</Badge>
                        </td>

                        <td style={S.td}>
                          {(Number(f?.assignments_count || 0) > 0) ? (
                            <Badge variant="violet">
                              {f.assignments_count} usuario{Number(f.assignments_count) === 1 ? "" : "s"}
                            </Badge>
                          ) : (
                            <Badge variant="default">Sin restricción</Badge>
                          )}
                        </td>

                        <td style={{ ...S.td, fontSize: 12, color: "#334155" }}>
                          {formatDate(f.created_at)}
                        </td>

                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openPreviewModal(f)} title="Ver" variant="primary">
                              <i className="fa-solid fa-eye" />
                            </IconBtn>

                            <IconBtn
                              onClick={() => openAssignmentsModal(f)}
                              title="Asignaciones"
                              variant="violet"
                            >
                              <i className="fa-solid fa-user-check" />
                            </IconBtn>

                            {f.status !== "PUBLICADO" ? (
                              <IconBtn
                                onClick={() => publishForm(f)}
                                disabled={publishingFormId === f.id}
                                variant="default"
                                title="Publicar"
                              >
                                <i className="fa-solid fa-cloud-arrow-up" />
                              </IconBtn>
                            ) : (
                              <IconBtn
                                onClick={() => unpublishForm(f)}
                                disabled={unpublishingFormId === f.id}
                                variant="default"
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
                      <td style={S.td} colSpan={6}>
                        Sin formularios
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={{ marginTop: 10, fontSize: 12, color: "#64748b" }}>
              Tip: si un formulario tiene usuarios asignados, solo ellos podrán verlo.
            </div>
          </>
        )}
      </Card>

      {openPreview && (
        <div style={S.modalOverlay} onClick={closePreviewModal}>
          <div
            style={S.modal}
            className="forms-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
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
                <div style={{ color: "#64748b", fontWeight: 800 }}>Cargando vista…</div>
              ) : (
                <div style={S.previewWrap} className="forms-preview-wrap">
                  <div style={S.previewLeft}>
                    <div
                      style={{
                        display: "flex",
                        justifyContent: "space-between",
                        gap: 10,
                        alignItems: "center",
                      }}
                    >
                      <div>
                        <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>
                          Formulario
                        </div>
                        <div style={{ fontSize: 18, fontWeight: 800, marginTop: 2 }}>
                          {previewForm.title || "—"}
                        </div>
                      </div>
                      <Badge variant={statusBadgeVariant(previewForm.status)}>
                        {previewForm.status || "—"}
                      </Badge>
                    </div>

                    <div style={{ marginTop: 14, borderTop: "1px solid #e2e8f0", paddingTop: 14 }}>
                      <div style={{ display: "grid", gap: 12 }}>
                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>Creado</div>
                          <div style={{ fontWeight: 800 }}>{formatDate(previewForm.created_at)}</div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>ID</div>
                          <div style={{ fontWeight: 800 }}>{previewForm.id}</div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>Campos</div>
                          <div style={{ fontWeight: 800 }}>
                            {Array.isArray(previewForm?.payload?.fields)
                              ? previewForm.payload.fields.length
                              : 0}
                          </div>
                        </div>

                        <div>
                          <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>
                            Clave código
                          </div>
                          <div style={{ fontWeight: 800 }}>
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
                      <div
                        style={{
                          display: "flex",
                          gap: 10,
                          alignItems: "center",
                          minWidth: 0,
                        }}
                      >
                        <i className="fa-solid fa-file-lines" />
                        <div
                          style={{
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                          }}
                        >
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

      {openAssignments && (
        <div style={S.modalOverlay} onClick={closeAssignmentsModal}>
          <div
            style={S.assignmentsModal}
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <div>
                <h3 style={S.modalTitle}>
                  Asignaciones {assignmentForm?.id ? `#${assignmentForm.id}` : ""}
                </h3>
                <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
                  {assignmentForm?.title || "Formulario"}
                </div>
              </div>

              <button
                type="button"
                style={S.xBtn}
                onClick={closeAssignmentsModal}
                aria-label="Cerrar"
              >
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {(loadingUsers || loadingAssignments) ? (
                <div style={{ color: "#64748b", fontWeight: 800 }}>
                  Cargando usuarios y asignaciones...
                </div>
              ) : (
                <div style={S.assignmentsGrid} className="forms-assignments-grid">
                  <div style={S.usersList}>
                    <div
                      style={{
                        padding: 14,
                        borderBottom: "1px solid #e2e8f0",
                        background: "#fff",
                      }}
                    >
                      <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800, marginBottom: 6 }}>
                        Buscar usuario
                      </div>
                      <input
                        value={assignmentSearch}
                        onChange={(e) => setAssignmentSearch(e.target.value)}
                        placeholder="Buscar por nombre, correo, rol, empresa..."
                        style={S.input}
                      />

                      <div
                        style={{
                          marginTop: 10,
                          display: "flex",
                          gap: 8,
                          flexWrap: "wrap",
                        }}
                      >
                        <Btn type="button" variant="violet" onClick={selectAllFilteredUsers}>
                          Seleccionar visibles
                        </Btn>
                        <Btn type="button" onClick={clearAllFilteredUsers}>
                          Quitar visibles
                        </Btn>
                      </div>
                    </div>

                    <div style={S.usersListBody}>
                      {filteredUsers.length ? (
                        filteredUsers.map((u) => {
                          const checked = selectedUserIds.includes(Number(u.id));
                          return (
                            <label key={u.id} style={S.userRow}>
                              <input
                                type="checkbox"
                                checked={checked}
                                onChange={() => toggleUserSelection(u.id)}
                                style={{ marginTop: 4 }}
                              />

                              <div style={{ minWidth: 0 }}>
                                <div style={{ fontWeight: 800, color: "#0f172a" }}>
                                  {u.name || "Sin nombre"}
                                </div>
                                <div style={{ fontSize: 12, color: "#475569", marginTop: 2 }}>
                                  {u.email || "Sin correo"}
                                </div>

                                <div
                                  style={{
                                    display: "flex",
                                    gap: 8,
                                    flexWrap: "wrap",
                                    marginTop: 8,
                                  }}
                                >
                                  {u.role || u.role_name ? (
                                    <Badge variant="info">{u.role || u.role_name}</Badge>
                                  ) : null}

                                  {u.unidad_servicio || u.service_unit || u.service_unit_name ? (
                                    <Badge variant="default">
                                      {u.unidad_servicio || u.service_unit || u.service_unit_name}
                                    </Badge>
                                  ) : null}

                                  {u.enterprise || u.enterprise_name ? (
                                    <Badge variant="default">
                                      {u.enterprise || u.enterprise_name}
                                    </Badge>
                                  ) : null}
                                </div>
                              </div>
                            </label>
                          );
                        })
                      ) : (
                        <div style={{ padding: 14, color: "#64748b" }}>
                          No hay usuarios para mostrar.
                        </div>
                      )}
                    </div>
                  </div>

                  <div style={S.summaryBox}>
                    <div>
                      <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>
                        Resumen
                      </div>
                      <div style={{ fontSize: 22, fontWeight: 900, color: "#0f172a", marginTop: 4 }}>
                        {selectedUserIds.length}
                      </div>
                      <div style={{ fontSize: 13, color: "#475569" }}>
                        usuario{selectedUserIds.length === 1 ? "" : "s"} con acceso
                      </div>
                    </div>

                    <div>
                      <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800, marginBottom: 8 }}>
                        Comportamiento
                      </div>
                      <div style={{ fontSize: 13, color: "#334155", lineHeight: 1.6 }}>
                        Si guardas usuarios aquí, el formulario quedará restringido y solo esos
                        usuarios podrán verlo.
                      </div>
                    </div>

                    <div>
                      <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800, marginBottom: 8 }}>
                        Seleccionados
                      </div>

                      <div
                        style={{
                          display: "flex",
                          flexDirection: "column",
                          gap: 8,
                          maxHeight: 260,
                          overflowY: "auto",
                        }}
                      >
                        {selectedUsersPreview.length ? (
                          selectedUsersPreview.map((u) => (
                            <div
                              key={u.id}
                              style={{
                                padding: "10px 12px",
                                borderRadius: 12,
                                border: "1px solid #e2e8f0",
                                background: "#fff",
                              }}
                            >
                              <div style={{ fontWeight: 800 }}>{u.name || "Sin nombre"}</div>
                              <div style={{ fontSize: 12, color: "#64748b", marginTop: 2 }}>
                                {u.email || "Sin correo"}
                              </div>
                            </div>
                          ))
                        ) : (
                          <div style={{ color: "#64748b", fontSize: 13 }}>
                            No hay usuarios seleccionados. Si guardas así, el formulario quedará sin restricción.
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>

            <div style={S.modalFooter}>
              <Btn type="button" onClick={closeAssignmentsModal}>
                Cancelar
              </Btn>
              <Btn
                type="button"
                variant="violet"
                onClick={saveAssignments}
                disabled={savingAssignments}
              >
                {savingAssignments ? "Guardando..." : "Guardar asignaciones"}
              </Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}