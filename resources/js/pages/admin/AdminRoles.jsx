// resources/js/pages/admin/AdminRoles.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminRoles() {
  const [err, setErr] = useState("");

  // ✅ toast (3s)
  const [toast, setToast] = useState(null); // { type: 'success'|'info'|'danger', text: string }
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

  // -------- ROLES --------
  const [rolesList, setRolesList] = useState([]); // {id,name}
  const [loadingRoles, setLoadingRoles] = useState(false);

  // ✅ buscador: draft + debounce (mismo fix que Users)
  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  // ✅ siempre 20
  const perPage = 20;
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const canPrev = useMemo(() => page > 1, [page]);
  const canNext = useMemo(() => page < (meta.last_page || 1), [page, meta.last_page]);

  // ✅ Focus keeper (soluciona el "letra por letra")
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

  // modal editar/crear rol
  const [openRoleModal, setOpenRoleModal] = useState(false);
  const [roleMode, setRoleMode] = useState("create"); // create | edit
  const [editingRoleId, setEditingRoleId] = useState(null);
  const [roleName, setRoleName] = useState("");
  const [savingRoleModal, setSavingRoleModal] = useState(false);

  // eliminar
  const [deletingRoleId, setDeletingRoleId] = useState(null);

  // modal asignar permisos a rol
  const [openRolePermModal, setOpenRolePermModal] = useState(false);
  const [rolePermLoading, setRolePermLoading] = useState(false);
  const [rolePermSaving, setRolePermSaving] = useState(false);
  const [rolePermRole, setRolePermRole] = useState(null); // {id,name}
  const [rolePermIsAdminRole, setRolePermIsAdminRole] = useState(false);
  const [rolePermAll, setRolePermAll] = useState([]); // nombres
  const [rolePermSelected, setRolePermSelected] = useState([]); // seleccionados

  const loadRolesList = async () => {
    setErr("");
    setLoadingRoles(true);
    try {
      // ✅ soporta ambos formatos:
      // 1) backend paginado: { data, last_page, total }
      // 2) backend simple: { roles: [...] }
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

  // ✅ debounce del buscador
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

  // ✅ cada vez que escribes, si algo te tumba el foco, lo recuperamos
  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    // ✅ si el usuario estaba escribiendo y el render lo "tumbó", lo recuperamos
    if (openRoleModal || openRolePermModal) return;
    if (!searchWasFocusedRef.current) return;

    const el = searchRef.current;
    if (!el) return;
    if (document.activeElement === el) return;

    el.focus({ preventScroll: true });
    try {
      const len = el.value?.length ?? 0;
      el.setSelectionRange(len, len);
    } catch {
      // ignore
    }
  }, [loadingRoles, rolesList, meta.last_page, meta.total, openRoleModal, openRolePermModal]);

  // helpers roles
  const openCreateRoleModal = () => {
    setErr("");
    setRoleMode("create");
    setEditingRoleId(null);
    setRoleName("");
    setOpenRoleModal(true);
  };

  const openEditRoleModal = (r) => {
    setErr("");
    setRoleMode("edit");
    setEditingRoleId(r.id);
    setRoleName(r.name || "");
    setOpenRoleModal(true);
  };

  const closeRoleModal = () => {
    setOpenRoleModal(false);
    setSavingRoleModal(false);
    setErr("");
  };

  const submitRoleModal = async (e) => {
    e.preventDefault();
    setErr("");
    setSavingRoleModal(true);

    try {
      const name = roleName.trim();
      if (!name) {
        setErr("Escribe un nombre de rol.");
        return;
      }

      if (roleMode === "create") {
        await apiPost("/admin/roles-list", { name });
        showToast("success", "✅ Rol creado correctamente");
      } else {
        await apiPut(`/admin/roles-list/${editingRoleId}`, { name });
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

    const ok = window.confirm(`¿Eliminar el rol "${r.name}"?`);
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

  // role permissions
  const openRolePermissions = async (r) => {
    setErr("");
    setOpenRolePermModal(true);
    setRolePermLoading(true);
    setRolePermSaving(false);
    setRolePermRole({ id: r.id, name: r.name });
    setRolePermAll([]);
    setRolePermSelected([]);
    setRolePermIsAdminRole(false);

    try {
      const data = await apiGet(`/admin/roles/${r.id}/permissions`);

      setRolePermRole(data.role || { id: r.id, name: r.name });
      setRolePermIsAdminRole(!!data.is_admin_role);

      const all = Array.isArray(data.all_permissions) ? data.all_permissions : [];
      const selected = Array.isArray(data.permissions) ? data.permissions : [];

      setRolePermAll(all);
      setRolePermSelected(selected.includes("*") ? [] : selected);
    } catch (e) {
      setErr(e?.message || "Error cargando permisos del rol");
      setOpenRolePermModal(false);
    } finally {
      setRolePermLoading(false);
    }
  };

  const closeRolePermModal = () => {
    setOpenRolePermModal(false);
    setRolePermLoading(false);
    setRolePermSaving(false);
    setErr("");
  };

  const toggleRolePerm = (permName) => {
    setRolePermSelected((prev) => {
      if (prev.includes(permName)) return prev.filter((p) => p !== permName);
      return [...prev, permName];
    });
  };

  const saveRolePermissions = async () => {
    if (!rolePermRole?.id) return;
    if (rolePermIsAdminRole) return;

    setErr("");
    setRolePermSaving(true);

    try {
      await apiPut(`/admin/roles/${rolePermRole.id}/permissions`, {
        permissions: rolePermSelected,
      });

      showToast("info", "✅ Permisos guardados");
      closeRolePermModal();
      await loadRolesList();
    } catch (e) {
      setErr(e?.message || "Error guardando permisos del rol");
    } finally {
      setRolePermSaving(false);
    }
  };

  // UI
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

  const Badge = ({ children }) => (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        padding: "6px 10px",
        borderRadius: 999,
        border: "1px solid #e4e4e7",
        background: "#f8fafc",
        fontSize: 12,
        fontWeight: 900,
        color: "#0f172a",
      }}
    >
      {children}
    </span>
  );

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

    // ✅ tabla centrada
    tableOuter: { display: "flex", justifyContent: "center" },
    tableWrap: { overflowX: "auto", width: "100%", maxWidth: 980 },
    table: { borderCollapse: "separate", borderSpacing: 0, width: "100%", minWidth: 640 },
    th: {
      textAlign: "left",
      fontSize: 12,
      color: "#475569",
      padding: "12px 10px",
      borderBottom: "1px solid #e4e4e7",
      background: "#fff",
      position: "sticky",
      top: 0,
      zIndex: 1,
    },
    td: {
      padding: "12px 10px",
      borderBottom: "1px solid #f1f5f9",
      verticalAlign: "middle",
      fontSize: 13,
      color: "#0f172a",
    },

    // ✅ modal
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
      maxWidth: 560,
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
    inputFull: {
      width: "100%",
      padding: "10px 12px",
      borderRadius: 12,
      border: "1px solid #e4e4e7",
      background: "#fff",
      outline: "none",
    },
    helper: { fontSize: 12, color: "#64748b" },
    rolesBox: {
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      borderRadius: 12,
      padding: 10,
      display: "flex",
      flexWrap: "wrap",
      gap: 10,
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

    responsiveStyleTag: `
      @media (max-width: 520px) {
        .roles-toolbar-input { min-width: 100% !important; width: 100% !important; }
      }
    `,
  };

  return (
    <div>
      <style>{S.responsiveStyleTag}</style>

      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Roles</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Total: <b>{meta.total}</b>
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }} className="roles-toolbar-input">
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
                placeholder="Nombre del rol"
                style={{ ...S.input, width: "100%" }}
                className="roles-toolbar-input"
              />
            </div>

            <Btn variant="primary" onClick={openCreateRoleModal}>
              <i className="fa-solid fa-plus" />
              Nuevo rol
            </Btn>

            <Btn type="button" onClick={loadRolesList} disabled={loadingRoles}>
              {loadingRoles ? "Actualizando..." : "Refrescar"}
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
        {loadingRoles ? (
          <div>Cargando roles...</div>
        ) : (
          <div style={S.tableOuter}>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>ID</th>
                    <th style={S.th}>Rol</th>
                    <th style={S.th}>Estado</th>
                    <th style={{ ...S.th, width: 220, textAlign: "right" }}>Acciones</th>
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
                            <div style={{ fontWeight: 900 }}>{r.name}</div>
                            {isAdmin ? (
                              <div style={{ fontSize: 12, color: "#64748b", marginTop: 2 }}>
                                Este rol no se puede editar ni eliminar.
                              </div>
                            ) : null}
                          </td>
                          <td style={S.td}>
                            <Badge>{isAdmin ? "Sistema" : "Normal"}</Badge>
                          </td>
                          <td style={{ ...S.td, textAlign: "right" }}>
                            <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                              <IconBtn
                                onClick={() => openEditRoleModal(r)}
                                disabled={isAdmin}
                                title={isAdmin ? "No se puede editar" : "Editar"}
                              >
                                <i className="fa-solid fa-pen" />
                              </IconBtn>

                              <IconBtn onClick={() => openRolePermissions(r)} title="Permisos" variant="primary">
                                <i className="fa-solid fa-key" />
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
                      <td style={S.td} colSpan={4}>
                        Sin roles
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        <div
          style={{
            display: "flex",
            gap: 10,
            alignItems: "center",
            marginTop: 12,
            flexWrap: "wrap",
            justifyContent: "center",
          }}
        >
          <Btn disabled={!canPrev} onClick={() => setPage((p) => Math.max(1, p - 1))} style={{ padding: "8px 10px" }}>
            Anterior
          </Btn>
          <div style={{ fontSize: 12 }}>
            Página <b>{page}</b> de <b>{meta.last_page}</b>
          </div>
          <Btn disabled={!canNext} onClick={() => setPage((p) => p + 1)} style={{ padding: "8px 10px" }}>
            Siguiente
          </Btn>
        </div>
      </Card>

      {/* ✅ Modal Rol (mismo estilo que Users) */}
      {openRoleModal && (
        <div style={S.modalOverlay} onClick={closeRoleModal}>
          <div style={S.modal} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>{roleMode === "create" ? "Crear rol" : "Editar rol"}</h3>
              <button type="button" style={S.xBtn} onClick={closeRoleModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitRoleModal}>
              <div style={S.modalBody}>
                <div>
                  <div style={S.label}>Nombre del rol</div>
                  <input
                    value={roleName}
                    onChange={(e) => setRoleName(e.target.value)}
                    required
                    style={S.inputFull}
                    placeholder="Ej. Supervisor"
                  />
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
              </div>

              <div style={S.modalFooter}>
                <Btn type="button" onClick={closeRoleModal}>
                  Cancelar
                </Btn>
                <Btn type="submit" disabled={savingRoleModal} variant="primary">
                  {savingRoleModal ? "Guardando..." : "Guardar"}
                </Btn>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ✅ Modal Permisos por Rol (con el mismo overlay/box) */}
      {openRolePermModal && (
        <div style={S.modalOverlay} onClick={closeRolePermModal}>
          <div style={{ ...S.modal, maxWidth: 760 }} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>
                Permisos del rol: <span style={{ fontWeight: 900 }}>{rolePermRole?.name || "—"}</span>
              </h3>
              <button type="button" style={S.xBtn} onClick={closeRolePermModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <div style={S.modalBody}>
              {rolePermLoading ? (
                <div>Cargando...</div>
              ) : rolePermIsAdminRole ? (
                <div style={{ fontSize: 14 }}>
                  <p style={{ marginTop: 0 }}>
                    Este rol tiene <b>acceso total por rol</b>. No requiere permisos.
                  </p>
                </div>
              ) : (
                <>
                  <div style={{ fontSize: 12, color: "#64748b", marginBottom: 8 }}>
                    Marca los permisos que tendrá el rol.
                  </div>

                  <div style={{ maxHeight: 360, overflow: "auto", border: "1px solid #e4e4e7", padding: 10, borderRadius: 12 }}>
                    {rolePermAll.length ? (
                      <div style={{ display: "flex", flexWrap: "wrap", gap: 10 }}>
                        {rolePermAll.map((p) => (
                          <label key={p} style={{ display: "inline-flex", gap: 8, alignItems: "center", fontWeight: 900 }}>
                            <input type="checkbox" checked={rolePermSelected.includes(p)} onChange={() => toggleRolePerm(p)} />
                            {p}
                          </label>
                        ))}
                      </div>
                    ) : (
                      <div style={{ fontSize: 12, color: "#64748b" }}>No hay permisos creados. Ve a “Permisos” y crea algunos.</div>
                    )}
                  </div>

                  {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
                </>
              )}
            </div>

            <div style={S.modalFooter}>
              <Btn type="button" onClick={closeRolePermModal}>
                Cancelar
              </Btn>
              <Btn
                type="button"
                onClick={saveRolePermissions}
                disabled={rolePermSaving || rolePermLoading || rolePermIsAdminRole}
                variant="primary"
              >
                {rolePermSaving ? "Guardando..." : "Guardar"}
              </Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}