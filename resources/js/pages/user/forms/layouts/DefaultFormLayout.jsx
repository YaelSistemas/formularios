// resources/js/pages/user/forms/layouts/DefaultFormLayout.jsx
import React from "react";

const NON_INPUT_TYPES = new Set(["static_text", "separator", "fixed_image", "fixed_file"]);

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
}) {
  const renderBasicInput = (f) => {
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
          value={answers[f.id] ?? ""}
          onChange={(e) => setVal(f.id, e.target.value)}
          rows={4}
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
        style={commonStyle}
      />
    );
  };

  const renderField = (f) => {
    if (f.type === "static_text") {
      return <div style={{ lineHeight: 1.5, color: "#111827" }}>{f.text || "Texto fijo"}</div>;
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
        <div style={{ overflowX: "auto" }}>
          <table
            style={{
              width: "100%",
              borderCollapse: "collapse",
              minWidth: 320,
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
                          padding: 10,
                          fontSize: 13,
                        }}
                      >
                        {String(row?.[col] ?? "—")}
                      </td>
                    ))}
                  </tr>
                ))
              ) : (
                <tr>
                  <td
                    colSpan={columns.length || 1}
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
      );
    }

    return renderBasicInput(f);
  };

  const shouldShowLabel = (f) => {
    if (f.type === "separator") return false;
    return true;
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
        <h2 style={{ margin: 0 }}>Llenar: {form?.title}</h2>
        <button type="button" onClick={onBack}>
          Volver
        </button>
      </div>

      {msg ? <p style={{ marginTop: 10, whiteSpace: "pre-line" }}>{msg}</p> : null}

      {!isOnline ? (
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

      <form onSubmit={onSubmit} style={{ marginTop: 12, display: "grid", gap: 12, maxWidth: 640 }}>
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

        <div style={{ display: "flex", justifyContent: "flex-end", gap: 10 }}>
          <button type="submit" disabled={saving}>
            {saving ? "Guardando..." : isOnline ? "Enviar" : "Guardar offline"}
          </button>
        </div>
      </form>
    </div>
  );
}