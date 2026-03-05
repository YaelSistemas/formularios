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
import AdminEmpresas from "./pages/admin/AdminEmpresas";
import AdminGrupos from "./pages/admin/AdminGrupos";
import AdminForms from "./pages/admin/AdminForms";

// PWA SW register (vite-plugin-pwa)
import { registerSW } from "virtual:pwa-register";
registerSW({ immediate: true });

function RequireAuth({ children }) {
  const token = localStorage.getItem("token");
  if (!token) return <Navigate to="/login" replace />;
  return children;
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
              <AdminLayout />
            </RequireAuth>
          }
        >
          <Route index element={<Navigate to="/admin/users" replace />} />
          <Route path="users" element={<AdminUsers />} />
          <Route path="roles" element={<AdminRoles />} />
          <Route path="permissions" element={<AdminPermissions />} />
          <Route path="empresas" element={<AdminEmpresas />} />
          <Route path="grupos" element={<AdminGrupos />} />
          <Route path="forms" element={<AdminForms />} />
        </Route>

        {/* Fallback */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);