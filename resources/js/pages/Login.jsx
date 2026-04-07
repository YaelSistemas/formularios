import React, { useEffect, useState } from "react";
import { apiLogin, apiMe } from "../services/api";
import {
  clearOfflineSession,
  getOfflineUser,
  saveOfflineSession,
} from "../offline/session";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const [err, setErr] = useState("");
  const [loading, setLoading] = useState(false);

  const normalizePermissions = (user) => {
    if (!user) return [];

    const directPermissions = Array.isArray(user.permissions)
      ? user.permissions
          .map((p) => (typeof p === "string" ? p : p?.name))
          .filter(Boolean)
      : [];

    return [...new Set(directPermissions)];
  };

  const normalizeRoles = (user) => {
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
  };

  const isAdmin = (user) => {
    const roles = normalizeRoles(user).map((r) => String(r).toLowerCase());
    return roles.includes("administrador");
  };

  const hasPermission = (user, permission) => {
    if (isAdmin(user)) return true;
    const permissions = normalizePermissions(user);
    return permissions.includes(permission);
  };

  const isInactiveUser = (user) => {
    if (!user || typeof user !== "object") return false;

    if (user.active === false) return true;
    if (user.is_active === false) return true;
    if (user.status === false) return true;
    if (user.enabled === false) return true;

    const statusValue = String(user.status ?? "").trim().toLowerCase();
    const stateValue = String(user.state ?? "").trim().toLowerCase();

    if (statusValue && ["inactive", "inactivo", "0"].includes(statusValue)) return true;
    if (stateValue && ["inactive", "inactivo", "0"].includes(stateValue)) return true;

    return false;
  };

  const inactiveMessage =
    "Tu usuario no tiene acceso en este momento. Comunícate con tu administrador o con el equipo de Sistemas.";

  const clearSession = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    clearOfflineSession();
  };

  const getRedirectPath = () => {
    return "/forms";
  };

  useEffect(() => {
    (async () => {
      const token = localStorage.getItem("token");

      if (!token) {
        if (!navigator.onLine) {
          const offlineUser = getOfflineUser();
          if (offlineUser?.id) {
            localStorage.setItem("user", JSON.stringify(offlineUser));
            window.location.href = "/forms";
            return;
          }
        }
        return;
      }

      try {
        const data = await apiMe();
        const user = data?.user || data;

        if (isInactiveUser(user)) {
          clearSession();
          setErr(inactiveMessage);
          return;
        }

        localStorage.setItem("user", JSON.stringify(user));
        saveOfflineSession(user);
        window.location.href = getRedirectPath();
      } catch {
        if (!navigator.onLine) {
          const offlineUser = getOfflineUser();
          if (offlineUser?.id) {
            localStorage.setItem("user", JSON.stringify(offlineUser));
            window.location.href = "/forms";
            return;
          }
        }

        clearSession();
      }
    })();
  }, []);

  const onSubmit = async (e) => {
    e.preventDefault();
    setErr("");
    setLoading(true);

    try {
      const { token, user } = await apiLogin({
        email: email.trim(),
        password,
      });

      if (isInactiveUser(user)) {
        clearSession();
        setErr(inactiveMessage);
        return;
      }

      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
      saveOfflineSession(user);
      window.location.href = getRedirectPath();
    } catch (ex) {
      clearSession();
      setErr(ex?.message || "Error al iniciar sesión");
    } finally {
      setLoading(false);
    }
  };

  const styles = {
    page: {
      minHeight: "100vh",
      background: "#f4f4f5",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      padding: 16,
      fontFamily: "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif",
    },
    card: {
      width: "100%",
      maxWidth: 460,
      background: "#fff",
      border: "1px solid #e4e4e7",
      borderRadius: 8,
      boxShadow: "0 2px 8px rgba(0,0,0,.04)",
      padding: 24,
    },
    title: {
      margin: 0,
      marginBottom: 8,
      fontSize: 24,
      lineHeight: 1.2,
      color: "#18181b",
      fontWeight: 700,
    },
    subtitle: {
      margin: 0,
      marginBottom: 20,
      color: "#52525b",
      fontSize: 14,
    },
    label: {
      display: "block",
      marginBottom: 6,
      color: "#27272a",
      fontSize: 14,
      fontWeight: 600,
    },
    input: {
      width: "100%",
      boxSizing: "border-box",
      border: "1px solid #d4d4d8",
      borderRadius: 8,
      padding: "12px 14px",
      fontSize: 14,
      outline: "none",
      background: "#fff",
      color: "#18181b",
      marginBottom: 14,
    },
    button: {
      width: "100%",
      border: 0,
      borderRadius: 8,
      padding: "12px 14px",
      background: loading ? "#94a3b8" : "#2563eb",
      color: "#fff",
      fontSize: 14,
      fontWeight: 700,
      cursor: loading ? "not-allowed" : "pointer",
    },
    error: {
      marginBottom: 14,
      padding: 12,
      borderRadius: 8,
      background: "#fef2f2",
      color: "#b91c1c",
      border: "1px solid #fecaca",
      fontSize: 14,
    },
  };

  return (
    <div style={styles.page}>
      <form onSubmit={onSubmit} style={styles.card}>
        <h1 style={styles.title}>Iniciar sesión</h1>
        <p style={styles.subtitle}>Accede a tus formularios asignados.</p>

        {err ? <div style={styles.error}>{err}</div> : null}

        <label style={styles.label}>Correo</label>
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          autoComplete="username"
          style={styles.input}
        />

        <label style={styles.label}>Contraseña</label>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          autoComplete="current-password"
          style={styles.input}
        />

        <button type="submit" disabled={loading} style={styles.button}>
          {loading ? "Entrando..." : "Entrar"}
        </button>
      </form>
    </div>
  );
}
