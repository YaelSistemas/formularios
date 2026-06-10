import React, { useEffect, useRef, useState } from "react";

export default function SGI_POP_LG_01_FO_04_Checklist_de_Mantenimiento_Cortadora_de_Banda({
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
  const [condicionesMecanicasOpen, setCondicionesMecanicasOpen] = useState(true);
  const [condicionesElectricasOpen, setCondicionesElectricasOpen] = useState(true);

  const [signatureModal, setSignatureModal] = useState({
    open: false,
    field: null,
  });

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

  const scrollToTopSafe = () => {
    try {
      topRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const clearFormFieldError = () => {
    setFormFieldError("");
    setFormFieldErrorId(null);

    if (formErrorTimerRef.current) {
      clearTimeout(formErrorTimerRef.current);
      formErrorTimerRef.current = null;
    }
  };

  const openSectionForFieldError = (fieldId) => {
    const id = String(fieldId || "");

    if (id.startsWith("mecanicas_")) {
      setIndicacionesOpen(true);
      setCondicionesMecanicasOpen(true);
      return;
    }

    if (id.startsWith("electricas_")) {
      setIndicacionesOpen(true);
      setCondicionesElectricasOpen(true);
      return;
    }
  };

  const showFormFieldError = (fieldId, message) => {
    setMsg("");
    setFormFieldError(message);
    setFormFieldErrorId(fieldId);
    openSectionForFieldError(fieldId);

    if (formErrorTimerRef.current) clearTimeout(formErrorTimerRef.current);

    setTimeout(() => {
      requestAnimationFrame(() => {
        const wrapEl = formFieldWrapRefs.current[fieldId];
        const inputEl = formFieldRefs.current[fieldId];

        if (wrapEl?.scrollIntoView) {
          wrapEl.scrollIntoView({
            behavior: "smooth",
            block: "center",
          });
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

  const closeSignatureModal = () => {
    setSignatureModal({ open: false, field: null });
  };

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
    canvas.style.height = `220px`;

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
      img.onload = () => {
        ctx.drawImage(img, 0, 0, rect.width, 220);
      };
      img.src = prevData;
    }
  };

  useEffect(() => {
    if (!signatureModal.open) return;

    const t = setTimeout(() => {
      resizeSignatureCanvas();
    }, 0);

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

    return {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };
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

    return id.includes("observaciones") || label.includes("observaciones");
  };

  const isEmptyValue = (value, type) => {
    if (type === "checkbox") return value !== true;
    return value === null || value === undefined || String(value).trim() === "";
  };

  const isSectionTitle = (f) => {
    if (f?.type !== "static_text") return false;

    const id = String(f?.id || "").toLowerCase();
    const text = String(f?.text || f?.label || "").trim();

    return (
      id.includes("titulo_") ||
      id.includes("subtitulo_") ||
      /^condiciones\s+/i.test(text) ||
      /^\d+\.\s/.test(text)
    );
  };

  const isMainTitle = (f) => {
    const id = String(f?.id || "").toLowerCase();
    const text = String(f?.text || f?.label || "").trim();

    return (
      id.includes("titulo_condiciones") ||
      text === "CONDICIONES MECANICAS" ||
      text === "CONDICIONES MECÁNICAS" ||
      text === "CONDICIONES ELECTRICAS" ||
      text === "CONDICIONES ELÉCTRICAS"
    );
  };

  const getSectionTheme = () => {
    return {
      border: "1px solid #dbe4ee",
      background: "#f8fafc",
      titleBg: "#eef2f7",
    };
  };

  const getCleanFieldLabel = (f) => {
    return String(f?.label || f?.text || "").trim();
  };

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
          ref={(el) => {
            formFieldRefs.current[f.id] = el;
          }}
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

    if (f.type === "checkbox") {
      return (
        <label style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
          <input
            ref={(el) => {
              formFieldRefs.current[f.id] = el;
            }}
            type="checkbox"
            checked={!!answers[f.id]}
            onChange={(e) => {
              setVal(f.id, e.target.checked);
              if (formFieldErrorId === f.id) clearFormFieldError();
            }}
            disabled={readOnly}
          />
          <span>Marcar</span>
        </label>
      );
    }

    if (f.type === "select" || f.type === "list") {
      const opts = Array.isArray(f.options) ? f.options : [];
      return (
        <select
          ref={(el) => {
            formFieldRefs.current[f.id] = el;
          }}
          value={answers[f.id] ?? ""}
          onChange={(e) => {
            setVal(f.id, e.target.value);
            if (formFieldErrorId === f.id) clearFormFieldError();
          }}
          disabled={readOnly}
          style={commonStyle}
        >
          <option value="">-- Selecciona --</option>
          {opts.map((opt) => (
            <option key={opt} value={opt}>
              {opt}
            </option>
          ))}
        </select>
      );
    }

    if (f.type === "radio") {
      const opts = Array.isArray(f.options) ? f.options : [];
      return (
        <div style={{ display: "grid", gap: 8 }}>
          {opts.length ? (
            opts.map((opt) => (
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
                    if (el && answers[f.id] === opt) {
                      formFieldRefs.current[f.id] = el;
                    } else if (el && !formFieldRefs.current[f.id]) {
                      formFieldRefs.current[f.id] = el;
                    }
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
            ))
          ) : (
            <div style={{ fontSize: 12, opacity: 0.7 }}>Sin opciones</div>
          )}
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
                style={{
                  display: "block",
                  maxWidth: "100%",
                  maxHeight: 140,
                  objectFit: "contain",
                  margin: "0 auto",
                }}
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
                fontSize: isMobile ? 14 : 14,
                boxShadow: hasFormError ? "0 0 0 3px rgba(251,146,60,0.12)" : "none",
              }}
            >
              Sin firma capturada
            </div>
          )}

          {!readOnly ? (
            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button
                ref={(el) => {
                  formFieldRefs.current[f.id] = el;
                }}
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
                  fontSize: isMobile ? 14 : 14,
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
                    fontSize: isMobile ? 14 : 14,
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

    const htmlType =
      f.type === "number"
        ? "number"
        : f.type === "date"
        ? "date"
        : f.type === "datetime"
        ? "datetime-local"
        : "text";

    return (
      <input
        ref={(el) => {
          formFieldRefs.current[f.id] = el;
        }}
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
            style={{
              maxWidth: "100%",
              width: isMobile ? 250 : 420,
              borderRadius: 10,
            }}
          />
        </div>
      );
    }

    return renderBasicInput(f);
  };

  const getField = (id) => fields.find((f) => f.id === id);

  const logo = getField("encabezado_logo");
  const headerLines = [
    getField("header_line_1"),
    getField("header_line_2"),
    getField("header_line_3"),
    getField("header_line_4"),
    getField("header_line_5"),
    getField("header_line_6"),
  ].filter(Boolean);

  const taller = getField("taller");
  const nombreResponsableMantenimiento = getField("nombre_responsable_mantenimiento");
  const firmaResponsableMantenimiento = getField("firma_responsable_mantenimiento");

  const visibleFields = fields.filter((f) => {
    if (!f?.id) return false;

    const excluded = [
      "encabezado_logo",
      "header_line_1",
      "header_line_2",
      "header_line_3",
      "header_line_4",
      "header_line_5",
      "header_line_6",
      "taller",
      "nombre_responsable_mantenimiento",
      "firma_responsable_mantenimiento",
    ];

    return !excluded.includes(f.id);
  });

  const topButtonStyle = {
    borderRadius: 12,
    border: "1px solid #d1d5db",
    background: "#fff",
    color: "#111827",
    padding: isMobile ? "10px 14px" : "10px 14px",
    cursor: "pointer",
    fontWeight: 700,
    fontSize: isMobile ? 14 : 14,
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
    fontSize: isMobile ? 14 : 14,
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
    <div
      key={key}
      style={{
        borderBottom: "1px solid #d1d5db",
        margin: "4px 0 0 0",
      }}
    />
  );

  const renderStaticText = (f) => {
    if (isSectionTitle(f)) {
      const theme = getSectionTheme(f.id);

      return (
        <div
          key={f.id}
          style={{
            borderRadius: 16,
            overflow: "hidden",
            border: theme.border,
            background: theme.background,
          }}
        >
          <div
            style={{
              padding: isMainTitle(f) ? "13px 14px" : "12px 14px",
              background: isMainTitle(f) ? theme.titleBg : "#fff",
              fontWeight: isMainTitle(f) ? 900 : 800,
              color: "#0f172a",
              fontSize: isMobile ? 14 : isMainTitle(f) ? 15 : 14,
              lineHeight: 1.4,
              textTransform: isMainTitle(f) ? "uppercase" : "none",
            }}
          >
            {f.text || f.label}
          </div>
        </div>
      );
    }

    return (
      <div
        key={f.id}
        style={{
          color: "#111827",
          fontSize: isMobile ? 14 : 14,
          lineHeight: 1.5,
        }}
      >
        {f.text || f.label}
      </div>
    );
  };

  const renderOuterField = (f) => {
    if (!f) return null;

    if (f.type === "static_text") {
      return renderStaticText(f);
    }

    const isRequiredVisual = !!f.required && !isObservationField(f);

    return (
      <div
        key={f.id}
        ref={(el) => {
          formFieldWrapRefs.current[f.id] = el;
        }}
        style={getOuterFieldBlockStyle(f.id)}
      >
        <label style={{ fontSize: isMobile ? 14 : 14, color: "#0f172a", lineHeight: 1.4 }}>
          <b>{getCleanFieldLabel(f)}</b>{" "}
          {isRequiredVisual ? <span style={{ color: "crimson" }}>*</span> : null}
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

  const renderFieldsWithDividers = (list) => {
    const items = [];

    list.forEach((f, index) => {
      if (!f) return;

      items.push(renderOuterField(f));

      if (index < list.length - 1) {
        items.push(renderDivider(`${f.id}_divider`));
      }
    });

    return items;
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

  const nestedSubSectionStyle = {
    border: "1px solid #cbd5e1",
    borderRadius: 16,
    background: "#ffffff",
    overflow: "hidden",
  };

  const nestedSubSectionHeaderStyle = {
    padding: isMobile ? "12px 14px" : "13px 16px",
    background: "#ffffff",
    borderBottom: "1px solid #e5e7eb",
    fontWeight: 900,
    color: "#0f172a",
    fontSize: isMobile ? 14 : 15,
    lineHeight: 1.4,
  };

  const nestedCriterionStyle = {
    border: "1px solid #e5e7eb",
    borderRadius: 14,
    background: "#fbfdff",
    padding: isMobile ? 12 : 14,
    display: "grid",
    gap: 12,
  };

  const renderCriterionBox = (baseId, title, resultLabel = null) => {
    const estado = getField(`${baseId}_estado`);
    const observaciones = getField(`${baseId}_observaciones`);

    if (!estado && !observaciones) return null;

    return (
      <div key={baseId} style={nestedCriterionStyle}>
        {estado ? renderOuterField({ ...estado, label: resultLabel || title }) : null}

        {observaciones ? (
          <>
            <div
              style={{
                borderBottom: "1px solid #e5e7eb",
                margin: "0",
              }}
            />
            {renderOuterField({ ...observaciones, label: "Observaciones" })}
          </>
        ) : null}
      </div>
    );
  };

  const renderSubSectionBox = (title, criteria) => {
    return (
      <div key={title} style={nestedSubSectionStyle}>
        <div style={nestedSubSectionHeaderStyle}>{title}</div>

        <div
          style={{
            padding: isMobile ? 12 : 14,
            display: "grid",
            gap: isMobile ? 12 : 14,
          }}
        >
          {criteria.map((item) =>
            renderCriterionBox(item.baseId, item.title, item.resultLabel || null)
          )}
        </div>
      </div>
    );
  };

  const renderMainSectionBox = (title, sections, isOpen = true, setIsOpen = null) => {
    return (
      <div style={nestedSectionStyle}>
        {typeof setIsOpen === "function" ? (
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
                fontSize: 16,
                lineHeight: 1,
                transform: isOpen ? "rotate(180deg)" : "rotate(0deg)",
                transition: "transform 0.2s ease",
              }}
            >
              ▾
            </span>
          </button>
        ) : (
          <div style={nestedSectionHeaderStyle}>{title}</div>
        )}

        {isOpen ? (
          <div
            style={{
              padding: isMobile ? 12 : 14,
              display: "grid",
              gap: isMobile ? 12 : 14,
            }}
          >
            {sections.map((section) => renderSubSectionBox(section.title, section.criteria))}
          </div>
        ) : null}
      </div>
    );
  };

  const condicionesMecanicasSections = [
    {
      title: "1. Moto-reductores",
      criteria: [
        { baseId: "mecanicas_1_1_niveles_aceite", title: "1.1. Niveles de aceite" },
        { baseId: "mecanicas_1_2_alineamiento_cadenas", title: "1.2. Alineamiento de cadenas" },
        { baseId: "mecanicas_1_3_baleros_motores", title: "1.3. Baleros de los motores" },
        { baseId: "mecanicas_1_4_chumaceras", title: "1.4. Chumaceras" },
        { baseId: "mecanicas_1_5_tren_engranaje", title: "1.5. Tren de engranaje" },
      ],
    },
    {
      title: "2. Mesa de Corte",
      criteria: [
        { baseId: "mecanicas_2_1_balero_chumaceras", title: "2.1. Balero de chumaceras" },
        { baseId: "mecanicas_2_2_tornilleria", title: "2.2. Tornillería" },
      ],
    },
  ];

  const condicionesElectricasSections = [
    {
      title: "1. Tablero de Control",
      criteria: [
        { baseId: "electricas_1_1_variadores_velocidad", title: "1.1. Variadores de velocidad" },
        { baseId: "electricas_1_2_contactores", title: "1.2. Contactores" },
        { baseId: "electricas_1_3_conexiones", title: "1.3. Conexiones" },
        { baseId: "electricas_1_4_selector_motor", title: "1.4. Selector de motor" },
      ],
    },
    {
      title: "2. Motores",
      criteria: [
        { baseId: "electricas_2_1_conexiones", title: "2.1. Conexiones" },
        { baseId: "electricas_2_2_aislantes", title: "2.2. Aislantes" },
      ],
    },
  ];

  const separacionIndicacionesLlenado = getField("separacion_indicaciones_llenado");
  const indicacion1 = getField("indicacion_1");
  const observacionesGenerales = getField("observaciones_generales");

  const validateSimpleRequiredField = (field, emptyMessage = null) => {
    if (!field) return false;

    const value = answers[field.id];

    if (field.type === "static_text" || field.type === "fixed_image") return true;

    if (field.type === "signature") {
      if (!value || String(value).trim() === "") {
        showFormFieldError(field.id, emptyMessage || `Falta responder: ${field.label}`);
        return false;
      }
      return true;
    }

    if (isEmptyValue(value, field.type)) {
      showFormFieldError(field.id, emptyMessage || `Falta responder: ${field.label}`);
      return false;
    }

    if (field.type === "select" || field.type === "list" || field.type === "radio") {
      const opts = Array.isArray(field.options) ? field.options : [];
      if (opts.length && !opts.includes(value)) {
        showFormFieldError(
          field.id,
          `Selecciona una opción válida para: ${field.label}`
        );
        return false;
      }
    }

    return true;
  };

  const validateBeforeSubmit = () => {
    clearFormFieldError();
    setMsg("");

    if (!validateSimpleRequiredField(taller, "Debes seleccionar el Taller.")) return false;

    const fieldsToValidate = fields.filter((f) => {
      if (!f?.id) return false;
      if (f.type === "static_text" || f.type === "fixed_image") return false;
      if (isObservationField(f)) return false;
      return !!f.required;
    });

    for (const field of fieldsToValidate) {
      if (field.id === "taller") continue;

      if (
        field.id === "nombre_responsable_mantenimiento" &&
        !validateSimpleRequiredField(
          field,
          "Debes capturar el Nombre del Responsable de Mantenimiento."
        )
      ) {
        return false;
      }

      if (
        field.id === "firma_responsable_mantenimiento" &&
        !validateSimpleRequiredField(
          field,
          "Debes capturar la Firma del Responsable de Mantenimiento."
        )
      ) {
        return false;
      }

      if (
        field.id !== "nombre_responsable_mantenimiento" &&
        field.id !== "firma_responsable_mantenimiento" &&
        !validateSimpleRequiredField(field)
      ) {
        return false;
      }
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
    if (isEditing) {
      return "Modifica la información necesaria y actualiza el registro cuando termines.";
    }
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
      style={{
        minHeight: "100%",
        background: "#f8fafc",
        padding: isMobile ? "8px 8px 22px" : "14px 14px 28px",
      }}
    >
      <div
        style={{
          maxWidth: isMobile ? 980 : desktopContentWidth,
          width: isMobile ? "95%" : "100%",
          margin: "0 auto",
        }}
      >
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
          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              gap: 14,
              alignItems: "flex-start",
              flexWrap: "wrap",
            }}
          >
            <div style={{ display: "grid", gap: isMobile ? 8 : 8 }}>
              <div
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  fontSize: 12,
                  fontWeight: 800,
                  color: readOnly ? "#7c3aed" : isEditing ? "#166534" : "#2563eb",
                  background: readOnly ? "#f5f3ff" : isEditing ? "#ecfdf5" : "#eff6ff",
                  border: `1px solid ${
                    readOnly ? "#ddd6fe" : isEditing ? "#86efac" : "#bfdbfe"
                  }`,
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
                <h2
                  style={{
                    margin: 0,
                    fontSize: isMobile ? 18 : 24,
                    lineHeight: isMobile ? 1.25 : 1.15,
                    color: "#0f172a",
                  }}
                >
                  {getModeTitle()}
                </h2>

                <div
                  style={{
                    marginTop: 8,
                    fontSize: isMobile ? 13 : 13,
                    color: "#64748b",
                    lineHeight: 1.6,
                    maxWidth: 760,
                  }}
                >
                  {getModeDescription()}
                </div>

                {readOnly && responseMeta ? (
                  <div
                    style={{
                      marginTop: 10,
                      fontSize: 12,
                      color: "#64748b",
                      lineHeight: 1.6,
                    }}
                  >
                    Respuesta #{responseMeta.id || "—"} • Usuario: {responseMeta.user_id || "—"} •
                    Fecha:{" "}
                    {responseMeta.created_at
                      ? new Date(responseMeta.created_at).toLocaleString()
                      : "—"}
                  </div>
                ) : null}
              </div>
            </div>

            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button type="button" onClick={onBack} style={topButtonStyle}>
                ← Volver
              </button>
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
                padding: isMobile ? "12px 14px" : "12px 14px",
                whiteSpace: "pre-line",
                fontSize: isMobile ? 13 : 13,
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
                padding: isMobile ? "12px 14px" : "12px 14px",
                fontSize: isMobile ? 13 : 13,
                lineHeight: 1.6,
              }}
            >
              Estás en <b>modo offline</b>. Al enviar, el registro se guardará en el
              dispositivo y se sincronizará automáticamente después.
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
                padding: isMobile ? "12px 14px" : "12px 14px",
                fontSize: isMobile ? 13 : 13,
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
              <div
                style={{
                  display: "flex",
                  justifyContent: "center",
                  paddingBottom: isMobile ? 4 : 0,
                }}
              >
                <div
                  style={{
                    width: "100%",
                    maxWidth: isMobile ? 250 : 420,
                    textAlign: "center",
                  }}
                >
                  {renderField(logo)}
                </div>
              </div>
            ) : null}

            <div
              style={{
                display: "grid",
                gap: isMobile ? 10 : 6,
                textAlign: "left",
              }}
            >
              {headerLines.map((line) => (
                <div
                  key={line.id}
                  style={{
                    width: "100%",
                    fontWeight: 700,
                    fontSize: isMobile ? 12 : 14,
                    color: "#111827",
                    textAlign: "left",
                    lineHeight: isMobile ? 1.45 : 1.35,
                  }}
                >
                  {line.text}
                </div>
              ))}
            </div>

            {renderDivider("header_divider")}

            {taller ? renderOuterField(taller) : null}

            {taller ? renderDivider("taller_divider") : null}

            {nombreResponsableMantenimiento ? renderOuterField(nombreResponsableMantenimiento) : null}

            {nombreResponsableMantenimiento && firmaResponsableMantenimiento
              ? renderDivider("nombre_responsable_divider")
              : null}

            {firmaResponsableMantenimiento ? renderOuterField(firmaResponsableMantenimiento) : null}

            {firmaResponsableMantenimiento ? renderDivider("firma_responsable_divider") : null}

            <div
              style={{
                border: "1px solid #dbe4ee",
                borderRadius: 16,
                overflow: "hidden",
                background: "#ffffff",
              }}
            >
              <button
                type="button"
                onClick={() => setIndicacionesOpen((prev) => !prev)}
                style={{
                  width: "100%",
                  border: "none",
                  borderBottom: indicacionesOpen ? "1px solid #dbe4ee" : "none",
                  background: "#eef2f7",
                  color: "#0f172a",
                  padding: isMobile ? "13px 14px" : "15px 18px",
                  cursor: "pointer",
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  gap: 12,
                  textAlign: "left",
                  fontWeight: 900,
                  fontSize: isMobile ? 15 : 16,
                  lineHeight: 1.35,
                  textTransform: "uppercase",
                  letterSpacing: 0.2,
                }}
              >
                <span>
                  {separacionIndicacionesLlenado?.text ||
                    separacionIndicacionesLlenado?.label ||
                    "INDICACIONES DE LLENADO"}
                </span>
                <span
                  aria-hidden="true"
                  style={{
                    fontSize: 18,
                    lineHeight: 1,
                    transform: indicacionesOpen ? "rotate(180deg)" : "rotate(0deg)",
                    transition: "transform 0.2s ease",
                  }}
                >
                  ▾
                </span>
              </button>

              {indicacionesOpen ? (
                <div
                  style={{
                    padding: isMobile ? 12 : 14,
                    display: "grid",
                    gap: isMobile ? 12 : 14,
                  }}
                >
                  {indicacion1 ? (
                    <div
                      style={{
                        color: "#111827",
                        fontSize: isMobile ? 14 : 14,
                        lineHeight: 1.5,
                        fontWeight: 700,
                      }}
                    >
                      {indicacion1.text || indicacion1.label}
                    </div>
                  ) : null}

                  {renderMainSectionBox("CONDICIONES MECÁNICAS", condicionesMecanicasSections, condicionesMecanicasOpen, setCondicionesMecanicasOpen)}

                  {renderMainSectionBox("CONDICIONES ELÉCTRICAS", condicionesElectricasSections, condicionesElectricasOpen, setCondicionesElectricasOpen)}
                </div>
              ) : null}
            </div>

            {renderDivider("observaciones_generales_divider")}
            
            {observacionesGenerales ? (
              <div>
                {renderOuterField({ ...observacionesGenerales, label: "Observaciones Generales" })}
              </div>
            ) : null}
          </div>

          {!readOnly ? (
            <div
              style={{
                position: "sticky",
                bottom: isMobile ? 8 : 12,
                zIndex: 20,
                marginTop: isMobile ? 16 : 18,
              }}
            >
              <div
                style={{
                  maxWidth: isMobile ? "100%" : desktopContentWidth,
                  width: "100%",
                  marginInline: "auto",
                  background: "rgba(255,255,255,0.92)",
                  backdropFilter: "blur(10px)",
                  border: "1px solid #dbe4ee",
                  borderRadius: isMobile ? 16 : 18,
                  padding: isMobile ? 14 : 14,
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  gap: 14,
                  flexWrap: "wrap",
                  boxShadow: "0 10px 30px rgba(15,23,42,0.08)",
                }}
              >
                <div style={{ display: "grid", gap: 5 }}>
                  <div
                    style={{
                      fontSize: isMobile ? 13 : 13,
                      fontWeight: 800,
                      color: "#0f172a",
                    }}
                  >
                    {getFooterStateText()}
                  </div>
                  <div
                    style={{
                      fontSize: isMobile ? 12 : 12,
                      color: "#64748b",
                      lineHeight: 1.5,
                    }}
                  >
                    Revisa la información antes de continuar.
                  </div>
                </div>

                <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                  <button type="button" onClick={onBack} style={topButtonStyle}>
                    Volver
                  </button>

                  <button
                    type="submit"
                    disabled={saving || (isEditing && !isOnline)}
                    style={{
                      ...primaryButtonStyle,
                      opacity: saving || (isEditing && !isOnline) ? 0.7 : 1,
                      cursor:
                        saving || (isEditing && !isOnline)
                          ? "not-allowed"
                          : "pointer",
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
            style={{
              width: "100%",
              maxWidth: 760,
              background: "#fff",
              borderRadius: 14,
              border: "1px solid #d1d5db",
              overflow: "hidden",
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
                {signatureModal.field?.label || "Capturar firma"}
              </h3>

              <button
                type="button"
                onClick={closeSignatureModal}
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

            <div style={{ padding: 16, display: "grid", gap: 12 }}>
              <div style={{ fontSize: 13, color: "#475569", lineHeight: 1.5 }}>
                Firma dentro del recuadro. Si te equivocas puedes limpiar y volver a firmar.
              </div>

              <div
                ref={signatureWrapperRef}
                style={{
                  width: "100%",
                  border: "1px solid #cbd5e1",
                  borderRadius: 12,
                  background: "#fff",
                  overflow: "hidden",
                  touchAction: "none",
                }}
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
                  style={{
                    display: "block",
                    width: "100%",
                    height: 220,
                    cursor: "crosshair",
                    background: "#fff",
                  }}
                />
              </div>
            </div>

            <div
              style={{
                padding: 16,
                borderTop: "1px solid #e5e7eb",
                display: "flex",
                justifyContent: "space-between",
                gap: 10,
                flexWrap: "wrap",
              }}
            >
              <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                <button
                  type="button"
                  onClick={clearSignatureCanvas}
                  style={{
                    borderRadius: 10,
                    border: "1px solid #fecaca",
                    background: "#fef2f2",
                    color: "#b91c1c",
                    padding: "10px 14px",
                    cursor: "pointer",
                    fontWeight: 700,
                  }}
                >
                  Limpiar firma
                </button>
              </div>

              <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
                <button type="button" onClick={closeSignatureModal} style={topButtonStyle}>
                  Cancelar
                </button>

                <button type="button" onClick={saveSignature} style={primaryButtonStyle}>
                  Guardar firma
                </button>
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}
