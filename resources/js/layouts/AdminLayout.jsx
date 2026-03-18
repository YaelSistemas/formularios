import React, { useEffect, useMemo, useRef, useState } from "react";
import {
  NavLink,
  Outlet,
  Navigate,
  useLocation,
} from "react-router-dom";
import { apiMe, apiPost } from "../services/api";
import { getAvatarColors, getInitialsFromName } from "../utils/userBadge";

export default function AdminLayout() {
  const location = useLocation();

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

  const [sidebarExpanded, setSidebarExpanded] = useState(true);
  const [isMobile, setIsMobile] = useState(() => {
    if (typeof window === "undefined") return false;
    return window.innerWidth < 1024;
  });

  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const userMenuRef = useRef(null);

  const isAdmin =
    !!me?.is_admin ||
    (Array.isArray(me?.roles) && me.roles.includes("Administrador"));

  useEffect(() => {
    function onResize() {
      setIsMobile(window.innerWidth < 1024);
    }

    window.addEventListener("resize", onResize);
    return () => window.removeEventListener("resize", onResize);
  }, []);

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

    if (userMenuOpen) {
      document.addEventListener("mousedown", onDocClick);
    }

    return () => document.removeEventListener("mousedown", onDocClick);
  }, [userMenuOpen]);

  useEffect(() => {
    function onKeyDown(e) {
      if (e.key === "Escape") {
        setUserMenuOpen(false);
      }
    }

    document.addEventListener("keydown", onKeyDown);
    return () => document.removeEventListener("keydown", onKeyDown);
  }, []);

  const kickToLogin = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
  };

  const goUserPanel = () => {
    window.location.href = "/forms";
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

  const roleLabel =
    (me?.roles || []).join(", ") ||
    (me?.is_admin ? "Administrador" : "Usuario");

  const desktopSidebarWidth = sidebarExpanded ? 240 : 72;
  const effectiveSidebarWidth = isMobile ? 0 : desktopSidebarWidth;
  const showDesktopText = sidebarExpanded;

  const toggleDesktopMenu = () => {
    if (!isMobile) {
      setSidebarExpanded((v) => !v);
    }
  };

  const navItemStyle = (active) => ({
    display: "flex",
    alignItems: "center",
    gap: 12,
    textDecoration: "none",
    minHeight: 44,
    padding: sidebarExpanded ? "10px 12px" : "10px 0",
    borderRadius: 12,
    background: active ? "#f1f5f9" : "transparent",
    color: active ? "#0f172a" : "#111827",
    fontWeight: active ? 800 : 700,
    fontSize: 14,
    justifyContent: !sidebarExpanded ? "center" : "flex-start",
    transition: "background .18s ease, color .18s ease",
    border: "none",
    width: "100%",
    cursor: "pointer",
  });

  const navIconWrapStyle = {
    width: 22,
    minWidth: 22,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    fontSize: 18,
  };

  const isUsersActive = location.pathname.startsWith("/admin/users");
  const isRolesActive = location.pathname.startsWith("/admin/roles");
  const isPermissionsActive = location.pathname.startsWith("/admin/permissions");
  const isUnidadesServicioActive = location.pathname.startsWith("/admin/unidades-servicio");
  const isEmpresasActive = location.pathname.startsWith("/admin/empresas");
  const isGruposActive = location.pathname.startsWith("/admin/grupos");
  const isFormsActive = location.pathname.startsWith("/admin/forms");

  const bottomNavScrollItemStyle = (active) => ({
    minWidth: 86,
    width: 86,
    display: "flex",
    alignItems: "stretch",
    justifyContent: "center",
    textDecoration: "none",
    color: active ? "#0f172a" : "#64748b",
    background: active ? "rgba(15, 23, 42, 0.05)" : "transparent",
    border: "none",
    cursor: "pointer",
    position: "relative",
    padding: 0,
    height: "100%",
    flex: "0 0 auto",
    WebkitTapHighlightColor: "transparent",
  });

  const bottomNavInnerStyle = (active) => ({
    width: "100%",
    height: "100%",
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    justifyContent: "center",
    paddingTop: 6,
    paddingBottom: 6,
    color: active ? "#0f172a" : "#64748b",
  });

  const bottomIconWrapStyle = (active) => ({
    width: "100%",
    height: 24,
    minHeight: 24,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    fontSize: 19,
    marginBottom: 4,
    color: active ? "#0f172a" : "#64748b",
  });

  const bottomLabelStyle = (active) => ({
    fontSize: 11,
    lineHeight: "12px",
    fontWeight: active ? 800 : 700,
    color: active ? "#0f172a" : "#64748b",
    textAlign: "center",
    padding: "0 4px",
    whiteSpace: "nowrap",
  });

  const bottomTopIndicatorStyle = (active) => ({
    position: "absolute",
    top: 0,
    left: "16%",
    right: "16%",
    height: 3,
    borderRadius: "0 0 999px 999px",
    background: active ? "#2563eb" : "transparent",
    boxShadow: active ? "0 1px 8px rgba(37, 99, 235, 0.35)" : "none",
  });

  const S = {
    page: {
      minHeight: "100vh",
      background: "#ffffff",
      fontFamily:
        "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif",
      color: "#0f172a",
      overflowX: "hidden",
    },

    sidebar: {
      width: desktopSidebarWidth,
      background: "#fff",
      borderRight: "1px solid #e5e7eb",
      position: "fixed",
      left: 0,
      top: 0,
      bottom: 0,
      zIndex: 40,
      display: isMobile ? "none" : "flex",
      flexDirection: "column",
      transition: "width .18s ease",
    },

    sidebarHeader: {
      height: 56,
      minHeight: 56,
      borderBottom: "1px solid #e5e7eb",
      display: "flex",
      alignItems: "center",
      gap: 12,
      padding: "0 12px",
    },

    topbar: {
      height: 56,
      background: "#fff",
      borderBottom: "1px solid #e5e7eb",
      display: "flex",
      alignItems: "center",
      justifyContent: "space-between",
      padding: isMobile ? "0 12px" : "0 16px",
      position: "sticky",
      top: 0,
      zIndex: 20,
      marginLeft: effectiveSidebarWidth,
      transition: "margin-left .18s ease",
    },

    topbarLeft: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      minWidth: 0,
    },

    topbarRight: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      minWidth: 0,
    },

    topbarTitle: {
      fontWeight: 900,
      fontSize: isMobile ? 16 : 18,
      whiteSpace: "nowrap",
      overflow: "hidden",
      textOverflow: "ellipsis",
    },

    burger: {
      width: 36,
      height: 36,
      borderRadius: 999,
      border: "none",
      background: "transparent",
      cursor: "pointer",
      display: "grid",
      placeItems: "center",
      flex: "0 0 auto",
    },

    logoRow: {
      display: "flex",
      alignItems: "center",
      gap: 10,
      minWidth: 0,
    },

    logo: {
      height: 26,
      width: "auto",
      objectFit: "contain",
      display: showDesktopText ? "block" : "none",
    },

    navWrap: {
      display: "flex",
      flexDirection: "column",
      flex: "1 1 auto",
      minHeight: 0,
    },

    navTop: {
      padding: "10px 8px",
      display: "flex",
      flexDirection: "column",
      gap: 4,
      overflowY: "auto",
      flex: 1,
      minHeight: 0,
    },

    navBottom: {
      padding: "10px 8px",
      borderTop: "1px solid #e5e7eb",
      flex: "0 0 auto",
    },

    navLabel: {
      display: showDesktopText ? "inline" : "none",
      whiteSpace: "nowrap",
      overflow: "hidden",
      textOverflow: "ellipsis",
    },

    mainWrap: {
      marginLeft: effectiveSidebarWidth,
      transition: "margin-left .18s ease",
      minHeight: "calc(100vh - 56px)",
    },

    main: {
      marginLeft: effectiveSidebarWidth,
      transition: "margin-left .18s ease",
      width: "calc(100% - " + effectiveSidebarWidth + "px)",
      padding: isMobile ? "12px 12px 96px 12px" : 16,
      maxWidth: "100%",
      boxSizing: "border-box",
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
      flex: "0 0 auto",
    },

    userMenu: {
      position: "absolute",
      left: isMobile ? 0 : "auto",
      right: isMobile ? "auto" : 0,
      top: 46,
      width: isMobile ? 240 : 280,
      maxWidth: "calc(100vw - 24px)",
      background: "#fff",
      border: "1px solid #e5e7eb",
      borderRadius: 12,
      boxShadow: "0 10px 25px rgba(0,0,0,.10)",
      padding: 10,
      zIndex: 50,
    },

    menuBtn: {
      width: "100%",
      marginTop: 10,
      borderRadius: 10,
      border: "1px solid #e5e7eb",
      background: "#fff",
      padding: "10px 12px",
      cursor: "pointer",
      fontWeight: 800,
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

    mobileBottomBarWrap: {
      display: isMobile ? "block" : "none",
      position: "fixed",
      left: 0,
      right: 0,
      bottom: 0,
      zIndex: 45,
      paddingBottom: "env(safe-area-inset-bottom, 0px)",
      pointerEvents: "none",
    },

    mobileBottomBar: {
      width: "100%",
      height: 72,
      background: "#ffffff",
      borderTop: "1px solid #e5e7eb",
      boxShadow: "0 -6px 18px rgba(15, 23, 42, 0.06)",
      overflowX: "auto",
      overflowY: "hidden",
      display: "flex",
      WebkitOverflowScrolling: "touch",
      scrollbarWidth: "none",
      msOverflowStyle: "none",
      pointerEvents: "auto",
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
      {!isMobile ? (
        <aside style={S.sidebar}>
          <div style={S.sidebarHeader}>
            <button
              type="button"
              style={S.burger}
              onClick={toggleDesktopMenu}
              aria-label="Abrir/cerrar menú"
              title="Menú"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path
                  d="M4 7h16M4 12h16M4 17h16"
                  stroke="#0f172a"
                  strokeWidth="2"
                  strokeLinecap="round"
                />
              </svg>
            </button>

            <div style={S.logoRow}>
              <img src="/images/Logo-vysisa.png" alt="VYSISA" style={S.logo} />
            </div>
          </div>

          <div style={S.navWrap}>
            <nav style={S.navTop}>
              <NavLink
                to="/admin/users"
                title="Usuarios"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-user" />
                </span>
                <span style={S.navLabel}>Usuarios</span>
              </NavLink>

              <NavLink
                to="/admin/roles"
                title="Roles"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-shield" />
                </span>
                <span style={S.navLabel}>Roles</span>
              </NavLink>

              <NavLink
                to="/admin/permissions"
                title="Permisos"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-lock" />
                </span>
                <span style={S.navLabel}>Permisos</span>
              </NavLink>

              <NavLink
                to="/admin/unidades-servicio"
                title="Unidades de servicio"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-layer-group" />
                </span>
                <span style={S.navLabel}>Unidades de servicio</span>
              </NavLink>

              <NavLink
                to="/admin/empresas"
                title="Empresas"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-building" />
                </span>
                <span style={S.navLabel}>Empresas</span>
              </NavLink>

              <NavLink
                to="/admin/grupos"
                title="Grupos"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-people-group" />
                </span>
                <span style={S.navLabel}>Grupos</span>
              </NavLink>

              <NavLink
                to="/admin/forms"
                title="Formularios"
                style={({ isActive }) => navItemStyle(isActive)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-brands fa-wpforms" />
                </span>
                <span style={S.navLabel}>Formularios</span>
              </NavLink>
            </nav>

            <div style={S.navBottom}>
              <button
                type="button"
                onClick={goUserPanel}
                title="Panel Usuario"
                style={navItemStyle(false)}
              >
                <span style={navIconWrapStyle}>
                  <i className="fa-solid fa-arrow-left" />
                </span>
                <span style={S.navLabel}>Panel Usuario</span>
              </button>
            </div>
          </div>
        </aside>
      ) : null}

      <header style={S.topbar}>
        {isMobile ? (
          <>
            <div style={S.topbarLeft} ref={userMenuRef}>
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
                  <div style={{ marginTop: 2, fontSize: 12, color: "#334155" }}>
                    <b>Rol:</b> {roleLabel}
                  </div>

                  <button
                    type="button"
                    style={S.menuBtn}
                    onClick={goUserPanel}
                  >
                    Ir a Panel Usuario
                  </button>

                  <button type="button" style={S.dangerBtn} onClick={onLogout}>
                    Cerrar sesión
                  </button>
                </div>
              ) : null}
            </div>

            <div style={S.topbarRight}>
              <div style={S.topbarTitle}>Panel Admin</div>
            </div>
          </>
        ) : (
          <>
            <div style={S.topbarLeft}>
              <div style={S.topbarTitle}>Panel Admin</div>
            </div>

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
                  <div style={{ marginTop: 2, fontSize: 12, color: "#334155" }}>
                    <b>Rol:</b> {roleLabel}
                  </div>

                  <button
                    type="button"
                    style={S.menuBtn}
                    onClick={goUserPanel}
                  >
                    Ir a Panel Usuario
                  </button>

                  <button type="button" style={S.dangerBtn} onClick={onLogout}>
                    Cerrar sesión
                  </button>
                </div>
              ) : null}
            </div>
          </>
        )}
      </header>

      <main style={S.main}>
        {err ? <div style={S.alert}>{err}</div> : null}
        <Outlet />
      </main>

      {isMobile ? (
        <div style={S.mobileBottomBarWrap}>
          <nav style={S.mobileBottomBar}>
            <NavLink
              to="/admin/users"
              style={() => bottomNavScrollItemStyle(isUsersActive)}
            >
              <span style={bottomTopIndicatorStyle(isUsersActive)} />
              <span style={bottomNavInnerStyle(isUsersActive)}>
                <span style={bottomIconWrapStyle(isUsersActive)}>
                  <i className="fa-solid fa-user" />
                </span>
                <span style={bottomLabelStyle(isUsersActive)}>Usuarios</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/roles"
              style={() => bottomNavScrollItemStyle(isRolesActive)}
            >
              <span style={bottomTopIndicatorStyle(isRolesActive)} />
              <span style={bottomNavInnerStyle(isRolesActive)}>
                <span style={bottomIconWrapStyle(isRolesActive)}>
                  <i className="fa-solid fa-shield" />
                </span>
                <span style={bottomLabelStyle(isRolesActive)}>Roles</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/permissions"
              style={() => bottomNavScrollItemStyle(isPermissionsActive)}
            >
              <span style={bottomTopIndicatorStyle(isPermissionsActive)} />
              <span style={bottomNavInnerStyle(isPermissionsActive)}>
                <span style={bottomIconWrapStyle(isPermissionsActive)}>
                  <i className="fa-solid fa-lock" />
                </span>
                <span style={bottomLabelStyle(isPermissionsActive)}>Permisos</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/unidades-servicio"
              style={() => bottomNavScrollItemStyle(isUnidadesServicioActive)}
            >
              <span style={bottomTopIndicatorStyle(isUnidadesServicioActive)} />
              <span style={bottomNavInnerStyle(isUnidadesServicioActive)}>
                <span style={bottomIconWrapStyle(isUnidadesServicioActive)}>
                  <i className="fa-solid fa-layer-group" />
                </span>
                <span style={bottomLabelStyle(isUnidadesServicioActive)}>Unidades</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/empresas"
              style={() => bottomNavScrollItemStyle(isEmpresasActive)}
            >
              <span style={bottomTopIndicatorStyle(isEmpresasActive)} />
              <span style={bottomNavInnerStyle(isEmpresasActive)}>
                <span style={bottomIconWrapStyle(isEmpresasActive)}>
                  <i className="fa-solid fa-building" />
                </span>
                <span style={bottomLabelStyle(isEmpresasActive)}>Empresas</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/grupos"
              style={() => bottomNavScrollItemStyle(isGruposActive)}
            >
              <span style={bottomTopIndicatorStyle(isGruposActive)} />
              <span style={bottomNavInnerStyle(isGruposActive)}>
                <span style={bottomIconWrapStyle(isGruposActive)}>
                  <i className="fa-solid fa-people-group" />
                </span>
                <span style={bottomLabelStyle(isGruposActive)}>Grupos</span>
              </span>
            </NavLink>

            <NavLink
              to="/admin/forms"
              style={() => bottomNavScrollItemStyle(isFormsActive)}
            >
              <span style={bottomTopIndicatorStyle(isFormsActive)} />
              <span style={bottomNavInnerStyle(isFormsActive)}>
                <span style={bottomIconWrapStyle(isFormsActive)}>
                  <i className="fa-brands fa-wpforms" />
                </span>
                <span style={bottomLabelStyle(isFormsActive)}>Formularios</span>
              </span>
            </NavLink>

            <button
              type="button"
              onClick={goUserPanel}
              style={bottomNavScrollItemStyle(false)}
            >
              <span style={bottomTopIndicatorStyle(false)} />
              <span style={bottomNavInnerStyle(false)}>
                <span style={bottomIconWrapStyle(false)}>
                  <i className="fa-solid fa-arrow-left" />
                </span>
                <span style={bottomLabelStyle(false)}>Usuario</span>
              </span>
            </button>
          </nav>
        </div>
      ) : null}
    </div>
  );
}