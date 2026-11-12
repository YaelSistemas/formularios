import React, {
  useEffect,
  useState,
} from "react";

import {
  apiGet,
  apiPost,
  apiPut,
  apiDelete,
} from "../../services/api";

function getStoredUser() {
  try {
    return JSON.parse(
      localStorage.getItem("user") ||
        "null"
    );
  } catch {
    return null;
  }
}

function normalizeRoles(user) {
  if (!user) return [];

  const rolesFromArray =
    Array.isArray(user.roles)
      ? user.roles
          .map((role) =>
            typeof role === "string"
              ? role
              : role?.name
          )
          .filter(Boolean)
      : [];

  const roleSingle =
    user.role
      ? [
          typeof user.role === "string"
            ? user.role
            : user.role?.name,
        ].filter(Boolean)
      : [];

  return [
    ...new Set([
      ...rolesFromArray,
      ...roleSingle,
    ]),
  ];
}

function normalizePermissions(user) {
  if (!user) return [];

  return [
    ...new Set(
      Array.isArray(user.permissions)
        ? user.permissions
            .map((permission) =>
              typeof permission ===
              "string"
                ? permission
                : permission?.name
            )
            .filter(Boolean)
        : []
    ),
  ];
}

function isAdministrator(user) {
  if (user?.is_admin) {
    return true;
  }

  return normalizeRoles(user).some(
    (role) =>
      String(role).toLowerCase() ===
      "administrador"
  );
}

function hasPermission(
  user,
  permission
) {
  if (isAdministrator(user)) {
    return true;
  }

  return normalizePermissions(
    user
  ).includes(permission);
}

