import React from "react";

// ✅ Catálogo de formularios por código
const FORMS_CATALOG = [
  {
    key: "herramienta",
    title: "Formulario de Herramienta",
    description: "Registro de herramienta, responsable, estado y observaciones.",
    status: "PUBLICADO",
  },

  // Ejemplos para después:
  // {
  //   key: "inspeccion_equipo",
  //   title: "Inspección de Equipo",
  //   description: "Checklist de revisión de equipo.",
  //   status: "PUBLICADO",
  // },
  // {
  //   key: "salida_material",
  //   title: "Salida de Material",
  //   description: "Control de salida de materiales y evidencia.",
  //   status: "BORRADOR",
  // },
];

export default function FormsIndex() {
  const items = FORMS_CATALOG.filter((f) => f.status === "PUBLICADO");

  const cardStyle = {
    border: "1px solid #e5e7eb",
    borderRadius: 14,
    padding: 16,
    background: "#fff",
    display: "flex",
    flexDirection: "column",
    gap: 8,
  };

  const badgeStyle = (status) => ({
    display: "inline-flex",
    alignItems: "center",
    padding: "4px 10px",
    borderRadius: 999,
    fontSize: 12,
    fontWeight: 700,
    border: "1px solid #d1fae5",
    background: status === "PUBLICADO" ? "#ecfdf5" : "#f8fafc",
    color: status === "PUBLICADO" ? "#065f46" : "#334155",
    width: "fit-content",
  });

  const btnStyle = {
    display: "inline-flex",
    alignItems: "center",
    justifyContent: "center",
    padding: "10px 14px",
    borderRadius: 10,
    background: "#0f172a",
    color: "#fff",
    textDecoration: "none",
    fontWeight: 700,
    width: "fit-content",
    marginTop: 6,
  };

  return (
    <div style={{ padding: 16 }}>
      <div style={{ display: "flex", justifyContent: "space-between", gap: 10, alignItems: "center", marginBottom: 16 }}>
        <div>
          <h2 style={{ margin: 0 }}>Formularios</h2>
          <div style={{ fontSize: 13, color: "#64748b", marginTop: 4 }}>
            Selecciona un formulario para capturar información en línea o sin conexión.
          </div>
        </div>
      </div>

      {items.length ? (
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(280px, 1fr))",
            gap: 14,
          }}
        >
          {items.map((f) => (
            <div key={f.key} style={cardStyle}>
              <div style={badgeStyle(f.status)}>{f.status}</div>

              <div style={{ fontSize: 18, fontWeight: 800, color: "#0f172a" }}>
                {f.title}
              </div>

              <div style={{ fontSize: 14, color: "#475569", minHeight: 40 }}>
                {f.description || "Sin descripción."}
              </div>

              <a href={`/formularios/${f.key}`} style={btnStyle}>
                Abrir formulario
              </a>
            </div>
          ))}
        </div>
      ) : (
        <div
          style={{
            border: "1px dashed #cbd5e1",
            borderRadius: 14,
            padding: 18,
            background: "#f8fafc",
            color: "#475569",
          }}
        >
          No hay formularios publicados.
        </div>
      )}
    </div>
  );
}