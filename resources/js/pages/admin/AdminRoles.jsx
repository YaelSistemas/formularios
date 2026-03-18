import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminRoles() {
  const [err, setErr] = useState("");

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

  const perPage = 20;
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
  }, [q, page]);

  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openRoleModal) return;
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
  }, [loadingRoles, rolesList, meta.last_page, meta.total, openRoleModal]);

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
    resetRoleForm();
    setRoleMode("create");
    setOpenRoleModal(true);
    setLoadingRoleDetail(true);

    try {
      const perms = await loadPermissionsCatalog();
      const normalized = perms.map((p) => (typeof p === "string" ? p : p?.name)).filter(Boolean);
      setAllPermissions(normalized);
    } finally {
      setLoadingRoleDetail(false);
    }
  };

  const openEditRoleModal = async (r) => {
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

  const deleteRole = async (r) => {
    if (r.name === "Administrador") {
      setErr("No puedes eliminar el rol Administrador.");
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
    };

    const t = tones[tone] || tones.default;

    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${t.border}`,
          background: t.bg,
          color: t.fg,
          fontSize: 12,
          fontWeight: 800,
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
      gridTemplateColumns: "minmax(220px, 1fr) auto",
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
      minWidth: 900,
      borderCollapse: "separate",
      borderSpacing: 0,
      background: "#fff",
    },
    th: {
      textAlign: "left",
      fontSize: 12,
      color: "#475569",
      padding: "14px 12px",
      borderBottom: "1px solid #e2e8f0",
      background: "#f8fafc",
      position: "sticky",
      top: 0,
      zIndex: 1,
      fontWeight: 800,
    },
    td: {
      padding: "14px 12px",
      borderBottom: "1px solid #f1f5f9",
      verticalAlign: "middle",
      fontSize: 13,
      color: "#0f172a",
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
      gap: 16,
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
      gap: 10,
    },
    permsBox: {
      maxHeight: 360,
      overflow: "auto",
      border: "1px solid #e2e8f0",
      padding: 12,
      borderRadius: 14,
      background: "#f8fafc",
    },
    permsGrid: {
      display: "grid",
      gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))",
      gap: 10,
    },
    permItem: {
      display: "flex",
      alignItems: "center",
      gap: 8,
      padding: "10px 12px",
      borderRadius: 12,
      border: "1px solid #e2e8f0",
      background: "#fff",
      fontWeight: 700,
      color: "#0f172a",
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
    const isAdmin = r.name === "Administrador";
    return (
      <Badge tone={isAdmin ? "system" : "role"}>
        {isAdmin ? "Sistema" : "Normal"}
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

          <Btn variant="primary" onClick={openCreateRoleModal}>
            <i className="fa-solid fa-plus" />
            Nuevo rol
          </Btn>
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
            <div style={S.label}>Página actual</div>
            <div
              style={{
                ...S.input,
                display: "flex",
                alignItems: "center",
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
                    <th style={S.th}>ID</th>
                    <th style={S.th}>Nombre interno</th>
                    <th style={S.th}>Nombre a mostrar</th>
                    <th style={S.th}>Descripción</th>
                    <th style={S.th}>Tipo</th>
                    <th style={{ ...S.th, width: 120, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {rolesList.length ? (
                    rolesList.map((r) => {
                      const isAdmin = r.name === "Administrador";

                      return (
                        <tr key={r.id}>
                          <td style={S.td}>{r.id}</td>
                          <td style={S.td}>
                            <div style={{ fontWeight: 800 }}>{r.name}</div>
                          </td>
                          <td style={S.td}>
                            <div style={{ fontWeight: 700 }}>
                              {r.nombre_mostrar || "—"}
                            </div>
                          </td>
                          <td style={S.td}>
                            <div style={{ color: "#475569", maxWidth: 280 }}>
                              {r.descripcion || "—"}
                            </div>
                          </td>
                          <td style={S.td}>{roleTypeBadge(r)}</td>
                          <td style={{ ...S.td, textAlign: "right" }}>
                            <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                              <IconBtn
                                onClick={() => openEditRoleModal(r)}
                                title="Editar"
                                variant="primary"
                              >
                                <i className="fa-solid fa-pen" />
                              </IconBtn>

                              <IconBtn
                                onClick={() => deleteRole(r)}
                                disabled={deletingRoleId === r.id || isAdmin}
                                title={isAdmin ? "No se puede eliminar" : "Eliminar"}
                                variant="danger"
                              >
                                <i className="fa-solid fa-trash" />
                              </IconBtn>
                            </div>
                          </td>
                        </tr>
                      );
                    })
                  ) : (
                    <tr>
                      <td style={S.td} colSpan={6}>
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
                  <div style={{ color: "#475569", fontWeight: 700 }}>Cargando datos del rol...</div>
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
                        {fieldErrors.name ? <div style={S.errorText}>{fieldErrors.name}</div> : null}
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
                        <div style={S.permsBox}>
                          {allPermissions.length ? (
                            <div style={S.permsGrid}>
                              {allPermissions.map((p) => (
                                <label key={p} style={S.permItem}>
                                  <input
                                    type="checkbox"
                                    checked={selectedPermissions.includes(p)}
                                    onChange={() => togglePermission(p)}
                                  />
                                  <span>{p}</span>
                                </label>
                              ))}
                            </div>
                          ) : (
                            <div style={{ fontSize: 12, color: "#64748b" }}>
                              No hay permisos creados. Ve a “Permisos” y crea algunos.
                            </div>
                          )}
                        </div>
                      )}
                    </div>

                    {err ? <div style={{ color: "#b91c1c", fontWeight: 800 }}>{err}</div> : null}
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
    </div>
  );
}