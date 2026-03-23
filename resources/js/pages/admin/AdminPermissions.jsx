import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminPermissions() {
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

  const canCreatePermissions = hasPermission("permisos.create");
  const canEditPermissions = hasPermission("permisos.edit");
  const canDeletePermissions = hasPermission("permisos.delete");
  const canShowActionsColumn = canEditPermissions || canDeletePermissions;

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

  const [permissions, setPermissions] = useState([]);
  const [loadingPerms, setLoadingPerms] = useState(false);

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

  const [openPermModal, setOpenPermModal] = useState(false);
  const [permMode, setPermMode] = useState("create");
  const [editingPermId, setEditingPermId] = useState(null);
  const [permName, setPermName] = useState("");
  const [savingPermModal, setSavingPermModal] = useState(false);
  const [deletingPermId, setDeletingPermId] = useState(null);
  const [fieldErrors, setFieldErrors] = useState({});

  const loadPermissions = async () => {
    setErr("");
    setLoadingPerms(true);
    try {
      const data = await apiGet(
        `/admin/permissions?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`
      );

      if (Array.isArray(data?.data)) {
        setPermissions(data.data || []);
        setMeta({
          last_page: data.last_page || 1,
          total: data.total || 0,
        });
      } else {
        const arr = Array.isArray(data?.permissions) ? data.permissions : [];
        setPermissions(arr);
        setMeta({ last_page: 1, total: arr.length });
      }
    } catch (e) {
      setErr(e?.message || "Error cargando permisos");
    } finally {
      setLoadingPerms(false);
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
    loadPermissions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    loadPermissions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, page, perPage]);

  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openPermModal) return;
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
  }, [loadingPerms, permissions, meta.last_page, meta.total, openPermModal]);

  const resetPermForm = () => {
    setEditingPermId(null);
    setPermName("");
    setFieldErrors({});
    setErr("");
  };

  const openCreatePermModal = () => {
    if (!canCreatePermissions) {
      setErr("No tienes permiso para crear permisos.");
      return;
    }

    resetPermForm();
    setPermMode("create");
    setOpenPermModal(true);
  };

  const openEditPermModal = (p) => {
    if (!canEditPermissions) {
      setErr("No tienes permiso para editar permisos.");
      return;
    }

    resetPermForm();
    setPermMode("edit");
    setEditingPermId(p.id);
    setPermName(p.name || "");
    setOpenPermModal(true);
  };

  const closePermModal = () => {
    setOpenPermModal(false);
    setSavingPermModal(false);
    setErr("");
    setFieldErrors({});
  };

  const validatePermForm = () => {
    const actionText = permMode === "create" ? "crear" : "actualizar";
    const errors = {};

    if (!permName.trim()) {
      errors.name = `No se puede ${actionText} el permiso porque falta el nombre.`;
    }

    setFieldErrors(errors);

    if (Object.keys(errors).length > 0) {
      setErr(Object.values(errors)[0]);
      return false;
    }

    return true;
  };

  const submitPermModal = async (e) => {
    e.preventDefault();
    setErr("");

    if (permMode === "create" && !canCreatePermissions) {
      setErr("No tienes permiso para crear permisos.");
      return;
    }

    if (permMode === "edit" && !canEditPermissions) {
      setErr("No tienes permiso para editar permisos.");
      return;
    }

    if (!validatePermForm()) return;

    setSavingPermModal(true);

    try {
      const payload = { name: permName.trim() };

      if (permMode === "create") {
        await apiPost("/admin/permissions", payload);
        showToast("success", "✅ Permiso creado correctamente");
      } else {
        await apiPut(`/admin/permissions/${editingPermId}`, payload);
        showToast("info", "✏️ Permiso actualizado");
      }

      closePermModal();
      await loadPermissions();
    } catch (e2) {
      setErr(e2?.message || "Error guardando permiso");
    } finally {
      setSavingPermModal(false);
    }
  };

  const permissionHasRoles = (p) => Number(p?.roles_count || 0) > 0;

  const deletePermission = async (p) => {
    if (!canDeletePermissions) {
      setErr("No tienes permiso para eliminar permisos.");
      return;
    }

    if (permissionHasRoles(p)) {
      setErr("No se puede eliminar el permiso porque está asignado a uno o más roles.");
      return;
    }

    const ok = window.confirm(`¿Eliminar el permiso "${p.name}"?`);
    if (!ok) return;

    setErr("");
    setDeletingPermId(p.id);

    try {
      await apiDelete(`/admin/permissions/${p.id}`);
      showToast("danger", "🗑️ Permiso eliminado");
      await loadPermissions();
    } catch (e2) {
      setErr(e2?.message || "Error eliminando permiso");
    } finally {
      setDeletingPermId(null);
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
      formatOk: { bg: "#ecfdf5", border: "#86efac", fg: "#166534" },
      formatWarn: { bg: "#fff7ed", border: "#fdba74", fg: "#c2410c" },
      warning: { bg: "#fef2f2", border: "#fecaca", fg: "#b91c1c" },
      info: { bg: "#eff6ff", border: "#93c5fd", fg: "#1e40af" },
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
    helper: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 700,
    },
    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border: "1px solid #e2e8f0",
      borderRadius: 16,
    },
    table: {
      width: "100%",
      minWidth: 760,
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
    badgesWrap: {
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      gap: 6,
      flexWrap: "wrap",
    },
    responsiveStyleTag: `
      @media (max-width: 860px) {
        .perms-filter-row {
          grid-template-columns: 1fr !important;
        }
        .perms-modal-full {
          max-width: 100% !important;
        }
      }

      @media (max-width: 560px) {
        .perms-header-mobile {
          align-items: stretch !important;
        }
        .perms-header-mobile button {
          width: 100%;
        }
        .perms-pagination-mobile {
          justify-content: center !important;
        }
      }
    `,
  };

  return (
    <div style={S.page}>
      <style>{S.responsiveStyleTag}</style>

      <Card>
        <div style={S.headerTop} className="perms-header-mobile">
          <div style={S.titleBlock}>
            <h2 style={{ margin: 0, fontSize: 24, color: "#0f172a" }}>Permisos</h2>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Administra permisos del sistema y su nomenclatura.
            </div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Total registrados: <b>{meta.total}</b>
            </div>
          </div>

          {canCreatePermissions ? (
            <Btn variant="primary" onClick={openCreatePermModal}>
              <i className="fa-solid fa-plus" />
              Nuevo permiso
            </Btn>
          ) : null}
        </div>

        <div style={S.filterRow} className="perms-filter-row">
          <div>
            <div style={S.label}>Buscar permiso</div>
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
              placeholder="Buscar por nombre del permiso"
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
        {loadingPerms ? (
          <div style={{ color: "#475569", fontWeight: 700 }}>Cargando permisos...</div>
        ) : (
          <>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Permiso</th>
                    <th style={S.th}>Formato</th>
                    <th style={S.th}>Roles asignados</th>
                    {canShowActionsColumn ? (
                      <th style={{ ...S.th, width: 140 }}>Acciones</th>
                    ) : null}
                  </tr>
                </thead>
                <tbody>
                  {permissions.length ? (
                    permissions.map((p) => {
                      const okFormat = String(p.name || "").includes(".");
                      const hasRoles = permissionHasRoles(p);

                      return (
                        <tr key={p.id}>
                          <td style={S.td}>
                            <div style={{ fontWeight: 800 }}>{p.name}</div>
                          </td>

                          <td style={S.td}>
                            <Badge tone={okFormat ? "formatOk" : "formatWarn"}>
                              {okFormat ? "modulo.accion" : "sin punto"}
                            </Badge>
                          </td>

                          <td style={S.td}>
                            <div style={S.badgesWrap}>
                              <Badge tone={hasRoles ? "warning" : "info"}>
                                {Number(p.roles_count || 0)} rol{Number(p.roles_count || 0) === 1 ? "" : "es"}
                              </Badge>
                            </div>
                          </td>

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
                                {canEditPermissions ? (
                                  <IconBtn
                                    onClick={() => openEditPermModal(p)}
                                    title="Editar"
                                    variant="primary"
                                  >
                                    <i className="fa-solid fa-pen" />
                                  </IconBtn>
                                ) : null}

                                {canDeletePermissions ? (
                                  <IconBtn
                                    onClick={() => deletePermission(p)}
                                    disabled={deletingPermId === p.id || hasRoles}
                                    variant="danger"
                                    title={
                                      hasRoles
                                        ? "No se puede eliminar porque está asignado a roles"
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
                      <td style={S.td} colSpan={canShowActionsColumn ? 4 : 3}>
                        Sin permisos registrados.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={{ marginTop: 10, fontSize: 12, color: "#64748b" }}>
              Sugerencia: usa formato <b>modulo.accion</b>, por ejemplo{" "}
              <b>tickets.view</b>, <b>tickets.edit</b>, <b>roles.create</b>.
            </div>

            <div style={S.pagination} className="perms-pagination-mobile">
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

      {openPermModal && (
        <div style={S.modalOverlay} onClick={closePermModal}>
          <div
            style={S.modal}
            className="perms-modal-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                {permMode === "create" ? "Crear permiso" : "Editar permiso"}
              </h3>
              <button type="button" style={S.xBtn} onClick={closePermModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitPermModal}>
              <div style={S.modalBody}>
                <div style={S.fieldWrap}>
                  <div style={S.label}>Nombre del permiso</div>
                  <input
                    value={permName}
                    onChange={(e) => {
                      setPermName(e.target.value);
                      setFieldErrors((prev) => ({ ...prev, name: "" }));
                    }}
                    style={{
                      ...S.inputFull,
                      borderColor: fieldErrors.name ? "#fecaca" : "#dbeafe",
                    }}
                    placeholder="Ej. tickets.view"
                  />
                  {fieldErrors.name ? (
                    <div style={S.errorText}>{fieldErrors.name}</div>
                  ) : (
                    <div style={{ ...S.helper, marginTop: 6 }}>
                      Tip: usa formato <b>modulo.accion</b>
                    </div>
                  )}
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 800 }}>{err}</div> : null}
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closePermModal}>
                  Cancelar
                </Btn>
                <Btn type="submit" disabled={savingPermModal} variant="primary">
                  {savingPermModal ? "Guardando..." : "Guardar"}
                </Btn>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}