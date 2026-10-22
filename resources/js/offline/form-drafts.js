import { db } from "./db";

function nowIso() {
  return new Date().toISOString();
}

function normalizeId(value) {
  const id = Number(value || 0);

  return Number.isFinite(id)
    ? id
    : 0;
}

/*
|--------------------------------------------------------------------------
| Obtener borrador
|--------------------------------------------------------------------------
*/

export async function getFormDraft(
  userId,
  formId
) {
  const normalizedUserId =
    normalizeId(userId);

  const normalizedFormId =
    normalizeId(formId);

  if (
    !normalizedUserId ||
    !normalizedFormId
  ) {
    return null;
  }

  const draft =
    await db.form_drafts
      .where("[user_id+form_id]")
      .equals([
        normalizedUserId,
        normalizedFormId,
      ])
      .first();

  return draft || null;
}

/*
|--------------------------------------------------------------------------
| Guardar / actualizar borrador
|--------------------------------------------------------------------------
|
| Si ya existe:
| - Conserva created_at.
| - Actualiza answers.
| - Actualiza updated_at.
|
| Si no existe:
| - Lo crea.
| - created_at representa cuándo comenzó la captura.
|
*/

export async function saveFormDraft(
  userId,
  formId,
  answers = {},
  formVersionId = null
) {
  const normalizedUserId =
    normalizeId(userId);

  const normalizedFormId =
    normalizeId(formId);

  const normalizedFormVersionId =
    normalizeId(formVersionId);

  if (!normalizedUserId) {
    throw new Error(
      "No se pudo identificar al usuario del borrador."
    );
  }

  if (!normalizedFormId) {
    throw new Error(
      "No se pudo identificar el formulario del borrador."
    );
  }

  if (!normalizedFormVersionId) {
    throw new Error(
      "No se pudo identificar la versión del formulario del borrador."
    );
  }

  const timestamp = nowIso();

  return db.transaction(
    "rw",
    db.form_drafts,
    async () => {
      const existing =
        await db.form_drafts
          .where("[user_id+form_id]")
          .equals([
            normalizedUserId,
            normalizedFormId,
          ])
          .first();

      if (existing?.id) {
        /*
         * Si el borrador ya tenía una versión,
         * esa versión queda congelada.
         *
         * Si es un borrador antiguo que todavía
         * no tenía form_version_id, se le asigna
         * la versión actual de la captura.
         */
        const storedFormVersionId =
          normalizeId(
            existing.form_version_id
          ) ||
          normalizedFormVersionId;

        await db.form_drafts.update(
          existing.id,
          {
            form_version_id:
              storedFormVersionId,

            answers:
              answers &&
              typeof answers === "object"
                ? answers
                : {},

            updated_at:
              timestamp,
          }
        );

        return {
          ...existing,

          form_version_id:
            storedFormVersionId,

          answers:
            answers &&
            typeof answers === "object"
              ? answers
              : {},

          updated_at:
            timestamp,
        };
      }

      const draft = {
        user_id:
          normalizedUserId,

        form_id:
          normalizedFormId,

        form_version_id:
          normalizedFormVersionId,

        answers:
          answers &&
          typeof answers === "object"
            ? answers
            : {},

        created_at:
          timestamp,

        updated_at:
          timestamp,
      };

      const id =
        await db.form_drafts.add(
          draft
        );

      return {
        id,
        ...draft,
      };
    }
  );
}

/*
|--------------------------------------------------------------------------
| Eliminar borrador
|--------------------------------------------------------------------------
*/

export async function deleteFormDraft(
  userId,
  formId
) {
  const normalizedUserId =
    normalizeId(userId);

  const normalizedFormId =
    normalizeId(formId);

  if (
    !normalizedUserId ||
    !normalizedFormId
  ) {
    return;
  }

  const draft =
    await getFormDraft(
      normalizedUserId,
      normalizedFormId
    );

  if (!draft?.id) {
    return;
  }

  await db.form_drafts.delete(
    draft.id
  );
}