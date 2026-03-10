import React, { useEffect, useMemo, useState } from "react";
import { apiPost } from "../../../services/api";
import { enqueue, syncNow } from "../../../offline/sync"; // ✅ ajusta ruta si tu carpeta es distinta

// ✅ Catálogo local de formularios (por código)
// Ideal: muévelo a /resources/js/forms/catalog.js para compartirlo con FormsIndex y FormShow
const FORMS_CATALOG = [
  {
    key: "herramienta",
    title: "Formulario de Herramienta",
    description: "Registro de herramienta, responsable, estado y observaciones.",
    status: "PUBLICADO",
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

// helpers
function getQueryParam(name) {
  const url = new URL(window.location.href);
  return url.searchParams.get(name);
}

function nowIsoLocal() {
  // YYYY-MM-DDTHH:mm (sin segundos)
  const d = new Date();
  const pad = (n) => String(n).padStart(2, "0");
  const yyyy = d.getFullYear();
  const mm = pad(d.getMonth() + 1);
  const dd = pad(d.getDate());
  const hh = pad(d.getHours());
  const mi = pad(d.getMinutes());
  return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
}

export default function FormNew() {
  const formKey = useMemo(() => getQueryParam("form") || "herramienta", []);
  const formDef = useMemo(() => FORMS_CATALOG.find((f) => f.key === formKey) || null, [formKey]);

  const fields = useMemo(() => (formDef?.schema?.fields ? formDef.schema.fields : []), [formDef]);

  // ✅ answers
  const [answers, setAnswers] = useState(() => {
    const init = {};
    for (const f of fields) {
      if (f.type === "checkbox") init[f.id] = false;
      else if (f.type === "datetime") init[f.id] = nowIsoLocal();
      else init[f.id] = "";
    }
    return init;
  });

  // si cambia formKey, recalcular defaults
  useEffect(() => {
    const init = {};
    for (const f of fields) {
      if (f.type === "checkbox") init[f.id] = false;
      else if (f.type === "datetime") init[f.id] = nowIsoLocal();
      else init[f.id] = "";
    }
    setAnswers(init);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formKey]);

  const [saving, setSaving] = useState(false);
  const [err, setErr] = useState("");
  const [toast, setToast] = useState(null); // { type:'success'|'info'|'danger', text }
  const showToast = (type, text) => {
    setToast({ type, text });
    setTimeout(() => setToast(null), 2800);
  };

  const isOnline = useMemo(() => typeof navigator !== "undefined" ? navigator.onLine : true, [toast]);

  const setFieldValue = (id, value) => {
    setAnswers((prev) => ({ ...prev, [id]: value }));
  };

  const validate = () => {
    if (!formDef) return "Formulario no válido.";
    if (formDef.status !== "PUBLICADO") return "Este formulario no está publicado.";

    for (const f of fields) {
      if (!f.required) continue;

      const v = answers[f.id];

      if (f.type === "checkbox") {
        if (!v) return `El campo "${f.label}" es obligatorio.`;
        continue;
      }

      if (v === null || v === undefined || String(v).trim() === "") {
        return `El campo "${f.label}" es obligatorio.`;
      }
    }

    return null;
  };

  const buildSubmissionPayload = () => {
    // Aquí defines tu estructura final de "registro" (submission)
    return {
      form_key: formDef.key,
      form_title: formDef.title,
      created_at_local: new Date().toISOString(),
      answers,
    };
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    setErr("");

    const vErr = validate();
    if (vErr) {
      setErr(vErr);
      return;
    }

    setSaving(true);

    const submission = buildSubmissionPayload();

    try {
      // ✅ 1) Guardar SIEMPRE en outbox offline (para no perder datos)
      // Usa un "type" que tu sync.js ya entienda
      await enqueue({
        type: "form_submission",
        payload: submission,
      });

      showToast("success", "✅ Guardado local (offline listo)");

      // ✅ 2) Si hay internet, intentar sincronizar de inmediato
      if (navigator.onLine) {
        try {
          // Opción A: que tu sync.js haga el POST real
          await syncNow();
          showToast("info", "☁️ Sincronización enviada");
        } catch {
          // si falla, no pasa nada: queda en outbox
          showToast("info", "📦 Quedó en cola para sincronizar");
        }
      } else {
        showToast("info", "📴 Sin internet: quedará en cola");
      }

      // ✅ 3) Volver al listado
      window.location.href = "/formularios";
    } catch (e2) {
      setErr(e2?.message || "Error guardando (offline)");
    } finally {
      setSaving(false);
    }
  };

  // ✅ UI helpers
  const card = {
    border: "1px solid #e5e7eb",
    borderRadius: 14,
    padding: 16,
    background: "#fff",
  };

  const labelStyle = { fontSize: 12, fontWeight: 900, color: "#64748b" };
  const inputStyle = {
    width: "100%",
    padding: "10px 12px",
    borderRadius: 12,
    border: "1px solid #e5e7eb",
    outline: "none",
    background: "#fff",
  };

  const toastStyle = (() => {
    if (!toast) return null;
    const map = {
      success: { bg: "#ecfdf5", border: "#86efac", fg: "#166534" },
      info: { bg: "#eff6ff", border: "#93c5fd", fg: "#1e40af" },
      danger: { bg: "#fef2f2", border: "#fecaca", fg: "#b91c1c" },
    };
    return map[toast.type] || map.info;
  })();

  if (!formDef) {
    return (
      <div style={{ padding: 16 }}>
        <h2 style={{ marginTop: 0 }}>Formulario no encontrado</h2>
        <div style={{ color: "#64748b" }}>
          No existe el formulario con clave: <b>{formKey}</b>
        </div>
        <div style={{ marginTop: 12 }}>
          <a href="/formularios">← Volver</a>
        </div>
      </div>
    );
  }

  return (
    <div style={{ padding: 16 }}>
      <div style={{ display: "flex", justifyContent: "space-between", gap: 12, alignItems: "center" }}>
        <div>
          <h2 style={{ margin: 0 }}>{formDef.title}</h2>
          <div style={{ color: "#64748b", marginTop: 6 }}>{formDef.description || "—"}</div>
          <div style={{ marginTop: 6, fontSize: 12, color: navigator.onLine ? "#166534" : "#b45309", fontWeight: 900 }}>
            {navigator.onLine ? "ONLINE" : "OFFLINE"} — {navigator.onLine ? "se intentará sincronizar" : "se guardará en cola"}
          </div>
        </div>

        <a href="/formularios" style={{ textDecoration: "none", fontWeight: 900 }}>
          ← Volver
        </a>
      </div>

      {toast ? (
        <div
          style={{
            marginTop: 12,
            padding: "10px 12px",
            borderRadius: 12,
            border: `1px solid ${toastStyle.border}`,
            background: toastStyle.bg,
            color: toastStyle.fg,
            fontWeight: 900,
          }}
        >
          {toast.text}
        </div>
      ) : null}

      {err ? (
        <div style={{ marginTop: 12, color: "#b91c1c", fontWeight: 900 }}>
          {err}
        </div>
      ) : null}

      <div style={{ height: 12 }} />

      <form onSubmit={onSubmit} style={card}>
        <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
          {fields.map((f) => {
            const value = answers[f.id];

            if (f.type === "static_text") {
              return (
                <div key={f.id} style={{ padding: 12, border: "1px dashed #e5e7eb", borderRadius: 12 }}>
                  <div style={{ fontSize: 12, fontWeight: 900, marginBottom: 6 }}>{f.label}</div>
                  <div style={{ color: "#334155" }}>{f.text || "—"}</div>
                </div>
              );
            }

            if (f.type === "separator") {
              return (
                <div key={f.id} style={{ padding: "6px 0" }}>
                  <div style={{ borderTop: "2px solid #e5e7eb" }} />
                </div>
              );
            }

            if (f.type === "textarea") {
              return (
                <div key={f.id}>
                  <div style={labelStyle}>
                    {f.label} {f.required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                  </div>
                  <textarea
                    rows={4}
                    value={value || ""}
                    onChange={(e) => setFieldValue(f.id, e.target.value)}
                    style={{ ...inputStyle, fontFamily: "inherit" }}
                    placeholder="Escribe aquí…"
                  />
                </div>
              );
            }

            if (f.type === "select" || f.type === "list") {
              const opts = Array.isArray(f.options) ? f.options : [];
              return (
                <div key={f.id}>
                  <div style={labelStyle}>
                    {f.label} {f.required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                  </div>
                  <select
                    value={value || ""}
                    onChange={(e) => setFieldValue(f.id, e.target.value)}
                    style={inputStyle}
                  >
                    <option value="">Selecciona…</option>
                    {opts.map((o) => (
                      <option key={o} value={o}>
                        {o}
                      </option>
                    ))}
                  </select>
                </div>
              );
            }

            if (f.type === "checkbox") {
              return (
                <label key={f.id} style={{ display: "flex", gap: 10, alignItems: "center", fontWeight: 900 }}>
                  <input
                    type="checkbox"
                    checked={!!value}
                    onChange={(e) => setFieldValue(f.id, e.target.checked)}
                  />
                  <span>
                    {f.label} {f.required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                  </span>
                </label>
              );
            }

            const htmlType =
              f.type === "number" ? "number" :
              f.type === "date" ? "date" :
              f.type === "datetime" ? "datetime-local" :
              "text";

            return (
              <div key={f.id}>
                <div style={labelStyle}>
                  {f.label} {f.required ? <span style={{ color: "#dc2626" }}>*</span> : null}
                </div>
                <input
                  type={htmlType}
                  value={value || ""}
                  onChange={(e) => setFieldValue(f.id, e.target.value)}
                  style={inputStyle}
                  placeholder="—"
                />
              </div>
            );
          })}
        </div>

        <button
          type="submit"
          disabled={saving}
          style={{
            marginTop: 14,
            width: "100%",
            padding: "12px 14px",
            borderRadius: 12,
            border: "1px solid #0f172a",
            background: "#0f172a",
            color: "#fff",
            fontWeight: 900,
            cursor: saving ? "not-allowed" : "pointer",
            opacity: saving ? 0.8 : 1,
          }}
        >
          {saving ? "Guardando..." : "Guardar"}
        </button>

        <div style={{ marginTop: 10, fontSize: 12, color: "#64748b" }}>
          * Se guarda primero en local; si hay internet se sincroniza. Si falla, queda en cola.
        </div>
      </form>
    </div>
  );
}