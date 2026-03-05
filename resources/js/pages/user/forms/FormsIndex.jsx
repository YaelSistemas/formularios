import React, { useEffect, useState } from "react";
import { apiGet } from "../../../services/api";

export default function FormsIndex() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  useEffect(() => {
    (async () => {
      setErr("");
      setLoading(true);
      try {
        const data = await apiGet("/forms");
        setItems(data.forms || []);
      } catch (e) {
        setErr(e?.message || "Error cargando formularios");
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  return (
    <div style={{ padding: 16 }}>
      <div style={{ display: "flex", justifyContent: "space-between", gap: 10, alignItems: "center" }}>
        <h2 style={{ margin: 0 }}>Formularios</h2>
        <a href="/formularios/nuevo">+ Nuevo</a>
      </div>

      {err ? <p style={{ color: "crimson" }}>{err}</p> : null}

      {loading ? (
        <div>Cargando...</div>
      ) : (
        <div style={{ marginTop: 12 }}>
          {items.length ? (
            <ul>
              {items.map((f) => (
                <li key={f.id}>
                  <a href={`/formularios/${f.id}`}>{f.title}</a>{" "}
                  <span style={{ opacity: 0.7, fontSize: 12 }}>({f.status})</span>
                </li>
              ))}
            </ul>
          ) : (
            <div>No hay formularios.</div>
          )}
        </div>
      )}
    </div>
  );
}