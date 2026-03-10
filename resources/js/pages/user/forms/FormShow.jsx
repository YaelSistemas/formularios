import React, { useMemo } from "react";
import { useParams } from "react-router-dom";

// ✅ Catálogo local (debe ser el mismo concepto que en FormsIndex)
// Si quieres, después lo movemos a /resources/js/forms/catalog.js para no repetirlo.
const FORMS_CATALOG = [
  {
    key: "herramienta",
    title: "Formulario de Herramienta",
    description: "Registro de herramienta, responsable, estado y observaciones.",
    status: "PUBLICADO",

    // ✅ definición simple por código (ejemplo)
    schema: {
      fields: [
        { id: "nombre_responsable", label: "Nombre del responsable", type: "text", required: true },
        { id: "area", label: "Área / Departamento", type: "text", required: true },
        { id: "herramienta", label: "Herramienta", type: "text", required: true },
        {
          id: "estado",
          label: "Estado",
          type: "select",
          required: true,
          options: ["BUENO", "REGULAR", "MALO"],
        },
        { id: "observaciones", label: "Observaciones", type: "textarea", required: false },
        { id: "confirmo", label: "Confirmo que la información es correcta", type: "checkbox", required: true },
      ],
    },
  },
];

export default function FormShow() {
  const { id } = useParams(); // ahora es formKey: "herramienta", etc.

  const item = useMemo(() => {
    return FORMS_CATALOG.find((f) => f.key === id) || null;
  }, [id]);

  const badgeStyle = (status) => ({
    display: "inline-flex",
    alignItems: "center",
    padding: "4px 10px",
    borderRadius: 999,
    fontSize: 12,
    fontWeight: 800,
    border: status === "PUBLICADO" ? "1px solid #86efac" : "1px solid #e5e7eb",
    background: status === "PUBLICADO" ? "#ecfdf5" : "#f8fafc",
    color: status === "PUBLICADO" ? "#166534" : "#334155",
  });

  const card = {
    border: "1px solid #e5e7eb",
    borderRadius: 14,
    padding: 16,
    background: "#fff",
  };

  const btn = (variant = "dark") => ({
    display: "inline-flex",
    alignItems: "center",
    gap: 8,
    padding: "10px 14px",
    borderRadius: 10,
    fontWeight: 800,
    textDecoration: "none",
    border: "1px solid #e5e7eb",
    ...(variant === "dark"
      ? { background: "#0f172a", color: "#fff", borderColor: "#0f172a" }
      : { background: "#fff", color: "#0f172a" }),
  });

  if (!item) {
    return (
      <div style={{ padding: 16 }}>
        <h2 style={{ marginTop: 0 }}>Formulario no encontrado</h2>
        <div style={{ color: "#64748b" }}>
          No existe un formulario con clave: <b>{id}</b>
        </div>

        <div style={{ marginTop: 14 }}>
          <a href="/formularios" style={btn("light")}>
            ← Volver
          </a>
        </div>
      </div>
    );
  }

  const fields = item?.schema?.fields || [];

  return (
    <div style={{ padding: 16 }}>
      <div style={{ display: "flex", justifyContent: "space-between", gap: 12, alignItems: "center" }}>
        <div>
          <div style={badgeStyle(item.status)}>{item.status}</div>
          <h2 style={{ margin: "8px 0 0 0" }}>{item.title}</h2>
          <div style={{ color: "#64748b", marginTop: 6 }}>{item.description || "—"}</div>
        </div>

        {/* ✅ aquí decides a qué ruta mandas para llenarlo */}
        {/* Opción 1: /formularios/:id/nuevo (si tienes esa ruta) */}
        {/* Opción 2: /formularios/nuevo?form=herramienta */}
        <a href={`/formularios/nuevo?form=${encodeURIComponent(item.key)}`} style={btn("dark")}>
          Llenar formulario
        </a>
      </div>

      <div style={{ height: 12 }} />

      <div style={card}>
        <div style={{ fontWeight: 900, marginBottom: 8 }}>Campos</div>

        {fields.length ? (
          <ul style={{ margin: 0, paddingLeft: 18 }}>
            {fields.map((f) => (
              <li key={f.id} style={{ marginBottom: 6, color: "#0f172a" }}>
                <b>{f.label}</b>{" "}
                <span style={{ color: "#64748b" }}>
                  — {f.type}
                  {f.required ? " (obligatorio)" : ""}
                </span>
              </li>
            ))}
          </ul>
        ) : (
          <div style={{ color: "#64748b" }}>Este formulario no tiene campos.</div>
        )}
      </div>

      <div style={{ marginTop: 14 }}>
        <a href="/formularios" style={btn("light")}>
          ← Volver
        </a>
      </div>
    </div>
  );
}