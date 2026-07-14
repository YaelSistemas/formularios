import React, { useEffect, useRef, useState } from "react";

export default function SST_PGI_TA_01_FO_01_Boleta_de_Observaciones({
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
  const [datosGeneralesOpen, setDatosGeneralesOpen] = useState(true);
  const [faltaCometidaOpen, setFaltaCometidaOpen] = useState(true);
  const [evidenciaAccionesOpen, setEvidenciaAccionesOpen] = useState(true);
  const [firmasOpen, setFirmasOpen] = useState(true);

  const [signatureModal, setSignatureModal] = useState({
    open: false,
    field: null,
    personIndex: null,
    personName: "",
    currentValue: "",
  });

  const [evidencePreview, setEvidencePreview] = useState({
    open: false,
    src: "",
    name: "",
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

  // Permite copiar inicialmente los nombres del personal observado al campo
  // "Nombre del Observado", sin sobrescribir cambios manuales posteriores.
  const previousPersonalObservadoRef = useRef(
    answers?.nombre_personal_observado ?? ""
  );

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

  useEffect(() => {
    if (!evidencePreview.open) return;

    const previousOverflow = document.body.style.overflow;

    const onKeyDown = (event) => {
      if (event.key === "Escape") {
        setEvidencePreview({
          open: false,
          src: "",
          name: "",
        });
      }
    };

    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [evidencePreview.open]);

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

  const openSectionForField = (fieldId) => {
    const id = String(fieldId || "");

    if (
      id === "taller" ||
      id === "planta_area_trabajo" ||
      id === "tipo_observacion" ||
      id === "descripcion_observacion"
    ) {
      setDatosGeneralesOpen(true);
      return;
    }

    if (
      id === "falta_cometida_seleccionada" ||
      id === "descripcion_falta_cometida"
    ) {
      setFaltaCometidaOpen(true);
      return;
    }

    if (
      id === "evidencia_fotografica" ||
      id === "acciones_preventivas_correctivas"
    ) {
      setEvidenciaAccionesOpen(true);
      return;
    }

    if (
      id === "nombre_reporta_observacion" ||
      id === "firma_reporta_observacion" ||
      id === "nombre_observado" ||
      id === "firma_observado"
    ) {
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

  const openSignatureModal = (
    field,
    {
      personIndex = null,
      personName = "",
      currentValue = "",
    } = {}
  ) => {
    if (readOnly) return;
    if (formFieldErrorId === field?.id) clearFormFieldError();

    setSignatureModal({
      open: true,
      field,
      personIndex,
      personName,
      currentValue,
    });
  };

  const closeSignatureModal = () =>
    setSignatureModal({
      open: false,
      field: null,
      personIndex: null,
      personName: "",
      currentValue: "",
    });

  const resizeSignatureCanvas = (initialData = "") => {
    const canvas = signatureCanvasRef.current;
    const wrapper = signatureWrapperRef.current;
    if (!canvas || !wrapper) return;

    const prevData = initialData || canvas.toDataURL("image/png");
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

    const t = setTimeout(
      () => resizeSignatureCanvas(signatureModal.currentValue || ""),
      0
    );

    const onResize = () => resizeSignatureCanvas();
    window.addEventListener("resize", onResize);

    return () => {
      clearTimeout(t);
      window.removeEventListener("resize", onResize);
    };
  }, [
    signatureModal.open,
    signatureModal.personIndex,
    signatureModal.currentValue,
  ]);

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

  const getObservedNames = () =>
    String(answers?.nombre_observado || "")
      .split(/\r?\n/)
      .map((name) => name.trim())
      .filter(Boolean);

  const normalizeObservedSignatures = (value = answers?.firma_observado) => {
    const names = getObservedNames();

    if (Array.isArray(value)) {
      return names.map((name, index) => {
        const byIndex = value[index];
        const byName = value.find(
          (item) =>
            item &&
            typeof item === "object" &&
            String(item.nombre || "").trim() === name
        );
        const item = byName || byIndex || {};

        return {
          nombre: name,
          firma:
            typeof item === "string"
              ? item
              : String(item?.firma || ""),
        };
      });
    }

    // Compatibilidad con registros anteriores que tenían una sola firma.
    if (typeof value === "string" && value.trim() !== "") {
      return names.map((name, index) => ({
        nombre: name,
        firma: index === 0 ? value : "",
      }));
    }

    return names.map((name) => ({ nombre: name, firma: "" }));
  };

  const getSignatureSrc = (value) => {
    if (typeof value !== "string") {
      return "";
    }

    const normalized = value.trim();

    if (!normalized) {
      return "";
    }

    if (normalized.startsWith("data:image/")) {
      return normalized;
    }

    if (/^https?:\/\//i.test(normalized)) {
      return normalized;
    }

    if (normalized.startsWith("/storage/")) {
      return normalized;
    }

    return `/storage/${normalized.replace(/^\/+/, "")}`;
  };

  const getEvidenceSrc = (file) => {
    if (!file) return "";

    let value = "";

    if (typeof file === "string") {
      value = file;
    } else if (typeof file === "object") {
      value =
        file.data ||
        file.url ||
        file.path ||
        file.file ||
        file.ruta ||
        "";
    }

    const normalized = String(value || "")
      .trim()
      .replace(/\\/g, "/");

    if (!normalized) return "";

    if (
      normalized.startsWith("data:image/") ||
      normalized.startsWith("blob:") ||
      /^https?:\/\//i.test(normalized)
    ) {
      return normalized;
    }

    if (normalized.startsWith("/storage/")) {
      return normalized;
    }

    if (normalized.startsWith("storage/")) {
      return `/${normalized}`;
    }

    if (normalized.startsWith("/")) {
      return normalized;
    }

    return `/storage/${normalized.replace(/^\/+/, "")}`;
  };

  const openEvidencePreview = (file, fileName = "Evidencia fotográfica") => {
    if (!readOnly) return;

    const src = getEvidenceSrc(file);

    if (!src) return;

    setEvidencePreview({
      open: true,
      src,
      name: fileName || "Evidencia fotográfica",
    });
  };

  const closeEvidencePreview = () => {
    setEvidencePreview({
      open: false,
      src: "",
      name: "",
    });
  };

  const saveSignature = () => {
    const field = signatureModal.field;
    const canvas = signatureCanvasRef.current;
    if (!field || !canvas) return;

    const dataUrl = canvas.toDataURL("image/png");

    if (
      field.id === "firma_observado" &&
      Number.isInteger(signatureModal.personIndex)
    ) {
      const signatures = normalizeObservedSignatures();
      const personIndex = signatureModal.personIndex;

      signatures[personIndex] = {
        nombre:
          signatureModal.personName ||
          signatures[personIndex]?.nombre ||
          "",
        firma: dataUrl,
      };

      setVal(field.id, signatures);
    } else {
      setVal(field.id, dataUrl);
    }

    if (formFieldErrorId === field.id) clearFormFieldError();
    setMsg("");
    closeSignatureModal();
  };

  const removeSignature = (fieldId, personIndex = null) => {
    if (readOnly) return;

    if (
      fieldId === "firma_observado" &&
      Number.isInteger(personIndex)
    ) {
      const signatures = normalizeObservedSignatures();

      if (signatures[personIndex]) {
        signatures[personIndex] = {
          ...signatures[personIndex],
          firma: "",
        };
      }

      setVal(fieldId, signatures);
    } else {
      setVal(fieldId, "");
    }

    if (formFieldErrorId === fieldId) clearFormFieldError();
  };

  const isObservationField = () => false;

  const isEmptyValue = (value, type) => {
    if (type === "checkbox") {
      return value !== true;
    }

    if (type === "file") {
      return (
        !value ||
        (Array.isArray(value) && value.length === 0)
      );
    }

    if (type === "signature") {
      if (Array.isArray(value)) {
        return !value.some((item) => {
          if (typeof item === "string") {
            return item.trim() !== "";
          }

          return (
            item &&
            typeof item === "object" &&
            typeof item.firma === "string" &&
            item.firma.trim() !== ""
          );
        });
      }

      return (
        value === null ||
        value === undefined ||
        typeof value !== "string" ||
        value.trim() === ""
      );
    }

    return (
      value === null ||
      value === undefined ||
      String(value).trim() === ""
    );
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

    const isObservedNamesField =
      f.id === "nombre_personal_observado" || f.id === "nombre_observado";

    if (f.type === "textarea" || isObservedNamesField) {
      return (
        <textarea
          ref={(el) => { formFieldRefs.current[f.id] = el; }}
          value={answers[f.id] ?? ""}
          onChange={(e) => {
            setVal(f.id, e.target.value);
            if (formFieldErrorId === f.id) clearFormFieldError();
          }}
          placeholder={f.placeholder || ""}
          rows={isObservedNamesField ? 5 : 4}
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


    if (f.type === "file") {
      const current = answers[f.id];
      const currentFiles = Array.isArray(current)
        ? current
        : current
        ? [current]
        : [];

      const getFileName = (file) => {
        if (!file) return "Archivo";
        if (typeof file === "string") return file.split("/").pop() || file;
        return file.name || file.path?.split("/").pop() || "Archivo";
      };

      const getFileSizeLabel = (file) => {
        const size = Number(file?.size || 0);
        if (!size) return "";
        if (size >= 1024 * 1024) return `${(size / (1024 * 1024)).toFixed(2)} MB`;
        if (size >= 1024) return `${(size / 1024).toFixed(1)} KB`;
        return `${size} B`;
      };

      const removeAttachedFile = (indexToRemove) => {
        if (readOnly) return;

        const nextFiles = currentFiles.filter(
          (_, index) => index !== indexToRemove
        );

        if (f.id === "evidencia_fotografica" || f.multiple) {
          setVal(f.id, nextFiles);
        } else {
          setVal(f.id, nextFiles[0] || null);
        }

        if (formFieldErrorId === f.id) clearFormFieldError();
      };

      return (
        <div style={{ display: "grid", gap: 10 }}>
          {!readOnly ? (
            <input
              ref={(el) => {
                formFieldRefs.current[f.id] = el;
              }}
              type="file"
              multiple={
                f.id === "evidencia_fotografica" ? true : !!f.multiple
              }
              accept={f.accept || ""}
              onChange={async (e) => {
                const files = Array.from(e.target.files || []);

                const invalidFiles = files.filter(
                  (file) => !file.type.startsWith("image/")
                );

                if (invalidFiles.length > 0) {
                  setMsg(
                    "Solo se permiten imágenes en Evidencia Fotográfica. No se agregaron archivos no válidos."
                  );

                  e.target.value = "";
                  return;
                }

                const toBase64 = (file) =>
                  new Promise((resolve, reject) => {
                    const reader = new FileReader();

                    reader.onload = () => {
                      resolve({
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        data: reader.result,
                      });
                    };

                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                  });

                const parsedFiles = await Promise.all(files.map(toBase64));

                const existingFiles = Array.isArray(answers[f.id])
                  ? answers[f.id]
                  : answers[f.id]
                  ? [answers[f.id]]
                  : [];

                const nextFiles =
                  f.id === "evidencia_fotografica" || f.multiple
                    ? [...existingFiles, ...parsedFiles]
                    : parsedFiles[0] || null;

                setVal(f.id, nextFiles);

                e.target.value = "";

                if (formFieldErrorId === f.id) clearFormFieldError();
              }}
              disabled={readOnly}
              style={commonStyle}
            />
          ) : null}

          <div
            style={{
              border: "1px solid #e5e7eb",
              background: "#f8fafc",
              borderRadius: 12,
              padding: 10,
              fontSize: 13,
              color: "#334155",
              lineHeight: 1.5,
            }}
          >
            {currentFiles.length ? (
              <div style={{ display: "grid", gap: 8 }}>
                <div
                  style={{
                    fontWeight: 800,
                    color: "#0f172a",
                    fontSize: 13,
                  }}
                >
                  Imágenes adjuntas
                </div>

                <div style={{ display: "grid", gap: 6 }}>
                  {currentFiles.map((file, index) => {
                    const fileName = getFileName(file);
                    const fileSize = getFileSizeLabel(file);
                    const fileSrc = getEvidenceSrc(file);
                    const canPreview = readOnly && Boolean(fileSrc);

                    return (
                      <div
                        key={`${f.id}_attached_${index}_${fileName}`}
                        role={canPreview ? "button" : undefined}
                        tabIndex={canPreview ? 0 : undefined}
                        onClick={
                          canPreview
                            ? () => openEvidencePreview(file, fileName)
                            : undefined
                        }
                        onKeyDown={
                          canPreview
                            ? (event) => {
                                if (
                                  event.key === "Enter" ||
                                  event.key === " "
                                ) {
                                  event.preventDefault();
                                  openEvidencePreview(file, fileName);
                                }
                              }
                            : undefined
                        }
                        title={
                          canPreview
                            ? "Haz clic para ampliar la imagen"
                            : undefined
                        }
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "space-between",
                          gap: 10,
                          border: canPreview
                            ? "1px solid #bfdbfe"
                            : "1px solid #e5e7eb",
                          background: canPreview ? "#eff6ff" : "#fff",
                          borderRadius: 10,
                          padding: isMobile ? "9px 10px" : "8px 10px",
                          cursor: canPreview ? "zoom-in" : "default",
                          transition:
                            "border-color 0.2s ease, background 0.2s ease",
                        }}
                      >
                        {canPreview ? (
                          <img
                            src={fileSrc}
                            alt={fileName}
                            style={{
                              width: isMobile ? 58 : 72,
                              height: isMobile ? 58 : 72,
                              flexShrink: 0,
                              display: "block",
                              objectFit: "cover",
                              borderRadius: 8,
                              border: "1px solid #dbeafe",
                              background: "#fff",
                            }}
                          />
                        ) : null}

                        <div
                          style={{
                            minWidth: 0,
                            display: "grid",
                            gap: 2,
                            flex: 1,
                          }}
                        >
                          <div
                            title={fileName}
                            style={{
                              overflow: "hidden",
                              textOverflow: "ellipsis",
                              whiteSpace: "nowrap",
                              color: "#0f172a",
                              fontWeight: 700,
                            }}
                          >
                            {fileName}
                          </div>

                          {fileSize ? (
                            <div
                              style={{
                                color: "#64748b",
                                fontSize: 12,
                              }}
                            >
                              {fileSize}
                            </div>
                          ) : null}

                          {canPreview ? (
                            <div
                              style={{
                                color: "#2563eb",
                                fontSize: 12,
                                fontWeight: 700,
                              }}
                            >
                              Haz clic para ampliar
                            </div>
                          ) : null}
                        </div>

                        {!readOnly ? (
                          <button
                            type="button"
                            onClick={() => removeAttachedFile(index)}
                            style={{
                              borderRadius: 9,
                              border: "1px solid #fecaca",
                              background: "#fef2f2",
                              color: "#b91c1c",
                              padding: isMobile ? "7px 10px" : "6px 10px",
                              cursor: "pointer",
                              fontWeight: 800,
                              fontSize: 12,
                              whiteSpace: "nowrap",
                            }}
                          >
                            Quitar
                          </button>
                        ) : null}
                      </div>
                    );
                  })}
                </div>
              </div>
            ) : (
              "Sin evidencia cargada."
            )}
          </div>
        </div>
      );
    }

    if (f.type === "signature") {
      if (f.id === "firma_observado") {
        const names = getObservedNames();
        const signatures = normalizeObservedSignatures();

        if (names.length === 0) {
          return (
            <div
              style={{
                border: "1px dashed #cbd5e1",
                borderRadius: 12,
                background: "#f8fafc",
                padding: isMobile ? 14 : 16,
                textAlign: "center",
                color: "#64748b",
                fontSize: 14,
              }}
            >
              Escribe al menos un nombre en “Nombre del Observado” para
              habilitar sus firmas.
            </div>
          );
        }

        return (
          <div style={{ display: "grid", gap: 12 }}>
            {signatures.map((item, index) => {
              const signatureSrc = getSignatureSrc(item.firma);

              return (
                <div
                  key={`${item.nombre}-${index}`}
                  style={{
                    border: "1px solid #dbe4ee",
                    borderRadius: 14,
                    background: "#f8fafc",
                    padding: isMobile ? 12 : 14,
                    display: "grid",
                    gap: 10,
                  }}
                >
                  <div
                    style={{
                      fontWeight: 800,
                      color: "#0f172a",
                      fontSize: 14,
                      lineHeight: 1.4,
                    }}
                  >
                    {index + 1}. {item.nombre}
                  </div>

                  {signatureSrc ? (
                    <div
                      style={{
                        border: "1px solid #d1d5db",
                        borderRadius: 12,
                        background: "#fff",
                        padding: 10,
                      }}
                    >
                      <img
                        src={signatureSrc}
                        alt={`Firma de ${item.nombre}`}
                        style={{
                          display: "block",
                          maxWidth: "100%",
                          maxHeight: 120,
                          objectFit: "contain",
                          margin: "0 auto",
                        }}
                      />
                    </div>
                  ) : (
                    <div
                      style={{
                        border: "1px dashed #cbd5e1",
                        borderRadius: 12,
                        background: "#fff",
                        padding: isMobile ? 12 : 14,
                        textAlign: "center",
                        color: "#64748b",
                        fontSize: 13,
                      }}
                    >
                      Sin firma capturada
                    </div>
                  )}

                  {!readOnly ? (
                    <div
                      style={{
                        display: "flex",
                        gap: 10,
                        flexWrap: "wrap",
                      }}
                    >
                      <button
                        ref={(el) => {
                          if (index === 0) {
                            formFieldRefs.current[f.id] = el;
                          }
                        }}
                        type="button"
                        onClick={() =>
                          openSignatureModal(f, {
                            personIndex: index,
                            personName: item.nombre,
                            currentValue: signatureSrc,
                          })
                        }
                        style={{
                          borderRadius: 12,
                          border: "1px solid #c7d2fe",
                          background: "#eef2ff",
                          color: "#1e40af",
                          padding: isMobile
                            ? "10px 14px"
                            : "10px 12px",
                          cursor: "pointer",
                          fontWeight: 800,
                          fontSize: 14,
                        }}
                      >
                        {item.firma
                          ? "Volver a firmar"
                          : `Firmar como ${item.nombre}`}
                      </button>

                      {item.firma ? (
                        <button
                          type="button"
                          onClick={() =>
                            removeSignature(f.id, index)
                          }
                          style={{
                            borderRadius: 12,
                            border: "1px solid #fecaca",
                            background: "#fef2f2",
                            color: "#b91c1c",
                            padding: isMobile
                              ? "10px 14px"
                              : "10px 12px",
                            cursor: "pointer",
                            fontWeight: 800,
                            fontSize: 14,
                          }}
                        >
                          Quitar firma
                        </button>
                      ) : null}
                    </div>
                  ) : null}
                </div>
              );
            })}
          </div>
        );
      }

      const value = answers[f.id] || "";
      const signatureSrc = getSignatureSrc(value);

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
                onClick={() =>
                  openSignatureModal(f, {
                    currentValue: signatureSrc,
                  })
                }
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

  const taller = getField("taller");
  const plantaAreaTrabajo = getField("planta_area_trabajo");
  const nombrePersonalObservado = getField("nombre_personal_observado");
  const tipoObservacion = getField("tipo_observacion");
  const descripcionObservacion = getField("descripcion_observacion");
  const faltaCometidaSeleccionada = getField("falta_cometida_seleccionada");
  const descripcionFaltaCometida = getField("descripcion_falta_cometida");
  const evidenciaFotografica = getField("evidencia_fotografica");
  const accionesPreventivasCorrectivas = getField("acciones_preventivas_correctivas");
  const nombreReportaObservacion = getField("nombre_reporta_observacion");
  const firmaReportaObservacion = getField("firma_reporta_observacion");
  const nombreObservado = getField("nombre_observado");
  const firmaObservado = getField("firma_observado");

  const tipoObservacionSeleccionado = answers.tipo_observacion || "";

  const opcionesFaltaPorTipo = {
    "Acto Inseguro": [
      "Bromas o Distracciones en Área de Trabajo",
      "No Portar EPP Específico por Actividad",
      "Trabajar con Equipo en Movimiento",
      "Uso de Herramientas en Mal Estado",
      "Exceso de Velocidad o Movimiento Inapropiado",
      "Trabajar en Alturas sin Medidas de Seguridad",
      "Uso Inadecuado de EPP",
      "No Realizar Bloqueos y Etiquetados",
      "Daño a la Maquinaria",
      "Daño a las Instalaciones",
      "Otros, especifique",
    ],
    "Condición Peligrosa": [
      "Áreas sin Delimitacion o Señalización Adecuada",
      "Equipos o Maquinaria con Matenimiento Deficiente",
      "Instalaciones Eléctricas Expuestas o en Mal Estado",
      "Piso Resbaladizo o con Obstaculos",
      "Iluminación Insuficiente",
      "Almacenamiento Inadecuado de Materiales",
      "Falta de Señalización de Emergencia o Rutas de Evacuación",
      "Otros, especifique",
    ],
    "Desviación": [
      "No Aplicar Procedimientos de Seguridad",
      "No Aplicar Procedimientos Operativos",
      "No Portar Credenciales o documentos de Acceso",
      "No Traer Tarjeta y Candado P/Bloqueo",
      "No Informar Situaciones Anormales o Riesgos Detectados",
      "No Desbloquear Equipos de los Clientes",
      "Otros, especifique",
    ],
  };

  const opcionesFalta =
    opcionesFaltaPorTipo[tipoObservacionSeleccionado] || [];

  const faltaCometidaDinamica = faltaCometidaSeleccionada
    ? {
        ...faltaCometidaSeleccionada,
        options: opcionesFalta,
      }
    : null;

  useEffect(() => {
    const valorActual = answers.falta_cometida_seleccionada;

    if (
      valorActual &&
      opcionesFalta.length > 0 &&
      !opcionesFalta.includes(valorActual)
    ) {
      setVal("falta_cometida_seleccionada", "");
    }

    if (!tipoObservacionSeleccionado && valorActual) {
      setVal("falta_cometida_seleccionada", "");
    }
  }, [tipoObservacionSeleccionado]);

  useEffect(() => {
    if (readOnly) return;

    const nuevoPersonalObservado =
      answers?.nombre_personal_observado ?? "";
    const nombreObservadoActual =
      answers?.nombre_observado ?? "";
    const valorAnteriorPersonal =
      previousPersonalObservadoRef.current ?? "";

    /*
     * Copia automáticamente los nombres cuando:
     * 1. "Nombre del Observado" está vacío, o
     * 2. todavía conserva exactamente el valor que fue copiado anteriormente.
     *
     * Si el usuario ya editó "Nombre del Observado", no se sobrescribe.
     */
    const puedeSincronizar =
      String(nombreObservadoActual).trim() === "" ||
      nombreObservadoActual === valorAnteriorPersonal;

    if (
      puedeSincronizar &&
      nombreObservadoActual !== nuevoPersonalObservado
    ) {
      setVal("nombre_observado", nuevoPersonalObservado);
    }

    previousPersonalObservadoRef.current = nuevoPersonalObservado;
  }, [
    answers?.nombre_personal_observado,
    readOnly,
    setVal,
  ]);

  useEffect(() => {
    if (readOnly) return;

    const currentValue = answers?.firma_observado;
    const names = getObservedNames();

    if (names.length === 0) {
      if (Array.isArray(currentValue) && currentValue.length > 0) {
        setVal("firma_observado", []);
      }
      return;
    }

    const normalized = normalizeObservedSignatures(currentValue);

    const currentComparable = Array.isArray(currentValue)
      ? currentValue.map((item) => ({
          nombre: String(item?.nombre || ""),
          firma:
            typeof item === "string"
              ? item
              : String(item?.firma || ""),
        }))
      : null;

    if (
      !currentComparable ||
      JSON.stringify(currentComparable) !== JSON.stringify(normalized)
    ) {
      setVal("firma_observado", normalized);
    }
  }, [answers?.nombre_observado, readOnly]);

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



  const validateSimpleRequiredField = (field, emptyMessage = null) => {
    if (!field) return true;
    const value = answers[field.id];
    if (field.type === "static_text" || field.type === "fixed_image") return true;

    if (field.type === "signature") {
      if (field.id === "firma_observado") {
        if (!field.required) {
          return true;
        }

        const signatures = Array.isArray(value) ? value : [];

        const hasAtLeastOneSignature = signatures.some((item) => {
          if (typeof item === "string") {
            return item.trim() !== "";
          }

          return (
            item &&
            typeof item === "object" &&
            typeof item.firma === "string" &&
            item.firma.trim() !== ""
          );
        });

        if (!hasAtLeastOneSignature) {
          openSectionForField(field.id);
          showFormFieldError(
            field.id,
            emptyMessage || `Falta responder: ${field.label}`
          );
          return false;
        }

        return true;
      }

      if (typeof value !== "string" || value.trim() === "") {
        openSectionForField(field.id);
        showFormFieldError(
          field.id,
          emptyMessage || `Falta responder: ${field.label}`
        );
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

            {renderCollapsibleSection(
              "DATOS GENERALES",
              datosGeneralesOpen,
              setDatosGeneralesOpen,
              <>
                {taller ? renderOuterField(taller) : null}
                {taller ? renderDivider("taller_divider") : null}
                {plantaAreaTrabajo ? renderOuterField(plantaAreaTrabajo) : null}
                {plantaAreaTrabajo ? renderDivider("planta_area_trabajo_divider") : null}
                {nombrePersonalObservado ? renderOuterField(nombrePersonalObservado) : null}
                {nombrePersonalObservado ? renderDivider("nombre_personal_observado_divider") : null}
                {tipoObservacion ? renderOuterField(tipoObservacion) : null}
                {tipoObservacion ? renderDivider("tipo_observacion_divider") : null}
                {descripcionObservacion ? renderOuterField(descripcionObservacion) : null}
              </>
            )}

            {tipoObservacionSeleccionado
              ? renderCollapsibleSection(
                  "FALTA COMETIDA",
                  faltaCometidaOpen,
                  setFaltaCometidaOpen,
                  <>
                    {faltaCometidaDinamica
                      ? renderOuterField(faltaCometidaDinamica)
                      : null}
                    {faltaCometidaDinamica
                      ? renderDivider("falta_cometida_seleccionada_divider")
                      : null}
                    {descripcionFaltaCometida
                      ? renderOuterField(descripcionFaltaCometida)
                      : null}
                  </>
                )
              : null}

            {renderCollapsibleSection(
              "EVIDENCIA Y ACCIONES PREVENTIVAS / CORRECTIVAS",
              evidenciaAccionesOpen,
              setEvidenciaAccionesOpen,
              <>
                {evidenciaFotografica ? renderOuterField(evidenciaFotografica) : null}
                {evidenciaFotografica
                  ? renderDivider("evidencia_fotografica_divider")
                  : null}
                {accionesPreventivasCorrectivas
                  ? renderOuterField(accionesPreventivasCorrectivas)
                  : null}
              </>
            )}

            {renderCollapsibleSection(
              "NOMBRES Y FIRMAS",
              firmasOpen,
              setFirmasOpen,
              <>
                {nombreReportaObservacion
                  ? renderOuterField(nombreReportaObservacion)
                  : null}
                {nombreReportaObservacion
                  ? renderDivider("nombre_reporta_observacion_divider")
                  : null}
                {firmaReportaObservacion
                  ? renderOuterField(firmaReportaObservacion)
                  : null}
                {firmaReportaObservacion
                  ? renderDivider("firma_reporta_observacion_divider")
                  : null}
                {nombreObservado ? renderOuterField(nombreObservado) : null}
                {nombreObservado
                  ? renderDivider("nombre_observado_divider")
                  : null}
                {firmaObservado ? renderOuterField(firmaObservado) : null}
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

      {readOnly &&
      evidencePreview.open &&
      evidencePreview.src ? (
        <div
          role="dialog"
          aria-modal="true"
          aria-label={evidencePreview.name || "Vista de evidencia"}
          onClick={closeEvidencePreview}
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 1200,
            background: "rgba(15, 23, 42, 0.88)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: isMobile ? 10 : 22,
          }}
        >
          <div
            onClick={(event) => event.stopPropagation()}
            style={{
              position: "relative",
              width: "100%",
              height: "100%",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
            }}
          >
            <button
              type="button"
              onClick={closeEvidencePreview}
              aria-label="Cerrar imagen"
              style={{
                position: "absolute",
                top: isMobile ? 4 : 0,
                right: isMobile ? 4 : 0,
                zIndex: 2,
                width: 42,
                height: 42,
                borderRadius: 999,
                border: "1px solid rgba(255,255,255,0.45)",
                background: "rgba(15,23,42,0.72)",
                color: "#fff",
                cursor: "pointer",
                fontSize: 22,
                lineHeight: 1,
                fontWeight: 800,
              }}
            >
              ✕
            </button>

            <img
              src={evidencePreview.src}
              alt={evidencePreview.name || "Evidencia fotográfica"}
              style={{
                display: "block",
                maxWidth: "96vw",
                maxHeight: "88vh",
                width: "auto",
                height: "auto",
                objectFit: "contain",
                borderRadius: 10,
                background: "#fff",
                boxShadow: "0 20px 60px rgba(0,0,0,0.45)",
              }}
            />

            <div
              style={{
                position: "absolute",
                left: "50%",
                bottom: isMobile ? 6 : 0,
                transform: "translateX(-50%)",
                maxWidth: "80vw",
                borderRadius: 999,
                background: "rgba(15,23,42,0.72)",
                color: "#fff",
                padding: "7px 12px",
                fontSize: 12,
                fontWeight: 700,
                whiteSpace: "nowrap",
                overflow: "hidden",
                textOverflow: "ellipsis",
              }}
            >
              {evidencePreview.name}
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
              <h3 style={{ margin: 0 }}>
                {signatureModal.personName
                  ? `Firma de ${signatureModal.personName}`
                  : signatureModal.field?.label || "Capturar firma"}
              </h3>
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
                {signatureModal.personName
                  ? `${signatureModal.personName} debe firmar dentro del recuadro. `
                  : "Firma dentro del recuadro. "}
                Si te equivocas puedes limpiar y volver a firmar.
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