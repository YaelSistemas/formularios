import React, { useCallback, useEffect, useState } from "react";
import { apiGet, apiPut } from "../../services/api";
const TIME_ZONE = "America/Mexico_City";
const LABELS = {
  pendiente: "Pendiente",
  visto_bueno: "Visto bueno",
  observada: "Observada",
  no_avalada: "No avalada"
};
const INCIDENCIAS = {
  vacaciones: "Vacaciones",
  incapacidad: "Incapacidad",
  festivo: "Día festivo",
};
const tipoEntrada = item => {
  const incidencia =
    INCIDENCIAS[item?.incidencia_tipo] ||
    item?.incidencia_tipo ||
    "Incidencia";
  if (item?.tipo_excepcion === "ambas") {
    return `${incidencia} + Día no laborable`;
  }
  if (item?.tipo_excepcion === "dia_no_laborable") {
    return "Día no laborable";
  }
  return incidencia;
};
const STATUS_STYLE = {
  pendiente: {
    background: "#fffbeb",
    borderColor: "#fde68a",
    color: "#92400e"
  },
  visto_bueno: {
    background: "#ecfdf5",
    borderColor: "#a7f3d0",
    color: "#047857"
  },
  observada: {
    background: "#eff6ff",
    borderColor: "#bfdbfe",
    color: "#1d4ed8"
  },
  no_avalada: {
    background: "#fef2f2",
    borderColor: "#fecaca",
    color: "#b91c1c"
  },
};
const S = {
  page: {
    display: "grid",
    gap: 12,
    padding: "14px 0 24px"
  },
  panel: {
    background: "#fff",
    border: "1px solid #e2e8f0",
    borderRadius: 16,
    padding: 15
  },
  title: {
    margin: 0,
    color: "#0f172a",
    fontSize: 20,
    fontWeight: 800
  },
  subtitle: {
    margin: "5px 0 0",
    color: "#64748b",
    fontSize: 12
  },
  label: {
    display: "block",
    fontWeight: 800,
    fontSize: 12,
    color: "#475569",
    marginBottom: 6
  },
  input: {
    width: "100%",
    boxSizing: "border-box",
    height: 38,
    border: "1px solid #cbd5e1",
    background: "#fff",
    color: "#0f172a",
    borderRadius: 9,
    padding: "8px 11px",
    fontSize: 13
  },
  primary: {
    border: 0,
    background: "#4f46e5",
    color: "#fff",
    padding: "10px 14px",
    borderRadius: 9,
    fontWeight: 800,
    cursor: "pointer",
    minHeight: 40
  },
  secondary: {
    border: "1px solid #cbd5e1",
    background: "#fff",
    color: "#334155",
    padding: "9px 13px",
    borderRadius: 9,
    fontWeight: 800,
    cursor: "pointer",
    minHeight: 40
  },
  tableWrap: {
    overflowX: "auto",
    border: "1px solid #e2e8f0",
    borderRadius: 12
  },
  th: {
    background: "#f8fafc",
    color: "#334155",
    fontSize: 12,
    fontWeight: 800,
    textAlign: "center",
    borderBottom: "1px solid #e2e8f0",
    padding: "12px 10px",
    whiteSpace: "nowrap"
  },
  td: {
    padding: "12px 10px",
    textAlign: "center",
    fontSize: 12,
    color: "#0f172a",
    borderBottom: "1px solid #f1f5f9",
    verticalAlign: "middle"
  },
  alert: {
    borderRadius: 10,
    padding: "11px 13px",
    fontWeight: 700,
    fontSize: 13,
    display: "flex",
    alignItems: "center",
    gap: 10
  },
};
function fmt(value) {
  if (!value) return "—";
  const raw = String(value).slice(0, 10);
  const bits = raw.split("-");
  return bits.length === 3 ? `${bits[2]}/${bits[1]}/${bits[0]}` : raw;
}
function fmtDateTime(value) {
  if (!value) return "—";
  // The API sends UTC timestamps with Z; convert to the same time zone used in Asistencias.
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  const p = new Intl.DateTimeFormat("es-MX", {
    timeZone: TIME_ZONE,
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  }).formatToParts(date);
  const get = type => p.find(x => x.type === type)?.value || "";
  return `${get("day")}/${get("month")}/${get("year")} ${get("hour")}:${get("minute")}:${get("second")}`;
}
function Status({ value }) {
  return <span
    style={{
      border: "1px solid",
      borderRadius: 999,
      padding: "5px 9px",
      display: "inline-block",
      fontSize: 11,
      fontWeight: 800,
      whiteSpace: "nowrap",
      ...(STATUS_STYLE[value] || STATUS_STYLE.pendiente)
    }}
  >{LABELS[value] || value || "—"}</span>;
}
export default function AdminEntradasConIncidencia() {
  const [records, setRecords] = useState([]);
  const [empresas, setEmpresas] = useState([]);
  const [unidades, setUnidades] = useState([]);
  const [filters, setFilters] = useState({
    estado: "pendiente",
    buscar: "",
    empresa_id: "",
    unidad_servicio_id: "",
    desde: "",
    hasta: "",
    per_page: "25"
  });
  const [applied, setApplied] = useState({
    estado: "pendiente",
    buscar: "",
    empresa_id: "",
    unidad_servicio_id: "",
    desde: "",
    hasta: "",
    per_page: "25"
  });
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [pendientes, setPendientes] = useState(0);
  const [selected, setSelected] = useState(null);
  const [estadoNuevo, setEstadoNuevo] = useState("visto_bueno");
  const [observaciones, setObservaciones] = useState("");
  const [touched, setTouched] = useState(false);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [notice, setNotice] = useState(null);
  const [refresh, setRefresh] = useState(0);
  const load = useCallback(async () => {
    setLoading(true);
    setError("");
    try {
      const query = new URLSearchParams({ ...applied, page: String(page) });
      const data = await apiGet(`/admin/entradas-con-incidencia?${query}`);
      setRecords(data?.registros?.data || []);
      setEmpresas(Array.isArray(data?.filtros?.empresas) ? data.filtros.empresas : []);
      setUnidades(Array.isArray(data?.filtros?.unidades) ? data.filtros.unidades : []);
      setLastPage(data?.registros?.last_page || 1);
      setTotal(data?.registros?.total || 0);
      setPendientes(data?.pendientes || 0);
    } catch (e) {
      setError(e?.message || "No se pudieron consultar las entradas con incidencia.");
    } finally { setLoading(false); }
  }, [applied, page, refresh]);
  useEffect(() => { load(); }, [load]);
  useEffect(() => {
    if (!notice) return undefined;
    const timer = setTimeout(() => setNotice(null), 5500);
    return () => clearTimeout(timer);
  }, [notice]);
  const searchRecords = e => {
    e.preventDefault();
    if (filters.desde && filters.hasta && filters.desde > filters.hasta) {
      setError("La fecha 'Hasta' no puede ser anterior a 'Desde'.");
      return;
    }
    setError(""); setPage(1); setApplied({ ...filters });
    setRefresh(x => x + 1);
  };
  const clearFilters = () => {
    const clean = {
      estado: "pendiente",
      buscar: "",
      empresa_id: "",
      unidad_servicio_id: "",
      desde: "",
      hasta: "",
      per_page: "25"
    };
    setFilters(clean); setApplied(clean); setPage(1); setError(""); setRefresh(x => x + 1);
  };
  const openReview = item => {
    setSelected(item);
    setEstadoNuevo(item.estado === "pendiente" ? "visto_bueno" : item.estado);
    setObservaciones(""); setTouched(false); setError("");
  };
  const closeReview = () => { if (!saving) { setSelected(null); setObservaciones(""); setTouched(false); } };
  const validReason = observaciones.trim().length >= 5 && observaciones.trim().length <= 2000;
  const reasonError = !observaciones.trim()
    ? "Escribe las observaciones de RH/Nóminas (mínimo 5 caracteres)."
    : observaciones.trim().length < 5
      ? `Faltan ${5 - observaciones.trim().length} caracteres para el mínimo requerido.`
      : observaciones.trim().length > 2000
        ? "Máximo 2,000 caracteres."
        : "";
  const review = async e => {
    e.preventDefault();
    setTouched(true);
    if (!selected || saving || !validReason) return;
    if (selected.asistencia_eliminada) {
      setError(
        "No se puede revisar esta entrada porque la asistencia fue eliminada."
      );
      return;
    }
    if (!window.confirm("¿Guardar el dictamen administrativo? La asistencia y las incidencias existentes se conservarán.")) return;
    setSaving(true); setError(""); setNotice(null);
    try {
      const result = await apiPut(
        `/admin/entradas-con-incidencia/${selected.id}/revision`,
        {
          estado: estadoNuevo,
          observaciones: observaciones.trim(),
        }
      );
      setSelected(null); setObservaciones(""); setTouched(false);
      setNotice({ type: "success", text: result?.message || "Revisión registrada correctamente." });
      setRefresh(x => x + 1);
    } catch (e) { setError(e?.message || "No se pudo guardar el dictamen."); }
    finally { setSaving(false); }
  };
  return <div style={S.page}>
    <section style={S.panel}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "start",
          gap: 16,
          flexWrap: "wrap"
        }}
      >
        <div>
          <h2 style={S.title}>Entradas extraordinarias</h2>
          <p style={S.subtitle}>Revisión posterior de entradas extraordinarias. No modifica las incidencias originales ni las horas trabajadas.</p>
        </div>
        <div
          style={{
            background: "#fffbeb",
            border: "1px solid #fde68a",
            color: "#92400e",
            padding: "8px 12px",
            borderRadius: 10,
            fontWeight: 800,
            fontSize: 12
          }}
        >
          Pendientes: {" "}
          {pendientes}
        </div>
      </div>
      <form
        onSubmit={searchRecords}
        style={{ marginTop: 18 }}
      >
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(155px, 1fr))",
            gap: 10
          }}
        >
          <div>
            <label
              htmlFor="ei-desde"
              style={S.label}
            >Fecha desde</label>
            <input
              id="ei-desde"
              type="date"
              value={filters.desde}
              onChange={e => setFilters(v => ({ ...v, desde: e.target.value }))}
              style={S.input}
            />
          </div>
          <div>
            <label
              htmlFor="ei-hasta"
              style={S.label}
            >Fecha hasta</label>
            <input
              id="ei-hasta"
              type="date"
              value={filters.hasta}
              onChange={e => setFilters(v => ({ ...v, hasta: e.target.value }))}
              style={S.input}
            />
          </div>
          <div>
            <label
              htmlFor="ei-estado"
              style={S.label}
            >Estado</label>
            <select
              id="ei-estado"
              value={filters.estado}
              onChange={e => setFilters(v => ({ ...v, estado: e.target.value }))}
              style={S.input}
            >
              <option value="pendiente">Pendientes</option>
              <option value="todos">Todos</option>
              <option value="visto_bueno">Con visto bueno</option>
              <option value="observada">Observadas</option>
              <option value="no_avalada">No avaladas</option>
            </select>
          </div>
          <div>
            <label htmlFor="ei-empresa" style={S.label}>Empresa laboral</label>
            <select
              id="ei-empresa"
              value={filters.empresa_id}
              onChange={e => setFilters(v => ({ ...v, empresa_id: e.target.value }))}
              style={S.input}
            >
              <option value="">Todas</option>
              {empresas.map(empresa => (
                <option key={empresa.id} value={empresa.id}>{empresa.nombre}</option>
              ))}
            </select>
          </div>
          <div>
            <label htmlFor="ei-unidad" style={S.label}>Unidad laboral</label>
            <select
              id="ei-unidad"
              value={filters.unidad_servicio_id}
              onChange={e => setFilters(v => ({ ...v, unidad_servicio_id: e.target.value }))}
              style={S.input}
            >
              <option value="">Todas</option>
              {unidades.map(unidad => (
                <option key={unidad.id} value={unidad.id}>{unidad.nombre}</option>
              ))}
            </select>
          </div>
          <div>
            <label
              htmlFor="ei-buscar"
              style={S.label}
            >Buscar empleado</label>
            <input
              id="ei-buscar"
              type="search"
              maxLength={150}
              placeholder="Nombre o correo"
              value={filters.buscar}
              onChange={e => setFilters(v => ({ ...v, buscar: e.target.value }))}
              style={S.input}
            />
          </div>
          <div>
            <label
              htmlFor="ei-perpage"
              style={S.label}
            >Por página</label>
            <select
              id="ei-perpage"
              value={filters.per_page}
              onChange={e => setFilters(v => ({ ...v, per_page: e.target.value }))}
              style={S.input}
            >
              <option value="10">10 registros</option>
              <option value="25">25 registros</option>
              <option value="50">50 registros</option>
              <option value="100">100 registros</option>
            </select>
          </div>
        </div>
        <div
          style={{
            display: "flex",
            gap: 8,
            flexWrap: "wrap",
            marginTop: 12
          }}
        >
          <button
            type="submit"
            style={S.primary}
            disabled={loading}
          >
            <i
              className="fa-solid fa-magnifying-glass"
              style={{ marginRight: 7 }}
            />Consultar
          </button>
          <button
            type="button"
            style={S.secondary}
            onClick={clearFilters}
            disabled={loading}
          >Limpiar filtros</button>
        </div>
      </form>
    </section>
    {error && <div
      role="alert"
      style={{
        ...S.alert,
        background: "#fef2f2",
        color: "#991b1b",
        border: "1px solid #fecaca"
      }}
    >
      <i className="fa-solid fa-circle-exclamation" />
      {error}
    </div>}
    {notice && <div
      role="status"
      style={{
        ...S.alert,
        background: "#f0fdf4",
        color: "#166534",
        border: "1px solid #bbf7d0"
      }}
    >
      <i className="fa-solid fa-circle-check" />
      {notice.text}
    </div>}
    <section style={S.panel}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          flexWrap: "wrap",
          gap: 10,
          marginBottom: 12
        }}
      >
        <div>
          <div style={{
            fontWeight: 800,
            color: "#0f172a",
            fontSize: 14
          }}>Entradas extraordinarias registradas</div>
          <div style={{
            color: "#64748b",
            fontSize: 12,
            marginTop: 4
          }}>
            Total encontrado: {" "}
            {total}
          </div>
        </div>
        <button
          type="button"
          style={S.secondary}
          disabled={loading}
          onClick={() => setRefresh(x => x + 1)}
        >
          <i
            className="fa-solid fa-rotate"
            style={{ marginRight: 7 }}
          />Actualizar
        </button>
      </div>
      <div style={S.tableWrap}>
        <table
          style={{
            width: "100%",
            minWidth: 1000,
            borderCollapse: "collapse"
          }}
        >
          <thead>
            <tr>{["Empleado", "Fecha", "Tipo de excepción", "Motivo operativo", "Entrada / Salida", "Estado", "Historial / Acción"].map(h => <th
              key={h}
              style={S.th}
            >{h}</th>)}</tr>
          </thead>
          <tbody>
            {records.map(item => <tr key={item.id}>
              <td style={{ ...S.td, minWidth: 175 }}>
                <strong>{item.user?.name || "—"}</strong>
                <div style={{
                  color: "#64748b",
                  fontSize: 11,
                  marginTop: 4
                }}>{item.user?.email || ""}</div>
              </td>
              <td style={S.td}>{fmt(item.fecha)}</td>
              <td style={S.td}>{tipoEntrada(item)}</td>
              <td style={{
                ...S.td,
                maxWidth: 240,
                overflowWrap: "anywhere"
              }}>{item.motivo || "—"}</td>
              <td style={S.td}>
                Entrada:{" "}
                {item.entrada_hora || "—"}
                <div style={{ marginTop: 4 }}>
                  Salida:{" "}
                  {item.asistencia_eliminada ? "—" : (item.salida_hora || "Pendiente")}
                </div>
              </td>
              <td style={S.td}>
                <Status value={item.estado} />
                {item.asistencia_eliminada && (
                  <div style={{ marginTop: 7 }}>
                    <span
                      style={{
                        display: "inline-block",
                        background: "#fef2f2",
                        border: "1px solid #fecaca",
                        color: "#b91c1c",
                        padding: "5px 9px",
                        borderRadius: 999,
                        fontSize: 11,
                        fontWeight: 800
                      }}
                    >
                      Asistencia eliminada
                    </span>
                  </div>
                )}
              </td>
              <td style={S.td}>
                <button
                  type="button"
                  style={{
                    ...S.secondary,
                    minHeight: 34,
                    fontSize: 12
                  }}
                  onClick={() => openReview(item)}
                >
                  <i
                    className="fa-solid fa-clock-rotate-left"
                    style={{ marginRight: 6 }}
                  />
                  {item.asistencia_eliminada
                    ? "Ver historial"
                    : "Revisar / historial"}
                </button>
              </td>
            </tr>)}
            {!records.length && <tr>
              <td
                colSpan={7}
                style={{
                  ...S.td,
                  padding: 24,
                  color: "#64748b"
                }}
              >{loading ? "Cargando registros..." : "No hay registros para los filtros aplicados."}</td>
            </tr>}
          </tbody>
        </table>
      </div>
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          flexWrap: "wrap",
          gap: 10,
          marginTop: 12
        }}
      >
        <span style={{ color: "#64748b", fontSize: 12 }}>
          Página {" "}
          {page} de {" "}
          {lastPage}
        </span>
        <div style={{ display: "flex", gap: 8 }}>
          <button
            type="button"
            style={S.secondary}
            disabled={page <= 1 || loading}
            onClick={() => setPage(v => v - 1)}
          >Anterior</button>
          <button
            type="button"
            style={S.secondary}
            disabled={page >= lastPage || loading}
            onClick={() => setPage(v => v + 1)}
          >Siguiente</button>
        </div>
      </div>
    </section>
    {selected && <div
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 1000,
        background: "rgba(15,23,42,.56)",
        display: "grid",
        placeItems: "center",
        padding: 14
      }}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="ei-dialog-title"
        style={{
          background: "#fff",
          borderRadius: 16,
          padding: 18,
          width: "100%",
          maxWidth: 650,
          maxHeight: "90vh",
          overflowY: "auto",
          boxSizing: "border-box",
          boxShadow: "0 15px 45px rgba(15,23,42,.15)"
        }}
      >
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            gap: 12,
            marginBottom: 16
          }}
        >
          <div>
            <h3
              id="ei-dialog-title"
              style={{
                margin: 0,
                fontSize: 17,
                color: "#0f172a"
              }}
            >{selected.asistencia_eliminada
              ? "Historial de entrada extraordinaria"
              : "Revisión de entrada extraordinaria"}</h3>
            <p style={{ ...S.subtitle, marginTop: 5 }}>{selected.asistencia_eliminada
              ? "Consulta el historial de una asistencia eliminada."
              : "Consulta el registro y emite un dictamen administrativo."}</p>
          </div>
          <button
            type="button"
            style={{ ...S.secondary, minWidth: 36 }}
            onClick={closeReview}
            disabled={saving}
            aria-label="Cerrar"
          >
            <i className="fa-solid fa-xmark" />
          </button>
        </div>
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(170px, 1fr))",
            gap: 9,
            marginBottom: 13
          }}
        >
          {[
            ["Empleado", selected.user?.name || "—"],
            ["Fecha", fmt(selected.fecha)],
            ["Tipo de excepción", tipoEntrada(selected)],
            ["Estado actual", <Status value={selected.estado} />],
          ].map(([lab, val]) => <div
            key={lab}
            style={{
              padding: 11,
              border: "1px solid #e2e8f0",
              borderRadius: 10,
              background: "#f8fafc"
            }}
          >
            <div style={{ ...S.label, marginBottom: 5 }}>{lab}</div>
            <div style={{
              fontWeight: 700,
              color: "#0f172a",
              fontSize: 13
            }}>{val}</div>
          </div>)}
        </div>
        <div
          style={{
            border: "1px solid #e2e8f0",
            borderRadius: 10,
            padding: 12,
            marginBottom: 14,
            fontSize: 13
          }}
        >
          <div>
            <b>Motivo operativo:</b>{" "}
            {selected.motivo || "—"}
          </div>
          <div style={{ marginTop: 8 }}>
            <b>Observaciones de la incidencia:</b>{" "}
            {selected.incidencia_observaciones || "—"}
          </div>
          <div style={{ marginTop: 8 }}>
            <b>Horario registrado:</b>{" "}
            {selected.asistencia_eliminada ? "Asistencia eliminada" : `${selected.entrada_hora || "—"} a ${selected.salida_hora || "Pendiente"}`}
          </div>
        </div>
        <div
          style={{
            fontWeight: 800,
            fontSize: 14,
            color: "#0f172a",
            marginBottom: 8
          }}
        >Historial de revisiones</div>
        <div
          style={{
            display: "grid",
            gap: 8,
            marginBottom: 16,
            maxHeight: 215,
            overflowY: "auto"
          }}
        >
          {(selected.revisiones || []).length ? selected.revisiones.map(rev => <div
            key={rev.id}
            style={{
              background: "#f8fafc",
              border: "1px solid #e2e8f0",
              borderRadius: 10,
              padding: 11
            }}
          >
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                gap: 8,
                flexWrap: "wrap",
                alignItems: "center"
              }}
            >
              <strong style={{ fontSize: 12 }}>
                {LABELS[rev.estado_anterior] || "Sin revisión"} {" "}→{" "}
                {LABELS[rev.estado_nuevo] || rev.estado_nuevo}
              </strong>
              <span style={{ fontSize: 11, color: "#64748b" }}>{fmtDateTime(rev.created_at)}</span>
            </div>
            <div style={{
              fontSize: 12,
              color: "#64748b",
              marginTop: 5
            }}>
              Realizado por: {" "}
              {rev.actor?.name || "Usuario anterior"}
            </div>
            <div
              style={{
                fontSize: 12,
                marginTop: 6,
                whiteSpace: "pre-wrap",
                overflowWrap: "anywhere"
              }}
            >{rev.observaciones || "—"}</div>
          </div>) : <div
            style={{
              padding: 12,
              background: "#f8fafc",
              borderRadius: 9,
              color: "#64748b",
              fontSize: 12
            }}
          >Aún no hay revisiones.</div>}
        </div>
        {selected.asistencia_eliminada && (
          <div
            role="status"
            style={{
              ...S.alert,
              background: "#fef2f2",
              border: "1px solid #fecaca",
              color: "#991b1b",
              marginBottom: 14
            }}
          >
            <i className="fa-solid fa-circle-exclamation" />
            Esta asistencia fue eliminada. Su historial se conserva
            únicamente para consulta y no se permiten nuevas revisiones.
          </div>
        )}
        {!selected.asistencia_eliminada && (
          <form
            onSubmit={review}
            noValidate
          >
            <div style={{ marginBottom: 12 }}>
              <label
                style={S.label}
                htmlFor="ei-dictamen"
              >Dictamen *</label>
              <select
                id="ei-dictamen"
                style={S.input}
                value={estadoNuevo}
                onChange={e => setEstadoNuevo(e.target.value)}
                disabled={saving}
              >
                <option value="visto_bueno">Visto bueno</option>
                <option value="observada">Observada</option>
                <option value="no_avalada">No avalada</option>
              </select>
            </div>
            <div>
              <label
                style={S.label}
                htmlFor="ei-observaciones"
              >Observaciones de RH/Nóminas *</label>
              <textarea
                id="ei-observaciones"
                rows={3}
                maxLength={2000}
                value={observaciones}
                onBlur={() => setTouched(true)}
                onChange={e => { setObservaciones(e.target.value); if (touched && e.target.value.trim().length >= 5) setTouched(false); }}
                placeholder="Describe el motivo del dictamen (mínimo 5 caracteres)"
                style={{
                  ...S.input,
                  height: "auto",
                  minHeight: 96,
                  resize: "vertical",
                  borderColor: touched && !validReason ? "#dc2626" : "#cbd5e1"
                }}
                aria-invalid={touched && !validReason}
                aria-describedby="ei-help"
                disabled={saving}
              />
              <div
                id="ei-help"
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  gap: 10,
                  flexWrap: "wrap",
                  marginTop: 5,
                  fontSize: 11,
                  color: touched && !validReason ? "#b91c1c" : "#64748b"
                }}
              >
                <span>{touched && !validReason ? reasonError : "Obligatorias: mínimo 5 y máximo 2,000 caracteres."}</span>
                <span>
                  {observaciones.length}/2000
                </span>
              </div>
            </div>
            {error && <div
              role="alert"
              style={{
                ...S.alert,
                background: "#fef2f2",
                color: "#991b1b",
                border: "1px solid #fecaca",
                marginTop: 10
              }}
            >
              <i className="fa-solid fa-circle-exclamation" />
              {error}
            </div>}
            <div
              style={{
                display: "flex",
                justifyContent: "flex-end",
                flexWrap: "wrap",
                gap: 8,
                marginTop: 16
              }}
            >
              <button
                type="button"
                style={S.secondary}
                onClick={closeReview}
                disabled={saving}
              >Cancelar</button>
              <button
                type="submit"
                style={{ ...S.primary, opacity: saving ? .65 : 1 }}
                disabled={saving}
              >
                <i
                  className="fa-solid fa-floppy-disk"
                  style={{ marginRight: 7 }}
                />
                {saving ? "Guardando..." : "Guardar revisión"}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>}
  </div>;
}
