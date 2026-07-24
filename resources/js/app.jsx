import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom/client";
import {
  BrowserRouter,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";

import "@fortawesome/fontawesome-free/css/all.min.css";

import {
  setupAutoSync,
  syncNow,
} from "./offline/sync";

import { apiMe } from "./services/api";

import {
  getOfflineUser,
  saveOfflineSession,
} from "./offline/session";

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

/*
|--------------------------------------------------------------------------
| Configuración de actualización offline
|--------------------------------------------------------------------------
*/

const OFFLINE_BOOTSTRAP_REASON_KEY =
  "offline_bootstrap_reason";

const CURRENT_USER_UPDATED_EVENT =
  "current-user-updated";

/*
 * Cada 20 segundos se consulta únicamente bootstrap-meta.
 * Solo si existen cambios se descarga el bootstrap completo.
 */
const REMOTE_CHECK_INTERVAL_MS = 20000;

/*
 * Cada 15 segundos se intenta subir la cola local pendiente.
 */
const LOCAL_SYNC_INTERVAL_MS = 15000;

/*
|--------------------------------------------------------------------------
| Service Worker
|--------------------------------------------------------------------------
*/

const updateSW = registerSW({
  immediate: true,

  onNeedRefresh() {
    console.log(
      "Nueva versión disponible"
    );

    updateSW(true);
  },

  onOfflineReady() {
    console.log(
      "La app ya está lista para usarse offline"
    );
  },
});

/*
|--------------------------------------------------------------------------
| Usuario almacenado
|--------------------------------------------------------------------------
*/

function getStoredUser() {
  try {
    const raw =
      localStorage.getItem("user");

    return raw
      ? JSON.parse(raw)
      : null;
  } catch {
    return null;
  }
}

/*
|--------------------------------------------------------------------------
| Motivo del bootstrap
|--------------------------------------------------------------------------
|
| Login.jsx guardará temporalmente:
|
| offline_bootstrap_reason = login
|
| La marca se consume una sola vez.
|
*/

function consumeOfflineBootstrapReason() {
  try {
    const reason =
      sessionStorage.getItem(
        OFFLINE_BOOTSTRAP_REASON_KEY
      ) || "";

    sessionStorage.removeItem(
      OFFLINE_BOOTSTRAP_REASON_KEY
    );

    return reason;
  } catch {
    return "";
  }
}

/*
|--------------------------------------------------------------------------
| Roles y permisos
|--------------------------------------------------------------------------
*/

function normalizeRoles(user) {
  if (!user) {
    return [];
  }

  const rolesFromArray =
    Array.isArray(user.roles)
      ? user.roles
          .map((role) =>
            typeof role === "string"
              ? role
              : role?.name
          )
          .filter(Boolean)
      : [];

  const roleSingle =
    user.role
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
}

function normalizePermissions(user) {
  if (!user) {
    return [];
  }

  const directPermissions =
    Array.isArray(user.permissions)
      ? user.permissions
          .map((permission) =>
            typeof permission === "string"
              ? permission
              : permission?.name
          )
          .filter(Boolean)
      : [];

  return [
    ...new Set(directPermissions),
  ];
}

function isAdmin(user) {
  const roles =
    normalizeRoles(user).map((role) =>
      String(role).toLowerCase()
    );

  return roles.includes(
    "administrador"
  );
}

function hasPermission(
  user,
  permission
) {
  if (isAdmin(user)) {
    return true;
  }

  const permissions =
    normalizePermissions(user);

  return permissions.includes(
    permission
  );
}

/*
|--------------------------------------------------------------------------
| Acceso offline
|--------------------------------------------------------------------------
*/

function canEnterOffline() {
  if (
    typeof navigator !== "undefined" &&
    navigator.onLine
  ) {
    return false;
  }

  const offlineUser =
    getOfflineUser();

  return Boolean(
    offlineUser?.id
  );
}

/*
|--------------------------------------------------------------------------
| Protección de rutas
|--------------------------------------------------------------------------
*/

function RequireAuth({ children }) {
  const token =
    localStorage.getItem("token");

  if (token) {
    return children;
  }

  if (canEnterOffline()) {
    const offlineUser =
      getOfflineUser();

    if (offlineUser) {
      localStorage.setItem(
        "user",
        JSON.stringify(offlineUser)
      );
    }

    return children;
  }

  return (
    <Navigate
      to="/login"
      replace
    />
  );
}

function RequireAdminPanelAccess({
  children,
}) {
  const user = getStoredUser();

  if (!user) {
    return (
      <Navigate
        to="/login"
        replace
      />
    );
  }

  if (
    isAdmin(user) ||
    hasPermission(
      user,
      "admin.panel.view"
    )
  ) {
    return children;
  }

  return (
    <Navigate
      to="/forms"
      replace
    />
  );
}

function RequireModulePermission({
  permission,
  children,
}) {
  const user = getStoredUser();

  if (!user) {
    return (
      <Navigate
        to="/login"
        replace
      />
    );
  }

  if (
    isAdmin(user) ||
    hasPermission(
      user,
      permission
    )
  ) {
    return children;
  }

  return (
    <Navigate
      to="/admin"
      replace
    />
  );
}

function AdminIndexRedirect() {
  const user = getStoredUser();

  if (!user) {
    return (
      <Navigate
        to="/login"
        replace
      />
    );
  }

  if (isAdmin(user)) {
    return (
      <Navigate
        to="/admin/users"
        replace
      />
    );
  }

  const adminModules = [
    {
      path: "/admin/users",
      permission: "usuarios.view",
    },
    {
      path: "/admin/roles",
      permission: "roles.view",
    },
    {
      path: "/admin/permissions",
      permission: "permisos.view",
    },
    {
      path: "/admin/unidades-servicio",
      permission:
        "unidades_servicio.view",
    },
    {
      path: "/admin/empresas",
      permission: "empresas.view",
    },
    {
      path: "/admin/grupos",
      permission: "grupos.view",
    },
    {
      path: "/admin/forms",
      permission:
        "formularios.admin.view",
    },
  ];

  const firstAllowed =
    adminModules.find((item) =>
      hasPermission(
        user,
        item.permission
      )
    );

  if (firstAllowed) {
    return (
      <Navigate
        to={firstAllowed.path}
        replace
      />
    );
  }

  return (
    <Navigate
      to="/forms"
      replace
    />
  );
}

/*
|--------------------------------------------------------------------------
| Aplicación principal
|--------------------------------------------------------------------------
*/

function App() {
  const [bootState, setBootState] =
    useState({
      checking: false,
      running: false,
      progress: null,
    });

  useEffect(() => {
    let cancelled = false;

    /*
     * Evita que los eventos online, visibilitychange
     * y el intervalo consulten al mismo tiempo.
     */
    let activeRefresh = null;

    /*
    |--------------------------------------------------------------------------
    | Actualización del usuario, roles y permisos
    |--------------------------------------------------------------------------
    */

    let activeUserRefresh = null;

    async function refreshCurrentUser() {
      /*
       * Evita varias peticiones apiMe simultáneas.
       */
      if (activeUserRefresh) {
        return activeUserRefresh;
      }

      activeUserRefresh = (async () => {
        const token =
          localStorage.getItem("token");

        if (
          !token ||
          !navigator.onLine
        ) {
          return {
            ok: false,
            skipped: true,
          };
        }

        try {
          const data =
            await apiMe();

          const updatedUser =
            data?.user || data;

          if (!updatedUser?.id) {
            return {
              ok: false,
              skipped: true,
            };
          }

          const previousUser =
            getStoredUser();

          const changed =
            JSON.stringify(
              previousUser ?? null
            ) !==
            JSON.stringify(
              updatedUser
            );

          /*
           * Actualiza el usuario utilizado
           * por la aplicación.
           */
          localStorage.setItem(
            "user",
            JSON.stringify(
              updatedUser
            )
          );

          /*
           * Actualiza también la copia
           * utilizada cuando está offline.
           */
          saveOfflineSession(
            updatedUser
          );

          /*
           * Avisamos a los componentes solamente
           * cuando realmente cambió el usuario,
           * sus roles o sus permisos.
           */
          if (changed) {
            window.dispatchEvent(
              new CustomEvent(
                CURRENT_USER_UPDATED_EVENT,
                {
                  detail:
                    updatedUser,
                }
              )
            );
          }

          return {
            ok: true,
            changed,
            user: updatedUser,
          };
        } catch (error) {
          console.error(
            "No se pudo actualizar el usuario:",
            error
          );

          return {
            ok: false,
            error,
          };
        }
      })();

      try {
        return await activeUserRefresh;
      } finally {
        activeUserRefresh = null;
      }
    }

    /*
    |--------------------------------------------------------------------------
    | Contexto de la sesión actual
    |--------------------------------------------------------------------------
    */

    function getOnlineSession() {
      const user = getStoredUser();

      const token =
        localStorage.getItem(
          "token"
        );

      if (
        !user?.id ||
        !token ||
        !navigator.onLine
      ) {
        return null;
      }

      return {
        user,
        token,
        userId: Number(user.id),
      };
    }

    /*
    |--------------------------------------------------------------------------
    | Ocultar pantalla de progreso
    |--------------------------------------------------------------------------
    */

    function closeBootstrapScreen() {
      if (cancelled) {
        return;
      }

      setBootState({
        checking: false,
        running: false,
        progress: null,
      });
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar información offline
    |--------------------------------------------------------------------------
    |
    | visible = true
    | Muestra OfflineBootstrapScreen.
    |
    | visible = false
    | Descarga en segundo plano.
    |
    */

    async function refreshOfflineData({
      visible = false,
      reason = "background",
      syncPending = false,
    } = {}) {
      /*
       * Si ya hay una revisión en proceso,
       * esperamos esa misma revisión.
       */
      if (activeRefresh) {
        return activeRefresh;
      }

      activeRefresh = (async () => {
        const session =
          getOnlineSession();

        if (!session) {
          return {
            ok: false,
            skipped: true,
            reason: "no_online_session",
          };
        }

        const {
          userId,
          token,
        } = session;

        /*
         * En inicio, reconexión o regreso del segundo plano,
         * primero intentamos subir las capturas pendientes.
         */
        if (syncPending) {
          await syncNow().catch(
            () => null
          );
        }

        const check =
          await shouldRunOfflineBootstrap({
            userId,
          });

        /*
         * Si el meta no cambió, no mostramos pantalla
         * ni descargamos el bootstrap completo.
         */
        if (!check?.shouldRun) {
          return {
            ok: true,
            skipped: true,
            reason:
              check?.reason ||
              "no_changes",
          };
        }

        /*
         * La pantalla solo se activa cuando se solicitó
         * explícitamente modo visible.
         */
        if (
          visible &&
          !cancelled
        ) {
          setBootState({
            checking: false,
            running: true,
            progress: {
              formsDone: 0,

              formsTotal: Number(
                check?.remoteMeta
                  ?.forms_count || 0
              ),

              recordsDone: 0,

              recordsTotal: Number(
                check?.remoteMeta
                  ?.submissions_count ||
                  0
              ),

              pdfsDone: 0,

              pdfsTotal: Number(
                check?.remoteMeta
                  ?.pdfs_count || 0
              ),

              message:
                "Preparando datos offline...",
            },
          });
        }

        try {
          const result =
            await runOfflineBootstrap({
              userId,
              token,

              /*
               * Reutilizamos el meta que ya consultamos
               * para no hacer una petición duplicada.
               */
              remoteMeta:
                check.remoteMeta,

              reason,

              mode: visible
                ? "visible"
                : "silent",

              /*
               * En modo silencioso no enviamos onProgress,
               * por lo tanto no se altera la interfaz.
               */
              onProgress: visible
                ? (progress) => {
                    if (cancelled) {
                      return;
                    }

                    setBootState({
                      checking: false,
                      running: true,
                      progress,
                    });
                  }
                : undefined,
            });

          return result;
        } finally {
          if (visible) {
            closeBootstrapScreen();
          }
        }
      })();

      try {
        return await activeRefresh;
      } finally {
        activeRefresh = null;
      }
    }

    /*
    |--------------------------------------------------------------------------
    | Sincronización periódica de la cola local
    |--------------------------------------------------------------------------
    |
    | Solo sube capturas pendientes.
    | No descarga formularios ni PDFs.
    |
    */

    const stopAutoSync =
      setupAutoSync({
        intervalMs:
          LOCAL_SYNC_INTERVAL_MS,

        /*
         * El inicio lo coordina este archivo para mantener
         * el orden correcto:
         *
         * 1. Subir pendientes.
         * 2. Consultar cambios.
         * 3. Descargar cambios.
         */
        runOnStart: false,
      });

    /*
    |--------------------------------------------------------------------------
    | Carga inicial
    |--------------------------------------------------------------------------
    |
    | Después del login:
    | - visible
    |
    | Recarga, reapertura o navegación normal:
    | - silenciosa
    |
    */

    async function runInitialRefresh() {
      await refreshCurrentUser();
    
      const session =
        getOnlineSession();

      if (!session) {
        return;
      }

      const storedReason =
        consumeOfflineBootstrapReason();

      const isLogin =
        storedReason === "login";

      await refreshOfflineData({
        visible: isLogin,

        reason: isLogin
          ? "login"
          : "reload",

        syncPending: true,
      });
    }

    /*
    |--------------------------------------------------------------------------
    | Recuperación de internet
    |--------------------------------------------------------------------------
    |
    | Primero sube pendientes y después consulta cambios.
    | La pantalla aparece solamente si realmente existen cambios.
    |
    */

    function handleOnline() {
      refreshCurrentUser()
        .then(() =>
          refreshOfflineData({
            visible: true,
            reason: "reconnect",
            syncPending: true,
          })
        )
        .catch(() => null);
    }

    /*
    |--------------------------------------------------------------------------
    | Regreso del segundo plano
    |--------------------------------------------------------------------------
    |
    | Se actualiza silenciosamente para no interrumpir
    | al usuario ni borrar una captura en proceso.
    |
    */

    function handleVisibilityChange() {
      if (
        document.visibilityState !==
        "visible"
      ) {
        return;
      }

      if (!navigator.onLine) {
        return;
      }

      refreshCurrentUser()
        .then(() =>
          refreshOfflineData({
            visible: false,
            reason: "visibility",
            syncPending: true,
          })
        )
        .catch(() => null);
    }

    /*
    |--------------------------------------------------------------------------
    | Revisión periódica de cambios remotos
    |--------------------------------------------------------------------------
    |
    | Solo consulta bootstrap-meta.
    | Si existe un cambio, descarga silenciosamente.
    |
    */

    const remoteCheckIntervalId =
      window.setInterval(() => {
        if (!navigator.onLine) {
          return;
        }

        if (
          document.visibilityState !==
          "visible"
        ) {
          return;
        }

        refreshCurrentUser()
          .then(() =>
            refreshOfflineData({
              visible: false,
              reason: "interval",
              syncPending: false,
            })
          )
          .catch(() => null);
      }, REMOTE_CHECK_INTERVAL_MS);

    window.addEventListener(
      "online",
      handleOnline
    );

    document.addEventListener(
      "visibilitychange",
      handleVisibilityChange
    );

    runInitialRefresh().catch(
      () => {
        closeBootstrapScreen();
      }
    );

    /*
    |--------------------------------------------------------------------------
    | Limpieza
    |--------------------------------------------------------------------------
    */

    return () => {
      cancelled = true;

      stopAutoSync?.();

      window.clearInterval(
        remoteCheckIntervalId
      );

      window.removeEventListener(
        "online",
        handleOnline
      );

      document.removeEventListener(
        "visibilitychange",
        handleVisibilityChange
      );
    };
  }, []);

  /*
  |--------------------------------------------------------------------------
  | Pantalla visible de preparación
  |--------------------------------------------------------------------------
  */

  if (bootState.running) {
    return (
      <OfflineBootstrapScreen
        progress={
          bootState.progress
        }
      />
    );
  }

  /*
  |--------------------------------------------------------------------------
  | Rutas
  |--------------------------------------------------------------------------
  */

  return (
    <BrowserRouter>
      <Routes>
        {/* Public */}
        <Route
          path="/login"
          element={<Login />}
        />

        {/* Panel normal */}
        <Route
          path="/"
          element={
            <RequireAuth>
              <AppLayout />
            </RequireAuth>
          }
        >
          <Route
            index
            element={
              <Navigate
                to="/forms"
                replace
              />
            }
          />

          <Route
            path="forms"
            element={<FormsIndex />}
          />
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
          <Route
            index
            element={
              <AdminIndexRedirect />
            }
          />

          <Route
            path="users"
            element={
              <RequireModulePermission
                permission="usuarios.view"
              >
                <AdminUsers />
              </RequireModulePermission>
            }
          />

          <Route
            path="roles"
            element={
              <RequireModulePermission
                permission="roles.view"
              >
                <AdminRoles />
              </RequireModulePermission>
            }
          />

          <Route
            path="permissions"
            element={
              <RequireModulePermission
                permission="permisos.view"
              >
                <AdminPermissions />
              </RequireModulePermission>
            }
          />

          <Route
            path="unidades-servicio"
            element={
              <RequireModulePermission
                permission="unidades_servicio.view"
              >
                <AdminUnidadesServicio />
              </RequireModulePermission>
            }
          />

          <Route
            path="empresas"
            element={
              <RequireModulePermission
                permission="empresas.view"
              >
                <AdminEmpresas />
              </RequireModulePermission>
            }
          />

          <Route
            path="grupos"
            element={
              <RequireModulePermission
                permission="grupos.view"
              >
                <AdminGrupos />
              </RequireModulePermission>
            }
          />

          <Route
            path="forms"
            element={
              <RequireModulePermission
                permission="formularios.admin.view"
              >
                <AdminForms />
              </RequireModulePermission>
            }
          />
        </Route>

        {/* Fallback */}
        <Route
          path="*"
          element={
            <Navigate
              to="/"
              replace
            />
          }
        />
      </Routes>
    </BrowserRouter>
  );
}

ReactDOM.createRoot(
  document.getElementById("app")
).render(
  <App />
);