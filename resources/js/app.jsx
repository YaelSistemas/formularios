import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import Login from "./pages/Login";

// Panel normal
import AppLayout from "./layouts/AppLayout";
import DashboardHome from "./pages/user/DashboardHome";
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
registerSW({ immediate: true });

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

function RequireAuth({ children }) {
  const token = localStorage.getItem("token");
  if (!token) return <Navigate to="/login" replace />;
  return children;
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
          <Route index element={<DashboardHome />} />
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