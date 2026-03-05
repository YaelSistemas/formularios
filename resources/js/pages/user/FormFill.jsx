// resources/js/pages/user/FormFill.jsx
import React, { useEffect, useMemo, useState } from "react";
import { apiPost } from "../../services/api";
import { enqueue, syncNow } from "../../offline/sync"; // ✅ ajusta la ruta si tu carpeta es distinta

// Tipos "no input" (no requieren answer)
const NON_INPUT_TYPES = new Set(["static_text", "separator", "fixed_image", "fixed_file"]);

export default function FormFill({ form, onBack }) {
  const token = useMemo(() => localStorage.getItem("token"), []);
  const fields = form?.payload?.fields || [];

  // ✅ estado reactivo de conectividad (NO uses navigator.onLine directo en render)
  const [isOnline, setIsOnline] = useState(() => navigator.onLine);

  const buildInitAnswers = () => {
    const init = {};
    for (const f of fields) {
      if (!f?.id) continue;

      // No-input: no guardamos nada (pero igual podemos dejar string vacío por compat)
      if (NON_INPUT_TYPES.has(f.type)) {
        init[f.id] = "";
        continue;
      }

      if (f.type === "checkbox") init[f.id] = false;
      else init[f.id] = "";
    }
    return init;
  };

  const [answers, setAnswers] = useState(() => buildInitAnswers());

  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState("");

  // ✅ actualiza answers si cambian fields (por si cambias de form)
  useEffect(() => {
    setAnswers(buildInitAnswers());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form?.id]);

  // ✅ listeners online/offline + sync cuando vuelve internet
  useEffect(() => {
    const onOnline = () => {
      setIsOnline(true);
      syncNow().catch(() => null);
    };
    const onOffline = () => setIsOnline(false);

    window.addEventListener("online", onOnline);
    window.addEventListener("offline", onOffline);

    // al entrar, si hay internet, intenta sync por si quedó cola
    if (navigator.onLine) syncNow().catch(() => null);

    return () => {
      window.removeEventListener("online", onOnline);
      window.removeEventListener("offline", onOffline);
    };
  }, []);

  const setVal = (id, value) => {
    setAnswers((prev) => ({ ...prev, [id]: value }));
  };

  const validate = () => {
    for (const f of fields) {
      if (!f?.id) continue;

      // no-input types: no se validan
      if (NON_INPUT_TYPES.has(f.type)) continue;

      // required
      if (f.required) {
        const v = answers[f.id];

        // checkbox required => debe ser true
        if (f.type === "checkbox") {
          if (!v) return `Falta responder: ${f.label}`;
          continue;
        }

        if (v === null || v === undefined || String(v).trim() === "") {
          return `Falta responder: ${f.label}`;
        }
      }

      // select/list/radio required => opción válida
      if (f.type === "select" || f.type === "list" || f.type === "radio") {
        const opts = Array.isArray(f.options) ? f.options : [];
        if (f.required && opts.length >= 2 && !opts.includes(answers[f.id])) {
          return `Selecciona una opción válida para: ${f.label}`;
        }
      }
    }
    return null;
  };

  const toFriendlyMessage = (e2) => {
    const m = e2?.response?.data?.message || e2?.message || "Error guardando respuestas.";
    return String(m);
  };

  const shouldQueueOffline = (e2) => {
    // offline real
    if (!navigator.onLine) return true;

    const msg = String(e2?.message || "").toLowerCase();
    if (msg.includes("failed to fetch")) return true;
    if (msg.includes("network error")) return true;
    if (msg.includes("timeout")) return true;

    // axios/fetch a veces traen code:
    const code = String(e2?.code || "").toUpperCase();
    if (code === "ERR_NETWORK" || code === "ECONNABORTED") return true;

    // 5xx: reintentar (opcional)
    const status = e2?.status || e2?.response?.status;
    if (status && Number(status) >= 500) return true;

    return false;
  };

  const resetForm = () => {
    setAnswers(buildInitAnswers());
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    setMsg("");

    const err = validate();
    if (err) {
      setMsg(err);
      return;
    }

    setSaving(true);

    // ✅ payload para offline (NO lo mandes al API, solo para Dexie)
    const offlinePayload = {
      form_id: form?.id,
      answers: { ...answers },
      meta: {
        offline_capable: true,
        user_agent: navigator.userAgent,
      },
    };

    try {
      // ✅ intento online normal (tu API real)
      await apiPost(`/forms/${form.id}/submit`, { answers }, token);

      setMsg("✅ Respuestas guardadas.");
      resetForm();

      // opcional: si había cola, intentamos sync
      if (navigator.onLine) syncNow().catch(() => null);
    } catch (e2) {
      // ✅ si es error de red / offline -> encolar
      if (shouldQueueOffline(e2)) {
        try {
          await enqueue("form_submission", offlinePayload);

          setMsg(
            "📴 Sin conexión. Guardado OFFLINE ✅\n" +
              "En cuanto vuelva el internet, se subirá automáticamente."
          );
          resetForm();

          // si justo volvió online mientras tanto
          if (navigator.onLine) syncNow().catch(() => null);
        } catch (qe) {
          setMsg("Error guardando offline: " + String(qe?.message || qe));
        }
      } else {
        // error real del backend (422/403/etc)
        setMsg(toFriendlyMessage(e2));
      }
    } finally {
      setSaving(false);
    }
  };

  const renderField = (f) => {
    // common para inputs/textarea/select
    const common = {
      value: answers[f.id] ?? "",
      onChange: (e) => setVal(f.id, e.target.value),
      style: {
        width: "100%",
        padding: 10,
        borderRadius: 8,
        border: "1px solid #ccc",
      },
    };

    // ---- NO INPUT TYPES ----
    if (f.type === "static_text") {
      return (
        <div
          style={{
            padding: 10,
            borderRadius: 8,
            border: "1px dashed rgba(0,0,0,0.25)",
            background: "rgba(0,0,0,0.03)",
            lineHeight: 1.35,
          }}
        >
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
        <img
          src={url}
          alt={f.label || "Imagen"}
          style={{ width: "100%", borderRadius: 10, border: "1px solid rgba(0,0,0,0.12)" }}
        />
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

    // ---- INPUT TYPES ----
    if (f.type === "text") return <input type="text" {...common} />;

    if (f.type === "textarea") {
      return (
        <textarea
          value={answers[f.id] ?? ""}
          onChange={(e) => setVal(f.id, e.target.value)}
          rows={4}
          style={{ ...common.style, resize: "vertical" }}
        />
      );
    }

    if (f.type === "number") return <input type="number" {...common} />;

    if (f.type === "date") return <input type="date" {...common} />;

    if (f.type === "datetime") return <input type="datetime-local" {...common} />;

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

    // select + list (mismo render)
    if (f.type === "select" || f.type === "list") {
      return (
        <select {...common}>
          <option value="">-- Selecciona --</option>
          {(f.options || []).map((opt) => (
            <option key={opt} value={opt}>
              {opt}
            </option>
          ))}
        </select>
      );
    }

    // radio
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

    // fallback
    return <input type="text" {...common} />;
  };

  const shouldShowLabel = (f) => {
    // Para separador no ponemos label arriba
    if (f.type === "separator") return false;
    // fixed_image puede mostrarse sin label, pero lo dejamos por consistencia
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
                  <b>{f.label}</b> {f.required && !NON_INPUT_TYPES.has(f.type) ? <span style={{ color: "crimson" }}>*</span> : null}
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