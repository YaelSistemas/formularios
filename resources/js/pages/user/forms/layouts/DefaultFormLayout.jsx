// resources/js/pages/user/forms/layouts/DefaultFormLayout.jsx
import React from "react";

const NON_INPUT_TYPES = new Set([
  "static_text",
  "separator",
  "fixed_image",
  "fixed_file",
]);

export default function DefaultFormLayout({
  form,
  fields,
  answers,
  setVal,
  saving,
  isOnline,
  msg,
  onBack,
  onSubmit,
  readOnly = false,
  isEditing = false,
}) {
  const commonStyle = {
    width: "100%",
    padding: 10,
    borderRadius: 8,
    border: "1px solid #ccc",
    background: readOnly ? "#f8fafc" : "#fff",
    color: "#111827",
  };

  const buttonStyle = {
    borderRadius: 10,
    border: "1px solid #d1d5db",
    background: "#fff",
    color: "#111827",
    padding: "10px 14px",
    cursor: "pointer",
    fontWeight: 700,
  };

  const primaryButtonStyle = {
    borderRadius: 10,
    border: "1px solid #2563eb",
    background: "#2563eb",
    color: "#fff",
    padding: "10px 16px",
    cursor: saving || readOnly ? "not-allowed" : "pointer",
    fontWeight: 700,
    opacity: saving || readOnly ? 0.7 : 1,
  };

  const setTableCellValue = (fieldId, rowIndex, colName, value) => {
    if (readOnly) return;

    const rows = Array.isArray(answers[fieldId]) ? answers[fieldId] : [];
    const nextRows = rows.map((row, idx) =>
      idx === rowIndex
        ? {
            ...(row || {}),
            [colName]: value,
          }
        : row
    );

    setVal(fieldId, nextRows);
  };

  const addTableRow = (fieldId, columns) => {
    if (readOnly) return;

    const rows = Array.isArray(answers[fieldId]) ? answers[fieldId] : [];
    const newRow = {};

    (columns || []).forEach((col) => {
      newRow[col] = "";
    });

    setVal(fieldId, [...rows, newRow]);
  };

  const removeTableRow = (fieldId, rowIndex) => {
    if (readOnly) return;

    const rows = Array.isArray(answers[fieldId]) ? answers[fieldId] : [];
    const nextRows = rows.filter((_, idx) => idx !== rowIndex);
    setVal(fieldId, nextRows);
  };

  const renderBasicInput = (f) => {
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
              <label
                key={opt}
                style={{ display: "inline-flex", alignItems: "center", gap: 8 }}
              >
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

  const renderField = (f) => {
    if (f.type === "static_text") {
      return (
        <div style={{ lineHeight: 1.5, color: "#111827" }}>
          {f.text || "Texto fijo"}
        </div>
      );
    }

    if (f.type === "separator") {
      return <hr style={{ border: 0, borderTop: "1px solid rgba(0,0,0,0.15)" }} />;
    }

    if (f.type === "fixed_image") {
      const url = f.url || "";
      if (!url) return <div style={{ fontSize: 12, opacity: 0.7 }}>Imagen fija sin URL</div>;

      return (
        <div style={{ textAlign: "center" }}>
          <img
            src={url}
            alt={f.label || "Imagen"}
            style={{ maxWidth: "100%", borderRadius: 10 }}
          />
        </div>
      );
    }

    if (f.type === "fixed_file") {
      const url = f.url || "";
      if (!url) return <div style={{ fontSize: 12, opacity: 0.7 }}>Archivo fijo sin URL</div>;

      return (
        <a
          href={url}
          target="_blank"
          rel="noreferrer"
          style={{
            display: "inline-flex",
            gap: 8,
            alignItems: "center",
            padding: "10px 12px",
            borderRadius: 8,
            border: "1px solid rgba(0,0,0,0.15)",
            textDecoration: "none",
          }}
        >
          📎 Abrir archivo
        </a>
      );
    }

    if (f.type === "table") {
      const rows = Array.isArray(answers[f.id]) ? answers[f.id] : [];
      const columns = Array.isArray(f.columns) ? f.columns : [];

      return (
        <div style={{ display: "grid", gap: 10 }}>
          <div style={{ overflowX: "auto" }}>
            <table
              style={{
                width: "100%",
                borderCollapse: "collapse",
                minWidth: 420,
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
                      {columns.map((col, idx) => (
                        <td
                          key={`${f.id}_${rowIndex}_${idx}`}
                          style={{
                            border: "1px solid #d1d5db",
                            padding: 8,
                            fontSize: 13,
                            verticalAlign: "middle",
                          }}
                        >
                          {readOnly ? (
                            <span>{String(row?.[col] ?? "—")}</span>
                          ) : (
                            <input
                              type="text"
                              value={row?.[col] ?? ""}
                              onChange={(e) =>
                                setTableCellValue(f.id, rowIndex, col, e.target.value)
                              }
                              style={{
                                width: "100%",
                                padding: 8,
                                borderRadius: 6,
                                border: "1px solid #cbd5e1",
                                background: "#fff",
                              }}
                            />
                          )}
                        </td>
                      ))}

                      {!readOnly ? (
                        <td
                          style={{
                            border: "1px solid #d1d5db",
                            padding: 8,
                            textAlign: "center",
                            verticalAlign: "middle",
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
                              padding: "8px 10px",
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
                      {readOnly ? "Sin registros capturados" : "Aún no hay filas capturadas"}
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {!readOnly ? (
            <div>
              <button
                type="button"
                onClick={() => addTableRow(f.id, columns)}
                style={{
                  borderRadius: 10,
                  border: "1px solid #86efac",
                  background: "#ecfdf5",
                  color: "#166534",
                  padding: "10px 14px",
                  cursor: "pointer",
                  fontWeight: 700,
                }}
              >
                Agregar fila
              </button>
            </div>
          ) : null}
        </div>
      );
    }

    return renderBasicInput(f);
  };

  const shouldShowLabel = (f) => {
    if (f.type === "separator") return false;
    return true;
  };

  const getTitle = () => {
    if (readOnly) return `Ver: ${form?.title}`;
    if (isEditing) return `Editar: ${form?.title}`;
    return `Llenar: ${form?.title}`;
  };

  const getSubmitText = () => {
    if (saving) {
      return isEditing ? "Actualizando..." : "Guardando...";
    }

    if (isEditing) {
      return "Actualizar registro";
    }

    return isOnline ? "Enviar" : "Guardar offline";
  };

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
        <h2 style={{ margin: 0 }}>{getTitle()}</h2>

        <button type="button" onClick={onBack} style={buttonStyle}>
          Volver
        </button>
      </div>

      {msg ? <p style={{ marginTop: 10, whiteSpace: "pre-line" }}>{msg}</p> : null}

      {!isOnline && !readOnly && !isEditing ? (
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

      {!isOnline && isEditing ? (
        <div
          style={{
            marginTop: 10,
            fontSize: 12,
            padding: 10,
            borderRadius: 8,
            border: "1px solid #fecaca",
            background: "#fff7ed",
            color: "#9a3412",
          }}
        >
          Estás sin conexión. La edición de registros existentes requiere internet para guardar cambios.
        </div>
      ) : null}

      <form
        onSubmit={onSubmit}
        style={{
          marginTop: 12,
          display: "grid",
          gap: 12,
          maxWidth: 640,
        }}
      >
        {fields.map((f) => {
          if (!f?.id) return null;

          return (
            <div key={f.id} style={{ display: "grid", gap: 6 }}>
              {shouldShowLabel(f) ? (
                <label>
                  <b>{f.label}</b>{" "}
                  {f.required && !NON_INPUT_TYPES.has(f.type) ? (
                    <span style={{ color: "crimson" }}>*</span>
                  ) : null}
                </label>
              ) : null}

              {renderField(f)}
            </div>
          );
        })}

        {!readOnly ? (
          <div style={{ display: "flex", justifyContent: "flex-end", gap: 10 }}>
            <button
              type="submit"
              disabled={saving || (isEditing && !isOnline)}
              style={primaryButtonStyle}
            >
              {getSubmitText()}
            </button>
          </div>
        ) : null}
      </form>
    </div>
  );
}