import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminUnidadesServicio() {
  const [err, setErr] = useState("");

  const getStoredUser = () => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  };

  const normalizeRoles = (user) => {
    if (!user) return [];

    const rolesFromArray = Array.isArray(user.roles)
      ? user.roles
          .map((r) => (typeof r === "string" ? r : r?.name))
          .filter(Boolean)
      : [];

    const roleSingle = user.role
      ? [typeof user.role === "string" ? user.role : user.role?.name].filter(Boolean)
      : [];

    return [...new Set([...rolesFromArray, ...roleSingle])];
  };

  const normalizePermissions = (user) => {
    if (!user) return [];

    const directPermissions = Array.isArray(user.permissions)
      ? user.permissions
          .map((p) => (typeof p === "string" ? p : p?.name))
          .filter(Boolean)
      : [];

    return [...new Set(directPermissions)];
  };

  const me = getStoredUser();

  const isAdmin =
    !!me?.is_admin ||
    normalizeRoles(me).some((r) => String(r).toLowerCase() === "administrador");

  const hasPermission = (permission) => {
    if (isAdmin) return true;
    return normalizePermissions(me).includes(permission);
  };

  const canCreateUnidades = hasPermission("unidades_servicio.create");
  const canEditUnidades = hasPermission("unidades_servicio.edit");
  const canDeleteUnidades = hasPermission("unidades_servicio.delete");
  const canShowActionsColumn = canEditUnidades || canDeleteUnidades;
  const canViewUnidadHistory = isAdmin;

  const [toast, setToast] = useState(null);
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

  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(false);

  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  const [perPage, setPerPage] = useState(25);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const canPrev = useMemo(() => page > 1, [page]);
  const canNext = useMemo(() => page < (meta.last_page || 1), [page, meta.last_page]);

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
      //
    }
  };

  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create");
  const [editingId, setEditingId] = useState(null);

  const [fNombre, setFNombre] = useState("");
  const [fDesc, setFDesc] = useState("");
  const [fActivo, setFActivo] = useState(true);

  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const [fieldErrors, setFieldErrors] = useState({});

  const [openHistoryModal, setOpenHistoryModal] = useState(false);
  const [historyUnidad, setHistoryUnidad] = useState(null);
  const [historyLoading, setHistoryLoading] = useState(false);
  const [historyItems, setHistoryItems] = useState([]);
  const [historyError, setHistoryError] = useState("");

  const unidadHasAssignedUsers = (row) => {
    if (!row) return false;

    if (Number(row.users_count || 0) > 0) return true;
    if (Number(row.assigned_users_count || 0) > 0) return true;
    if (Number(row.total_users || 0) > 0) return true;
    if (Array.isArray(row.users) && row.users.length > 0) return true;

    return false;
  };

  const load = async () => {
    setErr("");
    setLoading(true);
    try {
      const data = await apiGet(
        `/admin/unidades-servicio?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`
      );

      if (Array.isArray(data?.data)) {
        setRows(data.data || []);
        setMeta({ last_page: data.last_page || 1, total: data.total || 0 });
      } else {
        const arr = Array.isArray(data?.unidades_servicio) ? data.unidades_servicio : [];
        setRows(arr);
        setMeta({ last_page: 1, total: arr.length });
      }
    } catch (e) {
      setErr(e?.message || "Error cargando unidades de servicio");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const t = setTimeout(() => {
      setPage(1);
      setQ(qDraft);
    }, 350);
    return () => clearTimeout(t);
  }, [qDraft]);

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, page, perPage]);

  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openForm || openHistoryModal) return;
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
  }, [loading, rows, meta.last_page, meta.total, openForm, openHistoryModal]);

  const resetForm = () => {
    setEditingId(null);
    setFNombre("");
    setFDesc("");
    setFActivo(true);
    setFieldErrors({});
    setErr("");
  };

  const openCreate = () => {
    if (!canCreateUnidades) {
      setErr("No tienes permiso para crear unidades de servicio.");
      return;
    }

    resetForm();
    setFormMode("create");
    setOpenForm(true);
  };

  const openEdit = (r) => {
    if (!canEditUnidades) {
      setErr("No tienes permiso para editar unidades de servicio.");
      return;
    }

    resetForm();
    setFormMode("edit");
    setEditingId(r.id);
    setFNombre(r.nombre || "");
    setFDesc(r.descripcion || "");
    setFActivo(!!r.activo);
    setOpenForm(true);
  };

  const closeModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
    setFieldErrors({});
  };

  const closeHistoryModal = () => {
    setOpenHistoryModal(false);
    setHistoryUnidad(null);
    setHistoryItems([]);
    setHistoryError("");
    setHistoryLoading(false);
  };

  const openHistory = async (r) => {
    if (!canViewUnidadHistory) {
      setErr("No tienes permiso para ver el historial de unidades de servicio.");
      return;
    }

    setHistoryUnidad(r);
    setHistoryItems([]);
    setHistoryError("");
    setOpenHistoryModal(true);
    setHistoryLoading(true);

    try {
      const data = await apiGet(`/admin/unidades-servicio/${r.id}/history`);
      setHistoryItems(Array.isArray(data?.history) ? data.history : []);
    } catch (e) {
      setHistoryError(e?.message || "Error cargando historial de la unidad de servicio");
    } finally {
      setHistoryLoading(false);
    }
  };

  const validateForm = () => {
    const actionText = formMode === "create" ? "crear" : "actualizar";
    const errors = {};

    if (!fNombre.trim()) {
      errors.nombre = `No se puede ${actionText} la unidad de servicio porque falta el nombre.`;
    }

    setFieldErrors(errors);

    if (Object.keys(errors).length > 0) {
      setErr(Object.values(errors)[0]);
      return false;
    }

    return true;
  };

  const submit = async (e) => {
    e.preventDefault();
    setErr("");

    if (formMode === "create" && !canCreateUnidades) {
      setErr("No tienes permiso para crear unidades de servicio.");
      return;
    }

    if (formMode === "edit" && !canEditUnidades) {
      setErr("No tienes permiso para editar unidades de servicio.");
      return;
    }

    if (!validateForm()) return;

    setSaving(true);

    try {
      const payload = {
        nombre: fNombre.trim(),
        descripcion: fDesc.trim() || null,
        activo: !!fActivo,
      };

      if (formMode === "create") {
        await apiPost("/admin/unidades-servicio", payload);
        showToast("success", "✅ Unidad de servicio creada correctamente");
      } else {
        await apiPut(`/admin/unidades-servicio/${editingId}`, payload);
        showToast("info", "✏️ Unidad de servicio actualizada");
      }

      closeModal();
      await load();
    } catch (e2) {
      setErr(e2?.message || "Error guardando unidad de servicio");
    } finally {
      setSaving(false);
    }
  };

  const remove = async (r) => {
    if (!canDeleteUnidades) {
      setErr("No tienes permiso para eliminar unidades de servicio.");
      return;
    }

    if (unidadHasAssignedUsers(r)) {
      setErr("No puedes eliminar una unidad de servicio que ya está asignada a uno o más usuarios.");
      return;
    }

    const ok = window.confirm(`¿Eliminar la unidad de servicio "${r.nombre}"?`);
    if (!ok) return;

    setErr("");
    setDeletingId(r.id);
    try {
      await apiDelete(`/admin/unidades-servicio/${r.id}`);
      showToast("danger", "🗑️ Unidad de servicio eliminada");
      await load();
    } catch (e) {
      setErr(e?.message || "Error eliminando unidad de servicio");
    } finally {
      setDeletingId(null);
    }
  };

  const formatDateTime = (value) => {
    if (!value) return "—";
    try {
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return String(value);
      return d.toLocaleString();
    } catch {
      return String(value);
    }
  };

  const enumeratedHistoryItems = useMemo(() => {
    const priority = {
      created: 1,
      updated: 2,
      deleted: 3,
    };

    const sorted = [...historyItems].sort((a, b) => {
      const aPriority = priority[a?.action] || 99;
      const bPriority = priority[b?.action] || 99;

      if (aPriority !== bPriority) return aPriority - bPriority;

      const aDate = a?.created_at ? new Date(a.created_at).getTime() : 0;
      const bDate = b?.created_at ? new Date(b.created_at).getTime() : 0;

      return aDate - bDate;
    });

    let updateCounter = 0;

    return sorted.map((item) => {
      if (item.action === "updated") {
        updateCounter += 1;
        return {
          ...item,
          updateNumber: updateCounter,
        };
      }

      return {
        ...item,
        updateNumber: null,
      };
    });
  }, [historyItems]);

  const getHistoryActionMeta = (action, updateNumber = null) => {
    if (action === "created") {
      return {
        label: "Creación",
        tone: "success",
      };
    }

    if (action === "updated") {
      return {
        label: `Edición ${updateNumber ?? ""}`.trim(),
        tone: "info",
      };
    }

    if (action === "deleted") {
      return {
        label: "Eliminación",
        tone: "danger",
      };
    }

    return {
      label: action || "Movimiento",
      tone: "default",
    };
  };

  const renderHistoryValue = (type, value) => {
    if (type === "boolean") {
      const active =
        value === true || value === 1 || String(value).toLowerCase() === "true";
      return <Badge active={active}>{active ? "Activo" : "Inactivo"}</Badge>;
    }

    if (Array.isArray(value)) {
      if (!value.length) return <span style={{ color: "#94a3b8" }}>—</span>;

      return (
        <div style={S.historyValueWrap}>
          {value.map((item, idx) => (
            <Badge key={`${idx}-${item}`} tone="default">
              {item}
            </Badge>
          ))}
        </div>
      );
    }

    if (value === null || value === undefined || value === "") {
      return <span style={{ color: "#94a3b8" }}>—</span>;
    }

    if (
      String(value).toLowerCase() === "activo" ||
      String(value).toLowerCase() === "inactivo"
    ) {
      return <Badge active={String(value).toLowerCase() === "activo"}>{String(value)}</Badge>;
    }

    return <span style={{ color: "#0f172a", fontWeight: 700 }}>{String(value)}</span>;
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

  const Badge = ({ children, active = true, tone = null }) => {
    let border = active ? "#86efac" : "#fecaca";
    let background = active ? "#ecfdf5" : "#fef2f2";
    let color = active ? "#166534" : "#b91c1c";

    if (tone === "warning") {
      border = "#fdba74";
      background = "#fff7ed";
      color = "#c2410c";
    }

    if (tone === "default") {
      border = "#e2e8f0";
      background = "#f8fafc";
      color = "#0f172a";
    }

    if (tone === "info") {
      border = "#93c5fd";
      background = "#eff6ff";
      color = "#1e40af";
    }

    if (tone === "success") {
      border = "#86efac";
      background = "#ecfdf5";
      color = "#166534";
    }

    if (tone === "danger") {
      border = "#fecaca";
      background = "#fef2f2";
      color = "#b91c1c";
    }

    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${border}`,
          background,
          fontSize: 12,
          fontWeight: 800,
          color,
          textAlign: "center",
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
      gridTemplateColumns: "minmax(220px, 1fr) auto auto",
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
    select: {
      width: "100%",
      padding: "11px 12px",
      borderRadius: 12,
      border: "1px solid #dbeafe",
      background: "#f8fafc",
      outline: "none",
      minHeight: 44,
    },
    helper: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 700,
    },
    textarea: {
      width: "100%",
      padding: "12px 13px",
      borderRadius: 12,
      border: "1px solid #dbeafe",
      background: "#fff",
      outline: "none",
      minHeight: 100,
      boxSizing: "border-box",
      resize: "vertical",
      fontFamily: "inherit",
    },
    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border: "1px solid #e2e8f0",
      borderRadius: 16,
    },
    table: {
      width: "100%",
      minWidth: canViewUnidadHistory ? 980 : 760,
      borderCollapse: "separate",
      borderSpacing: 0,
      background: "#fff",
    },
    th: {
      textAlign: "center",
      fontSize: 12,
      color: "#475569",
      padding: "14px 12px",
      borderBottom: "1px solid #e2e8f0",
      background: "#f8fafc",
      position: "sticky",
      top: 0,
      zIndex: 1,
      fontWeight: 800,
      verticalAlign: "middle",
    },
    td: {
      padding: "14px 12px",
      borderBottom: "1px solid #f1f5f9",
      verticalAlign: "middle",
      fontSize: 13,
      color: "#0f172a",
      textAlign: "center",
    },
    pagination: {
      display: "flex",
      gap: 10,
      alignItems: "center",
      marginTop: 14,
      flexWrap: "wrap",
      justifyContent: "space-between",
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
      maxWidth: 820,
      maxHeight: "90vh",
      overflowY: "auto",
      background: "#fff",
      borderRadius: 18,
      border: "1px solid #e2e8f0",
      boxShadow: "0 25px 60px rgba(0,0,0,.18)",
    },
    historyModal: {
      width: "100%",
      maxWidth: 960,
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
    formGrid: {
      display: "grid",
      gridTemplateColumns: "1fr 1fr",
      gap: 14,
    },
    fieldWrap: {
      display: "flex",
      flexDirection: "column",
    },
    inputFull: {
      width: "100%",
      padding: "12px 13px",
      borderRadius: 12,
      border: "1px solid #dbeafe",
      background: "#fff",
      outline: "none",
      minHeight: 46,
      boxSizing: "border-box",
    },
    errorText: {
      color: "#b91c1c",
      fontSize: 12,
      fontWeight: 700,
      marginTop: 6,
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
    toggleWrap: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      minHeight: 46,
      padding: "10px 0",
      justifyContent: "flex-start",
    },
    toggleButton: {
      position: "relative",
      width: 58,
      height: 32,
      border: "none",
      borderRadius: 999,
      cursor: "pointer",
      transition: "all 0.2s ease",
      padding: 0,
      flexShrink: 0,
    },
    toggleThumb: {
      position: "absolute",
      top: 4,
      width: 24,
      height: 24,
      borderRadius: "50%",
      background: "#fff",
      boxShadow: "0 1px 3px rgba(0,0,0,.22)",
      transition: "all 0.2s ease",
    },
    historyCard: {
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      background: "#fff",
      overflow: "hidden",
    },
    historyCardHeader: {
      padding: "14px 16px",
      borderBottom: "1px solid #eef2f7",
      display: "flex",
      justifyContent: "space-between",
      gap: 12,
      alignItems: "flex-start",
      flexWrap: "wrap",
      background: "#f8fafc",
    },
    historyCardBody: {
      padding: 16,
      display: "flex",
      flexDirection: "column",
      gap: 12,
    },
    historyGrid: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
      gap: 12,
    },
    historyFieldBox: {
      border: "1px solid #e2e8f0",
      borderRadius: 12,
      background: "#fff",
      padding: 12,
      display: "flex",
      flexDirection: "column",
      gap: 8,
    },
    historyFieldLabel: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 800,
    },
    historySectionTitle: {
      fontSize: 13,
      color: "#334155",
      fontWeight: 900,
      marginBottom: 2,
    },
    historyValueWrap: {
      display: "flex",
      flexWrap: "wrap",
      gap: 6,
      alignItems: "center",
    },
    responsiveStyleTag: `
      @media (max-width: 860px) {
        .us-filter-row {
          grid-template-columns: 1fr !important;
        }
        .us-form-grid {
          grid-template-columns: 1fr !important;
        }
        .us-modal-full {
          max-width: 100% !important;
        }
      }

      @media (max-width: 560px) {
        .us-header-mobile {
          align-items: stretch !important;
        }
        .us-header-mobile button {
          width: 100%;
        }
        .us-pagination-mobile {
          justify-content: center !important;
        }
      }
    `,
  };

  return (
    <div style={S.page}>
      <style>{S.responsiveStyleTag}</style>

      <Card>
        <div style={S.headerTop} className="us-header-mobile">
          <div style={S.titleBlock}>
            <h2 style={{ margin: 0, fontSize: 24, color: "#0f172a" }}>Unidades de servicio</h2>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Administra unidades de servicio y su disponibilidad dentro del sistema.
            </div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Total registradas: <b>{meta.total}</b>
            </div>
          </div>

          {canCreateUnidades ? (
            <Btn variant="primary" onClick={openCreate}>
              <i className="fa-solid fa-plus" />
              Nueva unidad
            </Btn>
          ) : null}
        </div>

        <div style={S.filterRow} className="us-filter-row">
          <div>
            <div style={S.label}>Buscar unidad de servicio</div>
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
              placeholder="Buscar por nombre o descripción"
              style={S.input}
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
              <option value={5}>5 registros</option>
              <option value={10}>10 registros</option>
              <option value={20}>20 registros</option>
              <option value={25}>25 registros</option>
              <option value={50}>50 registros</option>
              <option value={75}>75 registros</option>
              <option value={100}>100 registros</option>
            </select>
          </div>

          <div>
            <div style={S.label}>Página actual</div>
            <div
              style={{
                ...S.input,
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                background: "#fff",
                color: "#334155",
                fontWeight: 700,
              }}
            >
              {page} de {meta.last_page}
            </div>
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
          <div style={{ marginTop: 12, color: "#b91c1c", fontWeight: 800 }}>
            {err}
          </div>
        ) : null}
      </Card>

      <Card>
        {loading ? (
          <div style={{ color: "#475569", fontWeight: 700 }}>Cargando unidades de servicio...</div>
        ) : (
          <>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Nombre</th>
                    <th style={S.th}>Descripción</th>
                    <th style={S.th}>Estado</th>
                    {canViewUnidadHistory ? (
                      <th style={{ ...S.th, width: 160 }}>Historial</th>
                    ) : null}
                    {canShowActionsColumn ? (
                      <th style={{ ...S.th, width: 160 }}>Acciones</th>
                    ) : null}
                  </tr>
                </thead>
                <tbody>
                  {rows.length ? (
                    rows.map((r) => {
                      const inUse = unidadHasAssignedUsers(r);

                      return (
                        <tr key={r.id}>
                          <td style={S.td}>
                            <div style={{ fontWeight: 800 }}>{r.nombre}</div>
                          </td>

                          <td style={S.td}>
                            <div style={{ color: "#475569", maxWidth: 320, margin: "0 auto" }}>
                              {r.descripcion || "—"}
                            </div>
                          </td>

                          <td style={S.td}>
                            <div
                              style={{
                                display: "flex",
                                gap: 8,
                                flexWrap: "wrap",
                                justifyContent: "center",
                              }}
                            >
                              <Badge active={!!r.activo}>
                                {r.activo ? "Activo" : "Inactivo"}
                              </Badge>

                              {inUse ? <Badge tone="warning">En uso</Badge> : null}
                            </div>
                          </td>

                          {canViewUnidadHistory ? (
                            <td style={{ ...S.td, textAlign: "center" }}>
                              <div
                                style={{
                                  display: "flex",
                                  justifyContent: "center",
                                  alignItems: "center",
                                }}
                              >
                                <Btn
                                  type="button"
                                  onClick={() => openHistory(r)}
                                  title="Ver historial"
                                  variant="default"
                                  style={{
                                    minWidth: 88,
                                    justifyContent: "center",
                                    padding: "6px 10px",
                                    margin: "0 auto",
                                    fontSize: 12,
                                    borderRadius: 9,
                                    gap: 5,
                                  }}
                                >
                                  <i className="fa-solid fa-clock-rotate-left" />
                                  Historial
                                </Btn>
                              </div>
                            </td>
                          ) : null}

                          {canShowActionsColumn ? (
                            <td style={S.td}>
                              <div
                                style={{
                                  display: "inline-flex",
                                  gap: 8,
                                  flexWrap: "nowrap",
                                  justifyContent: "center",
                                }}
                              >
                                {canEditUnidades ? (
                                  <IconBtn onClick={() => openEdit(r)} title="Editar" variant="primary">
                                    <i className="fa-solid fa-pen" />
                                  </IconBtn>
                                ) : null}

                                {canDeleteUnidades ? (
                                  <IconBtn
                                    disabled={deletingId === r.id || inUse}
                                    onClick={() => remove(r)}
                                    variant="danger"
                                    title={
                                      inUse
                                        ? "No se puede eliminar porque está asignada a usuarios"
                                        : "Eliminar"
                                    }
                                  >
                                    <i className="fa-solid fa-trash" />
                                  </IconBtn>
                                ) : null}
                              </div>
                            </td>
                          ) : null}
                        </tr>
                      );
                    })
                  ) : (
                    <tr>
                      <td
                        style={S.td}
                        colSpan={
                          3 +
                          (canViewUnidadHistory ? 1 : 0) +
                          (canShowActionsColumn ? 1 : 0)
                        }
                      >
                        Sin unidades de servicio registradas.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={S.pagination} className="us-pagination-mobile">
              <Btn disabled={!canPrev} onClick={() => setPage((p) => Math.max(1, p - 1))}>
                Anterior
              </Btn>

              <div style={{ fontSize: 13, color: "#475569", fontWeight: 700 }}>
                Mostrando página <b>{page}</b> de <b>{meta.last_page}</b>
              </div>

              <Btn disabled={!canNext} onClick={() => setPage((p) => p + 1)}>
                Siguiente
              </Btn>
            </div>
          </>
        )}
      </Card>

      {openForm && (
        <div style={S.modalOverlay} onClick={closeModal}>
          <div style={S.modal} className="us-modal-full" onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                {formMode === "create" ? "Crear unidad de servicio" : "Editar unidad de servicio"}
              </h3>
              <button type="button" style={S.xBtn} onClick={closeModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submit}>
              <div style={S.modalBody}>
                <div className="us-form-grid" style={S.formGrid}>
                  <div style={{ ...S.fieldWrap, gridColumn: "1 / -1" }}>
                    <div style={S.label}>Nombre</div>
                    <input
                      value={fNombre}
                      onChange={(e) => {
                        setFNombre(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, nombre: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.nombre ? "#fecaca" : "#dbeafe",
                      }}
                      placeholder="Ej. Mesa de ayuda"
                    />
                    {fieldErrors.nombre ? <div style={S.errorText}>{fieldErrors.nombre}</div> : null}
                  </div>

                  <div style={{ ...S.fieldWrap, gridColumn: "1 / -1" }}>
                    <div style={S.label}>Descripción</div>
                    <textarea
                      value={fDesc}
                      onChange={(e) => setFDesc(e.target.value)}
                      style={S.textarea}
                      placeholder="Opcional"
                    />
                    <div style={S.helper}>Opcional</div>
                  </div>

                  <div style={{ ...S.fieldWrap, gridColumn: "1 / -1" }}>
                    <div style={S.label}>Estado</div>
                    <div style={S.toggleWrap}>
                      <button
                        type="button"
                        onClick={() => setFActivo((prev) => !prev)}
                        aria-pressed={fActivo}
                        style={{
                          ...S.toggleButton,
                          background: fActivo ? "#22c55e" : "#cbd5e1",
                        }}
                      >
                        <span
                          style={{
                            ...S.toggleThumb,
                            left: fActivo ? 30 : 4,
                          }}
                        />
                      </button>

                      <span
                        style={{
                          fontSize: 14,
                          fontWeight: 800,
                          color: fActivo ? "#166534" : "#64748b",
                        }}
                      >
                        {fActivo ? "Activo" : "Inactivo"}
                      </span>
                    </div>
                    <div style={S.helper}>
                      Si está inactiva, puedes ocultarla para asignaciones nuevas.
                    </div>
                  </div>
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 800 }}>{err}</div> : null}
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closeModal}>
                  Cancelar
                </Btn>
                <Btn type="submit" disabled={saving} variant="primary">
                  {saving ? "Guardando..." : "Guardar"}
                </Btn>
              </div>
            </form>
          </div>
        </div>
      )}

      {openHistoryModal && (
        <div style={S.modalOverlay} onClick={closeHistoryModal}>
          <div
            style={S.historyModal}
            className="us-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <div>
                <h3 style={S.modalTitle}>
                  Historial de unidad de servicio
                  {historyUnidad?.nombre ? ` - ${historyUnidad.nombre}` : ""}
                </h3>
                <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
                  {historyUnidad?.nombre || "—"}
                </div>
              </div>

              <button
                type="button"
                style={S.xBtn}
                onClick={closeHistoryModal}
                aria-label="Cerrar"
              >
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {historyLoading ? (
                <div style={{ color: "#475569", fontWeight: 700 }}>Cargando historial...</div>
              ) : historyError ? (
                <div style={{ color: "#b91c1c", fontWeight: 800 }}>{historyError}</div>
              ) : enumeratedHistoryItems.length ? (
                enumeratedHistoryItems.map((item) => {
                  const metaAction = getHistoryActionMeta(item.action, item.updateNumber);
                  const changes = Array.isArray(item.changes) ? item.changes : [];
                  const snapshot =
                    item.snapshot && typeof item.snapshot === "object" ? item.snapshot : null;

                  return (
                    <div key={item.id} style={S.historyCard}>
                      <div style={S.historyCardHeader}>
                        <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                          <div
                            style={{
                              display: "flex",
                              gap: 8,
                              flexWrap: "wrap",
                              alignItems: "center",
                            }}
                          >
                            <Badge tone={metaAction.tone}>{metaAction.label}</Badge>
                            <Badge tone="default">{formatDateTime(item.created_at)}</Badge>
                          </div>

                          <div style={{ fontSize: 13, color: "#334155" }}>
                            <b>Por:</b> {item.actor?.name || "Sistema"}{" "}
                            {item.actor?.email ? `(${item.actor.email})` : ""}
                          </div>
                        </div>
                      </div>

                      <div style={S.historyCardBody}>
                        {item.action === "created" ? (
                          <>
                            <div style={S.historySectionTitle}>Datos iniciales</div>
                            <div style={S.historyGrid}>
                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Nombre</div>
                                {renderHistoryValue("text", snapshot?.nombre)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Descripción</div>
                                {renderHistoryValue("text", snapshot?.descripcion)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Estado</div>
                                {renderHistoryValue("text", snapshot?.estado)}
                              </div>
                            </div>
                          </>
                        ) : item.action === "updated" ? (
                          <>
                            <div style={S.historySectionTitle}>Cambios realizados</div>

                            {changes.length ? (
                              <div style={S.historyGrid}>
                                {changes.map((change, idx) => (
                                  <div key={`${item.id}-change-${idx}`} style={S.historyFieldBox}>
                                    <div style={S.historyFieldLabel}>{change.label}</div>

                                    <div
                                      style={{
                                        fontSize: 12,
                                        color: "#64748b",
                                        fontWeight: 800,
                                      }}
                                    >
                                      Antes
                                    </div>
                                    {renderHistoryValue(change.type, change.old)}

                                    <div
                                      style={{
                                        fontSize: 12,
                                        color: "#64748b",
                                        fontWeight: 800,
                                        marginTop: 4,
                                      }}
                                    >
                                      Ahora
                                    </div>
                                    {renderHistoryValue(change.type, change.new)}
                                  </div>
                                ))}
                              </div>
                            ) : (
                              <div style={{ color: "#64748b" }}>
                                No se detectaron cambios para mostrar.
                              </div>
                            )}
                          </>
                        ) : (
                          <>
                            <div style={S.historySectionTitle}>Datos al momento de eliminar</div>
                            <div style={S.historyGrid}>
                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Nombre</div>
                                {renderHistoryValue("text", snapshot?.nombre)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Descripción</div>
                                {renderHistoryValue("text", snapshot?.descripcion)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Estado</div>
                                {renderHistoryValue("text", snapshot?.estado)}
                              </div>
                            </div>
                          </>
                        )}
                      </div>
                    </div>
                  );
                })
              ) : (
                <div style={{ color: "#64748b", fontWeight: 700 }}>
                  Esta unidad de servicio todavía no tiene movimientos registrados.
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}