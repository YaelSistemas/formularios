import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { apiGet } from "../../../services/api";

export default function FormShow() {
  const { id } = useParams();
  const [item, setItem] = useState(null);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  useEffect(() => {
    (async () => {
      setErr("");
      setLoading(true);
      try {
        const data = await apiGet(`/forms/${id}`);
        setItem(data.form || null);
      } catch (e) {
        setErr(e?.message || "Error cargando formulario");
      } finally {
        setLoading(false);
      }
    })();
  }, [id]);

  return (
    <div style={{ padding: 16 }}>
      <h2>Detalle formulario</h2>
      {err ? <p style={{ color: "crimson" }}>{err}</p> : null}

      {loading ? (
        <div>Cargando...</div>
      ) : item ? (
        <div>
          <div><b>ID:</b> {item.id}</div>
          <div><b>Título:</b> {item.title}</div>
          <div><b>Status:</b> {item.status}</div>

          <h4 style={{ marginTop: 12 }}>Payload</h4>
          <pre style={{ background: "#f5f5f5", padding: 10, overflow: "auto" }}>
            {JSON.stringify(item.payload, null, 2)}
          </pre>
        </div>
      ) : (
        <div>No encontrado.</div>
      )}

      <div style={{ marginTop: 12 }}>
        <a href="/formularios">Volver</a>
      </div>
    </div>
  );
}