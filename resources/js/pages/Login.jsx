import React, { useEffect, useState } from "react";
import { apiLogin, apiMe } from "../services/api";
import {
  clearOfflineSession,
  getOfflineUser,
  saveOfflineSession,
} from "../offline/session";

const OFFLINE_BOOTSTRAP_REASON_KEY =
  "offline_bootstrap_reason";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const [err, setErr] = useState("");
  const [loading, setLoading] = useState(false);

  const normalizePermissions = (user) => {
    if (!user) return [];

    const directPermissions = Array.isArray(user.permissions)
      ? user.permissions
          .map((permission) =>
            typeof permission === "string"
              ? permission
              : permission?.name
          )
          .filter(Boolean)
      : [];

    return [...new Set(directPermissions)];
  };

  const normalizeRoles = (user) => {
    if (!user) return [];

    const rolesFromArray = Array.isArray(user.roles)
      ? user.roles
          .map((role) =>
            typeof role === "string"
              ? role
              : role?.name
          )
          .filter(Boolean)
      : [];

    const roleSingle = user.role
      ? [
          typeof user.role === "string"
            ? user.role
            : user.role?.name,
        ].filter(Boolean)
      : [];

    return [
      ...new Set([
        ...rolesFromArray,
        ...roleSingle,
      ]),
    ];
  };

  const isAdmin = (user) => {
    const roles = normalizeRoles(user).map(
      (role) =>
        String(role).toLowerCase()
    );

    return roles.includes(
      "administrador"
    );
  };

  const hasPermission = (
    user,
    permission
  ) => {
    if (isAdmin(user)) {
      return true;
    }

    const permissions =
      normalizePermissions(user);

    return permissions.includes(
      permission
    );
  };

  const isInactiveUser = (user) => {
    if (
      !user ||
      typeof user !== "object"
    ) {
      return false;
    }

    if (user.active === false) {
      return true;
    }

    if (user.is_active === false) {
      return true;
    }

    if (user.status === false) {
      return true;
    }

    if (user.enabled === false) {
      return true;
    }

    const statusValue = String(
      user.status ?? ""
    )
      .trim()
      .toLowerCase();

    const stateValue = String(
      user.state ?? ""
    )
      .trim()
      .toLowerCase();

    if (
      statusValue &&
      [
        "inactive",
        "inactivo",
        "0",
      ].includes(statusValue)
    ) {
      return true;
    }

    if (
      stateValue &&
      [
        "inactive",
        "inactivo",
        "0",
      ].includes(stateValue)
    ) {
      return true;
    }

    return false;
  };

  const inactiveMessage =
    "Tu usuario no tiene acceso en este momento. Comunícate con tu administrador o con el equipo de Sistemas.";

  const clearSession = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");

    try {
      sessionStorage.removeItem(
        OFFLINE_BOOTSTRAP_REASON_KEY
      );
    } catch {
      // La limpieza puede continuar aunque sessionStorage no esté disponible.
    }

    clearOfflineSession();
  };

  const getRedirectPath = () => {
    return "/forms";
  };

  useEffect(() => {
    (async () => {
      const token =
        localStorage.getItem("token");

      if (!token) {
        if (!navigator.onLine) {
          const offlineUser =
            getOfflineUser();

          if (offlineUser?.id) {
            localStorage.setItem(
              "user",
              JSON.stringify(
                offlineUser
              )
            );

            window.location.href =
              "/forms";

            return;
          }
        }

        return;
      }

      try {
        const data =
          await apiMe();

        const user =
          data?.user || data;

        if (
          isInactiveUser(user)
        ) {
          clearSession();
          setErr(
            inactiveMessage
          );

          return;
        }

        localStorage.setItem(
          "user",
          JSON.stringify(user)
        );

        saveOfflineSession(user);

        /*
         * Aquí no se marca como login manual.
         * Esta redirección corresponde a una sesión que ya existía.
         */
        window.location.href =
          getRedirectPath();
      } catch {
        if (!navigator.onLine) {
          const offlineUser =
            getOfflineUser();

          if (offlineUser?.id) {
            localStorage.setItem(
              "user",
              JSON.stringify(
                offlineUser
              )
            );

            window.location.href =
              "/forms";

            return;
          }
        }

        clearSession();
      }
    })();
  }, []);

  const onSubmit = async (event) => {
    event.preventDefault();

    setErr("");
    setLoading(true);

    try {
      const {
        token,
        user,
      } = await apiLogin({
        email: email.trim(),
        password,
      });

      if (
        isInactiveUser(user)
      ) {
        clearSession();
        setErr(
          inactiveMessage
        );

        return;
      }

      localStorage.setItem(
        "token",
        token
      );

      localStorage.setItem(
        "user",
        JSON.stringify(user)
      );

      saveOfflineSession(user);

      /*
       * Marcamos que la siguiente carga viene
       * de un inicio de sesión manual.
       *
       * app.jsx consumirá esta marca una sola vez.
       */
      try {
        sessionStorage.setItem(
          OFFLINE_BOOTSTRAP_REASON_KEY,
          "login"
        );
      } catch {
        // La redirección puede continuar aunque sessionStorage no esté disponible.
      }

      window.location.href =
        getRedirectPath();
    } catch (error) {
      clearSession();

      setErr(
        error?.message ||
          "Error al iniciar sesión"
      );
    } finally {
      setLoading(false);
    }
  };

  const styles = {
    page: {
      width: "100%",
      minHeight: "100vh",
      boxSizing: "border-box",
      background: "#f4f4f5",

      display: "flex",
      alignItems: "flex-start",
      justifyContent: "center",

      paddingTop:
        "clamp(55px, 16vh, 150px)",
      paddingRight: 16,
      paddingBottom: 40,
      paddingLeft: 16,

      fontFamily:
        "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif",
    },

    card: {
      width: "100%",
      maxWidth: 410,
      boxSizing: "border-box",

      background: "#ffffff",
      border:
        "1px solid #e4e4e7",
      borderRadius: 10,

      boxShadow:
        "0 10px 30px rgba(0, 0, 0, 0.10)",

      padding:
        "28px 26px 30px",
    },

    logoContainer: {
      width: "100%",
      display: "flex",
      justifyContent:
        "center",
      alignItems: "center",
      marginBottom: 24,
    },

    logo: {
      display: "block",
      width: "100%",
      maxWidth: 270,
      height: "auto",
      maxHeight: 135,
      objectFit: "contain",
    },

    title: {
      marginTop: 0,
      marginRight: 0,
      marginBottom: 22,
      marginLeft: 0,

      fontSize: 25,
      lineHeight: 1.2,
      color: "#18181b",
      fontWeight: 700,
      textAlign: "left",
    },

    fieldGroup: {
      width: "100%",
      marginBottom: 17,
    },

    label: {
      display: "block",
      marginBottom: 7,

      color: "#27272a",
      fontSize: 14,
      lineHeight: 1.3,
      fontWeight: 600,
    },

    input: {
      display: "block",
      width: "100%",
      height: 46,
      boxSizing: "border-box",

      border:
        "1px solid #cbd5e1",
      borderRadius: 7,

      padding: "11px 13px",

      fontSize: 14,
      color: "#18181b",
      background: "#ffffff",

      outline: "none",
    },

    button: {
      width: "100%",
      minHeight: 46,

      border: "none",
      borderRadius: 7,

      padding: "12px 14px",

      background: loading
        ? "#94a3b8"
        : "#2563eb",

      color: "#ffffff",
      fontSize: 15,
      lineHeight: 1.3,
      fontWeight: 700,

      cursor: loading
        ? "not-allowed"
        : "pointer",

      transition:
        "background-color 0.2s ease, transform 0.1s ease",
    },

    error: {
      marginBottom: 17,
      padding: 12,

      border:
        "1px solid #fecaca",
      borderRadius: 7,

      background: "#fef2f2",
      color: "#b91c1c",

      fontSize: 14,
      lineHeight: 1.4,
    },
  };

  return (
    <div style={styles.page}>
      <form
        onSubmit={onSubmit}
        style={styles.card}
      >
        <div
          style={
            styles.logoContainer
          }
        >
          <img
            src="/images/Logo-vysisa.png"
            alt="Grupo VYSISA"
            style={styles.logo}
          />
        </div>

        <h1 style={styles.title}>
          Iniciar sesión
        </h1>

        {err ? (
          <div
            style={
              styles.error
            }
          >
            {err}
          </div>
        ) : null}

        <div
          style={
            styles.fieldGroup
          }
        >
          <label
            htmlFor="login-email"
            style={styles.label}
          >
            Correo
          </label>

          <input
            id="login-email"
            name="email"
            type="email"
            value={email}
            onChange={(event) =>
              setEmail(
                event.target.value
              )
            }
            autoComplete="username"
            placeholder="Ingresa tu correo"
            required
            style={styles.input}
          />
        </div>

        <div
          style={
            styles.fieldGroup
          }
        >
          <label
            htmlFor="login-password"
            style={styles.label}
          >
            Contraseña
          </label>

          <input
            id="login-password"
            name="password"
            type="password"
            value={password}
            onChange={(event) =>
              setPassword(
                event.target.value
              )
            }
            autoComplete="current-password"
            placeholder="Ingresa tu contraseña"
            required
            style={styles.input}
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          style={styles.button}
        >
          {loading
            ? "Entrando..."
            : "Iniciar sesión"}
        </button>
      </form>
    </div>
  );
}