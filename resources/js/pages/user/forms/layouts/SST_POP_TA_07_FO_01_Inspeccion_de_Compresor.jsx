import React, { useEffect, useRef, useState } from "react";

export default function SST_POP_TA_07_FO_01_Inspeccion_de_Compresor({
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
  const [collapsedSections, setCollapsedSections] = useState({
    indicaciones_toggle: false,
  });

  const [tableModal, setTableModal] = useState({
    open: false,
    field: null,
    editIndex: null,
  });

  const [tableRowDraft, setTableRowDraft] = useState({});
  const [tableModalError, setTableModalError] = useState("");
  const [tableModalErrorFieldId, setTableModalErrorFieldId] = useState(null);

  const [formFieldError, setFormFieldError] = useState("");
  const [formFieldErrorId, setFormFieldErrorId] = useState(null);

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

  const tableFieldRefs = useRef({});
  const tableFieldWrapRefs = useRef({});
  const tableErrorTimerRef = useRef(null);

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
      if (tableErrorTimerRef.current) {
        clearTimeout(tableErrorTimerRef.current);
      }
      if (formErrorTimerRef.current) {
        clearTimeout(formErrorTimerRef.current);
      }
    };
  }, []);

  const scrollToTopSafe = () => {
    try {
      topRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const toggleSection = (id) => {
    setCollapsedSections((prev) => ({
      ...prev,
      [id]: !prev[id],
    }));
  };

  const buildRowDraft = (field, preload = null) => {
    const rowSchema = Array.isArray(field?.row_schema) ? field.row_schema : [];
    const init = {};

    rowSchema.forEach((col) => {
      if (preload && preload[col.id] !== undefined) {
        init[col.id] = preload[col.id];
      } else if (col.type === "checkbox") {
        init[col.id] = false;
      } else {
        init[col.id] = "";
      }
    });

    return init;
  };

  const clearTableModalError = () => {
    setTableModalError("");
    setTableModalErrorFieldId(null);

    if (tableErrorTimerRef.current) {
      clearTimeout(tableErrorTimerRef.current);
      tableErrorTimerRef.current = null;
    }
  };

  const showTableModalFieldError = (fieldId, message) => {
    setTableModalError(message);
    setTableModalErrorFieldId(fieldId);

    if (tableErrorTimerRef.current) {
      clearTimeout(tableErrorTimerRef.current);
    }

    requestAnimationFrame(() => {
      const wrapEl = tableFieldWrapRefs.current[fieldId];
      const inputEl = tableFieldRefs.current[fieldId];

      if (wrapEl?.scrollIntoView) {
        wrapEl.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });
      }

      setTimeout(() => {
        if (inputEl?.focus) {
          inputEl.focus();
        }
      }, 180);
    });

    tableErrorTimerRef.current = setTimeout(() => {
      setTableModalError("");
      setTableModalErrorFieldId(null);
      tableErrorTimerRef.current = null;
    }, 3000);
  };

  const clearFormFieldError = () => {
    setFormFieldError("");
    setFormFieldErrorId(null);

    if (formErrorTimerRef.current) {
      clearTimeout(formErrorTimerRef.current);
      formErrorTimerRef.current = null;
    }
  };

  const showFormFieldError = (fieldId, message) => {
    setMsg("");
    setFormFieldError(message);
    setFormFieldErrorId(fieldId);

    if (formErrorTimerRef.current) {
      clearTimeout(formErrorTimerRef.current);
    }

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
        if (inputEl?.focus) {
          inputEl.focus();
        }
      }, 180);
    });

    formErrorTimerRef.current = setTimeout(() => {
      setFormFieldError("");
      setFormFieldErrorId(null);
      formErrorTimerRef.current = null;
    }, 3000);
  };

  const openTableModal = (field) => {
    if (readOnly) return;

    clearTableModalError();
    setTableRowDraft(buildRowDraft(field, null));
    setTableModal({
      open: true,
      field,
      editIndex: null,
    });
  };

  const openEditTableModal = (field, rowIndex) => {
    if (readOnly) return;

    const rows = Array.isArray(answers[field.id]) ? answers[field.id] : [];
    const currentRow = rows[rowIndex] || {};

    clearTableModalError();
    setTableRowDraft(buildRowDraft(field, currentRow));
    setTableModal({
      open: true,
      field,
      editIndex: rowIndex,
    });
  };

  const closeTableModal = () => {
    clearTableModalError();
    tableFieldRefs.current = {};
    tableFieldWrapRefs.current = {};

    setTableModal({
      open: false,
      field: null,
      editIndex: null,
    });
    setTableRowDraft({});
  };

  const setTableRowVal = (id, value) => {
    setTableRowDraft((prev) => ({
      ...prev,
      [id]: value,
    }));
  };

  const isObservationColumn = (col) => {
    const id = String(col?.id || "").toLowerCase();
    const label = String(col?.label || "").toLowerCase();
    return id === "observaciones" || label === "observaciones";
  };

  const isEmptyValue = (value, type) => {
    if (type === "checkbox") return value !== true;
    return value === null || value === undefined || String(value).trim() === "";
  };

  const hasTableDraftData = () => {
    const field = tableModal.field;
    if (!field) return false;

    const rowSchema = Array.isArray(field.row_schema) ? field.row_schema : [];

    return rowSchema.some((col) => {
      const value = tableRowDraft[col.id];

      if (col.type === "checkbox") {
        return value === true;
      }

      return value !== null && value !== undefined && String(value).trim() !== "";
    });
  };

  const saveTableRow = () => {
    const field = tableModal.field;
    if (!field) return;

    const rowSchema = Array.isArray(field.row_schema) ? field.row_schema : [];

    for (const col of rowSchema) {
      if (!col?.id) continue;
      if (isObservationColumn(col)) continue;

      const v = tableRowDraft[col.id];

      if (isEmptyValue(v, col.type)) {
        showTableModalFieldError(col.id, `Falta responder: ${col.label}`);
        return;
      }

      if (col.type === "select" || col.type === "radio") {
        const opts = Array.isArray(col.options) ? col.options : [];
        if (opts.length && !opts.includes(v)) {
          showTableModalFieldError(
            col.id,
            `Selecciona una opción válida para: ${col.label}`
          );
          return;
        }
      }
    }

    const currentRows = Array.isArray(answers[field.id]) ? answers[field.id] : [];

    if (tableModal.editIndex === null || tableModal.editIndex === undefined) {
      setVal(field.id, [...currentRows, { ...tableRowDraft }]);
    } else {
      const nextRows = currentRows.map((row, idx) =>
        idx === tableModal.editIndex ? { ...tableRowDraft } : row
      );
      setVal(field.id, nextRows);
    }

    clearTableModalError();
    setMsg("");
    closeTableModal();
  };

  const removeTableRow = (fieldId, rowIndex) => {
    if (readOnly) return;

    const rows = Array.isArray(answers[fieldId]) ? answers[fieldId] : [];
    setVal(
      fieldId,
      rows.filter((_, idx) => idx !== rowIndex)
    );
  };

  const openSignatureModal = (field) => {
    if (readOnly) return;
    if (formFieldErrorId === field?.id) {
      clearFormFieldError();
    }
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
    if (formFieldErrorId === field.id) {
      clearFormFieldError();
    }
    setMsg("");
    closeSignatureModal();
  };

  const removeSignature = (fieldId) => {
    if (readOnly) return;
    setVal(fieldId, "");
    if (formFieldErrorId === fieldId) {
      clearFormFieldError();
    }
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
          : typeof value === "string" && value !== ""
          ? `/storage/${value}`
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

  const renderTableModalField = (f) => {
    const commonStyle = {
      width: "100%",
      padding: isMobile ? 11 : 10,
      borderRadius: 12,
      border:
        tableModalErrorFieldId === f.id ? "1px solid #fb923c" : "1px solid #d1d5db",
      background: "#fff",
      fontSize: isMobile ? 16 : 14,
      boxSizing: "border-box",
      minHeight: isMobile ? 46 : "auto",
      outline: "none",
      boxShadow:
        tableModalErrorFieldId === f.id
          ? "0 0 0 3px rgba(251,146,60,0.12)"
          : "none",
    };

    if (f.type === "textarea") {
      return (
        <textarea
          ref={(el) => {
            tableFieldRefs.current[f.id] = el;
          }}
          value={tableRowDraft[f.id] ?? ""}
          onChange={(e) => {
            setTableRowVal(f.id, e.target.value);
            if (tableModalErrorFieldId === f.id) clearTableModalError();
          }}
          rows={3}
          style={{ ...commonStyle, resize: "vertical" }}
        />
      );
    }

    if (f.type === "checkbox") {
      return (
        <label style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
          <input
            ref={(el) => {
              tableFieldRefs.current[f.id] = el;
            }}
            type="checkbox"
            checked={!!tableRowDraft[f.id]}
            onChange={(e) => {
              setTableRowVal(f.id, e.target.checked);
              if (tableModalErrorFieldId === f.id) clearTableModalError();
            }}
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
            tableFieldRefs.current[f.id] = el;
          }}
          value={tableRowDraft[f.id] ?? ""}
          onChange={(e) => {
            setTableRowVal(f.id, e.target.value);
            if (tableModalErrorFieldId === f.id) clearTableModalError();
          }}
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
        <div style={{ display: "grid", gap: 10 }}>
          {opts.length ? (
            opts.map((opt) => (
              <label
                key={opt}
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 10,
                  flexWrap: "wrap",
                  padding: 0,
                  border: "none",
                  background: "transparent",
                  borderRadius: 0,
                }}
              >
                <input
                  ref={(el) => {
                    if (el && tableRowDraft[f.id] === opt) {
                      tableFieldRefs.current[f.id] = el;
                    } else if (el && !tableFieldRefs.current[f.id]) {
                      tableFieldRefs.current[f.id] = el;
                    }
                  }}
                  type="radio"
                  name={`modal_${tableModal.field?.id || "table"}_${f.id}`}
                  value={opt}
                  checked={tableRowDraft[f.id] === opt}
                  onChange={(e) => {
                    setTableRowVal(f.id, e.target.value);
                    if (tableModalErrorFieldId === f.id) clearTableModalError();
                  }}
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

    const htmlType =
      f.type === "number"
        ? "number"
        : f.type === "date"
        ? "date"
        : f.type === "datetime"
        ? "datetime-local"
        : "text";

    if (isObservationColumn(f)) {
      return (
        <textarea
          ref={(el) => {
            tableFieldRefs.current[f.id] = el;
          }}
          value={tableRowDraft[f.id] ?? ""}
          onChange={(e) => {
            setTableRowVal(f.id, e.target.value);
            if (tableModalErrorFieldId === f.id) clearTableModalError();
          }}
          rows={3}
          style={{ ...commonStyle, resize: "vertical" }}
        />
      );
    }

    return (
      <input
        ref={(el) => {
          tableFieldRefs.current[f.id] = el;
        }}
        type={htmlType}
        value={tableRowDraft[f.id] ?? ""}
        onChange={(e) => {
          setTableRowVal(f.id, e.target.value);
          if (tableModalErrorFieldId === f.id) clearTableModalError();
        }}
        style={commonStyle}
      />
    );
  };

  const renderTableCellValue = (col, row) => {
    const value = row?.[col.id];

    if (value === null || value === undefined || value === "") return "—";
    if (typeof value === "boolean") return value ? "Sí" : "No";

    return String(value);
  };

  const renderField = (f) => {
    if (f.type === "fixed_image") {
      const url = f.url || "";
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

    if (f.type === "table") {
      const rows = Array.isArray(answers[f.id]) ? answers[f.id] : [];
      const columns = Array.isArray(f.columns) ? f.columns : [];
      const rowSchema = Array.isArray(f.row_schema) ? f.row_schema : [];

      return (
        <div style={{ display: "grid", gap: 10 }}>
          <div
            style={{
              overflowX: "auto",
              borderRadius: 12,
              border: "1px solid #d1d5db",
              background: "#fff",
            }}
          >
            <table
              style={{
                width: "100%",
                borderCollapse: "collapse",
                minWidth: isMobile ? 900 : 1200,
                background: "#fff",
              }}
            >
              <thead>
                <tr>
                  {columns.map((col, idx) => (
                    <th
                      key={`${f.id}_col_${idx}`}
                      style={{
                        border: "1px solid #d1d5db",
                        padding: isMobile ? 9 : 10,
                        background: "#f8fafc",
                        textAlign: "left",
                        fontSize: isMobile ? 11 : 12,
                        whiteSpace: "nowrap",
                      }}
                    >
                      {col}
                    </th>
                  ))}
                  {!readOnly ? (
                    <th
                      style={{
                        border: "1px solid #d1d5db",
                        padding: isMobile ? 9 : 10,
                        background: "#f8fafc",
                        textAlign: "center",
                        fontSize: isMobile ? 11 : 12,
                        width: 170,
                        whiteSpace: "nowrap",
                      }}
                    >
                      Acción
                    </th>
                  ) : null}
                </tr>
              </thead>
              <tbody>
                {rows.length ? (
                  rows.map((row, rowIndex) => (
                    <tr key={`${f.id}_row_${rowIndex}`}>
                      {rowSchema.map((col) => (
                        <td
                          key={`${f.id}_${rowIndex}_${col.id}`}
                          style={{
                            border: "1px solid #d1d5db",
                            padding: isMobile ? 9 : 10,
                            fontSize: isMobile ? 12 : 13,
                            verticalAlign: "top",
                          }}
                        >
                          {renderTableCellValue(col, row)}
                        </td>
                      ))}
                      {!readOnly ? (
                        <td
                          style={{
                            border: "1px solid #d1d5db",
                            padding: isMobile ? 9 : 10,
                            textAlign: "center",
                            verticalAlign: "top",
                          }}
                        >
                          <div
                            style={{
                              display: "inline-flex",
                              gap: 8,
                              flexWrap: "wrap",
                              justifyContent: "center",
                            }}
                          >
                            <button
                              type="button"
                              onClick={() => openEditTableModal(f, rowIndex)}
                              style={{
                                borderRadius: 8,
                                border: "1px solid #bfdbfe",
                                background: "#eff6ff",
                                color: "#1d4ed8",
                                padding: "6px 10px",
                                cursor: "pointer",
                                fontWeight: 700,
                                fontSize: isMobile ? 12 : 13,
                              }}
                            >
                              Editar
                            </button>

                            <button
                              type="button"
                              onClick={() => removeTableRow(f.id, rowIndex)}
                              style={{
                                borderRadius: 8,
                                border: "1px solid #fecaca",
                                background: "#fef2f2",
                                color: "#b91c1c",
                                padding: "6px 10px",
                                cursor: "pointer",
                                fontWeight: 700,
                                fontSize: isMobile ? 12 : 13,
                              }}
                            >
                              Quitar
                            </button>
                          </div>
                        </td>
                      ) : null}
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td
                      colSpan={columns.length + (readOnly ? 0 : 1)}
                      style={{
                        border: "1px solid #d1d5db",
                        padding: 12,
                        textAlign: "center",
                        color: "#64748b",
                        fontSize: isMobile ? 12 : 13,
                      }}
                    >
                      Sin registros capturados
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {!readOnly ? (
            <div style={{ display: "flex", justifyContent: "flex-end" }}>
              <button
                type="button"
                onClick={() => openTableModal(f)}
                style={{
                  borderRadius: 12,
                  border: "1px solid #c7d2fe",
                  background: "#eef2ff",
                  color: "#1e40af",
                  padding: isMobile ? "10px 14px" : "10px 12px",
                  cursor: "pointer",
                  fontWeight: 800,
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  fontSize: isMobile ? 14 : 14,
                }}
              >
                <i className="fa-solid fa-circle-plus"></i>
                Agregar fila
              </button>
            </div>
          ) : null}
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
  const nombreInspector = getField("nombre_inspector");
  const firmaInspector = getField("firma_inspector");
  const indicacionesToggle = getField("indicaciones_toggle");
  const indicacion1 = getField("indicaciones_line_1");
  const indicacion2 = getField("indicaciones_line_2");
  const indicacion3 = getField("indicaciones_line_3");
  const tablaCompresor = getField("tabla_compresor");
  const responsableSeguridad = getField("responsable_seguridad");
  const firmaResponsableSeguridad = getField("firma_responsable_seguridad");

  const isCollapsed = !!collapsedSections[indicacionesToggle?.id];

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

  const renderOuterRequiredField = (f) => {
    if (!f) return null;

    return (
      <div
        ref={(el) => {
          formFieldWrapRefs.current[f.id] = el;
        }}
        style={getOuterFieldBlockStyle(f.id)}
      >
        <label
          style={{
            fontSize: isMobile ? 14 : 14,
            color: "#0f172a",
            lineHeight: 1.4,
          }}
        >
          <b>{f.label}</b> <span style={{ color: "crimson" }}>*</span>
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

  const compresorImagenTop =
    "/images/forms/SST_POP_TA_07_FO_01_Inspeccion_de_Compresor/Imagen_Compresor.png";

  const compresorImagenCategorias =
    "/images/forms/SST_POP_TA_07_FO_01_Inspeccion_de_Compresor/Tipo_Categorias_Presion.png";

  const validateSimpleRequiredField = (field, emptyMessage = null) => {
    if (!field) return false;

    const value = answers[field.id];

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

    if (
      field.type === "select" ||
      field.type === "list" ||
      field.type === "radio"
    ) {
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

    if (!validateSimpleRequiredField(taller)) {
      return false;
    }

    if (!validateSimpleRequiredField(nombreInspector)) {
      return false;
    }

    if (!validateSimpleRequiredField(firmaInspector)) {
      return false;
    }

    if (!tablaCompresor) {
      setMsg("No se encontró la tabla del compresor.");
      scrollToTopSafe();
      return false;
    }

    const rows = Array.isArray(answers[tablaCompresor.id])
      ? answers[tablaCompresor.id]
      : [];

    if (!rows.length) {
      setMsg("Debes agregar al menos una fila en la tabla de criterios a inspeccionar.");
      scrollToTopSafe();
      return false;
    }

    const rowSchema = Array.isArray(tablaCompresor.row_schema)
      ? tablaCompresor.row_schema
      : [];

    for (let i = 0; i < rows.length; i += 1) {
      const row = rows[i] || {};

      for (const col of rowSchema) {
        if (!col?.id) continue;
        if (isObservationColumn(col)) continue;

        const value = row[col.id];

        if (isEmptyValue(value, col.type)) {
          setMsg(`En la fila ${i + 1} falta responder: ${col.label}`);
          scrollToTopSafe();
          return false;
        }

        if (col.type === "select" || col.type === "radio") {
          const opts = Array.isArray(col.options) ? col.options : [];
          if (opts.length && !opts.includes(value)) {
            setMsg(
              `En la fila ${i + 1} selecciona una opción válida para: ${col.label}`
            );
            scrollToTopSafe();
            return false;
          }
        }
      }
    }

    if (!validateSimpleRequiredField(responsableSeguridad)) {
      return false;
    }

    if (!validateSimpleRequiredField(firmaResponsableSeguridad)) {
      return false;
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
    if (readOnly) {
      return "Consulta la información registrada en este formulario.";
    }

    if (isEditing) {
      return "Modifica la información necesaria y actualiza el registro cuando termines.";
    }

    return "Completa la información solicitada y guarda el registro cuando termines.";
  };

  const getFooterStateText = () => {
    if (saving) {
      return isEditing ? "Actualizando registro..." : "Guardando registro...";
    }

    if (isEditing) {
      return isOnline ? "Listo para actualizar" : "Sin conexión para actualizar";
    }

    return isOnline ? "Listo para enviar" : "Listo para guardar offline";
  };

  const getSubmitText = () => {
    if (saving) {
      return isEditing ? "Actualizando..." : "Guardando...";
    }

    if (isEditing) {
      return "Actualizar registro";
    }

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
                    fontWeight: line.id === "header_line_3" ? 800 : 700,
                    fontSize:
                      line.id === "header_line_1"
                        ? isMobile
                          ? 14
                          : 18
                        : line.id === "header_line_2"
                        ? isMobile
                          ? 13
                          : 15
                        : isMobile
                        ? 12
                        : 14,
                    color: "#111827",
                    textAlign: "left",
                    lineHeight: isMobile ? 1.45 : 1.35,
                  }}
                >
                  {line.text}
                </div>
              ))}
            </div>

            {renderOuterRequiredField(taller)}

            {renderOuterRequiredField(nombreInspector)}

            {renderOuterRequiredField(firmaInspector)}

            {indicacionesToggle ? (
              <div
                style={{
                  border: "1px solid #e5e7eb",
                  borderRadius: 14,
                  overflow: "hidden",
                }}
              >
                <button
                  type="button"
                  onClick={() => toggleSection(indicacionesToggle.id)}
                  style={{
                    width: "100%",
                    padding: isMobile ? "14px 14px" : "14px 16px",
                    background: "#f8fafc",
                    border: "none",
                    borderBottom: isCollapsed ? "none" : "1px solid #e5e7eb",
                    textAlign: "left",
                    fontWeight: 800,
                    cursor: "pointer",
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    color: "#0f172a",
                    fontSize: isMobile ? 14 : 14,
                  }}
                >
                  <span>{indicacionesToggle.text || indicacionesToggle.label}</span>
                  <span>{isCollapsed ? "＋" : "－"}</span>
                </button>

                {!isCollapsed ? (
                  <div
                    style={{
                      padding: isMobile ? 14 : 16,
                      display: "grid",
                      gap: isMobile ? 14 : 14,
                      background: "#fff",
                    }}
                  >
                    <div
                      style={{
                        color: "#111827",
                        fontSize: isMobile ? 14 : 14,
                        lineHeight: 1.4,
                      }}
                    >
                      {indicacion1 ? <div>{indicacion1.text}</div> : null}

                      {indicacion2 ? (
                        <div style={{ marginTop: 2 }}>
                          {indicacion2.text}
                        </div>
                      ) : null}

                      {indicacion3 ? (
                        <div style={{ marginTop: 12 }}>
                          {indicacion3.text}
                        </div>
                      ) : null}
                    </div>

                    {tablaCompresor ? (
                      <div style={{ display: "grid", gap: 10 }}>
                        <div
                          style={{
                            fontWeight: 800,
                            color: "#0f172a",
                            fontSize: isMobile ? 14 : 14,
                            lineHeight: 1.4,
                          }}
                        >
                          Criterios a inspeccionar
                        </div>
                        {renderField(tablaCompresor)}
                      </div>
                    ) : null}
                  </div>
                ) : null}
              </div>
            ) : null}

            {renderOuterRequiredField(responsableSeguridad)}

            {renderOuterRequiredField(firmaResponsableSeguridad)}
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

      {!readOnly && tableModal.open && tableModal.field ? (
        <div
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(0,0,0,0.45)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
            zIndex: 1000,
          }}
          onClick={() => {
            if (!hasTableDraftData()) {
              closeTableModal();
            }
          }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{
              width: "100%",
              maxWidth: 760,
              maxHeight: "90vh",
              overflowY: "auto",
              background: "#fff",
              borderRadius: 14,
              border: "1px solid #d1d5db",
              overflowX: "hidden",
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
                position: "sticky",
                top: 0,
                background: "#fff",
                zIndex: 1,
              }}
            >
              <h3 style={{ margin: 0 }}>
                {tableModal.editIndex === null ? "Agregar registro" : "Editar registro"}
              </h3>
              <button
                type="button"
                onClick={closeTableModal}
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
              <div
                style={{
                  display: "grid",
                  gap: 14,
                  marginBottom: 4,
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "center",
                  }}
                >
                  <img
                    src={compresorImagenTop}
                    alt="Imagen compresor"
                    style={{
                      display: "block",
                      width: "100%",
                      maxWidth: isMobile ? 320 : 520,
                      maxHeight: isMobile ? 220 : 300,
                      objectFit: "contain",
                      borderRadius: 10,
                      background: "#fff",
                    }}
                  />
                </div>

                <div
                  style={{
                    borderTop: "1px solid #e5e7eb",
                  }}
                />

                <div
                  style={{
                    display: "flex",
                    justifyContent: "center",
                  }}
                >
                  <img
                    src={compresorImagenCategorias}
                    alt="Tipos y categorías de presión"
                    style={{
                      display: "block",
                      width: "100%",
                      maxWidth: isMobile ? 220 : 340,
                      maxHeight: isMobile ? 140 : 180,
                      objectFit: "contain",
                      borderRadius: 10,
                      background: "#fff",
                    }}
                  />
                </div>

                <div
                  style={{
                    borderTop: "1px solid #e5e7eb",
                  }}
                />
              </div>

              {(Array.isArray(tableModal.field.row_schema)
                ? tableModal.field.row_schema
                : []
              ).map((col) => (
                <div
                  key={col.id}
                  ref={(el) => {
                    tableFieldWrapRefs.current[col.id] = el;
                  }}
                  style={{
                    display: "grid",
                    gap: 8,
                    padding: tableModalErrorFieldId === col.id ? "10px" : 0,
                    borderRadius: 12,
                    background:
                      tableModalErrorFieldId === col.id ? "#fff7ed" : "transparent",
                    border:
                      tableModalErrorFieldId === col.id
                        ? "1px solid #fdba74"
                        : "1px solid transparent",
                    transition: "all 0.2s ease",
                  }}
                >
                  <label style={{ fontSize: isMobile ? 14 : 14, lineHeight: 1.4 }}>
                    <b>{col.label}</b>{" "}
                    {!isObservationColumn(col) ? (
                      <span style={{ color: "crimson" }}>*</span>
                    ) : null}
                  </label>

                  {tableModalErrorFieldId === col.id && tableModalError ? (
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
                      {tableModalError}
                    </div>
                  ) : null}

                  {renderTableModalField(col)}
                </div>
              ))}
            </div>

            <div
              style={{
                padding: 16,
                borderTop: "1px solid #e5e7eb",
                display: "flex",
                justifyContent: "flex-end",
                gap: 10,
                position: "sticky",
                bottom: 0,
                background: "#fff",
              }}
            >
              <button
                type="button"
                onClick={closeTableModal}
                style={{
                  borderRadius: 10,
                  border: "1px solid #e4e4e7",
                  background: "#fff",
                  padding: "10px 14px",
                  cursor: "pointer",
                  fontWeight: 700,
                }}
              >
                Cancelar
              </button>

              <button
                type="button"
                onClick={saveTableRow}
                style={{
                  borderRadius: 10,
                  border: "1px solid #c7d2fe",
                  background: "#eef2ff",
                  color: "#1e40af",
                  padding: "10px 14px",
                  cursor: "pointer",
                  fontWeight: 800,
                }}
              >
                {tableModal.editIndex === null ? "Guardar fila" : "Actualizar fila"}
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
              <h3 style={{ margin: 0 }}>Firma del inspector</h3>
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
                  onTouchMove={draw}
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
                <button
                  type="button"
                  onClick={closeSignatureModal}
                  style={{
                    borderRadius: 10,
                    border: "1px solid #e4e4e7",
                    background: "#fff",
                    padding: "10px 14px",
                    cursor: "pointer",
                    fontWeight: 700,
                  }}
                >
                  Cancelar
                </button>

                <button
                  type="button"
                  onClick={saveSignature}
                  style={{
                    borderRadius: 10,
                    border: "1px solid #c7d2fe",
                    background: "#eef2ff",
                    color: "#1e40af",
                    padding: "10px 14px",
                    cursor: "pointer",
                    fontWeight: 800,
                  }}
                >
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