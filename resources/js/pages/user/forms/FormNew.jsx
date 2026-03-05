import React, { useState } from "react";
import { apiPost } from "../../../services/api";

export default function FormNew() {
  const [title, setTitle] = useState("");
  const [payload, setPayload] = useState("{}");
  const [saving, setSaving] = useState(false);
  const [err, setErr] = useState("");

  const onSubmit = async (e) => {
    e.preventDefault();
    setErr("");
    setSaving(true);

    try {
      const json = JSON.parse(payload || "{}");
      const res = await apiPost("/forms", { title: title.trim(), payload: json });
      window.location.href = `/formularios/${res.form.id}`;
    } catch (e2) {
      setErr(e2?.message || "Error guardando");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div style={{ padding: 16 }}>
      <h2>Nuevo formulario</h2>
      {err ? <p style={{ color: "crimson" }}>{err}</p> : null}

      <form onSubmit={onSubmit}>
        <div>
          <label>Título</label><br />
          <input value={title} onChange={(e) => setTitle(e.target.value)} required style={{ width: "100%" }} />
        </div>

        <div style={{ marginTop: 10 }}>
          <label>Payload (JSON)</label><br />
          <textarea
            value={payload}
            onChange={(e) => setPayload(e.target.value)}
            rows={10}
            style={{ width: "100%", fontFamily: "monospace" }}
          />
        </div>

        <button style={{ marginTop: 12 }} type="submit" disabled={saving}>
          {saving ? "Guardando..." : "Guardar"}
        </button>
      </form>

      <div style={{ marginTop: 12 }}>
        <a href="/formularios">Volver</a>
      </div>
    </div>
  );
}