import React, {
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";

import {
  apiGet,
  apiPost,
} from "../../services/api";

const TIME_ZONE = "America/Mexico_City";
const CURRENT_USER_UPDATED_EVENT =
  "current-user-updated";

function getStoredUser() {
  try {
    return JSON.parse(
      localStorage.getItem("user") || "null"
    );
  } catch {
    return null;
  }
}

function normalizeRoles(user) {
  if (!user) return [];

  const rolesFromArray = Array.isArray(user.roles)
    ? user.roles
        .map((r) =>
          typeof r === "string" ? r : r?.name
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
}

function normalizePermissions(user) {
  if (!user) return [];

  return [
    ...new Set(
      Array.isArray(user.permissions)
        ? user.permissions
            .map((p) =>
              typeof p === "string"
                ? p
                : p?.name
            )
            .filter(Boolean)
        : []
    ),
  ];
}

function isAdmin(user) {
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
  if (isAdmin(user)) {
    return true;
  }

  return normalizePermissions(
    user
  ).includes(permission);
}

function formatDate(value) {
  if (!value) return "—";

  const date = new Date(
    `${String(value).slice(0, 10)}T12:00:00`
  );

  return new Intl.DateTimeFormat(
    "es-MX",
    {
      timeZone: TIME_ZONE,
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    }
  ).format(date);
}

function getTodayDateKey() {
  const parts =
    new Intl.DateTimeFormat(
      "en-US",
      {
        timeZone: TIME_ZONE,
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
      }
    ).formatToParts(
      new Date()
    );

  const year =
    parts.find(
      (part) =>
        part.type === "year"
    )?.value;

  const month =
    parts.find(
      (part) =>
        part.type === "month"
    )?.value;

  const day =
    parts.find(
      (part) =>
        part.type === "day"
    )?.value;

  return `${year}-${month}-${day}`;
}

function getIncidenciaLabel(tipo) {
  const labels = {
    vacaciones: "Vacaciones",
    incapacidad: "Incapacidad",
    festivo: "Día festivo",
  };

  return labels[tipo] || "Incidencia";
}

function formatTime(value) {
  if (!value) return "—";

  const date = new Date(value);

  return new Intl.DateTimeFormat(
    "es-MX",
    {
      timeZone: TIME_ZONE,
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    }
  ).format(date);
}

function formatDateTime(value) {
  if (!value) return "—";

  const date = new Date(value);

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

function formatDuration(
  start,
  end = null
) {
  if (!start) {
    return "00:00:00";
  }

  const startMs =
    new Date(start).getTime();

  const endMs = end
    ? new Date(end).getTime()
    : Date.now();

  if (
    !Number.isFinite(startMs) ||
    !Number.isFinite(endMs)
  ) {
    return "00:00:00";
  }

  const totalSeconds = Math.max(
    0,
    Math.floor(
      (endMs - startMs) / 1000
    )
  );

  const hours = Math.floor(
    totalSeconds / 3600
  );

  const minutes = Math.floor(
    (totalSeconds % 3600) / 60
  );

  const seconds =
    totalSeconds % 60;

  return [
    String(hours).padStart(2, "0"),
    String(minutes).padStart(2, "0"),
    String(seconds).padStart(2, "0"),
  ].join(":");
}

function formatSeconds(value) {
  if (
    value === null ||
    value === undefined ||
    value === ""
  ) {
    return "—";
  }

  const totalSeconds =
    Number(value);

  if (
    !Number.isFinite(
      totalSeconds
    )
  ) {
    return "—";
  }

  const secondsSafe =
    Math.max(
      0,
      Math.floor(
        totalSeconds
      )
    );

  const hours =
    Math.floor(
      secondsSafe / 3600
    );

  const minutes =
    Math.floor(
      (secondsSafe % 3600)
        / 60
    );

  const seconds =
    secondsSafe % 60;

  return [
    String(hours).padStart(
      2,
      "0"
    ),
    String(minutes).padStart(
      2,
      "0"
    ),
    String(seconds).padStart(
      2,
      "0"
    ),
  ].join(":");
}

function formatEstadoJornada(
  value,
  tieneSalida = false
) {
  if (!value) {
    return tieneSalida
      ? "Sin cálculo"
      : "En curso";
  }

  const estados = {
    incompleta: "Incompleta",
    cumplida: "Cumplida",
    excedida: "Excedida",
  };

  return (
    estados[value] ||
    value
  );
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

function getEstadosAsistencia(item) {
  const tieneSalida =
    !!item?.hora_salida;

  const turnoTieneComida =
    Number(
      item?.turno?.minutos_comida ||
      0
    ) > 0;

  const comidaAbierta =
    !!item?.hora_inicio_comida &&
    !item?.hora_fin_comida;

  const dia = {
    value:
      item?.estado_dia === "laborable"
        ? "Laborable"
        : item?.estado_dia ===
            "fuera_dia_laborable"
          ? "Fuera de día laborable"
          : "Sin cálculo",

    tone:
      item?.estado_dia === "laborable"
        ? "success"
        : item?.estado_dia ===
            "fuera_dia_laborable"
          ? "warning"
          : "neutral",
  };

  const entrada = {
    value:
      item?.estado_entrada ===
      "dentro_horario"
        ? "Dentro de horario"
        : item?.estado_entrada ===
            "fuera_horario"
          ? "Fuera de horario"
          : "Sin cálculo",

    tone:
      item?.estado_entrada ===
      "dentro_horario"
        ? "success"
        : item?.estado_entrada ===
            "fuera_horario"
          ? "danger"
          : "neutral",
  };

  const salida = {
    value:
      !tieneSalida
        ? "En curso"
        : item?.estado_salida ===
            "dentro_horario"
          ? "Dentro de horario"
          : item?.estado_salida ===
              "fuera_horario"
            ? "Fuera de horario"
            : "Sin cálculo",

    tone:
      !tieneSalida
        ? "neutral"
        : item?.estado_salida ===
            "dentro_horario"
          ? "success"
          : item?.estado_salida ===
              "fuera_horario"
            ? "danger"
            : "neutral",
  };

  let comida = {
    value: "Sin cálculo",
    tone: "neutral",
  };

  if (!turnoTieneComida) {
    comida = {
      value: "No aplica",
      tone: "neutral",
    };
  } else if (comidaAbierta) {
    comida = {
      value: "En curso",
      tone: "neutral",
    };
  } else if (
    item?.estado_comida ===
    "completa"
  ) {
    comida = {
      value: "Completa",
      tone: "success",
    };
  } else if (
    item?.estado_comida ===
    "menor"
  ) {
    comida = {
      value: "Menor",
      tone: "warning",
    };
  } else if (
    item?.estado_comida ===
    "excedida"
  ) {
    comida = {
      value: "Excedida",
      tone: "danger",
    };
  } else if (
    !tieneSalida &&
    !item?.hora_inicio_comida
  ) {
    comida = {
      value: "Sin registrar",
      tone: "neutral",
    };
  }

  const jornada = {
    value:
      formatEstadoJornada(
        item?.estado_jornada,
        tieneSalida
      ),

    tone:
      !tieneSalida
        ? "neutral"
        : item?.estado_jornada ===
            "cumplida"
          ? "success"
          : item?.estado_jornada ===
                "incompleta" ||
              item?.estado_jornada ===
                "excedida"
            ? "warning"
            : "neutral",
  };

  return {
    dia,
    entrada,
    salida,
    comida,
    jornada,
  };
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

  const lat =
    Number(latitude);

  const lng =
    Number(longitude);

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
    `?bbox=${encodeURIComponent(
      bbox
    )}` +
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
        padding: "4px 7px",
        borderRadius: 999,
        background:
          selected.background,
        border: `1px solid ${selected.border}`,
        color: selected.color,
        fontSize: 11,
        fontWeight: 800,
        whiteSpace: "nowrap",
      }}
    >
      {label}: {value}
    </span>
  );
}

export default function Asistencias() {
  const [me, setMe] = useState(
    () => getStoredUser()
  );

  const [actual, setActual] =
    useState(null);
  
  const [
    incidenciaHoy,
    setIncidenciaHoy,
  ] = useState(null);
  
  const [registros, setRegistros] =
    useState([]);

  const [loading, setLoading] =
    useState(true);

  const [saving, setSaving] =
    useState(false);

  const [error, setError] =
    useState("");

  const [message, setMessage] =
    useState("");
  
  const [messageTone, setMessageTone] =
    useState("success");
  
  const [tick, setTick] =
    useState(Date.now());

  const [isOnline, setIsOnline] =
    useState(() =>
      typeof navigator === "undefined"
        ? true
        : navigator.onLine
    );

  const [
    detailItem,
    setDetailItem,
  ] = useState(null);

  const [
    mostrarMapaDetalle,
    setMostrarMapaDetalle,
  ] = useState(false);

  const [
    mostrarFotoDetalle,
    setMostrarFotoDetalle,
  ] = useState(false);

  const [
    mostrarFirmaDetalle,
    setMostrarFirmaDetalle,
  ] = useState(false);

  const [activeView, setActiveView] =
    useState("personal");
  
  const [kioscoUsuarios, setKioscoUsuarios] =
    useState([]);
  
  const [kioscoLoading, setKioscoLoading] =
    useState(false);
  
  const [kioscoSearch, setKioscoSearch] =
    useState("");
  
  const [
    kioscoSavingUserId,
    setKioscoSavingUserId,
  ] = useState(null);

  const [entradaModal, setEntradaModal] =
  useState(null);

  const [entradaSaving, setEntradaSaving] =
    useState(false);
  
  const [entradaError, setEntradaError] =
    useState("");
  
  const [entradaEstado, setEntradaEstado] =
    useState("");
  
  const [firmaRealizada, setFirmaRealizada] =
    useState(false);
  
  const [
    firmaCapturadaAt,
    setFirmaCapturadaAt,
  ] = useState("");
  
  const [
    fotoCapturadaAt,
    setFotoCapturadaAt,
  ] = useState("");
  
  const firmaCanvasRef =
    useRef(null);
  
  const firmaDrawingRef =
    useRef(false);
  
  const firmaLastPointRef =
    useRef({
      x: 0,
      y: 0,
    });

  const [
    fotoEntradaBase64,
    setFotoEntradaBase64,
  ] = useState("");
  
  const [
    camaraActiva,
    setCamaraActiva,
  ] = useState(false);
  
  const [
    camaraError,
    setCamaraError,
  ] = useState("");
  
  const camaraVideoRef =
    useRef(null);
  
  const camaraStreamRef =
    useRef(null);

  const canCreate = useMemo(
    () =>
      hasPermission(
        me,
        "asistencias.create"
      ),
    [me]
  );

  const canKiosco = useMemo(
    () =>
      hasPermission(
        me,
        "asistencias.kiosco"
      ),
    [me]
  );

  const fechaHoy =
    getTodayDateKey();
  
  const registroHoy =
    registros.find(
      (item) =>
        String(
          item?.fecha || ""
        ).slice(0, 10) ===
        fechaHoy
    ) || null;
  
  const yaRegistroEntradaHoy =
    !!registroHoy?.hora_entrada;

  const kioscoUsuariosFiltrados =
    useMemo(() => {
      const search = kioscoSearch
        .trim()
        .toLowerCase();
  
      if (!search) {
        return kioscoUsuarios;
      }
  
      return kioscoUsuarios.filter(
        (user) => {
          const values = [
            user?.name,
            user?.email,
            user?.unidad_laboral?.nombre,
            user?.turno?.nombre,
            user?.tipo_trabajo,
          ];
  
          return values.some((value) =>
            String(value || "")
              .toLowerCase()
              .includes(search)
          );
        }
      );
    }, [
      kioscoUsuarios,
      kioscoSearch,
    ]);

  useEffect(() => {
    const onOnline = () =>
      setIsOnline(true);

    const onOffline = () =>
      setIsOnline(false);

    window.addEventListener(
      "online",
      onOnline
    );

    window.addEventListener(
      "offline",
      onOffline
    );

    return () => {
      window.removeEventListener(
        "online",
        onOnline
      );

      window.removeEventListener(
        "offline",
        onOffline
      );
    };
  }, []);

  useEffect(() => {
    const handler = (event) => {
      if (event?.detail?.id) {
        setMe(event.detail);
      }
    };

    window.addEventListener(
      CURRENT_USER_UPDATED_EVENT,
      handler
    );

    return () => {
      window.removeEventListener(
        CURRENT_USER_UPDATED_EVENT,
        handler
      );
    };
  }, []);

  useEffect(() => {
    if (!actual) {
      return undefined;
    }

    const timer = window.setInterval(
      () => {
        setTick(Date.now());
      },
      1000
    );

    return () =>
      window.clearInterval(timer);
  }, [actual]);

  useEffect(() => {
    if (!message) {
      return undefined;
    }
  
    const timer =
      window.setTimeout(
        () => {
          setMessage("");
          setMessageTone("success");
        },
        5000
      );
  
    return () =>
      window.clearTimeout(timer);
  }, [message]);

  async function loadData() {
    if (!navigator.onLine) {
      setLoading(false);
      return;
    }

    setLoading(true);
    setError("");

    try {
      const data =
        await apiGet(
          "/asistencias"
        );

      setActual(
        data?.actual || null
      );

      setIncidenciaHoy(
        data?.incidencia_hoy || null
      );

      setRegistros(
        Array.isArray(
          data?.registros
        )
          ? data.registros
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

  async function loadKioscoData() {
    if (
      !navigator.onLine ||
      !canKiosco
    ) {
      return;
    }
  
    setKioscoLoading(true);
    setError("");
  
    try {
      const data =
        await apiGet(
          "/asistencias/kiosco"
        );
  
      setKioscoUsuarios(
        Array.isArray(
          data?.usuarios
        )
          ? data.usuarios
          : []
      );
    } catch (err) {
      setError(
        err?.message ||
          "No se pudieron cargar los usuarios de Kiosco."
      );
    } finally {
      setKioscoLoading(false);
    }
  }

  useEffect(() => {
    if (isOnline) {
      loadData();
    } else {
      setLoading(false);
    }
  }, [isOnline]);

  useEffect(() => {
    if (
      !canKiosco &&
      activeView === "kiosco"
    ) {
      setActiveView("personal");
    }
  }, [
    canKiosco,
    activeView,
  ]);
  
  useEffect(() => {
    if (
      isOnline &&
      canKiosco &&
      activeView === "kiosco"
    ) {
      loadKioscoData();
    }
  }, [
    isOnline,
    canKiosco,
    activeView,
  ]);

  useEffect(() => {
    if (!entradaModal) {
      return;
    }
  
    const frame =
      window.requestAnimationFrame(
        () => {
          const canvas =
            firmaCanvasRef.current;
  
          if (!canvas) {
            return;
          }
  
          const rect =
            canvas.getBoundingClientRect();
  
          const dpr =
            window.devicePixelRatio || 1;
  
          canvas.width =
            Math.max(
              1,
              Math.round(
                rect.width * dpr
              )
            );
  
          canvas.height =
            Math.max(
              1,
              Math.round(
                rect.height * dpr
              )
            );
  
          const ctx =
            canvas.getContext("2d");
  
          ctx.setTransform(
            dpr,
            0,
            0,
            dpr,
            0,
            0
          );
  
          ctx.fillStyle =
            "#ffffff";
  
          ctx.fillRect(
            0,
            0,
            rect.width,
            rect.height
          );
  
          ctx.strokeStyle =
            "#0f172a";
  
          ctx.fillStyle =
            "#0f172a";
  
          ctx.lineWidth = 2.5;
          ctx.lineCap = "round";
          ctx.lineJoin = "round";
        }
      );
  
    return () =>
      window.cancelAnimationFrame(
        frame
      );
  }, [entradaModal]);

  useEffect(() => {
    if (!camaraActiva) {
      return;
    }
  
    const video =
      camaraVideoRef.current;
  
    const stream =
      camaraStreamRef.current;
  
    if (
      !video ||
      !stream
    ) {
      return;
    }
  
    video.srcObject =
      stream;
  
    video
      .play()
      .catch(() => {
        setCamaraError(
          "No fue posible iniciar la vista de la cámara."
        );
      });
  }, [camaraActiva]);

  function detenerCamaraEntrada() {
    const stream =
      camaraStreamRef.current;
  
    if (stream) {
      stream
        .getTracks()
        .forEach((track) =>
          track.stop()
        );
    }
  
    camaraStreamRef.current =
      null;
  
    if (camaraVideoRef.current) {
      camaraVideoRef.current.srcObject =
        null;
    }
  
    setCamaraActiva(false);
  }
  
  async function abrirCamaraEntrada() {
    if (entradaSaving) {
      return;
    }
  
    setCamaraError("");
    setEntradaError("");
  
    if (
      !navigator.mediaDevices ||
      !navigator.mediaDevices
        .getUserMedia
    ) {
      setCamaraError(
        "Este dispositivo o navegador no permite acceder a la cámara."
      );
  
      return;
    }
  
    detenerCamaraEntrada();
  
    try {
      const stream =
        await navigator.mediaDevices
          .getUserMedia({
            video: {
              facingMode: {
                ideal: "user",
              },
  
              width: {
                ideal: 1280,
              },
  
              height: {
                ideal: 720,
              },
            },
  
            audio: false,
          });
  
      camaraStreamRef.current =
        stream;
  
      setFotoEntradaBase64(
        ""
      );
  
      setCamaraActiva(true);
    } catch (err) {
      console.error(
        "Error de cámara:",
        err
      );
  
      setCamaraError(
        "No fue posible abrir la cámara. Verifica que hayas concedido el permiso."
      );
    }
  }
  
  function capturarFotoEntrada() {
    if (entradaSaving) {
      return;
    }
  
    const video =
      camaraVideoRef.current;
  
    if (
      !video ||
      !video.videoWidth ||
      !video.videoHeight
    ) {
      setCamaraError(
        "La cámara todavía no está lista para tomar la fotografía."
      );
  
      return;
    }
  
    const maxWidth = 720;
  
    const scale =
      Math.min(
        1,
        maxWidth /
          video.videoWidth
      );
  
    const width =
      Math.round(
        video.videoWidth *
          scale
      );
  
    const height =
      Math.round(
        video.videoHeight *
          scale
      );
  
    const canvas =
      document.createElement(
        "canvas"
      );
  
    canvas.width =
      width;
  
    canvas.height =
      height;
  
    const ctx =
      canvas.getContext("2d");
  
    if (!ctx) {
      setCamaraError(
        "No fue posible procesar la fotografía."
      );
  
      return;
    }
  
    /*
     * Al utilizar la cámara frontal,
     * mostramos/capturamos la imagen
     * como el usuario la observa.
     */
    ctx.translate(
      width,
      0
    );
  
    ctx.scale(
      -1,
      1
    );
  
    ctx.drawImage(
      video,
      0,
      0,
      width,
      height
    );
  
    const foto =
      canvas.toDataURL(
        "image/jpeg",
        0.82
      );
  
    setFotoEntradaBase64(
      foto
    );
    
    setFotoCapturadaAt(
      new Date().toISOString()
    );
    
    setCamaraError("");
  
    detenerCamaraEntrada();
  }
  
  function repetirFotoEntrada() {
    setFotoEntradaBase64(
      ""
    );
    
    setFotoCapturadaAt("");
    
    setCamaraError("");
    
    abrirCamaraEntrada();
  }

  function openEntradaModal(
    mode,
    user = null
  ) {
    detenerCamaraEntrada();

    setEntradaModal({
      mode,
      user,
    });
  
    setFirmaRealizada(false);
    setFirmaCapturadaAt("");
    
    setFotoEntradaBase64("");
    setFotoCapturadaAt("");
    
    setCamaraError("");
    setEntradaError("");
    setEntradaEstado("");
    setError("");
    setMessage("");
  }

  useEffect(() => {
    return () => {
      const stream =
        camaraStreamRef.current;
  
      if (stream) {
        stream
          .getTracks()
          .forEach((track) =>
            track.stop()
          );
      }
  
      camaraStreamRef.current =
        null;
    };
  }, []);
  
  function closeEntradaModal(
    force = false
  ) {
    detenerCamaraEntrada();

    if (
      entradaSaving &&
      !force
    ) {
      return;
    }
  
    setEntradaModal(null);

    setFirmaRealizada(false);
    setFirmaCapturadaAt("");
    
    setFotoEntradaBase64("");
    setFotoCapturadaAt("");
    
    setCamaraError("");
    setEntradaError("");
    setEntradaEstado("");
  
    firmaDrawingRef.current =
      false;
  }
  
  function getFirmaPoint(event) {
    const canvas =
      firmaCanvasRef.current;
  
    if (!canvas) {
      return null;
    }
  
    const rect =
      canvas.getBoundingClientRect();
  
    return {
      x:
        event.clientX -
        rect.left,
  
      y:
        event.clientY -
        rect.top,
    };
  }
  
  function handleFirmaPointerDown(
    event
  ) {
    if (entradaSaving) {
      return;
    }
  
    event.preventDefault();
  
    const canvas =
      firmaCanvasRef.current;
  
    const point =
      getFirmaPoint(event);
  
    if (
      !canvas ||
      !point
    ) {
      return;
    }
  
    canvas.setPointerCapture?.(
      event.pointerId
    );
  
    firmaDrawingRef.current =
      true;
  
    firmaLastPointRef.current =
      point;
  
    /*
     * Dibujar también un pequeño punto.
     * Así una pulsación corta cuenta como trazo.
     */
    const ctx =
      canvas.getContext("2d");
  
    ctx.beginPath();
  
    ctx.arc(
      point.x,
      point.y,
      1.25,
      0,
      Math.PI * 2
    );
  
    ctx.fill();
  
    setFirmaRealizada(true);

    setFirmaCapturadaAt(
      new Date().toISOString()
    );
  }
  
  function handleFirmaPointerMove(
    event
  ) {
    if (
      !firmaDrawingRef.current ||
      entradaSaving
    ) {
      return;
    }
  
    event.preventDefault();
  
    const canvas =
      firmaCanvasRef.current;
  
    const point =
      getFirmaPoint(event);
  
    if (
      !canvas ||
      !point
    ) {
      return;
    }
  
    const previous =
      firmaLastPointRef.current;
  
    const ctx =
      canvas.getContext("2d");
  
    ctx.beginPath();
  
    ctx.moveTo(
      previous.x,
      previous.y
    );
  
    ctx.lineTo(
      point.x,
      point.y
    );
  
    ctx.stroke();
  
    firmaLastPointRef.current =
      point;
  
    setFirmaRealizada(true);

    setFirmaCapturadaAt(
      new Date().toISOString()
    );
  }
  
  function handleFirmaPointerUp(
    event
  ) {
    const canvas =
      firmaCanvasRef.current;
  
    if (
      canvas &&
      canvas.hasPointerCapture?.(
        event.pointerId
      )
    ) {
      canvas.releasePointerCapture(
        event.pointerId
      );
    }
  
    firmaDrawingRef.current =
      false;
  }
  
  function clearFirma() {
    if (entradaSaving) {
      return;
    }
  
    const canvas =
      firmaCanvasRef.current;
  
    if (!canvas) {
      return;
    }
  
    const rect =
      canvas.getBoundingClientRect();
  
    const dpr =
      window.devicePixelRatio || 1;
  
    const ctx =
      canvas.getContext("2d");
  
    ctx.setTransform(
      1,
      0,
      0,
      1,
      0,
      0
    );
  
    ctx.clearRect(
      0,
      0,
      canvas.width,
      canvas.height
    );
  
    ctx.setTransform(
      dpr,
      0,
      0,
      dpr,
      0,
      0
    );
  
    ctx.fillStyle =
      "#ffffff";
  
    ctx.fillRect(
      0,
      0,
      rect.width,
      rect.height
    );
  
    ctx.strokeStyle =
      "#0f172a";
  
    ctx.fillStyle =
      "#0f172a";
  
    ctx.lineWidth = 2.5;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
  
    setFirmaRealizada(false);
    setFirmaCapturadaAt("");
    setEntradaError("");
  }
  
  function obtenerUbicacionEntrada() {
    return new Promise(
      (resolve, reject) => {
        if (
          !navigator.geolocation
        ) {
          reject(
            new Error(
              "Este dispositivo no permite obtener la ubicación."
            )
          );
  
          return;
        }
  
        navigator.geolocation.getCurrentPosition(
          (position) => {
            resolve({
              latitud:
                position.coords
                  .latitude,
  
              longitud:
                position.coords
                  .longitude,
  
              precision:
                position.coords
                  .accuracy,
            });
          },
  
          (geoError) => {
            if (
              geoError.code === 1
            ) {
              reject(
                new Error(
                  "Debes permitir el acceso a la ubicación para registrar la entrada."
                )
              );
  
              return;
            }
  
            if (
              geoError.code === 2
            ) {
              reject(
                new Error(
                  "No fue posible determinar la ubicación del dispositivo."
                )
              );
  
              return;
            }
  
            if (
              geoError.code === 3
            ) {
              reject(
                new Error(
                  "La ubicación tardó demasiado en responder. Intenta nuevamente."
                )
              );
  
              return;
            }
  
            reject(
              new Error(
                "No fue posible obtener la ubicación."
              )
            );
          },
  
          {
            enableHighAccuracy:
              true,
  
            timeout: 15000,
  
            maximumAge: 0,
          }
        );
      }
    );
  }
  
  async function confirmarEntrada() {
    if (
      !entradaModal ||
      entradaSaving
    ) {
      return;
    }
  
    if (!firmaRealizada) {
      setEntradaError(
        "Debes firmar antes de registrar la entrada."
      );
  
      return;
    }

    if (!fotoEntradaBase64) {
      setEntradaError(
        "Debes tomar una fotografía antes de registrar la entrada."
      );
    
      return;
    }
  
    const canvas =
      firmaCanvasRef.current;
  
    if (!canvas) {
      setEntradaError(
        "No fue posible obtener la firma."
      );
  
      return;
    }
  
    const firmaBase64 =
      canvas.toDataURL(
        "image/png"
      );
  
    setEntradaSaving(true);
    setEntradaError("");
    setEntradaEstado(
      "Obteniendo ubicación..."
    );
  
    const esKiosco =
      entradaModal.mode ===
      "kiosco";
  
    const kioscoUser =
      entradaModal.user;
  
    if (esKiosco) {
      setKioscoSavingUserId(
        kioscoUser?.id || null
      );
    } else {
      setSaving(true);
    }
  
    try {
      const ubicacion =
        await obtenerUbicacionEntrada();
  
      setEntradaEstado(
        `Ubicación obtenida (precisión aproximada ±${Math.round(
          ubicacion.precision
        )} m). Registrando entrada...`
      );
  
      const payload = {
        firma_entrada:
          firmaBase64,
      
        firma_capturada_at:
          firmaCapturadaAt,
      
        foto_entrada:
          fotoEntradaBase64,
      
        foto_capturada_at:
          fotoCapturadaAt,
      
        entrada_latitud:
          ubicacion.latitud,
      
        entrada_longitud:
          ubicacion.longitud,
      
        entrada_precision_metros:
          ubicacion.precision,
      };
  
      let data;
  
      if (esKiosco) {
        data =
          await apiPost(
            `/asistencias/kiosco/${kioscoUser.id}/entrada`,
            payload
          );
      } else {
        data =
          await apiPost(
            "/asistencias/entrada",
            payload
          );
      }
  
      setMessageTone("success");

      setMessage(
        data?.message ||
          "Entrada registrada correctamente."
      );
  
      closeEntradaModal(true);
  
      if (esKiosco) {
        await loadKioscoData();
      } else {
        await loadData();
      }
    } catch (err) {
      setEntradaError(
        err?.message ||
          "No se pudo registrar la entrada."
      );
  
      setEntradaEstado("");
    } finally {
      setEntradaSaving(false);
  
      if (esKiosco) {
        setKioscoSavingUserId(
          null
        );
      } else {
        setSaving(false);
      }
    }
  }

  async function handleEntrada() {
    if (
      !canCreate ||
      saving ||
      entradaSaving
    ) {
      return;
    }
  
    if (incidenciaHoy) {
      setError(
        `Hoy tienes registrada la incidencia: ${getIncidenciaLabel(
          incidenciaHoy.tipo
        )}. No necesitas registrar entrada.`
      );
  
      return;
    }
  
    if (yaRegistroEntradaHoy) {
      setError(
        "Ya registraste una entrada el día de hoy. No puedes registrar otra entrada."
      );
  
      return;
    }
  
    openEntradaModal(
      "personal"
    );
  }

  async function handleSalida() {
    if (!canCreate || saving) {
      return;
    }

    const ok = window.confirm(
      "¿Deseas registrar tu hora de salida ahora?"
    );

    if (!ok) return;

    setSaving(true);
    setError("");
    setMessage("");

    try {
      const data =
        await apiPost(
          "/asistencias/salida",
          {}
        );

      setMessageTone("danger");

      setMessage(
        data?.message ||
          "Hora de salida registrada."
      );

      await loadData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo registrar la salida."
      );
    } finally {
      setSaving(false);
    }
  }

  async function handleInicioComida() {
    if (!canCreate || saving) {
      return;
    }
  
    const ok = window.confirm(
      "¿Deseas iniciar tu tiempo de comida ahora?"
    );
  
    if (!ok) return;
  
    setSaving(true);
    setError("");
    setMessage("");
  
    try {
      const data =
        await apiPost(
          "/asistencias/comida/inicio",
          {}
        );
  
      setMessageTone("meal");

      setMessage(
        data?.message ||
          "Inicio de comida registrado."
      );
  
      await loadData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo iniciar el tiempo de comida."
      );
    } finally {
      setSaving(false);
    }
  }
  
  async function handleFinComida() {
    if (!canCreate || saving) {
      return;
    }
  
    const ok = window.confirm(
      "¿Deseas finalizar tu tiempo de comida ahora?"
    );
  
    if (!ok) return;
  
    setSaving(true);
    setError("");
    setMessage("");
  
    try {
      const data =
        await apiPost(
          "/asistencias/comida/fin",
          {}
        );
  
      setMessageTone("meal");

      setMessage(
        data?.message ||
          "Fin de comida registrado."
      );
  
      await loadData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo finalizar el tiempo de comida."
      );
    } finally {
      setSaving(false);
    }
  }

  async function handleKioscoEntrada(
    user
  ) {
    if (
      !canKiosco ||
      kioscoSavingUserId ||
      entradaSaving
    ) {
      return;
    }
  
    if (user?.incidencia_hoy) {
      setError(
        `${user.name} tiene registrada la incidencia ${getIncidenciaLabel(
          user.incidencia_hoy.tipo
        )} para el día de hoy. No necesita registrar entrada.`
      );
  
      return;
    }
  
    if (
      user?.asistencia_hoy
        ?.hora_entrada
    ) {
      setError(
        `${user.name} ya registró una entrada el día de hoy. No puede registrar otra entrada.`
      );
  
      return;
    }
  
    openEntradaModal(
      "kiosco",
      user
    );
  }
  
  async function handleKioscoInicioComida(
    user
  ) {
    if (
      !canKiosco ||
      kioscoSavingUserId
    ) {
      return;
    }
  
    const ok = window.confirm(
      `¿Deseas iniciar el tiempo de comida de ${user.name}?`
    );
  
    if (!ok) return;
  
    setKioscoSavingUserId(
      user.id
    );
  
    setError("");
    setMessage("");
  
    try {
      const data =
        await apiPost(
          `/asistencias/kiosco/${user.id}/comida/inicio`,
          {}
        );
  
      setMessageTone("meal");

      setMessage(
        data?.message ||
          "Inicio de comida registrado correctamente."
      );
  
      await loadKioscoData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo iniciar el tiempo de comida."
      );
    } finally {
      setKioscoSavingUserId(
        null
      );
    }
  }
  
  async function handleKioscoFinComida(
    user
  ) {
    if (
      !canKiosco ||
      kioscoSavingUserId
    ) {
      return;
    }
  
    const ok = window.confirm(
      `¿Deseas finalizar el tiempo de comida de ${user.name}?`
    );
  
    if (!ok) return;
  
    setKioscoSavingUserId(
      user.id
    );
  
    setError("");
    setMessage("");
  
    try {
      const data =
        await apiPost(
          `/asistencias/kiosco/${user.id}/comida/fin`,
          {}
        );
  
      setMessageTone("meal");

      setMessage(
        data?.message ||
          "Fin de comida registrado correctamente."
      );
  
      await loadKioscoData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo finalizar el tiempo de comida."
      );
    } finally {
      setKioscoSavingUserId(
        null
      );
    }
  }

  async function handleKioscoSalida(
    user
  ) {
    if (
      !canKiosco ||
      kioscoSavingUserId
    ) {
      return;
    }
  
    const ok = window.confirm(
      `¿Deseas registrar la salida de ${user.name}?`
    );
  
    if (!ok) return;
  
    setKioscoSavingUserId(
      user.id
    );
  
    setError("");
    setMessage("");
  
    try {
      const data =
        await apiPost(
          `/asistencias/kiosco/${user.id}/salida`,
          {}
        );
  
      setMessageTone("danger");

      setMessage(
        data?.message ||
          "Salida registrada correctamente."
      );
  
      await loadKioscoData();
    } catch (err) {
      setError(
        err?.message ||
          "No se pudo registrar la salida."
      );
    } finally {
      setKioscoSavingUserId(
        null
      );
    }
  }

  /*
   * tick se usa para obligar a React
   * a actualizar el contador cada segundo.
   */
  void tick;

  const minutosComidaTurno =
    Number(
      actual?.turno?.minutos_comida || 0
    );
  
  const tieneComida =
    minutosComidaTurno > 0;
  
  const comidaIniciada =
    !!actual?.hora_inicio_comida;
  
  const comidaFinalizada =
    !!actual?.hora_fin_comida;
  
  const comidaEnCurso =
    comidaIniciada &&
    !comidaFinalizada;
  
  const detalleEstados =
    detailItem
      ? getEstadosAsistencia(
          detailItem
        )
      : null;

  const puedeVerFirmaDetalle =
    detailItem?.puede_ver_firma ===
    true;
  
  const puedeVerFotoDetalle =
    detailItem?.puede_ver_foto ===
    true;
  
  const puedeVerUbicacionDetalle =
    detailItem?.puede_ver_ubicacion ===
    true;
  
  const puedeVerEvidenciaDetalle =
    puedeVerFirmaDetalle ||
    puedeVerFotoDetalle ||
    puedeVerUbicacionDetalle;

  const detalleTieneUbicacion =
    puedeVerUbicacionDetalle &&
    detailItem?.entrada_latitud !== null &&
    detailItem?.entrada_latitud !== undefined &&
    detailItem?.entrada_longitud !== null &&
    detailItem?.entrada_longitud !== undefined;

  const detalleMapaUrl =
    detailItem &&
    detalleTieneUbicacion
      ? getOpenStreetMapEmbedUrl(
          detailItem.entrada_latitud,
          detailItem.entrada_longitud
        )
      : null;

  function cerrarDetalle() {
    setMostrarMapaDetalle(false);
    setMostrarFotoDetalle(false);
    setMostrarFirmaDetalle(false);
    setDetailItem(null);
  }

  const S = {
    page: {
      display: "flex",
      flexDirection: "column",
      gap: 14,
      width: "100%",
    },

    card: {
      border:
        "1px solid #e2e8f0",
      borderRadius: 16,
      background: "#ffffff",
      padding: 16,
      boxShadow:
        "0 4px 14px rgba(15,23,42,.04)",
    },

    heading: {
      fontSize: 20,
      fontWeight: 900,
      margin: 0,
      color: "#0f172a",
    },

    muted: {
      fontSize: 13,
      color: "#64748b",
      marginTop: 4,
    },

    statusActive: {
      display: "inline-flex",
      alignItems: "center",
      gap: 7,
      padding: "6px 10px",
      borderRadius: 999,
      background: "#ecfdf5",
      border:
        "1px solid #86efac",
      color: "#166534",
      fontWeight: 800,
      fontSize: 12,
    },

    statusInactive: {
      display: "inline-flex",
      alignItems: "center",
      gap: 7,
      padding: "6px 10px",
      borderRadius: 999,
      background: "#f8fafc",
      border:
        "1px solid #cbd5e1",
      color: "#475569",
      fontWeight: 800,
      fontSize: 12,
    },

    timeBig: {
      fontSize:
        "clamp(28px, 6vw, 44px)",
      lineHeight: 1,
      letterSpacing: 1,
      fontWeight: 900,
      color: "#0f172a",
      fontVariantNumeric:
        "tabular-nums",
      marginTop: 18,
    },

    buttonPrimary: {
      border: "none",
      borderRadius: 11,
      padding: "11px 16px",
      background: "#2563eb",
      color: "#ffffff",
      fontWeight: 900,
      cursor: "pointer",
      minHeight: 44,
    },

    buttonMeal: {
      border: "none",
      borderRadius: 11,
      padding: "11px 16px",
      background: "#d97706",
      color: "#ffffff",
      fontWeight: 900,
      cursor: "pointer",
      minHeight: 44,
    },

    buttonExit: {
      border: "none",
      borderRadius: 11,
      padding: "11px 16px",
      background: "#dc2626",
      color: "#ffffff",
      fontWeight: 900,
      cursor: "pointer",
      minHeight: 44,
    },

    tableWrap: {
      width: "100%",
      overflowX: "auto",
      marginTop: 12,
    },

    table: {
      width: "100%",
      borderCollapse:
        "collapse",
      minWidth: 1000,
    },

    th: {
      padding: "10px 8px",
      borderBottom:
        "1px solid #e2e8f0",
      textAlign: "center",
      fontSize: 12,
      color: "#475569",
      fontWeight: 900,
    },

    td: {
      padding: "11px 8px",
      borderBottom:
        "1px solid #f1f5f9",
      fontSize: 13,
      color: "#0f172a",
      textAlign: "center",
    },

    iconButton: {
      width: 34,
      height: 34,
      borderRadius: 9,
      border:
        "1px solid #bfdbfe",
      background: "#eff6ff",
      color: "#2563eb",
      cursor: "pointer",
      marginRight: 6,
    },


    alertError: {
      border:
        "1px solid #fecaca",
      background: "#fef2f2",
      color: "#991b1b",
      padding: "10px 12px",
      borderRadius: 10,
      fontSize: 13,
      fontWeight: 700,
    },

    alertSuccess: {
      border:
        "1px solid #bbf7d0",
      background: "#f0fdf4",
      color: "#166534",
      padding: "10px 12px",
      borderRadius: 10,
      fontSize: 13,
      fontWeight: 700,
    },

    alertMeal: {
      border:
        "1px solid #fed7aa",
      background: "#fff7ed",
      color: "#9a3412",
      padding: "10px 12px",
      borderRadius: 10,
      fontSize: 13,
      fontWeight: 700,
    },
    
    alertExit: {
      border:
        "1px solid #fecaca",
      background: "#fef2f2",
      color: "#991b1b",
      padding: "10px 12px",
      borderRadius: 10,
      fontSize: 13,
      fontWeight: 700,
    },

    offline: {
      border:
        "1px solid #fed7aa",
      background: "#fff7ed",
      color: "#9a3412",
      padding: "14px",
      borderRadius: 12,
      fontWeight: 800,
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
      maxWidth: 480,
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

    signatureCanvas: {
      width: "100%",
      height: 220,
      display: "block",
      border:
        "2px dashed #cbd5e1",
      borderRadius: 12,
      background: "#ffffff",
      touchAction: "none",
      cursor: "crosshair",
      boxSizing: "border-box",
    },
    
    signatureHint: {
      marginTop: 7,
      fontSize: 12,
      color: "#64748b",
      lineHeight: 1.45,
    },
    
    locationBox: {
      marginTop: 14,
      padding: "10px 12px",
      borderRadius: 10,
      background: "#f8fafc",
      border:
        "1px solid #e2e8f0",
      color: "#475569",
      fontSize: 12,
      lineHeight: 1.5,
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
      marginTop: 10,
    },

    input: {
      width: "100%",
      boxSizing: "border-box",
      minHeight: 42,
      padding: "8px 10px",
      border:
        "1px solid #cbd5e1",
      borderRadius: 9,
      fontSize: 14,
      marginTop: 5,
    },

    tabs: {
      display: "inline-flex",
      gap: 4,
      padding: 4,
      borderRadius: 12,
      background: "#f1f5f9",
      marginTop: 16,
    },
    
    tabButton: {
      border: "none",
      borderRadius: 9,
      padding: "9px 14px",
      fontWeight: 900,
      cursor: "pointer",
      minHeight: 40,
    },
    
    kioscoToolbar: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      marginTop: 14,
      flexWrap: "wrap",
    },
    
    kioscoSearch: {
      flex: 1,
      minWidth: 220,
      minHeight: 42,
      padding: "9px 12px",
      borderRadius: 10,
      border: "1px solid #cbd5e1",
      outline: "none",
      fontSize: 14,
    },
    
    kioscoGrid: {
      display: "grid",
      gridTemplateColumns:
        "repeat(auto-fit, minmax(280px, 1fr))",
      gap: 12,
      marginTop: 14,
    },
    
    kioscoCard: {
      border: "1px solid #e2e8f0",
      borderRadius: 14,
      padding: 14,
      background: "#fff",
    },
    
    kioscoName: {
      fontSize: 16,
      fontWeight: 900,
      color: "#0f172a",
    },
    
    kioscoMeta: {
      fontSize: 12,
      color: "#64748b",
      marginTop: 5,
      lineHeight: 1.5,
    },
  };

  if (!isOnline) {
    return (
      <div style={S.page}>
        <div style={S.card}>
          <h1 style={S.heading}>
            Control de acceso
          </h1>

          <div style={S.muted}>
            Registro de entrada y
            salida.
          </div>
        </div>

        <div style={S.offline}>
          <i
            className="fa-solid fa-wifi"
            style={{
              marginRight: 8,
            }}
          />

          Control de acceso requiere
          conexión a internet.
        </div>
      </div>
    );
  }

  return (
    <div style={S.page}>
      <div style={S.card}>
        <h1 style={S.heading}>
          Control de acceso
        </h1>
      
        <div style={S.muted}>
          Registro de entrada y salida.
        </div>
      
        {canKiosco ? (
          <div style={S.tabs}>
            <button
              type="button"
              onClick={() => {
                setActiveView("personal");
                setError("");
                setMessage("");
              }}
              style={{
                ...S.tabButton,
      
                background:
                  activeView === "personal"
                    ? "#ffffff"
                    : "transparent",
      
                color:
                  activeView === "personal"
                    ? "#0f172a"
                    : "#64748b",
      
                boxShadow:
                  activeView === "personal"
                    ? "0 1px 4px rgba(15,23,42,.12)"
                    : "none",
              }}
            >
              <i
                className="fa-solid fa-user"
                style={{
                  marginRight: 7,
                }}
              />
      
              Mi asistencia
            </button>
      
            <button
              type="button"
              onClick={() => {
                setActiveView("kiosco");
                setError("");
                setMessage("");
              }}
              style={{
                ...S.tabButton,
      
                background:
                  activeView === "kiosco"
                    ? "#ffffff"
                    : "transparent",
      
                color:
                  activeView === "kiosco"
                    ? "#0f172a"
                    : "#64748b",
      
                boxShadow:
                  activeView === "kiosco"
                    ? "0 1px 4px rgba(15,23,42,.12)"
                    : "none",
              }}
            >
              <i
                className="fa-solid fa-users"
                style={{
                  marginRight: 7,
                }}
              />
      
              Kiosco
            </button>
          </div>
        ) : null}
      </div>

      {error ? (
        <div style={S.alertError}>
          {error}
        </div>
      ) : null}

      {message ? (
        <div
          style={
            messageTone === "meal"
              ? S.alertMeal
              : messageTone === "danger"
                ? S.alertExit
                : S.alertSuccess
          }
        >
          {message}
        </div>
      ) : null}

    {activeView === "personal" ? (
      <>
      <div style={S.card}>
        {loading ? (
          <div
            style={{
              fontWeight: 800,
              color: "#64748b",
            }}
          >
            Cargando...
          </div>
        ) : actual ? (
          <>
            <span
              style={S.statusActive}
            >
              <span>●</span>
              Jornada en curso
            </span>

            <div style={S.timeBig}>
              {formatDuration(
                actual.hora_entrada
              )}
            </div>

            <div
              style={{
                marginTop: 12,
                fontSize: 14,
                color: "#334155",
              }}
            >
              <b>Entrada:</b>{" "}
              {formatTime(
                actual.hora_entrada
              )}
            </div>

            {canCreate ? (
              <div
                style={{
                  display: "flex",
                  flexWrap: "wrap",
                  gap: 10,
                  marginTop: 18,
                }}
              >
                {tieneComida &&
                !comidaIniciada ? (
                  <button
                    type="button"
                    onClick={
                      handleInicioComida
                    }
                    disabled={saving}
                    style={{
                      ...S.buttonMeal,
                      opacity:
                        saving ? 0.6 : 1,
                    }}
                  >
                    <i
                      className="fa-solid fa-utensils"
                      style={{
                        marginRight: 8,
                      }}
                    />
            
                    {saving
                      ? "Registrando..."
                      : "Iniciar comida"}
                  </button>
                ) : null}
            
                {tieneComida &&
                comidaEnCurso ? (
                  <button
                    type="button"
                    onClick={
                      handleFinComida
                    }
                    disabled={saving}
                    style={{
                      ...S.buttonMeal,
                      opacity:
                        saving ? 0.6 : 1,
                    }}
                  >
                    <i
                      className="fa-solid fa-utensils"
                      style={{
                        marginRight: 8,
                      }}
                    />
            
                    {saving
                      ? "Registrando..."
                      : "Terminar comida"}
                  </button>
                ) : null}
            
                <button
                  type="button"
                  onClick={handleSalida}
                  disabled={saving}
                  style={{
                    ...S.buttonExit,
                    opacity:
                      saving ? 0.6 : 1,
                  }}
                >
                  <i
                    className="fa-solid fa-right-from-bracket"
                    style={{
                      marginRight: 8,
                    }}
                  />
            
                  {saving
                    ? "Registrando..."
                    : "Registrar salida"}
                </button>
              </div>
            ) : null}
          </>
        ) : incidenciaHoy ? (
          <>
            <span
              style={S.statusInactive}
            >
              <span>●</span>
              {getIncidenciaLabel(
                incidenciaHoy.tipo
              )}
            </span>
        
            <div
              style={{
                marginTop: 18,
                fontSize: 15,
                fontWeight: 800,
                color: "#334155",
              }}
            >
              Hoy tienes registrada la
              incidencia{" "}
              {getIncidenciaLabel(
                incidenciaHoy.tipo
              )}.
            </div>
        
            <div
              style={{
                marginTop: 10,
                fontSize: 13,
                color: "#64748b",
              }}
            >
              No necesitas registrar entrada
              el día de hoy.
            </div>
        
            {incidenciaHoy.observaciones ? (
              <div
                style={{
                  marginTop: 10,
                  fontSize: 13,
                  color: "#64748b",
                }}
              >
                <b>Observaciones:</b>{" "}
                {
                  incidenciaHoy.observaciones
                }
              </div>
            ) : null}
          </>
        ) : yaRegistroEntradaHoy ? (
          <>
            <span
              style={S.statusInactive}
            >
              <span>●</span>
              Jornada registrada hoy
            </span>
        
            <div
              style={{
                marginTop: 18,
                fontSize: 15,
                fontWeight: 800,
                color: "#334155",
              }}
            >
              Ya registraste tu entrada
              del día de hoy.
            </div>
        
            <div
              style={{
                marginTop: 10,
                fontSize: 13,
                color: "#64748b",
              }}
            >
              <b>Entrada:</b>{" "}
              {formatTime(
                registroHoy?.hora_entrada
              )}
        
              {registroHoy?.hora_salida ? (
                <>
                  {" · "}
                  <b>Salida:</b>{" "}
                  {formatTime(
                    registroHoy?.hora_salida
                  )}
                </>
              ) : null}
            </div>
        
            <div
              style={{
                marginTop: 10,
                fontSize: 13,
                color: "#64748b",
              }}
            >
              No puedes registrar una
              segunda entrada el mismo día.
            </div>
          </>
        ) : (
          <>
            <span
              style={S.statusInactive}
            >
              <span>●</span>
              Sin jornada iniciada
            </span>
        
            <div
              style={{
                marginTop: 18,
                fontSize: 15,
                fontWeight: 800,
                color: "#334155",
              }}
            >
              No tienes una asistencia
              en curso.
            </div>
        
            {canCreate ? (
              <button
                type="button"
                onClick={handleEntrada}
                disabled={saving}
                style={{
                  ...S.buttonPrimary,
                  marginTop: 18,
                  opacity:
                    saving ? 0.6 : 1,
                }}
              >
                <i
                  className="fa-solid fa-right-to-bracket"
                  style={{
                    marginRight: 8,
                  }}
                />
        
                {saving
                  ? "Registrando..."
                  : "Registrar entrada"}
              </button>
            ) : null}
          </>
        )}
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
          <div>
            <div
              style={{
                fontSize: 16,
                fontWeight: 900,
              }}
            >
              Mis asistencias
            </div>

            <div style={S.muted}>
              Historial de tus últimos
              registros.
            </div>
          </div>

          <button
            type="button"
            onClick={loadData}
            disabled={loading}
            title="Actualizar"
            style={S.iconButton}
          >
            <i className="fa-solid fa-rotate" />
          </button>
        </div>

        <div style={S.tableWrap}>
          <table style={S.table}>
            <thead>
              <tr>
                <th style={S.th}>
                  Fecha
                </th>

                <th style={S.th}>
                  Entrada
                </th>

                <th style={S.th}>
                  Salida
                </th>

                <th style={S.th}>
                  Presencia
                </th>

                <th style={S.th}>
                  Comida
                </th>

                <th style={S.th}>
                  Trabajado
                </th>

                <th style={S.th}>
                  Jornada
                </th>

                <th style={S.th}>
                  Detalle
                </th>
              </tr>
            </thead>

            <tbody>
              {registros.length ===
              0 ? (
                <tr>
                  <td
                    colSpan={8}
                    style={{
                      ...S.td,
                      textAlign:
                        "center",
                      color: "#64748b",
                      padding: 24,
                    }}
                  >
                    No hay asistencias
                    registradas.
                  </td>
                </tr>
              ) : (
                registros.map(
                  (item) => {
                    const tieneSalida =
                      !!item.hora_salida;

                    const turnoTieneComida =
                      Number(
                        item.turno
                          ?.minutos_comida ||
                          0
                      ) > 0;

                    const comidaAbierta =
                      !!item.hora_inicio_comida &&
                      !item.hora_fin_comida;

                    const estados =
                      getEstadosAsistencia(
                        item
                      );

                    return (
                      <tr key={item.id}>
                        <td style={S.td}>
                          {formatDate(
                            item.fecha
                          )}
                        </td>

                        <td style={S.td}>
                          {formatTime(
                            item.hora_entrada
                          )}
                        </td>

                        <td style={S.td}>
                          {formatTime(
                            item.hora_salida
                          )}
                        </td>

                        <td style={S.td}>
                          {tieneSalida
                            ? formatSeconds(
                                item.segundos_presencia
                              )
                            : formatDuration(
                                item.hora_entrada
                              )}
                        </td>

                        <td style={S.td}>
                          {!turnoTieneComida
                            ? "No aplica"
                            : comidaAbierta
                              ? formatDuration(
                                  item.hora_inicio_comida
                                )
                              : item.hora_inicio_comida
                                ? formatSeconds(
                                    item.segundos_comida
                                  )
                                : "00:00:00"}
                        </td>

                        <td style={S.td}>
                          {tieneSalida
                            ? formatSeconds(
                                item.segundos_trabajados
                              )
                            : "En curso"}
                        </td>

                        <td style={S.td}>
                          <EstadoBadge
                            label="Jornada"
                            value={
                              estados
                                .jornada
                                .value
                            }
                            tone={
                              estados
                                .jornada
                                .tone
                            }
                          />
                        </td>

                        <td style={S.td}>
                          <button
                            type="button"
                            title="Ver detalle"
                            onClick={() => {
                              setMostrarMapaDetalle(
                                false
                              );
                            
                              setMostrarFotoDetalle(
                                false
                              );
                            
                              setMostrarFirmaDetalle(
                                false
                              );
                            
                              setDetailItem(
                                item
                              );
                            }}
                            style={
                              S.detailButton
                            }
                          >
                            <i className="fa-solid fa-eye" />
                          </button>
                        </td>
                      </tr>
                    );
                  }
                )
              )}
            </tbody>
          </table>
        </div>
      </div>
        </>
      ) : null}

      {activeView === "kiosco" &&
      canKiosco ? (
        <div style={S.card}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent:
                "space-between",
              gap: 10,
              flexWrap: "wrap",
            }}
          >
            <div>
              <div
                style={{
                  fontSize: 18,
                  fontWeight: 900,
                  color: "#0f172a",
                }}
              >
                Kiosco
              </div>
      
              <div style={S.muted}>
                Registra la entrada o
                salida de empleados
                habilitados para Kiosco.
              </div>
            </div>
      
            <button
              type="button"
              onClick={
                loadKioscoData
              }
              disabled={
                kioscoLoading
              }
              title="Actualizar"
              style={S.iconButton}
            >
              <i className="fa-solid fa-rotate" />
            </button>
          </div>
      
          <div
            style={
              S.kioscoToolbar
            }
          >
            <input
              type="search"
              value={kioscoSearch}
              onChange={(e) =>
                setKioscoSearch(
                  e.target.value
                )
              }
              placeholder="Buscar empleado, unidad, turno..."
              style={
                S.kioscoSearch
              }
            />
          </div>
      
          {kioscoLoading ? (
            <div
              style={{
                marginTop: 18,
                color: "#64748b",
                fontWeight: 800,
              }}
            >
              Cargando empleados...
            </div>
          ) : kioscoUsuariosFiltrados.length ===
            0 ? (
            <div
              style={{
                marginTop: 18,
                padding: 20,
                borderRadius: 12,
                background: "#f8fafc",
                color: "#64748b",
                textAlign: "center",
                fontWeight: 800,
              }}
            >
              No hay empleados
              habilitados para Kiosco.
            </div>
          ) : (
            <div style={S.kioscoGrid}>
              {kioscoUsuariosFiltrados.map(
                (user) => {
                  const tieneAsistencia =
                    !!user.asistencia_actual;
      
                  const trabajando =
                    kioscoSavingUserId ===
                    user.id;

                  const asistenciaActual =
                    user.asistencia_actual;
                  
                  const asistenciaHoy =
                    user.asistencia_hoy;
                  
                  const incidenciaHoyKiosco =
                    user.incidencia_hoy;
                  
                  const yaRegistroEntradaHoyKiosco =
                    !!asistenciaHoy?.hora_entrada;

                  /*
                   * Si hay una jornada abierta,
                   * usar el turno histórico de
                   * esa asistencia.
                   *
                   * Si no hay jornada abierta,
                   * usar el turno actual.
                   */
                  const turnoMostrado =
                    asistenciaActual?.turno ||
                    asistenciaHoy?.turno ||
                    user.turno;
                  
                  const minutosComidaTurno =
                    Number(
                      turnoMostrado
                        ?.minutos_comida || 0
                    );
                  
                  const tieneComida =
                    minutosComidaTurno > 0;
                  
                  const comidaIniciada =
                    !!asistenciaActual
                      ?.hora_inicio_comida;
                  
                  const comidaFinalizada =
                    !!asistenciaActual
                      ?.hora_fin_comida;
                  
                  const comidaEnCurso =
                    comidaIniciada &&
                    !comidaFinalizada;
      
                  const tipoTrabajo =
                    user.tipo_trabajo ===
                    "mixto_salidas"
                      ? "Mixto / Salidas"
                      : "Fijo";
      
                  return (
                    <div
                      key={user.id}
                      style={
                        S.kioscoCard
                      }
                    >
                      <div
                        style={
                          S.kioscoName
                        }
                      >
                        {user.name}
                      </div>
      
                      {user.email ? (
                        <div
                          style={{
                            ...S.kioscoMeta,
                            marginTop: 2,
                          }}
                        >
                          {user.email}
                        </div>
                      ) : null}
      
                      <div
                        style={{
                          ...S.kioscoMeta,
                          marginTop: 12,
                        }}
                      >
                        <div>
                          <b>
                            Unidad laboral:
                          </b>{" "}
                          {user
                            .unidad_laboral
                            ?.nombre ||
                            "—"}
                        </div>
      
                        <div>
                          <b>Turno:</b>{" "}
                          {turnoMostrado
                            ?.nombre ||
                            "—"}
                        </div>
                        
                        {turnoMostrado ? (
                          <div>
                            <b>Horario:</b>{" "}
                            {String(
                              turnoMostrado
                                .hora_inicio ||
                                ""
                            ).slice(
                              0,
                              5
                            )}
                            {" - "}
                            {String(
                              turnoMostrado
                                .hora_fin ||
                                ""
                            ).slice(
                              0,
                              5
                            )}
                          </div>
                        ) : null}
      
                        <div>
                          <b>
                            Tipo de trabajo:
                          </b>{" "}
                          {tipoTrabajo}
                        </div>
                      </div>
      
                      <div
                        style={{
                          marginTop: 14,
                          paddingTop: 12,
                          borderTop:
                            "1px solid #e2e8f0",
                        }}
                      >
                        {tieneAsistencia ? (
                          <>
                            <span
                              style={
                                S.statusActive
                              }
                            >
                              <span>●</span>
                              Jornada en curso
                            </span>
      
                            <div
                              style={{
                                marginTop: 10,
                                fontSize: 13,
                                color: "#334155",
                              }}
                            >
                              <b>
                                Entrada:
                              </b>{" "}
                              {formatTime(
                                user
                                  .asistencia_actual
                                  .hora_entrada
                              )}
                            </div>

                            {tieneComida &&
                            !comidaIniciada ? (
                              <button
                                type="button"
                                disabled={
                                  trabajando
                                }
                                onClick={() =>
                                  handleKioscoInicioComida(
                                    user
                                  )
                                }
                                style={{
                                  ...S.buttonMeal,
                                  width: "100%",
                                  marginTop: 12,
                                  opacity:
                                    trabajando
                                      ? 0.6
                                      : 1,
                                }}
                              >
                                <i
                                  className="fa-solid fa-utensils"
                                  style={{
                                    marginRight: 8,
                                  }}
                                />
                            
                                {trabajando
                                  ? "Registrando..."
                                  : "Iniciar comida"}
                              </button>
                            ) : null}
                            
                            {tieneComida &&
                            comidaEnCurso ? (
                              <>
                                <div
                                  style={{
                                    marginTop: 10,
                                    fontSize: 13,
                                    color: "#92400e",
                                    fontWeight: 800,
                                  }}
                                >
                                  Comida iniciada:{" "}
                                  {formatTime(
                                    asistenciaActual
                                      ?.hora_inicio_comida
                                  )}
                                </div>
                            
                                <button
                                  type="button"
                                  disabled={
                                    trabajando
                                  }
                                  onClick={() =>
                                    handleKioscoFinComida(
                                      user
                                    )
                                  }
                                  style={{
                                    ...S.buttonMeal,
                                    width: "100%",
                                    marginTop: 10,
                                    opacity:
                                      trabajando
                                        ? 0.6
                                        : 1,
                                  }}
                                >
                                  <i
                                    className="fa-solid fa-utensils"
                                    style={{
                                      marginRight: 8,
                                    }}
                                  />
                            
                                  {trabajando
                                    ? "Registrando..."
                                    : "Terminar comida"}
                                </button>
                              </>
                            ) : null}
                            
                            {tieneComida &&
                            comidaFinalizada ? (
                              <div
                                style={{
                                  marginTop: 10,
                                  fontSize: 13,
                                  color: "#166534",
                                  fontWeight: 800,
                                }}
                              >
                                Comida:{" "}
                                {formatTime(
                                  asistenciaActual
                                    ?.hora_inicio_comida
                                )}
                                {" - "}
                                {formatTime(
                                  asistenciaActual
                                    ?.hora_fin_comida
                                )}
                              </div>
                            ) : null}

                            <button
                              type="button"
                              disabled={
                                trabajando
                              }
                              onClick={() =>
                                handleKioscoSalida(
                                  user
                                )
                              }
                              style={{
                                ...S.buttonExit,
                                width: "100%",
                                marginTop: 14,
                                opacity:
                                  trabajando
                                    ? 0.6
                                    : 1,
                              }}
                            >
                              <i
                                className="fa-solid fa-right-from-bracket"
                                style={{
                                  marginRight: 8,
                                }}
                              />
      
                              {trabajando
                                ? "Registrando..."
                                : "Registrar salida"}
                            </button>
                          </>
                        ) : incidenciaHoyKiosco ? (
                          <>
                            <span
                              style={S.statusInactive}
                            >
                              <span>●</span>
                              {getIncidenciaLabel(
                                incidenciaHoyKiosco.tipo
                              )}
                            </span>
                        
                            <div
                              style={{
                                marginTop: 12,
                                fontSize: 13,
                                color: "#334155",
                                fontWeight: 800,
                              }}
                            >
                              Este empleado tiene registrada
                              la incidencia{" "}
                              {getIncidenciaLabel(
                                incidenciaHoyKiosco.tipo
                              )}.
                            </div>
                        
                            <div
                              style={{
                                marginTop: 8,
                                fontSize: 12,
                                color: "#64748b",
                                fontWeight: 800,
                              }}
                            >
                              No requiere registro de entrada
                              el día de hoy.
                            </div>
                        
                            {incidenciaHoyKiosco
                              .observaciones ? (
                              <div
                                style={{
                                  marginTop: 8,
                                  fontSize: 12,
                                  color: "#64748b",
                                }}
                              >
                                <b>Observaciones:</b>{" "}
                                {
                                  incidenciaHoyKiosco
                                    .observaciones
                                }
                              </div>
                            ) : null}
                          </>
                        ) : yaRegistroEntradaHoyKiosco ? (
                          <>
                            <span
                              style={S.statusInactive}
                            >
                              <span>●</span>
                              Jornada registrada hoy
                            </span>
                        
                            <div
                              style={{
                                marginTop: 12,
                                fontSize: 13,
                                color: "#334155",
                              }}
                            >
                              <b>Entrada:</b>{" "}
                              {formatTime(
                                asistenciaHoy?.hora_entrada
                              )}
                            </div>
                        
                            {asistenciaHoy?.hora_salida ? (
                              <div
                                style={{
                                  marginTop: 6,
                                  fontSize: 13,
                                  color: "#334155",
                                }}
                              >
                                <b>Salida:</b>{" "}
                                {formatTime(
                                  asistenciaHoy?.hora_salida
                                )}
                              </div>
                            ) : null}
                        
                            <div
                              style={{
                                marginTop: 10,
                                fontSize: 12,
                                color: "#64748b",
                                fontWeight: 800,
                              }}
                            >
                              Este empleado ya registró
                              su entrada el día de hoy.
                            </div>
                          </>
                        ) : (
                          <>
                            <span
                              style={
                                S.statusInactive
                              }
                            >
                              <span>●</span>
                              Sin jornada iniciada
                            </span>
                        
                            <button
                              type="button"
                              disabled={
                                trabajando
                              }
                              onClick={() =>
                                handleKioscoEntrada(
                                  user
                                )
                              }
                              style={{
                                ...S.buttonPrimary,
                                width: "100%",
                                marginTop: 14,
                                opacity:
                                  trabajando
                                    ? 0.6
                                    : 1,
                              }}
                            >
                              <i
                                className="fa-solid fa-right-to-bracket"
                                style={{
                                  marginRight: 8,
                                }}
                              />
                        
                              {trabajando
                                ? "Registrando..."
                                : "Registrar entrada"}
                            </button>
                          </>
                        )}
                      </div>
                    </div>
                  );
                }
              )}
            </div>
          )}
        </div>
      ) : null}


      {detailItem ? (
        <div
          style={S.modalBackdrop}
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              cerrarDetalle();
            }
          }}
        >
          <div
            style={{
              ...S.modal,
              maxWidth: 820,
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
                  Detalle de asistencia
                </div>

                <div style={S.muted}>
                  {formatDate(
                    detailItem.fecha
                  )}
                </div>
              </div>

              <button
                type="button"
                onClick={
                  cerrarDetalle
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
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Unidad laboral
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
                  {detailItem
                    .unidad_laboral
                    ?.nombre ||
                    "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Tipo de trabajo
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
                  {formatTipoTrabajo(
                    detailItem
                      .tipo_trabajo
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Turno
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
                  {detailItem.turno
                    ?.nombre ||
                    "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Horario del turno
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
                  {detailItem.turno
                    ? `${String(
                        detailItem
                          .turno
                          .hora_inicio ||
                          ""
                      ).slice(
                        0,
                        5
                      )} - ${String(
                        detailItem
                          .turno
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
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Jornada requerida
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
                  {detailItem.turno
                    ?.horas_jornada !==
                  undefined
                    ? `${detailItem.turno.horas_jornada} h`
                    : "—"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div
                  style={
                    S.detailLabel
                  }
                >
                  Método de entrada
                </div>

                <div
                  style={
                    S.detailValue
                  }
                >
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
                    detailItem.hora_entrada
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
                    detailItem.hora_salida
                  )}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Presencia
                </div>

                <div style={S.detailValue}>
                  {detailItem.hora_salida
                    ? formatSeconds(
                        detailItem
                          .segundos_presencia
                      )
                    : formatDuration(
                        detailItem
                          .hora_entrada
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
                    : detailItem
                          .hora_inicio_comida
                      ? detailItem
                          .hora_fin_comida
                        ? formatSeconds(
                            detailItem
                              .segundos_comida
                          )
                        : "En curso"
                      : "00:00:00"}
                </div>
              </div>

              <div style={S.detailBox}>
                <div style={S.detailLabel}>
                  Tiempo trabajado
                </div>

                <div style={S.detailValue}>
                  {detailItem.hora_salida
                    ? formatSeconds(
                        detailItem
                          .segundos_trabajados
                      )
                    : "En curso"}
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
                value={
                  detalleEstados
                    ?.dia.value
                }
                tone={
                  detalleEstados
                    ?.dia.tone
                }
              />

              <EstadoBadge
                label="Entrada"
                value={
                  detalleEstados
                    ?.entrada.value
                }
                tone={
                  detalleEstados
                    ?.entrada.tone
                }
              />

              <EstadoBadge
                label="Salida"
                value={
                  detalleEstados
                    ?.salida.value
                }
                tone={
                  detalleEstados
                    ?.salida.tone
                }
              />

              <EstadoBadge
                label="Comida"
                value={
                  detalleEstados
                    ?.comida.value
                }
                tone={
                  detalleEstados
                    ?.comida.tone
                }
              />

              <EstadoBadge
                label="Jornada"
                value={
                  detalleEstados
                    ?.jornada.value
                }
                tone={
                  detalleEstados
                    ?.jornada.tone
                }
              />
            </div>

            {puedeVerEvidenciaDetalle ? (
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
            
                {puedeVerFirmaDetalle ? (
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
            
                    {!detailItem
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
                ) : null}
            
                {puedeVerFotoDetalle ? (
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
            
                    {!detailItem
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
                ) : null}
            
                {puedeVerUbicacionDetalle ? (
                  <>
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
            
                    {!detalleTieneUbicacion ? (
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
                            className={
                              mostrarMapaDetalle
                                ? "fa-solid fa-map"
                                : "fa-solid fa-map-location-dot"
                            }
                            style={{
                              marginRight: 8,
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
                                <div
                                  style={
                                    S.detailLabel
                                  }
                                >
                                  Precisión
                                </div>
                            
                                <div
                                  style={
                                    S.detailValue
                                  }
                                >
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
                              <div
                                style={
                                  S.detailBox
                                }
                              >
                                <div
                                  style={
                                    S.detailLabel
                                  }
                                >
                                  Latitud
                                </div>
            
                                <div
                                  style={
                                    S.detailValue
                                  }
                                >
                                  {detailItem
                                    .entrada_latitud}
                                </div>
                              </div>
            
                              <div
                                style={
                                  S.detailBox
                                }
                              >
                                <div
                                  style={
                                    S.detailLabel
                                  }
                                >
                                  Longitud
                                </div>
            
                                <div
                                  style={
                                    S.detailValue
                                  }
                                >
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
                  </>
                ) : null}
              </div>
            ) : null}

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
                  cerrarDetalle
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

      {entradaModal ? (
        <div
          style={S.modalBackdrop}
          onMouseDown={(e) => {
            if (
              e.target ===
              e.currentTarget
            ) {
              closeEntradaModal();
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
                justifyContent:
                  "space-between",
                alignItems: "flex-start",
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
                  Firma de entrada
                </div>
      
                <div style={S.muted}>
                  {entradaModal.mode ===
                  "kiosco"
                    ? entradaModal.user
                        ?.name ||
                      "Empleado"
                    : me?.name ||
                      "Mi asistencia"}
                </div>
              </div>
      
              <button
                type="button"
                onClick={() =>
                  closeEntradaModal()
                }
                disabled={
                  entradaSaving
                }
                style={{
                  width: 36,
                  height: 36,
                  flexShrink: 0,
                  borderRadius: 999,
                  border:
                    "1px solid #e2e8f0",
                  background: "#fff",
                  cursor:
                    entradaSaving
                      ? "not-allowed"
                      : "pointer",
                  opacity:
                    entradaSaving
                      ? 0.5
                      : 1,
                }}
              >
                <i className="fa-solid fa-xmark" />
              </button>
            </div>
      
            <div
              style={{
                marginTop: 18,
                fontSize: 13,
                color: "#334155",
                fontWeight: 800,
              }}
            >
              Firma del empleado
            </div>
      
            <div
              style={{
                marginTop: 8,
              }}
            >
              <canvas
                ref={firmaCanvasRef}
                onPointerDown={
                  handleFirmaPointerDown
                }
                onPointerMove={
                  handleFirmaPointerMove
                }
                onPointerUp={
                  handleFirmaPointerUp
                }
                onPointerCancel={
                  handleFirmaPointerUp
                }
                style={
                  S.signatureCanvas
                }
              />
            </div>
      
            <div
              style={
                S.signatureHint
              }
            >
              Firma dentro del recuadro
              utilizando el dedo, mouse
              o lápiz digital.
            </div>
      
            <button
              type="button"
              onClick={clearFirma}
              disabled={
                entradaSaving
              }
              style={{
                marginTop: 9,
                minHeight: 36,
                padding: "7px 11px",
                borderRadius: 8,
                border:
                  "1px solid #cbd5e1",
                background: "#ffffff",
                color: "#475569",
                fontWeight: 800,
                cursor:
                  entradaSaving
                    ? "not-allowed"
                    : "pointer",
              }}
            >
              <i
                className="fa-solid fa-eraser"
                style={{
                  marginRight: 7,
                }}
              />
      
              Limpiar firma
            </button>

            <div
              style={{
                marginTop: 18,
                fontSize: 13,
                color: "#334155",
                fontWeight: 800,
              }}
            >
              Fotografía del empleado
            </div>
            
            <div
              style={{
                marginTop: 8,
                padding: 12,
                border:
                  "1px solid #cbd5e1",
                borderRadius: 10,
                background: "#f8fafc",
              }}
            >
              {fotoEntradaBase64 ? (
                <>
                  <img
                    src={
                      fotoEntradaBase64
                    }
                    alt="Fotografía de entrada"
                    style={{
                      display: "block",
                      width: "100%",
                      maxHeight: 320,
                      objectFit: "contain",
                      borderRadius: 8,
                      background: "#0f172a",
                    }}
                  />
            
                  <button
                    type="button"
                    onClick={
                      repetirFotoEntrada
                    }
                    disabled={
                      entradaSaving
                    }
                    style={{
                      marginTop: 10,
                      minHeight: 38,
                      padding: "8px 12px",
                      borderRadius: 8,
                      border:
                        "1px solid #cbd5e1",
                      background: "#ffffff",
                      color: "#334155",
                      fontWeight: 800,
                      cursor:
                        entradaSaving
                          ? "not-allowed"
                          : "pointer",
                    }}
                  >
                    <i
                      className="fa-solid fa-camera-rotate"
                      style={{
                        marginRight: 7,
                      }}
                    />
            
                    Tomar otra foto
                  </button>
                </>
              ) : camaraActiva ? (
                <>
                  <video
                    ref={camaraVideoRef}
                    autoPlay
                    muted
                    playsInline
                    style={{
                      display: "block",
                      width: "100%",
                      maxHeight: 320,
                      objectFit: "cover",
                      borderRadius: 8,
                      background: "#0f172a",
            
                      /*
                       * Efecto espejo para
                       * cámara frontal.
                       */
                      transform:
                        "scaleX(-1)",
                    }}
                  />
            
                  <button
                    type="button"
                    onClick={
                      capturarFotoEntrada
                    }
                    disabled={
                      entradaSaving
                    }
                    style={{
                      marginTop: 10,
                      minHeight: 42,
                      padding: "9px 14px",
                      borderRadius: 9,
                      border: "none",
                      background: "#2563eb",
                      color: "#ffffff",
                      fontWeight: 900,
                      cursor:
                        entradaSaving
                          ? "not-allowed"
                          : "pointer",
                    }}
                  >
                    <i
                      className="fa-solid fa-camera"
                      style={{
                        marginRight: 8,
                      }}
                    />
            
                    Tomar fotografía
                  </button>
                </>
              ) : (
                <>
                  <div
                    style={{
                      fontSize: 12,
                      color: "#64748b",
                    }}
                  >
                    La fotografía debe tomarse
                    en este momento con la cámara
                    del dispositivo.
                  </div>
            
                  <button
                    type="button"
                    onClick={
                      abrirCamaraEntrada
                    }
                    disabled={
                      entradaSaving
                    }
                    style={{
                      marginTop: 10,
                      minHeight: 42,
                      padding: "9px 14px",
                      borderRadius: 9,
                      border:
                        "1px solid #2563eb",
                      background: "#ffffff",
                      color: "#2563eb",
                      fontWeight: 900,
                      cursor:
                        entradaSaving
                          ? "not-allowed"
                          : "pointer",
                    }}
                  >
                    <i
                      className="fa-solid fa-camera"
                      style={{
                        marginRight: 8,
                      }}
                    />
            
                    Abrir cámara
                  </button>
                </>
              )}
            
              {camaraError ? (
                <div
                  style={{
                    marginTop: 9,
                    color: "#b91c1c",
                    fontSize: 12,
                    fontWeight: 800,
                  }}
                >
                  {camaraError}
                </div>
              ) : null}
            </div>
      
            <div
              style={S.locationBox}
            >
              <div
                style={{
                  fontWeight: 900,
                  color: "#334155",
                }}
              >
                <i
                  className="fa-solid fa-location-dot"
                  style={{
                    marginRight: 7,
                  }}
                />
      
                Ubicación obligatoria
              </div>
      
              <div
                style={{
                  marginTop: 4,
                }}
              >
                Al confirmar se solicitará
                la ubicación actual de este
                dispositivo.
              </div>
      
              {entradaModal.mode ===
              "kiosco" ? (
                <div
                  style={{
                    marginTop: 5,
                  }}
                >
                  La ubicación corresponderá
                  al dispositivo Kiosco donde
                  el empleado está realizando
                  su registro.
                </div>
              ) : null}
            </div>
      
            {entradaEstado ? (
              <div
                style={{
                  marginTop: 12,
                  padding: "9px 11px",
                  borderRadius: 9,
                  background: "#eff6ff",
                  border:
                    "1px solid #bfdbfe",
                  color: "#1d4ed8",
                  fontSize: 12,
                  fontWeight: 800,
                }}
              >
                <i
                  className="fa-solid fa-location-crosshairs"
                  style={{
                    marginRight: 7,
                  }}
                />
      
                {entradaEstado}
              </div>
            ) : null}
      
            {entradaError ? (
              <div
                style={{
                  ...S.alertError,
                  marginTop: 12,
                }}
              >
                {entradaError}
              </div>
            ) : null}
      
            <div
              style={{
                display: "flex",
                justifyContent:
                  "flex-end",
                gap: 8,
                marginTop: 18,
                flexWrap: "wrap",
              }}
            >
              <button
                type="button"
                onClick={() =>
                  closeEntradaModal()
                }
                disabled={
                  entradaSaving
                }
                style={{
                  minHeight: 42,
                  padding: "9px 14px",
                  borderRadius: 9,
                  border:
                    "1px solid #cbd5e1",
                  background: "#fff",
                  fontWeight: 800,
                  cursor:
                    entradaSaving
                      ? "not-allowed"
                      : "pointer",
                }}
              >
                Cancelar
              </button>
      
              <button
                type="button"
                onClick={
                  confirmarEntrada
                }
                disabled={
                  entradaSaving
                }
                style={{
                  ...S.buttonPrimary,
                  opacity:
                    entradaSaving
                      ? 0.6
                      : 1,
                }}
              >
                <i
                  className="fa-solid fa-signature"
                  style={{
                    marginRight: 8,
                  }}
                />
      
                {entradaSaving
                  ? "Registrando..."
                  : "Registrar entrada"}
              </button>
            </div>
          </div>
        </div>
      ) : null}

    </div>
  );
}