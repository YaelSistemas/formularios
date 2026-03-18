import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete } from "../../services/api";

export default function AdminUnidadesServicio() {
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

  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(false);

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

  const [openForm, setOpenForm] = useState(false);
  const [formMode, setFormMode] = useState("create");
  const [editingId, setEditingId] = useState(null);

  const [fNombre, setFNombre] = useState("");
  const [fDesc, setFDesc] = useState("");
  const [fActivo, setFActivo] = useState(true);

  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState(null);
  const [fieldErrors, setFieldErrors] = useState({});

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
      //
    }
  }, [loading, rows, meta.last_page, meta.total, openForm]);

  const resetForm = () => {
    setEditingId(null);
    setFNombre("");
    setFDesc("");
    setFActivo(true);
    setFieldErrors({});
    setErr("");
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

  const Badge = ({ children, active = true }) => (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        padding: "6px 10px",
        borderRadius: 999,
        border: `1px solid ${active ? "#86efac" : "#fecaca"}`,
        background: active ? "#ecfdf5" : "#fef2f2",
        fontSize: 12,
        fontWeight: 800,
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
      minWidth: 760,
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
      maxWidth: 820,
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

          <Btn variant="primary" onClick={openCreate}>
            <i className="fa-solid fa-plus" />
            Nueva unidad
          </Btn>
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

        {err ? <div style={{ marginTop: 12, color: "#b91c1c", fontWeight: 800 }}>{err}</div> : null}
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
                    <th style={{ ...S.th, width: 140, textAlign: "right" }}>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {rows.length ? (
                    rows.map((r) => (
                      <tr key={r.id}>
                        <td style={S.td}>
                          <div style={{ fontWeight: 800 }}>{r.nombre}</div>
                        </td>
                        <td style={S.td}>
                          <div style={{ color: "#475569", maxWidth: 320 }}>
                            {r.descripcion || "—"}
                          </div>
                        </td>
                        <td style={S.td}>
                          <Badge active={!!r.activo}>
                            {r.activo ? "Activo" : "Inactivo"}
                          </Badge>
                        </td>
                        <td style={{ ...S.td, textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 8, flexWrap: "nowrap" }}>
                            <IconBtn onClick={() => openEdit(r)} title="Editar" variant="primary">
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
    </div>
  );
}