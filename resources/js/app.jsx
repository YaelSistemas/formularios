import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { setupAutoSync } from "./offline/sync";
import { getOfflineUser } from "./offline/session";
import OfflineBootstrapScreen from "./components/OfflineBootstrapScreen";
import {
  shouldRunOfflineBootstrap,
  runOfflineBootstrap,
} from "./offline/bootstrap";

import Login from "./pages/Login";

// Panel normal
import AppLayout from "./layouts/AppLayout";
import FormsIndex from "./pages/user/FormsIndex";

// Admin
import AdminLayout from "./layouts/AdminLayout";
import AdminUsers from "./pages/admin/AdminUsers";
import AdminRoles from "./pages/admin/AdminRoles";
import AdminPermissions from "./pages/admin/AdminPermissions";
import AdminUnidadesServicio from "./pages/admin/AdminUnidadesServicio";
import AdminEmpresas from "./pages/admin/AdminEmpresas";
import AdminGrupos from "./pages/admin/AdminGrupos";
import AdminForms from "./pages/admin/AdminForms";

// PWA SW register (vite-plugin-pwa)
import { registerSW } from "virtual:pwa-register";

const updateSW = registerSW({
  immediate: true,
  onNeedRefresh() {
    console.log("Nueva versión disponible");
    updateSW(true);
  },
  onOfflineReady() {
    console.log("La app ya está lista para usarse offline");
  },
});

setupAutoSync({
  intervalMs: 15000,
  runOnStart: true,
});

