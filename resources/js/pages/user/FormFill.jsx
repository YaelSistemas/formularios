import React, { useEffect, useMemo, useRef, useState } from "react";
import { apiPost } from "../../services/api";
import { enqueue } from "../../offline/sync";
import { saveOfflineSubmission } from "../../offline/forms-cache";

import {
  saveFormDraft,
  deleteFormDraft,
} from "../../offline/form-drafts";

import {
  hasFormLayoutComponent,
  resolveFormLayoutComponent,
} from "./forms/layouts/registry";

const NON_INPUT_TYPES = new Set([
  "static_text",
  "separator",
  "fixed_image",
  "fixed_file",
]);

export default function FormFill({
  form,
  onBack,
  readOnly = false,
  initialAnswers = null,
  responseMeta = null,
  isEditing = false,
  editSubmissionId = null,
  onSaved = null,
}) {
  const token = useMemo(() => localStorage.getItem("token") || "", []);
  const fields = Array.isArray(
    form?.payload?.fields
  )
    ? form.payload.fields
    : [];
  
  const layout =
    form?.version_layout_key ||
    form?.form_version?.layout_key ||
    form?.payload?.meta?.layout ||
    "default";

  const [isOnline, setIsOnline] = useState(() => navigator.onLine);
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState("");

  const [successModal, setSuccessModal] = useState({
    open: false,
    title: "",
    text: "",
  });

  const [me] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem("user") || "null");
    } catch {
      return null;
    }
  });

  const currentUserId = Number(me?.id || 0);

  const captureFormVersionId = Number(
    form?.form_version_id ||
      form?.form_version?.id ||
      0
  );
  
  const versionedLayoutMissing =
    captureFormVersionId > 0 &&
    !hasFormLayoutComponent(
      layout
    );

  const permissionSet = useMemo(() => {
    const raw = me?.permissions;
    if (!Array.isArray(raw)) return new Set();

    return new Set(
      raw
        .map((p) => (typeof p === "string" ? p : p?.name))
        .filter(Boolean)
    );
  }, [me]);

  const canSubmit = permissionSet.has("formularios.submit");
  const canEdit = permissionSet.has("formularios.edit");

  const successTimerRef = useRef(null);

  const clearSuccessTimer = () => {
    if (successTimerRef.current) {
      clearTimeout(successTimerRef.current);
      successTimerRef.current = null;
    }
  };

  const closeSuccessModal = () => {
    clearSuccessTimer();
    setSuccessModal({
      open: false,
      title: "",
      text: "",
    });
  };

  const openSuccessModalAndBack = (title, text) => {
    clearSuccessTimer();

    setSuccessModal({
      open: true,
      title,
      text,
    });

    successTimerRef.current = setTimeout(async () => {
      setSuccessModal({
        open: false,
        title: "",
        text: "",
      });

      if (typeof onSaved === "function") {
        await onSaved();
        return;
      }

      if (typeof onBack === "function") {
        onBack();
      }
    }, 10000);
  };

  useEffect(() => {
    return () => {
      clearSuccessTimer();
    };
  }, []);

  const buildInitAnswers = (keepPrev = null, preload = null) => {
    const init = {};

    for (const f of fields) {
      if (!f?.id) continue;

      if (preload && preload[f.id] !== undefined) {
        init[f.id] = preload[f.id];
        continue;
      }

      if (keepPrev && keepPrev[f.id] !== undefined) {
        init[f.id] = keepPrev[f.id];
        continue;
      }

      if (NON_INPUT_TYPES.has(f.type)) {
        init[f.id] = "";
        continue;
      }
      
      if (
        (f.type === "select" || f.type === "list") &&
        f.multiple
      ) {
        init[f.id] = [];
        continue;
      }
      
      if (f.type === "checkbox") {
        init[f.id] = false;
        continue;
      }
      
      if (f.type === "table") {
        init[f.id] = [];
        continue;
      }
      
      init[f.id] = "";
    }

    return init;
  };

  const [answers, setAnswers] = useState(() =>
    buildInitAnswers(null, initialAnswers)
  );

  const draftSaveTimerRef = useRef(null);
  const draftTouchedRef = useRef(false);
  const latestAnswersRef = useRef(answers);
  
  const draftEnabled =
    !readOnly &&
    !isEditing &&
    currentUserId > 0 &&
    Number(form?.id || 0) > 0;
  
  const clearDraftSaveTimer = () => {
    if (draftSaveTimerRef.current) {
      clearTimeout(
        draftSaveTimerRef.current
      );
  
      draftSaveTimerRef.current = null;
    }
  };
  
  const saveDraftNow = async () => {
    if (
      !draftEnabled ||
      !draftTouchedRef.current
    ) {
      return;
    }
  
    clearDraftSaveTimer();
  
    await saveFormDraft(
      currentUserId,
      form.id,
      latestAnswersRef.current,
      captureFormVersionId
    );
  };
  
  const clearCurrentDraft = async () => {
    clearDraftSaveTimer();
  
    /*
     * Lo desactivamos antes de limpiar answers
     * para impedir que el reset vuelva a crear
     * un borrador vacío.
     */
    draftTouchedRef.current = false;
  
    if (!draftEnabled) {
      return;
    }
  
    await deleteFormDraft(
      currentUserId,
      form.id
    );
  };

  useEffect(() => {
    setAnswers((prev) => buildInitAnswers(prev, initialAnswers));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form?.id, JSON.stringify(initialAnswers || {})]);

  /*
  |--------------------------------------------------------------------------
  | Mantener la última respuesta disponible
  |--------------------------------------------------------------------------
  */
  
  useEffect(() => {
    latestAnswersRef.current = answers;
  }, [answers]);
  
  /*
  |--------------------------------------------------------------------------
  | Autoguardado del borrador
  |--------------------------------------------------------------------------
  |
  | Solo se crea un borrador después de que el usuario
  | haya modificado al menos un campo.
  |
  | No se crean borradores al:
  | - Ver registros.
  | - Editar registros existentes.
  |
  */
  
  useEffect(() => {
    if (
      !draftEnabled ||
      !draftTouchedRef.current
    ) {
      return;
    }
  
    clearDraftSaveTimer();
  
    draftSaveTimerRef.current =
      setTimeout(() => {
        saveFormDraft(
          currentUserId,
          form.id,
          latestAnswersRef.current,
          captureFormVersionId
        ).catch(() => null);
  
        draftSaveTimerRef.current = null;
      }, 500);
  
    return () => {
      clearDraftSaveTimer();
    };
  }, [
    answers,
    draftEnabled,
    currentUserId,
    form?.id,
    captureFormVersionId,
  ]);
  
  /*
  |--------------------------------------------------------------------------
  | Guardado al mandar la PWA a segundo plano
  |--------------------------------------------------------------------------
  |
  | Esto ayuda especialmente en celular.
  |
  */
  
  useEffect(() => {
    const flushDraft = () => {
      if (
        document.visibilityState !== "hidden" ||
        !draftEnabled ||
        !draftTouchedRef.current
      ) {
        return;
      }
  
      saveFormDraft(
        currentUserId,
        form.id,
        latestAnswersRef.current,
        captureFormVersionId
      ).catch(() => null);
    };
  
    document.addEventListener(
      "visibilitychange",
      flushDraft
    );
  
    return () => {
      document.removeEventListener(
        "visibilitychange",
        flushDraft
      );
    };
  }, [
    draftEnabled,
    currentUserId,
    form?.id,
    captureFormVersionId,
  ]);
  
  /*
  |--------------------------------------------------------------------------
  | Última protección al salir del componente
  |--------------------------------------------------------------------------
  */
  
  useEffect(() => {
    return () => {
      if (
        draftEnabled &&
        draftTouchedRef.current
      ) {
        saveFormDraft(
          currentUserId,
          form.id,
          latestAnswersRef.current,
          captureFormVersionId
        ).catch(() => null);
      }
    };
  }, [
    draftEnabled,
    currentUserId,
    form?.id,
    captureFormVersionId,
  ]);

  useEffect(() => {
    const onOnline = () => {
      setIsOnline(true);
    };
  
    const onOffline = () => {
      setIsOnline(false);
    };
  
    window.addEventListener("online", onOnline);
    window.addEventListener("offline", onOffline);
  
    return () => {
      window.removeEventListener("online", onOnline);
      window.removeEventListener("offline", onOffline);
    };
  }, []);
  
  const setVal = (id, value) => {
    if (readOnly) return;
  
    /*
     * A partir del primer cambio real del usuario
     * consideramos que existe una captura en progreso.
     */
    if (!isEditing) {
      draftTouchedRef.current = true;
    }
  
    setAnswers((prev) => {
      const next = {
        ...prev,
        [id]: value,
      };
  
      /*
       * Actualizamos inmediatamente la referencia.
       * Así, si el usuario pulsa Volver justo después
       * de escribir, tenemos el valor más reciente.
       */
      latestAnswersRef.current = next;
  
      return next;
    });
  };
  
  const handleBack = async () => {
    /*
     * Si existe una captura modificada,
     * hacemos un último guardado antes de salir.
     */
    try {
      await saveDraftNow();
    } catch {
      // Salir del formulario no debe bloquearse
      // por un fallo de IndexedDB.
    }
  
    if (typeof onBack === "function") {
      onBack();
    }
  };

  const validate = () => {
    for (const f of fields) {
      if (!f?.id) continue;
  
      if (NON_INPUT_TYPES.has(f.type)) continue;
  
      if (f.type === "table") {
        const rows = Array.isArray(answers[f.id])
          ? answers[f.id]
          : [];
  
        if (f.required && rows.length < 1) {
          return `Falta responder: ${f.label}`;
        }
  
        continue;
      }
  
      const v = answers[f.id];
  
      if (f.required) {
        if (f.type === "checkbox") {
          if (!v) {
            return `Falta responder: ${f.label}`;
          }
  
          continue;
        }
  
        if (f.multiple) {
          const values = Array.isArray(v) ? v : [];
  
          if (values.length < 1) {
            return `Falta responder: ${f.label}`;
          }
        } else if (
          v === null ||
          v === undefined ||
          String(v).trim() === ""
        ) {
          return `Falta responder: ${f.label}`;
        }
      }
  
      if (
        f.type === "select" ||
        f.type === "list" ||
        f.type === "radio"
      ) {
        const opts = Array.isArray(f.options)
          ? f.options
          : [];
  
        if (f.multiple) {
          const values = Array.isArray(v) ? v : [];
  
          const invalidValue = values.find(
            (value) => !opts.includes(value)
          );
  
          if (invalidValue !== undefined) {
            return `Selecciona opciones válidas para: ${f.label}`;
          }
        } else if (
          f.required &&
          opts.length >= 1 &&
          !opts.includes(v)
        ) {
          return `Selecciona una opción válida para: ${f.label}`;
        }
      }
    }
  
    return null;
  };

  const toFriendlyMessage = (e2) => {
    if (e2?.response?.data?.message) {
      return String(e2.response.data.message);
    }

    if (e2?.message) {
      return String(e2.message);
    }

    return isEditing
      ? "Error actualizando respuestas."
      : "Error guardando respuestas.";
  };

  const shouldQueueOffline = (e2) => {
    if (!navigator.onLine) return true;

    const msg = String(e2?.message || "").toLowerCase();
    if (msg.includes("failed to fetch")) return true;
    if (msg.includes("network error")) return true;
    if (msg.includes("timeout")) return true;

    const code = String(e2?.code || "").toUpperCase();
    if (code === "ERR_NETWORK" || code === "ECONNABORTED") return true;

    const status = e2?.status || e2?.response?.status;
    if (status && Number(status) >= 500) return true;

    return false;
  };

  const resetForm = () => {
    setAnswers(buildInitAnswers(null, null));
  };

  const updateSubmission = async () => {
    if (!form?.id || !editSubmissionId) {
      throw new Error("No se encontró el registro a editar.");
    }

    const response = await fetch(
      `/api/forms/${form.id}/submissions/${editSubmissionId}`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          answers,
        }),
      }
    );

    let data = null;
    const contentType = response.headers.get("content-type") || "";

    if (contentType.includes("application/json")) {
      data = await response.json();
    } else {
      const text = await response.text();
      data = text ? { message: text } : null;
    }

    if (response.status === 401) {
      throw new Error("401 No autorizado");
    }

    if (response.status === 403) {
      throw new Error(
        data?.message ||
          "No cuentas con los permisos necesarios para editar este registro. Contacta a tu administrador o al equipo de Sistemas."
      );
    }

    if (!response.ok) {
      throw new Error(data?.message || "No se pudo actualizar el registro.");
    }

    return data;
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    if (readOnly) return;

    setMsg("");

    if (!form?.id) {
      setMsg("Formulario inválido (sin id).");
      return;
    }

    if (
      !isEditing &&
      !captureFormVersionId
    ) {
      setMsg(
        "No se pudo identificar la versión del formulario. Vuelve al listado e inicia nuevamente la captura."
      );
      return;
    }

    if (isEditing && !canEdit) {
      setMsg(
        "No cuentas con los permisos necesarios para editar este registro. Contacta a tu administrador o al equipo de Sistemas."
      );
      return;
    }

    if (!isEditing && !canSubmit) {
      setMsg(
        "No cuentas con los permisos necesarios para guardar este registro. Contacta a tu administrador o al equipo de Sistemas."
      );
      return;
    }

    const err = validate();
    if (err) {
      setMsg(err);
      return;
    }

    setSaving(true);

    const offlineCreatedAt =
      new Date().toISOString();

    const offlinePayload = {
      form_id: form.id,
    
      form_version_id:
        captureFormVersionId,
    
      answers: {
        ...answers,
      },
    
      offline_created_at:
        offlineCreatedAt,
    
      meta: {
        offline_capable: true,
        user_agent:
          navigator.userAgent,
        created_at:
          offlineCreatedAt,
      },
    };

    try {
      if (isEditing) {
        await updateSubmission();

        setMsg("");

        openSuccessModalAndBack(
          "Registro actualizado correctamente",
          "Las respuestas se actualizaron exitosamente."
        );
      } else {
        await apiPost(
          `/forms/${form.id}/submit`,
          {
            form_version_id:
              captureFormVersionId,
        
            answers,
          }
        );
        
        await clearCurrentDraft();
        
        setMsg("");
        resetForm();

        openSuccessModalAndBack(
          "Registro creado correctamente",
          "Las respuestas se guardaron exitosamente."
        );
      }
    } catch (e2) {
      if (String(e2?.message || "").includes("401")) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        window.location.href = "/login";
        return;
      }

      if (!isEditing && shouldQueueOffline(e2)) {
        try {
          const localUuid = await enqueue("form_submission", offlinePayload);

          await saveOfflineSubmission(
            currentUserId,
            form,
            answers,
            localUuid,
            offlineCreatedAt
          );

          await clearCurrentDraft();

          window.dispatchEvent(
            new CustomEvent("offline-record-saved", {
              detail: {
                formId: form.id,
                localUuid,
              },
            })
          );
          
          setMsg("");
          resetForm();
          
          // NO sincronizamos aquí
          // el autosync se encarga
          
          openSuccessModalAndBack(
            "Registro guardado offline",
            "Se guardó en el dispositivo y se sincronizará automáticamente cuando vuelva la conexión."
          );
        } catch (qe) {
          setMsg("Error guardando offline: " + String(qe?.message || qe));
        }
      } else {
        setMsg(toFriendlyMessage(e2));
      }
    } finally {
      setSaving(false);
    }
  };

  const sharedProps = {
    form,
    fields,
    answers,
    setVal,
    saving,
    isOnline,
    msg,
    onBack: handleBack,
    onSubmit,
    setMsg,
    readOnly,
    responseMeta,
    isEditing,
    editSubmissionId,
  };

  if (versionedLayoutMissing) {
    return (
      <div
        style={{
          maxWidth: 760,
          margin: "40px auto",
          padding: 24,
          border: "1px solid #fecaca",
          borderRadius: 16,
          background: "#fff",
          color: "#991b1b",
        }}
      >
        <h3
          style={{
            margin: "0 0 10px",
          }}
        >
          No se pudo cargar esta versión del formulario
        </h3>
  
        <p
          style={{
            margin: 0,
            lineHeight: 1.6,
          }}
        >
          El layout{" "}
          <strong>
            {layout}
          </strong>{" "}
          no está registrado en la aplicación.
          Contacta al equipo de Sistemas.
        </p>
  
        <button
          type="button"
          onClick={handleBack}
          style={{
            marginTop: 18,
            padding: "10px 18px",
            borderRadius: 10,
            border: "1px solid #cbd5e1",
            background: "#fff",
            cursor: "pointer",
            fontWeight: 700,
          }}
        >
          Volver
        </button>
      </div>
    );
  }

  const renderLayout = () => {
    const LayoutComponent =
      resolveFormLayoutComponent(
        layout
      );
  
    return (
      <LayoutComponent
        {...sharedProps}
      />
    );
  };

  return (
    <>
      {renderLayout()}

      {successModal.open ? (
        <div
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(15, 23, 42, 0.45)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
            zIndex: 3000,
          }}
        >
          <div
            style={{
              width: "100%",
              maxWidth: 420,
              background: "#fff",
              borderRadius: 18,
              border: "1px solid #dbe4ee",
              boxShadow: "0 20px 50px rgba(15,23,42,0.18)",
              padding: 24,
              textAlign: "center",
            }}
          >
            <div
              style={{
                width: 72,
                height: 72,
                margin: "0 auto 16px",
                borderRadius: 999,
                background: "#ecfdf5",
                border: "1px solid #86efac",
                display: "grid",
                placeItems: "center",
              }}
            >
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                <path
                  d="M20 7 9 18l-5-5"
                  stroke="#16a34a"
                  strokeWidth="2.5"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </div>

            <h3
              style={{
                margin: 0,
                fontSize: 22,
                lineHeight: 1.2,
                color: "#0f172a",
              }}
            >
              {successModal.title}
            </h3>

            <p
              style={{
                margin: "10px 0 0",
                fontSize: 14,
                lineHeight: 1.6,
                color: "#64748b",
              }}
            >
              {successModal.text}
            </p>

            <div
              style={{
                marginTop: 18,
                fontSize: 12,
                color: "#94a3b8",
              }}
            >
              Esta ventana se cerrará automáticamente en 10 segundos.
            </div>

            <div
              style={{
                marginTop: 20,
                display: "flex",
                justifyContent: "center",
              }}
            >
              <button
                type="button"
                onClick={async () => {
                  closeSuccessModal();

                  if (typeof onSaved === "function") {
                    await onSaved();
                    return;
                  }

                  if (typeof onBack === "function") {
                    onBack();
                  }
                }}
                style={{
                  borderRadius: 12,
                  border: "1px solid #c7d2fe",
                  background: "#2563eb",
                  color: "#fff",
                  padding: "11px 22px",
                  cursor: "pointer",
                  fontWeight: 800,
                  fontSize: 14,
                  boxShadow: "0 8px 18px rgba(37,99,235,0.18)",
                }}
              >
                OK
              </button>
            </div>
          </div>
        </div>
      ) : null}
    </>
  );
}