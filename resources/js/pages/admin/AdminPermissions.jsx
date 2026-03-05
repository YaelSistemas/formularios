// resources/js/pages/admin/AdminPermissions.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminPermissions() {
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

  // -------- PERMISOS --------
  const [permissions, setPermissions] = useState([]); // {id,name}
  const [loadingPerms, setLoadingPerms] = useState(false);

  // ✅ buscador: draft + debounce (y fix foco)
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

  // modal
  const [openPermModal, setOpenPermModal] = useState(false);
  const [permMode, setPermMode] = useState("create"); // create | edit
  const [editingPermId, setEditingPermId] = useState(null);
  const [permName, setPermName] = useState("");
  const [savingPermModal, setSavingPermModal] = useState(false);
  const [deletingPermId, setDeletingPermId] = useState(null);

  const loadPermissions = async () => {
    setErr("");
    setLoadingPerms(true);
    try {
      // ✅ soporta ambos formatos:
      // 1) backend paginado: { data, last_page, total }
      // 2) backend simple: { permissions: [...] }
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

  // ✅ debounce del buscador
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
  }, [q, page]);

  // ✅ cada vez que escribes, si algo te tumba el foco, lo recuperamos
  useEffect(() => {
    restoreFocusIfNeeded();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qDraft]);

  useEffect(() => {
    // ✅ si el usuario estaba escribiendo y el render lo "tumbó", lo recuperamos
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
      // ignore
    }
  }, [loadingPerms, permissions, meta.last_page, meta.total, openPermModal]);

  const openCreatePermModal = () => {
    setErr("");
    setPermMode("create");
    setEditingPermId(null);
    setPermName("");
    setOpenPermModal(true);
  };

  const openEditPermModal = (p) => {
    setErr("");
    setPermMode("edit");
    setEditingPermId(p.id);
    setPermName(p.name || "");
    setOpenPermModal(true);
  };

  const closePermModal = () => {
    setOpenPermModal(false);
    setSavingPermModal(false);
    setErr("");
  };

  const submitPermModal = async (e) => {
    e.preventDefault();
    setErr("");
    setSavingPermModal(true);

    try {
      const name = permName.trim();
      if (!name) {
        setErr("Escribe un nombre de permiso.");
        return;
      }

      if (permMode === "create") {
        await apiPost("/admin/permissions", { name });
        showToast("success", "✅ Permiso creado correctamente");
      } else {
        await apiPut(`/admin/permissions/${editingPermId}`, { name });
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

  const deletePermission = async (p) => {
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
        .perms-toolbar-input { min-width: 100% !important; width: 100% !important; }
      }
    `,
  };

  return (
    <div>
      <style>{S.responsiveStyleTag}</style>

      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Permisos</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Total: <b>{meta.total}</b>
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }} className="perms-toolbar-input">
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
                placeholder="Ej. tickets.view"
                style={{ ...S.input, width: "100%" }}
                className="perms-toolbar-input"
              />
            </div>

            <Btn variant="primary" onClick={openCreatePermModal}>
              <i className="fa-solid fa-plus" />
              Nuevo permiso
            </Btn>

            <Btn type="button" onClick={loadPermissions} disabled={loadingPerms}>
              {loadingPerms ? "Actualizando..." : "Refrescar"}
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
        {loadingPerms ? (
          <div>Cargando permisos...</div>
        ) : (
          <div style={S.tableOuter}>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>ID</th>
                    <th style={S.th}>Permiso</th>
                    <th style={S.th}>Formato</th>
                    <th style={{ ...S.th, width: 160, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {permissions.length ? (
                    permissions.map((p) => {
                      const okFormat = String(p.name || "").includes(".");
                      return (
                        <tr key={p.id}>
                          <td style={S.td}>{p.id}</td>
                          <td style={S.td}>
                            <div style={{ fontWeight: 900 }}>{p.name}</div>
                          </td>
                          <td style={S.td}>
                            <Badge>{okFormat ? "modulo.accion" : "sin punto"}</Badge>
                          </td>
                          <td style={{ ...S.td, textAlign: "right" }}>
                            <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                              <IconBtn onClick={() => openEditPermModal(p)} title="Editar">
                                <i className="fa-solid fa-pen" />
                              </IconBtn>

                              <IconBtn
                                onClick={() => deletePermission(p)}
                                disabled={deletingPermId === p.id}
                                variant="danger"
                                title="Eliminar"
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
                        Sin permisos
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>

              <div style={{ marginTop: 8, fontSize: 12, color: "#64748b" }}>
                Sugerencia: usa formato <b>modulo.accion</b> (ej. <b>tickets.view</b>, <b>tickets.edit</b>).
              </div>
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

      {/* ✅ Modal Permisos (mismo estilo que Users/Roles) */}
      {openPermModal && (
        <div style={S.modalOverlay} onClick={closePermModal}>
          <div style={S.modal} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>{permMode === "create" ? "Crear permiso" : "Editar permiso"}</h3>
              <button type="button" style={S.xBtn} onClick={closePermModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submitPermModal}>
              <div style={S.modalBody}>
                <div>
                  <div style={S.label}>Nombre del permiso</div>
                  <input
                    value={permName}
                    onChange={(e) => setPermName(e.target.value)}
                    required
                    style={S.inputFull}
                    placeholder="Ej. tickets.view"
                  />
                  <div style={S.helper}>Tip: usa formato modulo.accion</div>
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
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