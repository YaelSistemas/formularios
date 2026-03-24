import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

function PermissionModuleGroup({
  title,
  permissions,
  styles,
  selectedPermissions,
  togglePermission,
  roleIsAdmin,
  formatPermissionLabel,
  formatPermissionHint,
}) {
  if (!permissions?.length) return null;

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
      <div
        style={{
          fontSize: 13,
          fontWeight: 900,
          color: "#0f172a",
        }}
      >
        {title}
      </div>

      <div style={styles.permsGrid}>
        {permissions.map((p) => (
          <label key={p} style={styles.permItem}>
            <input
              type="checkbox"
              checked={selectedPermissions.includes(p)}
              onChange={() => togglePermission(p)}
              disabled={roleIsAdmin}
              style={{ marginTop: 2 }}
            />
            <span style={styles.permContent}>
              <span style={styles.permTitle}>{formatPermissionLabel(p)}</span>
              <span style={styles.permHint}>{formatPermissionHint(p)}</span>
            </span>
          </label>
        ))}
      </div>
    </div>
  );
}

export default function AdminRoles() {
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

  const canCreateRoles = hasPermission("roles.create");
  const canEditRoles = hasPermission("roles.edit");
  const canDeleteRoles = hasPermission("roles.delete");
  const canShowActionsColumn = canEditRoles || canDeleteRoles;
  const canViewRoleHistory = isAdmin;

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

  const [rolesList, setRolesList] = useState([]);
  const [loadingRoles, setLoadingRoles] = useState(false);

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

  const [openRoleModal, setOpenRoleModal] = useState(false);
  const [roleMode, setRoleMode] = useState("create");
  const [editingRoleId, setEditingRoleId] = useState(null);

  const [roleName, setRoleName] = useState("");
  const [roleDisplayName, setRoleDisplayName] = useState("");
  const [roleDescription, setRoleDescription] = useState("");
  const [allPermissions, setAllPermissions] = useState([]);
  const [selectedPermissions, setSelectedPermissions] = useState([]);
  const [roleIsAdmin, setRoleIsAdmin] = useState(false);

  const [savingRoleModal, setSavingRoleModal] = useState(false);
  const [loadingRoleDetail, setLoadingRoleDetail] = useState(false);
  const [deletingRoleId, setDeletingRoleId] = useState(null);

  const [fieldErrors, setFieldErrors] = useState({});

  const [openHistoryModal, setOpenHistoryModal] = useState(false);
  const [historyRole, setHistoryRole] = useState(null);
  const [historyLoading, setHistoryLoading] = useState(false);
  const [historyItems, setHistoryItems] = useState([]);
  const [historyError, setHistoryError] = useState("");

  const loadRolesList = async () => {
    setErr("");
    setLoadingRoles(true);
    try {
      const data = await apiGet(
        `/admin/roles-list?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`
      );

      if (Array.isArray(data?.data)) {
        setRolesList(data.data || []);
        setMeta({
          last_page: data.last_page || 1,
          total: data.total || 0,
        });
      } else {
        const arr = Array.isArray(data?.roles) ? data.roles : [];
        setRolesList(arr);
        setMeta({ last_page: 1, total: arr.length });
      }
    } catch (e) {
      setErr(e?.message || "Error cargando roles");
    } finally {
      setLoadingRoles(false);
    }
  };

  const loadPermissionsCatalog = async () => {
    try {
      const data = await apiGet("/admin/permissions");
      if (Array.isArray(data?.permissions)) {
        return data.permissions;
      }
      if (Array.isArray(data?.data)) {
        return data.data;
      }
      return [];
    } catch {
      return [];
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
    loadRolesList();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    loadRolesList();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, page, perPage]);

  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openRoleModal || openHistoryModal) return;
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
  }, [loadingRoles, rolesList, meta.last_page, meta.total, openRoleModal, openHistoryModal]);

  const resetRoleForm = () => {
    setEditingRoleId(null);
    setRoleName("");
    setRoleDisplayName("");
    setRoleDescription("");
    setAllPermissions([]);
    setSelectedPermissions([]);
    setRoleIsAdmin(false);
    setFieldErrors({});
    setErr("");
  };

  const openCreateRoleModal = async () => {
    if (!canCreateRoles) {
      setErr("No tienes permiso para crear roles.");
      return;
    }

    resetRoleForm();
    setRoleMode("create");
    setOpenRoleModal(true);
    setLoadingRoleDetail(true);

    try {
      const perms = await loadPermissionsCatalog();
      const normalized = perms
        .map((p) => (typeof p === "string" ? p : p?.name))
        .filter(Boolean);
      setAllPermissions(normalized);
    } finally {
      setLoadingRoleDetail(false);
    }
  };

  const openEditRoleModal = async (r) => {
    if (!canEditRoles) {
      setErr("No tienes permiso para editar roles.");
      return;
    }

    resetRoleForm();
    setRoleMode("edit");
    setEditingRoleId(r.id);
    setOpenRoleModal(true);
    setLoadingRoleDetail(true);

    try {
      const data = await apiGet(`/admin/roles-list/${r.id}`);

      const role = data?.role || {};
      const perms = Array.isArray(data?.all_permissions) ? data.all_permissions : [];
      const selected = Array.isArray(role?.permissions) ? role.permissions : [];

      setRoleName(role.name || "");
      setRoleDisplayName(role.nombre_mostrar || "");
      setRoleDescription(role.descripcion || "");
      setAllPermissions(perms);
      setSelectedPermissions(selected);
      setRoleIsAdmin(!!data?.is_admin_role);
    } catch (e) {
      setErr(e?.message || "Error cargando el rol");
      setOpenRoleModal(false);
    } finally {
      setLoadingRoleDetail(false);
    }
  };

  const closeRoleModal = () => {
    setOpenRoleModal(false);
    setSavingRoleModal(false);
    setLoadingRoleDetail(false);
    setErr("");
    setFieldErrors({});
  };

  const closeHistoryModal = () => {
    setOpenHistoryModal(false);
    setHistoryRole(null);
    setHistoryItems([]);
    setHistoryError("");
    setHistoryLoading(false);
  };

  const openHistory = async (r) => {
    if (!canViewRoleHistory) {
      setErr("No tienes permiso para ver el historial de roles.");
      return;
    }

    setHistoryRole(r);
    setHistoryItems([]);
    setHistoryError("");
    setOpenHistoryModal(true);
    setHistoryLoading(true);

    try {
      const data = await apiGet(`/admin/roles-list/${r.id}/history`);
      setHistoryItems(Array.isArray(data?.history) ? data.history : []);
    } catch (e) {
      setHistoryError(e?.message || "Error cargando historial del rol");
    } finally {
      setHistoryLoading(false);
    }
  };

  const togglePermission = (permName) => {
    if (roleIsAdmin) return;

    setSelectedPermissions((prev) => {
      if (prev.includes(permName)) {
        return prev.filter((p) => p !== permName);
      }
      return [...prev, permName];
    });
  };

  const validateRoleForm = () => {
    const actionText = roleMode === "create" ? "crear" : "actualizar";
    const errors = {};

    if (!roleName.trim()) {
      errors.name = `No se puede ${actionText} el rol porque falta el nombre interno.`;
    }

    if (!roleDisplayName.trim()) {
      errors.nombre_mostrar = `No se puede ${actionText} el rol porque falta el nombre a mostrar.`;
    }

    if (!roleDescription.trim()) {
      errors.descripcion = `No se puede ${actionText} el rol porque falta la descripción.`;
    }

    setFieldErrors(errors);

    if (Object.keys(errors).length > 0) {
      setErr(Object.values(errors)[0]);
      return false;
    }

    return true;
  };

  const submitRoleModal = async (e) => {
    e.preventDefault();
    setErr("");

    if (roleMode === "create" && !canCreateRoles) {
      setErr("No tienes permiso para crear roles.");
      return;
    }

    if (roleMode === "edit" && !canEditRoles) {
      setErr("No tienes permiso para editar roles.");
      return;
    }

    if (!validateRoleForm()) return;

    setSavingRoleModal(true);

    try {
      const payload = {
        name: roleName.trim(),
        nombre_mostrar: roleDisplayName.trim(),
        descripcion: roleDescription.trim(),
        permissions: roleIsAdmin ? [] : selectedPermissions,
      };

      if (roleMode === "create") {
        await apiPost("/admin/roles-list", payload);
        showToast("success", "✅ Rol creado correctamente");
      } else {
        await apiPut(`/admin/roles-list/${editingRoleId}`, payload);
        showToast("info", "✏️ Rol actualizado");
      }

      closeRoleModal();
      await loadRolesList();
    } catch (e2) {
      setErr(e2?.message || "Error guardando rol");
    } finally {
      setSavingRoleModal(false);
    }
  };

  const roleHasAssignedUsers = (r) => {
    if (!r) return false;

    if (Number(r.users_count || 0) > 0) return true;
    if (Number(r.assigned_users_count || 0) > 0) return true;
    if (Number(r.total_users || 0) > 0) return true;
    if (Array.isArray(r.users) && r.users.length > 0) return true;

    return false;
  };

  const isProtectedRole = (r) => {
    if (!r) return false;
    return r.name === "Administrador" || roleHasAssignedUsers(r);
  };

  const deleteRole = async (r) => {
    if (!canDeleteRoles) {
      setErr("No tienes permiso para eliminar roles.");
      return;
    }

    if (r.name === "Administrador") {
      setErr("No puedes eliminar el rol Administrador.");
      return;
    }

    if (roleHasAssignedUsers(r)) {
      setErr("No puedes eliminar un rol que ya está asignado a uno o más usuarios.");
      return;
    }

    const ok = window.confirm(`¿Eliminar el rol "${r.nombre_mostrar || r.name}"?`);
    if (!ok) return;

    setErr("");
    setDeletingRoleId(r.id);

    try {
      await apiDelete(`/admin/roles-list/${r.id}`);
      showToast("danger", "🗑️ Rol eliminado");
      await loadRolesList();
    } catch (e2) {
      setErr(e2?.message || "Error eliminando rol");
    } finally {
      setDeletingRoleId(null);
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
      return <Badge tone={active ? "success" : "danger"}>{active ? "Sí" : "No"}</Badge>;
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

  const Badge = ({ children, tone = "default" }) => {
    const tones = {
      default: { bg: "#f8fafc", border: "#e2e8f0", fg: "#0f172a" },
      role: { bg: "#f1f5f9", border: "#cbd5e1", fg: "#334155" },
      system: { bg: "#eff6ff", border: "#bfdbfe", fg: "#1d4ed8" },
      warning: { bg: "#fff7ed", border: "#fdba74", fg: "#c2410c" },
      success: { bg: "#ecfdf5", border: "#86efac", fg: "#166534" },
      danger: { bg: "#fef2f2", border: "#fecaca", fg: "#b91c1c" },
      info: { bg: "#eff6ff", border: "#93c5fd", fg: "#1d4ed8" },
      warn: { bg: "#fffbeb", border: "#fde68a", fg: "#92400e" },
    };

    const t = tones[tone] || tones.default;

    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${t.border}`,
          background: t.bg,
          color: t.fg,
          fontSize: 12,
          fontWeight: 800,
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

  const moduleLabelMap = {
    admin: "panel admin",
    formularios: "formularios",
    usuarios: "usuarios",
    roles: "roles",
    permisos: "permisos",
    empresas: "empresas",
    grupos: "grupos",
    unidades_servicio: "unidades de servicio",
  };

  const actionLabelMap = {
    view: "Ver",
    create: "Crear",
    edit: "Editar",
    delete: "Eliminar",
    submit: "Enviar",
    assign: "Asignar",
    publish: "Publicar",
  };

  const formatPermissionLabel = (permission) => {
    if (!permission) return "Permiso";

    if (permission === "admin.panel.view") {
      return "Ver panel admin";
    }

    if (permission === "formularios.submissions.view") {
      return "Ver respuestas de formularios";
    }

    if (permission.startsWith("formularios.admin.")) {
      const action = permission.replace("formularios.admin.", "");
      return `${actionLabelMap[action] || action} formularios admin`;
    }

    const parts = String(permission).split(".");
    const [module, action, third] = parts;

    if (module === "formularios" && action === "submissions" && third === "view") {
      return "Ver respuestas de formularios";
    }

    if (parts.length >= 2) {
      return `${actionLabelMap[action] || action} ${moduleLabelMap[module] || module}`;
    }

    return permission;
  };

  const formatPermissionHint = (permission) => permission;

  const sortPermissions = (permissions) => {
    return [...permissions].sort((a, b) => {
      const aParts = String(a).split(".");
      const bParts = String(b).split(".");

      const aAction =
        a === "admin.panel.view"
          ? "view"
          : aParts[0] === "formularios" && aParts[1] === "submissions"
          ? "submissions_view"
          : aParts[aParts.length - 1];

      const bAction =
        b === "admin.panel.view"
          ? "view"
          : bParts[0] === "formularios" && bParts[1] === "submissions"
          ? "submissions_view"
          : bParts[bParts.length - 1];

      const customOrder = {
        view: 1,
        create: 2,
        edit: 3,
        delete: 4,
        submit: 5,
        submissions_view: 6,
        assign: 7,
        publish: 8,
      };

      return (customOrder[aAction] || 999) - (customOrder[bAction] || 999);
    });
  };

  const groupedPermissions = useMemo(() => {
    const groups = {
      userPanel: {
        formularios: [],
      },
      adminPanel: {
        admin: [],
        usuarios: [],
        roles: [],
        permisos: [],
        empresas: [],
        grupos: [],
        unidades_servicio: [],
        formularios_admin: [],
      },
      other: [],
    };

    (allPermissions || []).forEach((p) => {
      if (
        p === "formularios.view" ||
        p === "formularios.create" ||
        p === "formularios.edit" ||
        p === "formularios.delete" ||
        p === "formularios.submit" ||
        p === "formularios.submissions.view"
      ) {
        groups.userPanel.formularios.push(p);
        return;
      }

      if (p === "admin.panel.view") {
        groups.adminPanel.admin.push(p);
        return;
      }

      if (p.startsWith("usuarios.")) {
        groups.adminPanel.usuarios.push(p);
        return;
      }

      if (p.startsWith("roles.")) {
        groups.adminPanel.roles.push(p);
        return;
      }

      if (p.startsWith("permisos.")) {
        groups.adminPanel.permisos.push(p);
        return;
      }

      if (p.startsWith("empresas.")) {
        groups.adminPanel.empresas.push(p);
        return;
      }

      if (p.startsWith("grupos.")) {
        groups.adminPanel.grupos.push(p);
        return;
      }

      if (p.startsWith("unidades_servicio.")) {
        groups.adminPanel.unidades_servicio.push(p);
        return;
      }

      if (p.startsWith("formularios.admin.")) {
        groups.adminPanel.formularios_admin.push(p);
        return;
      }

      groups.other.push(p);
    });

    Object.keys(groups.userPanel).forEach((key) => {
      groups.userPanel[key] = sortPermissions(groups.userPanel[key]);
    });

    Object.keys(groups.adminPanel).forEach((key) => {
      groups.adminPanel[key] = sortPermissions(groups.adminPanel[key]);
    });

    groups.other = sortPermissions(groups.other);

    return groups;
  }, [allPermissions]);

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
    textarea: {
      width: "100%",
      padding: "12px 13px",
      borderRadius: 12,
      border: "1px solid #dbeafe",
      background: "#fff",
      outline: "none",
      minHeight: 110,
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
      minWidth: canViewRoleHistory ? 1000 : 840,
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
      maxWidth: 1120,
      maxHeight: "90vh",
      overflowY: "auto",
      background: "#fff",
      borderRadius: 18,
      border: "1px solid #e2e8f0",
      boxShadow: "0 25px 60px rgba(0,0,0,.18)",
    },
    historyModal: {
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
      gap: 18,
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
    helper: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 700,
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
    permsSection: {
      display: "flex",
      flexDirection: "column",
      gap: 12,
    },
    permsGroup: {
      border: "1px solid #e2e8f0",
      borderRadius: 16,
      background: "#f8fafc",
      padding: 14,
      display: "flex",
      flexDirection: "column",
      gap: 12,
    },
    permsGroupTitle: {
      fontSize: 14,
      fontWeight: 900,
      color: "#0f172a",
    },
    permsGroupDesc: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 700,
      marginTop: -4,
    },
    permsGrid: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit, minmax(260px, 1fr))",
      gap: 10,
    },
    permItem: {
      display: "flex",
      alignItems: "flex-start",
      gap: 10,
      padding: "12px 12px",
      borderRadius: 12,
      border: "1px solid #e2e8f0",
      background: "#fff",
      color: "#0f172a",
    },
    permContent: {
      display: "flex",
      flexDirection: "column",
      gap: 4,
      minWidth: 0,
    },
    permTitle: {
      fontWeight: 800,
      fontSize: 13,
      color: "#0f172a",
    },
    permHint: {
      fontSize: 11,
      color: "#64748b",
      wordBreak: "break-word",
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
        .roles-filter-row {
          grid-template-columns: 1fr !important;
        }
        .roles-form-grid {
          grid-template-columns: 1fr !important;
        }
        .roles-modal-full {
          max-width: 100% !important;
        }
      }

      @media (max-width: 560px) {
        .roles-header-mobile {
          align-items: stretch !important;
        }
        .roles-header-mobile button {
          width: 100%;
        }
        .roles-pagination-mobile {
          justify-content: center !important;
        }
      }
    `,
  };

  const roleTypeBadge = (r) => {
    const isSystemRole = r.name === "Administrador";
    return (
      <Badge tone={isSystemRole ? "system" : "role"}>
        {isSystemRole ? "Sistema" : "Normal"}
      </Badge>
    );
  };

  return (
    <div style={S.page}>
      <style>{S.responsiveStyleTag}</style>

      <Card>
        <div style={S.headerTop} className="roles-header-mobile">
          <div style={S.titleBlock}>
            <h2 style={{ margin: 0, fontSize: 24, color: "#0f172a" }}>Roles</h2>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Administra roles, descripción y permisos dentro del sistema.
            </div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Total registrados: <b>{meta.total}</b>
            </div>
          </div>

          {canCreateRoles ? (
            <Btn variant="primary" onClick={openCreateRoleModal}>
              <i className="fa-solid fa-plus" />
              Nuevo rol
            </Btn>
          ) : null}
        </div>

        <div style={S.filterRow} className="roles-filter-row">
          <div>
            <div style={S.label}>Buscar rol</div>
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
              placeholder="Buscar por nombre, nombre a mostrar o descripción"
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
        {loadingRoles ? (
          <div style={{ color: "#475569", fontWeight: 700 }}>Cargando roles...</div>
        ) : (
          <>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Nombre interno</th>
                    <th style={S.th}>Nombre a mostrar</th>
                    <th style={S.th}>Descripción</th>
                    <th style={S.th}>Tipo</th>
                    {canViewRoleHistory ? (
                      <th style={{ ...S.th, width: 160 }}>Historial</th>
                    ) : null}
                    {canShowActionsColumn ? (
                      <th style={{ ...S.th, width: 150 }}>Acciones</th>
                    ) : null}
                  </tr>
                </thead>
                <tbody>
                  {rolesList.length ? (
                    rolesList.map((r) => {
                      const protectedRole = isProtectedRole(r);
                      const usedByUsers = roleHasAssignedUsers(r);
                      const deleteTitle =
                        r.name === "Administrador"
                          ? "No se puede eliminar"
                          : usedByUsers
                          ? "Rol asignado a usuarios"
                          : "Eliminar";

                      return (
                        <tr key={r.id}>
                          <td style={S.td}>
                            <div style={{ fontWeight: 800 }}>{r.name}</div>
                          </td>

                          <td style={S.td}>
                            <div style={{ fontWeight: 700 }}>
                              {r.nombre_mostrar || "—"}
                            </div>
                          </td>

                          <td style={S.td}>
                            <div
                              style={{
                                color: "#475569",
                                maxWidth: 320,
                                margin: "0 auto",
                              }}
                            >
                              {r.descripcion || "—"}
                            </div>
                          </td>

                          <td style={S.td}>
                            <div style={{ display: "flex", justifyContent: "center" }}>
                              {usedByUsers && r.name !== "Administrador" ? (
                                <div
                                  style={{
                                    display: "flex",
                                    gap: 8,
                                    flexWrap: "wrap",
                                    justifyContent: "center",
                                  }}
                                >
                                  {roleTypeBadge(r)}
                                  <Badge tone="warning">En uso</Badge>
                                </div>
                              ) : (
                                roleTypeBadge(r)
                              )}
                            </div>
                          </td>

                          {canViewRoleHistory ? (
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
                              <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                                {canEditRoles ? (
                                  <IconBtn
                                    onClick={() => openEditRoleModal(r)}
                                    title="Editar"
                                    variant="primary"
                                  >
                                    <i className="fa-solid fa-pen" />
                                  </IconBtn>
                                ) : null}

                                {canDeleteRoles ? (
                                  <IconBtn
                                    onClick={() => deleteRole(r)}
                                    disabled={deletingRoleId === r.id || protectedRole}
                                    title={deleteTitle}
                                    variant="danger"
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
                          4 +
                          (canViewRoleHistory ? 1 : 0) +
                          (canShowActionsColumn ? 1 : 0)
                        }
                      >
                        Sin roles registrados.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={S.pagination} className="roles-pagination-mobile">
              <Btn
                disabled={!canPrev}
                onClick={() => setPage((p) => Math.max(1, p - 1))}
              >
                Anterior
              </Btn>

              <div style={{ fontSize: 13, color: "#475569", fontWeight: 700 }}>
                Mostrando página <b>{page}</b> de <b>{meta.last_page}</b>
              </div>

              <Btn
                disabled={!canNext}
                onClick={() => setPage((p) => p + 1)}
              >
                Siguiente
              </Btn>
            </div>
          </>
        )}
      </Card>

      {openRoleModal && (
        <div style={S.modalOverlay} onClick={closeRoleModal}>
          <div
            style={S.modal}
            className="roles-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                {roleMode === "create" ? "Crear rol" : "Editar rol"}
              </h3>
              <button type="button" style={S.xBtn} onClick={closeRoleModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitRoleModal}>
              <div style={S.modalBody}>
                {loadingRoleDetail ? (
                  <div style={{ color: "#475569", fontWeight: 700 }}>
                    Cargando datos del rol...
                  </div>
                ) : (
                  <>
                    <div className="roles-form-grid" style={S.formGrid}>
                      <div style={S.fieldWrap}>
                        <div style={S.label}>Nombre interno</div>
                        <input
                          value={roleName}
                          onChange={(e) => {
                            setRoleName(e.target.value);
                            setFieldErrors((prev) => ({ ...prev, name: "" }));
                          }}
                          style={{
                            ...S.inputFull,
                            borderColor: fieldErrors.name ? "#fecaca" : "#dbeafe",
                          }}
                          placeholder="Ej. supervisor"
                          disabled={roleIsAdmin}
                        />
                        {fieldErrors.name ? (
                          <div style={S.errorText}>{fieldErrors.name}</div>
                        ) : null}
                      </div>

                      <div style={S.fieldWrap}>
                        <div style={S.label}>Nombre a mostrar</div>
                        <input
                          value={roleDisplayName}
                          onChange={(e) => {
                            setRoleDisplayName(e.target.value);
                            setFieldErrors((prev) => ({ ...prev, nombre_mostrar: "" }));
                          }}
                          style={{
                            ...S.inputFull,
                            borderColor: fieldErrors.nombre_mostrar ? "#fecaca" : "#dbeafe",
                          }}
                          placeholder="Ej. Supervisor"
                          disabled={roleIsAdmin}
                        />
                        {fieldErrors.nombre_mostrar ? (
                          <div style={S.errorText}>{fieldErrors.nombre_mostrar}</div>
                        ) : null}
                      </div>

                      <div style={{ ...S.fieldWrap, gridColumn: "1 / -1" }}>
                        <div style={S.label}>Descripción</div>
                        <textarea
                          value={roleDescription}
                          onChange={(e) => {
                            setRoleDescription(e.target.value);
                            setFieldErrors((prev) => ({ ...prev, descripcion: "" }));
                          }}
                          style={{
                            ...S.textarea,
                            borderColor: fieldErrors.descripcion ? "#fecaca" : "#dbeafe",
                          }}
                          placeholder="Describe para qué sirve este rol"
                          disabled={roleIsAdmin}
                        />
                        {fieldErrors.descripcion ? (
                          <div style={S.errorText}>{fieldErrors.descripcion}</div>
                        ) : null}
                      </div>
                    </div>

                    <div style={S.permsSection}>
                      <div style={S.label}>Permisos</div>

                      {roleIsAdmin ? (
                        <div
                          style={{
                            padding: "12px 14px",
                            borderRadius: 12,
                            border: "1px solid #bfdbfe",
                            background: "#eff6ff",
                            color: "#1d4ed8",
                            fontWeight: 700,
                          }}
                        >
                          Este rol tiene acceso total por sistema. No requiere permisos específicos.
                        </div>
                      ) : (
                        <>
                          <div style={S.permsGroup}>
                            <div style={S.permsGroupTitle}>Permisos panel usuario</div>
                            <div style={S.permsGroupDesc}>
                              Accesos relacionados con captura, consulta y gestión de formularios del panel usuario.
                            </div>

                            <PermissionModuleGroup
                              title="Formularios"
                              permissions={groupedPermissions.userPanel.formularios}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />
                          </div>

                          <div style={S.permsGroup}>
                            <div style={S.permsGroupTitle}>Permisos panel admin</div>
                            <div style={S.permsGroupDesc}>
                              Accesos relacionados con administración del sistema, catálogos, usuarios, roles y formularios del panel admin.
                            </div>

                            <PermissionModuleGroup
                              title="Panel admin"
                              permissions={groupedPermissions.adminPanel.admin}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Usuarios"
                              permissions={groupedPermissions.adminPanel.usuarios}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Roles"
                              permissions={groupedPermissions.adminPanel.roles}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Permisos"
                              permissions={groupedPermissions.adminPanel.permisos}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Empresas"
                              permissions={groupedPermissions.adminPanel.empresas}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Grupos"
                              permissions={groupedPermissions.adminPanel.grupos}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Unidades de servicio"
                              permissions={groupedPermissions.adminPanel.unidades_servicio}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />

                            <PermissionModuleGroup
                              title="Formularios admin"
                              permissions={groupedPermissions.adminPanel.formularios_admin}
                              styles={S}
                              selectedPermissions={selectedPermissions}
                              togglePermission={togglePermission}
                              roleIsAdmin={roleIsAdmin}
                              formatPermissionLabel={formatPermissionLabel}
                              formatPermissionHint={formatPermissionHint}
                            />
                          </div>

                          {groupedPermissions.other.length ? (
                            <div style={S.permsGroup}>
                              <div style={S.permsGroupTitle}>Otros permisos</div>
                              <div style={S.permsGroupDesc}>
                                Permisos adicionales que no entran en las categorías anteriores.
                              </div>

                              <PermissionModuleGroup
                                title="Adicionales"
                                permissions={groupedPermissions.other}
                                styles={S}
                                selectedPermissions={selectedPermissions}
                                togglePermission={togglePermission}
                                roleIsAdmin={roleIsAdmin}
                                formatPermissionLabel={formatPermissionLabel}
                                formatPermissionHint={formatPermissionHint}
                              />
                            </div>
                          ) : null}
                        </>
                      )}
                    </div>

                    {err ? (
                      <div style={{ color: "#b91c1c", fontWeight: 800 }}>{err}</div>
                    ) : null}
                  </>
                )}
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closeRoleModal}>
                  Cancelar
                </Btn>
                <Btn
                  type="submit"
                  disabled={savingRoleModal || loadingRoleDetail || roleIsAdmin}
                  variant="primary"
                >
                  {savingRoleModal ? "Guardando..." : "Guardar"}
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
            className="roles-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <div>
                <h3 style={S.modalTitle}>
                  Historial de rol {historyRole?.nombre_mostrar ? `- ${historyRole.nombre_mostrar}` : ""}
                </h3>
                <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
                  {historyRole?.name || "—"}
                </div>
              </div>

              <button type="button" style={S.xBtn} onClick={closeHistoryModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {historyLoading ? (
                <div style={{ color: "#475569", fontWeight: 700 }}>
                  Cargando historial...
                </div>
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
                                <div style={S.historyFieldLabel}>Nombre interno</div>
                                {renderHistoryValue("text", snapshot?.name)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Nombre a mostrar</div>
                                {renderHistoryValue("text", snapshot?.nombre_mostrar)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Descripción</div>
                                {renderHistoryValue("text", snapshot?.descripcion)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Permisos</div>
                                {renderHistoryValue("list", snapshot?.permissions)}
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

                                    <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800 }}>
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
                                <div style={S.historyFieldLabel}>Nombre interno</div>
                                {renderHistoryValue("text", snapshot?.name)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Nombre a mostrar</div>
                                {renderHistoryValue("text", snapshot?.nombre_mostrar)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Descripción</div>
                                {renderHistoryValue("text", snapshot?.descripcion)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Permisos</div>
                                {renderHistoryValue("list", snapshot?.permissions)}
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
                  No hay historial para mostrar.
                </div>
              )}
            </div>

            <div style={S.modalFooter}>
              <Btn type="button" onClick={closeHistoryModal}>
                Cerrar
              </Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}