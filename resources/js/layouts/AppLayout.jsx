import React, { useEffect, useRef, useState } from "react";
import { NavLink, Outlet, useNavigate } from "react-router-dom";
import { apiPost } from "../services/api";
import { getAvatarColors, getInitialsFromName } from "../utils/userBadge";

export default function AppLayout() {
  const navigate = useNavigate();

  const [me, setMe] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const isAdmin = !!me?.is_admin || (Array.isArray(me?.roles) && me.roles.includes("Administrador"));

  // ✅ sidebar always visible: open = full, closed = rail
  const [sidebarOpen, setSidebarOpen] = useState(true);

  // user dropdown
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const userMenuRef = useRef(null);

  useEffect(() => {
    function onDocClick(e) {
      if (!userMenuRef.current) return;
      if (userMenuRef.current.contains(e.target)) return;
      setUserMenuOpen(false);
    }
    if (userMenuOpen) document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, [userMenuOpen]);

  const initials = getInitialsFromName(me?.name);
  const colors = getAvatarColors(me);

  const roleLabel =
    me?.role ||
    (me?.is_admin ? "Administrador" : "") ||
    (Array.isArray(me?.roles) && me.roles.join(", ")) ||
    "Usuario";

  const kickToLogin = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
  };

  const onLogout = async () => {
    try {
      await apiPost("/logout", {});
    } catch {
      // ignore
    } finally {
      kickToLogin();
    }
  };

  // CSS variable para que el contenido se ajuste solo
  const sidebarW = sidebarOpen ? 260 : 72;

  const S = {
    page: {
      minHeight: "100vh",
      background: "#f4f4f5",
      fontFamily: "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif",
      color: "#0f172a",
    },
    topbar: {
      height: 56,
      background: "#fff",
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      padding: "0 14px",
      position: "sticky",
      top: 0,
      zIndex: 20,
      marginLeft: sidebarW, // ✅ se alinea con el sidebar
      transition: "margin-left .18s ease",
    },
    contentWrap: {
      display: "flex",
      minHeight: "calc(100vh - 56px)",
    },
    sidebar: {
      width: sidebarW,
      transition: "width .18s ease",
      background: "#fff",
      borderRight: "1px solid #e4e4e7",
      position: "fixed",
      left: 0,
      top: 0,
      bottom: 0,
      zIndex: 30,
      display: "flex",
      flexDirection: "column",
    },
    sidebarHeader: {
      height: 56,
      borderBottom: "1px solid #e4e4e7",
      display: "flex",
      alignItems: "center",
      gap: 10,
      padding: "0 12px",
    },
    burger: {
      width: 36,
      height: 36,
      borderRadius: 10,
      border: "1px solid #e4e4e7",
      background: "#fff",
      cursor: "pointer",
      display: "grid",
      placeItems: "center",
      flex: "0 0 auto",
    },
    logo: {
      height: 26,
      width: "auto",
      objectFit: "contain",
      display: sidebarOpen ? "block" : "none",
    },
    nav: {
      padding: 10,
      display: "flex",
      flexDirection: "column",
      gap: 8,
      overflowY: "auto",
    },
    navItem: (active) => ({
      display: "flex",
      alignItems: "center",
      gap: 10,
      textDecoration: "none",
      padding: "10px 10px",
      borderRadius: 12,
      border: "1px solid " + (active ? "#c7d2fe" : "#e4e4e7"),
      background: active ? "#eef2ff" : "#fff",
      color: "#0f172a",
      fontWeight: 800,
      fontSize: 13,
      justifyContent: sidebarOpen ? "flex-start" : "center",
    }),
    navLabel: {
      display: sidebarOpen ? "inline" : "none",
      whiteSpace: "nowrap",
    },
    main: {
      marginLeft: sidebarW, // ✅ contenido se ajusta
      transition: "margin-left .18s ease",
      width: "calc(100% - " + sidebarW + "px)", // ✅ ocupa el espacio real restante
      padding: 16,
      maxWidth: "none", // ✅ quita el límite
    },
    avatarBtn: {
      width: 38,
      height: 38,
      borderRadius: 999,
      border: `1px solid ${colors.ring}`,
      background: colors.bg,
      color: colors.fg,
      fontWeight: 900,
      display: "grid",
      placeItems: "center",
      cursor: "pointer",
      userSelect: "none",
    },
    userMenu: {
      position: "absolute",
      right: 0,
      top: 46,
      width: 280,
      background: "#fff",
      border: "1px solid #e4e4e7",
      borderRadius: 12,
      boxShadow: "0 10px 25px rgba(0,0,0,.10)",
      padding: 10,
      zIndex: 50,
    },
    menuBtn: {
      width: "100%",
      marginTop: 10,
      borderRadius: 10,
      border: "1px solid #e4e4e7",
      background: "#fff",
      padding: "10px 12px",
      cursor: "pointer",
      fontWeight: 800,
    },
    dangerBtn: {
      width: "100%",
      marginTop: 8,
      borderRadius: 10,
      border: "1px solid #fecaca",
      background: "#fef2f2",
      padding: "10px 12px",
      cursor: "pointer",
      fontWeight: 900,
      color: "#b91c1c",
    },
  };

  return (
    <div style={S.page}>
      {/* SIDEBAR (siempre visible) */}
      <aside style={S.sidebar}>
        <div style={S.sidebarHeader}>
          <button
            type="button"
            style={S.burger}
            onClick={() => setSidebarOpen((v) => !v)}
            aria-label="Abrir/cerrar menú"
            title="Menú"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M4 7h16M4 12h16M4 17h16" stroke="#0f172a" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </button>

          {/* Logo visible solo cuando está abierto */}
          <img src="/images/Logo-vysisa.png" alt="VYSISA" style={S.logo} />
        </div>

        <nav style={S.nav}>
          <NavLink
            to="/"
            end
            style={({ isActive }) => S.navItem(isActive)}
            title="Dashboard"
          >
            {/* icon */}
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M4 13h7V4H4v9Zm9 7h7V11h-7v9ZM4 20h7v-5H4v5Zm9-11h7V4h-7v5Z" stroke="#0f172a" strokeWidth="1.8" />
            </svg>
            <span style={S.navLabel}>Dashboard</span>
          </NavLink>

          <NavLink
            to="/forms"
            style={({ isActive }) => S.navItem(isActive)}
            title="Formularios"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M7 3h10v18H7V3Zm2 4h6M9 11h6M9 15h6" stroke="#0f172a" strokeWidth="1.8" strokeLinecap="round" />
            </svg>
            <span style={S.navLabel}>Formularios</span>
          </NavLink>

          {isAdmin ? (
            <button
              type="button"
              onClick={() => (window.location.href = "/admin")}
              style={S.navItem(false)}
              title="Panel Admin"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path
                  d="M12 2 4 5v6c0 5 3.5 9.7 8 11 4.5-1.3 8-6 8-11V5l-8-3Z"
                  stroke="#0f172a"
                  strokeWidth="1.8"
                />
              </svg>
              <span style={S.navLabel}>Panel Admin</span>
            </button>
          ) : null}
        </nav>
      </aside>

      {/* TOPBAR */}
      <header style={S.topbar}>
        <div style={{ fontWeight: 900 }}>Formularios PWA</div>

        {/* Avatar + dropdown */}
        <div style={{ position: "relative" }} ref={userMenuRef}>
          <div
            style={S.avatarBtn}
            onClick={() => setUserMenuOpen((v) => !v)}
            title={me?.name || "Usuario"}
          >
            {initials}
          </div>

          {userMenuOpen ? (
            <div style={S.userMenu}>
              <div style={{ fontSize: 12, color: "#64748b" }}>Conectado como</div>
              <div style={{ fontWeight: 900 }}>{me?.name || "—"}</div>
              <div style={{ marginTop: 2, fontSize: 12, color: "#334155" }}>
                <b>Rol:</b> {roleLabel}
              </div>
              {me?.email ? (
                <div style={{ marginTop: 4, fontSize: 12, color: "#64748b" }}>
                  {me.email}
                </div>
              ) : null}

              <button
                type="button"
                style={S.menuBtn}
                onClick={() => {
                  setUserMenuOpen(false);
                  navigate("/app/profile"); // opcional (si luego creas esta ruta)
                }}
              >
                Ver perfil (opcional)
              </button>

              <button type="button" style={S.dangerBtn} onClick={onLogout}>
                Cerrar sesión
              </button>
            </div>
          ) : null}
        </div>
      </header>

      {/* MAIN */}
      <div style={S.contentWrap}>
        <main style={S.main}>
          <Outlet />
        </main>
      </div>
    </div>
  );
}