import React from "react";

function Row({ label, done, total }) {
  return (
    <div style={{ display: "grid", gap: 6 }}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          gap: 12,
          fontSize: 14,
          fontWeight: 800,
          color: "#0f172a",
        }}
      >
        <span>{label}</span>
        <span>
          {done}/{total}
        </span>
      </div>

      <div
        style={{
          width: "100%",
          height: 10,
          borderRadius: 999,
          background: "#e2e8f0",
          overflow: "hidden",
        }}
      >
        <div
          style={{
            height: "100%",
            width: `${total > 0 ? Math.min((done / total) * 100, 100) : 0}%`,
            background: "#2563eb",
            transition: "width .2s ease",
          }}
        />
      </div>
    </div>
  );
}

export default function OfflineBootstrapScreen({
  progress,
}) {
  return (
    <div
      style={{
        minHeight: "100vh",
        display: "grid",
        placeItems: "center",
        background: "#f8fafc",
        padding: 16,
      }}
    >
      <div
        style={{
          width: "100%",
          maxWidth: 520,
          background: "#fff",
          border: "1px solid #e5e7eb",
          borderRadius: 18,
          padding: 22,
          boxShadow: "0 20px 40px rgba(15,23,42,0.08)",
          display: "grid",
          gap: 18,
        }}
      >
        <div style={{ display: "grid", gap: 6 }}>
          <div
            style={{
              fontSize: 22,
              fontWeight: 900,
              color: "#0f172a",
            }}
          >
            Preparando datos
          </div>
          <div style={{ fontSize: 14, color: "#475569" }}>
            {progress?.message || "Sincronizando información para uso offline..."}
          </div>
        </div>

        <Row
          label="Formularios"
          done={Number(progress?.formsDone || 0)}
          total={Number(progress?.formsTotal || 0)}
        />

        <Row
          label="Registros"
          done={Number(progress?.recordsDone || 0)}
          total={Number(progress?.recordsTotal || 0)}
        />

        <Row
          label="PDFs"
          done={Number(progress?.pdfsDone || 0)}
          total={Number(progress?.pdfsTotal || 0)}
        />
      </div>
    </div>
  );
}