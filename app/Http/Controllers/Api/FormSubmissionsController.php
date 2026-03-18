<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
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

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user?->id,
            'answers' => $cleanAnswers,
        ]);

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
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'form_id', 'user_id', 'answers', 'created_at'])
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'form_id' => $sub->form_id,
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

        $cleanAnswers = $this->validateAndCleanAnswers(
            form: $form,
            userId: $user?->id,
            answers: $data['answers'],
            previousAnswers: is_array($submission->answers) ? $submission->answers : []
        );

        if ($cleanAnswers instanceof \Illuminate\Http\JsonResponse) {
            return $cleanAnswers;
        }

        $submission->answers = $cleanAnswers;
        $submission->save();

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

                    // Guardar PNG físico solo para este formulario
                    if (
                        $formCodeKey === 'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil' &&
                        str_starts_with($v, 'data:image/')
                    ) {
                        $storedPath = $this->storeSignatureForChecklistHerramienta($v, $userId, $id);

                        if (!$storedPath) {
                            return response()->json([
                                'message' => "No se pudo guardar la firma del campo {$label}."
                            ], 422);
                        }

                        $cleanAnswers[$id] = $storedPath;
                        continue;
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