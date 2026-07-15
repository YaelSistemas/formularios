import React, { useEffect, useRef, useState } from "react";

export default function SGI_POP_GT_01_FO_09_Checklist_de_Prensas({
  form,
  fields,
  answers,
  setVal,
  saving,
  isOnline,
  msg,
  onBack,
  onSubmit,
  setMsg,
  readOnly = false,
  responseMeta = null,
  isEditing = false,
}) {
  const [formFieldError, setFormFieldError] = useState("");
  const [formFieldErrorId, setFormFieldErrorId] = useState(null);
  const [indicacionesOpen, setIndicacionesOpen] = useState(true);
  const [tipoPrensaOpen, setTipoPrensaOpen] = useState(true);
  const [unidadServicioOpen, setUnidadServicioOpen] = useState(true);
  const [prestamoDevolucionOpen, setPrestamoDevolucionOpen] = useState(true);
  const [firmasOpen, setFirmasOpen] = useState(true);

  const [signatureModal, setSignatureModal] = useState({ open: false, field: null });

  const [equipmentModal, setEquipmentModal] = useState({
    open: false,
    editIndex: null,
  });

  const [equipmentDraft, setEquipmentDraft] = useState({
    cantidad: "",
    nombre_equipo: "",
    numero_serie: "",
    observaciones: "",
  });

  const [equipmentError, setEquipmentError] = useState("");

  const [isMobile, setIsMobile] = useState(() => {
    if (typeof window === "undefined") return false;
    return window.innerWidth < 768;
  });

  const signatureCanvasRef = useRef(null);
  const signatureWrapperRef = useRef(null);
  const drawingRef = useRef(false);
  const lastPointRef = useRef({ x: 0, y: 0 });
  const topRef = useRef(null);
  const formFieldRefs = useRef({});
  const formFieldWrapRefs = useRef({});
  const formErrorTimerRef = useRef(null);

  useEffect(() => {
    const onResize = () => setIsMobile(window.innerWidth < 768);
    window.addEventListener("resize", onResize);
    return () => window.removeEventListener("resize", onResize);
  }, []);

  useEffect(() => {
    return () => {
      if (formErrorTimerRef.current) clearTimeout(formErrorTimerRef.current);
    };
  }, []);

  const normalizeAssetUrl = (url) => {
    if (!url) return "";
    let normalized = String(url).trim();
    normalized = normalized.replace(/\\/g, "/");
    normalized = normalized.replace(/^\/?public\//i, "/");
    if (/^https?:\/\//i.test(normalized)) return normalized;
    if (!normalized.startsWith("/")) normalized = `/${normalized}`;
    return normalized;
  };

  const clearFormFieldError = () => {
    setFormFieldError("");
    setFormFieldErrorId(null);
    if (formErrorTimerRef.current) {
      clearTimeout(formErrorTimerRef.current);
      formErrorTimerRef.current = null;
    }
  };

  const getField = (id) => fields.find((f) => f.id === id);

  const getEquipmentRows = () => {
    const rows = answers?.tabla_equipos_medicion;

    if (Array.isArray(rows)) {
      return rows;
    }

    // Compatibilidad con registros anteriores que usaban campos individuales.
    const legacyRow = {
      cantidad: answers?.cantidad_equipo_medicion ?? "",
      nombre_equipo: answers?.nombre_equipo ?? "",
      numero_serie: answers?.numero_serie_equipo ?? "",
      observaciones: answers?.observaciones ?? "",
    };

    const hasLegacyData = Object.values(legacyRow).some(
      (value) => value !== null && value !== undefined && String(value).trim() !== ""
    );

    return hasLegacyData ? [legacyRow] : [];
  };

  const openEquipmentModal = (editIndex = null) => {
    if (readOnly) return;

    const rows = getEquipmentRows();
    const row =
      editIndex === null || editIndex === undefined
        ? null
        : rows[editIndex] || null;

    setEquipmentDraft({
      cantidad: row?.cantidad ?? "",
      nombre_equipo: row?.nombre_equipo ?? "",
      numero_serie: row?.numero_serie ?? "",
      observaciones: row?.observaciones ?? "",
    });

    setEquipmentError("");
    setEquipmentModal({
      open: true,
      editIndex,
    });
  };

  const closeEquipmentModal = () => {
    setEquipmentModal({
      open: false,
      editIndex: null,
    });

    setEquipmentDraft({
      cantidad: "",
      nombre_equipo: "",
      numero_serie: "",
      observaciones: "",
    });

    setEquipmentError("");
  };

  const updateEquipmentDraft = (fieldId, value) => {
    setEquipmentDraft((prev) => ({
      ...prev,
      [fieldId]: value,
    }));

    if (equipmentError) {
      setEquipmentError("");
    }
  };

  const saveEquipmentRow = () => {
    const hasAnyValue = Object.values(equipmentDraft).some(
      (value) => value !== null && value !== undefined && String(value).trim() !== ""
    );

    if (!hasAnyValue) {
      setEquipmentError("Captura al menos un dato del equipo antes de guardarlo.");
      return;
    }

    if (equipmentDraft.cantidad !== "" && Number(equipmentDraft.cantidad) < 0) {
      setEquipmentError("La cantidad no puede ser negativa.");
      return;
    }

    const rows = [...getEquipmentRows()];
    const normalizedRow = {
      cantidad: equipmentDraft.cantidad === "" ? "" : Number(equipmentDraft.cantidad),
      nombre_equipo: String(equipmentDraft.nombre_equipo ?? "").trim(),
      numero_serie: String(equipmentDraft.numero_serie ?? "").trim(),
      observaciones: String(equipmentDraft.observaciones ?? "").trim(),
    };

    if (equipmentModal.editIndex === null || equipmentModal.editIndex === undefined) {
      rows.push(normalizedRow);
    } else {
      rows[equipmentModal.editIndex] = normalizedRow;
    }

    setVal("tabla_equipos_medicion", rows);
    setMsg("");
    closeEquipmentModal();
  };

  const removeEquipmentRow = (index) => {
    if (readOnly) return;

    const rows = getEquipmentRows().filter((_, rowIndex) => rowIndex !== index);
    setVal("tabla_equipos_medicion", rows);
    setMsg("");
  };

  const openSectionForField = (fieldId) => {
    const id = String(fieldId || "");

    if (
      id === "codigo" ||
      id === "no_serie" ||
      id === "tipo" ||
      id === "corriente"
    ) {
      setIndicacionesOpen(true);
      setTipoPrensaOpen(true);
      return;
    }

    if (
      id === "taller_origen" ||
      id === "taller_solicita" ||
      id === "accion_realizar" ||
      id.endsWith("_estado") ||
      id.endsWith("_cantidad") ||
      id.endsWith("_comentarios") ||
      id === "notas"
    ) {
      setIndicacionesOpen(true);
      setUnidadServicioOpen(true);
      return;
    }

    if (
      id === "tabla_equipos_medicion" ||
      id === "cantidad_equipo_medicion" ||
      id === "nombre_equipo" ||
      id === "numero_serie_equipo" ||
      id === "observaciones"
    ) {
      setIndicacionesOpen(true);
      setPrestamoDevolucionOpen(true);
      return;
    }

    if (id.includes("firma_") || id.startsWith("nombre_")) {
      setIndicacionesOpen(true);
      setFirmasOpen(true);
    }
  };

  const showFormFieldError = (fieldId, message) => {
    setMsg("");
    setFormFieldError(message);
    setFormFieldErrorId(fieldId);
    openSectionForField(fieldId);

    if (formErrorTimerRef.current) clearTimeout(formErrorTimerRef.current);

    setTimeout(() => {
      requestAnimationFrame(() => {
        const wrapEl = formFieldWrapRefs.current[fieldId];
        const inputEl = formFieldRefs.current[fieldId];
        if (wrapEl?.scrollIntoView) {
          wrapEl.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        setTimeout(() => {
          if (inputEl?.focus) inputEl.focus();
        }, 180);
      });
    }, 80);

    formErrorTimerRef.current = setTimeout(() => {
      setFormFieldError("");
      setFormFieldErrorId(null);
      formErrorTimerRef.current = null;
    }, 3000);
  };

  const openSignatureModal = (field) => {
    if (readOnly) return;
    if (formFieldErrorId === field?.id) clearFormFieldError();
    setSignatureModal({ open: true, field });
  };

  const closeSignatureModal = () => setSignatureModal({ open: false, field: null });

  const resizeSignatureCanvas = () => {
    const canvas = signatureCanvasRef.current;
    const wrapper = signatureWrapperRef.current;
    if (!canvas || !wrapper) return;

    const prevData = canvas.toDataURL("image/png");
    const rect = wrapper.getBoundingClientRect();
    const ratio = Math.max(window.devicePixelRatio || 1, 1);

    canvas.width = Math.max(rect.width * ratio, 1);
    canvas.height = Math.max(220 * ratio, 1);
    canvas.style.width = `${rect.width}px`;
    canvas.style.height = "220px";

    const ctx = canvas.getContext("2d");
    ctx.scale(ratio, ratio);
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.lineWidth = 2;
    ctx.strokeStyle = "#111827";
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, rect.width, 220);

    if (prevData && prevData !== "data:,") {
      const img = new Image();
      img.onload = () => ctx.drawImage(img, 0, 0, rect.width, 220);
      img.src = prevData;
    }
  };

  useEffect(() => {
    if (!signatureModal.open) return;
    const t = setTimeout(() => resizeSignatureCanvas(), 0);
    const onResize = () => resizeSignatureCanvas();
    window.addEventListener("resize", onResize);
    return () => {
      clearTimeout(t);
      window.removeEventListener("resize", onResize);
    };
  }, [signatureModal.open]);

  const getCanvasPoint = (event) => {
    const canvas = signatureCanvasRef.current;
    if (!canvas) return { x: 0, y: 0 };
    const rect = canvas.getBoundingClientRect();
    if (event.touches && event.touches.length) {
      return {
        x: event.touches[0].clientX - rect.left,
        y: event.touches[0].clientY - rect.top,
      };
    }
    return { x: event.clientX - rect.left, y: event.clientY - rect.top };
  };

  const startDrawing = (event) => {
    if (readOnly) return;
    const canvas = signatureCanvasRef.current;
    if (!canvas) return;
    drawingRef.current = true;
    const point = getCanvasPoint(event);
    lastPointRef.current = point;
    const ctx = canvas.getContext("2d");
    ctx.beginPath();
    ctx.moveTo(point.x, point.y);
  };

  const draw = (event) => {
    if (!drawingRef.current || readOnly) return;
    const canvas = signatureCanvasRef.current;
    if (!canvas) return;
    const point = getCanvasPoint(event);
    const ctx = canvas.getContext("2d");
    ctx.beginPath();
    ctx.moveTo(lastPointRef.current.x, lastPointRef.current.y);
    ctx.lineTo(point.x, point.y);
    ctx.stroke();
    lastPointRef.current = point;
  };

  const stopDrawing = () => {
    drawingRef.current = false;
  };

  const clearSignatureCanvas = () => {
    if (readOnly) return;
    const canvas = signatureCanvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    const rect = canvas.getBoundingClientRect();
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, rect.width, rect.height);
  };

  const saveSignature = () => {
    const field = signatureModal.field;
    const canvas = signatureCanvasRef.current;
    if (!field || !canvas) return;
    const dataUrl = canvas.toDataURL("image/png");
    setVal(field.id, dataUrl);
    if (formFieldErrorId === field.id) clearFormFieldError();
    setMsg("");
    closeSignatureModal();
  };

  const removeSignature = (fieldId) => {
    if (readOnly) return;
    setVal(fieldId, "");
    if (formFieldErrorId === fieldId) clearFormFieldError();
  };

  const isObservationField = (f) => {
    const id = String(f?.id || "").toLowerCase();
    const label = String(f?.label || f?.text || "").toLowerCase();
    return (
      id.includes("comentarios") ||
      id.includes("notas") ||
      id.includes("observaciones") ||
      label.includes("comentarios") ||
      label.includes("notas") ||
      label.includes("observaciones")
    );
  };

  const isEmptyValue = (value, type) => {
    if (type === "checkbox") return value !== true;
    return value === null || value === undefined || String(value).trim() === "";
  };

  const getCleanFieldLabel = (f) => String(f?.label || f?.text || "").trim();

  const renderBasicInput = (f) => {
    const hasFormError = formFieldErrorId === f.id;
    const commonStyle = {
      width: "100%",
      padding: isMobile ? 11 : 10,
      borderRadius: 12,
      border: hasFormError ? "1px solid #fb923c" : "1px solid #d1d5db",
      background: readOnly ? "#f8fafc" : "#fff",
      fontSize: isMobile ? 16 : 14,
      color: "#111827",
      outline: "none",
      boxSizing: "border-box",
      minHeight: isMobile ? 46 : "auto",
      boxShadow: hasFormError ? "0 0 0 3px rgba(251,146,60,0.12)" : "none",
    };

    if (f.type === "textarea") {
      return (
        <textarea
          ref={(el) => { formFieldRefs.current[f.id] = el; }}
          value={answers[f.id] ?? ""}
          onChange={(e) => {
            setVal(f.id, e.target.value);
            if (formFieldErrorId === f.id) clearFormFieldError();
          }}
          rows={4}
          disabled={readOnly}
          style={{ ...commonStyle, resize: "vertical" }}
        />
      );
    }

    if (f.type === "select" || f.type === "list") {
      const opts = Array.isArray(f.options) ? f.options : [];
      return (
        <select
          ref={(el) => { formFieldRefs.current[f.id] = el; }}
          value={answers[f.id] ?? ""}
          onChange={(e) => {
            setVal(f.id, e.target.value);
            if (formFieldErrorId === f.id) clearFormFieldError();
          }}
          disabled={readOnly}
          style={commonStyle}
        >
          <option value="">-- Selecciona --</option>
          {opts.map((opt) => <option key={opt} value={opt}>{opt}</option>)}
        </select>
      );
    }

    if (f.type === "radio") {
      const opts = Array.isArray(f.options) ? f.options : [];
      return (
        <div style={{ display: "grid", gap: 8 }}>
          {opts.length ? opts.map((opt) => (
            <label
              key={opt}
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                padding: isMobile ? "10px 12px" : 0,
                borderRadius: isMobile ? 12 : 0,
                border: isMobile ? "1px solid #e5e7eb" : "none",
                background: isMobile ? "#fff" : "transparent",
              }}
            >
              <input
                ref={(el) => {
                  if (el && answers[f.id] === opt) formFieldRefs.current[f.id] = el;
                  else if (el && !formFieldRefs.current[f.id]) formFieldRefs.current[f.id] = el;
                }}
                type="radio"
                name={f.id}
                value={opt}
                checked={answers[f.id] === opt}
                onChange={(e) => {
                  setVal(f.id, e.target.value);
                  if (formFieldErrorId === f.id) clearFormFieldError();
                }}
                disabled={readOnly}
              />
              <span>{opt}</span>
            </label>
          )) : <div style={{ fontSize: 12, opacity: 0.7 }}>Sin opciones</div>}
        </div>
      );
    }

    if (f.type === "signature") {
      const value = answers[f.id] || "";
      const signatureSrc =
        typeof value === "string" && value.startsWith("data:image")
          ? value
          : typeof value === "string" && value.startsWith("/storage/")
          ? value
          : typeof value === "string" && value !== ""
          ? `/storage/${String(value).replace(/^\/+/, "")}`
          : "";

      return (
        <div style={{ display: "grid", gap: 10 }}>
          {signatureSrc ? (
            <div
              style={{
                border: hasFormError ? "1px solid #fb923c" : "1px solid #d1d5db",
                borderRadius: 12,
                background: "#fff",
                padding: 10,
                boxShadow: hasFormError ? "0 0 0 3px rgba(251,146,60,0.12)" : "none",
              }}
            >
              <img
                src={signatureSrc}
                alt="Firma capturada"
                style={{ display: "block", maxWidth: "100%", maxHeight: 140, objectFit: "contain", margin: "0 auto" }}
              />
            </div>
          ) : (
            <div
              style={{
                border: hasFormError ? "1px solid #fb923c" : "1px dashed #cbd5e1",
                borderRadius: 12,
                background: "#f8fafc",
                padding: isMobile ? 14 : 16,
                textAlign: "center",
                color: "#64748b",
                fontSize: 14,
                boxShadow: hasFormError ? "0 0 0 3px rgba(251,146,60,0.12)" : "none",
              }}
            >
              Sin firma capturada
            </div>
          )}

          {!readOnly ? (
            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button
                ref={(el) => { formFieldRefs.current[f.id] = el; }}
                type="button"
                onClick={() => openSignatureModal(f)}
                style={{
                  borderRadius: 12,
                  border: hasFormError ? "1px solid #fb923c" : "1px solid #c7d2fe",
                  background: hasFormError ? "#fff7ed" : "#eef2ff",
                  color: hasFormError ? "#9a3412" : "#1e40af",
                  padding: isMobile ? "10px 14px" : "10px 12px",
                  cursor: "pointer",
                  fontWeight: 800,
                  fontSize: 14,
                  boxShadow: hasFormError ? "0 0 0 3px rgba(251,146,60,0.12)" : "none",
                }}
              >
                {value ? "Volver a firmar" : "Capturar firma"}
              </button>

              {value ? (
                <button
                  type="button"
                  onClick={() => removeSignature(f.id)}
                  style={{
                    borderRadius: 12,
                    border: "1px solid #fecaca",
                    background: "#fef2f2",
                    color: "#b91c1c",
                    padding: isMobile ? "10px 14px" : "10px 12px",
                    cursor: "pointer",
                    fontWeight: 800,
                    fontSize: 14,
                  }}
                >
                  Eliminar firma
                </button>
              ) : null}
            </div>
          ) : null}
        </div>
      );
    }

    const htmlType = f.type === "number" ? "number" : f.type === "date" ? "date" : f.type === "datetime" ? "datetime-local" : "text";
    return (
      <input
        ref={(el) => { formFieldRefs.current[f.id] = el; }}
        type={htmlType}
        value={answers[f.id] ?? ""}
        onChange={(e) => {
          setVal(f.id, e.target.value);
          if (formFieldErrorId === f.id) clearFormFieldError();
        }}
        disabled={readOnly}
        style={commonStyle}
      />
    );
  };

  const renderField = (f) => {
    if (f.type === "fixed_image") {
      const url = normalizeAssetUrl(f.url || "");
      if (!url) return null;
      return (
        <div style={{ textAlign: "center" }}>
          <img
            src={url}
            alt={f.label || "Imagen"}
            style={{ maxWidth: "100%", width: isMobile ? "100%" : 520, borderRadius: 10 }}
          />
        </div>
      );
    }
    return renderBasicInput(f);
  };

  const logo = getField("encabezado_logo");
  const headerLines = [
    getField("header_line_1"),
    getField("header_line_2"),
    getField("header_line_3"),
    getField("header_line_4"),
    getField("header_line_5"),
    getField("header_line_6"),
  ].filter(Boolean);

  const codigo = getField("codigo");
  const noSerie = getField("no_serie");
  const separacionIndicacionesLlenado = getField("separacion_indicaciones_llenado");
  const imagenPrensas = getField("imagen_prensas");
  const tipo = getField("tipo");
  const corriente = getField("corriente");
  const tallerOrigen = getField("taller_origen");
  const tallerSolicita = getField("taller_solicita");
  const accionRealizar = getField("accion_realizar");
  const textoEstado = getField("texto_estado");
  const notas = getField("notas");
  const tablaEquiposMedicion = getField("tabla_equipos_medicion");
  const nombreEntregaPrensa = getField("nombre_entrega_prensa");
  const firmaEntregaPrensa = getField("firma_entrega_prensa");
  const nombreRecibePrensa = getField("nombre_recibe_prensa");
  const firmaRecibePrensa = getField("firma_recibe_prensa");
  const nombreInspeccionaMantenimiento = getField("nombre_inspecciona_mantenimiento");
  const firmaInspeccionaMantenimiento = getField("firma_inspecciona_mantenimiento");

  const prensaCriteria = [
    { baseId: "caja_control_cabezas_control", title: "CAJA DE CONTROL Y/O CABEZAS DE CONTROL" },
    { baseId: "plato_superior", title: "PLATO SUPERIOR" },
    { baseId: "plato_inferior", title: "PLATO INFERIOR" },
    { baseId: "termostato_superior", title: "TERMOSTATO SUPERIOR" },
    { baseId: "termostato_inferior", title: "TERMOSTATO INFERIOR" },
    { baseId: "termometro_plato_superior", title: "TERMOMETRO PLATO SUPERIOR" },
    { baseId: "termometro_plato_inferior", title: "TERMOMETRO PLATO INFERIOR" },
    { baseId: "cable_maestro_plato_superior", title: "CABLE MAESTRO PLATO SUPERIOR" },
    { baseId: "cable_maestro_plato_inferior", title: "CABLE MAESTRO PLATO INFERIOR" },
    { baseId: "ploga_alimentacion_principal", title: "PLOGA DE ALIMENTACIÓN PRINCIPAL" },
    { baseId: "extension_alimentacion_principal", title: "EXTENSIÓN DE ALIMENTACIÓN PRINCIPAL" },
    { baseId: "puente_interconector", title: "PUENTE INTERCONECTOR" },
    { baseId: "camara_presion", title: "CAMARA DE PRESIÓN" },
    { baseId: "acople_rapido", title: "ACOPLE RAPIDO" },
    { baseId: "verificador_presion", title: "VERIFICADOR DE PRESIÓN" },
    { baseId: "manguera_llenado", title: "MANGUERA DE LLENADO" },
    { baseId: "tornillos_pernos", title: "TORNILLOS Y/O PERNOS" },
    { baseId: "platos_compensadores_calor", title: "PLATOS COMPENSADORES DE CALOR" },
    { baseId: "rieles", title: "RIELES" },
    { baseId: "mangueras_enfriamiento", title: "MANGUERAS PARA ENFRIAMIENTO" },
    { baseId: "seguros_rieles", title: "SEGUROS DE RIELES" },
    { baseId: "sistema_presion_bomba_compresor", title: "SISTEMA DE PRESIÓN: BOMBA / COMPRESOR" },
  ];

  const topButtonStyle = {
    borderRadius: 12,
    border: "1px solid #d1d5db",
    background: "#fff",
    color: "#111827",
    padding: isMobile ? "10px 14px" : "10px 14px",
    cursor: "pointer",
    fontWeight: 700,
    fontSize: 14,
    boxShadow: "0 1px 2px rgba(0,0,0,0.04)",
  };

  const primaryButtonStyle = {
    borderRadius: 12,
    border: "1px solid #c7d2fe",
    background: "#2563eb",
    color: "#fff",
    padding: isMobile ? "11px 16px" : "12px 18px",
    cursor: "pointer",
    fontWeight: 800,
    fontSize: 14,
    boxShadow: "0 8px 18px rgba(37,99,235,0.18)",
  };

  const fieldBlockStyle = {
    display: "grid",
    gap: isMobile ? 8 : 6,
    padding: isMobile ? "12px 12px 10px" : 0,
    borderRadius: isMobile ? 14 : 0,
    background: isMobile ? "#fafafa" : "transparent",
    border: isMobile ? "1px solid #eef2f7" : "none",
  };

  const getOuterFieldBlockStyle = (fieldId) => {
    const hasError = formFieldErrorId === fieldId;
    return {
      ...fieldBlockStyle,
      padding: hasError ? "10px" : fieldBlockStyle.padding,
      borderRadius: hasError ? 12 : fieldBlockStyle.borderRadius,
      background: hasError ? "#fff7ed" : fieldBlockStyle.background,
      border: hasError ? "1px solid #fdba74" : fieldBlockStyle.border,
      transition: "all 0.2s ease",
    };
  };

  const renderDivider = (key = null) => (
    <div key={key} style={{ borderBottom: "1px solid #d1d5db", margin: "4px 0 0 0" }} />
  );

  const renderOuterField = (f, overrideLabel = null) => {
    if (!f) return null;
    if (f.type === "static_text") {
      return (
        <div key={f.id} style={{ color: "#111827", fontSize: 14, lineHeight: 1.5, fontWeight: 700 }}>
          {f.text || f.label}
        </div>
      );
    }

    const isRequiredVisual = !!f.required && !isObservationField(f);
    const label = overrideLabel || getCleanFieldLabel(f);

    return (
      <div
        key={f.id}
        ref={(el) => { formFieldWrapRefs.current[f.id] = el; }}
        style={getOuterFieldBlockStyle(f.id)}
      >
        <label style={{ fontSize: 14, color: "#0f172a", lineHeight: 1.4 }}>
          <b>{label}</b> {isRequiredVisual ? <span style={{ color: "crimson" }}>*</span> : null}
        </label>

        {formFieldErrorId === f.id && formFieldError ? (
          <div
            style={{
              borderRadius: 10,
              border: "1px solid #fdba74",
              background: "#fff7ed",
              color: "#9a3412",
              padding: "8px 10px",
              fontSize: 13,
              lineHeight: 1.4,
              fontWeight: 700,
            }}
          >
            {formFieldError}
          </div>
        ) : null}

        {renderField(f)}
      </div>
    );
  };

  const nestedSectionStyle = {
    border: "1px solid #dbe4ee",
    borderRadius: 18,
    background: "#f8fafc",
    overflow: "hidden",
    boxShadow: "0 6px 18px rgba(15,23,42,0.04)",
  };

  const nestedSectionHeaderStyle = {
    padding: isMobile ? "13px 14px" : "15px 18px",
    background: "#eef2f7",
    borderBottom: "1px solid #dbe4ee",
    fontWeight: 900,
    color: "#0f172a",
    fontSize: isMobile ? 15 : 16,
    lineHeight: 1.35,
    textTransform: "uppercase",
    letterSpacing: 0.2,
  };

  const renderCollapsibleSection = (title, isOpen, setIsOpen, children) => (
    <div style={nestedSectionStyle}>
      <button
        type="button"
        onClick={() => setIsOpen((prev) => !prev)}
        style={{
          ...nestedSectionHeaderStyle,
          width: "100%",
          border: "none",
          borderBottom: isOpen ? "1px solid #dbe4ee" : "none",
          cursor: "pointer",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          gap: 12,
          textAlign: "left",
        }}
      >
        <span>{title}</span>
        <span
          aria-hidden="true"
          style={{
            fontSize: 18,
            lineHeight: 1,
            transform: isOpen ? "rotate(180deg)" : "rotate(0deg)",
            transition: "transform 0.2s ease",
          }}
        >
          ▾
        </span>
      </button>

      {isOpen ? (
        <div style={{ padding: isMobile ? 12 : 14, display: "grid", gap: isMobile ? 12 : 14 }}>
          {children}
        </div>
      ) : null}
    </div>
  );

  const renderCriterionBox = (baseId, title) => {
    const estado = getField(`${baseId}_estado`);
    const cantidad = getField(`${baseId}_cantidad`);
    const comentarios = getField(`${baseId}_comentarios`);

    return (
      <div
        key={baseId}
        style={{
          border: "1px solid #e5e7eb",
          borderRadius: 14,
          background: "#fbfdff",
          padding: isMobile ? 12 : 14,
          display: "grid",
          gap: 12,
        }}
      >
        <div
          style={{
            padding: isMobile ? "12px 14px" : "13px 16px",
            background: "#ffffff",
            border: "1px solid #e5e7eb",
            borderRadius: 12,
            fontWeight: 900,
            color: "#0f172a",
            fontSize: isMobile ? 14 : 15,
            lineHeight: 1.4,
          }}
        >
          {title}
        </div>

        {estado ? renderOuterField({ ...estado, label: title }) : null}
        {estado && cantidad ? renderDivider(`${baseId}_estado_divider`) : null}
        {cantidad ? renderOuterField({ ...cantidad, label: "Cantidad (pza)" }) : null}
        {cantidad && comentarios ? renderDivider(`${baseId}_cantidad_divider`) : null}
        {comentarios ? renderOuterField({ ...comentarios, label: "Comentarios" }) : null}
      </div>
    );
  };

  const renderEquipmentTable = () => {
    const rows = getEquipmentRows();

    return (
      <div
        ref={(el) => {
          formFieldWrapRefs.current.tabla_equipos_medicion = el;
        }}
        style={{ display: "grid", gap: 12 }}
      >
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            gap: 12,
            flexWrap: "wrap",
          }}
        >
          <div>
            <div style={{ fontSize: 14, fontWeight: 900, color: "#0f172a" }}>
              {tablaEquiposMedicion?.label || "Equipos de medición"}
            </div>
            <div style={{ marginTop: 4, fontSize: 12, color: "#64748b", lineHeight: 1.5 }}>
              Puedes agregar, editar o eliminar varios equipos.
            </div>
          </div>

          {!readOnly ? (
            <button
              type="button"
              onClick={() => openEquipmentModal(null)}
              style={{
                borderRadius: 12,
                border: "1px solid #c7d2fe",
                background: "#eef2ff",
                color: "#1e40af",
                padding: "10px 14px",
                cursor: "pointer",
                fontWeight: 800,
                fontSize: 14,
              }}
            >
              + Agregar equipo
            </button>
          ) : null}
        </div>

        {rows.length === 0 ? (
          <div
            style={{
              border: "1px dashed #cbd5e1",
              borderRadius: 12,
              background: "#f8fafc",
              padding: 16,
              textAlign: "center",
              color: "#64748b",
              fontSize: 14,
            }}
          >
            Sin equipos de medición registrados.
          </div>
        ) : isMobile ? (
          <div style={{ display: "grid", gap: 12 }}>
            {rows.map((row, index) => (
              <div
                key={`equipo_${index}`}
                style={{
                  border: "1px solid #dbe4ee",
                  borderRadius: 14,
                  background: "#fff",
                  padding: 14,
                  display: "grid",
                  gap: 8,
                }}
              >
                <div style={{ fontWeight: 900, color: "#0f172a" }}>
                  Equipo #{index + 1}
                </div>
                <div style={{ fontSize: 13 }}><b>Cantidad:</b> {row?.cantidad ?? "—"}</div>
                <div style={{ fontSize: 13 }}><b>Nombre:</b> {row?.nombre_equipo || "—"}</div>
                <div style={{ fontSize: 13 }}><b>Número de serie:</b> {row?.numero_serie || "—"}</div>
                <div style={{ fontSize: 13, whiteSpace: "pre-line" }}>
                  <b>Observaciones:</b> {row?.observaciones || "—"}
                </div>

                {!readOnly ? (
                  <div style={{ display: "flex", gap: 8, flexWrap: "wrap", marginTop: 4 }}>
                    <button
                      type="button"
                      onClick={() => openEquipmentModal(index)}
                      style={{
                        borderRadius: 10,
                        border: "1px solid #bfdbfe",
                        background: "#eff6ff",
                        color: "#1d4ed8",
                        padding: "8px 12px",
                        cursor: "pointer",
                        fontWeight: 800,
                      }}
                    >
                      Editar
                    </button>

                    <button
                      type="button"
                      onClick={() => removeEquipmentRow(index)}
                      style={{
                        borderRadius: 10,
                        border: "1px solid #fecaca",
                        background: "#fef2f2",
                        color: "#b91c1c",
                        padding: "8px 12px",
                        cursor: "pointer",
                        fontWeight: 800,
                      }}
                    >
                      Eliminar
                    </button>
                  </div>
                ) : null}
              </div>
            ))}
          </div>
        ) : (
          <div style={{ width: "100%", overflowX: "auto", border: "1px solid #dbe4ee", borderRadius: 12 }}>
            <table
              style={{
                width: "100%",
                minWidth: 700,
                borderCollapse: "collapse",
                tableLayout: "fixed",
                fontSize: 13,
              }}
            >
              <thead>
                <tr style={{ background: "#eef2f7" }}>
                  <th style={{ borderBottom: "1px solid #dbe4ee", padding: 10 }}>Cantidad</th>
                  <th style={{ borderBottom: "1px solid #dbe4ee", padding: 10 }}>Nombre del equipo</th>
                  <th style={{ borderBottom: "1px solid #dbe4ee", padding: 10 }}>Número de serie</th>
                  <th style={{ borderBottom: "1px solid #dbe4ee", padding: 10 }}>Observaciones</th>
                  {!readOnly ? (
                    <th style={{ width: 150, borderBottom: "1px solid #dbe4ee", padding: 10 }}>Acciones</th>
                  ) : null}
                </tr>
              </thead>

              <tbody>
                {rows.map((row, index) => (
                  <tr key={`equipo_${index}`}>
                    <td style={{ borderBottom: "1px solid #e5e7eb", padding: 10, textAlign: "center", verticalAlign: "top" }}>
                      {row?.cantidad ?? ""}
                    </td>
                    <td style={{ borderBottom: "1px solid #e5e7eb", padding: 10, verticalAlign: "top" }}>
                      {row?.nombre_equipo || ""}
                    </td>
                    <td style={{ borderBottom: "1px solid #e5e7eb", padding: 10, verticalAlign: "top" }}>
                      {row?.numero_serie || ""}
                    </td>
                    <td style={{ borderBottom: "1px solid #e5e7eb", padding: 10, whiteSpace: "pre-line", verticalAlign: "top" }}>
                      {row?.observaciones || ""}
                    </td>

                    {!readOnly ? (
                      <td style={{ borderBottom: "1px solid #e5e7eb", padding: 10, verticalAlign: "top" }}>
                        <div style={{ display: "flex", justifyContent: "center", gap: 8, flexWrap: "wrap" }}>
                          <button
                            type="button"
                            onClick={() => openEquipmentModal(index)}
                            style={{
                              borderRadius: 9,
                              border: "1px solid #bfdbfe",
                              background: "#eff6ff",
                              color: "#1d4ed8",
                              padding: "7px 10px",
                              cursor: "pointer",
                              fontWeight: 800,
                            }}
                          >
                            Editar
                          </button>
                          <button
                            type="button"
                            onClick={() => removeEquipmentRow(index)}
                            style={{
                              borderRadius: 9,
                              border: "1px solid #fecaca",
                              background: "#fef2f2",
                              color: "#b91c1c",
                              padding: "7px 10px",
                              cursor: "pointer",
                              fontWeight: 800,
                            }}
                          >
                            Eliminar
                          </button>
                        </div>
                      </td>
                    ) : null}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    );
  };

  const validateSimpleRequiredField = (field, emptyMessage = null) => {
    if (!field) return true;
    const value = answers[field.id];
    if (field.type === "static_text" || field.type === "fixed_image") return true;

    if (field.type === "signature") {
      if (!value || String(value).trim() === "") {
        openSectionForField(field.id);
        showFormFieldError(field.id, emptyMessage || `Falta responder: ${field.label}`);
        return false;
      }
      return true;
    }

    if (isEmptyValue(value, field.type)) {
      openSectionForField(field.id);
      showFormFieldError(field.id, emptyMessage || `Falta responder: ${field.label}`);
      return false;
    }

    if (field.type === "select" || field.type === "list" || field.type === "radio") {
      const opts = Array.isArray(field.options) ? field.options : [];
      if (opts.length && !opts.includes(value)) {
        openSectionForField(field.id);
        showFormFieldError(field.id, `Selecciona una opción válida para: ${field.label}`);
        return false;
      }
    }

    return true;
  };

  const validateBeforeSubmit = () => {
    clearFormFieldError();
    setMsg("");

    const fieldsToValidate = fields.filter((f) => {
      if (!f?.id) return false;
      if (f.type === "static_text" || f.type === "fixed_image") return false;
      if (isObservationField(f)) return false;
      return !!f.required;
    });

    for (const field of fieldsToValidate) {
      if (!validateSimpleRequiredField(field)) return false;
    }

    setMsg("");
    return true;
  };

  const handleSubmit = (e) => {
    if (readOnly) {
      e.preventDefault();
      return;
    }
    const ok = validateBeforeSubmit();
    if (!ok) {
      e.preventDefault();
      return;
    }
    onSubmit?.(e);
  };

  const getModeBadge = () => {
    if (readOnly) return "Modo lectura";
    if (isEditing) return "Edición de registro";
    return "Captura de formulario";
  };

  const getModeTitle = () => {
    if (readOnly) return "Ver respuesta";
    if (isEditing) return `Editar: ${form?.title || "Formulario"}`;
    return form?.title || "Formulario";
  };

  const getModeDescription = () => {
    if (readOnly) return "Consulta la información registrada en este formulario.";
    if (isEditing) return "Modifica la información necesaria y actualiza el registro cuando termines.";
    return "Completa la información solicitada y guarda el registro cuando termines.";
  };

  const getFooterStateText = () => {
    if (saving) return isEditing ? "Actualizando registro..." : "Guardando registro...";
    if (isEditing) return isOnline ? "Listo para actualizar" : "Sin conexión para actualizar";
    return isOnline ? "Listo para enviar" : "Listo para guardar offline";
  };

  const getSubmitText = () => {
    if (saving) return isEditing ? "Actualizando..." : "Guardando...";
    if (isEditing) return "Actualizar registro";
    return isOnline ? "Enviar formulario" : "Guardar offline";
  };

  const desktopContentWidth = 900;

  return (
    <div
      ref={topRef}
      style={{ minHeight: "100%", background: "#f8fafc", padding: isMobile ? "8px 8px 22px" : "14px 14px 28px" }}
    >
      <div style={{ maxWidth: isMobile ? 980 : desktopContentWidth, width: isMobile ? "95%" : "100%", margin: "0 auto" }}>
        <div
          style={{
            background: "#ffffff",
            border: "1px solid #e5e7eb",
            borderRadius: isMobile ? 16 : 18,
            padding: isMobile ? 16 : 18,
            marginBottom: isMobile ? 12 : 16,
            boxShadow: "0 6px 24px rgba(15,23,42,0.05)",
          }}
        >
          <div style={{ display: "flex", justifyContent: "space-between", gap: 14, alignItems: "flex-start", flexWrap: "wrap" }}>
            <div style={{ display: "grid", gap: 8 }}>
              <div
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  fontSize: 12,
                  fontWeight: 800,
                  color: readOnly ? "#7c3aed" : isEditing ? "#166534" : "#2563eb",
                  background: readOnly ? "#f5f3ff" : isEditing ? "#ecfdf5" : "#eff6ff",
                  border: `1px solid ${readOnly ? "#ddd6fe" : isEditing ? "#86efac" : "#bfdbfe"}`,
                  borderRadius: 999,
                  padding: "6px 10px",
                  width: "fit-content",
                }}
              >
                <span
                  style={{
                    width: 8,
                    height: 8,
                    borderRadius: 999,
                    background: readOnly ? "#7c3aed" : isEditing ? "#16a34a" : "#2563eb",
                    display: "inline-block",
                  }}
                />
                {getModeBadge()}
              </div>

              <div>
                <h2 style={{ margin: 0, fontSize: isMobile ? 18 : 24, lineHeight: isMobile ? 1.25 : 1.15, color: "#0f172a" }}>
                  {getModeTitle()}
                </h2>
                <div style={{ marginTop: 8, fontSize: 13, color: "#64748b", lineHeight: 1.6, maxWidth: 760 }}>
                  {getModeDescription()}
                </div>
                {readOnly && responseMeta ? (
                  <div style={{ marginTop: 10, fontSize: 12, color: "#64748b", lineHeight: 1.6 }}>
                    Respuesta #{responseMeta.id || "—"} • Usuario: {responseMeta.user_id || "—"} • Fecha:{" "}
                    {responseMeta.created_at ? new Date(responseMeta.created_at).toLocaleString() : "—"}
                  </div>
                ) : null}
              </div>
            </div>

            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button type="button" onClick={onBack} style={topButtonStyle}>← Volver</button>
            </div>
          </div>

          {msg ? (
            <div
              style={{
                marginTop: 14,
                borderRadius: 14,
                border: "1px solid #fde68a",
                background: "#fffbeb",
                color: "#92400e",
                padding: "12px 14px",
                whiteSpace: "pre-line",
                fontSize: 13,
                lineHeight: 1.6,
              }}
            >
              {msg}
            </div>
          ) : null}

          {!isOnline && !readOnly && !isEditing ? (
            <div
              style={{
                marginTop: 14,
                borderRadius: 14,
                border: "1px solid #fed7aa",
                background: "#fff7ed",
                color: "#9a3412",
                padding: "12px 14px",
                fontSize: 13,
                lineHeight: 1.6,
              }}
            >
              Estás en <b>modo offline</b>. Al enviar, el registro se guardará en el dispositivo y se sincronizará automáticamente después.
            </div>
          ) : null}

          {!isOnline && isEditing ? (
            <div
              style={{
                marginTop: 14,
                borderRadius: 14,
                border: "1px solid #fecaca",
                background: "#fff7ed",
                color: "#9a3412",
                padding: "12px 14px",
                fontSize: 13,
                lineHeight: 1.6,
              }}
            >
              Estás sin conexión. La edición de registros existentes requiere internet para guardar cambios.
            </div>
          ) : null}
        </div>

        <form onSubmit={handleSubmit}>
          <div
            style={{
              maxWidth: isMobile ? "100%" : desktopContentWidth,
              marginInline: "auto",
              background: "#fff",
              border: "1px solid #dbe4ee",
              borderRadius: isMobile ? 16 : 18,
              padding: isMobile ? 16 : 22,
              display: "grid",
              gap: isMobile ? 16 : 16,
              boxShadow: "0 8px 28px rgba(15,23,42,0.06)",
            }}
          >
            {logo ? (
              <div style={{ display: "flex", justifyContent: "center", paddingBottom: isMobile ? 4 : 0 }}>
                <div style={{ width: "100%", maxWidth: isMobile ? 250 : 420, textAlign: "center" }}>{renderField(logo)}</div>
              </div>
            ) : null}

            <div style={{ display: "grid", gap: isMobile ? 10 : 6, textAlign: "left" }}>
              {headerLines.map((line) => (
                <div key={line.id} style={{ width: "100%", fontWeight: 700, fontSize: isMobile ? 12 : 14, color: "#111827", textAlign: "left", lineHeight: isMobile ? 1.45 : 1.35 }}>
                  {line.text}
                </div>
              ))}
            </div>

            {renderDivider("header_divider")}

            {codigo ? renderOuterField(codigo) : null}
            {codigo ? renderDivider("codigo_divider") : null}
            {noSerie ? renderOuterField(noSerie) : null}
            {noSerie ? renderDivider("no_serie_divider") : null}

            {renderCollapsibleSection(
              separacionIndicacionesLlenado?.text || separacionIndicacionesLlenado?.label || "INDICACIONES DE LLENADO",
              indicacionesOpen,
              setIndicacionesOpen,
              <>
                {renderCollapsibleSection(
                  "MARCAR TIPO DE PRENSA A REVISIÓN Y TIPO DE CORRIENTE",
                  tipoPrensaOpen,
                  setTipoPrensaOpen,
                  <>
                    {imagenPrensas ? (
                      <div style={{ display: "flex", justifyContent: "center", marginTop: 4, marginBottom: 6 }}>
                        <img
                          src={normalizeAssetUrl(imagenPrensas.url || "/images/forms/SGI_POP_GT_01_FO_09_Checklist_de_Prensas/prensas.png")}
                          alt="Prensas"
                          style={{
                            maxWidth: "100%",
                            width: isMobile ? "100%" : 520,
                            height: "auto",
                            border: "1px solid #dbe4ee",
                            borderRadius: 12,
                            objectFit: "contain",
                          }}
                        />
                      </div>
                    ) : null}
                    {imagenPrensas ? renderDivider("imagen_prensas_divider") : null}
                    {tipo ? renderOuterField(tipo) : null}
                    {tipo ? renderDivider("tipo_divider") : null}
                    {corriente ? renderOuterField(corriente) : null}
                  </>
                )}

                {renderCollapsibleSection(
                  "ESPECIFICAR UNIDAD DE SERVICIO",
                  unidadServicioOpen,
                  setUnidadServicioOpen,
                  <>
                    {tallerOrigen ? renderOuterField(tallerOrigen) : null}
                    {tallerOrigen ? renderDivider("taller_origen_divider") : null}
                    {tallerSolicita ? renderOuterField(tallerSolicita) : null}
                    {tallerSolicita ? renderDivider("taller_solicita_divider") : null}
                    {accionRealizar ? renderOuterField(accionRealizar) : null}
                    {accionRealizar ? renderDivider("accion_realizar_divider") : null}
                    {textoEstado ? renderOuterField(textoEstado) : null}
                    {textoEstado ? renderDivider("texto_estado_divider") : null}
                    {prensaCriteria.map((item) => renderCriterionBox(item.baseId, item.title))}
                    {notas ? renderOuterField({ ...notas, label: "Notas" }) : null}
                  </>
                )}

                {renderCollapsibleSection(
                  "EN CASO DE PRESTAMO O DEVOLUCIÓN: INTEGRAR LOS DATOS DEL EQUIPO DE MEDICIÓN COMO COMPLEMENTO DE LA PRENSA",
                  prestamoDevolucionOpen,
                  setPrestamoDevolucionOpen,
                  <>
                    {tablaEquiposMedicion ? renderEquipmentTable() : null}
                  </>
                )}

                {renderCollapsibleSection(
                  "COLOCAR FIRMAS QUE CORRESPONDAN",
                  firmasOpen,
                  setFirmasOpen,
                  <>
                    {nombreEntregaPrensa ? renderOuterField(nombreEntregaPrensa) : null}
                    {nombreEntregaPrensa ? renderDivider("nombre_entrega_prensa_divider") : null}
                    {firmaEntregaPrensa ? renderOuterField(firmaEntregaPrensa) : null}
                    {firmaEntregaPrensa ? renderDivider("firma_entrega_prensa_divider") : null}
                    {nombreRecibePrensa ? renderOuterField(nombreRecibePrensa) : null}
                    {nombreRecibePrensa ? renderDivider("nombre_recibe_prensa_divider") : null}
                    {firmaRecibePrensa ? renderOuterField(firmaRecibePrensa) : null}
                    {firmaRecibePrensa ? renderDivider("firma_recibe_prensa_divider") : null}
                    {nombreInspeccionaMantenimiento ? renderOuterField(nombreInspeccionaMantenimiento) : null}
                    {nombreInspeccionaMantenimiento ? renderDivider("nombre_inspecciona_mantenimiento_divider") : null}
                    {firmaInspeccionaMantenimiento ? renderOuterField(firmaInspeccionaMantenimiento) : null}
                  </>
                )}
              </>
            )}
          </div>

          {!readOnly ? (
            <div style={{ position: "sticky", bottom: isMobile ? 8 : 12, zIndex: 20, marginTop: isMobile ? 16 : 18 }}>
              <div
                style={{
                  maxWidth: isMobile ? "100%" : desktopContentWidth,
                  width: "100%",
                  marginInline: "auto",
                  background: "rgba(255,255,255,0.92)",
                  backdropFilter: "blur(10px)",
                  border: "1px solid #dbe4ee",
                  borderRadius: isMobile ? 16 : 18,
                  padding: 14,
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  gap: 14,
                  flexWrap: "wrap",
                  boxShadow: "0 10px 30px rgba(15,23,42,0.08)",
                }}
              >
                <div style={{ display: "grid", gap: 5 }}>
                  <div style={{ fontSize: 13, fontWeight: 800, color: "#0f172a" }}>{getFooterStateText()}</div>
                  <div style={{ fontSize: 12, color: "#64748b", lineHeight: 1.5 }}>Revisa la información antes de continuar.</div>
                </div>

                <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                  <button type="button" onClick={onBack} style={topButtonStyle}>Volver</button>
                  <button
                    type="submit"
                    disabled={saving || (isEditing && !isOnline)}
                    style={{
                      ...primaryButtonStyle,
                      opacity: saving || (isEditing && !isOnline) ? 0.7 : 1,
                      cursor: saving || (isEditing && !isOnline) ? "not-allowed" : "pointer",
                    }}
                  >
                    {getSubmitText()}
                  </button>
                </div>
              </div>
            </div>
          ) : null}
        </form>
      </div>

      {!readOnly && equipmentModal.open ? (
        <div
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(0,0,0,0.45)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
            zIndex: 1080,
          }}
          onClick={closeEquipmentModal}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: "100%",
              maxWidth: 720,
              maxHeight: "90vh",
              overflowY: "auto",
              background: "#fff",
              borderRadius: 14,
              border: "1px solid #d1d5db",
            }}
          >
            <div
              style={{
                padding: "14px 16px",
                borderBottom: "1px solid #e5e7eb",
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                gap: 10,
              }}
            >
              <h3 style={{ margin: 0 }}>
                {equipmentModal.editIndex === null || equipmentModal.editIndex === undefined
                  ? "Agregar equipo de medición"
                  : "Editar equipo de medición"}
              </h3>
              <button
                type="button"
                onClick={closeEquipmentModal}
                style={{
                  border: "1px solid #e5e7eb",
                  background: "#fff",
                  borderRadius: 8,
                  width: 34,
                  height: 34,
                  cursor: "pointer",
                }}
              >
                ✕
              </button>
            </div>

            <div style={{ padding: 16, display: "grid", gap: 14 }}>
              {equipmentError ? (
                <div
                  style={{
                    borderRadius: 10,
                    border: "1px solid #fdba74",
                    background: "#fff7ed",
                    color: "#9a3412",
                    padding: "10px 12px",
                    fontSize: 13,
                    fontWeight: 700,
                  }}
                >
                  {equipmentError}
                </div>
              ) : null}

              <label style={{ display: "grid", gap: 6 }}>
                <b style={{ fontSize: 14 }}>Cantidad</b>
                <input
                  type="number"
                  min="0"
                  value={equipmentDraft.cantidad}
                  onChange={(e) => updateEquipmentDraft("cantidad", e.target.value)}
                  style={{
                    width: "100%",
                    padding: 10,
                    borderRadius: 12,
                    border: "1px solid #d1d5db",
                    fontSize: isMobile ? 16 : 14,
                    boxSizing: "border-box",
                  }}
                />
              </label>

              <label style={{ display: "grid", gap: 6 }}>
                <b style={{ fontSize: 14 }}>Nombre del equipo</b>
                <input
                  type="text"
                  value={equipmentDraft.nombre_equipo}
                  onChange={(e) => updateEquipmentDraft("nombre_equipo", e.target.value)}
                  style={{
                    width: "100%",
                    padding: 10,
                    borderRadius: 12,
                    border: "1px solid #d1d5db",
                    fontSize: isMobile ? 16 : 14,
                    boxSizing: "border-box",
                  }}
                />
              </label>

              <label style={{ display: "grid", gap: 6 }}>
                <b style={{ fontSize: 14 }}>Número de serie</b>
                <input
                  type="text"
                  value={equipmentDraft.numero_serie}
                  onChange={(e) => updateEquipmentDraft("numero_serie", e.target.value)}
                  style={{
                    width: "100%",
                    padding: 10,
                    borderRadius: 12,
                    border: "1px solid #d1d5db",
                    fontSize: isMobile ? 16 : 14,
                    boxSizing: "border-box",
                  }}
                />
              </label>

              <label style={{ display: "grid", gap: 6 }}>
                <b style={{ fontSize: 14 }}>Observaciones</b>
                <textarea
                  rows={4}
                  value={equipmentDraft.observaciones}
                  onChange={(e) => updateEquipmentDraft("observaciones", e.target.value)}
                  style={{
                    width: "100%",
                    padding: 10,
                    borderRadius: 12,
                    border: "1px solid #d1d5db",
                    fontSize: isMobile ? 16 : 14,
                    boxSizing: "border-box",
                    resize: "vertical",
                  }}
                />
              </label>
            </div>

            <div
              style={{
                padding: 16,
                borderTop: "1px solid #e5e7eb",
                display: "flex",
                justifyContent: "flex-end",
                gap: 10,
                flexWrap: "wrap",
              }}
            >
              <button type="button" onClick={closeEquipmentModal} style={topButtonStyle}>
                Cancelar
              </button>
              <button type="button" onClick={saveEquipmentRow} style={primaryButtonStyle}>
                {equipmentModal.editIndex === null || equipmentModal.editIndex === undefined
                  ? "Agregar equipo"
                  : "Guardar cambios"}
              </button>
            </div>
          </div>
        </div>
      ) : null}

      {!readOnly && signatureModal.open && signatureModal.field ? (
        <div
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(0,0,0,0.45)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
            zIndex: 1100,
          }}
          onClick={closeSignatureModal}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{ width: "100%", maxWidth: 760, background: "#fff", borderRadius: 14, border: "1px solid #d1d5db", overflow: "hidden" }}
          >
            <div style={{ padding: "14px 16px", borderBottom: "1px solid #e5e7eb", display: "flex", justifyContent: "space-between", alignItems: "center", gap: 10 }}>
              <h3 style={{ margin: 0 }}>{signatureModal.field?.label || "Capturar firma"}</h3>
              <button
                type="button"
                onClick={closeSignatureModal}
                style={{ border: "1px solid #e5e7eb", background: "#fff", borderRadius: 8, width: 34, height: 34, cursor: "pointer" }}
              >
                ✕
              </button>
            </div>

            <div style={{ padding: 16, display: "grid", gap: 12 }}>
              <div style={{ fontSize: 13, color: "#475569", lineHeight: 1.5 }}>
                Firma dentro del recuadro. Si te equivocas puedes limpiar y volver a firmar.
              </div>
              <div
                ref={signatureWrapperRef}
                style={{ width: "100%", border: "1px solid #cbd5e1", borderRadius: 12, background: "#fff", overflow: "hidden", touchAction: "none" }}
              >
                <canvas
                  ref={signatureCanvasRef}
                  onMouseDown={startDrawing}
                  onMouseMove={draw}
                  onMouseUp={stopDrawing}
                  onMouseLeave={stopDrawing}
                  onTouchStart={startDrawing}
                  onTouchMove={(e) => {
                    e.preventDefault();
                    draw(e);
                  }}
                  onTouchEnd={stopDrawing}
                  style={{ display: "block", width: "100%", height: 220, cursor: "crosshair", background: "#fff" }}
                />
              </div>
            </div>

            <div style={{ padding: 16, borderTop: "1px solid #e5e7eb", display: "flex", justifyContent: "space-between", gap: 10, flexWrap: "wrap" }}>
              <button
                type="button"
                onClick={clearSignatureCanvas}
                style={{ borderRadius: 10, border: "1px solid #fecaca", background: "#fef2f2", color: "#b91c1c", padding: "10px 14px", cursor: "pointer", fontWeight: 700 }}
              >
                Limpiar firma
              </button>

              <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                <button type="button" onClick={closeSignatureModal} style={topButtonStyle}>Cancelar</button>
                <button type="button" onClick={saveSignature} style={primaryButtonStyle}>Guardar firma</button>
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}
