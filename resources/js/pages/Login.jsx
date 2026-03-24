import React, { useEffect, useState } from "react";
import { apiLogin, apiMe } from "../services/api";

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
  };

  const getRedirectPath = () => {
    return "/forms";
  };

  useEffect(() => {
    (async () => {
      const token = localStorage.getItem("token");
      if (!token) return;

      try {
        const data = await apiMe();
        const user = data?.user || data;

        if (isInactiveUser(user)) {
          clearSession();
          setErr(inactiveMessage);
          return;
        }

        localStorage.setItem("user", JSON.stringify(user));
        window.location.href = getRedirectPath();
      } catch {
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
      boxShadow: "0 2px 8px rgba(0,0,0,.06)",
      padding: 24,
    },
    logoWrap: { display: "flex", justifyContent: "center", marginBottom: 18 },
    logo: { height: 64, width: "auto", objectFit: "contain" },
    label: {
      display: "block",
      fontSize: 13,
      fontWeight: 600,
      color: "#3f3f46",
      marginBottom: 6,
    },
    input: {
      width: "100%",
      borderRadius: 6,
      border: "1px solid #d4d4d8",
      background: "#f8fafc",
      padding: "10px 12px",
      outline: "none",
      fontSize: 14,
    },
    err: {
      marginBottom: 12,
      borderRadius: 6,
      border: "1px solid #fecaca",
      background: "#fef2f2",
      color: "#b91c1c",
      padding: "8px 10px",
      fontSize: 13,
      lineHeight: 1.5,
    },
    row: { marginBottom: 12 },
    actions: { display: "flex", justifyContent: "flex-end", paddingTop: 8 },
    btn: {
      border: "none",
      borderRadius: 6,
      background: "#1f2937",
      color: "#fff",
      padding: "10px 16px",
      fontSize: 13,
      fontWeight: 700,
      cursor: "pointer",
      letterSpacing: 0.3,
    },
    btnDisabled: { opacity: 0.6, cursor: "not-allowed" },
  };

  return (
    <div style={styles.page}>
      <div style={styles.card}>
        <div style={styles.logoWrap}>
          <img src="/images/Logo-vysisa.png" alt="VYSISA" style={styles.logo} />
        </div>

        {err ? <div style={styles.err}>{err}</div> : null}

        <form onSubmit={onSubmit}>
          <div style={styles.row}>
            <label style={styles.label}>Correo electrónico</label>
            <input
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoComplete="email"
              placeholder="correo@empresa.com"
              style={styles.input}
            />
          </div>

          <div style={styles.row}>
            <label style={styles.label}>Contraseña</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              autoComplete="current-password"
              style={styles.input}
            />
          </div>

          <div style={styles.actions}>
            <button
              type="submit"
              disabled={loading}
              style={{ ...styles.btn, ...(loading ? styles.btnDisabled : {}) }}
            >
              {loading ? "INICIANDO..." : "INICIAR SESIÓN"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}