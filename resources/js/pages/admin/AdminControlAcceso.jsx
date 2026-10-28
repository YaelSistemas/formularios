import React, {
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";

import {
  apiGet,
  apiPost,
  apiPut,
  apiDelete,
} from "../../services/api";

const DAYS = [
  ["lunes", "Lunes"],
  ["martes", "Martes"],
  ["miercoles", "Miércoles"],
  ["jueves", "Jueves"],
  ["viernes", "Viernes"],
  ["sabado", "Sábado"],
  ["domingo", "Domingo"],
];

const EMPTY_FORM = {
  nombre: "",
  descripcion: "",
  hora_inicio: "06:00",
  hora_fin: "20:00",
  horas_jornada: "8.00",
  minutos_comida: "60",
  comida_cuenta_jornada: false,

  lunes: true,
  martes: true,
  miercoles: true,
  jueves: true,
  viernes: true,
  sabado: true,
  domingo: false,

  vigente_desde: "",
  vigente_hasta: "",
  activo: true,
};

export default function AdminControlAcceso() {
  const [err, setErr] = useState("");

  /*
  |--------------------------------------------------------------------------
  | Usuario / permisos
  |--------------------------------------------------------------------------
  */

  const getStoredUser = () => {
    try {
      return JSON.parse(
        localStorage.getItem("user") ||
          "null"
      );
    } catch {
      return null;
    }
  };

  const normalizeRoles = (user) => {
    if (!user) return [];

    const rolesFromArray =
      Array.isArray(user.roles)
        ? user.roles
            .map((r) =>
              typeof r === "string"
                ? r
                : r?.name
            )
            .filter(Boolean)
        : [];

    const roleSingle = user.role
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
  };

  const normalizePermissions = (
    user
  ) => {
    if (!user) return [];

    const directPermissions =
      Array.isArray(user.permissions)
        ? user.permissions
            .map((p) =>
              typeof p === "string"
                ? p
                : p?.name
            )
            .filter(Boolean)
        : [];

    return [
      ...new Set(directPermissions),
    ];
  };

  const me = getStoredUser();

  const isAdmin =
    !!me?.is_admin ||
    normalizeRoles(me).some(
      (r) =>
        String(r).toLowerCase() ===
        "administrador"
    );

  const hasPermission = (
    permission
  ) => {
    if (isAdmin) return true;

    return normalizePermissions(
      me
    ).includes(permission);
  };

  const canCreate =
    hasPermission("turnos.create");

  const canEdit =
    hasPermission("turnos.edit");

  const canDelete =
    hasPermission("turnos.delete");

  const canShowActionsColumn =
    canEdit || canDelete;

  /*
  |--------------------------------------------------------------------------
  | Toast
  |--------------------------------------------------------------------------
  */

  const [toast, setToast] =
    useState(null);

  const toastTimer =
    useRef(null);

  const showToast = (
    type,
    text
  ) => {
    setToast({
      type,
      text,
    });

    if (toastTimer.current) {
      clearTimeout(
        toastTimer.current
      );
    }

    toastTimer.current =
      setTimeout(
        () => setToast(null),
        3000
      );
  };

  useEffect(() => {
    return () => {
      if (toastTimer.current) {
        clearTimeout(
          toastTimer.current
        );
      }
    };
  }, []);

  /*
  |--------------------------------------------------------------------------
  | Listado
  |--------------------------------------------------------------------------
  */

  const [rows, setRows] =
    useState([]);

  const [loading, setLoading] =
    useState(false);

  const [qDraft, setQDraft] =
    useState("");

  const [q, setQ] =
    useState("");

  const [perPage, setPerPage] =
    useState(25);

  const [page, setPage] =
    useState(1);

  /*
   * Por ahora la API devuelve todos los turnos.
   * La paginación la hacemos en React.
   */
  const filteredRows =
    useMemo(() => {
      const search = q
        .trim()
        .toLowerCase();

      if (!search) {
        return rows;
      }

      return rows.filter((row) => {
        const nombre = String(
          row?.nombre || ""
        ).toLowerCase();

        const descripcion = String(
          row?.descripcion || ""
        ).toLowerCase();

        return (
          nombre.includes(search) ||
          descripcion.includes(search)
        );
      });
    }, [rows, q]);

  const total =
    filteredRows.length;

  const lastPage = Math.max(
    1,
    Math.ceil(
      total / perPage
    )
  );

  const currentPage = Math.min(
    page,
    lastPage
  );

  const visibleRows =
    useMemo(() => {
      const start =
        (currentPage - 1) *
        perPage;

      return filteredRows.slice(
        start,
        start + perPage
      );
    }, [
      filteredRows,
      currentPage,
      perPage,
    ]);

  const canPrev =
    currentPage > 1;

  const canNext =
    currentPage < lastPage;

  const searchRef =
    useRef(null);

  const searchWasFocusedRef =
    useRef(false);

  const rememberFocus = () => {
    searchWasFocusedRef.current =
      true;
  };

  /*
  |--------------------------------------------------------------------------
  | Formulario
  |--------------------------------------------------------------------------
  */

  const [openForm, setOpenForm] =
    useState(false);

  const [formMode, setFormMode] =
    useState("create");

  const [editingId, setEditingId] =
    useState(null);

  const [form, setForm] =
    useState({
      ...EMPTY_FORM,
    });

  const [saving, setSaving] =
    useState(false);

  const [
    deletingId,
    setDeletingId,
  ] = useState(null);

  const [
    fieldErrors,
    setFieldErrors,
  ] = useState({});

  /*
  |--------------------------------------------------------------------------
  | Carga
  |--------------------------------------------------------------------------
  */

  const load = async () => {
    setErr("");
    setLoading(true);

    try {
      const data =
        await apiGet(
          "/admin/turnos"
        );

      setRows(
        Array.isArray(
          data?.turnos
        )
          ? data.turnos
          : []
      );
    } catch (e) {
      setErr(
        e?.message ||
          "Error cargando turnos."
      );
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  useEffect(() => {
    const timer =
      setTimeout(() => {
        setPage(1);
        setQ(qDraft);
      }, 350);

    return () =>
      clearTimeout(timer);
  }, [qDraft]);

  useEffect(() => {
    if (
      page > lastPage
    ) {
      setPage(lastPage);
    }
  }, [
    page,
    lastPage,
  ]);

  /*
  |--------------------------------------------------------------------------
  | Formulario
  |--------------------------------------------------------------------------
  */

  const resetForm = () => {
    setEditingId(null);

    setForm({
      ...EMPTY_FORM,
    });

    setFieldErrors({});
    setErr("");
  };

  const openCreate = () => {
    if (!canCreate) {
      setErr(
        "No tienes permiso para crear turnos."
      );
      return;
    }

    resetForm();

    setFormMode(
      "create"
    );

    setOpenForm(true);
  };

  const normalizeTimeForInput = (
    value
  ) => {
    if (!value) return "";

    return String(value).slice(
      0,
      5
    );
  };

  const normalizeDateForInput = (
    value
  ) => {
    if (!value) return "";

    return String(value).slice(
      0,
      10
    );
  };

  const openEdit = (row) => {
    if (!canEdit) {
      setErr(
        "No tienes permiso para editar turnos."
      );
      return;
    }

    resetForm();

    setFormMode(
      "edit"
    );

    setEditingId(row.id);

    setForm({
      nombre:
        row.nombre || "",

      descripcion:
        row.descripcion || "",

      hora_inicio:
        normalizeTimeForInput(
          row.hora_inicio
        ),

      hora_fin:
        normalizeTimeForInput(
          row.hora_fin
        ),

      horas_jornada:
        row.horas_jornada ??
        "8.00",

      minutos_comida:
        row.minutos_comida ??
        0,

      comida_cuenta_jornada:
        !!row.comida_cuenta_jornada,

      lunes: !!row.lunes,
      martes: !!row.martes,
      miercoles:
        !!row.miercoles,
      jueves: !!row.jueves,
      viernes:
        !!row.viernes,
      sabado: !!row.sabado,
      domingo:
        !!row.domingo,

      vigente_desde:
        normalizeDateForInput(
          row.vigente_desde
        ),

      vigente_hasta:
        normalizeDateForInput(
          row.vigente_hasta
        ),

      activo: !!row.activo,
    });

    setOpenForm(true);
  };

  const closeModal = () => {
    setOpenForm(false);
    setSaving(false);
    setErr("");
    setFieldErrors({});
  };

  const setField = (
    field,
    value
  ) => {
    setForm((prev) => ({
      ...prev,
      [field]: value,
    }));

    setFieldErrors(
      (prev) => ({
        ...prev,
        [field]: "",
        dias: "",
      })
    );
  };

  /*
  |--------------------------------------------------------------------------
  | Validación frontend
  |--------------------------------------------------------------------------
  */

  const validateForm = () => {
    const errors = {};

    if (
      !form.nombre.trim()
    ) {
      errors.nombre =
        "El nombre del turno es obligatorio.";
    }

    if (!form.hora_inicio) {
      errors.hora_inicio =
        "La hora de inicio es obligatoria.";
    }

    if (!form.hora_fin) {
      errors.hora_fin =
        "La hora final es obligatoria.";
    }

    const horas = Number(
      form.horas_jornada
    );

    if (
      !Number.isFinite(horas) ||
      horas <= 0 ||
      horas > 24
    ) {
      errors.horas_jornada =
        "Indica una jornada válida.";
    }

    const minutos = Number(
      form.minutos_comida
    );

    if (
      !Number.isInteger(
        minutos
      ) ||
      minutos < 0 ||
      minutos > 600
    ) {
      errors.minutos_comida =
        "Indica minutos de comida válidos.";
    }

    if (
      !form.vigente_desde
    ) {
      errors.vigente_desde =
        "La fecha de inicio de vigencia es obligatoria.";
    }

    if (
      form.vigente_hasta &&
      form.vigente_desde &&
      form.vigente_hasta <
        form.vigente_desde
    ) {
      errors.vigente_hasta =
        "La vigencia final no puede ser anterior a la inicial.";
    }

    const hasDay =
      DAYS.some(
        ([key]) =>
          !!form[key]
      );

    if (!hasDay) {
      errors.dias =
        "Selecciona al menos un día laborable.";
    }

    setFieldErrors(
      errors
    );

    if (
      Object.keys(errors)
        .length > 0
    ) {
      setErr(
        Object.values(
          errors
        )[0]
      );

      return false;
    }

    return true;
  };

  /*
  |--------------------------------------------------------------------------
  | Guardar
  |--------------------------------------------------------------------------
  */

  const submit = async (e) => {
    e.preventDefault();

    setErr("");

    if (
      formMode === "create" &&
      !canCreate
    ) {
      setErr(
        "No tienes permiso para crear turnos."
      );
      return;
    }

    if (
      formMode === "edit" &&
      !canEdit
    ) {
      setErr(
        "No tienes permiso para editar turnos."
      );
      return;
    }

    if (!validateForm()) {
      return;
    }

    setSaving(true);

    try {
      const payload = {
        nombre:
          form.nombre.trim(),

        descripcion:
          form.descripcion.trim() ||
          null,

        hora_inicio:
          form.hora_inicio,

        hora_fin:
          form.hora_fin,

        horas_jornada:
          Number(
            form.horas_jornada
          ),

        minutos_comida:
          Number(
            form.minutos_comida
          ),

        comida_cuenta_jornada:
          !!form.comida_cuenta_jornada,

        lunes:
          !!form.lunes,

        martes:
          !!form.martes,

        miercoles:
          !!form.miercoles,

        jueves:
          !!form.jueves,

        viernes:
          !!form.viernes,

        sabado:
          !!form.sabado,

        domingo:
          !!form.domingo,

        vigente_desde:
          form.vigente_desde,

        vigente_hasta:
          form.vigente_hasta ||
          null,

        activo:
          !!form.activo,
      };

      if (
        formMode ===
        "create"
      ) {
        await apiPost(
          "/admin/turnos",
          payload
        );

        showToast(
          "success",
          "✅ Turno creado correctamente"
        );
      } else {
        await apiPut(
          `/admin/turnos/${editingId}`,
          payload
        );

        showToast(
          "info",
          "✏️ Turno actualizado correctamente"
        );
      }

      closeModal();
      await load();
    } catch (e2) {
      setErr(
        e2?.message ||
          "Error guardando turno."
      );
    } finally {
      setSaving(false);
    }
  };

  /*
  |--------------------------------------------------------------------------
  | Eliminar
  |--------------------------------------------------------------------------
  */

  const remove = async (
    row
  ) => {
    if (!canDelete) {
      setErr(
        "No tienes permiso para eliminar turnos."
      );
      return;
    }

    const ok =
      window.confirm(
        `¿Eliminar el turno "${row.nombre}"?`
      );

    if (!ok) return;

    setErr("");
    setDeletingId(
      row.id
    );

    try {
      await apiDelete(
        `/admin/turnos/${row.id}`
      );

      showToast(
        "danger",
        "🗑️ Turno eliminado"
      );

      await load();
    } catch (e) {
      setErr(
        e?.message ||
          "Error eliminando turno."
      );
    } finally {
      setDeletingId(null);
    }
  };

  /*
  |--------------------------------------------------------------------------
  | Formatos
  |--------------------------------------------------------------------------
  */

  const formatTime = (
    value
  ) => {
    if (!value) return "—";

    return String(value).slice(
      0,
      5
    );
  };

  const formatDate = (
    value
  ) => {
    if (!value) return "—";

    const raw =
      String(value).slice(
        0,
        10
      );

    const parts =
      raw.split("-");

    if (
      parts.length !== 3
    ) {
      return raw;
    }

    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  };

  const getDays = (
    row
  ) => {
    return DAYS.filter(
      ([key]) => !!row[key]
    ).map(
      ([, label]) => label
    );
  };

  /*
  |--------------------------------------------------------------------------
  | Componentes visuales
  |--------------------------------------------------------------------------
  */

  const Card = ({
    children,
    style,
  }) => (
    <div
      style={{
        background: "#fff",
        border:
          "1px solid #e2e8f0",
        borderRadius: 18,
        padding: 16,
        boxShadow:
          "0 8px 24px rgba(15, 23, 42, 0.05)",
        ...style,
      }}
    >
      {children}
    </div>
  );

  const Btn = ({
    children,
    style,
    variant = "default",
    ...props
  }) => {
    const variants = {
      default: {
        border: "#cbd5e1",
        bg: "#fff",
        fg: "#0f172a",
      },

      primary: {
        border: "#bfdbfe",
        bg: "#eff6ff",
        fg: "#1d4ed8",
      },

      danger: {
        border: "#fecaca",
        bg: "#fef2f2",
        fg: "#b91c1c",
      },
    };

    const v =
      variants[variant] ||
      variants.default;

    return (
      <button
        {...props}
        style={{
          borderRadius: 12,
          border:
            `1px solid ${v.border}`,
          background: v.bg,
          color: v.fg,
          padding: "10px 14px",
          cursor:
            props.disabled
              ? "not-allowed"
              : "pointer",
          fontWeight: 800,
          opacity:
            props.disabled
              ? 0.7
              : 1,
          display:
            "inline-flex",
          alignItems:
            "center",
          justifyContent:
            "center",
          gap: 8,
          ...style,
        }}
      >
        {children}
      </button>
    );
  };

  const IconBtn = ({
    children,
    variant = "default",
    title,
    style,
    ...props
  }) => {
    const variants = {
      default: {
        border: "#e2e8f0",
        bg: "#fff",
        fg: "#0f172a",
      },

      primary: {
        border: "#bfdbfe",
        bg: "#eff6ff",
        fg: "#1d4ed8",
      },

      danger: {
        border: "#fecaca",
        bg: "#fef2f2",
        fg: "#b91c1c",
      },
    };

    const v =
      variants[variant] ||
      variants.default;

    return (
      <button
        {...props}
        title={title}
        aria-label={title}
        style={{
          width: 38,
          height: 38,
          borderRadius: 12,
          border:
            `1px solid ${v.border}`,
          background: v.bg,
          color: v.fg,
          display: "grid",
          placeItems: "center",
          cursor:
            props.disabled
              ? "not-allowed"
              : "pointer",
          opacity:
            props.disabled
              ? 0.7
              : 1,
          ...style,
        }}
      >
        {children}
      </button>
    );
  };

  const Badge = ({
    children,
    active = true,
    tone = null,
  }) => {
    let border =
      active
        ? "#86efac"
        : "#fecaca";

    let background =
      active
        ? "#ecfdf5"
        : "#fef2f2";

    let color =
      active
        ? "#166534"
        : "#b91c1c";

    if (tone === "default") {
      border = "#e2e8f0";
      background = "#f8fafc";
      color = "#0f172a";
    }

    if (tone === "info") {
      border = "#93c5fd";
      background = "#eff6ff";
      color = "#1e40af";
    }

    return (
      <span
        style={{
          display:
            "inline-flex",
          alignItems:
            "center",
          justifyContent:
            "center",
          padding:
            "6px 10px",
          borderRadius: 999,
          border:
            `1px solid ${border}`,
          background,
          fontSize: 12,
          fontWeight: 800,
          color,
          textAlign: "center",
        }}
      >
        {children}
      </span>
    );
  };

  /*
  |--------------------------------------------------------------------------
  | Estilos
  |--------------------------------------------------------------------------
  */

  const toastStyle =
    (() => {
      if (!toast) {
        return {};
      }

      const map = {
        success: {
          bg: "#ecfdf5",
          border: "#86efac",
          fg: "#166534",
        },

        info: {
          bg: "#eff6ff",
          border: "#93c5fd",
          fg: "#1e40af",
        },

        danger: {
          bg: "#fef2f2",
          border: "#fecaca",
          fg: "#b91c1c",
        },
      };

      return (
        map[toast.type] ||
        map.info
      );
    })();

  const S = {
    page: {
      display: "flex",
      flexDirection:
        "column",
      gap: 14,
    },

    headerTop: {
      display: "flex",
      justifyContent:
        "space-between",
      gap: 12,
      flexWrap: "wrap",
      alignItems: "center",
    },

    titleBlock: {
      display: "flex",
      flexDirection:
        "column",
      gap: 4,
    },

    filterRow: {
      display: "grid",
      gridTemplateColumns:
        "minmax(220px, 1fr) auto auto",
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
      padding:
        "11px 12px",
      borderRadius: 12,
      border:
        "1px solid #dbeafe",
      background: "#f8fafc",
      outline: "none",
      minHeight: 44,
      boxSizing:
        "border-box",
    },

    select: {
      width: "100%",
      padding:
        "11px 12px",
      borderRadius: 12,
      border:
        "1px solid #dbeafe",
      background: "#f8fafc",
      outline: "none",
      minHeight: 44,
    },

    helper: {
      fontSize: 12,
      color: "#64748b",
      fontWeight: 700,
      marginTop: 5,
    },

    tableWrap: {
      width: "100%",
      overflowX: "auto",
      border:
        "1px solid #e2e8f0",
      borderRadius: 16,
    },

    table: {
      width: "100%",
      minWidth:
        canShowActionsColumn
          ? 1250
          : 1150,
      borderCollapse:
        "separate",
      borderSpacing: 0,
      background: "#fff",
    },

    th: {
      textAlign:
        "center",
      fontSize: 12,
      color: "#475569",
      padding:
        "14px 10px",
      borderBottom:
        "1px solid #e2e8f0",
      background: "#f8fafc",
      fontWeight: 800,
      verticalAlign:
        "middle",
    },

    td: {
      padding:
        "14px 10px",
      borderBottom:
        "1px solid #f1f5f9",
      verticalAlign:
        "middle",
      fontSize: 13,
      color: "#0f172a",
      textAlign: "center",
    },

    pagination: {
      display: "flex",
      gap: 10,
      alignItems: "center",
      marginTop: 14,
      flexWrap: "wrap",
      justifyContent:
        "space-between",
    },

    modalOverlay: {
      position: "fixed",
      inset: 0,
      background:
        "rgba(2, 6, 23, 0.55)",
      display: "flex",
      alignItems: "center",
      justifyContent:
        "center",
      padding: 14,
      zIndex: 100,
    },

    modal: {
      width: "100%",
      maxWidth: 900,
      maxHeight: "90vh",
      overflowY: "auto",
      background: "#fff",
      borderRadius: 18,
      border:
        "1px solid #e2e8f0",
      boxShadow:
        "0 25px 60px rgba(0,0,0,.18)",
    },

    modalHeader: {
      padding:
        "16px 18px",
      borderBottom:
        "1px solid #e2e8f0",
      display: "flex",
      alignItems: "center",
      justifyContent:
        "space-between",
      gap: 12,
      position: "sticky",
      top: 0,
      background: "#fff",
      zIndex: 2,
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
      flexDirection:
        "column",
      gap: 18,
    },

    modalFooter: {
      padding: 18,
      borderTop:
        "1px solid #e2e8f0",
      display: "flex",
      gap: 10,
      justifyContent:
        "flex-end",
      flexWrap: "wrap",
      position: "sticky",
      bottom: 0,
      background: "#fff",
    },

    formGrid: {
      display: "grid",
      gridTemplateColumns:
        "1fr 1fr",
      gap: 14,
    },

    fieldWrap: {
      display: "flex",
      flexDirection:
        "column",
    },

    inputFull: {
      width: "100%",
      padding:
        "12px 13px",
      borderRadius: 12,
      border:
        "1px solid #dbeafe",
      background: "#fff",
      outline: "none",
      minHeight: 46,
      boxSizing:
        "border-box",
    },

    errorText: {
      color: "#b91c1c",
      fontSize: 12,
      fontWeight: 700,
      marginTop: 6,
    },

    xBtn: {
      border:
        "1px solid #e2e8f0",
      background: "#fff",
      borderRadius: 10,
      width: 38,
      height: 38,
      display: "grid",
      placeItems: "center",
      cursor: "pointer",
      fontWeight: 900,
    },

    sectionTitle: {
      fontSize: 13,
      fontWeight: 900,
      color: "#334155",
      borderBottom:
        "1px solid #e2e8f0",
      paddingBottom: 7,
    },

    daysGrid: {
      display: "grid",
      gridTemplateColumns:
        "repeat(7, minmax(90px, 1fr))",
      gap: 8,
    },

    dayButton: {
      minHeight: 42,
      borderRadius: 11,
      cursor: "pointer",
      fontWeight: 800,
      padding: "8px 6px",
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
      boxShadow:
        "0 1px 3px rgba(0,0,0,.22)",
    },

    responsiveStyleTag: `
      @media (max-width: 860px) {
        .turnos-filter-row {
          grid-template-columns: 1fr !important;
        }

        .turnos-form-grid {
          grid-template-columns: 1fr !important;
        }

        .turnos-days-grid {
          grid-template-columns: repeat(2, 1fr) !important;
        }

        .turnos-modal-full {
          max-width: 100% !important;
        }
      }

      @media (max-width: 560px) {
        .turnos-header-mobile {
          align-items: stretch !important;
        }

        .turnos-header-mobile button {
          width: 100%;
        }

        .turnos-pagination-mobile {
          justify-content: center !important;
        }

        .turnos-days-grid {
          grid-template-columns: 1fr 1fr !important;
        }
      }
    `,
  };

  /*
  |--------------------------------------------------------------------------
  | Render
  |--------------------------------------------------------------------------
  */

  return (
    <div style={S.page}>
      <style>
        {S.responsiveStyleTag}
      </style>

      <Card>
        <div
          style={S.headerTop}
          className="turnos-header-mobile"
        >
          <div
            style={S.titleBlock}
          >
            <h2
              style={{
                margin: 0,
                fontSize: 24,
                color: "#0f172a",
              }}
            >
              Control de acceso
            </h2>

            <div
              style={{
                fontSize: 13,
                color: "#64748b",
              }}
            >
              Administra turnos,
              jornadas y días
              laborables.
            </div>

            <div
              style={{
                fontSize: 12,
                color: "#64748b",
              }}
            >
              Turnos registrados:{" "}
              <b>{rows.length}</b>
            </div>
          </div>

          {canCreate ? (
            <Btn
              variant="primary"
              onClick={
                openCreate
              }
            >
              <i className="fa-solid fa-plus" />
              Nuevo turno
            </Btn>
          ) : null}
        </div>

        <div
          style={S.filterRow}
          className="turnos-filter-row"
        >
          <div>
            <div
              style={S.label}
            >
              Buscar turno
            </div>

            <input
              ref={searchRef}
              value={qDraft}
              onFocus={
                rememberFocus
              }
              onChange={(e) => {
                rememberFocus();

                setQDraft(
                  e.target.value
                );
              }}
              placeholder="Buscar por nombre o descripción"
              style={S.input}
            />
          </div>

          <div>
            <div
              style={S.label}
            >
              Por página
            </div>

            <select
              value={perPage}
              onChange={(e) => {
                setPage(1);

                setPerPage(
                  Number(
                    e.target.value
                  )
                );
              }}
              style={S.select}
            >
              <option value={5}>
                5 registros
              </option>

              <option value={10}>
                10 registros
              </option>

              <option value={20}>
                20 registros
              </option>

              <option value={25}>
                25 registros
              </option>

              <option value={50}>
                50 registros
              </option>

              <option value={100}>
                100 registros
              </option>
            </select>
          </div>

          <div>
            <div
              style={S.label}
            >
              Página actual
            </div>

            <div
              style={{
                ...S.input,
                display: "flex",
                alignItems:
                  "center",
                justifyContent:
                  "center",
                background: "#fff",
                color: "#334155",
                fontWeight: 700,
              }}
            >
              {currentPage} de{" "}
              {lastPage}
            </div>
          </div>
        </div>

        {toast ? (
          <div
            style={{
              marginTop: 14,
              padding:
                "10px 12px",
              borderRadius: 12,
              border:
                `1px solid ${toastStyle.border}`,
              background:
                toastStyle.bg,
              color:
                toastStyle.fg,
              fontWeight: 800,
            }}
          >
            {toast.text}
          </div>
        ) : null}

        {err ? (
          <div
            style={{
              marginTop: 12,
              color: "#b91c1c",
              fontWeight: 800,
            }}
          >
            {err}
          </div>
        ) : null}
      </Card>

      <Card>
        {loading ? (
          <div
            style={{
              color: "#475569",
              fontWeight: 700,
            }}
          >
            Cargando turnos...
          </div>
        ) : (
          <>
            <div
              style={S.tableWrap}
            >
              <table
                style={S.table}
              >
                <thead>
                  <tr>
                    <th
                      style={S.th}
                    >
                      Turno
                    </th>

                    <th
                      style={S.th}
                    >
                      Horario
                    </th>

                    <th
                      style={S.th}
                    >
                      Jornada
                    </th>

                    <th
                      style={S.th}
                    >
                      Comida
                    </th>

                    <th
                      style={S.th}
                    >
                      Días
                    </th>

                    <th
                      style={S.th}
                    >
                      Vigencia
                    </th>

                    <th
                      style={S.th}
                    >
                      Estado
                    </th>

                    {canShowActionsColumn ? (
                      <th
                        style={{
                          ...S.th,
                          width: 130,
                        }}
                      >
                        Acciones
                      </th>
                    ) : null}
                  </tr>
                </thead>

                <tbody>
                  {visibleRows.length ? (
                    visibleRows.map(
                      (row) => {
                        const days =
                          getDays(
                            row
                          );

                        return (
                          <tr
                            key={
                              row.id
                            }
                          >
                            <td
                              style={
                                S.td
                              }
                            >
                              <div
                                style={{
                                  fontWeight: 900,
                                }}
                              >
                                {
                                  row.nombre
                                }
                              </div>

                              {row.descripcion ? (
                                <div
                                  style={{
                                    marginTop: 3,
                                    fontSize: 11,
                                    color: "#64748b",
                                  }}
                                >
                                  {
                                    row.descripcion
                                  }
                                </div>
                              ) : null}
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <b>
                                {formatTime(
                                  row.hora_inicio
                                )}
                              </b>

                              {" - "}

                              <b>
                                {formatTime(
                                  row.hora_fin
                                )}
                              </b>
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <Badge tone="info">
                                {
                                  Number(
                                    row.horas_jornada
                                  ).toFixed(
                                    2
                                  )
                                }{" "}
                                h
                              </Badge>
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <div>
                                {
                                  row.minutos_comida
                                }{" "}
                                min
                              </div>

                              <div
                                style={{
                                  marginTop: 4,
                                  fontSize: 11,
                                  color: "#64748b",
                                }}
                              >
                                {row.comida_cuenta_jornada
                                  ? "Cuenta en jornada"
                                  : "Se descuenta"}
                              </div>
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <div
                                style={{
                                  display:
                                    "flex",
                                  gap: 4,
                                  flexWrap:
                                    "wrap",
                                  justifyContent:
                                    "center",
                                }}
                              >
                                {days.map(
                                  (
                                    day
                                  ) => (
                                    <Badge
                                      key={
                                        day
                                      }
                                      tone="default"
                                    >
                                      {
                                        day
                                      }
                                    </Badge>
                                  )
                                )}
                              </div>
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <div>
                                {formatDate(
                                  row.vigente_desde
                                )}
                              </div>

                              <div
                                style={{
                                  marginTop: 3,
                                  fontSize: 11,
                                  color: "#64748b",
                                }}
                              >
                                hasta{" "}
                                {formatDate(
                                  row.vigente_hasta
                                )}
                              </div>
                            </td>

                            <td
                              style={
                                S.td
                              }
                            >
                              <Badge
                                active={
                                  !!row.activo
                                }
                              >
                                {row.activo
                                  ? "Activo"
                                  : "Inactivo"}
                              </Badge>
                            </td>

                            {canShowActionsColumn ? (
                              <td
                                style={
                                  S.td
                                }
                              >
                                <div
                                  style={{
                                    display:
                                      "inline-flex",
                                    gap: 8,
                                    justifyContent:
                                      "center",
                                  }}
                                >
                                  {canEdit ? (
                                    <IconBtn
                                      onClick={() =>
                                        openEdit(
                                          row
                                        )
                                      }
                                      title="Editar"
                                      variant="primary"
                                    >
                                      <i className="fa-solid fa-pen" />
                                    </IconBtn>
                                  ) : null}

                                  {canDelete ? (
                                    <IconBtn
                                      disabled={
                                        deletingId ===
                                        row.id
                                      }
                                      onClick={() =>
                                        remove(
                                          row
                                        )
                                      }
                                      variant="danger"
                                      title="Eliminar"
                                    >
                                      <i className="fa-solid fa-trash" />
                                    </IconBtn>
                                  ) : null}
                                </div>
                              </td>
                            ) : null}
                          </tr>
                        );
                      }
                    )
                  ) : (
                    <tr>
                      <td
                        style={
                          S.td
                        }
                        colSpan={
                          7 +
                          (canShowActionsColumn
                            ? 1
                            : 0)
                        }
                      >
                        Sin turnos
                        registrados.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div
              style={S.pagination}
              className="turnos-pagination-mobile"
            >
              <Btn
                disabled={
                  !canPrev
                }
                onClick={() =>
                  setPage(
                    (p) =>
                      Math.max(
                        1,
                        p - 1
                      )
                  )
                }
              >
                Anterior
              </Btn>

              <div
                style={{
                  fontSize: 13,
                  color: "#475569",
                  fontWeight: 700,
                }}
              >
                Mostrando página{" "}
                <b>
                  {currentPage}
                </b>{" "}
                de{" "}
                <b>
                  {lastPage}
                </b>
              </div>

              <Btn
                disabled={
                  !canNext
                }
                onClick={() =>
                  setPage(
                    (p) =>
                      Math.min(
                        lastPage,
                        p + 1
                      )
                  )
                }
              >
                Siguiente
              </Btn>
            </div>
          </>
        )}
      </Card>

      {openForm ? (
        <div
          style={
            S.modalOverlay
          }
          onClick={
            closeModal
          }
        >
          <div
            style={S.modal}
            className="turnos-modal-full"
            onClick={(e) =>
              e.stopPropagation()
            }
          >
            <div
              style={
                S.modalHeader
              }
            >
              <h3
                style={
                  S.modalTitle
                }
              >
                {formMode ===
                "create"
                  ? "Crear turno"
                  : "Editar turno"}
              </h3>

              <button
                type="button"
                style={S.xBtn}
                onClick={
                  closeModal
                }
                aria-label="Cerrar"
              >
                ✕
              </button>
            </div>

            <form
              onSubmit={
                submit
              }
            >
              <div
                style={
                  S.modalBody
                }
              >
                <div
                  style={
                    S.sectionTitle
                  }
                >
                  Datos generales
                </div>

                <div
                  style={
                    S.formGrid
                  }
                  className="turnos-form-grid"
                >
                  <div
                    style={{
                      ...S.fieldWrap,
                      gridColumn:
                        "1 / -1",
                    }}
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Nombre del
                      turno
                    </div>

                    <input
                      value={
                        form.nombre
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "nombre",
                          e.target
                            .value
                        )
                      }
                      style={{
                        ...S.inputFull,
                        borderColor:
                          fieldErrors.nombre
                            ? "#fecaca"
                            : "#dbeafe",
                      }}
                      placeholder="Ej. Diurno"
                    />

                    {fieldErrors.nombre ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.nombre
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={{
                      ...S.fieldWrap,
                      gridColumn:
                        "1 / -1",
                    }}
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Descripción
                    </div>

                    <input
                      value={
                        form.descripcion
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "descripcion",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                      placeholder="Ej. Turno operativo diurno"
                    />

                    <div
                      style={
                        S.helper
                      }
                    >
                      Opcional
                    </div>
                  </div>
                </div>

                <div
                  style={
                    S.sectionTitle
                  }
                >
                  Horario y
                  jornada
                </div>

                <div
                  style={
                    S.formGrid
                  }
                  className="turnos-form-grid"
                >
                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Hora inicio
                    </div>

                    <input
                      type="time"
                      value={
                        form.hora_inicio
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "hora_inicio",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    {fieldErrors.hora_inicio ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.hora_inicio
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Hora final
                    </div>

                    <input
                      type="time"
                      value={
                        form.hora_fin
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "hora_fin",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    {fieldErrors.hora_fin ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.hora_fin
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Horas de
                      jornada
                    </div>

                    <input
                      type="number"
                      min="0.25"
                      max="24"
                      step="0.25"
                      value={
                        form.horas_jornada
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "horas_jornada",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    <div
                      style={
                        S.helper
                      }
                    >
                      Ej. 8, 7.5,
                      7
                    </div>

                    {fieldErrors.horas_jornada ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.horas_jornada
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Minutos de
                      comida
                    </div>

                    <input
                      type="number"
                      min="0"
                      max="600"
                      step="1"
                      value={
                        form.minutos_comida
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "minutos_comida",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    {fieldErrors.minutos_comida ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.minutos_comida
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={{
                      ...S.fieldWrap,
                      gridColumn:
                        "1 / -1",
                    }}
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Tiempo de
                      comida
                    </div>

                    <div
                      style={
                        S.toggleWrap
                      }
                    >
                      <button
                        type="button"
                        onClick={() =>
                          setField(
                            "comida_cuenta_jornada",
                            !form.comida_cuenta_jornada
                          )
                        }
                        aria-pressed={
                          form.comida_cuenta_jornada
                        }
                        style={{
                          ...S.toggleButton,
                          background:
                            form.comida_cuenta_jornada
                              ? "#22c55e"
                              : "#cbd5e1",
                        }}
                      >
                        <span
                          style={{
                            ...S.toggleThumb,
                            left:
                              form.comida_cuenta_jornada
                                ? 30
                                : 4,
                          }}
                        />
                      </button>

                      <span
                        style={{
                          fontSize: 14,
                          fontWeight: 800,
                          color:
                            form.comida_cuenta_jornada
                              ? "#166534"
                              : "#64748b",
                        }}
                      >
                        {form.comida_cuenta_jornada
                          ? "Cuenta dentro de la jornada"
                          : "Se descuenta de la jornada"}
                      </span>
                    </div>
                  </div>
                </div>

                <div
                  style={
                    S.sectionTitle
                  }
                >
                  Días
                  laborables
                </div>

                <div
                  style={
                    S.daysGrid
                  }
                  className="turnos-days-grid"
                >
                  {DAYS.map(
                    ([
                      key,
                      label,
                    ]) => {
                      const selected =
                        !!form[
                          key
                        ];

                      return (
                        <button
                          key={
                            key
                          }
                          type="button"
                          onClick={() =>
                            setField(
                              key,
                              !selected
                            )
                          }
                          style={{
                            ...S.dayButton,
                            border:
                              selected
                                ? "1px solid #86efac"
                                : "1px solid #cbd5e1",
                            background:
                              selected
                                ? "#ecfdf5"
                                : "#fff",
                            color:
                              selected
                                ? "#166534"
                                : "#64748b",
                          }}
                        >
                          {selected
                            ? "✓ "
                            : ""}
                          {label}
                        </button>
                      );
                    }
                  )}
                </div>

                {fieldErrors.dias ? (
                  <div
                    style={
                      S.errorText
                    }
                  >
                    {
                      fieldErrors.dias
                    }
                  </div>
                ) : null}

                <div
                  style={
                    S.sectionTitle
                  }
                >
                  Vigencia
                </div>

                <div
                  style={
                    S.formGrid
                  }
                  className="turnos-form-grid"
                >
                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Vigente desde
                    </div>

                    <input
                      type="date"
                      value={
                        form.vigente_desde
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "vigente_desde",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    {fieldErrors.vigente_desde ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.vigente_desde
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={
                      S.fieldWrap
                    }
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Vigente hasta
                    </div>

                    <input
                      type="date"
                      value={
                        form.vigente_hasta
                      }
                      onChange={(
                        e
                      ) =>
                        setField(
                          "vigente_hasta",
                          e.target
                            .value
                        )
                      }
                      style={
                        S.inputFull
                      }
                    />

                    <div
                      style={
                        S.helper
                      }
                    >
                      Déjalo vacío
                      si no tiene una
                      fecha final.
                    </div>

                    {fieldErrors.vigente_hasta ? (
                      <div
                        style={
                          S.errorText
                        }
                      >
                        {
                          fieldErrors.vigente_hasta
                        }
                      </div>
                    ) : null}
                  </div>

                  <div
                    style={{
                      ...S.fieldWrap,
                      gridColumn:
                        "1 / -1",
                    }}
                  >
                    <div
                      style={
                        S.label
                      }
                    >
                      Estado
                    </div>

                    <div
                      style={
                        S.toggleWrap
                      }
                    >
                      <button
                        type="button"
                        onClick={() =>
                          setField(
                            "activo",
                            !form.activo
                          )
                        }
                        aria-pressed={
                          form.activo
                        }
                        style={{
                          ...S.toggleButton,
                          background:
                            form.activo
                              ? "#22c55e"
                              : "#cbd5e1",
                        }}
                      >
                        <span
                          style={{
                            ...S.toggleThumb,
                            left:
                              form.activo
                                ? 30
                                : 4,
                          }}
                        />
                      </button>

                      <span
                        style={{
                          fontSize: 14,
                          fontWeight: 800,
                          color:
                            form.activo
                              ? "#166534"
                              : "#64748b",
                        }}
                      >
                        {form.activo
                          ? "Activo"
                          : "Inactivo"}
                      </span>
                    </div>
                  </div>
                </div>

                {err ? (
                  <div
                    style={{
                      color:
                        "#b91c1c",
                      fontWeight: 800,
                    }}
                  >
                    {err}
                  </div>
                ) : null}
              </div>

              <div
                style={
                  S.modalFooter
                }
              >
                <Btn
                  type="button"
                  onClick={
                    closeModal
                  }
                >
                  Cancelar
                </Btn>

                <Btn
                  type="submit"
                  disabled={
                    saving
                  }
                  variant="primary"
                >
                  {saving
                    ? "Guardando..."
                    : "Guardar"}
                </Btn>
              </div>
            </form>
          </div>
        </div>
      ) : null}
    </div>
  );
}