import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import Login from "./pages/Login";
import AdminDashboard from "./pages/admin/AdminDashboard";
import UserDashboard from "./pages/user/UserDashboard";

// NUEVO: páginas formularios
import FormsIndex from "./pages/user/forms/FormsIndex";
import FormNew from "./pages/user/forms/FormNew";
import FormShow from "./pages/user/forms/FormShow";

// NUEVO: unauthorized
import Unauthorized from "./pages/Unauthorized";

import { registerSW } from "virtual:pwa-register";
import { apiMe } from "./services/api";

registerSW({ immediate: true });

function RequireAuth({ children }) {
  const token = localStorage.getItem("token");
  if (!token) return <Navigate to="/login" replace />;
  return children;
}

function RequireAdmin({ children }) {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (!token) return <Navigate to="/login" replace />;
  if (!user?.is_admin) return <Navigate to="/" replace />;

  return children;
}

function RequirePermission({ permission, children }) {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (!token) return <Navigate to="/login" replace />;

  // Admin bypass (por rol)
  if (user?.is_admin) return children;

  const perms = Array.isArray(user?.permissions) ? user.permissions : [];
  if (!perms.includes(permission)) return <Navigate to="/unauthorized" replace />;

  return children;
}

// valida token al cargar
function BootGuard({ children }) {
  const [ready, setReady] = useState(false);

  useEffect(() => {
    (async () => {
      const token = localStorage.getItem("token");
      if (!token) return setReady(true);

      try {
        const data = await apiMe();
        localStorage.setItem("user", JSON.stringify(data.user));
      } catch (e) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
      } finally {
        setReady(true);
      }
    })();
  }, []);

  if (!ready) return <div style={{ padding: 16 }}>Cargando...</div>;
  return children;
}

function App() {
  return (
    <BrowserRouter>
      <BootGuard>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/unauthorized" element={<Unauthorized />} />

          <Route
            path="/admin"
            element={
              <RequireAdmin>
                <AdminDashboard />
              </RequireAdmin>
            }
          />

          {/* HOME: manda a formularios */}
          <Route
            path="/"
            element={
              <RequireAuth>
                <Navigate to="/formularios" replace />
              </RequireAuth>
            }
          />

          {/* PANEL NORMAL (puedes usar UserDashboard como layout si quieres después) */}
          <Route
            path="/dashboard"
            element={
              <RequireAuth>
                <UserDashboard />
              </RequireAuth>
            }
          />

          {/* FORMULARIOS */}
          <Route
            path="/formularios"
            element={
              <RequirePermission permission="formularios.view">
                <FormsIndex />
              </RequirePermission>
            }
          />

          <Route
            path="/formularios/nuevo"
            element={
              <RequirePermission permission="formularios.create">
                <FormNew />
              </RequirePermission>
            }
          />

          <Route
            path="/formularios/:id"
            element={
              <RequirePermission permission="formularios.view">
                <FormShow />
              </RequirePermission>
            }
          />

          {/* fallback */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BootGuard>
    </BrowserRouter>
  );
}

ReactDOM.createRoot(document.getElementById("app")).render(<App />);