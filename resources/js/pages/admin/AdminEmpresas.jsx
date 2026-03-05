// resources/js/pages/admin/AdminEmpresas.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminEmpresas() {
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

  // -------- DATA --------
  const [rows, setRows] = useState([]); // {id,nombre,razon_social,activo}
  const [loading, setLoading] = useState(false);

  // ✅ buscador: draft + debounce + fix foco
  const [qDraft, setQDraft] = useState("");
  const [q, setQ] = useState("");

  // ✅ siempre 20
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
      // ignore
    }
  };

  // -------- MODAL --------
  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create"); // create | edit
  const [editingId, setEditingId] = useState(null);

  const [fNombre, setFNombre] = useState("");
  const [fRazon, setFRazon] = useState("");
  const [fActivo, setFActivo] = useState(true);

  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState(null);

  const load = async () => {
    setErr("");
    setLoading(true);
    try {
      const data = await apiGet(
        `/admin/empresas?q=${encodeURIComponent(q)}&per_page=${perPage}&page=${page}`
      );

      if (Array.isArray(data?.data)) {
        setRows(data.data || []);
        setMeta({ last_page: data.last_page || 1, total: data.total || 0 });
      } else {
        const arr = Array.isArray(data?.empresas) ? data.empresas : [];
        setRows(arr);
        setMeta({ last_page: 1, total: arr.length });
      }
    } catch (e) {
      setErr(e?.message || "Error cargando empresas");
    } finally {
      setLoading(false);
    }
  };

  // debounce
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
  }, [q, page]);

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
      // ignore
    }
  }, [loading, rows, meta.last_page, meta.total, openForm]);

  const resetForm = () => {
    setEditingId(null);
    setFNombre("");
    setFRazon("");
    setFActivo(true);
  };

  const openCreate = () => {
    resetForm();
    setFormMode("create");
    setOpenForm(true);
  };

  const openEdit = (r) => {
    resetForm();
    setFormMode("edit");
    setEditingId(r.id);
    setFNombre(r.nombre || "");
    setFRazon(r.razon_social || "");
    setFActivo(!!r.activo);
    setOpenForm(true);
  };

  const closeModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
  };

  const submit = async (e) => {
    e.preventDefault();
    setErr("");
    setSaving(true);

    try {
      const payload = {
        nombre: fNombre.trim(),
        razon_social: fRazon.trim() || null,
        activo: !!fActivo,
      };

      if (!payload.nombre) {
        setErr("Escribe el nombre de la empresa.");
        return;
      }

      if (formMode === "create") {
        await apiPost("/admin/empresas", payload);
        showToast("success", "✅ Empresa creada correctamente");
      } else {
        await apiPut(`/admin/empresas/${editingId}`, payload);
        showToast("info", "✏️ Empresa actualizada");
      }

      closeModal();
      await load();
    } catch (e2) {
      setErr(e2?.message || "Error guardando empresa");
    } finally {
      setSaving(false);
    }
  };

  const remove = async (r) => {
    const ok = window.confirm(`¿Eliminar la empresa "${r.nombre}"?`);
    if (!ok) return;

    setErr("");
    setDeletingId(r.id);
    try {
      await apiDelete(`/admin/empresas/${r.id}`);
      showToast("danger", "🗑️ Empresa eliminada");
      await load();
    } catch (e) {
      setErr(e?.message || "Error eliminando empresa");
    } finally {
      setDeletingId(null);
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

  const Badge = ({ children, active = true }) => (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        padding: "6px 10px",
        borderRadius: 999,
        border: "1px solid " + (active ? "#bbf7d0" : "#fecaca"),
        background: active ? "#ecfdf5" : "#fef2f2",
        fontSize: 12,
        fontWeight: 900,
        color: active ? "#166534" : "#b91c1c",
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
    tableOuter: { display: "flex", justifyContent: "center" },
    tableWrap: { overflowX: "auto", width: "100%", maxWidth: 980 },
    table: { borderCollapse: "separate", borderSpacing: 0, width: "100%", minWidth: 720 },
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
    formGrid: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 },
    responsiveStyleTag: `
      @media (max-width: 520px) {
        .emp-toolbar-input { min-width: 100% !important; width: 100% !important; }
        .emp-form-grid { grid-template-columns: 1fr !important; }
      }
    `,
  };

  return (
    <div>
      <style>{S.responsiveStyleTag}</style>

      <Card style={{ marginBottom: 14 }}>
        <div style={S.toolbar}>
          <div>
            <h2 style={{ margin: 0 }}>Empresas</h2>
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Total: <b>{meta.total}</b>
            </div>
          </div>

          <div style={S.inputsRow}>
            <div style={{ minWidth: 260 }} className="emp-toolbar-input">
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
                placeholder="Nombre o razón social"
                style={{ ...S.input, width: "100%" }}
                className="emp-toolbar-input"
              />
            </div>

            <Btn variant="primary" onClick={openCreate}>
              <i className="fa-solid fa-plus" />
              Nueva empresa
            </Btn>

            <Btn type="button" onClick={load} disabled={loading}>
              {loading ? "Actualizando..." : "Refrescar"}
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
        {loading ? (
          <div>Cargando empresas...</div>
        ) : (
          <div style={S.tableOuter}>
            <div style={S.tableWrap}>
              <table style={S.table}>
                <thead>
                  <tr>
                    <th style={S.th}>Nombre</th>
                    <th style={S.th}>Razón social</th>
                    <th style={S.th}>Activo</th>
                    <th style={{ ...S.th, width: 160, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {rows.length ? (
                    rows.map((r) => (
                      <tr key={r.id}>
                        <td style={S.td}>
                          <div style={{ fontWeight: 900 }}>{r.nombre}</div>
                        </td>
                        <td style={S.td}>
                          <div style={{ color: "#334155" }}>{r.razon_social || "—"}</div>
                        </td>
                        <td style={S.td}>
                          <Badge active={!!r.activo}>{r.activo ? "Activo" : "Inactivo"}</Badge>
                        </td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openEdit(r)} title="Editar">
                              <i className="fa-solid fa-pen" />
                            </IconBtn>

                            <IconBtn
                              disabled={deletingId === r.id}
                              onClick={() => remove(r)}
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
                      <td style={S.td} colSpan={4}>
                        Sin empresas
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

      {/* ✅ Modal Empresas */}
      {openForm && (
        <div style={S.modalOverlay} onClick={closeModal}>
          <div style={S.modal} onClick={(e) => e.stopPropagation()}>
            <div style={S.modalHeader}>
              <h3 style={S.modalTitle}>{formMode === "create" ? "Crear empresa" : "Editar empresa"}</h3>
              <button type="button" style={S.xBtn} onClick={closeModal} aria-label="Cerrar">
                ✕
              </button>
            </div>

            <form onSubmit={submit}>
              <div style={S.modalBody}>
                <div className="emp-form-grid" style={S.formGrid}>
                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>Nombre</div>
                    <input
                      value={fNombre}
                      onChange={(e) => setFNombre(e.target.value)}
                      required
                      style={S.inputFull}
                      placeholder="Ej. VYSISA"
                    />
                  </div>

                  <div style={{ gridColumn: "1 / -1" }}>
                    <div style={S.label}>Razón social</div>
                    <input
                      value={fRazon}
                      onChange={(e) => setFRazon(e.target.value)}
                      style={S.inputFull}
                      placeholder="Ej. VULCANIZACIÓN Y SERVICIOS INDUSTRIALES, S.A. DE C.V."
                    />
                    <div style={S.helper}>Opcional</div>
                  </div>

                  <div style={{ gridColumn: "1 / -1" }}>
                    <label style={{ display: "inline-flex", gap: 8, alignItems: "center", fontWeight: 900 }}>
                      <input type="checkbox" checked={!!fActivo} onChange={(e) => setFActivo(e.target.checked)} />
                      Activo
                    </label>
                    <div style={S.helper}>Si está inactiva, puedes ocultarla para asignaciones nuevas.</div>
                  </div>
                </div>

                {err ? <div style={{ color: "#b91c1c", fontWeight: 900 }}>{err}</div> : null}
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
    </div>
  );
}