import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

function MultiSelectInlineBox({
  label,
  items = [],
  selectedValues = [],
  onChange,
  getLabel,
  getDescription,
  placeholder = "Buscar...",
  helper = "",
  error = "",
  disabled = false,
  height = 180,
}) {
  const [search, setSearch] = useState("");

  const selectedSet = useMemo(
    () => new Set((selectedValues || []).map((v) => Number(v))),
    [selectedValues]
  );

  const filteredItems = useMemo(() => {
    const term = search.trim().toLowerCase();
    if (!term) return items;

    return items.filter((item) => {
      const labelText = String(getLabel?.(item) || "").toLowerCase();
      const descText = String(getDescription?.(item) || "").toLowerCase();
      return labelText.includes(term) || descText.includes(term);
    });
  }, [items, search, getLabel, getDescription]);

  const selectedItems = useMemo(
    () => items.filter((item) => selectedSet.has(Number(item.id))),
    [items, selectedSet]
  );

  const toggleValue = (id) => {
    if (disabled) return;
    const numId = Number(id);

    if (selectedSet.has(numId)) {
      onChange(selectedValues.filter((v) => Number(v) !== numId));
    } else {
      onChange([...selectedValues, numId]);
    }
  };

  const removeValue = (id) => {
    if (disabled) return;
    const numId = Number(id);
    onChange(selectedValues.filter((v) => Number(v) !== numId));
  };

  return (
    <div style={{ display: "flex", flexDirection: "column" }}>
      <div style={{ fontSize: 12, color: "#64748b", fontWeight: 800, marginBottom: 6 }}>
        {label}
      </div>

      <div
        style={{
          border: `1px solid ${error ? "#fecaca" : "#dbeafe"}`,
          borderRadius: 12,
          background: disabled ? "#f8fafc" : "#fff",
          overflow: "hidden",
        }}
      >
        <div
          style={{
            padding: "10px 12px 8px 12px",
            borderBottom: "1px solid #eef2f7",
            minHeight: 52,
            display: "flex",
            flexWrap: "wrap",
            gap: 8,
            alignItems: "flex-start",
            alignContent: "flex-start",
          }}
        >
          {selectedItems.length ? (
            selectedItems.map((item) => (
              <span
                key={item.id}
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  padding: "6px 10px",
                  borderRadius: 999,
                  border: "1px solid #bfdbfe",
                  background: "#eff6ff",
                  color: "#1d4ed8",
                  fontSize: 12,
                  fontWeight: 800,
                  maxWidth: "100%",
                }}
              >
                <span
                  style={{
                    overflow: "hidden",
                    textOverflow: "ellipsis",
                    whiteSpace: "nowrap",
                    maxWidth: 180,
                  }}
                >
                  {getLabel(item)}
                </span>
                <button
                  type="button"
                  disabled={disabled}
                  onClick={() => removeValue(item.id)}
                  style={{
                    border: "none",
                    background: "transparent",
                    color: "#1d4ed8",
                    cursor: disabled ? "not-allowed" : "pointer",
                    fontWeight: 900,
                    padding: 0,
                    lineHeight: 1,
                  }}
                >
                  ✕
                </button>
              </span>
            ))
          ) : (
            <span style={{ fontSize: 13, color: "#94a3b8", paddingTop: 2 }}>
              Sin selección
            </span>
          )}
        </div>

        <div style={{ padding: 10, borderBottom: "1px solid #eef2f7" }}>
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder={placeholder}
            disabled={disabled}
            style={{
              width: "100%",
              padding: "10px 12px",
              borderRadius: 10,
              border: "1px solid #dbeafe",
              background: disabled ? "#f8fafc" : "#fff",
              outline: "none",
              minHeight: 40,
              boxSizing: "border-box",
            }}
          />
        </div>

        <div
          style={{
            maxHeight: height,
            overflowY: "auto",
            background: "#fff",
          }}
        >
          {filteredItems.length ? (
            filteredItems.map((item, idx) => {
              const checked = selectedSet.has(Number(item.id));

              return (
                <label
                  key={item.id}
                  style={{
                    display: "flex",
                    alignItems: "flex-start",
                    gap: 10,
                    padding: "10px 12px",
                    borderBottom:
                      idx !== filteredItems.length - 1 ? "1px solid #eef2f7" : "none",
                    cursor: disabled ? "not-allowed" : "pointer",
                    background: checked ? "#f8fbff" : "#fff",
                  }}
                >
                  <input
                    type="checkbox"
                    checked={checked}
                    disabled={disabled}
                    onChange={() => toggleValue(item.id)}
                    style={{ marginTop: 3 }}
                  />
                  <div style={{ minWidth: 0 }}>
                    <div
                      style={{
                        fontSize: 13,
                        fontWeight: 700,
                        color: "#0f172a",
                      }}
                    >
                      {getLabel(item)}
                    </div>
                    {getDescription?.(item) ? (
                      <div
                        style={{
                          marginTop: 2,
                          fontSize: 12,
                          color: "#64748b",
                          lineHeight: 1.35,
                        }}
                      >
                        {getDescription(item)}
                      </div>
                    ) : null}
                  </div>
                </label>
              );
            })
          ) : (
            <div
              style={{
                padding: "12px",
                fontSize: 12,
                color: "#64748b",
              }}
            >
              No hay resultados para mostrar.
            </div>
          )}
        </div>
      </div>

      {error ? (
        <div style={{ color: "#b91c1c", fontSize: 12, fontWeight: 700, marginTop: 6 }}>
          {error}
        </div>
      ) : helper ? (
        <div style={{ fontSize: 12, color: "#64748b", fontWeight: 700, marginTop: 6 }}>
          {helper}
        </div>
      ) : null}
    </div>
  );
}