function formatDate(value) {
  if (!value) {
    return "—";
  }

  const raw =
    String(value).slice(0, 10);

  const parts =
    raw.split("-");

  if (parts.length !== 3) {
    return raw;
  }

  return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function getTipoLabel(value) {
  const tipos = {
    vacaciones: "Vacaciones",
    incapacidad: "Incapacidad",
    festivo: "Día festivo",
  };

  return tipos[value] || value || "—";
}

function getTipoStyle(value) {
  if (value === "vacaciones") {
    return {
      background: "#eff6ff",
      border: "#bfdbfe",
      color: "#1d4ed8",
    };
  }

  if (value === "incapacidad") {
    return {
      background: "#fff7ed",
      border: "#fed7aa",
      color: "#c2410c",
    };
  }

  if (value === "festivo") {
    return {
      background: "#f0fdf4",
      border: "#bbf7d0",
      color: "#15803d",
    };
  }

  return {
    background: "#f8fafc",
    border: "#cbd5e1",
    color: "#475569",
  };
}

function formatHistoryDateTime(
  value
) {
  if (!value) {
    return "—";
  }

  const raw =
    String(value);

  const [
    datePart,
    timePart,
  ] = raw.split(" ");

  return `${formatDate(
    datePart
  )}${timePart ? ` ${timePart}` : ""}`;
}

function getHistoryActionLabel(
  action
) {
  const labels = {
    created: "Creación",
    updated: "Edición",
    deleted: "Eliminación",
  };

  return (
    labels[action] ||
    action ||
    "Movimiento"
  );
}

function getHistoryFieldLabel(
  field
) {
  const labels = {
    empleado: "Empleado",
    tipo: "Tipo",
    fecha_desde: "Desde",
    fecha_hasta: "Hasta",
    dias: "Días",
    alcance: "Alcance",
    empleados: "Empleados incluidos",
    empleados_modificados: "Empleados modificados",
    empleados_retirados: "Empleados retirados",
    nombres_empleados: "Nombres de empleados retirados",
    user_ids: "Identificadores de empleados",
    modo: "Modalidad de edición",
    detalle_empleados: "Cambios por empleado",
    observaciones:
      "Observaciones",
  };

  return (
    labels[field] ||
    field
  );
}

function formatHistoryValue(
  field,
  value
) {
  if (
    value === null ||
    value === undefined ||
    value === ""
  ) {
    return "—";
  }

  if (field === "nombres_empleados" && Array.isArray(value)) {
    return value.length ? value.join(", ") : "—";
  }

  if (field === "tipo") {
    return getTipoLabel(value);
  }
  if (field === "modo") {
    return { todos: "Todos los empleados", seleccion: "Sólo empleados seleccionados", originales: "Sólo empleados sin cambios previos" }[value] || String(value);
  }
  if (field === "alcance") {
    return { todos: "Todos los empleados", unidad: "Unidad de servicio", seleccion: "Empleados seleccionados" }[value] || String(value);
  }

  if (
    field === "fecha_desde" ||
    field === "fecha_hasta"
  ) {
    return formatDate(value);
  }

  if (
    typeof value === "object"
  ) {
    if (value?.name) {
      return value.email
        ? `${value.name} (${value.email})`
        : value.name;
    }

    return JSON.stringify(
      value
    );
  }

  return String(value);
}

const EMPTY_FORM = {
  user_id: "",
  tipo: "",
  fecha_desde: "",
  fecha_hasta: "",
  observaciones: "",
  alcance: "individual", unidad_id: "", user_ids: [],
};

const EMPTY_FILTERS = {
  fecha_desde: "",
  fecha_hasta: "",
  user_id: "",
  tipo: "",
  search: "",
};

export default function AdminAsistenciaIncidencias() {
  const [me, setMe] =
    useState(
      () => getStoredUser()
    );

  const [
    incidencias,
    setIncidencias,
  ] = useState([]);

  const [
    usuarios,
    setUsuarios,
  ] = useState([]);

  const [
    tipos,
    setTipos,
  ] = useState([]);
  const [unidades, setUnidades] = useState([]);
  const [bulkDetail, setBulkDetail] = useState(null);
  const [bulkSelected, setBulkSelected] = useState([]);
  const [bulkMode, setBulkMode] = useState("seleccion");
  const [bulkSearch, setBulkSearch] = useState("");
  const [bulkLoading, setBulkLoading] = useState(false);

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    saving,
    setSaving,
  ] = useState(false);

  const [exportFormat, setExportFormat] = useState("detalle");

  const [
    exportLoading,
    setExportLoading,
  ] = useState("");

  const [
    error,
    setError,
  ] = useState("");

  const [
    successMessage,
    setSuccessMessage,
  ] = useState(null);

  const [
    filters,
    setFilters,
  ] = useState({
    ...EMPTY_FILTERS,
  });

  const [
    appliedFilters,
    setAppliedFilters,
  ] = useState({
    ...EMPTY_FILTERS,
  });

  const [
    modalOpen,
    setModalOpen,
  ] = useState(false);

  const [
    editingGrupoUuid,
    setEditingGrupoUuid,
  ] = useState(null);

  const [
    form,
    setForm,
  ] = useState({
    ...EMPTY_FORM,
  });

  const [
    modalError,
    setModalError,
  ] = useState("");

  const [
    historyItem,
    setHistoryItem,
  ] = useState(null);
  
  const [
    historyItems,
    setHistoryItems,
  ] = useState([]);
  
  const [
    historyLoading,
    setHistoryLoading,
  ] = useState(false);
  
  const [
    historyError,
    setHistoryError,
  ] = useState("");
  
  const [
    generalHistoryOpen,
    setGeneralHistoryOpen,
  ] = useState(false);
  
  const [
    generalHistoryRows,
    setGeneralHistoryRows,
  ] = useState([]);
  
  const [
    generalHistoryLoading,
    setGeneralHistoryLoading,
  ] = useState(false);
  
  const [
    generalHistoryError,
    setGeneralHistoryError,
  ] = useState("");
  
  const [
    generalHistoryPage,
    setGeneralHistoryPage,
  ] = useState(1);
  
  const [
    generalHistoryPagination,
    setGeneralHistoryPagination,
  ] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });

  const canCreate =
    hasPermission(
      me,
      "asistencias.incidencias.create"
    );

  const canEdit =
    hasPermission(
      me,
      "asistencias.incidencias.edit"
    );

  const canDelete =
    hasPermission(
      me,
      "asistencias.incidencias.delete"
    );

  const canViewHistory =
    isAdministrator(me);

  function showSuccessMessage(
    message,
    type = "create"
  ) {
    setSuccessMessage({
      message,
      type,
    });
  
    window.setTimeout(() => {
      setSuccessMessage(null);
    }, 5000);
  }

  useEffect(() => {
    function handleUserUpdated(
      event
    ) {
      if (event?.detail) {
        setMe(event.detail);
      }
    }

    window.addEventListener(
      "current-user-updated",
      handleUserUpdated
    );

    return () => {
      window.removeEventListener(
        "current-user-updated",
        handleUserUpdated
      );
    };
  }, []);

  function buildQuery(
    currentFilters
  ) {
    const params =
      new URLSearchParams();

    if (
      currentFilters.fecha_desde
    ) {
      params.set(
        "fecha_desde",
        currentFilters.fecha_desde
      );
    }

    if (
      currentFilters.fecha_hasta
    ) {
      params.set(
        "fecha_hasta",
        currentFilters.fecha_hasta
      );
    }

    if (
      currentFilters.user_id
    ) {
      params.set(
        "user_id",
        currentFilters.user_id
      );
    }

    if (currentFilters.tipo) {
      params.set(
        "tipo",
        currentFilters.tipo
      );
    }

    if (
      currentFilters.search.trim()
    ) {
      params.set(
        "search",
        currentFilters.search.trim()
      );
    }

    return params.toString();
  }

  async function exportIncidencias(
    type
  ) {
    if (exportLoading) {
      return;
    }
  
    const isExcel =
      type === "excel";
  
    setExportLoading(type);
    setError("");
  
    try {
      const params = new URLSearchParams(buildQuery(appliedFilters));
      params.set("formato", exportFormat);
      const query = params.toString();
  
      const endpoint =
        isExcel
          ? "/admin/asistencia-incidencias/export/excel"
          : "/admin/asistencia-incidencias/export/pdf";
  
      const url =
        `/api${endpoint}` +
        (
          query
            ? `?${query}`
            : ""
        );
  
      const response =
        await fetch(
          url,
          {
            headers: {
              Accept:
                isExcel
                  ? "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                  : "application/pdf",
  
              Authorization:
                `Bearer ${localStorage.getItem(
                  "token"
                )}`,
            },
          }
        );
  
      if (!response.ok) {
        let message =
          "No se pudo generar la exportación.";
  
        try {
          const data =
            await response.json();
  
          message =
            data?.message ||
            message;
        } catch {
          // Mantener mensaje genérico.
        }
  
        throw new Error(
          message
        );
      }
  
      const blob =
        await response.blob();
  
      const blobUrl =
        URL.createObjectURL(
          blob
        );
  
      const disposition =
        response.headers.get(
          "Content-Disposition"
        ) || "";
  
      let fileName =
        isExcel
          ? "incidencias_asistencia.xlsx"
          : "incidencias_asistencia.pdf";
  
      const fileNameMatch =
        disposition.match(
          /filename="?([^"]+)"?/i
        );
  
      if (
        fileNameMatch?.[1]
      ) {
        fileName =
          fileNameMatch[1];
      }
  
      const link =
        document.createElement(
          "a"
        );
  
      link.href =
        blobUrl;
  
      link.download =
        fileName;
  
      document.body.appendChild(
        link
      );
  
      link.click();
  
      link.remove();
  
      URL.revokeObjectURL(
        blobUrl
      );
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo generar la exportación."
      );
    } finally {
      setExportLoading("");
    }
  }

  async function load(
    currentFilters =
      appliedFilters
  ) {
    setLoading(true);
    setError("");

    try {
      const query =
        buildQuery(
          currentFilters
        );

      const endpoint =
        "/admin/asistencia-incidencias" +
        (query
          ? `?${query}`
          : "");

      const data =
        await apiGet(
          endpoint
        );

      setIncidencias(
        Array.isArray(
          data?.incidencias
        )
          ? data.incidencias
          : []
      );

      setUsuarios(
        Array.isArray(
          data?.usuarios
        )
          ? data.usuarios
          : []
      );

      setTipos(Array.isArray(data?.tipos) ? data.tipos : []);
      setUnidades(Array.isArray(data?.unidades) ? data.unidades : []);
    } catch (err) {
      setError(
        err?.message ||
          "No se pudieron cargar las incidencias."
      );
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load(
      EMPTY_FILTERS
    );
  }, []);

  function consultar() {
    setAppliedFilters({
      ...filters,
    });

    load(filters);
  }

  function limpiar() {
    const clean = {
      ...EMPTY_FILTERS,
    };

    setFilters(clean);

    setAppliedFilters(
      clean
    );

    load(clean);
  }

  function openCreate() {
    if (!canCreate) {
      return;
    }

    setEditingGrupoUuid(null);
    setBulkDetail(null);
    setBulkSelected([]);
    setBulkMode("seleccion");

    setForm({
      ...EMPTY_FORM,
    });

    setModalError("");
    setError("");
    setSuccessMessage(null);
    setModalOpen(true);
  }

  async function openEdit(
    row
  ) {
    if (
      !canEdit ||
      !row?.grupo_uuid
    ) {
      return;
    }

    setModalError("");
    setError("");
    setSuccessMessage(null);
    setSaving(true);

    try {
      const data =
        await apiGet(
          `/admin/asistencia-incidencias/${row.grupo_uuid}`
        );

      setEditingGrupoUuid(row.grupo_uuid);
      setBulkDetail(data?.es_masiva ? data : null);
      setBulkSelected([]);
      setBulkMode("seleccion");

      setForm({
        user_id: String(data?.user_id || ""),
        alcance: data?.es_masiva ? (data?.alcance || "todos") : "individual",
        unidad_id: "",
        user_ids: [],

        tipo:
          data?.tipo || "",

        fecha_desde:
          data?.fecha_desde || "",

        fecha_hasta:
          data?.fecha_hasta || "",

        observaciones:
          data?.observaciones ||
          "",
      });

      setModalOpen(true);
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo cargar la incidencia."
      );
    } finally {
      setSaving(false);
    }
  }

  function closeModal() {
    if (saving) {
      return;
    }

    setModalOpen(false);
    setBulkDetail(null);
    setBulkSelected([]);
    setEditingGrupoUuid(
      null
    );
    setForm({
      ...EMPTY_FORM,
    });
    setModalError("");
  }

  function setField(
    field,
    value
  ) {
    setForm(
      (prev) => ({
        ...prev,
        [field]: value,
      })
    );

    setModalError("");
  }

  async function saveIncidencia(
    event
  ) {
    event.preventDefault();

    if (saving) {
      return;
    }

    if (!editingGrupoUuid && form.alcance === "individual" && !form.user_id) {
      setModalError("Selecciona un empleado.");
      return;
    }
    if (!editingGrupoUuid && form.alcance === "unidad" && !form.unidad_id) {
      setModalError("Selecciona una unidad de servicio.");
      return;
    }
    if (!editingGrupoUuid && form.alcance === "seleccion" && !form.user_ids.length) {
      setModalError("Selecciona al menos un empleado.");
      return;
    }
    if (bulkDetail && bulkMode === "seleccion" && !bulkSelected.length) {
      setModalError("Selecciona los empleados que deseas modificar.");
      return;
    }

    if (!form.tipo) {
      setModalError(
        "Selecciona el tipo de incidencia."
      );
      return;
    }

    if (!form.fecha_desde) {
      setModalError(
        "Selecciona la fecha inicial."
      );
      return;
    }

    if (!form.fecha_hasta) {
      setModalError(
        "Selecciona la fecha final."
      );
      return;
    }

    if (
      form.fecha_hasta <
      form.fecha_desde
    ) {
      setModalError(
        "La fecha final no puede ser menor a la fecha inicial."
      );
      return;
    }

    const payload = {
      ...(bulkDetail ? {modo: bulkMode, user_ids: bulkSelected} :
         form.alcance === "individual" ? {user_id: Number(form.user_id)} : {
           alcance: form.alcance,
           unidad_id: form.unidad_id ? Number(form.unidad_id) : null,
           user_ids: form.user_ids.map(Number),
         }),

      tipo:
        form.tipo,

      fecha_desde:
        form.fecha_desde,

      fecha_hasta:
        form.fecha_hasta,

      observaciones:
        form.observaciones.trim()
          ? form.observaciones.trim()
          : null,
    };

    if (bulkDetail) {
      const affected = bulkMode === "seleccion" ? bulkSelected.length :
        bulkMode === "originales" ? bulkDetail.empleados.filter(e => !e.personalizada).length : bulkDetail.empleados.length;
      if (!affected) { setModalError("No hay empleados que cumplan la selección."); return; }
      if (!window.confirm(`Vas a modificar ${affected} empleado(s). ¿Deseas continuar?`)) return;
    } else if (!editingGrupoUuid && form.alcance !== "individual") {
      if (!window.confirm("La incidencia se registrará para todos los empleados activos dentro del alcance elegido. ¿Deseas continuar?")) return;
    }

    const isEditing =
      Boolean(
        editingGrupoUuid
      );

    setSaving(true);
    setModalError("");
    setError("");
    setSuccessMessage(null);

    try {
      let data;

      if (
        editingGrupoUuid
      ) {
        data =
          await apiPut(
            `/admin/asistencia-incidencias/${editingGrupoUuid}${bulkDetail ? "/masiva" : ""}`,
            payload
          );
      } else {
        data =
          await apiPost(
            "/admin/asistencia-incidencias",
            payload
          );
      }

      showSuccessMessage(
        data?.message ||
          (
            isEditing
              ? "Incidencia actualizada correctamente."
              : "Incidencia registrada correctamente."
          ),
        isEditing
          ? "edit"
          : "create"
      );

      setModalOpen(false);
      setEditingGrupoUuid(null);
      setBulkDetail(null);
      setBulkSelected([]);

      setForm({
        ...EMPTY_FORM,
      });

      await load(
        appliedFilters
      );
    } catch (err) {
      setModalError(
        err?.message ||
          "No se pudo guardar la incidencia."
      );
    } finally {
      setSaving(false);
    }
  }

  async function viewBulk(row) {
    setBulkLoading(true);
    setError("");
    try {
      const data = await apiGet(`/admin/asistencia-incidencias/${row.grupo_uuid}`);
      setBulkDetail(data);
      setBulkSelected([]);
      setBulkSearch("");
    } catch (err) {
      setError(err?.message || "No se pudieron cargar los empleados.");
    } finally {
      setBulkLoading(false);
    }
  }

  async function removeBulkSelected() {
    if (!bulkDetail || !bulkSelected.length || !canDelete) return;
    if (!window.confirm(`¿Retirar la incidencia únicamente a ${bulkSelected.length} empleados seleccionados?`)) return;
    setBulkLoading(true);
    try {
      const resp = await apiPost(`/admin/asistencia-incidencias/${bulkDetail.grupo_uuid}/retirar-empleados`, {user_ids: bulkSelected});
      setBulkDetail(null);
      setBulkSelected([]);
      showSuccessMessage(resp?.message || "Empleados retirados.", "delete");
      await load(appliedFilters);
    } catch (err) {
      setError(err?.message || "No fue posible retirar empleados.");
    } finally {
      setBulkLoading(false);
    }
  }

  function toggleBulkMember(id) {
    setBulkSelected(prev => prev.includes(id) ? prev.filter(n => n !== id) : [...prev, id]);
  }

  function bulkMemberPicker() {
    if (!bulkDetail) return null;
    const members = bulkDetail.empleados || [];
    const visible = members.filter(member => `${member.user?.name || ""} ${member.user?.email || ""}`.toLowerCase().includes(bulkSearch.toLowerCase()));
    return (
      <div style={{...S.field, gridColumn: "1 / -1", marginTop: 8}}>
        <label style={S.label}>Empleados incluidos ({members.length})</label>
        <input style={S.input} placeholder="Buscar nombre o correo..." value={bulkSearch} onChange={e => setBulkSearch(e.target.value)} />
        <div style={{maxHeight: 330, overflowY: "auto", border: "1px solid #cbd5e1", borderRadius: 9, padding: 8, marginTop: 6}}>
          {visible.length === 0 ? <p style={{...S.muted, padding: 10}}>No hay empleados que coincidan con la búsqueda.</p> : null}
          {visible.map(member => {
            const periodos = member.periodos?.length ? member.periodos : [{
              tipo: member.tipo,
              fecha_desde: member.fecha_desde,
              fecha_hasta: member.fecha_hasta,
              observaciones: member.observaciones,
            }];
            return (
              <div key={member.user_id} style={{border: "1px solid #e2e8f0", borderRadius: 9, padding: "10px 12px", marginBottom: 8, background: bulkSelected.includes(member.user_id) ? "#f8faff" : "#fff"}}>
                <label style={{display: "flex", alignItems: "flex-start", gap: 9, cursor: "pointer", fontSize: 13}}>
                  <input type="checkbox" style={{marginTop: 3}} checked={bulkSelected.includes(member.user_id)} onChange={() => toggleBulkMember(member.user_id)} />
                  <span style={{flex: 1, minWidth: 0}}>
                    <strong style={{display: "block", overflowWrap: "anywhere"}}>{member.user?.name || `Empleado ${member.user_id}`}</strong>
                    <small style={{color: "#64748b", overflowWrap: "anywhere"}}>{member.user?.email}</small>
                  </span>
                  <span style={{fontSize: 11, fontWeight: 700, color: member.personalizada ? "#9a3412" : "#166534", whiteSpace: "nowrap"}}>{member.personalizada ? "Modificado" : "Original"}</span>
                </label>
                <div style={{marginLeft: 24, marginTop: 9, display: "grid", gap: 6}}>
                  {periodos.map((periodo, i) => (
                    <div key={`${member.user_id}-${i}`} style={{display: "grid", gridTemplateColumns: "minmax(95px, 1fr) repeat(2, minmax(90px, 1fr))", gap: 8, padding: "8px 10px", background: "#f8fafc", borderRadius: 7, fontSize: 12}}>
                      <div><span style={{display: "block", color: "#64748b", fontSize: 10, fontWeight: 700}}>Tipo</span><strong>{getTipoLabel(periodo.tipo)}</strong></div>
                      <div><span style={{display: "block", color: "#64748b", fontSize: 10, fontWeight: 700}}>Desde</span><strong>{formatDate(periodo.fecha_desde)}</strong></div>
                      <div><span style={{display: "block", color: "#64748b", fontSize: 10, fontWeight: 700}}>Hasta</span><strong>{formatDate(periodo.fecha_hasta)}</strong></div>
                    </div>
                  ))}
                  {periodos.length > 1 ? <small style={{color: "#64748b"}}>{periodos.length} periodos distintos para este empleado</small> : null}
                </div>
              </div>
            );
          })}
        </div>
        <div style={{display: "flex", gap: 8, flexWrap: "wrap", marginTop: 8}}>
          <button type="button" style={S.buttonNeutral} onClick={() => setBulkSelected(members.map(m => m.user_id))}>Seleccionar todos</button>
          <button type="button" style={S.buttonNeutral} onClick={() => setBulkSelected([])}>Quitar selección</button>
          <span style={{alignSelf: "center", fontSize: 12}}>{bulkSelected.length} seleccionados</span>
        </div>
      </div>
    );
  }

  async function deleteIncidencia(
    row
  ) {
    if (
      !canDelete ||
      !row?.grupo_uuid
    ) {
      return;
    }

    const employee = row?.es_masiva
      ? `TODOS los ${row.empleados} empleados de esta incidencia masiva`
      : row?.user?.name || "el empleado";

    const ok =
      window.confirm(
        `¿Deseas eliminar la incidencia de ${employee} del ${formatDate(
          row.fecha_desde
        )} al ${formatDate(
          row.fecha_hasta
        )}?`
      );

    if (!ok) {
      return;
    }

    setError("");
    setSuccessMessage(null);

    try {
      const data =
        await apiDelete(
          `/admin/asistencia-incidencias/${row.grupo_uuid}`
        );

      showSuccessMessage(
        data?.message ||
          "Incidencia eliminada correctamente.",
        "delete"
      );

      await load(
        appliedFilters
      );
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo eliminar la incidencia."
      );
    }
  }

  async function openHistory(
    row
  ) {
    if (
      !canViewHistory ||
      !row?.grupo_uuid
    ) {
      return;
    }
  
    setHistoryItem({
      grupo_uuid:
        row.grupo_uuid,
  
      empleado:
        row.user
          ? {
              id:
                row.user.id,
  
              name:
                row.user.name,
  
              email:
                row.user.email,
            }
          : null,
  
      tipo:
        row.tipo,
  
      fecha_desde:
        row.fecha_desde,
  
      fecha_hasta:
        row.fecha_hasta,
  
      dias:
        row.dias,
  
      observaciones:
        row.observaciones,
  
      registrado_por:
        row.registrado_por,
    });
  
    setHistoryItems([]);
    setHistoryError("");
    setHistoryLoading(true);
  
    try {
      const data =
        await apiGet(
          `/admin/asistencia-incidencias/${row.grupo_uuid}/history`
        );
  
      if (data?.incidencia) {
        setHistoryItem(
          data.incidencia
        );
      }
  
      setHistoryItems(
        Array.isArray(
          data?.history
        )
          ? data.history
          : []
      );
    } catch (err) {
      setHistoryError(
        err?.message ||
          "No se pudo cargar el historial de la incidencia."
      );
    } finally {
      setHistoryLoading(false);
    }
  }
  
  function closeHistory() {
    if (historyLoading) {
      return;
    }
  
    setHistoryItem(null);
    setHistoryItems([]);
    setHistoryError("");
  }
  
  async function loadGeneralHistory(
    requestedPage = 1
  ) {
    setGeneralHistoryLoading(
      true
    );
  
    setGeneralHistoryError("");
  
    try {
      const data =
        await apiGet(
          `/admin/asistencia-incidencias/history?page=${requestedPage}&per_page=25`
        );
  
      const registros =
        data?.registros || {};
  
      setGeneralHistoryRows(
        Array.isArray(
          registros?.data
        )
          ? registros.data
          : []
      );
  
      setGeneralHistoryPagination({
        current_page:
          Number(
            registros?.current_page ||
              1
          ),
  
        last_page:
          Number(
            registros?.last_page ||
              1
          ),
  
        total:
          Number(
            registros?.total ||
              0
          ),
  
        per_page:
          Number(
            registros?.per_page ||
              25
          ),
      });
  
      setGeneralHistoryPage(
        Number(
          registros?.current_page ||
            1
        )
      );
    } catch (err) {
      setGeneralHistoryError(
        err?.message ||
          "No se pudo cargar el historial general de incidencias."
      );
    } finally {
      setGeneralHistoryLoading(
        false
      );
    }
  }
  
  function openGeneralHistory() {
    if (!canViewHistory) {
      return;
    }
  
    setGeneralHistoryOpen(
      true
    );
  
    setGeneralHistoryRows(
      []
    );
  
    setGeneralHistoryError(
      ""
    );
  
    setGeneralHistoryPage(
      1
    );
  
    loadGeneralHistory(1);
  }
  
  function closeGeneralHistory() {
    if (
      generalHistoryLoading
    ) {
      return;
    }
  
    setGeneralHistoryOpen(
      false
    );
  
    setGeneralHistoryRows(
      []
    );
  
    setGeneralHistoryError(
      ""
    );
  
    setGeneralHistoryPage(
      1
    );
  
    setGeneralHistoryPagination({
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 25,
    });
  }
  
  function openHistoryFromGeneral(
    row
  ) {
    if (
      !row?.grupo_uuid
    ) {
      return;
    }
  
    /*
     * El historial general ya trae todos
     * los movimientos, por lo que no es
     * necesario hacer otra petición.
     *
     * Esto funciona también para una
     * incidencia eliminada.
     */
    setHistoryItem({
      grupo_uuid:
        row.grupo_uuid,
  
      empleado:
        row.empleado,
      es_masiva: !!row.es_masiva,
      empleados: row.empleados_originales ?? row.empleados_actuales,
      empleados_originales: row.empleados_originales,
      empleados_actuales: row.empleados_actuales,
      miembros_originales: row.miembros_originales || [],
      miembros_actuales: row.miembros_actuales || [],
  
      tipo:
        row.tipo,
  
      fecha_desde:
        row.fecha_desde,
  
      fecha_hasta:
        row.fecha_hasta,
  
      dias:
        row.dias,
  
      observaciones:
        row.observaciones,
  
      registrado_por:
        row.registrado_por,
  
      deleted:
        row.deleted,
    });
  
    setHistoryItems(
      Array.isArray(
        row.history
      )
        ? row.history
        : []
    );
  
    setHistoryError("");
    setHistoryLoading(false);
  }

  const S = {
    page: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
      width: "100%",
    },

    card: {
      background: "#fff",
      border: "1px solid #e2e8f0",
      borderRadius: 18,
      padding: 16,
      boxShadow:
        "0 8px 24px rgba(15,23,42,.05)",
    },
    
    title: {
      margin: 0,
      fontSize: 24,
      color: "#0f172a",
    },
    
    muted: {
      marginTop: 4,
      fontSize: 13,
      color: "#64748b",
    },
    
    filters: {
      display: "grid",
      gridTemplateColumns:
        "repeat(auto-fit, minmax(190px, 1fr))",
      gap: 12,
      marginTop: 16,
    },
    
    field: {
      display: "flex",
      flexDirection: "column",
    },
    
    label: {
      fontSize: 12,
      fontWeight: 800,
      color: "#64748b",
      marginBottom: 6,
    },
    
    input: {
      width: "100%",
      minHeight: 42,
      padding: "9px 10px",
      borderRadius: 10,
      border:
        "1px solid #cbd5e1",
      boxSizing: "border-box",
      background: "#fff",
      color: "#0f172a",
      fontSize: 14,
    },

    textarea: {
      width: "100%",
      boxSizing: "border-box",
      minHeight: 100,
      resize: "vertical",
      border:
        "1px solid #cbd5e1",
      borderRadius: 9,
      padding: "9px 10px",
      background: "#ffffff",
      color: "#0f172a",
      fontSize: 14,
    },

    buttonCreate: {
      minHeight: 42,
      padding: "9px 14px",
      borderRadius: 10,
      border:
        "1px solid #bfdbfe",
      background: "#eff6ff",
      color: "#1d4ed8",
      fontWeight: 800,
      cursor: "pointer",
    },

    buttonPrimary: {
      minHeight: 42,
      padding: "9px 14px",
      borderRadius: 10,
      border: "none",
      background: "#2563eb",
      color: "#fff",
      fontWeight: 900,
      cursor: "pointer",
    },

    buttonNeutral: {
      minHeight: 42,
      padding: "9px 14px",
      borderRadius: 10,
      border:
        "1px solid #cbd5e1",
      background: "#fff",
      color: "#334155",
      fontWeight: 800,
      cursor: "pointer",
    },

    buttonEdit: {
      border:
        "1px solid #fde68a",
      background: "#fffbeb",
      color: "#b45309",
      width: 35,
      height: 35,
      borderRadius: 8,
      cursor: "pointer",
    },

    buttonDelete: {
      border:
        "1px solid #fecaca",
      background: "#fef2f2",
      color: "#dc2626",
      width: 35,
      height: 35,
      borderRadius: 8,
      cursor: "pointer",
    },

    alertError: {
      padding: "11px 13px",
      borderRadius: 10,
      background: "#fef2f2",
      border:
        "1px solid #fecaca",
      color: "#991b1b",
      fontSize: 13,
      fontWeight: 800,
    },

    alertSuccess: {
      padding: "11px 13px",
      borderRadius: 10,
      background: "#f0fdf4",
      border:
        "1px solid #bbf7d0",
      color: "#166534",
      fontSize: 13,
      fontWeight: 800,
    },

    alertEdit: {
      padding: "11px 13px",
      borderRadius: 10,
      background: "#fffbeb",
      border:
        "1px solid #fde68a",
      color: "#92400e",
      fontSize: 13,
      fontWeight: 800,
    },
    
    alertDelete: {
      padding: "11px 13px",
      borderRadius: 10,
      background: "#fef2f2",
      border:
        "1px solid #fecaca",
      color: "#991b1b",
      fontSize: 13,
      fontWeight: 800,
    },

    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border:
        "1px solid #e2e8f0",
      borderRadius: 14,
    },

    table: {
      width: "100%",
      borderCollapse:
        "collapse",
      minWidth: 1040,
    },

    th: {
      padding: "12px 9px",
      background: "#f8fafc",
      borderBottom:
        "1px solid #e2e8f0",
      color: "#475569",
      fontSize: 12,
      fontWeight: 900,
      textAlign: "center",
      whiteSpace: "nowrap",
    },

    td: {
      padding: "11px 9px",
      borderBottom:
        "1px solid #f1f5f9",
      fontSize: 13,
      color: "#0f172a",
      verticalAlign: "middle",
      textAlign: "center",
    },

    modalBackdrop: {
      position: "fixed",
      inset: 0,
      zIndex: 1000,
      display: "grid",
      placeItems: "center",
      padding: 16,
      background:
        "rgba(15,23,42,.45)",
    },

    modal: {
      width: "100%",
      maxWidth: 620,
      maxHeight:
        "calc(100vh - 32px)",
      overflowY: "auto",
      background: "#ffffff",
      borderRadius: 16,
      border:
        "1px solid #e2e8f0",
      padding: 20,
      boxShadow:
        "0 20px 60px rgba(15,23,42,.22)",
      boxSizing: "border-box",
    },
  };

  return (
    <div style={S.page}>
      <div style={S.card}>
        <div
          style={{
            display: "flex",
            justifyContent:
              "space-between",
            gap: 12,
            flexWrap: "wrap",
            alignItems: "center",
          }}
        >
          <div>
            <h1 style={S.title}>
              Incidencias de asistencia
            </h1>
      
            <div style={S.muted}>
              Vacaciones, incapacidades y
              días festivos de los empleados.
            </div>
          </div>
      
          {canCreate ? (
            <button
              type="button"
              onClick={openCreate}
              style={S.buttonCreate}
            >
              <i
                className="fa-solid fa-plus"
                style={{
                  marginRight: 7,
                }}
              />
      
              Nueva incidencia
            </button>
          ) : null}
        </div>
      
        <div style={S.filters}>
          <div style={S.field}>
            <label style={S.label}>
              Desde
            </label>
      
            <input
              type="date"
              value={
                filters.fecha_desde
              }
              onChange={(e) =>
                setFilters(
                  (prev) => ({
                    ...prev,
                    fecha_desde:
                      e.target.value,
                  })
                )
              }
              style={S.input}
            />
          </div>
      
          <div style={S.field}>
            <label style={S.label}>
              Hasta
            </label>
      
            <input
              type="date"
              value={
                filters.fecha_hasta
              }
              onChange={(e) =>
                setFilters(
                  (prev) => ({
                    ...prev,
                    fecha_hasta:
                      e.target.value,
                  })
                )
              }
              style={S.input}
            />
          </div>
      
          <div style={S.field}>
            <label style={S.label}>
              Empleado
            </label>
      
            <select
              value={
                filters.user_id
              }
              onChange={(e) =>
                setFilters(
                  (prev) => ({
                    ...prev,
                    user_id:
                      e.target.value,
                  })
                )
              }
              style={S.input}
            >
              <option value="">
                Todos
              </option>
      
              {usuarios.map(
                (user) => (
                  <option
                    key={user.id}
                    value={user.id}
                  >
                    {user.name}
                  </option>
                )
              )}
            </select>
          </div>
      
          <div style={S.field}>
            <label style={S.label}>
              Tipo
            </label>
      
            <select
              value={
                filters.tipo
              }
              onChange={(e) =>
                setFilters(
                  (prev) => ({
                    ...prev,
                    tipo:
                      e.target.value,
                  })
                )
              }
              style={S.input}
            >
              <option value="">
                Todos
              </option>
      
              {tipos.map(
                (tipo) => (
                  <option
                    key={
                      tipo.value
                    }
                    value={
                      tipo.value
                    }
                  >
                    {tipo.label}
                  </option>
                )
              )}
            </select>
          </div>
      
          <div style={S.field}>
            <label style={S.label}>
              Buscar
            </label>
      
            <input
              type="search"
              placeholder="Nombre o correo"
              value={
                filters.search
              }
              onChange={(e) =>
                setFilters(
                  (prev) => ({
                    ...prev,
                    search:
                      e.target.value,
                  })
                )
              }
              style={S.input}
            />
          </div>
        </div>
      
        <div
          style={{
            display: "flex",
            gap: 8,
            flexWrap: "wrap",
            marginTop: 14,
          }}
        >
          <button
            type="button"
            onClick={consultar}
            disabled={loading}
            style={{
              ...S.buttonPrimary,
              opacity:
                loading ? 0.6 : 1,
            }}
          >
            <i
              className="fa-solid fa-magnifying-glass"
              style={{
                marginRight: 7,
              }}
            />
      
            Consultar
          </button>
      
          <button
            type="button"
            onClick={limpiar}
            disabled={loading}
            style={
              S.buttonNeutral
            }
          >
            Limpiar filtros
          </button>
        </div>
      </div>
      
      {error ? (
        <div style={S.alertError}>
          {error}
        </div>
      ) : null}
      
      {successMessage ? (
        <div
          style={{
            ...(
              successMessage.type ===
              "edit"
                ? S.alertEdit
                : successMessage.type ===
                    "delete"
                  ? S.alertDelete
                  : S.alertSuccess
            ),
      
            display: "flex",
            alignItems: "center",
            gap: 9,
          }}
        >
          <i
            className={
              successMessage.type ===
              "edit"
                ? "fa-solid fa-pen"
                : successMessage.type ===
                    "delete"
                  ? "fa-solid fa-trash"
                  : "fa-solid fa-circle-check"
            }
          />
      
          <span>
            {successMessage.message}
          </span>
        </div>
      ) : null}
      
      <div style={S.card}>
        <div
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent:
              "space-between",
            gap: 10,
            marginBottom: 12,
            flexWrap: "wrap",
          }}
        >
          <div>
            <div
              style={{
                fontSize: 16,
                fontWeight: 900,
              }}
            >
              Incidencias registradas
            </div>
          
            <div style={S.muted}>
              Total encontrado:{" "}
              <b>
                {incidencias.length}
              </b>
            </div>
          </div>

          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: 8,
              flexWrap: "wrap",
            }}
          >
            <label style={{ display: "flex", alignItems: "center", gap: 7, fontSize: 13, fontWeight: 700 }}>
              Formato
              <select
                aria-label="Formato de exportación"
                value={exportFormat}
                onChange={(event) => setExportFormat(event.target.value)}
                disabled={!!exportLoading}
                style={{ ...S.input, width: "auto", minWidth: 175, padding: "9px 10px" }}
              >
                <option value="resumen">Resumen agrupado</option>
                <option value="detalle">Detalle por empleado</option>
              </select>
            </label>

            <button
              type="button"
              onClick={() =>
                exportIncidencias(
                  "excel"
                )
              }
              disabled={
                !!exportLoading
              }
              style={{
                ...S.buttonNeutral,
                color: "#166534",
                borderColor:
                  "#bbf7d0",
                background:
                  "#f0fdf4",
              }}
            >
              <i
                className="fa-solid fa-file-excel"
                style={{
                  marginRight: 7,
                }}
              />
      
              {exportLoading ===
              "excel"
                ? "Generando..."
                : "Excel"}
            </button>
      
            <button
              type="button"
              onClick={() =>
                exportIncidencias(
                  "pdf"
                )
              }
              disabled={
                !!exportLoading
              }
              style={{
                ...S.buttonNeutral,
                color: "#b91c1c",
                borderColor:
                  "#fecaca",
                background:
                  "#fef2f2",
              }}
            >
              <i
                className="fa-solid fa-file-pdf"
                style={{
                  marginRight: 7,
                }}
              />
      
              {exportLoading ===
              "pdf"
                ? "Generando..."
                : "PDF"}
            </button>
      
            {canViewHistory ? (
              <button
                type="button"
                onClick={
                  openGeneralHistory
                }
                style={
                  S.buttonNeutral
                }
              >
                <i
                  className="fa-solid fa-clock-rotate-left"
                  style={{
                    marginRight: 7,
                  }}
                />
      
                Historial general
              </button>
            ) : null}
      
            <button
              type="button"
              onClick={() =>
                load(
                  appliedFilters
                )
              }
              disabled={loading}
              style={
                S.buttonNeutral
              }
            >
              <i
                className="fa-solid fa-rotate"
                style={{
                  marginRight: 7,
                }}
              />
      
              Actualizar
            </button>
          </div>
        </div>

        <div style={S.tableWrap}>
          <table style={S.table}>
            <thead>
              <tr>
                <th style={S.th}>
                  Empleado
                </th>

                <th style={S.th}>
                  Tipo
                </th>

                <th style={S.th}>
                  Desde
                </th>

                <th style={S.th}>
                  Hasta
                </th>

                <th style={S.th}>
                  Días
                </th>

                <th style={S.th}>
                  Observaciones
                </th>

                <th style={S.th}>
                  Registrado por
                </th>
                
                {canViewHistory ? (
                  <th style={S.th}>
                    Historial
                  </th>
                ) : null}
                
                {canEdit ||
                canDelete ? (
                  <th style={S.th}>
                    Acciones
                  </th>
                ) : null}
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td
                    colSpan={
                      7 +
                      (canViewHistory
                        ? 1
                        : 0) +
                      (canEdit || canDelete
                        ? 1
                        : 0)
                    }
                    style={{
                      ...S.td,
                      textAlign:
                        "center",
                      padding: 26,
                      color:
                        "#64748b",
                      fontWeight:
                        800,
                    }}
                  >
                    Cargando...
                  </td>
                </tr>
              ) : incidencias.length ===
                0 ? (
                <tr>
                  <td
                    colSpan={
                      7 +
                      (canViewHistory
                        ? 1
                        : 0) +
                      (canEdit || canDelete
                        ? 1
                        : 0)
                    }
                    style={{
                      ...S.td,
                      textAlign:
                        "center",
                      padding: 26,
                      color:
                        "#64748b",
                    }}
                  >
                    No hay incidencias
                    registradas.
                  </td>
                </tr>
              ) : (
                incidencias.map(
                  (row) => {
                    const tipoStyle =
                      getTipoStyle(
                        row.tipo
                      );

                    return (
                      <tr
                        key={
                          row.grupo_uuid ||
                          `${row.user_id}-${row.fecha_desde}`
                        }
                      >
                        <td
                          style={S.td}
                        >
                          <b>{row.es_masiva
                            ? (row.alcance === "todos" ? "Todos los empleados" : row.alcance === "unidad" ? "Unidad de servicio" : "Empleados seleccionados")
                            : (row.user?.name || "—")}</b>
                          {row.es_masiva ? <div style={{fontSize: 11, color: "#64748b"}}>{row.empleados} empleados · {row.personalizados} modificados</div> : null}
                          {row.es_masiva ? <button type="button" onClick={() => viewBulk(row)} style={{...S.buttonNeutral, marginTop: 6, fontSize: 11}}>Ver empleados</button> : null}

                          {row.user
                            ?.email ? (
                            <div
                              style={{
                                marginTop:
                                  3,
                                fontSize:
                                  11,
                                color:
                                  "#64748b",
                              }}
                            >
                              {
                                row
                                  .user
                                  .email
                              }
                            </div>
                          ) : null}
                        </td>

                        <td
                          style={S.td}
                        >
                          <span
                            style={{
                              display:
                                "inline-flex",
                              padding:
                                "5px 8px",
                              borderRadius:
                                999,
                              border:
                                `1px solid ${tipoStyle.border}`,
                              background:
                                tipoStyle.background,
                              color:
                                tipoStyle.color,
                              fontSize:
                                12,
                              fontWeight:
                                900,
                            }}
                          >
                            {getTipoLabel(
                              row.tipo
                            )}
                          </span>
                        </td>

                        <td
                          style={S.td}
                        >
                          {formatDate(
                            row.fecha_desde
                          )}
                        </td>

                        <td
                          style={S.td}
                        >
                          {formatDate(
                            row.fecha_hasta
                          )}
                        </td>

                        <td
                          style={S.td}
                        >
                          {row.dias ??
                            "—"}
                        </td>

                        <td
                          style={{
                            ...S.td,
                            maxWidth:
                              280,
                          }}
                        >
                          {row.observaciones ||
                            "—"}
                        </td>

                        <td
                          style={S.td}
                        >
                          {row
                            .registrado_por
                            ?.name ||
                            "—"}
                        </td>
                        
                        {canViewHistory ? (
                          <td style={S.td}>
                            <button
                              type="button"
                              title="Ver historial"
                              onClick={() =>
                                openHistory(row)
                              }
                              style={{
                                minHeight: 32,
                                padding:
                                  "6px 10px",
                                borderRadius: 9,
                                border:
                                  "1px solid #cbd5e1",
                                background:
                                  "#ffffff",
                                color:
                                  "#334155",
                                fontSize: 12,
                                fontWeight: 800,
                                cursor: "pointer",
                                display:
                                  "inline-flex",
                                alignItems:
                                  "center",
                                justifyContent:
                                  "center",
                                gap: 5,
                              }}
                            >
                              <i className="fa-solid fa-clock-rotate-left" />
                        
                              Historial
                            </button>
                          </td>
                        ) : null}
                        
                        {canEdit ||
                        canDelete ? (
                          <td
                            style={S.td}
                          >
                            <div
                              style={{
                                display:
                                  "flex",
                                gap: 7,
                                justifyContent:
                                  "center",
                                alignItems:
                                  "center",
                              }}
                            >
                              {canEdit ? (
                                <button
                                  type="button"
                                  title="Editar"
                                  onClick={() =>
                                    openEdit(
                                      row
                                    )
                                  }
                                  style={
                                    S.buttonEdit
                                  }
                                >
                                  <i className="fa-solid fa-pen" />
                                </button>
                              ) : null}

                              {canDelete ? (
                                <button
                                  type="button"
                                  title="Eliminar"
                                  onClick={() =>
                                    deleteIncidencia(
                                      row
                                    )
                                  }
                                  style={
                                    S.buttonDelete
                                  }
                                >
                                  <i className="fa-solid fa-trash" />
                                </button>
                              ) : null}
                            </div>
                          </td>
                        ) : null}
                      </tr>
                    );
                  }
                )
              )}
            </tbody>
          </table>
        </div>
      </div>

      {modalOpen ? (
        <div
          style={
            S.modalBackdrop
          }
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeModal();
            }
          }}
        >
          <div style={S.modal}>
            <div
              style={{
                display: "flex",
                justifyContent:
                  "space-between",
                alignItems:
                  "flex-start",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 19,
                    fontWeight:
                      900,
                    color:
                      "#0f172a",
                  }}
                >
                  {editingGrupoUuid
                    ? "Editar incidencia"
                    : "Nueva incidencia"}
                </div>

                <div
                  style={
                    S.muted
                  }
                >
                  Registra una fecha
                  individual o un rango
                  completo.
                </div>
              </div>

              <button
                type="button"
                onClick={
                  closeModal
                }
                disabled={saving}
                style={{
                  width: 36,
                  height: 36,
                  borderRadius:
                    999,
                  border:
                    "1px solid #e2e8f0",
                  background:
                    "#ffffff",
                  cursor:
                    "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>

            <form
              onSubmit={
                saveIncidencia
              }
            >
              <div
                style={{
                  ...S.filters,
                  marginTop: 20,
                }}
              >
                {!editingGrupoUuid ? (
                  <div style={{...S.field, gridColumn: "1 / -1"}}>
                    <label style={S.label}>Aplicar incidencia a *</label>
                    <select style={S.input} value={form.alcance} onChange={e => setField("alcance", e.target.value)} disabled={saving}>
                      <option value="individual">Un empleado</option>
                      <option value="todos">Todos los empleados activos</option>
                      <option value="unidad">Empleados activos de una unidad</option>
                      <option value="seleccion">Empleados específicos</option>
                    </select>
                  </div>
                ) : null}
                {bulkDetail ? (
                  <div style={{...S.field, gridColumn: "1 / -1"}}>
                    <label style={S.label}>Modificar *</label>
                    <select style={S.input} value={bulkMode} onChange={e => setBulkMode(e.target.value)}>
                      <option value="seleccion">Sólo empleados seleccionados</option>
                      <option value="originales">Sólo empleados que no han sido modificados</option>
                      <option value="todos">Todos los empleados del grupo</option>
                    </select>
                    <p style={{fontSize: 12, color: "#64748b"}}>Los cambios en empleados seleccionados no alteran al resto.</p>
                  </div>
                ) : null}
                {bulkDetail ? bulkMemberPicker() : null}
                {(!editingGrupoUuid && form.alcance === "unidad") ? (
                  <div style={{...S.field, gridColumn: "1 / -1"}}>
                    <label style={S.label}>Unidad de servicio *</label>
                    <select style={S.input} value={form.unidad_id} onChange={e => setField("unidad_id", e.target.value)}>
                      <option value="">Selecciona una unidad</option>
                      {unidades.map(u => <option key={u.id} value={u.id}>{u.nombre}</option>)}
                    </select>
                  </div>
                ) : null}
                {(!editingGrupoUuid && form.alcance === "seleccion") ? (
                  <div style={{...S.field, gridColumn: "1 / -1"}}>
                    <label style={S.label}>Empleados específicos ({form.user_ids.length} seleccionados) *</label>
                    <div style={{maxHeight: 175, overflowY: "auto", border: "1px solid #cbd5e1", borderRadius: 9, padding: 8}}>
                      {usuarios.map(u => <label key={u.id} style={{display: "flex", gap: 7, padding: 4, fontSize: 13}}>
                        <input type="checkbox" checked={form.user_ids.includes(String(u.id))} onChange={e => setField("user_ids", e.target.checked ? [...form.user_ids, String(u.id)] : form.user_ids.filter(n => n !== String(u.id)))} />
                        {u.name} {u.email ? ` - ${u.email}` : ""}
                      </label>)}
                    </div>
                  </div>
                ) : null}
                {(!editingGrupoUuid ? form.alcance === "individual" : !bulkDetail) ? (
                <div
                  style={{
                    ...S.field,
                    gridColumn:
                      "1 / -1",
                  }}
                >
                  <label
                    style={
                      S.label
                    }
                  >
                    Empleado *
                  </label>

                  <select
                    value={
                      form.user_id
                    }
                    onChange={(e) =>
                      setField(
                        "user_id",
                        e.target
                          .value
                      )
                    }
                    disabled={
                      saving
                    }
                    style={
                      S.input
                    }
                  >
                    <option value="">
                      Selecciona un
                      empleado
                    </option>

                    {usuarios.map(
                      (user) => (
                        <option
                          key={
                            user.id
                          }
                          value={
                            user.id
                          }
                        >
                          {user.name}
                          {user.email
                            ? ` - ${user.email}`
                            : ""}
                        </option>
                      )
                    )}
                  </select>
                </div>

                ) : null}

                <div
                  style={{
                    ...S.field,
                    gridColumn:
                      "1 / -1",
                  }}
                >
                  <label
                    style={
                      S.label
                    }
                  >
                    Tipo *
                  </label>

                  <select
                    value={
                      form.tipo
                    }
                    onChange={(e) =>
                      setField(
                        "tipo",
                        e.target
                          .value
                      )
                    }
                    disabled={
                      saving
                    }
                    style={
                      S.input
                    }
                  >
                    <option value="">
                      Selecciona el
                      motivo
                    </option>

                    {tipos.map(
                      (tipo) => (
                        <option
                          key={
                            tipo.value
                          }
                          value={
                            tipo.value
                          }
                        >
                          {
                            tipo.label
                          }
                        </option>
                      )
                    )}
                  </select>
                </div>

                <div style={S.field}>
                  <label
                    style={
                      S.label
                    }
                  >
                    Desde *
                  </label>

                  <input
                    type="date"
                    value={
                      form.fecha_desde
                    }
                    onChange={(e) =>
                      setField(
                        "fecha_desde",
                        e.target
                          .value
                      )
                    }
                    disabled={
                      saving
                    }
                    style={
                      S.input
                    }
                  />
                </div>

                <div style={S.field}>
                  <label
                    style={
                      S.label
                    }
                  >
                    Hasta *
                  </label>

                  <input
                    type="date"
                    value={
                      form.fecha_hasta
                    }
                    min={
                      form.fecha_desde ||
                      undefined
                    }
                    onChange={(e) =>
                      setField(
                        "fecha_hasta",
                        e.target
                          .value
                      )
                    }
                    disabled={
                      saving
                    }
                    style={
                      S.input
                    }
                  />
                </div>

                <div
                  style={{
                    ...S.field,
                    gridColumn:
                      "1 / -1",
                  }}
                >
                  <label
                    style={
                      S.label
                    }
                  >
                    Observaciones
                  </label>

                  <textarea
                    value={
                      form.observaciones
                    }
                    onChange={(e) =>
                      setField(
                        "observaciones",
                        e.target
                          .value
                      )
                    }
                    disabled={
                      saving
                    }
                    maxLength={
                      2000
                    }
                    placeholder="Comentario opcional"
                    style={
                      S.textarea
                    }
                  />
                </div>
              </div>

              {modalError ? (
                <div
                  style={{
                    ...S.alertError,
                    marginTop: 14,
                  }}
                >
                  {modalError}
                </div>
              ) : null}

              <div
                style={{
                  display: "flex",
                  justifyContent:
                    "flex-end",
                  gap: 8,
                  flexWrap:
                    "wrap",
                  marginTop: 18,
                }}
              >
                <button
                  type="button"
                  onClick={
                    closeModal
                  }
                  disabled={
                    saving
                  }
                  style={
                    S.buttonNeutral
                  }
                >
                  Cancelar
                </button>

                <button
                  type="submit"
                  disabled={
                    saving
                  }
                  style={{
                    ...S.buttonPrimary,
                    opacity:
                      saving
                        ? 0.6
                        : 1,
                  }}
                >
                  <i
                    className="fa-solid fa-floppy-disk"
                    style={{
                      marginRight:
                        7,
                    }}
                  />

                  {saving
                    ? "Guardando..."
                    : editingGrupoUuid
                      ? "Guardar cambios"
                      : "Registrar incidencia"}
                </button>
              </div>
            </form>
          </div>
        </div>
      ) : null}

      {bulkDetail && !modalOpen ? (
        <div style={S.modalBackdrop}>
          <div style={{...S.modal, maxWidth: 760}}>
            <div style={{display: "flex", justifyContent: "space-between", alignItems: "center"}}>
              <div><strong>Detalle de incidencia masiva</strong><div style={S.muted}>{bulkDetail.empleados?.length || 0} empleados incluidos · {formatDate(bulkDetail.fecha_desde)} al {formatDate(bulkDetail.fecha_hasta)}</div></div>
              <button style={S.buttonNeutral} onClick={() => {setBulkDetail(null);setBulkSelected([]);}}>Cerrar</button>
            </div>
            <div style={{marginTop: 15}}>{bulkMemberPicker()}</div>
            <div style={{display: "flex", gap: 8, justifyContent: "flex-end", marginTop: 14, flexWrap: "wrap"}}>
              {canDelete ? <button type="button" style={{...S.buttonDelete, width: "auto", height: "auto", minHeight: 42, padding: "9px 14px", fontWeight: 800, whiteSpace: "nowrap", flexShrink: 0, opacity: (!bulkSelected.length || bulkLoading) ? 0.55 : 1}} disabled={!bulkSelected.length || bulkLoading} onClick={removeBulkSelected}>Retirar seleccionados</button> : null}
              {canEdit ? <button type="button" style={S.buttonPrimary} onClick={() => {const row = {grupo_uuid: bulkDetail.grupo_uuid};setBulkDetail(null);openEdit(row);}}>Editar empleados</button> : null}
            </div>
          </div>
        </div>
      ) : null}

      {generalHistoryOpen ? (
        <div
          style={
            S.modalBackdrop
          }
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeGeneralHistory();
            }
          }}
        >
          <div
            style={{
              ...S.modal,
              maxWidth: 1050,
            }}
          >
            <div
              style={{
                display: "flex",
                justifyContent:
                  "space-between",
                alignItems:
                  "flex-start",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 19,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Historial general de incidencias
                </div>
      
                <div
                  style={S.muted}
                >
                  Consulta las creaciones,
                  modificaciones y eliminaciones
                  registradas.
                </div>
              </div>
      
              <button
                type="button"
                onClick={
                  closeGeneralHistory
                }
                disabled={
                  generalHistoryLoading
                }
                style={{
                  width: 36,
                  height: 36,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background:
                    "#ffffff",
                  cursor:
                    generalHistoryLoading
                      ? "default"
                      : "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            <div
              style={{
                marginTop: 16,
                padding:
                  "10px 12px",
                border:
                  "1px solid #e2e8f0",
                borderRadius: 10,
                background:
                  "#f8fafc",
                fontSize: 13,
                color:
                  "#475569",
                fontWeight: 700,
              }}
            >
              Total de incidencias con historial:{" "}
              <b>
                {generalHistoryPagination
                  .total}
              </b>
            </div>
      
            {generalHistoryError ? (
              <div
                style={{
                  ...S.alertError,
                  marginTop: 14,
                }}
              >
                {generalHistoryError}
              </div>
            ) : null}
      
            {generalHistoryLoading ? (
              <div
                style={{
                  padding: 30,
                  textAlign:
                    "center",
                  color: "#64748b",
                  fontWeight: 800,
                }}
              >
                Cargando historial...
              </div>
            ) : generalHistoryRows.length ===
              0 ? (
              <div
                style={{
                  padding: 30,
                  textAlign:
                    "center",
                  color: "#64748b",
                }}
              >
                No hay movimientos
                registrados.
              </div>
            ) : (
              <div
                style={{
                  width: "100%",
                  overflowX: "auto",
                  marginTop: 16,
                }}
              >
                <table
                  style={{
                    width: "100%",
                    borderCollapse:
                      "collapse",
                    minWidth: 900,
                  }}
                >
                  <thead>
                    <tr>
                      <th style={S.th}>
                        Empleado / Alcance
                      </th>
      
                      <th style={S.th}>
                        Tipo
                      </th>
      
                      <th style={S.th}>
                        Periodo
                      </th>
      
                      <th style={S.th}>
                        Estado
                      </th>
      
                      <th style={S.th}>
                        Movimientos
                      </th>
      
                      <th style={S.th}>
                        Último movimiento
                      </th>
      
                      <th style={S.th}>
                        Historial
                      </th>
                    </tr>
                  </thead>
      
                  <tbody>
                    {generalHistoryRows.map(
                      (row) => {
                        const tipoStyle =
                          getTipoStyle(
                            row.tipo
                          );
      
                        return (
                          <tr
                            key={
                              row.grupo_uuid
                            }
                          >
                            <td
                              style={S.td}
                            >
                              <b>
                                {row.es_masiva
                                  ? (row.alcance === "unidad" ? "Incidencia por unidad" : row.alcance === "seleccion" ? "Grupo de empleados" : "Todos los empleados")
                                  : (row?.empleado?.name || "—")}
                              </b>
                              {row.es_masiva && (
                                <div style={{marginTop: 4, fontSize: 11, color: "#64748b"}}>
                                  {row.empleados_originales ?? "—"} empleados originalmente
                                  {row.deleted ? " · Grupo eliminado" : ` · ${row.empleados_actuales ?? "—"} incluidos actualmente`}
                                </div>
                              )}
      
                              {!row.es_masiva && row
                                ?.empleado
                                ?.email ? (
                                <div
                                  style={{
                                    marginTop:
                                      3,
                                    fontSize:
                                      11,
                                    color:
                                      "#64748b",
                                  }}
                                >
                                  {
                                    row
                                      .empleado
                                      .email
                                  }
                                </div>
                              ) : null}
                            </td>
      
                            <td
                              style={S.td}
                            >
                              <span
                                style={{
                                  display:
                                    "inline-flex",
                                  padding:
                                    "5px 8px",
                                  borderRadius:
                                    999,
                                  border:
                                    `1px solid ${tipoStyle.border}`,
                                  background:
                                    tipoStyle.background,
                                  color:
                                    tipoStyle.color,
                                  fontSize:
                                    12,
                                  fontWeight:
                                    900,
                                }}
                              >
                                {getTipoLabel(
                                  row.tipo
                                )}
                              </span>
                            </td>
      
                            <td
                              style={S.td}
                            >
                              <div>
                                {formatDate(
                                  row.fecha_desde
                                )}
                              </div>
      
                              <div
                                style={{
                                  marginTop:
                                    3,
                                  fontSize:
                                    11,
                                  color:
                                    "#64748b",
                                }}
                              >
                                hasta{" "}
                                {formatDate(
                                  row.fecha_hasta
                                )}
                              </div>
                            </td>
      
                            <td
                              style={S.td}
                            >
                              {row.deleted ? (
                                <span
                                  style={{
                                    display:
                                      "inline-flex",
                                    padding:
                                      "5px 8px",
                                    borderRadius:
                                      999,
                                    border:
                                      "1px solid #fecaca",
                                    background:
                                      "#fef2f2",
                                    color:
                                      "#dc2626",
                                    fontSize:
                                      12,
                                    fontWeight:
                                      900,
                                  }}
                                >
                                  Eliminada
                                </span>
                              ) : (
                                <span
                                  style={{
                                    display:
                                      "inline-flex",
                                    padding:
                                      "5px 8px",
                                    borderRadius:
                                      999,
                                    border:
                                      "1px solid #bbf7d0",
                                    background:
                                      "#f0fdf4",
                                    color:
                                      "#15803d",
                                    fontSize:
                                      12,
                                    fontWeight:
                                      900,
                                  }}
                                >
                                  Activa
                                </span>
                              )}
                            </td>
      
                            <td
                              style={{
                                ...S.td,
                                textAlign:
                                  "center",
                              }}
                            >
                              {row.movimientos ??
                                0}
                            </td>
      
                            <td
                              style={S.td}
                            >
                              {formatHistoryDateTime(
                                row.last_action_at
                              )}
                            </td>
      
                            <td
                              style={S.td}
                            >
                              <button
                                type="button"
                                title="Ver historial"
                                onClick={() =>
                                  openHistoryFromGeneral(
                                    row
                                  )
                                }
                                style={{
                                  minHeight:
                                    32,
                                  padding:
                                    "6px 10px",
                                  borderRadius:
                                    9,
                                  border:
                                    "1px solid #cbd5e1",
                                  background:
                                    "#ffffff",
                                  color:
                                    "#334155",
                                  fontSize:
                                    12,
                                  fontWeight:
                                    800,
                                  cursor:
                                    "pointer",
                                  display:
                                    "inline-flex",
                                  alignItems:
                                    "center",
                                  justifyContent:
                                    "center",
                                  gap: 5,
                                }}
                              >
                                <i className="fa-solid fa-clock-rotate-left" />
      
                                Ver historial
                              </button>
                            </td>
                          </tr>
                        );
                      }
                    )}
                  </tbody>
                </table>
              </div>
            )}
      
            <div
              style={{
                display: "flex",
                alignItems:
                  "center",
                justifyContent:
                  "space-between",
                gap: 10,
                flexWrap: "wrap",
                marginTop: 18,
              }}
            >
              <div
                style={{
                  fontSize: 13,
                  color: "#64748b",
                }}
              >
                Página{" "}
                <b>
                  {generalHistoryPagination
                    .current_page}
                </b>{" "}
                de{" "}
                <b>
                  {generalHistoryPagination
                    .last_page}
                </b>
              </div>
      
              <div
                style={{
                  display: "flex",
                  gap: 8,
                  flexWrap: "wrap",
                }}
              >
                <button
                  type="button"
                  disabled={
                    generalHistoryLoading ||
                    generalHistoryPage <=
                      1
                  }
                  onClick={() =>
                    loadGeneralHistory(
                      Math.max(
                        1,
                        generalHistoryPage -
                          1
                      )
                    )
                  }
                  style={{
                    ...S.buttonNeutral,
                    opacity:
                      generalHistoryLoading ||
                      generalHistoryPage <=
                        1
                        ? 0.5
                        : 1,
                  }}
                >
                  Anterior
                </button>
      
                <button
                  type="button"
                  disabled={
                    generalHistoryLoading ||
                    generalHistoryPage >=
                      generalHistoryPagination
                        .last_page
                  }
                  onClick={() =>
                    loadGeneralHistory(
                      Math.min(
                        generalHistoryPagination
                          .last_page,
                        generalHistoryPage +
                          1
                      )
                    )
                  }
                  style={{
                    ...S.buttonNeutral,
                    opacity:
                      generalHistoryLoading ||
                      generalHistoryPage >=
                        generalHistoryPagination
                          .last_page
                        ? 0.5
                        : 1,
                  }}
                >
                  Siguiente
                </button>
      
                <button
                  type="button"
                  onClick={
                    closeGeneralHistory
                  }
                  disabled={
                    generalHistoryLoading
                  }
                  style={
                    S.buttonNeutral
                  }
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      ) : null}

      {historyItem ? (
        <div
          style={
            S.modalBackdrop
          }
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeHistory();
            }
          }}
        >
          <div
            style={{
              ...S.modal,
              maxWidth: 760,
            }}
          >
            <div
              style={{
                display: "flex",
                justifyContent:
                  "space-between",
                alignItems:
                  "flex-start",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 19,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Historial de incidencia
                </div>
      
                <div
                  style={S.muted}
                >
                  {historyItem?.es_masiva
                    ? `Incidencia masiva`
                    : (historyItem?.empleado?.name || "Empleado")}
                  {!historyItem?.es_masiva && historyItem?.empleado?.email
                    ? ` · ${historyItem.empleado.email}`
                    : ""}
                  {historyItem?.es_masiva && historyItem.empleados_originales != null
                    ? ` · ${historyItem.empleados_originales} originalmente`
                    : ""}
                </div>
              </div>
      
              <button
                type="button"
                onClick={
                  closeHistory
                }
                disabled={
                  historyLoading
                }
                style={{
                  width: 36,
                  height: 36,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background:
                    "#ffffff",
                  cursor:
                    historyLoading
                      ? "default"
                      : "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            <div
              style={{
                display: "grid",
                gridTemplateColumns:
                  "repeat(auto-fit, minmax(160px, 1fr))",
                gap: 10,
                marginTop: 18,
              }}
            >
              <div
                style={{
                  padding: 11,
                  border:
                    "1px solid #e2e8f0",
                  borderRadius: 10,
                }}
              >
                <div
                  style={S.label}
                >
                  Tipo
                </div>
      
                <div
                  style={{
                    marginTop: 5,
                    fontWeight: 800,
                  }}
                >
                  {getTipoLabel(
                    historyItem.tipo
                  )}
                </div>
              </div>
      
              <div
                style={{
                  padding: 11,
                  border:
                    "1px solid #e2e8f0",
                  borderRadius: 10,
                }}
              >
                <div
                  style={S.label}
                >
                  Periodo
                </div>
      
                <div
                  style={{
                    marginTop: 5,
                    fontWeight: 800,
                  }}
                >
                  {formatDate(
                    historyItem.fecha_desde
                  )}
      
                  {" al "}
      
                  {formatDate(
                    historyItem.fecha_hasta
                  )}
                </div>
              </div>
      
              <div
                style={{
                  padding: 11,
                  border:
                    "1px solid #e2e8f0",
                  borderRadius: 10,
                }}
              >
                <div
                  style={S.label}
                >
                  Días
                </div>
      
                <div
                  style={{
                    marginTop: 5,
                    fontWeight: 800,
                  }}
                >
                  {historyItem.dias ??
                    "—"}
                </div>
              </div>
            </div>
      
            {historyItem.deleted ? (
              <div
                style={{
                  marginTop: 12,
                  padding:
                    "9px 11px",
                  borderRadius: 9,
                  border:
                    "1px solid #fecaca",
                  background:
                    "#fef2f2",
                  color:
                    "#b91c1c",
                  fontWeight: 800,
                  fontSize: 13,
                }}
              >
                <i
                  className="fa-solid fa-trash"
                  style={{
                    marginRight: 7,
                  }}
                />
      
                Esta incidencia fue eliminada.
              </div>
            ) : null}
      
            {historyItem?.es_masiva && (
              <div style={{marginTop: 13, padding: 12, border: "1px solid #dbeafe", background: "#eff6ff", borderRadius: 10}}>
                <div style={{fontWeight: 900, fontSize: 13, color: "#1e3a8a"}}>Empleados del grupo</div>
                <div style={{fontSize: 12, color: "#475569", marginTop: 5}}>
                  {historyItem.empleados_originales ?? "—"} originalmente
                  {historyItem.empleados_actuales !== undefined && historyItem.empleados_actuales !== null
                    ? ` · ${historyItem.empleados_actuales} actualmente`
                    : ""}
                </div>
                {Array.isArray(historyItem.miembros_originales) && historyItem.miembros_originales.length > 0 ? (
                  <div style={{marginTop: 8, maxHeight: 170, overflowY: "auto", background: "white", borderRadius: 7, padding: 8}}>
                    {historyItem.miembros_originales.map((miembro, i) => (
                      <div key={miembro.id ?? i} style={{padding: "5px 2px", borderBottom: i === historyItem.miembros_originales.length - 1 ? "none" : "1px solid #f1f5f9", fontSize: 12}}>
                        <b>{miembro.name || `Empleado #${miembro.id}`}</b>
                        {miembro.email ? <span style={{color: "#64748b"}}> · {miembro.email}</span> : null}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div style={{marginTop: 8, fontSize: 12, color: "#64748b"}}>
                    Este grupo se registró antes de almacenar la lista completa de integrantes en el historial. Se conservan los movimientos y cantidades disponibles.
                  </div>
                )}
              </div>
            )}

            {historyError ? (
              <div
                style={{
                  ...S.alertError,
                  marginTop: 14,
                }}
              >
                {historyError}
              </div>
            ) : null}
      
            <div
              style={{
                marginTop: 18,
                fontSize: 15,
                fontWeight: 900,
                color: "#0f172a",
              }}
            >
              Movimientos
            </div>
      
            {historyLoading ? (
              <div
                style={{
                  padding: 24,
                  textAlign:
                    "center",
                  color: "#64748b",
                  fontWeight: 800,
                }}
              >
                Cargando historial...
              </div>
            ) : historyItems.length ===
              0 ? (
              <div
                style={{
                  padding: 24,
                  textAlign:
                    "center",
                  color: "#64748b",
                }}
              >
                No hay movimientos
                registrados.
              </div>
            ) : (
              <div
                style={{
                  display: "flex",
                  flexDirection:
                    "column",
                  gap: 10,
                  marginTop: 12,
                }}
              >
                {historyItems.map(
                  (item) => {
                    const detalles = Array.isArray(item?.changes?.detalle_empleados)
                      ? item.changes.detalle_empleados : [];
                    const changes = Object.entries(item?.changes || {})
                      .filter(([field]) => field !== "detalle_empleados" && field !== "user_ids");
      
                    return (
                      <div
                        key={item.id}
                        style={{
                          padding: 13,
                          border:
                            "1px solid #e2e8f0",
                          borderRadius:
                            12,
                          background:
                            "#f8fafc",
                        }}
                      >
                        <div
                          style={{
                            display:
                              "flex",
                            justifyContent:
                              "space-between",
                            gap: 10,
                            flexWrap:
                              "wrap",
                          }}
                        >
                          <div
                            style={{
                              fontWeight:
                                900,
                              color:
                                "#0f172a",
                            }}
                          >
                            {getHistoryActionLabel(
                              item.action
                            )}
                          </div>
      
                          <div
                            style={{
                              fontSize:
                                12,
                              color:
                                "#64748b",
                            }}
                          >
                            {formatHistoryDateTime(
                              item.created_at
                            )}
                          </div>
                        </div>
      
                        <div
                          style={{
                            marginTop: 5,
                            fontSize: 12,
                            color:
                              "#64748b",
                          }}
                        >
                          Realizado por:{" "}
                          <b>
                            {item?.actor
                              ?.name ||
                              "Usuario no disponible"}
                          </b>
                        </div>
      
                        {detalles.length > 0 && (
                          <div style={{ marginTop: 12, padding: 11, background: "#eef6ff", border: "1px solid #bfdbfe", borderRadius: 9 }}>
                            <div style={{ fontWeight: 800, marginBottom: 8 }}>Detalle por empleado ({detalles.length})</div>
                            {detalles.map((detalle, indice) => (
                              <div key={`${detalle.user_id}-${indice}`} style={{ padding: "9px 0", borderTop: indice ? "1px solid #cbd5e1" : "none", fontSize: 12 }}>
                                <b>{detalle.empleado}</b>
                                <div>Antes: {getTipoLabel(detalle.antes?.tipo)} · {formatDate(detalle.antes?.fecha_desde)} al {formatDate(detalle.antes?.fecha_hasta)}</div>
                                <div>Después: {getTipoLabel(detalle.despues?.tipo)} · {formatDate(detalle.despues?.fecha_desde)} al {formatDate(detalle.despues?.fecha_hasta)}</div>
                                {detalle.antes?.observaciones !== detalle.despues?.observaciones && (
                                  <div>Observaciones: {detalle.antes?.observaciones || "—"} → {detalle.despues?.observaciones || "—"}</div>
                                )}
                              </div>
                            ))}
                          </div>
                        )}
                        {changes.length >
                        0 ? (
                          <div
                            style={{
                              marginTop:
                                12,
                              display:
                                "flex",
                              flexDirection:
                                "column",
                              gap: 8,
                            }}
                          >
                            {changes.map(
                              ([
                                field,
                                values,
                              ]) => (
                                <div
                                  key={
                                    field
                                  }
                                  style={{
                                    padding:
                                      10,
                                    border:
                                      "1px solid #e2e8f0",
                                    borderRadius:
                                      9,
                                    background:
                                      "#ffffff",
                                  }}
                                >
                                  <div
                                    style={{
                                      fontSize:
                                        12,
                                      fontWeight:
                                        900,
                                      color:
                                        "#334155",
                                    }}
                                  >
                                    {getHistoryFieldLabel(
                                      field
                                    )}
                                  </div>
      
                                  {values && typeof values === "object" && !Array.isArray(values) && ("before" in values || "after" in values) ? <>
                                  <div
                                    style={{
                                      marginTop:
                                        6,
                                      fontSize:
                                        12,
                                    }}
                                  >
                                    <b>
                                      Antes:
                                    </b>{" "}
                                    {formatHistoryValue(
                                      field,
                                      values
                                        ?.before
                                    )}
                                  </div>
      
                                  <div
                                    style={{
                                      marginTop:
                                        4,
                                      fontSize:
                                        12,
                                    }}
                                  >
                                    <b>
                                      Después:
                                    </b>{" "}
                                    {formatHistoryValue(
                                      field,
                                      values
                                        ?.after
                                    )}
                                  </div>
                                  </> : <div style={{ marginTop: 6, fontSize: 12 }}>{formatHistoryValue(field, values)}</div>}
                                </div>
                              )
                            )}
                          </div>
                        ) : (
                          <div
                            style={{
                              marginTop:
                                10,
                              fontSize:
                                12,
                              color:
                                "#64748b",
                            }}
                          >
                            {item.action ===
                            "created"
                              ? "Se creó la incidencia."
                              : item.action ===
                                  "deleted"
                                ? "Se eliminó la incidencia."
                                : "Sin cambios detallados."}
                          </div>
                        )}
                      </div>
                    );
                  }
                )}
              </div>
            )}
      
            <div
              style={{
                display: "flex",
                justifyContent:
                  "flex-end",
                marginTop: 18,
              }}
            >
              <button
                type="button"
                onClick={
                  closeHistory
                }
                disabled={
                  historyLoading
                }
                style={
                  S.buttonNeutral
                }
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      ) : null}

    </div>
  );
}