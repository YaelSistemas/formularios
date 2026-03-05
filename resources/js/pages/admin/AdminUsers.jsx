import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminUsers() {
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

  // -------- USERS --------
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loadingUsers, setLoadingUsers] = useState(false);

  // ✅ Catalogos (Empresas y Grupos)
  const [empresasAll, setEmpresasAll] = useState([]); // [{id,nombre,razon_social,activo}]
  const [gruposAll, setGruposAll] = useState([]); // [{id,nombre,nombre_mostrar,activo}]
  const [loadingCats, setLoadingCats] = useState(false);

  // ✅ buscador: draft + debounce
  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  // ✅ default por página en 20 (y mantenemos que siempre arranque en 20)
  const [perPage, setPerPage] = useState(20);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ last_page: 1, total: 0 });

  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create"); // create | edit
  const [editingId, setEditingId] = useState(null);

  const [fName, setFName] = useState("");
  const [fEmail, setFEmail] = useState("");
  const [fPassword, setFPassword] = useState("");
  const [fRoles, setFRoles] = useState([]);

  // ✅ nuevos campos
  const [fActivo, setFActivo] = useState(true);
  const [fEmpresas, setFEmpresas] = useState([]); // ids
  const [fGrupos, setFGrupos] = useState([]); // ids

  const [saving, setSaving] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);

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

    // vuelve a enfocar y deja el cursor al final
    el.focus({ preventScroll: true });
    try {
      const len = el.value?.length ?? 0;
      el.setSelectionRange(len, len);
    } catch {
      // ignore
    }
  };

  const loadRolesNames = async () => {
    const data = await apiGet("/admin/roles");
    setRoles(data.roles || []);
  };

  const loadCatalogs = async () => {
    setLoadingCats(true);
    try {
      const [eData, gData] = await Promise.all([
        apiGet("/admin/empresas?per_page=1000&page=1&q="),
        apiGet("/admin/grupos?per_page=1000&page=1&q="),
      ]);
      setEmpresasAll(Array.isArray(eData?.data) ? eData.data : []);
      setGruposAll(Array.isArray(gData?.data) ? gData.data : []);
    } catch (e) {
      // no bloquea, pero avisamos si quieres
      // setErr(e?.message || "Error cargando catálogos");
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

  // ✅ debounce del buscador
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
      } catch {
        // ignore
      }

      // ✅ catálogos para selects (empresas/grupos)
      await loadCatalogs();

      await loadUsers();
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    loadUsers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, perPage, page]);

  // ✅ cada vez que escribes, si algo te tumba el foco, lo recuperamos
  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    if (openForm) return; // si hay modal, no tocar foco
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
  }, [loadingUsers, users, meta.last_page, meta.total, openForm]);

  const resetUserForm = () => {
    setEditingId(null);
    setFName("");
    setFEmail("");
    setFPassword("");
    setFRoles([]);

    // ✅ nuevos
    setFActivo(true);
    setFEmpresas([]);
    setFGrupos([]);
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
    setFRoles(Array.isArray(u.roles) ? u.roles : []);

    // ✅ precarga: activo / empresas / grupos
    setFActivo(u.activo !== undefined ? !!u.activo : true);

    const empresasIds = Array.isArray(u.empresas)
      ? u.empresas.map((x) => Number(x?.id)).filter((x) => Number.isFinite(x))
      : Array.isArray(u.empresa_ids)
      ? u.empresa_ids.map((x) => Number(x)).filter((x) => Number.isFinite(x))
      : [];
    const gruposIds = Array.isArray(u.grupos)
      ? u.grupos.map((x) => Number(x?.id)).filter((x) => Number.isFinite(x))
      : Array.isArray(u.grupo_ids)
      ? u.grupo_ids.map((x) => Number(x)).filter((x) => Number.isFinite(x))
      : [];

    setFEmpresas(empresasIds);
    setFGrupos(gruposIds);

    setOpenForm(true);
  };

  const closeUserModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
  };

  const toggleRole = (roleName) => {
    setFRoles((prev) => {
      if (prev.includes(roleName)) return prev.filter((r) => r !== roleName);
      return [...prev, roleName];
    });
  };

  const toggleEmpresa = (id) => {
    const num = Number(id);
    if (!Number.isFinite(num)) return;
    setFEmpresas((prev) => (prev.includes(num) ? prev.filter((x) => x !== num) : [...prev, num]));
  };

  const toggleGrupo = (id) => {
    const num = Number(id);
    if (!Number.isFinite(num)) return;
    setFGrupos((prev) => (prev.includes(num) ? prev.filter((x) => x !== num) : [...prev, num]));
  };

  const submitUserForm = async (e) => {
    e.preventDefault();
    setErr("");
    setSaving(true);

    try {
      const payload = {
        name: fName.trim(),
        email: fEmail.trim(),
        roles: fRoles,

        // ✅ nuevos
        activo: !!fActivo,
        empresa_ids: fEmpresas, // ids
        grupo_ids: fGrupos, // ids
      };

      if (formMode === "create") {
        payload.password = fPassword;
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

  // UI helpers
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
    select: {
      padding: "10px 12px",
      borderRadius: 10,
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      outline: "none",
      minWidth: 92,
    },

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
    formGrid: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 },
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
    sectionBox: {
      border: "1px solid #e4e4e7",
      background: "#f8fafc",
      borderRadius: 12,
      padding: 10,
      display: "flex",
      flexDirection: "column",
      gap: 8,
    },
    chipsRow: {
      display: "flex",
      flexWrap: "wrap",
      gap: 10,
      alignItems: "center",
    },
    responsiveStyleTag: `
      @media (max-width: 520px) {
        .users-toolbar-input { min-width: 100% !important; width: 100% !important; }
        .users-form-grid { grid-template-columns: 1fr !important; }
      }
    `,
  };

  const roleLabel = (u) => {
    const rr = Array.isArray(u?.roles) ? u.roles : [];
    return rr.length ? rr[0] : "—";
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
          fontWeight: 900,
        }}
      >
        {a ? "Activo" : "Inactivo"}
      </span>
    );
  };

  return (
    <div>
      <style>{S.responsiveStyleTag}</style>

      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Usuarios</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Total: <b>{meta.total}</b>
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }} className="users-toolbar-input">
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
                placeholder="Nombre o correo"
                style={{ ...S.input, width: "100%" }}
                className="users-toolbar-input"
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
                <option value={10}>10</option>
                <option value={20}>20</option>
                <option value={50}>50</option>
              </select>
            </div>

            <Btn
              type="button"
              onClick={loadUsers}
              disabled={loadingUsers}
              style={{ opacity: loadingUsers ? 0.7 : 1 }}
            >
              {loadingUsers ? "Actualizando..." : "Refrescar"}
            </Btn>

            <Btn variant="primary" onClick={openCreate}>
              <i className="fa-solid fa-user-plus" />
              Nuevo usuario
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
        {loadingUsers ? (
          <div>Cargando usuarios...</div>
        ) : (
          <div style={S.tableOuter}>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Nombre</th>
                    <th style={S.th}>Correo</th>
                    <th style={S.th}>Rol</th>
                    <th style={S.th}>Estado</th>
                    <th style={{ ...S.th, width: 120, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {users.length ? (
                    users.map((u) => (
                      <tr key={u.id}>
                        <td style={S.td}>
                          <div style={{ fontWeight: 900 }}>{u.name}</div>
                        </td>
                        <td style={S.td}>
                          <div style={{ color: "#334155" }}>{u.email}</div>
                        </td>
                        <td style={S.td}>
                          <Badge>{roleLabel(u)}</Badge>
                        </td>
                        <td style={S.td}>{activeBadge(u)}</td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openEdit(u)} title="Editar">
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
                      <td style={S.td} colSpan={5}>
                        Sin usuarios
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
          <Btn
            disabled={!canPrev}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            style={{ padding: "8px 10px" }}
          >
            Anterior
          </Btn>
          <div style={{ fontSize: 12 }}>
            Página <b>{page}</b> de <b>{meta.last_page}</b>
          </div>
          <Btn
            disabled={!canNext}
            onClick={() => setPage((p) => p + 1)}
            style={{ padding: "8px 10px" }}
          >
            Siguiente
          </Btn>
        </div>
      </Card>

      {/* ✅ Modal Usuarios */}
      {openForm && (
        <div style={S.modalOverlay} onClick={closeUserModal}>
          <div style={S.modal} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>{formMode === "create" ? "Crear usuario" : "Editar usuario"}</h3>
              <button type="button" style={S.xBtn} onClick={closeUserModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitUserForm}>
              <div style={S.modalBody}>
                <div className="users-form-grid" style={S.formGrid}>
                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>Nombre</div>
                    <input
                      value={fName}
                      onChange={(e) => setFName(e.target.value)}
                      required
                      style={S.inputFull}
                      placeholder="Ej. Juan Carlos Cruz"
                    />
                  </div>

                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>Correo</div>
                    <input
                      value={fEmail}
                      onChange={(e) => setFEmail(e.target.value)}
                      required
                      type="email"
                      style={S.inputFull}
                      placeholder="correo@empresa.com"
                    />
                  </div>

                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>
                      Contraseña <span style={S.helper}>{formMode === "edit" ? "(opcional)" : "(requerida)"}</span>
                    </div>
                    <input
                      value={fPassword}
                      onChange={(e) => setFPassword(e.target.value)}
                      type="password"
                      required={formMode === "create"}
                      style={S.inputFull}
                      placeholder={formMode === "edit" ? "Dejar en blanco para no cambiar" : "••••••••"}
                    />
                  </div>

                  {/* ✅ Activo */}
                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>Estado</div>
                    <label style={{ display: "inline-flex", alignItems: "center", gap: 10, fontWeight: 900 }}>
                      <input
                        type="checkbox"
                        checked={!!fActivo}
                        onChange={(e) => setFActivo(e.target.checked)}
                      />
                      {fActivo ? "Activo" : "Inactivo"}
                      <span style={S.helper}>Si está inactivo, no podrá iniciar sesión.</span>
                    </label>
                  </div>
                </div>

                {/* ✅ Roles */}
                <div>
                  <div style={S.label}>Roles</div>
                  <div style={S.rolesBox}>
                    {roles.length ? (
                      roles.map((r) => (
                        <label key={r} style={{ display: "inline-flex", gap: 8, alignItems: "center", fontWeight: 900 }}>
                          <input type="checkbox" checked={fRoles.includes(r)} onChange={() => toggleRole(r)} />
                          {r}
                        </label>
                      ))
                    ) : (
                      <div style={{ fontSize: 12, color: "#64748b" }}>No hay roles. Ve a “Roles” y crea uno.</div>
                    )}
                  </div>
                </div>

                {/* ✅ Empresas */}
                <div>
                  <div style={S.label}>Empresas</div>
                  <div style={S.sectionBox}>
                    {loadingCats ? (
                      <div style={{ fontSize: 12, color: "#64748b" }}>Cargando empresas...</div>
                    ) : empresasAll.length ? (
                      <div style={S.chipsRow}>
                        {empresasAll.map((e) => (
                          <label
                            key={e.id}
                            style={{
                              display: "inline-flex",
                              gap: 8,
                              alignItems: "center",
                              fontWeight: 900,
                              opacity: e.activo ? 1 : 0.6,
                            }}
                            title={!e.activo ? "Empresa inactiva" : ""}
                          >
                            <input
                              type="checkbox"
                              checked={fEmpresas.includes(Number(e.id))}
                              onChange={() => toggleEmpresa(e.id)}
                            />
                            {e.nombre}
                            {e.razon_social ? (
                              <span style={{ fontWeight: 700, color: "#64748b" }}>— {e.razon_social}</span>
                            ) : null}
                          </label>
                        ))}
                      </div>
                    ) : (
                      <div style={{ fontSize: 12, color: "#64748b" }}>No hay empresas. Crea una en “Empresas”.</div>
                    )}
                  </div>
                </div>

                {/* ✅ Grupos */}
                <div>
                  <div style={S.label}>Grupos</div>
                  <div style={S.sectionBox}>
                    {loadingCats ? (
                      <div style={{ fontSize: 12, color: "#64748b" }}>Cargando grupos...</div>
                    ) : gruposAll.length ? (
                      <div style={S.chipsRow}>
                        {gruposAll.map((g) => (
                          <label
                            key={g.id}
                            style={{
                              display: "inline-flex",
                              gap: 8,
                              alignItems: "center",
                              fontWeight: 900,
                              opacity: g.activo ? 1 : 0.6,
                            }}
                            title={!g.activo ? "Grupo inactivo" : ""}
                          >
                            <input
                              type="checkbox"
                              checked={fGrupos.includes(Number(g.id))}
                              onChange={() => toggleGrupo(g.id)}
                            />
                            {g.nombre_mostrar || g.nombre}
                            {g.descripcion ? (
                              <span style={{ fontWeight: 700, color: "#64748b" }}>— {g.descripcion}</span>
                            ) : null}
                          </label>
                        ))}
                      </div>
                    ) : (
                      <div style={{ fontSize: 12, color: "#64748b" }}>No hay grupos. Crea uno en “Grupos”.</div>
                    )}
                  </div>
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
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