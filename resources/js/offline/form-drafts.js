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
  answers = {}
) {
  const normalizedUserId =
    normalizeId(userId);

  const normalizedFormId =
    normalizeId(formId);

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
        await db.form_drafts.update(
          existing.id,
          {
            answers:
              answers &&
              typeof answers === "object"
                ? answers
                : {},

            updated_at: timestamp,
          }
        );

        return {
          ...existing,

          answers:
            answers &&
            typeof answers === "object"
              ? answers
              : {},

          updated_at: timestamp,
        };
      }

      const draft = {
        user_id:
          normalizedUserId,

        form_id:
          normalizedFormId,

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