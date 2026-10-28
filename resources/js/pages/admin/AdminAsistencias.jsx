import React, { useEffect, useState } from "react";
import {
  apiGet,
  apiPut,
  apiDelete,
} from "../../services/api";

const TIME_ZONE = "America/Mexico_City";

const CURRENT_USER_UPDATED_EVENT =
  "current-user-updated";

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
  if (!user) {
    return [];
  }

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
  if (!user) {
    return [];
  }

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

function formatDate(value) {
  if (!value) return "—";
  const raw = String(value).slice(0, 10);
  const parts = raw.split("-");
  if (parts.length !== 3) return raw;
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

function formatTime(value) {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    const raw = String(value);
    return raw.length >= 19 ? raw.slice(11, 19) : raw;
  }

  return new Intl.DateTimeFormat("es-MX", {
    timeZone: TIME_ZONE,
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  }).format(date);
}

function formatSeconds(value) {
  if (value === null || value === undefined || value === "") {
    return "—";
  }

  const total = Number(value);
  if (!Number.isFinite(total)) return "—";

  const safe = Math.max(0, Math.floor(total));
  const hours = Math.floor(safe / 3600);
  const minutes = Math.floor((safe % 3600) / 60);
  const seconds = safe % 60;

  return [
    String(hours).padStart(2, "0"),
    String(minutes).padStart(2, "0"),
    String(seconds).padStart(2, "0"),
  ].join(":");
}


function formatDateTime(value) {
  if (!value) return "—";

  const date = new Date(value);

  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return String(value);
  }

  return new Intl.DateTimeFormat(
    "es-MX",
    {
      timeZone: TIME_ZONE,
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    }
  ).format(date);
}

function formatTipoTrabajo(value) {
  if (value === "mixto_salidas") {
    return "Mixto / Salidas";
  }

  if (value === "fijo") {
    return "Fijo";
  }

  return value || "—";
}

function formatMetodoEntrada(value) {
  if (value === "personal") {
    return "Mi asistencia";
  }

  if (value === "kiosco") {
    return "Kiosco";
  }

  if (value === "manual") {
    return "Manual";
  }

  return value || "—";
}

function formatEstadoDia(value) {
  if (value === "laborable") {
    return "Laborable";
  }

  if (value === "fuera_dia_laborable") {
    return "Fuera de día laborable";
  }

  return "Sin cálculo";
}

function formatEstadoHorario(value) {
  if (value === "dentro_horario") {
    return "Dentro de horario";
  }

  if (value === "fuera_horario") {
    return "Fuera de horario";
  }

  return "Sin cálculo";
}

function formatEstadoComida(value, minutosComida) {
  if (Number(minutosComida || 0) <= 0) {
    return "No aplica";
  }

  const map = {
    menor: "Menor",
    completa: "Completa",
    excedida: "Excedida",
  };

  return map[value] || "Sin cálculo";
}

function getOpenStreetMapEmbedUrl(
  latitude,
  longitude
) {
  if (
    latitude === null ||
    latitude === undefined ||
    latitude === "" ||
    longitude === null ||
    longitude === undefined ||
    longitude === ""
  ) {
    return null;
  }

  const lat = Number(latitude);
  const lng = Number(longitude);

  if (
    !Number.isFinite(lat) ||
    !Number.isFinite(lng)
  ) {
    return null;
  }

  const delta = 0.003;

  const bbox = [
    lng - delta,
    lat - delta,
    lng + delta,
    lat + delta,
  ].join(",");

  return (
    "https://www.openstreetmap.org/export/embed.html" +
    `?bbox=${encodeURIComponent(bbox)}` +
    "&layer=mapnik" +
    `&marker=${encodeURIComponent(
      `${lat},${lng}`
    )}`
  );
}

function EstadoBadge({
  label,
  value,
  tone = "neutral",
}) {
  const tones = {
    success: {
      background: "#ecfdf5",
      border: "#86efac",
      color: "#166534",
    },
    warning: {
      background: "#fffbeb",
      border: "#fde68a",
      color: "#92400e",
    },
    danger: {
      background: "#fef2f2",
      border: "#fecaca",
      color: "#991b1b",
    },
    neutral: {
      background: "#f8fafc",
      border: "#cbd5e1",
      color: "#475569",
    },
  };

  const selected =
    tones[tone] ||
    tones.neutral;

  return (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        padding: "5px 8px",
        borderRadius: 999,
        background: selected.background,
        border: `1px solid ${selected.border}`,
        color: selected.color,
        fontSize: 11,
        fontWeight: 900,
        whiteSpace: "nowrap",
      }}
    >
      {label}: {value}
    </span>
  );
}

function formatEstadoJornada(value) {
  const map = {
    incompleta: "Incompleta",
    cumplida: "Cumplida",
    excedida: "Excedida",
  };

  return map[value] || "—";
}

function JornadaBadge({ value }) {
  const map = {
    cumplida: {
      background: "#ecfdf5",
      border: "#86efac",
      color: "#166534",
    },
    incompleta: {
      background: "#fffbeb",
      border: "#fde68a",
      color: "#92400e",
    },
    excedida: {
      background: "#fffbeb",
      border: "#fde68a",
      color: "#92400e",
    },
  };

  const selected = map[value] || {
    background: "#f8fafc",
    border: "#cbd5e1",
    color: "#475569",
  };

  return (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        padding: "5px 8px",
        borderRadius: 999,
        background: selected.background,
        border: `1px solid ${selected.border}`,
        color: selected.color,
        fontSize: 11,
        fontWeight: 900,
        whiteSpace: "nowrap",
      }}
    >
      {formatEstadoJornada(value)}
    </span>
  );
}

function IncidenciaBadge({
  tipo,
}) {
  const config = {
    vacaciones: {
      label: "Vacaciones",
      background: "#eff6ff",
      border: "#bfdbfe",
      color: "#1d4ed8",
    },

    incapacidad: {
      label: "Incapacidad",
      background: "#fff7ed",
      border: "#fed7aa",
      color: "#c2410c",
    },

    festivo: {
      label: "Día festivo",
      background: "#f0fdf4",
      border: "#bbf7d0",
      color: "#15803d",
    },
  };

  const selected =
    config[tipo] || {
      label: "Incidencia",
      background: "#f8fafc",
      border: "#cbd5e1",
      color: "#475569",
    };

  return (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        padding: "5px 8px",
        borderRadius: 999,
        background:
          selected.background,
        border:
          `1px solid ${selected.border}`,
        color:
          selected.color,
        fontSize: 11,
        fontWeight: 900,
        whiteSpace: "nowrap",
      }}
    >
      {selected.label}
    </span>
  );
}