export default function AdminUsers() {
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

  const canCreateUsers = hasPermission("usuarios.create");
  const canEditUsers = hasPermission("usuarios.edit");
  const canDeleteUsers = hasPermission("usuarios.delete");
  const canShowActionsColumn = canEditUsers || canDeleteUsers;
  const canViewUserHistory = isAdmin;

  const myUserId = Number(me?.id || 0);

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

  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loadingUsers, setLoadingUsers] = useState(false);

  const [empresasAll, setEmpresasAll] = useState([]);
  const [gruposAll, setGruposAll] = useState([]);
  const [unidadesServicioAll, setUnidadesServicioAll] = useState([]);
  const [loadingCats, setLoadingCats] = useState(false);

  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  const [perPage, setPerPage] = useState(25);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create");
  const [editingId, setEditingId] = useState(null);

  const [fName, setFName] = useState("");
  const [fEmail, setFEmail] = useState("");
  const [fPassword, setFPassword] = useState("");
  const [fRole, setFRole] = useState("");
  const [fActivo, setFActivo] = useState(true);
  const [fEmpresas, setFEmpresas] = useState([]);
  const [fGrupos, setFGrupos] = useState([]);
  const [fUnidadesServicio, setFUnidadesServicio] = useState([]);

  const [saving, setSaving] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);

  const [fieldErrors, setFieldErrors] = useState({});

  const [openHistoryModal, setOpenHistoryModal] = useState(false);
  const [historyUser, setHistoryUser] = useState(null);
  const [historyLoading, setHistoryLoading] = useState(false);
  const [historyItems, setHistoryItems] = useState([]);
  const [historyError, setHistoryError] = useState("");

  const enumeratedHistoryItems = useMemo(() => {
    let updateCounter = 0;
  
    return historyItems.map((item) => {
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

  const loadRolesNames = async () => {
    const data = await apiGet("/admin/roles");
    setRoles(data.roles || []);
  };

  const loadCatalogs = async () => {
    setLoadingCats(true);
    try {
      const [eData, gData, usData] = await Promise.all([
        apiGet("/admin/empresas?per_page=1000&page=1&q="),
        apiGet("/admin/grupos?per_page=1000&page=1&q="),
        apiGet("/admin/unidades-servicio?per_page=1000&page=1&q="),
      ]);
  
      const onlyActive = (items) =>
        (Array.isArray(items) ? items : []).filter(
          (item) => item?.activo === true || Number(item?.activo) === 1
        );
  
      setEmpresasAll(onlyActive(eData?.data));
      setGruposAll(onlyActive(gData?.data));
      setUnidadesServicioAll(onlyActive(usData?.data));
    } finally {
      setLoadingCats(false);
    }
  };

  const loadUsers = async () => {
    setErr("");
    setLoadingUsers(true);
    try {
      const data = await apiGet(
        `/admin/users?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`
      );
      setUsers(data.data || []);
      setMeta({
        last_page: data.last_page || 1,
        total: data.total || 0,
      });
    } catch (e) {
      setErr(e?.message || "Error cargando usuarios");
    } finally {
      setLoadingUsers(false);
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
    (async () => {
      try {
        await Promise.all([
          loadRolesNames(),
          loadCatalogs(),
        ]);
      } catch {
        //
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    loadUsers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, perPage, page]);

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
  }, [loadingUsers, users, meta.last_page, meta.total, openForm, openHistoryModal]);

  const resetUserForm = () => {
    setEditingId(null);
    setFName("");
    setFEmail("");
    setFPassword("");
    setFRole("");
    setFActivo(true);
    setFEmpresas([]);
    setFGrupos([]);
    setFUnidadesServicio([]);
    setFieldErrors({});
    setErr("");
  };

  const openCreate = () => {
    if (!canCreateUsers) {
      setErr("No tienes permiso para crear usuarios.");
      return;
    }

    resetUserForm();
    setFormMode("create");
    setOpenForm(true);
  };

  const openEdit = (u) => {
    if (!canEditUsers) {
      setErr("No tienes permiso para editar usuarios.");
      return;
    }

    resetUserForm();
    setFormMode("edit");
    setEditingId(u.id);

    setFName(u.name || "");
    setFEmail(u.email || "");

    const roleValue = Array.isArray(u.roles) && u.roles.length ? u.roles[0] : "";
    setFRole(roleValue);

    setFActivo(u.activo === true || u.activo === 1);

    const empresaIds = Array.isArray(u.empresas)
      ? u.empresas.map((e) => Number(e?.id)).filter(Boolean)
      : Array.isArray(u.empresa_ids)
      ? u.empresa_ids.map(Number).filter(Boolean)
      : [];

    const grupoIds = Array.isArray(u.grupos)
      ? u.grupos.map((g) => Number(g?.id)).filter(Boolean)
      : Array.isArray(u.grupo_ids)
      ? u.grupo_ids.map(Number).filter(Boolean)
      : [];

    const unidadServicioIds = Array.isArray(u.unidades_servicio)
      ? u.unidades_servicio.map((us) => Number(us?.id)).filter(Boolean)
      : Array.isArray(u.unidad_servicio_ids)
      ? u.unidad_servicio_ids.map(Number).filter(Boolean)
      : [];

    setFEmpresas(empresaIds);
    setFGrupos(grupoIds);
    setFUnidadesServicio(unidadServicioIds);

    setOpenForm(true);
  };

  const closeUserModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
    setFieldErrors({});
  };

  const closeHistoryModal = () => {
    setOpenHistoryModal(false);
    setHistoryUser(null);
    setHistoryItems([]);
    setHistoryError("");
    setHistoryLoading(false);
  };

  const openHistory = async (u) => {
    if (!canViewUserHistory) {
      setErr("No tienes permiso para ver el historial de usuarios.");
      return;
    }

    setHistoryUser(u);
    setHistoryItems([]);
    setHistoryError("");
    setOpenHistoryModal(true);
    setHistoryLoading(true);

    try {
      const data = await apiGet(`/admin/users/${u.id}/history`);
      setHistoryItems(Array.isArray(data?.history) ? data.history : []);
    } catch (e) {
      setHistoryError(e?.message || "Error cargando historial del usuario");
    } finally {
      setHistoryLoading(false);
    }
  };

  const validateForm = () => {
    const actionText = formMode === "create" ? "crear" : "actualizar";
    const errors = {};

    if (!fName.trim()) {
      errors.name = `No se puede ${actionText} el usuario porque falta el nombre.`;
    }
    if (!fEmail.trim()) {
      errors.email = `No se puede ${actionText} el usuario porque falta el correo.`;
    }
    if (formMode === "create" && !fPassword.trim()) {
      errors.password = "No se puede crear el usuario porque falta la contraseña.";
    }
    if (!fRole) {
      errors.role = `No se puede ${actionText} el usuario porque falta el rol.`;
    }
    if (!fUnidadesServicio.length) {
      errors.unidad_servicio = `No se puede ${actionText} el usuario porque falta la unidad de servicio.`;
    }
    if (!fEmpresas.length) {
      errors.empresa = `No se puede ${actionText} el usuario porque falta la empresa.`;
    }
    if (!fGrupos.length) {
      errors.grupo = `No se puede ${actionText} el usuario porque falta el grupo.`;
    }

    setFieldErrors(errors);

    if (Object.keys(errors).length > 0) {
      const firstError = Object.values(errors)[0];
      setErr(firstError);
      return false;
    }

    return true;
  };

  const submitUserForm = async (e) => {
    e.preventDefault();
    setErr("");

    if (formMode === "create" && !canCreateUsers) {
      setErr("No tienes permiso para crear usuarios.");
      return;
    }

    if (formMode === "edit" && !canEditUsers) {
      setErr("No tienes permiso para editar usuarios.");
      return;
    }

    if (!validateForm()) return;

    setSaving(true);

    try {
      const payload = {
        name: fName.trim(),
        email: fEmail.trim(),
        roles: [fRole],
        activo: !!fActivo,
        empresa_ids: fEmpresas,
        grupo_ids: fGrupos,
        unidad_servicio_ids: fUnidadesServicio,
      };

      if (formMode === "create") {
        payload.password = fPassword.trim();
        await apiPost("/admin/users", payload);
        showToast("success", "✅ Usuario creado correctamente");
      } else {
        if (fPassword.trim()) payload.password = fPassword.trim();
        await apiPut(`/admin/users/${editingId}`, payload);
        showToast("info", "✏️ Usuario actualizado");
      }

      closeUserModal();
      await loadUsers();
    } catch (e2) {
      setErr(e2?.message || "Error guardando usuario");
    } finally {
      setSaving(false);
    }
  };

  const userHasAdminRole = (u) => {
    const rr = Array.isArray(u?.roles) ? u.roles : [];
    return rr.some((r) => String(r).toLowerCase() === "administrador");
  };

  const isCurrentLoggedUser = (u) => Number(u?.id) === myUserId;

  const isProtectedUser = (u) => userHasAdminRole(u) || isCurrentLoggedUser(u);

  const getDeleteTitle = (u) => {
    if (userHasAdminRole(u)) return "No se puede eliminar un usuario con rol Administrador";
    if (isCurrentLoggedUser(u)) return "No puedes eliminar tu propio usuario";
    return "Eliminar";
  };

  const deleteUser = async (u) => {
    if (!canDeleteUsers) {
      setErr("No tienes permiso para eliminar usuarios.");
      return;
    }

    if (userHasAdminRole(u)) {
      setErr("No se puede eliminar un usuario con rol Administrador.");
      return;
    }

    if (isCurrentLoggedUser(u)) {
      setErr("No puedes eliminar tu propio usuario.");
      return;
    }

    const ok = window.confirm(`¿Eliminar al usuario ${u.name} (${u.email})?`);
    if (!ok) return;

    setErr("");
    setConfirmingDelete(true);
    try {
      await apiDelete(`/admin/users/${u.id}`);
      showToast("danger", "🗑️ Usuario eliminado");
      await loadUsers();
    } catch (e) {
      setErr(e?.message || "Error eliminando usuario");
    } finally {
      setConfirmingDelete(false);
    }
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
    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border: "1px solid #e2e8f0",
      borderRadius: 16,
    },
    table: {
      width: "100%",
      minWidth: canViewUserHistory ? 1120 : 980,
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
      maxWidth: 760,
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
    toggleWrap: {
      display: "flex",
      alignItems: "center",
      gap: 12,
      minHeight: 46,
      padding: "10px 0",
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
    badgesWrap: {
      display: "flex",
      gap: 6,
      flexWrap: "wrap",
      justifyContent: "center",
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
        .users-filter-row {
          grid-template-columns: 1fr !important;
        }
        .users-form-grid {
          grid-template-columns: 1fr !important;
        }
        .users-modal-full {
          max-width: 100% !important;
        }
      }

      @media (max-width: 560px) {
        .users-header-mobile {
          align-items: stretch !important;
        }
        .users-header-mobile button {
          width: 100%;
        }
        .users-pagination-mobile {
          justify-content: center !important;
        }
      }
    `,
  };

  const roleLabels = (u) => {
    const rr = Array.isArray(u?.roles) ? u.roles : [];
    return rr.length ? rr : ["—"];
  };

  const empresaLabels = (u) => {
    if (Array.isArray(u?.empresas) && u.empresas.length) {
      return u.empresas.map((e) => e?.nombre).filter(Boolean);
    }
    return ["—"];
  };

  const grupoLabels = (u) => {
    if (Array.isArray(u?.grupos) && u.grupos.length) {
      return u.grupos.map((g) => g?.nombre_mostrar || g?.nombre).filter(Boolean);
    }
    return ["—"];
  };

  const unidadServicioLabels = (u) => {
    if (Array.isArray(u?.unidades_servicio) && u.unidades_servicio.length) {
      return u.unidades_servicio.map((us) => us?.nombre).filter(Boolean);
    }
    return ["—"];
  };

  const activeBadge = (u) => {
    const a = u?.activo !== undefined ? !!u.activo : true;
    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          gap: 6,
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${a ? "#86efac" : "#fecaca"}`,
          background: a ? "#ecfdf5" : "#fef2f2",
          color: a ? "#166534" : "#b91c1c",
          fontSize: 12,
          fontWeight: 800,
          textAlign: "center",
        }}
      >
        {a ? "Activo" : "Inactivo"}
      </span>
    );
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
      const active = value === true || value === 1 || String(value).toLowerCase() === "true";
      return <Badge tone={active ? "success" : "danger"}>{active ? "Activo" : "Inactivo"}</Badge>;
    }

    if (type === "password") {
      return <Badge tone="warn">{value || "Actualizada"}</Badge>;
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

  return (
    <div style={S.page}>
      <style>{S.responsiveStyleTag}</style>

      <Card>
        <div style={S.headerTop} className="users-header-mobile">
          <div style={S.titleBlock}>
            <h2 style={{ margin: 0, fontSize: 24, color: "#0f172a" }}>Usuarios</h2>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Administra usuarios, roles, empresa, grupo, unidad de servicio y estado.
            </div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Total registrados: <b>{meta.total}</b>
            </div>
          </div>

          {canCreateUsers ? (
            <Btn variant="primary" onClick={openCreate}>
              <i className="fa-solid fa-user-plus" />
              Nuevo usuario
            </Btn>
          ) : null}
        </div>

        <div style={S.filterRow} className="users-filter-row">
          <div>
            <div style={S.label}>Buscar usuario</div>
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
              placeholder="Buscar por nombre, correo, rol, unidad, empresa o grupo"
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
        {loadingUsers ? (
          <div style={{ color: "#475569", fontWeight: 700 }}>Cargando usuarios...</div>
        ) : (
          <>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Nombre</th>
                    <th style={S.th}>Correo</th>
                    <th style={S.th}>Rol</th>
                    <th style={S.th}>Unidad de servicio</th>
                    <th style={S.th}>Empresa</th>
                    <th style={S.th}>Grupo</th>
                    <th style={S.th}>Estado</th>
                    {canViewUserHistory ? <th style={{ ...S.th, width: 160 }}>Historial</th> : null}
                    {canShowActionsColumn ? <th style={{ ...S.th, width: 120 }}>Acciones</th> : null}
                  </tr>
                </thead>
                <tbody>
                  {users.length ? (
                    users.map((u) => (
                      <tr key={u.id}>
                        <td style={S.td}>
                          <div style={{ fontWeight: 800 }}>{u.name}</div>
                        </td>

                        <td style={S.td}>
                          <div style={{ color: "#334155" }}>{u.email}</div>
                        </td>

                        <td style={S.td}>
                          <div style={S.badgesWrap}>
                            {roleLabels(u).map((r, idx) => (
                              <Badge key={`${u.id}-role-${idx}`} tone="role">
                                {r}
                              </Badge>
                            ))}
                          </div>
                        </td>

                        <td style={S.td}>
                          <div style={S.badgesWrap}>
                            {unidadServicioLabels(u).map((label, idx) => (
                              <Badge key={`${u.id}-us-${idx}`}>{label}</Badge>
                            ))}
                          </div>
                        </td>

                        <td style={S.td}>
                          <div style={S.badgesWrap}>
                            {empresaLabels(u).map((label, idx) => (
                              <Badge key={`${u.id}-emp-${idx}`}>{label}</Badge>
                            ))}
                          </div>
                        </td>

                        <td style={S.td}>
                          <div style={S.badgesWrap}>
                            {grupoLabels(u).map((label, idx) => (
                              <Badge key={`${u.id}-grp-${idx}`}>{label}</Badge>
                            ))}
                          </div>
                        </td>

                        <td style={S.td}>{activeBadge(u)}</td>

                        {canViewUserHistory ? (
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
                                onClick={() => openHistory(u)}
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
                            <div style={{ display: "inline-flex", gap: 8, justifyContent: "center" }}>
                              {canEditUsers ? (
                                <IconBtn onClick={() => openEdit(u)} title="Editar" variant="primary">
                                  <i className="fa-solid fa-pen" />
                                </IconBtn>
                              ) : null}

                              {canDeleteUsers ? (
                                <IconBtn
                                  disabled={confirmingDelete || isProtectedUser(u)}
                                  onClick={() => deleteUser(u)}
                                  variant="danger"
                                  title={getDeleteTitle(u)}
                                >
                                  <i className="fa-solid fa-trash" />
                                </IconBtn>
                              ) : null}
                            </div>
                          </td>
                        ) : null}
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td
                        style={S.td}
                        colSpan={
                          7 +
                          (canViewUserHistory ? 1 : 0) +
                          (canShowActionsColumn ? 1 : 0)
                        }
                      >
                        Sin usuarios registrados.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={S.pagination} className="users-pagination-mobile">
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
        <div style={S.modalOverlay} onClick={closeUserModal}>
          <div
            style={S.modal}
            className="users-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                {formMode === "create" ? "Crear usuario" : "Editar usuario"}
              </h3>

              <button type="button" style={S.xBtn} onClick={closeUserModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitUserForm}>
              <div style={S.modalBody}>
                <div className="users-form-grid" style={S.formGrid}>
                  <div style={{ ...S.fieldWrap, gridColumn: "1 / -1" }}>
                    <div style={S.label}>Nombre</div>
                    <input
                      value={fName}
                      onChange={(e) => {
                        setFName(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, name: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.name ? "#fecaca" : "#dbeafe",
                      }}
                      placeholder="Ej. Juan Carlos Cruz"
                    />
                    {fieldErrors.name ? <div style={S.errorText}>{fieldErrors.name}</div> : null}
                  </div>

                  <div style={S.fieldWrap}>
                    <div style={S.label}>Correo</div>
                    <input
                      value={fEmail}
                      onChange={(e) => {
                        setFEmail(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, email: "" }));
                      }}
                      type="email"
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.email ? "#fecaca" : "#dbeafe",
                      }}
                      placeholder="correo@empresa.com"
                    />
                    {fieldErrors.email ? <div style={S.errorText}>{fieldErrors.email}</div> : null}
                  </div>

                  <div style={S.fieldWrap}>
                    <div style={S.label}>
                      Contraseña{" "}
                      <span style={S.helper}>
                        {formMode === "create" ? "(requerida)" : "(opcional)"}
                      </span>
                    </div>
                    <input
                      value={fPassword}
                      onChange={(e) => {
                        setFPassword(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, password: "" }));
                      }}
                      type="password"
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.password ? "#fecaca" : "#dbeafe",
                      }}
                      placeholder={
                        formMode === "edit" ? "Dejar en blanco para no cambiar" : "••••••••"
                      }
                    />
                    {fieldErrors.password ? (
                      <div style={S.errorText}>{fieldErrors.password}</div>
                    ) : null}
                  </div>

                  <div style={S.fieldWrap}>
                    <div style={S.label}>Rol</div>
                    <select
                      value={fRole}
                      onChange={(e) => {
                        setFRole(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, role: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.role ? "#fecaca" : "#dbeafe",
                      }}
                    >
                      <option value="">Selecciona un rol</option>
                      {roles.map((r) => (
                        <option key={r} value={r}>
                          {r}
                        </option>
                      ))}
                    </select>
                    {fieldErrors.role ? <div style={S.errorText}>{fieldErrors.role}</div> : null}
                  </div>

                  <div style={S.fieldWrap}>
                    <MultiSelectInlineBox
                      label="Unidades de servicio"
                      items={unidadesServicioAll}
                      selectedValues={fUnidadesServicio}
                      onChange={(vals) => {
                        setFUnidadesServicio(vals);
                        setFieldErrors((prev) => ({ ...prev, unidad_servicio: "" }));
                      }}
                      getLabel={(item) => item.nombre}
                      getDescription={(item) => item.descripcion || ""}
                      placeholder="Buscar unidad de servicio..."
                      helper="Selecciona una o varias unidades."
                      error={fieldErrors.unidad_servicio}
                      disabled={loadingCats}
                      height={140}
                    />
                  </div>

                  <div style={S.fieldWrap}>
                    <MultiSelectInlineBox
                      label="Empresa"
                      items={empresasAll}
                      selectedValues={fEmpresas}
                      onChange={(vals) => {
                        setFEmpresas(vals);
                        setFieldErrors((prev) => ({ ...prev, empresa: "" }));
                      }}
                      getLabel={(item) => item.nombre}
                      getDescription={(item) => item.razon_social || ""}
                      placeholder="Buscar empresa..."
                      helper="Selecciona una o varias empresas."
                      error={fieldErrors.empresa}
                      disabled={loadingCats}
                      height={140}
                    />
                  </div>

                  <div style={S.fieldWrap}>
                    <MultiSelectInlineBox
                      label="Grupo"
                      items={gruposAll}
                      selectedValues={fGrupos}
                      onChange={(vals) => {
                        setFGrupos(vals);
                        setFieldErrors((prev) => ({ ...prev, grupo: "" }));
                      }}
                      getLabel={(item) => item.nombre_mostrar || item.nombre}
                      getDescription={(item) => item.descripcion || ""}
                      placeholder="Buscar grupo..."
                      helper="Selecciona uno o varios grupos."
                      error={fieldErrors.grupo}
                      disabled={loadingCats}
                      height={140}
                    />
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
                  </div>
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 800 }}>{err}</div> : null}
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closeUserModal}>
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
            className="users-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <div>
                <h3 style={S.modalTitle}>
                  Historial de usuario {historyUser?.name ? `- ${historyUser.name}` : ""}
                </h3>
                <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
                  {historyUser?.email || "—"}
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
                  const snapshot = item.snapshot && typeof item.snapshot === "object" ? item.snapshot : null;

                  return (
                    <div key={item.id} style={S.historyCard}>
                      <div style={S.historyCardHeader}>
                        <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                          <div style={{ display: "flex", gap: 8, flexWrap: "wrap", alignItems: "center" }}>
                            <Badge tone={metaAction.tone}>{metaAction.label}</Badge>
                            <Badge tone="default">
                              {formatDateTime(item.created_at)}
                            </Badge>
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
                                {renderHistoryValue("text", snapshot?.name)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Correo</div>
                                {renderHistoryValue("text", snapshot?.email)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Rol</div>
                                {renderHistoryValue("list", snapshot?.roles)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Unidad de servicio</div>
                                {renderHistoryValue("list", snapshot?.unidades_servicio)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Empresa</div>
                                {renderHistoryValue("list", snapshot?.empresas)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Grupo</div>
                                {renderHistoryValue("list", snapshot?.grupos)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Estado</div>
                                {renderHistoryValue("boolean", snapshot?.activo)}
                              </div>

                              {changes.some((c) => c.field === "password") ? (
                                <div style={S.historyFieldBox}>
                                  <div style={S.historyFieldLabel}>Contraseña</div>
                                  {renderHistoryValue("password", "Establecida")}
                                </div>
                              ) : null}
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
                                <div style={S.historyFieldLabel}>Nombre</div>
                                {renderHistoryValue("text", snapshot?.name)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Correo</div>
                                {renderHistoryValue("text", snapshot?.email)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Rol</div>
                                {renderHistoryValue("list", snapshot?.roles)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Unidad de servicio</div>
                                {renderHistoryValue("list", snapshot?.unidades_servicio)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Empresa</div>
                                {renderHistoryValue("list", snapshot?.empresas)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Grupo</div>
                                {renderHistoryValue("list", snapshot?.grupos)}
                              </div>

                              <div style={S.historyFieldBox}>
                                <div style={S.historyFieldLabel}>Estado</div>
                                {renderHistoryValue("boolean", snapshot?.activo)}
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