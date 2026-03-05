import React from "react";

export default function Unauthorized() {
  return (
    <div style={{ padding: 16 }}>
      <h2>Sin permisos</h2>
      <p>No tienes permisos para ver esta sección.</p>
      <a href="/">Volver</a>
    </div>
  );
}