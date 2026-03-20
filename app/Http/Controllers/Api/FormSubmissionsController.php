<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormSubmissionsController extends Controller
{
    public function store(Request $request, Form $form)
    {
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }
    
        // usuario normal solo puede responder PUBLICADOS
        if (!$user->hasRole('Administrador')) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }
    
            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }
    
        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);
    
        $cleanAnswers = $this->validateAndCleanAnswers(
            form: $form,
            userId: $user?->id,
            answers: $data['answers']
        );
    
        if ($cleanAnswers instanceof \Illuminate\Http\JsonResponse) {
            return $cleanAnswers;
        }

        $submission = DB::transaction(function () use ($form, $user, $cleanAnswers) {
            $consecutive = $this->getNextAvailableConsecutive((int) $form->id);

            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'consecutive' => $consecutive,
                'user_id' => $user?->id,
                'answers' => $cleanAnswers,
            ]);

            FormSubmissionHistory::create([
                'form_submission_id' => $submission->id,
                'form_id' => $form->id,
                'user_id' => $user?->id,
                'action' => 'created',
                'snapshot' => $cleanAnswers,
                'changes' => null,
            ]);

            return $submission;
        });
    
        return response()->json([
            'ok' => true,
            'message' => 'Registro creado correctamente.',
            'submission' => $submission,
        ], 201);
    }

    public function index(Request $request, Form $form)
    {
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }
    
        if (!$user->hasRole('Administrador')) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }
    
            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }
    
        $query = FormSubmission::query()
            ->with(['user:id,name'])
            ->where('form_id', $form->id);
    
        if (!$user->hasRole('Administrador')) {
            $unidadIds = $user->unidadesServicio()->pluck('unidades_servicio.id');
    
            if ($unidadIds->isEmpty()) {
                return response()->json(['submissions' => []]);
            }
    
            $query->whereHas('user.unidadesServicio', function ($q) use ($unidadIds) {
                $q->whereIn('unidades_servicio.id', $unidadIds);
            });
        }
    
        $subs = $query
            ->orderByDesc('consecutive')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'form_id', 'consecutive', 'user_id', 'answers', 'created_at'])
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'form_id' => $sub->form_id,
                    'consecutive' => $sub->consecutive,
                    'user_id' => $sub->user_id,
                    'user_name' => $sub->user?->name,
                    'answers' => $sub->answers,
                    'created_at' => $sub->created_at,
                ];
            })
            ->values();
    
        return response()->json(['submissions' => $subs]);
    }

    public function update(Request $request, Form $form, FormSubmission $submission)
    {
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }
    
        if ((int) $submission->form_id !== (int) $form->id) {
            return response()->json(['message' => 'Registro no encontrado para este formulario.'], 404);
        }
    
        // usuario normal solo puede editar PUBLICADOS
        if (!$user->hasRole('Administrador')) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }
    
            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }
    
        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);
    
        $previousAnswers = is_array($submission->answers) ? $submission->answers : [];
    
        $cleanAnswers = $this->validateAndCleanAnswers(
            form: $form,
            userId: $user?->id,
            answers: $data['answers'],
            previousAnswers: $previousAnswers
        );
    
        if ($cleanAnswers instanceof \Illuminate\Http\JsonResponse) {
            return $cleanAnswers;
        }
    
        $changes = $this->buildSubmissionChanges(
            form: $form,
            oldAnswers: $previousAnswers,
            newAnswers: $cleanAnswers
        );
    
        $submission->answers = $cleanAnswers;
        $submission->save();
    
        if (!empty($changes)) {
            FormSubmissionHistory::create([
                'form_submission_id' => $submission->id,
                'form_id' => $form->id,
                'user_id' => $user?->id,
                'action' => 'updated',
                'snapshot' => $cleanAnswers,
                'changes' => $changes,
            ]);
        }
    
        return response()->json([
            'ok' => true,
            'message' => 'Registro actualizado correctamente.',
            'submission' => $submission,
        ]);
    }

    /**
     * Determina si el usuario puede acceder al formulario.
     * - Si no hay asignaciones, cualquiera puede acceder
     * - Si hay asignaciones, solo usuarios asignados
     */
    private function userCanAccessForm(int $userId, Form $form): bool
    {
        $hasAssignments = $form->assignedUsers()->exists();

        if (!$hasAssignments) {
            return true;
        }

        return $form->assignedUsers()
            ->where('users.id', $userId)
            ->exists();
    }

    /**
     * Obtiene el primer consecutivo libre por formulario.
     * Ejemplo:
     * existentes = [1,2,3,5,6] => devuelve 4
     * existentes = [2,3] => devuelve 1
     * existentes = [] => devuelve 1
     */
    private function getNextAvailableConsecutive(int $formId): int
    {
        $usedNumbers = FormSubmission::where('form_id', $formId)
            ->whereNotNull('consecutive')
            ->orderBy('consecutive')
            ->lockForUpdate()
            ->pluck('consecutive')
            ->map(fn ($n) => (int) $n)
            ->values()
            ->all();

        $next = 1;

        foreach ($usedNumbers as $number) {
            if ($number < $next) {
                continue;
            }

            if ($number > $next) {
                return $next;
            }

            $next++;
        }

        return $next;
    }

    /**
     * Valida y limpia respuestas del formulario.
     * Retorna array limpio o JsonResponse en caso de error.
     */
    private function validateAndCleanAnswers(
        Form $form,
        ?int $userId,
        array $answers,
        array $previousAnswers = []
    ) {
        // payload estándar esperado
        $fields = [];
        if (
            is_array($form->payload) &&
            isset($form->payload['fields']) &&
            is_array($form->payload['fields'])
        ) {
            $fields = $form->payload['fields'];
        }

        $formCodeKey = data_get($form->payload, '_code_key');

        // Tipos que NO son de entrada
        $nonInputTypes = ['static_text', 'separator', 'fixed_image', 'fixed_file'];

        // Tipos choice
        $choiceTypes = ['select', 'radio', 'list'];

        // Tipos soportados
        $inputTypes = array_merge(
            [
                'text',
                'textarea',
                'number',
                'date',
                'datetime',
                'checkbox',
                'contact',
                'address',
                'table',
                'photo',
                'file',
                'signature',
            ],
            $choiceTypes
        );

        $cleanAnswers = [];

        foreach ($fields as $f) {
            if (!is_array($f)) {
                continue;
            }

            $id = $f['id'] ?? null;
            if (!$id) {
                continue;
            }

            $label = $f['label'] ?? $id;
            $type = (string) ($f['type'] ?? 'text');
            $required = (bool) ($f['required'] ?? false);

            if (in_array($type, $nonInputTypes, true)) {
                continue;
            }

            if (!in_array($type, $inputTypes, true)) {
                $type = 'text';
            }

            $val = $answers[$id] ?? null;

            // checkbox
            if ($type === 'checkbox') {
                if ($val === '1' || $val === 1) $val = true;
                if ($val === '0' || $val === 0) $val = false;
                if ($val === 'true') $val = true;
                if ($val === 'false') $val = false;

                if ($val === null) $val = false;

                $val = (bool) $val;

                if ($required && $val !== true) {
                    return response()->json(['message' => "Debes aceptar: {$label}"], 422);
                }

                $cleanAnswers[$id] = $val;
                continue;
            }

            // required general
            if ($required) {
                $isEmpty = is_null($val) || (is_string($val) && trim($val) === '');
                if (is_array($val) && count($val) === 0) {
                    $isEmpty = true;
                }

                if ($isEmpty) {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }
            }

            $isEmpty = is_null($val) || (is_string($val) && trim($val) === '');
            if (is_array($val) && count($val) === 0) {
                $isEmpty = true;
            }

            if ($isEmpty) {
                continue;
            }

            // number
            if ($type === 'number') {
                if (!is_numeric($val)) {
                    return response()->json(['message' => "El campo {$label} debe ser numérico."], 422);
                }

                $cleanAnswers[$id] = 0 + $val;
                continue;
            }

            // date
            if ($type === 'date') {
                $ts = strtotime((string) $val);
                if ($ts === false) {
                    return response()->json(['message' => "El campo {$label} debe ser una fecha válida."], 422);
                }

                $cleanAnswers[$id] = date('Y-m-d', $ts);
                continue;
            }

            // datetime
            if ($type === 'datetime') {
                $ts = strtotime((string) $val);
                if ($ts === false) {
                    return response()->json(['message' => "El campo {$label} debe ser fecha y hora válida."], 422);
                }

                $cleanAnswers[$id] = date('Y-m-d H:i:s', $ts);
                continue;
            }

            // choice
            if (in_array($type, $choiceTypes, true)) {
                $opts = $f['options'] ?? [];
                if (!is_array($opts)) {
                    $opts = [];
                }

                $valStr = trim((string) $val);

                if ($required && $valStr === '') {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }

                if ($valStr !== '' && count($opts) > 0 && !in_array($valStr, $opts, true)) {
                    return response()->json(['message' => "El campo {$label} tiene una opción inválida."], 422);
                }

                $cleanAnswers[$id] = $valStr;
                continue;
            }

            // contact / address
            if ($type === 'contact' || $type === 'address') {
                if (is_array($val)) {
                    $cleanAnswers[$id] = $val;
                } else {
                    $cleanAnswers[$id] = is_string($val) ? trim($val) : (string) $val;
                }
                continue;
            }

            // table
            if ($type === 'table') {
                if (!is_array($val)) {
                    return response()->json([
                        'message' => "El campo {$label} (tabla) debe ser un arreglo de filas."
                    ], 422);
                }

                $rowSchema = $f['row_schema'] ?? [];
                if (!is_array($rowSchema)) {
                    $rowSchema = [];
                }

                $allowedKeys = array_values(array_filter(array_map(function ($col) {
                    return is_array($col) ? ($col['id'] ?? null) : null;
                }, $rowSchema)));

                $rows = array_values(array_filter($val, function ($row) {
                    if (is_array($row)) {
                        return count($row) > 0;
                    }

                    return $row !== null && $row !== '';
                }));

                if ($required && count($rows) < 1) {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }

                $normalizedRows = [];

                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        return response()->json([
                            'message' => "El campo {$label} (tabla) tiene filas inválidas."
                        ], 422);
                    }

                    $cleanRow = [];

                    if (count($allowedKeys) > 0) {
                        foreach ($allowedKeys as $key) {
                            $cleanRow[$key] = $row[$key] ?? '';
                        }
                    } else {
                        $cleanRow = $row;
                    }

                    $normalizedRows[] = $cleanRow;
                }

                $cleanAnswers[$id] = $normalizedRows;
                continue;
            }

            // photo / file / signature
            if (in_array($type, ['photo', 'file', 'signature'], true)) {
                if ($type === 'signature') {
                    if (is_array($val)) {
                        $cleanAnswers[$id] = $val;
                        continue;
                    }

                    $v = is_string($val) ? trim($val) : (string) $val;

                    if ($required && $v === '') {
                        return response()->json(['message' => "Falta responder: {$label}"], 422);
                    }

                    if ($v === '') {
                        continue;
                    }

                    // Si ya es una ruta existente, conservarla
                    if (!str_starts_with($v, 'data:image/')) {
                        $cleanAnswers[$id] = $v;
                        continue;
                    }

                    // Guardar PNG físico para formularios específicos
                    if (str_starts_with($v, 'data:image/')) {
                        $storedPath = null;
                    
                        if ($formCodeKey === 'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil') {
                            $storedPath = $this->storeSignatureForChecklistHerramienta($v, $userId, $id);
                        }
                    
                        if ($formCodeKey === 'sst_pop_ta_07_fo_01_inspeccion_de_compresor') {
                            $storedPath = $this->storeSignatureForInspeccionCompresor($v, $userId, $id);
                        }
                    
                        if (
                            in_array($formCodeKey, [
                                'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil',
                                'sst_pop_ta_07_fo_01_inspeccion_de_compresor',
                            ], true)
                        ) {
                            if (!$storedPath) {
                                return response()->json([
                                    'message' => "No se pudo guardar la firma del campo {$label}."
                                ], 422);
                            }
                    
                            $cleanAnswers[$id] = $storedPath;
                            continue;
                        }
                    }

                    // fallback
                    $cleanAnswers[$id] = $v;
                    continue;
                }

                if (is_array($val)) {
                    $cleanAnswers[$id] = $val;
                } else {
                    $v = is_string($val) ? trim($val) : (string) $val;

                    if ($required && $v === '') {
                        return response()->json(['message' => "Falta responder: {$label}"], 422);
                    }

                    $cleanAnswers[$id] = $v;
                }

                continue;
            }

            // text / textarea / fallback
            $cleanAnswers[$id] = is_string($val) ? trim($val) : (string) $val;
        }

        // En update, conservar respuestas previas de campos que ya no vengan si existen
        foreach ($previousAnswers as $prevKey => $prevValue) {
            if (!array_key_exists($prevKey, $cleanAnswers) && !array_key_exists($prevKey, $answers)) {
                $cleanAnswers[$prevKey] = $prevValue;
            }
        }

        return $cleanAnswers;
    }

    private function buildSubmissionChanges(Form $form, array $oldAnswers, array $newAnswers): array
    {
        $changes = [];
    
        $fields = [];
        if (
            is_array($form->payload) &&
            isset($form->payload['fields']) &&
            is_array($form->payload['fields'])
        ) {
            $fields = $form->payload['fields'];
        }
    
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
    
            $fieldId = $field['id'] ?? null;
            if (!$fieldId) {
                continue;
            }
    
            $type = (string) ($field['type'] ?? 'text');
            $label = $field['label'] ?? $fieldId;
    
            $nonInputTypes = ['static_text', 'separator', 'fixed_image', 'fixed_file'];
            if (in_array($type, $nonInputTypes, true)) {
                continue;
            }
    
            $oldValue = $oldAnswers[$fieldId] ?? null;
            $newValue = $newAnswers[$fieldId] ?? null;
    
            if (!$this->valuesAreDifferent($oldValue, $newValue)) {
                continue;
            }
    
            $changes[] = [
                'field' => $fieldId,
                'label' => $label,
                'type' => $type,
                'old_value' => $this->normalizeHistoryValue($oldValue),
                'new_value' => $this->normalizeHistoryValue($newValue),
            ];
        }
    
        $knownFieldIds = collect($fields)
            ->map(fn ($f) => is_array($f) ? ($f['id'] ?? null) : null)
            ->filter()
            ->values()
            ->all();
    
        $allKeys = array_unique(array_merge(array_keys($oldAnswers), array_keys($newAnswers)));
    
        foreach ($allKeys as $extraKey) {
            if (in_array($extraKey, $knownFieldIds, true)) {
                continue;
            }
    
            $oldValue = $oldAnswers[$extraKey] ?? null;
            $newValue = $newAnswers[$extraKey] ?? null;
    
            if (!$this->valuesAreDifferent($oldValue, $newValue)) {
                continue;
            }
    
            $changes[] = [
                'field' => $extraKey,
                'label' => $extraKey,
                'type' => 'unknown',
                'old_value' => $this->normalizeHistoryValue($oldValue),
                'new_value' => $this->normalizeHistoryValue($newValue),
            ];
        }
    
        return array_values($changes);
    }
    
    private function valuesAreDifferent($oldValue, $newValue): bool
    {
        return json_encode($this->normalizeForComparison($oldValue), JSON_UNESCAPED_UNICODE) !==
            json_encode($this->normalizeForComparison($newValue), JSON_UNESCAPED_UNICODE);
    }
    
    private function normalizeForComparison($value)
    {
        if (is_array($value)) {
            if ($this->isAssoc($value)) {
                ksort($value);
    
                $normalized = [];
                foreach ($value as $key => $item) {
                    $normalized[$key] = $this->normalizeForComparison($item);
                }
    
                return $normalized;
            }
    
            return array_map(fn ($item) => $this->normalizeForComparison($item), $value);
        }
    
        if (is_string($value)) {
            return trim($value);
        }
    
        return $value;
    }
    
    private function normalizeHistoryValue($value)
    {
        if (is_array($value)) {
            return $value;
        }
    
        if (is_string($value)) {
            return trim($value);
        }
    
        return $value;
    }
    
    private function isAssoc(array $array): bool
    {
        if ([] === $array) {
            return false;
        }
    
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public function history(Request $request, Form $form, FormSubmission $submission)
    {
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }
    
        if ((int) $submission->form_id !== (int) $form->id) {
            return response()->json(['message' => 'Registro no encontrado para este formulario.'], 404);
        }
    
        if (!$user->hasRole('Administrador')) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }
    
            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }
    
        $history = $submission->histories()
            ->with(['user:id,name'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user?->name,
                    'snapshot' => $item->snapshot,
                    'changes' => $item->changes,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            })
            ->values();
    
        return response()->json([
            'ok' => true,
            'submission_id' => $submission->id,
            'history' => $history,
        ]);
    }

    /**
     * Guarda la firma como PNG físico para el checklist de herramienta.
     */
    private function storeSignatureForChecklistHerramienta(string $dataUrl, ?int $userId, string $fieldId): ?string
    {
        if (!preg_match('/^data:image\/png;base64,/', $dataUrl)) {
            return null;
        }

        $base64 = preg_replace('/^data:image\/png;base64,/', '', $dataUrl);
        $base64 = str_replace(' ', '+', $base64);

        $binary = base64_decode($base64, true);

        if ($binary === false) {
            return null;
        }

        $directory = 'forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil';

        $fileName = 'firma_' . $fieldId . '_u' . ($userId ?: 'guest') . '_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.png';

        $relativePath = $directory . '/' . $fileName;

        Storage::disk('public')->put($relativePath, $binary);

        return $relativePath;
    }

    /**
     * Guarda la firma como PNG físico para la inspección de compresor.
     */
    private function storeSignatureForInspeccionCompresor(string $dataUrl, ?int $userId, string $fieldId): ?string
    {
        if (!preg_match('/^data:image\/png;base64,/', $dataUrl)) {
            return null;
        }
    
        $base64 = preg_replace('/^data:image\/png;base64,/', '', $dataUrl);
        $base64 = str_replace(' ', '+', $base64);
    
        $binary = base64_decode($base64, true);
    
        if ($binary === false) {
            return null;
        }
    
        $baseDirectory = 'forms/signatures/SSTPOPTA07FO01_InspeccionCompresor';
    
        $directory = match ($fieldId) {
            'firma_responsable_seguridad' => $baseDirectory . '/Responsable_Seguridad',
            default => $baseDirectory,
        };
    
        $fileName = 'firma_' . $fieldId . '_u' . ($userId ?: 'guest') . '_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.png';
    
        $relativePath = $directory . '/' . $fileName;
    
        Storage::disk('public')->put($relativePath, $binary);
    
        return $relativePath;
    }

    public function destroy(Request $request, Form $form, FormSubmission $submission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        if ((int) $submission->form_id !== (int) $form->id) {
            return response()->json(['message' => 'Registro no encontrado para este formulario.'], 404);
        }

        if (!$user->hasRole('Administrador')) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }

            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }

        $submission->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Registro eliminado correctamente.',
        ]);
    }
}