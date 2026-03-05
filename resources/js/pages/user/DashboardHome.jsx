import React, { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { getAvatarColors, getInitialsFromName } from "../../utils/userBadge";

export default function DashboardHome() {
  const navigate = useNavigate();

  const me = useMemo(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  }, []);

  const initials = getInitialsFromName(me?.name);
  const colors = getAvatarColors(me);

  return (
    <div>
      <div style={{ background: "#fff", border: "1px solid #e4e4e7", borderRadius: 12, padding: 14 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
          <div
            style={{
              width: 44,
              height: 44,
              borderRadius: 999,
              border: `1px solid ${colors.ring}`,
              background: colors.bg,
              color: colors.fg,
              fontWeight: 900,
              display: "grid",
              placeItems: "center",
            }}
          >
            {initials}
          </div>

          <div>
            <div style={{ fontWeight: 900, fontSize: 16 }}>Bienvenido{me?.name ? `, ${me.name}` : ""}</div>
            <div style={{ fontSize: 12, color: "#64748b" }}>
              Aquí va tu resumen (pendientes, formularios recientes, etc.)
            </div>
          </div>
        </div>

        <div style={{ marginTop: 12, display: "flex", gap: 10, flexWrap: "wrap" }}>
          <button
            type="button"
            onClick={() => navigate("/app/forms")}
            style={{
              borderRadius: 10,
              border: "1px solid #e4e4e7",
              background: "#fff",
              padding: "10px 12px",
              cursor: "pointer",
              fontWeight: 900,
            }}
          >
            Ir a Formularios
          </button>
        </div>
      </div>
    </div>
  );
}