export default function AdminAsistencias() {

  const [
    me,
    setMe,
  ] = useState(
    () => getStoredUser()
  );
  
  const roles =
    normalizeRoles(me);
  
  const permissions =
    normalizePermissions(me);
  
  const isAdministrator =
    !!me?.is_admin ||
    roles.some(
      (role) =>
        String(role).toLowerCase() ===
        "administrador"
    );
  
  const canEdit =
    isAdministrator ||
    permissions.includes(
      "asistencias.edit"
    );

  const canDelete =
    isAdministrator ||
    permissions.includes(
      "asistencias.delete"
    );

  const canViewHistory =
    isAdministrator;

  const [rows, setRows] = useState([]);
  const [empleados, setEmpleados] = useState([]);
  const [empresas, setEmpresas] = useState([]);
  const [unidades, setUnidades] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [page, setPage] = useState(1);

  const [
    exportLoading,
    setExportLoading,
  ] = useState("");

  const [
    detailItem,
    setDetailItem,
  ] = useState(null);

  const [
    detailLoading,
    setDetailLoading,
  ] = useState(false);

  const [
    detailError,
    setDetailError,
  ] = useState("");

  const [
    mostrarMapaDetalle,
    setMostrarMapaDetalle,
  ] = useState(false);

  const [
    mostrarFirmaDetalle,
    setMostrarFirmaDetalle,
  ] = useState(false);

  const [
    mostrarFotoDetalle,
    setMostrarFotoDetalle,
  ] = useState(false);

  const [
    editItem,
    setEditItem,
  ] = useState(null);
  
  const [
    editForm,
    setEditForm,
  ] = useState({
    fecha: "",
    hora_entrada: "",
    hora_inicio_comida: "",
    hora_fin_comida: "",
    hora_salida: "",
  });
  
  const [
    editLoading,
    setEditLoading,
  ] = useState(false);
  
  const [
    editError,
    setEditError,
  ] = useState("");

  const [
    deleteItem,
    setDeleteItem,
  ] = useState(null);
  
  const [
    deleteLoading,
    setDeleteLoading,
  ] = useState(false);
  
  const [
    deleteError,
    setDeleteError,
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

  const [
    successMessage,
    setSuccessMessage,
  ] = useState(null);

  const [filters, setFilters] = useState({
    fecha_desde: "",
    fecha_hasta: "",
    user_id: "",
    empresa_id: "",
    unidad_servicio_id: "",
    search: "",
    per_page: 25,
  });

  const [
    appliedFilters,
    setAppliedFilters,
  ] = useState({
    fecha_desde: "",
    fecha_hasta: "",
    user_id: "",
    empresa_id: "",
    unidad_servicio_id: "",
    search: "",
    per_page: 25,
  });

  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });

  function showSuccessMessage(
    message,
    type = "edit"
  ) {
    setSuccessMessage({
      message,
      type,
    });
  
    window.setTimeout(() => {
      setSuccessMessage(null);
    }, 3000);
  }

  function setFilter(key, value) {
    setFilters((prev) => ({
      ...prev,
      [key]: value,
    }));
  }

  function buildQuery(currentFilters, requestedPage) {
    const params = new URLSearchParams();

    params.set("page", String(requestedPage));
    params.set("per_page", String(currentFilters.per_page));

    if (currentFilters.fecha_desde) {
      params.set("fecha_desde", currentFilters.fecha_desde);
    }

    if (currentFilters.fecha_hasta) {
      params.set("fecha_hasta", currentFilters.fecha_hasta);
    }

    if (currentFilters.user_id) {
      params.set("user_id", currentFilters.user_id);
    }
    
    if (currentFilters.empresa_id) {
      params.set("empresa_id", currentFilters.empresa_id);
    }
    
    if (currentFilters.unidad_servicio_id) {
      params.set("unidad_servicio_id", currentFilters.unidad_servicio_id);
    }

    if (currentFilters.search.trim()) {
      params.set("search", currentFilters.search.trim());
    }

    return params.toString();
  }

  function buildExportQuery(
    currentFilters
  ) {
    const params =
      new URLSearchParams();
  
    if (currentFilters.fecha_desde) {
      params.set(
        "fecha_desde",
        currentFilters.fecha_desde
      );
    }
  
    if (currentFilters.fecha_hasta) {
      params.set(
        "fecha_hasta",
        currentFilters.fecha_hasta
      );
    }
  
    if (currentFilters.user_id) {
      params.set(
        "user_id",
        currentFilters.user_id
      );
    }
  
    if (currentFilters.empresa_id) {
      params.set(
        "empresa_id",
        currentFilters.empresa_id
      );
    }

    if (
      currentFilters
        .unidad_servicio_id
    ) {
      params.set(
        "unidad_servicio_id",
        currentFilters
          .unidad_servicio_id
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

  async function exportAsistencias(
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
      const query =
        buildExportQuery(
          appliedFilters
        );
  
      const endpoint =
        isExcel
          ? "/admin/asistencias/export/excel"
          : "/admin/asistencias/export/pdf";
  
      const url =
        `/api${endpoint}` +
        (query
          ? `?${query}`
          : "");
  
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
          ? "asistencias.xlsx"
          : "asistencias.pdf";
  
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

  async function load(requestedPage = page, currentFilters = filters) {
    setLoading(true);
    setError("");

    try {
      const query = buildQuery(currentFilters, requestedPage);
      const data = await apiGet(`/admin/asistencias?${query}`);
      setAppliedFilters({
        ...currentFilters,
      });
      const paginated = data?.registros || {};

      setRows(Array.isArray(paginated?.data) ? paginated.data : []);

      setPagination({
        current_page: Number(paginated?.current_page || 1),
        last_page: Number(paginated?.last_page || 1),
        total: Number(paginated?.total || 0),
        per_page: Number(paginated?.per_page || currentFilters.per_page),
      });

      setPage(Number(paginated?.current_page || 1));

      setEmpleados(
        Array.isArray(data?.filtros?.empleados)
          ? data.filtros.empleados
          : []
      );
      
      setEmpresas(
        Array.isArray(data?.filtros?.empresas)
          ? data.filtros.empresas
          : []
      );
      
      setUnidades(
        Array.isArray(data?.filtros?.unidades)
          ? data.filtros.unidades
          : []
      );
    } catch (err) {
      setError(
        err?.message ||
          "No se pudieron cargar las asistencias."
      );
    } finally {
      setLoading(false);
    }
  }

  function consultar() {
    setPage(1);
    load(1, filters);
  }

  function limpiar() {
    const clean = {
      fecha_desde: "",
      fecha_hasta: "",
      user_id: "",
      empresa_id: "",
      unidad_servicio_id: "",
      search: "",
      per_page: filters.per_page,
    };

    setFilters(clean);
    setPage(1);
    load(1, clean);
  }

  async function openDetail(
    row
  ) {
    if (!row?.id) {
      return;
    }

    setDetailLoading(true);
    setDetailError("");
    setMostrarMapaDetalle(false);
    setMostrarFirmaDetalle(false);
    setMostrarFotoDetalle(false);

    try {
      const data =
        await apiGet(
          `/admin/asistencias/${row.id}`
        );

      setDetailItem(
        data?.asistencia || null
      );
    } catch (err) {
      setDetailError(
        err?.message ||
          "No se pudo cargar el detalle de la asistencia."
      );
    } finally {
      setDetailLoading(false);
    }
  }

  function closeDetail() {
    setDetailItem(null);
    setDetailError("");
    setMostrarMapaDetalle(false);
    setMostrarFirmaDetalle(false);
    setMostrarFotoDetalle(false);
  }

  function openEdit(row) {
    if (!row?.id) {
      return;
    }
  
    setEditError("");
  
    setEditItem(row);
  
    setEditForm({
      fecha:
        row.fecha
          ? String(row.fecha).slice(0, 10)
          : "",
  
      hora_entrada:
        row.hora_entrada
          ? formatTime(row.hora_entrada)
          : "",
  
      hora_inicio_comida:
        row.hora_inicio_comida
          ? formatTime(
              row.hora_inicio_comida
            )
          : "",
  
      hora_fin_comida:
        row.hora_fin_comida
          ? formatTime(
              row.hora_fin_comida
            )
          : "",
  
      hora_salida:
        row.hora_salida
          ? formatTime(row.hora_salida)
          : "",
    });
  }

  function closeEdit() {
    if (editLoading) {
      return;
    }
  
    setEditItem(null);
    setEditError("");
  
    setEditForm({
      fecha: "",
      hora_entrada: "",
      hora_inicio_comida: "",
      hora_fin_comida: "",
      hora_salida: "",
    });
  }

  function openDelete(row) {
    if (!row?.id) {
      return;
    }
  
    setDeleteError("");
    setDeleteItem(row);
  }
  
  function closeDelete() {
    if (deleteLoading) {
      return;
    }
  
    setDeleteItem(null);
    setDeleteError("");
  }

  async function openHistory(row) {
    if (!row?.id) {
      return;
    }
  
    setHistoryItem(row);
    setHistoryItems([]);
    setHistoryError("");
    setHistoryLoading(true);
  
    try {
      const data =
        await apiGet(
          `/admin/asistencias/${row.id}/history`
        );
  
      setHistoryItems(
        Array.isArray(data?.history)
          ? data.history
          : []
      );
    } catch (err) {
      setHistoryError(
        err?.message ||
          "No se pudo cargar el historial de la asistencia."
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
    setGeneralHistoryLoading(true);
    setGeneralHistoryError("");
  
    try {
      const data =
        await apiGet(
          `/admin/asistencias/history?page=${requestedPage}&per_page=25`
        );
  
      const registros =
        data?.registros || {};
  
      setGeneralHistoryRows(
        Array.isArray(registros?.data)
          ? registros.data
          : []
      );
  
      setGeneralHistoryPagination({
        current_page: Number(
          registros?.current_page || 1
        ),
        last_page: Number(
          registros?.last_page || 1
        ),
        total: Number(
          registros?.total || 0
        ),
        per_page: Number(
          registros?.per_page || 25
        ),
      });
  
      setGeneralHistoryPage(
        Number(
          registros?.current_page || 1
        )
      );
    } catch (err) {
      setGeneralHistoryError(
        err?.message ||
          "No se pudo cargar el historial general de asistencias."
      );
    } finally {
      setGeneralHistoryLoading(false);
    }
  }
  
  function openGeneralHistory() {
    if (!canViewHistory) {
      return;
    }
  
    setGeneralHistoryOpen(true);
    setGeneralHistoryRows([]);
    setGeneralHistoryError("");
    setGeneralHistoryPage(1);
  
    loadGeneralHistory(1);
  }
  
  function closeGeneralHistory() {
    if (generalHistoryLoading) {
      return;
    }
  
    setGeneralHistoryOpen(false);
    setGeneralHistoryRows([]);
    setGeneralHistoryError("");
    setGeneralHistoryPage(1);
  
    setGeneralHistoryPagination({
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 25,
    });
  }
  
  function openHistoryFromGeneral(row) {
    if (!row?.asistencia_id) {
      return;
    }
  
    /*
     * No hacemos una nueva petición al endpoint
     * individual porque la asistencia podría
     * estar eliminada.
     *
     * El endpoint general ya nos entregó todo
     * su historial.
     */
    setHistoryItem({
      id:
        row.asistencia_id,
  
      empleado_nombre:
        row.empleado_nombre,
  
      empleado_email:
        row.empleado_email,
  
      fecha:
        row.fecha,
    });
  
    setHistoryItems(
      Array.isArray(row.history)
        ? row.history
        : []
    );
  
    setHistoryError("");
    setHistoryLoading(false);
  }
  
  async function confirmDelete() {
    if (
      !deleteItem?.id ||
      deleteLoading
    ) {
      return;
    }
  
    if (!canDelete) {
      setDeleteError(
        "No tienes permiso para eliminar asistencias."
      );
      return;
    }
  
    setDeleteLoading(true);
    setDeleteError("");
  
    try {
      await apiDelete(
      `/admin/asistencias/${deleteItem.id}`
    );
    
    setDeleteItem(null);
    
    showSuccessMessage(
      "Asistencia eliminada correctamente.",
      "delete"
    );
  
      /*
       * Si eliminamos el último registro
       * de una página distinta de la primera,
       * regresar a la página anterior.
       */
      const nextPage =
        rows.length === 1 &&
        page > 1
          ? page - 1
          : page;
  
      await load(
        nextPage,
        filters
      );
    } catch (err) {
      setDeleteError(
        err?.message ||
          "No se pudo eliminar la asistencia."
      );
    } finally {
      setDeleteLoading(false);
    }
  }

  function setEditField(
      key,
      value
    ) {
      setEditForm((prev) => ({
        ...prev,
        [key]: value,
      }));
    }
  
    async function saveEdit() {
    if (
      !editItem?.id ||
      editLoading
    ) {
      return;
    }
  
    if (!editForm.fecha) {
      setEditError(
        "La fecha es obligatoria."
      );
      return;
    }
  
    if (!editForm.hora_entrada) {
      setEditError(
        "La hora de entrada es obligatoria."
      );
      return;
    }
  
    setEditLoading(true);
    setEditError("");
  
    try {
      await apiPut(
        `/admin/asistencias/${editItem.id}`,
        {
          fecha:
            editForm.fecha,
  
          hora_entrada:
            editForm.hora_entrada,
  
          hora_inicio_comida:
            editForm.hora_inicio_comida ||
            null,
  
          hora_fin_comida:
            editForm.hora_fin_comida ||
            null,
  
          hora_salida:
            editForm.hora_salida ||
            null,
        }
      );
  
      setEditItem(null);

      showSuccessMessage(
        "Asistencia actualizada correctamente.",
        "edit"
      );
      
      setEditForm({
        fecha: "",
        hora_entrada: "",
        hora_inicio_comida: "",
        hora_fin_comida: "",
        hora_salida: "",
      });
  
      await load(
        page,
        filters
      );
    } catch (err) {
      setEditError(
        err?.message ||
          "No se pudo actualizar la asistencia."
      );
    } finally {
      setEditLoading(false);
    }
  }

  useEffect(() => {
    function handleCurrentUserUpdated(
      event
    ) {
      const updatedUser =
        event?.detail;
  
      if (!updatedUser?.id) {
        return;
      }
  
      setMe(updatedUser);
    }
  
    window.addEventListener(
      CURRENT_USER_UPDATED_EVENT,
      handleCurrentUserUpdated
    );
  
    return () => {
      window.removeEventListener(
        CURRENT_USER_UPDATED_EVENT,
        handleCurrentUserUpdated
      );
    };
  }, []);

  useEffect(() => {
    load(1, filters);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const detalleMapaUrl =
    detailItem
      ? getOpenStreetMapEmbedUrl(
          detailItem.entrada_latitud,
          detailItem.entrada_longitud
        )
      : null;

  const detalleTieneUbicacion =
    detailItem?.puede_ver_ubicacion ===
      true &&
    detalleMapaUrl !== null;

  function formatHistoryValue(
    field,
    type,
    value
  ) {
    if (
      value === null ||
      value === undefined ||
      value === ""
    ) {
      return "—";
    }
  
    if (type === "date") {
      return formatDate(value);
    }
  
    if (type === "time") {
      return String(value).slice(0, 8);
    }
  
    if (type === "duration") {
      return formatSeconds(value);
    }
  
    if (field === "estado_dia") {
      return formatEstadoDia(value);
    }
  
    if (
      field === "estado_entrada" ||
      field === "estado_salida"
    ) {
      return formatEstadoHorario(value);
    }
  
    if (field === "estado_jornada") {
      return formatEstadoJornada(value);
    }
  
    if (field === "estado_comida") {
      const map = {
        menor: "Menor",
        completa: "Completa",
        excedida: "Excedida",
      };
  
      return map[value] || String(value);
    }
  
    return String(value);
  }

  function getCreationHistoryFields(snapshot) {
    if (!snapshot) {
      return [];
    }
  
    const turnoHorario =
      snapshot.turno_hora_inicio &&
      snapshot.turno_hora_fin
        ? `${String(
            snapshot.turno_hora_inicio
          ).slice(0, 5)} - ${String(
            snapshot.turno_hora_fin
          ).slice(0, 5)}`
        : "—";
  
    return [
      {
        label: "Fecha",
        value: formatDate(
          snapshot.fecha
        ),
      },
      {
        label: "Hora de entrada",
        value:
          snapshot.hora_entrada
            ? String(
                snapshot.hora_entrada
              ).slice(0, 8)
            : "—",
      },
      {
        label: "Empresa laboral",
        value:
          snapshot
            .empresa_nombre ||
          "—",
      },
      {
        label: "Unidad laboral",
        value:
          snapshot
            .unidad_servicio_nombre ||
          "—",
      },
      {
        label: "Tipo de trabajo",
        value: formatTipoTrabajo(
          snapshot.tipo_trabajo
        ),
      },
      {
        label: "Turno",
        value:
          snapshot.turno_nombre ||
          "—",
      },
      {
        label: "Horario del turno",
        value: turnoHorario,
      },
      {
        label: "Jornada requerida",
        value:
          snapshot
            .turno_horas_jornada !==
            null &&
          snapshot
            .turno_horas_jornada !==
            undefined
            ? `${snapshot.turno_horas_jornada} h`
            : "—",
      },
      {
        label: "Tiempo de comida",
        value:
          Number(
            snapshot.turno_minutos_comida || 0
          ) > 0
            ? `${snapshot.turno_minutos_comida} min`
            : "No aplica",
      },
      {
        label: "Método de entrada",
        value: formatMetodoEntrada(
          snapshot.metodo_entrada
        ),
      },
      {
        label: "Estado del día",
        value: formatEstadoDia(
          snapshot.estado_dia
        ),
      },
      {
        label: "Estado de entrada",
        value: formatEstadoHorario(
          snapshot.estado_entrada
        ),
      },
    ];
  }

  function getDeletionHistoryFields(snapshot) {
    if (!snapshot) {
      return [];
    }
  
    return [
      {
        label: "Fecha",
        value: formatDate(
          snapshot.fecha
        ),
      },
      {
        label: "Empresa laboral",
        value:
          snapshot
            .empresa_nombre ||
          "—",
      },
      {
        label: "Unidad laboral",
        value:
          snapshot
            .unidad_servicio_nombre ||
          "—",
      },
      {
        label: "Turno",
        value:
          snapshot.turno_nombre ||
          "—",
      },
      {
        label: "Método de entrada",
        value: formatMetodoEntrada(
          snapshot.metodo_entrada
        ),
      },
      {
        label: "Hora de entrada",
        value:
          snapshot.hora_entrada
            ? String(
                snapshot.hora_entrada
              ).slice(0, 8)
            : "—",
      },
      {
        label: "Inicio de comida",
        value:
          snapshot.hora_inicio_comida
            ? String(
                snapshot.hora_inicio_comida
              ).slice(0, 8)
            : "—",
      },
      {
        label: "Fin de comida",
        value:
          snapshot.hora_fin_comida
            ? String(
                snapshot.hora_fin_comida
              ).slice(0, 8)
            : "—",
      },
      {
        label: "Hora de salida",
        value:
          snapshot.hora_salida
            ? String(
                snapshot.hora_salida
              ).slice(0, 8)
            : "—",
      },
      {
        label: "Presencia",
        value: formatSeconds(
          snapshot.segundos_presencia
        ),
      },
      {
        label: "Tiempo de comida",
        value:
          Number(
            snapshot.turno_minutos_comida ||
              0
          ) <= 0
            ? "No aplica"
            : formatSeconds(
                snapshot.segundos_comida
              ),
      },
      {
        label: "Tiempo trabajado",
        value: formatSeconds(
          snapshot.segundos_trabajados
        ),
      },
      {
        label: "Estado del día",
        value: formatEstadoDia(
          snapshot.estado_dia
        ),
      },
      {
        label: "Estado de entrada",
        value: formatEstadoHorario(
          snapshot.estado_entrada
        ),
      },
      {
        label: "Estado de salida",
        value:
          snapshot.hora_salida
            ? formatEstadoHorario(
                snapshot.estado_salida
              )
            : "Sin salida",
      },
      {
        label: "Estado de comida",
        value: formatEstadoComida(
          snapshot.estado_comida,
          snapshot.turno_minutos_comida
        ),
      },
      {
        label: "Estado de jornada",
        value: formatEstadoJornada(
          snapshot.estado_jornada
        ),
      },
    ];
  }

  const numberedHistoryItems = (() => {
    let editCounter = 0;
  
    return historyItems.map((item) => {
      if (item.action === "updated") {
        editCounter += 1;
  
        return {
          ...item,
          updateNumber: editCounter,
        };
      }
  
      return {
        ...item,
        updateNumber: null,
      };
    });
  })();

  const S = {
    page: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
    },
    card: {
      background: "#fff",
      border: "1px solid #e2e8f0",
      borderRadius: 18,
      padding: 16,
      boxShadow: "0 8px 24px rgba(15,23,42,.05)",
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
      gridTemplateColumns: "repeat(auto-fit, minmax(190px, 1fr))",
      gap: 12,
      marginTop: 16,
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
      border: "1px solid #cbd5e1",
      boxSizing: "border-box",
      background: "#fff",
    },
    actions: {
      display: "flex",
      gap: 8,
      flexWrap: "wrap",
      marginTop: 14,
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
    buttonSecondary: {
      minHeight: 42,
      padding: "9px 14px",
      borderRadius: 10,
      border: "1px solid #cbd5e1",
      background: "#fff",
      color: "#334155",
      fontWeight: 800,
      cursor: "pointer",
    },
    error: {
      padding: "10px 12px",
      borderRadius: 10,
      border: "1px solid #fecaca",
      background: "#fef2f2",
      color: "#991b1b",
      fontWeight: 800,
    },
    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border: "1px solid #e2e8f0",
      borderRadius: 14,
    },
    table: {
      width: "100%",
      minWidth: 1250,
      borderCollapse: "collapse",
    },
    th: {
      padding: "12px 9px",
      background: "#f8fafc",
      borderBottom: "1px solid #e2e8f0",
      color: "#475569",
      fontSize: 12,
      fontWeight: 900,
      textAlign: "center",
      whiteSpace: "nowrap",
    },
    td: {
      padding: "11px 9px",
      borderBottom: "1px solid #f1f5f9",
      color: "#0f172a",
      fontSize: 13,
      textAlign: "center",
      verticalAlign: "middle",
    },
    detailButton: {
      width: 36,
      height: 36,
      borderRadius: 9,
      border:
        "1px solid #bfdbfe",
      background: "#eff6ff",
      color: "#2563eb",
      cursor: "pointer",
    },

    modalBackdrop: {
      position: "fixed",
      inset: 0,
      background:
        "rgba(15,23,42,.45)",
      display: "grid",
      placeItems: "center",
      zIndex: 100,
      padding: 16,
    },

    modal: {
      width: "100%",
      maxWidth: 860,
      maxHeight:
        "calc(100vh - 32px)",
      overflowY: "auto",
      boxSizing: "border-box",
      background: "#fff",
      borderRadius: 16,
      border:
        "1px solid #e2e8f0",
      boxShadow:
        "0 20px 50px rgba(15,23,42,.20)",
      padding: 18,
    },

    detailGrid: {
      display: "grid",
      gridTemplateColumns:
        "repeat(auto-fit, minmax(170px, 1fr))",
      gap: 10,
    },

    detailBox: {
      padding: "11px 12px",
      border:
        "1px solid #e2e8f0",
      borderRadius: 10,
      background: "#f8fafc",
    },

    detailLabel: {
      fontSize: 11,
      color: "#64748b",
      fontWeight: 800,
      marginBottom: 4,
    },

    detailValue: {
      fontSize: 13,
      color: "#0f172a",
      fontWeight: 800,
    },

    signatureImage: {
      display: "block",
      width: "100%",
      maxWidth: 420,
      maxHeight: 180,
      objectFit: "contain",
      background: "#ffffff",
      border:
        "1px solid #e2e8f0",
      borderRadius: 10,
      marginTop: 8,
    },

    photoImage: {
      display: "block",
      width: "100%",
      maxWidth: 420,
      maxHeight: 320,
      objectFit: "contain",
      background: "#0f172a",
      border:
        "1px solid #e2e8f0",
      borderRadius: 10,
      marginTop: 8,
    },

    pagination: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      gap: 10,
      marginTop: 14,
      flexWrap: "wrap",
    },
  };

  return (
    <div style={S.page}>
      <div style={S.card}>
        <h2 style={S.title}>Gestión de asistencias</h2>

        <div style={S.muted}>
          Consulta administrativa de registros de asistencia.
        </div>

        <div style={S.filters}>
          <div>
            <div style={S.label}>Fecha desde</div>
            <input
              type="date"
              value={filters.fecha_desde}
              onChange={(e) => setFilter("fecha_desde", e.target.value)}
              style={S.input}
            />
          </div>

          <div>
            <div style={S.label}>Fecha hasta</div>
            <input
              type="date"
              value={filters.fecha_hasta}
              onChange={(e) => setFilter("fecha_hasta", e.target.value)}
              style={S.input}
            />
          </div>

          <div>
            <div style={S.label}>Empleado</div>
            <select
              value={filters.user_id}
              onChange={(e) => setFilter("user_id", e.target.value)}
              style={S.input}
            >
              <option value="">Todos</option>
              {empleados.map((empleado) => (
                <option key={empleado.id} value={empleado.id}>
                  {empleado.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <div style={S.label}>Empresa laboral</div>
          
            <select
              value={filters.empresa_id}
              onChange={(e) =>
                setFilter(
                  "empresa_id",
                  e.target.value
                )
              }
              style={S.input}
            >
              <option value="">Todas</option>
          
              {empresas.map((empresa) => (
                <option
                  key={empresa.id}
                  value={empresa.id}
                >
                  {empresa.nombre || "Sin nombre"}
                </option>
              ))}
            </select>
          </div>

          <div>
            <div style={S.label}>Unidad laboral</div>
            <select
              value={filters.unidad_servicio_id}
              onChange={(e) =>
                setFilter("unidad_servicio_id", e.target.value)
              }
              style={S.input}
            >
              <option value="">Todas</option>
              {unidades.map((unidad) => (
                <option key={unidad.id} value={unidad.id}>
                  {unidad.nombre || "Sin nombre"}
                </option>
              ))}
            </select>
          </div>

          <div>
            <div style={S.label}>Buscar</div>
            <input
              type="search"
              value={filters.search}
              onChange={(e) => setFilter("search", e.target.value)}
              onKeyDown={(e) => {
                if (e.key === "Enter") consultar();
              }}
              placeholder="Empleado, correo, empresa, unidad o turno"
              style={S.input}
            />
          </div>

          <div>
            <div style={S.label}>Por página</div>
            <select
              value={filters.per_page}
              onChange={(e) =>
                setFilter("per_page", Number(e.target.value))
              }
              style={S.input}
            >
              <option value={10}>10 registros</option>
              <option value={25}>25 registros</option>
              <option value={50}>50 registros</option>
              <option value={100}>100 registros</option>
            </select>
          </div>
        </div>

        <div style={S.actions}>
          <button
            type="button"
            onClick={consultar}
            disabled={loading}
            style={{
              ...S.buttonPrimary,
              opacity: loading ? 0.6 : 1,
            }}
          >
            <i
              className="fa-solid fa-magnifying-glass"
              style={{ marginRight: 7 }}
            />
            Consultar
          </button>

          <button
            type="button"
            onClick={limpiar}
            disabled={loading}
            style={S.buttonSecondary}
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      {successMessage ? (
        <div
          style={{
            padding: "11px 14px",
            borderRadius: 10,
      
            border:
              successMessage.type === "delete"
                ? "1px solid #fecaca"
                : "1px solid #fde68a",
      
            background:
              successMessage.type === "delete"
                ? "#fef2f2"
                : "#fffbeb",
      
            color:
              successMessage.type === "delete"
                ? "#dc2626"
                : "#b45309",
      
            fontWeight: 900,
            fontSize: 13,
          }}
        >
          <i
            className={
              successMessage.type === "delete"
                ? "fa-solid fa-trash"
                : "fa-solid fa-pen"
            }
            style={{ marginRight: 8 }}
          />
      
          {successMessage.message}
        </div>
      ) : null}
      
      {error ? <div style={S.error}>{error}</div> : null}

      {detailError ? (
        <div style={S.error}>
          {detailError}
        </div>
      ) : null}

      <div style={S.card}>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            gap: 10,
            marginBottom: 12,
            flexWrap: "wrap",
          }}
        >
          <div>
            <div style={{ fontSize: 16, fontWeight: 900 }}>Registros</div>
            <div style={S.muted}>
              Total encontrado: <b>{pagination.total}</b>
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
            <button
              type="button"
              onClick={() =>
                exportAsistencias(
                  "excel"
                )
              }
              disabled={
                !!exportLoading
              }
              style={{
                ...S.buttonSecondary,
                color: "#166534",
                borderColor: "#bbf7d0",
                background: "#f0fdf4",
              }}
            >
              <i
                className="fa-solid fa-file-excel"
                style={{
                  marginRight: 7,
                }}
              />
            
              {exportLoading === "excel"
                ? "Generando..."
                : "Excel"}
            </button>
            
            <button
              type="button"
              onClick={() =>
                exportAsistencias(
                  "pdf"
                )
              }
              disabled={
                !!exportLoading
              }
              style={{
                ...S.buttonSecondary,
                color: "#b91c1c",
                borderColor: "#fecaca",
                background: "#fef2f2",
              }}
            >
              <i
                className="fa-solid fa-file-pdf"
                style={{
                  marginRight: 7,
                }}
              />
            
              {exportLoading === "pdf"
                ? "Generando..."
                : "PDF"}
            </button>
            {canViewHistory ? (
              <button
                type="button"
                onClick={openGeneralHistory}
                style={{
                  ...S.buttonSecondary,
                  color: "#334155",
                }}
              >
                <i
                  className="fa-solid fa-clock-rotate-left"
                  style={{ marginRight: 7 }}
                />
          
                Historial general
              </button>
            ) : null}
          
            <button
              type="button"
              onClick={() =>
                load(page, filters)
              }
              disabled={loading}
              style={S.buttonSecondary}
            >
              <i
                className="fa-solid fa-rotate"
                style={{ marginRight: 7 }}
              />
          
              Actualizar
            </button>
          </div>
        </div>

        {loading ? (
          <div
            style={{
              padding: 20,
              color: "#64748b",
              fontWeight: 800,
            }}
          >
            Cargando registros...
          </div>
        ) : (
          <div style={S.tableWrap}>
            <table style={S.table}>
              <thead>
                <tr>
                  <th style={S.th}>Fecha</th>
                  <th style={S.th}>Empleado</th>
                  <th style={S.th}>Empresa laboral</th>
                  <th style={S.th}>Unidad laboral</th>
                  <th style={S.th}>Turno</th>
                  <th style={S.th}>Entrada</th>
                  <th style={S.th}>Comida</th>
                  <th style={S.th}>Salida</th>
                  <th style={S.th}>Presencia</th>
                  <th style={S.th}>Trabajado</th>
                  <th style={S.th}>Jornada</th>
                  
                  {canViewHistory ? (
                    <th style={S.th}>Historial</th>
                  ) : null}
                  
                  <th style={S.th}>Acciones</th>
                </tr>
              </thead>

              <tbody>
                {rows.length === 0 ? (
                  <tr>
                    <td
                      colSpan={
                        12 +
                        (canViewHistory ? 1 : 0)
                      }
                      style={{
                        ...S.td,
                        padding: 24,
                        color: "#64748b",
                      }}
                    >
                      No hay registros con los filtros seleccionados.
                    </td>
                  </tr>
                ) : (
                  rows.map((row) => {
                    const esIncidencia =
                      row.registro_tipo ===
                      "incidencia";
                  
                    return (
                      <tr
                        key={`${row.registro_tipo}-${row.id}`}
                      >
                        <td style={S.td}>
                          {formatDate(
                            row.fecha
                          )}
                        </td>
                  
                        <td style={S.td}>
                          <div
                            style={{
                              fontWeight: 900,
                            }}
                          >
                            {row.empleado_nombre ||
                              "—"}
                          </div>
                  
                          {row.empleado_email ? (
                            <div
                              style={{
                                marginTop: 3,
                                fontSize: 11,
                                color: "#64748b",
                              }}
                            >
                              {row.empleado_email}
                            </div>
                          ) : null}
                        </td>

                        <td style={S.td}>
                          {row.empresa_nombre_snapshot ||
                            "—"}
                        </td>

                        <td style={S.td}>
                          {row.unidad_servicio_nombre_snapshot ||
                            "—"}
                        </td>
                  
                        <td style={S.td}>
                          {row.turno_nombre_snapshot ||
                            "—"}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia
                            ? "—"
                            : formatTime(
                                row.hora_entrada
                              )}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia ? (
                            "—"
                          ) : row.hora_inicio_comida ? (
                            `${formatTime(
                              row.hora_inicio_comida
                            )} - ${formatTime(
                              row.hora_fin_comida
                            )}`
                          ) : (
                            "—"
                          )}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia
                            ? "—"
                            : formatTime(
                                row.hora_salida
                              )}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia
                            ? "—"
                            : formatSeconds(
                                row.segundos_presencia
                              )}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia
                            ? "—"
                            : formatSeconds(
                                row.segundos_trabajados
                              )}
                        </td>
                  
                        <td style={S.td}>
                          {esIncidencia ? (
                            <div>
                              <IncidenciaBadge
                                tipo={
                                  row.incidencia_tipo
                                }
                              />
                  
                              {row.incidencia_observaciones ? (
                                <div
                                  style={{
                                    marginTop: 5,
                                    maxWidth: 220,
                                    fontSize: 11,
                                    color: "#64748b",
                                    lineHeight: 1.35,
                                  }}
                                >
                                  {
                                    row.incidencia_observaciones
                                  }
                                </div>
                              ) : null}
                            </div>
                          ) : (
                            <JornadaBadge
                              value={
                                row.estado_jornada
                              }
                            />
                          )}
                        </td>
                  
                        {canViewHistory ? (
                          <td style={S.td}>
                            {esIncidencia ? (
                              <span
                                style={{
                                  color: "#94a3b8",
                                }}
                              >
                                —
                              </span>
                            ) : (
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
                            )}
                          </td>
                        ) : null}
                  
                        <td style={S.td}>
                          {esIncidencia ? (
                            <span
                              style={{
                                color: "#94a3b8",
                                fontSize: 12,
                                fontWeight: 800,
                              }}
                            >
                              —
                            </span>
                          ) : (
                            <div
                              style={{
                                display: "flex",
                                justifyContent:
                                  "center",
                                alignItems:
                                  "center",
                                gap: 7,
                              }}
                            >
                              <button
                                type="button"
                                title="Ver detalle"
                                onClick={() =>
                                  openDetail(row)
                                }
                                disabled={
                                  detailLoading
                                }
                                style={{
                                  ...S.detailButton,
                                  opacity:
                                    detailLoading
                                      ? 0.6
                                      : 1,
                                }}
                              >
                                <i className="fa-solid fa-eye" />
                              </button>
                  
                              {canEdit ? (
                                <button
                                  type="button"
                                  title="Editar asistencia"
                                  onClick={() =>
                                    openEdit(row)
                                  }
                                  style={{
                                    ...S.detailButton,
                                    border:
                                      "1px solid #fde68a",
                                    background:
                                      "#fffbeb",
                                    color:
                                      "#b45309",
                                  }}
                                >
                                  <i className="fa-solid fa-pen" />
                                </button>
                              ) : null}
                  
                              {canDelete ? (
                                <button
                                  type="button"
                                  title="Eliminar asistencia"
                                  onClick={() =>
                                    openDelete(row)
                                  }
                                  style={{
                                    ...S.detailButton,
                                    border:
                                      "1px solid #fecaca",
                                    background:
                                      "#fef2f2",
                                    color:
                                      "#dc2626",
                                  }}
                                >
                                  <i className="fa-solid fa-trash" />
                                </button>
                              ) : null}
                            </div>
                          )}
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        )}

        <div style={S.pagination}>
          <button
            type="button"
            disabled={loading || pagination.current_page <= 1}
            onClick={() =>
              load(Math.max(1, page - 1), filters)
            }
            style={S.buttonSecondary}
          >
            Anterior
          </button>

          <div
            style={{
              fontSize: 13,
              color: "#475569",
              fontWeight: 800,
            }}
          >
            Página {pagination.current_page} de {pagination.last_page}
          </div>

          <button
            type="button"
            disabled={
              loading ||
              pagination.current_page >= pagination.last_page
            }
            onClick={() =>
              load(Math.min(pagination.last_page, page + 1), filters)
            }
            style={S.buttonSecondary}
          >
            Siguiente
          </button>
        </div>
      </div>

      {generalHistoryOpen ? (
        <div
          style={S.modalBackdrop}
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
              maxWidth: 1150,
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems: "flex-start",
                justifyContent:
                  "space-between",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 20,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Historial general de asistencias
                </div>
      
                <div style={S.muted}>
                  Consulta de movimientos,
                  incluyendo asistencias eliminadas.
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
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background: "#ffffff",
                  cursor: "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            <div
              style={{
                marginTop: 18,
              }}
            >
              {generalHistoryError ? (
                <div style={S.error}>
                  {generalHistoryError}
                </div>
              ) : null}
      
              {generalHistoryLoading ? (
                <div
                  style={{
                    padding: 20,
                    color: "#64748b",
                    fontWeight: 800,
                  }}
                >
                  Cargando historial general...
                </div>
              ) : (
                <div
                  style={{
                    ...S.tableWrap,
                    marginTop:
                      generalHistoryError
                        ? 12
                        : 0,
                  }}
                >
                  <table
                    style={{
                      ...S.table,
                      minWidth: 1000,
                    }}
                  >
                    <thead>
                      <tr>
                        <th style={S.th}>
                          Fecha
                        </th>
                        
                        <th style={S.th}>
                          Empleado
                        </th>
                        
                        <th style={S.th}>
                          Empresa laboral
                        </th>
                        
                        <th style={S.th}>
                          Unidad laboral
                        </th>
                        
                        <th style={S.th}>
                          Turno
                        </th>
      
                        <th style={S.th}>
                          Movimientos
                        </th>
      
                        <th style={S.th}>
                          Estado
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
                      {generalHistoryRows.length ===
                      0 ? (
                        <tr>
                          <td
                            colSpan={9}
                            style={{
                              ...S.td,
                              padding: 24,
                              color:
                                "#64748b",
                            }}
                          >
                            No hay registros de historial.
                          </td>
                        </tr>
                      ) : (
                        generalHistoryRows.map(
                          (row) => (
                            <tr
                              key={
                                row.asistencia_id
                              }
                            >
                              <td style={S.td}>
                                {formatDate(
                                  row.fecha
                                )}
                              </td>
      
                              <td style={S.td}>
                                <div
                                  style={{
                                    fontWeight:
                                      900,
                                  }}
                                >
                                  {row.empleado_nombre ||
                                    "—"}
                                </div>
      
                                {row.empleado_email ? (
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
                                      row.empleado_email
                                    }
                                  </div>
                                ) : null}
                              </td>
      
                              <td style={S.td}>
                                {row.empresa_laboral ||
                                  "—"}
                              </td>
                              
                              <td style={S.td}>
                                {row.unidad_laboral ||
                                  "—"}
                              </td>
                              
                              <td style={S.td}>
                                {row.turno ||
                                  "—"}
                              </td>
      
                              <td style={S.td}>
                                <span
                                  style={{
                                    display:
                                      "inline-flex",
                                    alignItems:
                                      "center",
                                    justifyContent:
                                      "center",
                                    minWidth: 30,
                                    padding:
                                      "5px 9px",
                                    borderRadius:
                                      999,
                                    border:
                                      "1px solid #cbd5e1",
                                    background:
                                      "#f8fafc",
                                    color:
                                      "#334155",
                                    fontSize: 12,
                                    fontWeight:
                                      900,
                                  }}
                                >
                                  {row.movimientos ??
                                    0}
                                </span>
                              </td>
      
                              <td style={S.td}>
                                <span
                                  style={{
                                    display:
                                      "inline-flex",
                                    alignItems:
                                      "center",
                                    justifyContent:
                                      "center",
                                    padding:
                                      "5px 9px",
                                    borderRadius:
                                      999,
      
                                    border:
                                      row.deleted
                                        ? "1px solid #fecaca"
                                        : "1px solid #bbf7d0",
      
                                    background:
                                      row.deleted
                                        ? "#fef2f2"
                                        : "#f0fdf4",
      
                                    color:
                                      row.deleted
                                        ? "#b91c1c"
                                        : "#166534",
      
                                    fontSize: 12,
                                    fontWeight:
                                      900,
                                    whiteSpace:
                                      "nowrap",
                                  }}
                                >
                                  <i
                                    className={
                                      row.deleted
                                        ? "fa-solid fa-trash"
                                        : "fa-solid fa-circle-check"
                                    }
                                    style={{
                                      marginRight:
                                        6,
                                    }}
                                  />
      
                                  {row.deleted
                                    ? "Eliminado"
                                    : "Activo"}
                                </span>
                              </td>
      
                              <td style={S.td}>
                                {formatDateTime(
                                  row.last_action_at
                                )}
                              </td>
      
                              <td style={S.td}>
                                <button
                                  type="button"
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
                                    fontSize: 12,
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
                          )
                        )
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
      
            {!generalHistoryLoading ? (
              <div
                style={{
                  ...S.pagination,
                  marginTop: 16,
                }}
              >
                <button
                  type="button"
                  disabled={
                    generalHistoryPagination
                      .current_page <= 1
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
                  style={S.buttonSecondary}
                >
                  Anterior
                </button>
      
                <div
                  style={{
                    fontSize: 13,
                    color: "#475569",
                    fontWeight: 800,
                  }}
                >
                  Página{" "}
                  {
                    generalHistoryPagination
                      .current_page
                  }{" "}
                  de{" "}
                  {
                    generalHistoryPagination
                      .last_page
                  }
                  {" · "}
                  {
                    generalHistoryPagination
                      .total
                  }{" "}
                  asistencias
                </div>
      
                <button
                  type="button"
                  disabled={
                    generalHistoryPagination
                      .current_page >=
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
                  style={S.buttonSecondary}
                >
                  Siguiente
                </button>
              </div>
            ) : null}
      
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
                  closeGeneralHistory
                }
                disabled={
                  generalHistoryLoading
                }
                style={S.buttonPrimary}
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      ) : null}

      {deleteItem ? (
        <div
          style={S.modalBackdrop}
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeDelete();
            }
          }}
        >
          <div
            style={{
              ...S.modal,
              maxWidth: 500,
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems:
                  "flex-start",
                justifyContent:
                  "space-between",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 20,
                    fontWeight: 900,
                    color: "#991b1b",
                  }}
                >
                  Eliminar asistencia
                </div>
      
                <div
                  style={{
                    ...S.muted,
                    marginTop: 5,
                  }}
                >
                  Esta acción no se puede
                  deshacer.
                </div>
              </div>
      
              <button
                type="button"
                onClick={closeDelete}
                disabled={deleteLoading}
                style={{
                  width: 36,
                  height: 36,
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background: "#ffffff",
                  cursor: "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            {deleteError ? (
              <div
                style={{
                  ...S.error,
                  marginTop: 16,
                }}
              >
                {deleteError}
              </div>
            ) : null}
      
            <div
              style={{
                marginTop: 18,
                padding: 14,
                borderRadius: 12,
                border:
                  "1px solid #fecaca",
                background: "#fef2f2",
              }}
            >
              <div
                style={{
                  fontSize: 13,
                  color: "#7f1d1d",
                  lineHeight: 1.6,
                }}
              >
                ¿Seguro que deseas eliminar
                la asistencia de{" "}
                <strong>
                  {deleteItem
                    .empleado_nombre ||
                    "este empleado"}
                </strong>
                {" "}del{" "}
                <strong>
                  {formatDate(
                    deleteItem.fecha
                  )}
                </strong>
                ?
              </div>
      
              <div
                style={{
                  marginTop: 8,
                  fontSize: 12,
                  color: "#991b1b",
                }}
              >
                También se eliminarán la firma
                y fotografía asociadas a este
                registro.
              </div>
            </div>
      
            <div
              style={{
                display: "flex",
                justifyContent:
                  "flex-end",
                gap: 10,
                marginTop: 20,
              }}
            >
              <button
                type="button"
                onClick={closeDelete}
                disabled={deleteLoading}
                style={S.buttonSecondary}
              >
                Cancelar
              </button>
      
              <button
                type="button"
                onClick={confirmDelete}
                disabled={deleteLoading}
                style={{
                  minHeight: 42,
                  padding: "9px 14px",
                  borderRadius: 10,
                  border: "none",
                  background: "#dc2626",
                  color: "#ffffff",
                  fontWeight: 900,
                  opacity:
                    deleteLoading
                      ? 0.65
                      : 1,
                  cursor:
                    deleteLoading
                      ? "not-allowed"
                      : "pointer",
                }}
              >
                <i
                  className="fa-solid fa-trash"
                  style={{
                    marginRight: 7,
                  }}
                />
              
                {deleteLoading
                  ? "Eliminando..."
                  : "Eliminar asistencia"}
              </button>
            </div>
          </div>
        </div>
      ) : null}

      {historyItem ? (
        <div
          style={{
            ...S.modalBackdrop,
            zIndex: 110,
          }}
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
              maxWidth: 980,
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems: "flex-start",
                justifyContent: "space-between",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 20,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Historial de asistencia
                </div>
      
                <div style={S.muted}>
                  {historyItem
                    .empleado_nombre ||
                    "Empleado"}
      
                  {" · "}
      
                  {formatDate(
                    historyItem.fecha
                  )}
                </div>
              </div>
      
              <button
                type="button"
                onClick={closeHistory}
                disabled={historyLoading}
                style={{
                  width: 36,
                  height: 36,
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background: "#ffffff",
                  cursor: "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            <div
              style={{
                marginTop: 20,
              }}
            >
              {historyLoading ? (
                <div
                  style={{
                    color: "#64748b",
                    fontWeight: 800,
                  }}
                >
                  Cargando historial...
                </div>
              ) : historyError ? (
                <div style={S.error}>
                  {historyError}
                </div>
              ) : historyItems.length === 0 ? (
                <div
                  style={{
                    color: "#64748b",
                    fontWeight: 700,
                  }}
                >
                  No hay historial para mostrar.
                </div>
              ) : (
                numberedHistoryItems.map(
                  (item) => {
                    const changes =
                      Array.isArray(
                        item.changes
                      )
                        ? item.changes
                        : [];
      
                    return (
                      <div
                        key={item.id}
                        style={{
                          border:
                            "1px solid #e2e8f0",
                          borderRadius: 14,
                          overflow: "hidden",
                          marginBottom: 14,
                        }}
                      >
                        <div
                          style={{
                            padding:
                              "13px 15px",
                            background:
                              "#f8fafc",
                            borderBottom:
                              "1px solid #e2e8f0",
                          }}
                        >
                          <div
                            style={{
                              display: "flex",
                              alignItems:
                                "center",
                              gap: 8,
                              flexWrap:
                                "wrap",
                            }}
                          >
                            <span
                              style={{
                                display:
                                  "inline-flex",
                                padding:
                                  "5px 9px",
                                borderRadius:
                                  999,
                                border:
                                  item.action ===
                                  "deleted"
                                    ? "1px solid #fecaca"
                                    : item.action ===
                                      "created"
                                    ? "1px solid #86efac"
                                    : "1px solid #93c5fd",
      
                                background:
                                  item.action ===
                                  "deleted"
                                    ? "#fef2f2"
                                    : item.action ===
                                      "created"
                                    ? "#ecfdf5"
                                    : "#eff6ff",
      
                                color:
                                  item.action ===
                                  "deleted"
                                    ? "#b91c1c"
                                    : item.action ===
                                      "created"
                                    ? "#166534"
                                    : "#1d4ed8",
      
                                fontSize: 12,
                                fontWeight: 900,
                              }}
                            >
                              {item.action ===
                              "created"
                                ? "Creación"
                                : item.action ===
                                  "deleted"
                                ? "Eliminación"
                                : `Edición ${
                                    item.updateNumber
                                  }`}
                            </span>
      
                            <span
                              style={{
                                fontSize: 12,
                                color:
                                  "#475569",
                                fontWeight: 800,
                              }}
                            >
                              {formatDateTime(
                                item.created_at
                              )}
                            </span>
                          </div>
      
                          <div
                            style={{
                              marginTop: 8,
                              fontSize: 13,
                              color:
                                "#334155",
                            }}
                          >
                            <strong>
                              Por:
                            </strong>{" "}
                            {item.actor
                              ?.name ||
                              "Sistema"}
      
                            {item.actor
                              ?.email
                              ? ` (${item.actor.email})`
                              : ""}
                          </div>
                        </div>
      
                        <div
                          style={{
                            padding: 15,
                          }}
                        >
                          {item.action ===
                          "updated" ? (
                            changes.length ? (
                              <div
                                style={{
                                  display:
                                    "grid",
                                  gridTemplateColumns:
                                    "repeat(auto-fit, minmax(210px, 1fr))",
                                  gap: 12,
                                }}
                              >
                                {changes.map(
                                  (
                                    change,
                                    changeIndex
                                  ) => (
                                    <div
                                      key={`${item.id}-${changeIndex}`}
                                      style={{
                                        border:
                                          "1px solid #e2e8f0",
                                        borderRadius:
                                          12,
                                        padding: 12,
                                        background:
                                          "#ffffff",
                                      }}
                                    >
                                      <div
                                        style={{
                                          fontSize:
                                            12,
                                          color:
                                            "#334155",
                                          fontWeight:
                                            900,
                                          marginBottom:
                                            10,
                                        }}
                                      >
                                        {
                                          change.label
                                        }
                                      </div>
      
                                      <div
                                        style={{
                                          fontSize:
                                            11,
                                          color:
                                            "#64748b",
                                          fontWeight:
                                            800,
                                        }}
                                      >
                                        Antes
                                      </div>
      
                                      <div
                                        style={{
                                          marginTop:
                                            3,
                                          color:
                                            "#0f172a",
                                          fontWeight:
                                            800,
                                        }}
                                      >
                                        {formatHistoryValue(
                                          change.field,
                                          change.type,
                                          change.old
                                        )}
                                      </div>
      
                                      <div
                                        style={{
                                          marginTop:
                                            10,
                                          fontSize:
                                            11,
                                          color:
                                            "#64748b",
                                          fontWeight:
                                            800,
                                        }}
                                      >
                                        Ahora
                                      </div>
      
                                      <div
                                        style={{
                                          marginTop:
                                            3,
                                          color:
                                            "#0f172a",
                                          fontWeight:
                                            800,
                                        }}
                                      >
                                        {formatHistoryValue(
                                          change.field,
                                          change.type,
                                          change.new
                                        )}
                                      </div>
                                    </div>
                                  )
                                )}
                              </div>
                            ) : (
                              <div
                                style={{
                                  color:
                                    "#64748b",
                                }}
                              >
                                No se detectaron cambios.
                              </div>
                            )
                          ) : item.action === "created" ? (
                            <div>
                              <div
                                style={{
                                  marginBottom: 12,
                                  fontSize: 13,
                                  fontWeight: 900,
                                  color: "#166534",
                                }}
                              >
                                Datos iniciales de la asistencia
                              </div>
                          
                              <div
                                style={{
                                  display: "grid",
                                  gridTemplateColumns:
                                    "repeat(auto-fit, minmax(190px, 1fr))",
                                  gap: 10,
                                }}
                              >
                                {getCreationHistoryFields(
                                  item.snapshot
                                ).map(
                                  (
                                    field,
                                    fieldIndex
                                  ) => (
                                    <div
                                      key={`${item.id}-creation-${fieldIndex}`}
                                      style={{
                                        border:
                                          "1px solid #bbf7d0",
                                        borderRadius: 12,
                                        padding: 12,
                                        background:
                                          "#f0fdf4",
                                      }}
                                    >
                                      <div
                                        style={{
                                          fontSize: 11,
                                          color: "#166534",
                                          fontWeight: 900,
                                          marginBottom: 5,
                                        }}
                                      >
                                        {field.label}
                                      </div>
                          
                                      <div
                                        style={{
                                          fontSize: 13,
                                          color: "#14532d",
                                          fontWeight: 800,
                                        }}
                                      >
                                        {field.value}
                                      </div>
                                    </div>
                                  )
                                )}
                              </div>
                            </div>
                          ) : item.action === "deleted" ? (
                            <div>
                              <div
                                style={{
                                  marginBottom: 12,
                                  fontSize: 13,
                                  fontWeight: 900,
                                  color: "#b91c1c",
                                }}
                              >
                                Estado final antes de eliminar
                              </div>
                          
                              <div
                                style={{
                                  display: "grid",
                                  gridTemplateColumns:
                                    "repeat(auto-fit, minmax(190px, 1fr))",
                                  gap: 10,
                                }}
                              >
                                {getDeletionHistoryFields(
                                  item.snapshot
                                ).map(
                                  (
                                    field,
                                    fieldIndex
                                  ) => (
                                    <div
                                      key={`${item.id}-deletion-${fieldIndex}`}
                                      style={{
                                        border:
                                          "1px solid #fecaca",
                                        borderRadius: 12,
                                        padding: 12,
                                        background:
                                          "#fef2f2",
                                      }}
                                    >
                                      <div
                                        style={{
                                          fontSize: 11,
                                          color: "#991b1b",
                                          fontWeight: 900,
                                          marginBottom: 5,
                                        }}
                                      >
                                        {field.label}
                                      </div>
                          
                                      <div
                                        style={{
                                          fontSize: 13,
                                          color: "#7f1d1d",
                                          fontWeight: 800,
                                        }}
                                      >
                                        {field.value}
                                      </div>
                                    </div>
                                  )
                                )}
                              </div>
                            </div>
                          ) : (
                            <div
                              style={{
                                color: "#64748b",
                                fontWeight: 700,
                              }}
                            >
                              Información histórica almacenada correctamente.
                            </div>
                          )}
                        </div>
                      </div>
                    );
                  }
                )
              )}
            </div>
      
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
                onClick={closeHistory}
                style={S.buttonPrimary}
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      ) : null}

      {editItem ? (
        <div
          style={S.modalBackdrop}
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeEdit();
            }
          }}
        >
          <div
            style={{
              ...S.modal,
              maxWidth: 620,
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems:
                  "flex-start",
                justifyContent:
                  "space-between",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 20,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Editar asistencia
                </div>
      
                <div style={S.muted}>
                  {editItem
                    .empleado_nombre ||
                    "Empleado"}
                </div>
              </div>
      
              <button
                type="button"
                onClick={closeEdit}
                disabled={editLoading}
                style={{
                  width: 36,
                  height: 36,
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background:
                    "#ffffff",
                  cursor: "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            {editError ? (
              <div
                style={{
                  ...S.error,
                  marginTop: 16,
                }}
              >
                {editError}
              </div>
            ) : null}
      
            <div
              style={{
                ...S.detailGrid,
                marginTop: 20,
              }}
            >
              <div>
                <div style={S.label}>
                  Fecha
                </div>
      
                <input
                  type="date"
                  value={
                    editForm.fecha
                  }
                  onChange={(e) =>
                    setEditField(
                      "fecha",
                      e.target.value
                    )
                  }
                  style={S.input}
                />
              </div>
      
              <div>
                <div style={S.label}>
                  Hora de entrada
                </div>
      
                <input
                  type="time"
                  step="1"
                  value={
                    editForm
                      .hora_entrada
                  }
                  onChange={(e) =>
                    setEditField(
                      "hora_entrada",
                      e.target.value
                    )
                  }
                  style={S.input}
                />
              </div>
      
              <div>
                <div style={S.label}>
                  Inicio de comida
                </div>
      
                <input
                  type="time"
                  step="1"
                  value={
                    editForm
                      .hora_inicio_comida
                  }
                  onChange={(e) =>
                    setEditField(
                      "hora_inicio_comida",
                      e.target.value
                    )
                  }
                  style={S.input}
                />
              </div>
      
              <div>
                <div style={S.label}>
                  Fin de comida
                </div>
      
                <input
                  type="time"
                  step="1"
                  value={
                    editForm
                      .hora_fin_comida
                  }
                  onChange={(e) =>
                    setEditField(
                      "hora_fin_comida",
                      e.target.value
                    )
                  }
                  style={S.input}
                />
              </div>
      
              <div>
                <div style={S.label}>
                  Hora de salida
                </div>
      
                <input
                  type="time"
                  step="1"
                  value={
                    editForm
                      .hora_salida
                  }
                  onChange={(e) =>
                    setEditField(
                      "hora_salida",
                      e.target.value
                    )
                  }
                  style={S.input}
                />
              </div>
            </div>
      
            <div
              style={{
                display: "flex",
                justifyContent:
                  "flex-end",
                gap: 10,
                marginTop: 22,
              }}
            >
              <button
                type="button"
                onClick={closeEdit}
                disabled={editLoading}
                style={S.buttonSecondary}
              >
                Cancelar
              </button>
      
              <button
                type="button"
                onClick={saveEdit}
                disabled={editLoading}
                style={{
                  ...S.buttonPrimary,
                  opacity:
                    editLoading
                      ? 0.65
                      : 1,
                  cursor:
                    editLoading
                      ? "not-allowed"
                      : "pointer",
                }}
              >
                <i
                  className="fa-solid fa-floppy-disk"
                  style={{
                    marginRight: 7,
                  }}
                />
              
                {editLoading
                  ? "Guardando..."
                  : "Guardar cambios"}
              </button>
            </div>
          </div>
        </div>
      ) : null}

      {detailItem ? (
        <div
          style={
            S.modalBackdrop
          }
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeDetail();
            }
          }}
        >
          <div style={S.modal}>
            <div
              style={{
                display: "flex",
                alignItems:
                  "flex-start",
                justifyContent:
                  "space-between",
                gap: 12,
              }}
            >
              <div>
                <div
                  style={{
                    fontSize: 20,
                    fontWeight: 900,
                    color: "#0f172a",
                  }}
                >
                  Detalle de asistencia
                </div>

                <div style={S.muted}>
                  {detailItem
                    .empleado?.name ||
                    "Empleado"}
                  {" · "}
                  {formatDate(
                    detailItem.fecha
                  )}
                </div>
              </div>

              <button
                type="button"
                onClick={
                  closeDetail
                }
                style={{
                  width: 36,
                  height: 36,
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background: "#ffffff",
                  cursor: "pointer",
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>

            <div
              style={{
                marginTop: 20,
                fontSize: 14,
                fontWeight: 900,
                color: "#0f172a",
              }}
            >
              Información laboral
            </div>

            <div
              style={{
                ...S.detailGrid,
                marginTop: 10,
              }}
            >
              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Empleado
                </div>

                <div style={S.detailValue}>
                  {detailItem
                    .empleado?.name ||
                    "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Correo
                </div>

                <div style={S.detailValue}>
                  {detailItem
                    .empleado?.email ||
                    "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Empresa laboral
                </div>
              
                <div style={S.detailValue}>
                  {detailItem
                    .empresa_laboral
                    ?.nombre ||
                    "—"}
                </div>
              </div>
              
              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Unidad laboral
                </div>
              
                <div style={S.detailValue}>
                  {detailItem
                    .unidad_laboral
                    ?.nombre ||
                    "—"}
                </div>
              </div>
              
              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Tipo de trabajo
                </div>

                <div style={S.detailValue}>
                  {formatTipoTrabajo(
                    detailItem
                      .tipo_trabajo
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Turno
                </div>

                <div style={S.detailValue}>
                  {detailItem.turno
                    ?.nombre ||
                    "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Horario del turno
                </div>

                <div style={S.detailValue}>
                  {detailItem.turno
                    ? `${String(
                        detailItem.turno
                          .hora_inicio ||
                          ""
                      ).slice(
                        0,
                        5
                      )} - ${String(
                        detailItem.turno
                          .hora_fin ||
                          ""
                      ).slice(
                        0,
                        5
                      )}`
                    : "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Jornada requerida
                </div>

                <div style={S.detailValue}>
                  {detailItem.turno
                    ?.horas_jornada !==
                  null
                    ? `${detailItem.turno.horas_jornada} h`
                    : "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Método de entrada
                </div>

                <div style={S.detailValue}>
                  {formatMetodoEntrada(
                    detailItem
                      .metodo_entrada
                  )}
                </div>
              </div>
            </div>

            <div
              style={{
                marginTop: 20,
                fontSize: 14,
                fontWeight: 900,
                color: "#0f172a",
              }}
            >
              Tiempos
            </div>

            <div
              style={{
                ...S.detailGrid,
                marginTop: 10,
              }}
            >
              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Entrada
                </div>

                <div style={S.detailValue}>
                  {formatTime(
                    detailItem
                      .hora_entrada
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Inicio comida
                </div>

                <div style={S.detailValue}>
                  {formatTime(
                    detailItem
                      .hora_inicio_comida
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Fin comida
                </div>

                <div style={S.detailValue}>
                  {formatTime(
                    detailItem
                      .hora_fin_comida
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Salida
                </div>

                <div style={S.detailValue}>
                  {formatTime(
                    detailItem
                      .hora_salida
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Presencia
                </div>

                <div style={S.detailValue}>
                  {formatSeconds(
                    detailItem
                      .segundos_presencia
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Comida
                </div>

                <div style={S.detailValue}>
                  {Number(
                    detailItem.turno
                      ?.minutos_comida ||
                      0
                  ) <= 0
                    ? "No aplica"
                    : formatSeconds(
                        detailItem
                          .segundos_comida
                      )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Tiempo trabajado
                </div>

                <div style={S.detailValue}>
                  {formatSeconds(
                    detailItem
                      .segundos_trabajados
                  )}
                </div>
              </div>
            </div>

            <div
              style={{
                marginTop: 20,
                fontSize: 14,
                fontWeight: 900,
                color: "#0f172a",
              }}
            >
              Estados
            </div>

            <div
              style={{
                display: "flex",
                flexWrap: "wrap",
                gap: 7,
                marginTop: 10,
              }}
            >
              <EstadoBadge
                label="Día"
                value={formatEstadoDia(
                  detailItem
                    .estado_dia
                )}
                tone={
                  detailItem
                    .estado_dia ===
                  "laborable"
                    ? "success"
                    : detailItem
                        .estado_dia ===
                      "fuera_dia_laborable"
                      ? "warning"
                      : "neutral"
                }
              />

              <EstadoBadge
                label="Entrada"
                value={formatEstadoHorario(
                  detailItem
                    .estado_entrada
                )}
                tone={
                  detailItem
                    .estado_entrada ===
                  "dentro_horario"
                    ? "success"
                    : detailItem
                        .estado_entrada ===
                      "fuera_horario"
                      ? "danger"
                      : "neutral"
                }
              />

              <EstadoBadge
                label="Salida"
                value={
                  detailItem
                    .hora_salida
                    ? formatEstadoHorario(
                        detailItem
                          .estado_salida
                      )
                    : "En curso"
                }
                tone={
                  !detailItem
                    .hora_salida
                    ? "neutral"
                    : detailItem
                        .estado_salida ===
                      "dentro_horario"
                      ? "success"
                      : detailItem
                          .estado_salida ===
                        "fuera_horario"
                        ? "danger"
                        : "neutral"
                }
              />

              <EstadoBadge
                label="Comida"
                value={formatEstadoComida(
                  detailItem
                    .estado_comida,
                  detailItem.turno
                    ?.minutos_comida
                )}
                tone={
                  detailItem
                    .estado_comida ===
                  "completa"
                    ? "success"
                    : detailItem
                          .estado_comida ===
                        "menor"
                      ? "warning"
                      : detailItem
                          .estado_comida ===
                        "excedida"
                        ? "danger"
                        : "neutral"
                }
              />

              <EstadoBadge
                label="Jornada"
                value={formatEstadoJornada(
                  detailItem
                    .estado_jornada
                )}
                tone={
                  detailItem
                    .estado_jornada ===
                  "cumplida"
                    ? "success"
                    : detailItem
                          .estado_jornada ===
                        "incompleta" ||
                      detailItem
                          .estado_jornada ===
                        "excedida"
                      ? "warning"
                      : "neutral"
                }
              />
            </div>

            <div
              style={{
                marginTop: 22,
                paddingTop: 18,
                borderTop:
                  "1px solid #e2e8f0",
              }}
            >
              <div
                style={{
                  fontSize: 14,
                  fontWeight: 900,
                  color: "#0f172a",
                }}
              >
                Evidencia de entrada
              </div>

              <div
                style={{
                  marginTop: 12,
                }}
              >
                <div
                  style={{
                    fontSize: 12,
                    fontWeight: 900,
                    color: "#475569",
                  }}
                >
                  Firma
                </div>
              
                {detailItem
                  .puede_ver_firma !== true ? (
                  <div
                    style={{
                      marginTop: 9,
                      padding: "12px 14px",
                      borderRadius: 10,
                      border:
                        "1px solid #cbd5e1",
                      background: "#f8fafc",
                      color: "#475569",
                      fontSize: 12,
                      lineHeight: 1.5,
                    }}
                  >
                    <div
                      style={{
                        fontWeight: 900,
                        color: "#334155",
                      }}
                    >
                      <i
                        className="fa-solid fa-lock"
                        style={{
                          marginRight: 7,
                        }}
                      />
              
                      Firma restringida
                    </div>
              
                    <div
                      style={{
                        marginTop: 4,
                      }}
                    >
                      No tienes permiso para
                      consultar la firma
                      registrada en esta
                      asistencia.
                    </div>
                  </div>
                ) : !detailItem
                    .firma_entrada_url ? (
                  <div
                    style={{
                      ...S.detailBox,
                      marginTop: 9,
                      color: "#64748b",
                    }}
                  >
                    Este registro no cuenta
                    con firma de entrada.
                  </div>
                ) : (
                  <>
                    <button
                      type="button"
                      onClick={() =>
                        setMostrarFirmaDetalle(
                          (prev) => !prev
                        )
                      }
                      style={{
                        ...S.buttonPrimary,
                        marginTop: 10,
                      }}
                    >
                      <i
                        className={
                          mostrarFirmaDetalle
                            ? "fa-solid fa-eye-slash"
                            : "fa-solid fa-signature"
                        }
                        style={{
                          marginRight: 8,
                        }}
                      />
              
                      {mostrarFirmaDetalle
                        ? "Ocultar firma"
                        : "Ver firma"}
                    </button>
              
                    {mostrarFirmaDetalle ? (
                      <>
                        <div
                          style={{
                            ...S.detailBox,
                            marginTop: 9,
                            display: "block",
                            width: "fit-content",
                            minWidth: 190,
                            maxWidth: "100%",
                          }}
                        >
                          <div
                            style={
                              S.detailLabel
                            }
                          >
                            Capturada
                          </div>
                    
                          <div
                            style={
                              S.detailValue
                            }
                          >
                            {formatDateTime(
                              detailItem
                                .firma_capturada_at
                            )}
                          </div>
                        </div>
                    
                        <img
                          src={
                            detailItem
                              .firma_entrada_url
                          }
                          alt="Firma de entrada"
                          style={
                            S.signatureImage
                          }
                        />
                      </>
                    ) : null}
                  </>
                )}
              </div>

              <div
                style={{
                  marginTop: 18,
                }}
              >
                <div
                  style={{
                    fontSize: 12,
                    fontWeight: 900,
                    color: "#475569",
                  }}
                >
                  Fotografía de entrada
                </div>
              
                {detailItem
                  .puede_ver_foto !== true ? (
                  <div
                    style={{
                      marginTop: 9,
                      padding: "12px 14px",
                      borderRadius: 10,
                      border:
                        "1px solid #cbd5e1",
                      background: "#f8fafc",
                      color: "#475569",
                      fontSize: 12,
                      lineHeight: 1.5,
                    }}
                  >
                    <div
                      style={{
                        fontWeight: 900,
                        color: "#334155",
                      }}
                    >
                      <i
                        className="fa-solid fa-lock"
                        style={{
                          marginRight: 7,
                        }}
                      />
              
                      Fotografía restringida
                    </div>
              
                    <div
                      style={{
                        marginTop: 4,
                      }}
                    >
                      No tienes permiso para
                      consultar la fotografía
                      registrada en esta
                      asistencia.
                    </div>
                  </div>
                ) : !detailItem
                    .foto_entrada_url ? (
                  <div
                    style={{
                      ...S.detailBox,
                      marginTop: 9,
                      color: "#64748b",
                    }}
                  >
                    Este registro no cuenta
                    con fotografía de entrada.
                  </div>
                ) : (
                  <>
                    <button
                      type="button"
                      onClick={() =>
                        setMostrarFotoDetalle(
                          (prev) => !prev
                        )
                      }
                      style={{
                        ...S.buttonPrimary,
                        marginTop: 10,
                      }}
                    >
                      <i
                        className={
                          mostrarFotoDetalle
                            ? "fa-solid fa-eye-slash"
                            : "fa-solid fa-camera"
                        }
                        style={{
                          marginRight: 8,
                        }}
                      />
                
                      {mostrarFotoDetalle
                        ? "Ocultar fotografía"
                        : "Ver fotografía"}
                    </button>
                
                    {mostrarFotoDetalle ? (
                      <>
                        <div
                          style={{
                            ...S.detailBox,
                            marginTop: 9,
                            display: "block",
                            width: "fit-content",
                            minWidth: 190,
                            maxWidth: "100%",
                          }}
                        >
                          <div
                            style={
                              S.detailLabel
                            }
                          >
                            Capturada
                          </div>
                    
                          <div
                            style={
                              S.detailValue
                            }
                          >
                            {formatDateTime(
                              detailItem
                                .foto_capturada_at
                            )}
                          </div>
                        </div>
                    
                        <img
                          src={
                            detailItem
                              .foto_entrada_url
                          }
                          alt="Fotografía de entrada"
                          style={
                            S.photoImage
                          }
                        />
                      </>
                    ) : null}
                  </>
                )}
              </div>

              <div
                style={{
                  marginTop: 18,
                  fontSize: 12,
                  fontWeight: 900,
                  color: "#475569",
                }}
              >
                Ubicación
              </div>

              {detailItem
                .puede_ver_ubicacion !==
              true ? (
                <div
                  style={{
                    marginTop: 9,
                    padding: "12px 14px",
                    borderRadius: 10,
                    border:
                      "1px solid #cbd5e1",
                    background: "#f8fafc",
                    color: "#475569",
                    fontSize: 12,
                    lineHeight: 1.5,
                  }}
                >
                  <div
                    style={{
                      fontWeight: 900,
                      color: "#334155",
                    }}
                  >
                    <i
                      className="fa-solid fa-lock"
                      style={{
                        marginRight: 7,
                      }}
                    />

                    Ubicación restringida
                  </div>

                  <div
                    style={{
                      marginTop: 4,
                    }}
                  >
                    No tienes permiso para
                    consultar la ubicación
                    registrada en esta
                    asistencia.
                  </div>
                </div>
              ) : !detalleTieneUbicacion ? (
                <div
                  style={{
                    ...S.detailBox,
                    marginTop: 9,
                    color: "#64748b",
                  }}
                >
                  Este registro no cuenta
                  con ubicación de entrada.
                </div>
              ) : (
                <>

                  <button
                    type="button"
                    onClick={() =>
                      setMostrarMapaDetalle(
                        (prev) => !prev
                      )
                    }
                    style={{
                      ...S.buttonPrimary,
                      marginTop: 12,
                    }}
                  >
                    <i
                      className="fa-solid fa-map-location-dot"
                      style={{
                        marginRight: 7,
                      }}
                    />

                    {mostrarMapaDetalle
                      ? "Ocultar ubicación en mapa"
                      : "Ver ubicación en mapa"}
                  </button>

                  {mostrarMapaDetalle &&
                  detalleMapaUrl ? (
                    <>
                      <div
                        style={{
                          ...S.detailGrid,
                          marginTop: 9,
                        }}
                      >
                        <div style={S.detailBox}>
                          <div style={S.detailLabel}>
                            Precisión
                          </div>
                      
                          <div style={S.detailValue}>
                            {detailItem
                              .entrada_precision_metros !==
                            null
                              ? `± ${Math.round(
                                  Number(
                                    detailItem
                                      .entrada_precision_metros
                                  )
                                )} m`
                              : "—"}
                          </div>
                        </div>
                      
                        <div style={S.detailBox}>
                          <div style={S.detailLabel}>
                            Capturada
                          </div>
                      
                          <div style={S.detailValue}>
                            {formatDateTime(
                              detailItem
                                .entrada_ubicacion_capturada_at
                            )}
                          </div>
                        </div>
                      </div>

                      <div
                        style={{
                          ...S.detailGrid,
                          marginTop: 12,
                        }}
                      >
                        <div style={S.detailBox}>
                          <div style={S.detailLabel}>
                            Latitud
                          </div>

                          <div style={S.detailValue}>
                            {detailItem
                              .entrada_latitud}
                          </div>
                        </div>

                        <div style={S.detailBox}>
                          <div style={S.detailLabel}>
                            Longitud
                          </div>

                          <div style={S.detailValue}>
                            {detailItem
                              .entrada_longitud}
                          </div>
                        </div>
                      </div>

                      <div
                        style={{
                          marginTop: 12,
                          overflow: "hidden",
                          borderRadius: 12,
                          border:
                            "1px solid #e2e8f0",
                        }}
                      >
                        <iframe
                          title="Ubicación de entrada"
                          src={
                            detalleMapaUrl
                          }
                          width="100%"
                          height="280"
                          style={{
                            display: "block",
                            border: 0,
                          }}
                          loading="lazy"
                        />
                      </div>
                    </>
                  ) : null}
                </>
              )}
            </div>

            <div
              style={{
                display: "flex",
                justifyContent:
                  "flex-end",
                marginTop: 20,
              }}
            >
              <button
                type="button"
                onClick={
                  closeDetail
                }
                style={
                  S.buttonPrimary
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
