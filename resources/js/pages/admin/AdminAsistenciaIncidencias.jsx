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

const EMPTY_FORM = {
  user_id: "",
  tipo: "",
  fecha_desde: "",
  fecha_hasta: "",
  observaciones: "",
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

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    saving,
    setSaving,
  ] = useState(false);

  const [
    error,
    setError,
  ] = useState("");

  const [
    message,
    setMessage,
  ] = useState("");

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

      setTipos(
        Array.isArray(
          data?.tipos
        )
          ? data.tipos
          : []
      );
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

    setEditingGrupoUuid(
      null
    );

    setForm({
      ...EMPTY_FORM,
    });

    setModalError("");
    setError("");
    setMessage("");
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
    setMessage("");
    setSaving(true);

    try {
      const data =
        await apiGet(
          `/admin/asistencia-incidencias/${row.grupo_uuid}`
        );

      setEditingGrupoUuid(
        row.grupo_uuid
      );

      setForm({
        user_id:
          String(
            data?.user_id || ""
          ),

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

    if (!form.user_id) {
      setModalError(
        "Selecciona un empleado."
      );
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
      user_id:
        Number(form.user_id),

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

    setSaving(true);
    setModalError("");
    setError("");
    setMessage("");

    try {
      let data;

      if (
        editingGrupoUuid
      ) {
        data =
          await apiPut(
            `/admin/asistencia-incidencias/${editingGrupoUuid}`,
            payload
          );
      } else {
        data =
          await apiPost(
            "/admin/asistencia-incidencias",
            payload
          );
      }

      setMessage(
        data?.message ||
          (
            editingGrupoUuid
              ? "Incidencia actualizada correctamente."
              : "Incidencia registrada correctamente."
          )
      );

      setModalOpen(false);
      setEditingGrupoUuid(
        null
      );

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

  async function deleteIncidencia(
    row
  ) {
    if (
      !canDelete ||
      !row?.grupo_uuid
    ) {
      return;
    }

    const employee =
      row?.user?.name ||
      "el empleado";

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
    setMessage("");

    try {
      const data =
        await apiDelete(
          `/admin/asistencia-incidencias/${row.grupo_uuid}`
        );

      setMessage(
        data?.message ||
          "Incidencia eliminada correctamente."
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

  const S = {
    page: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
      width: "100%",
    },

    card: {
      padding: 18,
      border:
        "1px solid #e2e8f0",
      borderRadius: 16,
      background: "#ffffff",
      boxShadow:
        "0 4px 14px rgba(15,23,42,.04)",
    },

    title: {
      margin: 0,
      fontSize: 22,
      fontWeight: 900,
      color: "#0f172a",
    },

    muted: {
      marginTop: 5,
      color: "#64748b",
      fontSize: 13,
    },

    filters: {
      display: "grid",
      gridTemplateColumns:
        "repeat(auto-fit, minmax(180px, 1fr))",
      gap: 12,
      marginTop: 16,
    },

    field: {
      display: "flex",
      flexDirection: "column",
      gap: 5,
    },

    label: {
      fontSize: 12,
      fontWeight: 900,
      color: "#475569",
    },

    input: {
      width: "100%",
      boxSizing: "border-box",
      minHeight: 42,
      border:
        "1px solid #cbd5e1",
      borderRadius: 9,
      padding: "8px 10px",
      background: "#ffffff",
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

    buttonPrimary: {
      border: "none",
      borderRadius: 9,
      minHeight: 42,
      padding: "9px 14px",
      background: "#2563eb",
      color: "#ffffff",
      cursor: "pointer",
      fontWeight: 900,
    },

    buttonNeutral: {
      border:
        "1px solid #cbd5e1",
      borderRadius: 9,
      minHeight: 42,
      padding: "9px 14px",
      background: "#ffffff",
      color: "#334155",
      cursor: "pointer",
      fontWeight: 800,
    },

    buttonEdit: {
      border:
        "1px solid #bfdbfe",
      background: "#eff6ff",
      color: "#1d4ed8",
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

    tableWrap: {
      width: "100%",
      overflowX: "auto",
      marginTop: 14,
    },

    table: {
      width: "100%",
      borderCollapse:
        "collapse",
      minWidth: 920,
    },

    th: {
      padding: "10px 9px",
      borderBottom:
        "1px solid #cbd5e1",
      textAlign: "left",
      fontSize: 12,
      fontWeight: 900,
      color: "#475569",
      whiteSpace: "nowrap",
    },

    td: {
      padding: "11px 9px",
      borderBottom:
        "1px solid #f1f5f9",
      fontSize: 13,
      color: "#0f172a",
      verticalAlign: "middle",
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
            alignItems:
              "flex-start",
            justifyContent:
              "space-between",
            gap: 12,
            flexWrap: "wrap",
          }}
        >
          <div>
            <h1 style={S.title}>
              Incidencias de asistencia
            </h1>

            <div style={S.muted}>
              Vacaciones,
              incapacidades y días
              festivos de los
              empleados.
            </div>
          </div>

          {canCreate ? (
            <button
              type="button"
              onClick={openCreate}
              style={
                S.buttonPrimary
              }
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
      </div>

      {error ? (
        <div
          style={S.alertError}
        >
          {error}
        </div>
      ) : null}

      {message ? (
        <div
          style={S.alertSuccess}
        >
          {message}
        </div>
      ) : null}

      <div style={S.card}>
        <div
          style={{
            fontSize: 16,
            fontWeight: 900,
            color: "#0f172a",
          }}
        >
          Filtros
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
                    value={
                      user.id
                    }
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
                loading
                  ? 0.6
                  : 1,
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
            Limpiar
          </button>
        </div>
      </div>

      <div style={S.card}>
        <div
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent:
              "space-between",
            gap: 10,
          }}
        >
          <div
            style={{
              fontSize: 16,
              fontWeight: 900,
            }}
          >
            Incidencias registradas
          </div>

          <button
            type="button"
            title="Actualizar"
            onClick={() =>
              load(
                appliedFilters
              )
            }
            disabled={loading}
            style={{
              ...S.buttonNeutral,
              minHeight: 36,
              width: 40,
              padding: 0,
            }}
          >
            <i className="fa-solid fa-rotate" />
          </button>
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
                    colSpan={8}
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
                    colSpan={8}
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
                          <b>
                            {row.user
                              ?.name ||
                              "—"}
                          </b>

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
    </div>
  );
}