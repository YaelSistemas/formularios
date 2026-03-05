import React, { useEffect, useMemo, useRef, useState } from "react";
import { NavLink, Outlet, Navigate } from "react-router-dom";
import { apiMe, apiPost } from "../services/api";
import { getAvatarColors, getInitialsFromName } from "../utils/userBadge";

export default function AdminLayout() {
  const token = useMemo(() => localStorage.getItem("token"), []);
  if (!token) return <Navigate to="/login" replace />;

  const [me, setMe] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const userMenuRef = useRef(null);

  const isAdmin =
    !!me?.is_admin ||
    (Array.isArray(me?.roles) && me.roles.includes("Administrador"));

  useEffect(() => {
    (async () => {
      setErr("");
      setLoading(true);

      try {
        const data = await apiMe();
        localStorage.setItem("user", JSON.stringify(data.user));
        setMe(data.user);
      } catch {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        window.location.href = "/login";
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  useEffect(() => {
    function onDocClick(e) {
      if (!userMenuRef.current) return;
      if (userMenuRef.current.contains(e.target)) return;
      setUserMenuOpen(false);
    }
    if (userMenuOpen) document.addEventListener("mousedown", onDocClick);
    return () => document.removeEventListener("mousedown", onDocClick);
  }, [userMenuOpen]);

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

  const initials = getInitialsFromName(me?.name);
  const colors = getAvatarColors(me);
  const sidebarW = sidebarOpen ? 260 : 72;

  const S = {
    page: {
      minHeight: "100vh",
      background: "#f4f4f5",
      fontFamily:
        "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif",
      color: "#0f172a",
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
      flex: "0 0 auto",
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

    // ✅ wrapper para que "Panel Usuario" quede abajo siempre
    navWrap: {
      display: "flex",
      flexDirection: "column",
      flex: "1 1 auto",
      minHeight: 0,
    },
    navTop: {
      padding: 10,
      display: "flex",
      flexDirection: "column",
      gap: 8,
      overflowY: "auto",
      flex: "1 1 auto",
      minHeight: 0,
    },
    navBottom: {
      padding: 10,
      borderTop: "1px solid #e4e4e7",
      flex: "0 0 auto",
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
      fontWeight: 900,
      fontSize: 13,
      justifyContent: sidebarOpen ? "flex-start" : "center",
      cursor: "pointer",
    }),
    navLabel: {
      display: sidebarOpen ? "inline" : "none",
      whiteSpace: "nowrap",
    },
    iconFA: {
      width: 18,
      display: "inline-grid",
      placeItems: "center",
      fontSize: 16,
      lineHeight: 1,
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
      marginLeft: sidebarW,
      transition: "margin-left .18s ease",
    },
    main: {
      marginLeft: sidebarW,
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
      fontWeight: 900,
      textAlign: "left",
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
      textAlign: "left",
    },
    alert: {
      background: "#fff",
      border: "1px solid #fecaca",
      color: "#b91c1c",
      padding: 12,
      borderRadius: 12,
      marginBottom: 12,
      fontWeight: 900,
    },
  };

  if (loading) return <div style={{ padding: 16 }}>Cargando panel admin...</div>;

  if (!isAdmin) {
    return (
      <div style={{ padding: 16 }}>
        <h2>Acceso denegado</h2>
        <p>No tienes permisos de Administrador.</p>
      </div>
    );
  }

  return (
    <div style={S.page}>
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
              <path
                d="M4 7h16M4 12h16M4 17h16"
                stroke="#0f172a"
                strokeWidth="2"
                strokeLinecap="round"
              />
            </svg>
          </button>

          {/* Ajusta el path de tu logo si es diferente */}
          <img src="/images/Logo-vysisa.png" alt="VYSISA" style={S.logo} />
        </div>

        {/* ✅ wrap: top scroll + bottom fijo */}
        <div style={S.navWrap}>
          <nav style={S.navTop}>
            <NavLink
              to="/admin/users"
              style={({ isActive }) => S.navItem(isActive)}
              title="Usuarios"
            >
              <span style={S.iconFA}>
                <i class="fa-solid fa-user"></i>
              </span>
              <span style={S.navLabel}>Usuarios</span>
            </NavLink>

            <NavLink
              to="/admin/roles"
              style={({ isActive }) => S.navItem(isActive)}
              title="Roles"
            >
              <span style={S.iconFA}>
                <i class="fa-solid fa-shield"></i>
              </span>
              <span style={S.navLabel}>Roles</span>
            </NavLink>

            <NavLink
              to="/admin/permissions"
              style={({ isActive }) => S.navItem(isActive)}
              title="Permisos"
            >
              <span style={S.iconFA}>
                <i className="fa-solid fa-lock"></i>
              </span>
              <span style={S.navLabel}>Permisos</span>
            </NavLink>

            <NavLink
              to="/admin/empresas"
              style={({ isActive }) => S.navItem(isActive)}
              title="Empresas"
            >
              <span style={S.iconFA}>
                <i className="fa-solid fa-building" />
              </span>
              <span style={S.navLabel}>Empresas</span>
            </NavLink>
            
            <NavLink
              to="/admin/grupos"
              style={({ isActive }) => S.navItem(isActive)}
              title="Grupos"
            >
              <span style={S.iconFA}>
                <i className="fa-solid fa-people-group" />
              </span>
              <span style={S.navLabel}>Grupos</span>
            </NavLink>

            <NavLink
              to="/admin/forms"
              style={({ isActive }) => S.navItem(isActive)}
              title="Formularios (Admin)"
            >
              <span style={S.iconFA}>
                <i className="fa-brands fa-wpforms" />
              </span>
              <span style={S.navLabel}>Formularios</span>
            </NavLink>
          </nav>

          {/* ✅ bottom fijo */}
          <div style={S.navBottom}>
            <button
              type="button"
              onClick={() => (window.location.href = "/")}
              style={S.navItem(false)}
              title="Regresar al panel de usuario"
            >
              <span style={S.iconFA}>
                <i className="fa-solid fa-arrow-left" />
              </span>
              <span style={S.navLabel}>Panel Usuario</span>
            </button>
          </div>
        </div>
      </aside>

      <header style={S.topbar}>
        <div style={{ fontWeight: 900 }}>Panel Admin</div>

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
              <div style={{ fontSize: 12, color: "#64748b" }}>
                Conectado como
              </div>
              <div style={{ fontWeight: 900 }}>{me?.name || "—"}</div>
              <div
                style={{ marginTop: 2, fontSize: 12, color: "#334155" }}
              >
                <b>Rol:</b>{" "}
                {(me?.roles || []).join(", ") ||
                  (me?.is_admin ? "Administrador" : "Usuario")}
              </div>

              <button
                type="button"
                style={S.menuBtn}
                onClick={() => (window.location.href = "/")}
              >
                Ir a Panel Usuario
              </button>

              <button type="button" style={S.dangerBtn} onClick={onLogout}>
                Cerrar sesión
              </button>
            </div>
          ) : null}
        </div>
      </header>

      <main style={S.main}>
        {err ? <div style={S.alert}>{err}</div> : null}
        <Outlet />
      </main>
    </div>
  );
}