function getStoredUser() {
  try {
    const raw = localStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function normalizeRoles(user) {
  if (!user) return [];

  const rolesFromArray = Array.isArray(user.roles)
    ? user.roles
        .map((r) => (typeof r === "string" ? r : r?.name))
        .filter(Boolean)
    : [];

  const roleSingle = user.role
    ? [typeof user.role === "string" ? user.role : user.role?.name].filter(Boolean)
    : [];

  return [...new Set([...rolesFromArray, ...roleSingle])];
}

function normalizePermissions(user) {
  if (!user) return [];

  const directPermissions = Array.isArray(user.permissions)
    ? user.permissions
        .map((p) => (typeof p === "string" ? p : p?.name))
        .filter(Boolean)
    : [];

  return [...new Set(directPermissions)];
}

function isAdmin(user) {
  const roles = normalizeRoles(user).map((r) => String(r).toLowerCase());
  return roles.includes("administrador");
}

function hasPermission(user, permission) {
  if (isAdmin(user)) return true;
  const permissions = normalizePermissions(user);
  return permissions.includes(permission);
}

function canEnterOffline() {
  if (typeof navigator !== "undefined" && navigator.onLine) return false;
  const offlineUser = getOfflineUser();
  return !!offlineUser?.id;
}

function RequireAuth({ children }) {
  const token = localStorage.getItem("token");

  if (token) return children;

  if (canEnterOffline()) {
    const offlineUser = getOfflineUser();
    if (offlineUser) {
      localStorage.setItem("user", JSON.stringify(offlineUser));
    }
    return children;
  }

  return <Navigate to="/login" replace />;
}

function RequireAdminPanelAccess({ children }) {
  const user = getStoredUser();

  if (!user) return <Navigate to="/login" replace />;

  if (isAdmin(user) || hasPermission(user, "admin.panel.view")) {
    return children;
  }

  return <Navigate to="/forms" replace />;
}

function RequireModulePermission({ permission, children }) {
  const user = getStoredUser();

  if (!user) return <Navigate to="/login" replace />;

  if (isAdmin(user) || hasPermission(user, permission)) {
    return children;
  }

  return <Navigate to="/admin" replace />;
}

function AdminIndexRedirect() {
  const user = getStoredUser();

  if (!user) return <Navigate to="/login" replace />;

  if (isAdmin(user)) {
    return <Navigate to="/admin/users" replace />;
  }

  const adminModules = [
    { path: "/admin/users", permission: "usuarios.view" },
    { path: "/admin/roles", permission: "roles.view" },
    { path: "/admin/permissions", permission: "permisos.view" },
    { path: "/admin/unidades-servicio", permission: "unidades_servicio.view" },
    { path: "/admin/empresas", permission: "empresas.view" },
    { path: "/admin/grupos", permission: "grupos.view" },
    { path: "/admin/forms", permission: "formularios.admin.view" },
  ];

  const firstAllowed = adminModules.find((item) =>
    hasPermission(user, item.permission)
  );

  if (firstAllowed) {
    return <Navigate to={firstAllowed.path} replace />;
  }

  return <Navigate to="/forms" replace />;
}

function App() {
  const [bootState, setBootState] = useState({
    checking: true,
    running: false,
    progress: null,
  });

  useEffect(() => {
    let cancelled = false;

    async function boot() {
      try {
        const user = getStoredUser();
        const token = localStorage.getItem("token");

        if (!user?.id || !token || !navigator.onLine) {
          if (!cancelled) {
            setBootState({
              checking: false,
              running: false,
              progress: null,
            });
          }
          return;
        }

        const check = await shouldRunOfflineBootstrap();

        if (!check?.shouldRun) {
          if (!cancelled) {
            setBootState({
              checking: false,
              running: false,
              progress: null,
            });
          }
          return;
        }

        if (!cancelled) {
          setBootState({
            checking: false,
            running: true,
            progress: {
              formsDone: 0,
              formsTotal: Number(check?.remoteMeta?.forms_count || 0),
              recordsDone: 0,
              recordsTotal: Number(check?.remoteMeta?.submissions_count || 0),
              pdfsDone: 0,
              pdfsTotal: Number(check?.remoteMeta?.pdfs_count || 0),
              message: "Preparando datos offline...",
            },
          });
        }

        await runOfflineBootstrap({
          userId: Number(user.id),
          token,
          onProgress: (progress) => {
            if (cancelled) return;
            setBootState({
              checking: false,
              running: true,
              progress,
            });
          },
        });

        if (!cancelled) {
          setBootState({
            checking: false,
            running: false,
            progress: null,
          });
        }
      } catch (e) {
        if (!cancelled) {
          setBootState({
            checking: false,
            running: false,
            progress: null,
          });
        }
      }
    }

    boot();

    return () => {
      cancelled = true;
    };
  }, []);

  if (bootState.checking || bootState.running) {
    return <OfflineBootstrapScreen progress={bootState.progress} />;
  }

  return (
    <BrowserRouter>
      <Routes>
        {/* Public */}
        <Route path="/login" element={<Login />} />

        {/* Panel normal */}
        <Route
          path="/"
          element={
            <RequireAuth>
              <AppLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="/forms" replace />} />
          <Route path="forms" element={<FormsIndex />} />
        </Route>

        {/* Admin con layout + subrutas */}
        <Route
          path="/admin"
          element={
            <RequireAuth>
              <RequireAdminPanelAccess>
                <AdminLayout />
              </RequireAdminPanelAccess>
            </RequireAuth>
          }
        >
          <Route index element={<AdminIndexRedirect />} />

          <Route
            path="users"
            element={
              <RequireModulePermission permission="usuarios.view">
                <AdminUsers />
              </RequireModulePermission>
            }
          />

          <Route
            path="roles"
            element={
              <RequireModulePermission permission="roles.view">
                <AdminRoles />
              </RequireModulePermission>
            }
          />

          <Route
            path="permissions"
            element={
              <RequireModulePermission permission="permisos.view">
                <AdminPermissions />
              </RequireModulePermission>
            }
          />

          <Route
            path="unidades-servicio"
            element={
              <RequireModulePermission permission="unidades_servicio.view">
                <AdminUnidadesServicio />
              </RequireModulePermission>
            }
          />

          <Route
            path="empresas"
            element={
              <RequireModulePermission permission="empresas.view">
                <AdminEmpresas />
              </RequireModulePermission>
            }
          />

          <Route
            path="grupos"
            element={
              <RequireModulePermission permission="grupos.view">
                <AdminGrupos />
              </RequireModulePermission>
            }
          />

          <Route
            path="forms"
            element={
              <RequireModulePermission permission="formularios.admin.view">
                <AdminForms />
              </RequireModulePermission>
            }
          />
        </Route>

        {/* Fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);