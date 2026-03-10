import React, { useEffect, useRef, useState } from "react";

export default function SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil({
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
}) {
  const [collapsedSections, setCollapsedSections] = useState({
    indicaciones_toggle: false,
  });

  const [tableModal, setTableModal] = useState({
    open: false,
    field: null,
  });

  const [tableRowDraft, setTableRowDraft] = useState({});

  const [signatureModal, setSignatureModal] = useState({
    open: false,
    field: null,
  });

  const signatureCanvasRef = useRef(null);
  const signatureWrapperRef = useRef(null);
  const drawingRef = useRef(false);
  const lastPointRef = useRef({ x: 0, y: 0 });

  const toggleSection = (id) => {
    setCollapsedSections((prev) => ({
      ...prev,
      [id]: !prev[id],
    }));
  };

  const openTableModal = (field) => {
    if (readOnly) return;

    const rowSchema = Array.isArray(field?.row_schema) ? field.row_schema : [];
    const init = {};

    rowSchema.forEach((col) => {
      if (col.type === "checkbox") init[col.id] = false;
      else init[col.id] = "";
    });

    setTableRowDraft(init);
    setTableModal({
      open: true,
      field,
    });
  };

  const closeTableModal = () => {
    setTableModal({
      open: false,
      field: null,
    });
    setTableRowDraft({});
  };

  const setTableRowVal = (id, value) => {
    setTableRowDraft((prev) => ({
      ...prev,
      [id]: value,
    }));
  };

  const addTableRow = () => {
    const field = tableModal.field;
    if (!field) return;

    const rowSchema = Array.isArray(field.row_schema) ? field.row_schema : [];

    for (const col of rowSchema) {
      if (!col?.id || !col.required) continue;

      const v = tableRowDraft[col.id];

      if (col.type === "checkbox") {
        if (!v) {
          setMsg(`Falta responder: ${col.label}`);
          return;
        }
        continue;
      }

      if (v === null || v === undefined || String(v).trim() === "") {
        setMsg(`Falta responder: ${col.label}`);
        return;
      }

      if (col.type === "select" || col.type === "radio") {
        const opts = Array.isArray(col.options) ? col.options : [];
        if (opts.length && !opts.includes(v)) {
          setMsg(`Selecciona una opción válida para: ${col.label}`);
          return;
        }
      }
    }

    setVal(field.id, [
      ...(Array.isArray(answers[field.id]) ? answers[field.id] : []),
      { ...tableRowDraft },
    ]);
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
    setMsg("");
    closeSignatureModal();
  };

  const removeSignature = (fieldId) => {
    if (readOnly) return;
    setVal(fieldId, "");
  };

  const renderBasicInput = (f) => {
    const commonStyle = {
      width: "100%",
      padding: 10,
      borderRadius: 8,
      border: "1px solid #ccc",
      background: readOnly ? "#f8fafc" : "#fff",
    };

    if (f.type === "textarea") {
      return (
        <textarea
          value={answers[f.id] ?? ""}
          onChange={(e) => setVal(f.id, e.target.value)}
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
            type="checkbox"
            checked={!!answers[f.id]}
            onChange={(e) => setVal(f.id, e.target.checked)}
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
          value={answers[f.id] ?? ""}
          onChange={(e) => setVal(f.id, e.target.value)}
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
              <label key={opt} style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
                <input
                  type="radio"
                  name={f.id}
                  value={opt}
                  checked={answers[f.id] === opt}
                  onChange={(e) => setVal(f.id, e.target.value)}
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
                border: "1px solid #d1d5db",
                borderRadius: 10,
                background: "#fff",
                padding: 10,
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
                border: "1px dashed #cbd5e1",
                borderRadius: 10,
                background: "#f8fafc",
                padding: 16,
                textAlign: "center",
                color: "#64748b",
              }}
            >
              Sin firma capturada
            </div>
          )}

          {!readOnly ? (
            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button
                type="button"
                onClick={() => openSignatureModal(f)}
                style={{
                  borderRadius: 10,
                  border: "1px solid #c7d2fe",
                  background: "#eef2ff",
                  color: "#1e40af",
                  padding: "10px 12px",
                  cursor: "pointer",
                  fontWeight: 800,
                }}
              >
                {value ? "Volver a firmar" : "Capturar firma"}
              </button>

              {value ? (
                <button
                  type="button"
                  onClick={() => removeSignature(f.id)}
                  style={{
                    borderRadius: 10,
                    border: "1px solid #fecaca",
                    background: "#fef2f2",
                    color: "#b91c1c",
                    padding: "10px 12px",
                    cursor: "pointer",
                    fontWeight: 800,
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
        type={htmlType}
        value={answers[f.id] ?? ""}
        onChange={(e) => setVal(f.id, e.target.value)}
        disabled={readOnly}
        style={commonStyle}
      />
    );
  };

  const renderTableModalField = (f) => {
    const commonStyle = {
      width: "100%",
      padding: 10,
      borderRadius: 8,
      border: "1px solid #ccc",
      background: "#fff",
    };

    if (f.type === "textarea") {
      return (
        <textarea
          value={tableRowDraft[f.id] ?? ""}
          onChange={(e) => setTableRowVal(f.id, e.target.value)}
          rows={3}
          style={{ ...commonStyle, resize: "vertical" }}
        />
      );
    }

    if (f.type === "checkbox") {
      return (
        <label style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
          <input
            type="checkbox"
            checked={!!tableRowDraft[f.id]}
            onChange={(e) => setTableRowVal(f.id, e.target.checked)}
          />
          <span>Marcar</span>
        </label>
      );
    }

    if (f.type === "select" || f.type === "list") {
      const opts = Array.isArray(f.options) ? f.options : [];
      return (
        <select
          value={tableRowDraft[f.id] ?? ""}
          onChange={(e) => setTableRowVal(f.id, e.target.value)}
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
                  flexWrap: "wrap",
                }}
              >
                <input
                  type="radio"
                  name={`modal_${tableModal.field?.id || "table"}_${f.id}`}
                  value={opt}
                  checked={tableRowDraft[f.id] === opt}
                  onChange={(e) => setTableRowVal(f.id, e.target.value)}
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

    return (
      <input
        type={htmlType}
        value={tableRowDraft[f.id] ?? ""}
        onChange={(e) => setTableRowVal(f.id, e.target.value)}
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
            style={{ maxWidth: "100%", width: 420, borderRadius: 10 }}
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
          <div style={{ overflowX: "auto" }}>
            <table
              style={{
                width: "100%",
                borderCollapse: "collapse",
                minWidth: 1200,
                background: "#fff",
                border: "1px solid #d1d5db",
              }}
            >
              <thead>
                <tr>
                  {columns.map((col, idx) => (
                    <th
                      key={`${f.id}_col_${idx}`}
                      style={{
                        border: "1px solid #d1d5db",
                        padding: 10,
                        background: "#f8fafc",
                        textAlign: "left",
                        fontSize: 12,
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
                        padding: 10,
                        background: "#f8fafc",
                        textAlign: "center",
                        fontSize: 12,
                        width: 90,
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
                            padding: 10,
                            fontSize: 13,
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
                            padding: 10,
                            textAlign: "center",
                            verticalAlign: "top",
                          }}
                        >
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
                            }}
                          >
                            Quitar
                          </button>
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
                  borderRadius: 10,
                  border: "1px solid #c7d2fe",
                  background: "#eef2ff",
                  color: "#1e40af",
                  padding: "10px 12px",
                  cursor: "pointer",
                  fontWeight: 800,
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
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
  const tablaHerramientas = getField("tabla_herramientas");

  const isCollapsed = !!collapsedSections[indicacionesToggle?.id];

  return (
    <div style={{ padding: 16 }}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          gap: 10,
          alignItems: "center",
          flexWrap: "wrap",
        }}
      >
        <div>
          <h2 style={{ margin: 0 }}>
            {readOnly ? "Ver respuesta" : `Llenar: ${form?.title}`}
          </h2>

          {readOnly && responseMeta ? (
            <div style={{ fontSize: 12, color: "#64748b", marginTop: 4 }}>
              Respuesta #{responseMeta.id} • Usuario: {responseMeta.user_id} • Fecha:{" "}
              {responseMeta.created_at ? new Date(responseMeta.created_at).toLocaleString() : "—"}
            </div>
          ) : null}
        </div>

        <button type="button" onClick={onBack}>
          Volver
        </button>
      </div>

      {msg ? <p style={{ marginTop: 10, whiteSpace: "pre-line" }}>{msg}</p> : null}

      {!isOnline && !readOnly ? (
        <div
          style={{
            marginTop: 10,
            fontSize: 12,
            padding: 10,
            borderRadius: 8,
            border: "1px solid rgba(0,0,0,0.15)",
            opacity: 0.9,
          }}
        >
          Estás en <b>modo offline</b>. Al enviar, se guardará en el dispositivo y se sincronizará después.
        </div>
      ) : null}

      <form onSubmit={onSubmit}>
        <div
          style={{
            marginTop: 12,
            maxWidth: 900,
            marginInline: "auto",
            background: "#fff",
            border: "1px solid #d1d5db",
            borderRadius: 14,
            padding: 20,
            display: "grid",
            gap: 14,
          }}
        >
          {logo ? (
            <div style={{ display: "flex", justifyContent: "center" }}>
              <div style={{ width: "100%", maxWidth: 420, textAlign: "center" }}>
                {renderField(logo)}
              </div>
            </div>
          ) : null}

          <div
            style={{
              display: "grid",
              gap: 6,
              justifyItems: "end",
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
                      ? 18
                      : line.id === "header_line_2"
                      ? 15
                      : 14,
                  color: "#111827",
                  textAlign: "left",
                }}
              >
                {line.text}
              </div>
            ))}
          </div>

          {taller ? (
            <div style={{ display: "grid", gap: 6 }}>
              <label>
                <b>{taller.label}</b> {taller.required ? <span style={{ color: "crimson" }}>*</span> : null}
              </label>
              {renderField(taller)}
            </div>
          ) : null}

          {nombreInspector ? (
            <div style={{ display: "grid", gap: 6 }}>
              <label>
                <b>{nombreInspector.label}</b> {nombreInspector.required ? <span style={{ color: "crimson" }}>*</span> : null}
              </label>
              {renderField(nombreInspector)}
            </div>
          ) : null}

          {firmaInspector ? (
            <div style={{ display: "grid", gap: 6 }}>
              <label>
                <b>{firmaInspector.label}</b> {firmaInspector.required ? <span style={{ color: "crimson" }}>*</span> : null}
              </label>
              {renderField(firmaInspector)}
            </div>
          ) : null}

          {indicacionesToggle ? (
            <div
              style={{
                border: "1px solid #e5e7eb",
                borderRadius: 12,
                overflow: "hidden",
              }}
            >
              <button
                type="button"
                onClick={() => toggleSection(indicacionesToggle.id)}
                style={{
                  width: "100%",
                  padding: "12px 14px",
                  background: "#f8fafc",
                  border: "none",
                  borderBottom: isCollapsed ? "none" : "1px solid #e5e7eb",
                  textAlign: "left",
                  fontWeight: 800,
                  cursor: "pointer",
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                }}
              >
                <span>{indicacionesToggle.text || indicacionesToggle.label}</span>
                <span>{isCollapsed ? "＋" : "－"}</span>
              </button>

              {!isCollapsed ? (
                <div style={{ padding: 14, display: "grid", gap: 12, background: "#fff" }}>
                  {indicacion1 ? <div style={{ color: "#111827", lineHeight: 1.5 }}>{indicacion1.text}</div> : null}
                  {indicacion2 ? <div style={{ color: "#111827", lineHeight: 1.5 }}>{indicacion2.text}</div> : null}

                  {tablaHerramientas ? (
                    <div style={{ display: "grid", gap: 8 }}>
                      <div style={{ fontWeight: 800 }}>Criterios a inspeccionar</div>
                      {renderField(tablaHerramientas)}
                    </div>
                  ) : null}
                </div>
              ) : null}
            </div>
          ) : null}
        </div>

        {!readOnly ? (
          <div
            style={{
              marginTop: 16,
              maxWidth: 900,
              marginInline: "auto",
              display: "flex",
              justifyContent: "flex-end",
              gap: 10,
            }}
          >
            <button
              type="submit"
              disabled={saving}
              style={{
                borderRadius: 10,
                border: "1px solid #e4e4e7",
                background: "#fff",
                padding: "10px 14px",
                cursor: saving ? "not-allowed" : "pointer",
                fontWeight: 800,
              }}
            >
              {saving ? "Guardando..." : isOnline ? "Enviar" : "Guardar offline"}
            </button>
          </div>
        ) : null}
      </form>

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
          onClick={closeTableModal}
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
              <h3 style={{ margin: 0 }}>Agregar registro</h3>
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

            <div style={{ padding: 16, display: "grid", gap: 12 }}>
              {(Array.isArray(tableModal.field.row_schema) ? tableModal.field.row_schema : []).map((col) => (
                <div key={col.id} style={{ display: "grid", gap: 6 }}>
                  <label>
                    <b>{col.label}</b> {col.required ? <span style={{ color: "crimson" }}>*</span> : null}
                  </label>
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
                onClick={addTableRow}
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
                Guardar fila
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
              <div style={{ fontSize: 13, color: "#475569" }}>
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