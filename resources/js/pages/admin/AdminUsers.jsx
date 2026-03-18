import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminUsers() {
  const [err, setErr] = useState("");

  // toast
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

  // users
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loadingUsers, setLoadingUsers] = useState(false);

  // catálogos
  const [empresasAll, setEmpresasAll] = useState([]);
  const [gruposAll, setGruposAll] = useState([]);
  const [unidadesServicioAll, setUnidadesServicioAll] = useState([]);
  const [loadingCats, setLoadingCats] = useState(false);

  // buscador
  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  const [perPage, setPerPage] = useState(20);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create");
  const [editingId, setEditingId] = useState(null);

  // form
  const [fName, setFName] = useState("");
  const [fEmail, setFEmail] = useState("");
  const [fPassword, setFPassword] = useState("");
  const [fRole, setFRole] = useState("");
  const [fActivo, setFActivo] = useState(true);
  const [fEmpresa, setFEmpresa] = useState("");
  const [fGrupo, setFGrupo] = useState("");
  const [fUnidadServicio, setFUnidadServicio] = useState("");

  const [saving, setSaving] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);

  const [fieldErrors, setFieldErrors] = useState({});

  const canPrev = useMemo(() => page > 1, [page]);
  const canNext = useMemo(() => page < (meta.last_page || 1), [page, meta.last_page]);

  // focus buscador
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

      setEmpresasAll(Array.isArray(eData?.data) ? eData.data : []);
      setGruposAll(Array.isArray(gData?.data) ? gData.data : []);
      setUnidadesServicioAll(Array.isArray(usData?.data) ? usData.data : []);
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
        await loadRolesNames();
        await loadCatalogs();
        await loadUsers();
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
    if (openForm) return;
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
  }, [loadingUsers, users, meta.last_page, meta.total, openForm]);

  const resetUserForm = () => {
    setEditingId(null);
    setFName("");
    setFEmail("");
    setFPassword("");
    setFRole("");
    setFActivo(true);
    setFEmpresa("");
    setFGrupo("");
    setFUnidadServicio("");
    setFieldErrors({});
    setErr("");
  };

  const openCreate = () => {
    resetUserForm();
    setFormMode("create");
    setOpenForm(true);
  };

  const openEdit = (u) => {
    resetUserForm();
    setFormMode("edit");
    setEditingId(u.id);

    setFName(u.name || "");
    setFEmail(u.email || "");

    const roleValue = Array.isArray(u.roles) && u.roles.length ? u.roles[0] : "";
    setFRole(roleValue);

    setFActivo(u.activo === true || u.activo === 1);

    const empresaId = Array.isArray(u.empresas) && u.empresas.length
      ? String(u.empresas[0]?.id ?? "")
      : Array.isArray(u.empresa_ids) && u.empresa_ids.length
      ? String(u.empresa_ids[0] ?? "")
      : "";

    const grupoId = Array.isArray(u.grupos) && u.grupos.length
      ? String(u.grupos[0]?.id ?? "")
      : Array.isArray(u.grupo_ids) && u.grupo_ids.length
      ? String(u.grupo_ids[0] ?? "")
      : "";

    const unidadServicioId = Array.isArray(u.unidades_servicio) && u.unidades_servicio.length
      ? String(u.unidades_servicio[0]?.id ?? "")
      : Array.isArray(u.unidad_servicio_ids) && u.unidad_servicio_ids.length
      ? String(u.unidad_servicio_ids[0] ?? "")
      : "";

    setFEmpresa(empresaId);
    setFGrupo(grupoId);
    setFUnidadServicio(unidadServicioId);

    setOpenForm(true);
  };

  const closeUserModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
    setFieldErrors({});
  };

  const validateForm = () => {
    const actionText = formMode === "create" ? "crear" : "actualizar";
    const errors = {};

    if (!fName.trim()) errors.name = `No se puede ${actionText} el usuario porque falta el nombre.`;
    if (!fEmail.trim()) errors.email = `No se puede ${actionText} el usuario porque falta el correo.`;
    if (formMode === "create" && !fPassword.trim()) {
      errors.password = "No se puede crear el usuario porque falta la contraseña.";
    }
    if (!fRole) errors.role = `No se puede ${actionText} el usuario porque falta el rol.`;
    if (!fUnidadServicio) {
      errors.unidad_servicio = `No se puede ${actionText} el usuario porque falta la unidad de servicio.`;
    }
    if (!fEmpresa) errors.empresa = `No se puede ${actionText} el usuario porque falta la empresa.`;
    if (!fGrupo) errors.grupo = `No se puede ${actionText} el usuario porque falta el grupo.`;

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

    if (!validateForm()) return;

    setSaving(true);

    try {
      const payload = {
        name: fName.trim(),
        email: fEmail.trim(),
        roles: [fRole],
        activo: !!fActivo,
        empresa_ids: [Number(fEmpresa)],
        grupo_ids: [Number(fGrupo)],
        unidad_servicio_ids: [Number(fUnidadServicio)],
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

  const deleteUser = async (u) => {
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
      minWidth: 920,
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

  const roleLabel = (u) => {
    const rr = Array.isArray(u?.roles) ? u.roles : [];
    return rr.length ? rr[0] : "—";
  };

  const firstEmpresaLabel = (u) => {
    if (Array.isArray(u?.empresas) && u.empresas.length) {
      return u.empresas[0]?.nombre || "—";
    }
    return "—";
  };

  const firstGrupoLabel = (u) => {
    if (Array.isArray(u?.grupos) && u.grupos.length) {
      return u.grupos[0]?.nombre_mostrar || u.grupos[0]?.nombre || "—";
    }
    return "—";
  };

  const firstUnidadServicioLabel = (u) => {
    if (Array.isArray(u?.unidades_servicio) && u.unidades_servicio.length) {
      return u.unidades_servicio[0]?.nombre || "—";
    }
    return "—";
  };

  const activeBadge = (u) => {
    const a = u?.activo !== undefined ? !!u.activo : true;
    return (
      <span
        style={{
          display: "inline-flex",
          alignItems: "center",
          gap: 6,
          padding: "6px 10px",
          borderRadius: 999,
          border: `1px solid ${a ? "#86efac" : "#fecaca"}`,
          background: a ? "#ecfdf5" : "#fef2f2",
          color: a ? "#166534" : "#b91c1c",
          fontSize: 12,
          fontWeight: 800,
        }}
      >
        {a ? "Activo" : "Inactivo"}
      </span>
    );
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

          <Btn variant="primary" onClick={openCreate}>
            <i className="fa-solid fa-user-plus" />
            Nuevo usuario
          </Btn>
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
              <option value={10}>10 registros</option>
              <option value={20}>20 registros</option>
              <option value={50}>50 registros</option>
            </select>
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
                    <th style={{ ...S.th, width: 120, textAlign: "right" }}>Acciones</th>
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
                          <Badge tone="role">{roleLabel(u)}</Badge>
                        </td>
                        <td style={S.td}>{firstUnidadServicioLabel(u)}</td>
                        <td style={S.td}>{firstEmpresaLabel(u)}</td>
                        <td style={S.td}>{firstGrupoLabel(u)}</td>
                        <td style={S.td}>{activeBadge(u)}</td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8 }}>
                            <IconBtn onClick={() => openEdit(u)} title="Editar" variant="primary">
                              <i className="fa-solid fa-pen" />
                            </IconBtn>

                            <IconBtn
                              disabled={confirmingDelete}
                              onClick={() => deleteUser(u)}
                              variant="danger"
                              title="Eliminar"
                            >
                              <i className="fa-solid fa-trash" />
                            </IconBtn>
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td style={S.td} colSpan={8}>
                        Sin usuarios registrados.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div style={S.pagination} className="users-pagination-mobile">
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
                  {/* Nombre solo */}
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

                  {/* Correo y contraseña */}
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

                  {/* Rol y unidad */}
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
                    <div style={S.label}>Unidad de servicio</div>
                    <select
                      value={fUnidadServicio}
                      onChange={(e) => {
                        setFUnidadServicio(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, unidad_servicio: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.unidad_servicio ? "#fecaca" : "#dbeafe",
                      }}
                      disabled={loadingCats}
                    >
                      <option value="">Selecciona una unidad de servicio</option>
                      {unidadesServicioAll.map((u) => (
                        <option key={u.id} value={u.id}>
                          {u.nombre}
                        </option>
                      ))}
                    </select>
                    {fieldErrors.unidad_servicio ? (
                      <div style={S.errorText}>{fieldErrors.unidad_servicio}</div>
                    ) : null}
                  </div>

                  {/* Empresa y grupo */}
                  <div style={S.fieldWrap}>
                    <div style={S.label}>Empresa</div>
                    <select
                      value={fEmpresa}
                      onChange={(e) => {
                        setFEmpresa(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, empresa: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.empresa ? "#fecaca" : "#dbeafe",
                      }}
                      disabled={loadingCats}
                    >
                      <option value="">Selecciona una empresa</option>
                      {empresasAll.map((e) => (
                        <option key={e.id} value={e.id}>
                          {e.nombre}
                        </option>
                      ))}
                    </select>
                    {fieldErrors.empresa ? (
                      <div style={S.errorText}>{fieldErrors.empresa}</div>
                    ) : null}
                  </div>

                  <div style={S.fieldWrap}>
                    <div style={S.label}>Grupo</div>
                    <select
                      value={fGrupo}
                      onChange={(e) => {
                        setFGrupo(e.target.value);
                        setFieldErrors((prev) => ({ ...prev, grupo: "" }));
                      }}
                      style={{
                        ...S.inputFull,
                        borderColor: fieldErrors.grupo ? "#fecaca" : "#dbeafe",
                      }}
                      disabled={loadingCats}
                    >
                      <option value="">Selecciona un grupo</option>
                      {gruposAll.map((g) => (
                        <option key={g.id} value={g.id}>
                          {g.nombre_mostrar || g.nombre}
                        </option>
                      ))}
                    </select>
                    {fieldErrors.grupo ? <div style={S.errorText}>{fieldErrors.grupo}</div> : null}
                  </div>

                  {/* Estado solo */}
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
    </div>
  );
}