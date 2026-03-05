<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionsController extends Controller
{
    public function store(Request $request, Form $form)
    {
        $user = $request->user();

        // usuario normal solo puede responder PUBLICADOS
        if (!$user->hasRole('Administrador') && $form->status !== 'PUBLICADO') {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $answers = $data['answers'];

        // payload estándar esperado
        $fields = [];
        if (is_array($form->payload) && isset($form->payload['fields']) && is_array($form->payload['fields'])) {
            $fields = $form->payload['fields'];
        }

        // Tipos que NO son de entrada (no deben validar required ni guardarse)
        $nonInputTypes = ['static_text', 'separator', 'fixed_image', 'fixed_file'];

        // Tipos "choice" (validan contra options)
        $choiceTypes = ['select', 'list', 'radio'];

        // Tipos de entrada soportados
        $inputTypes = array_merge(['text', 'textarea', 'number', 'date', 'datetime', 'checkbox'], $choiceTypes);

        // Aquí vamos construyendo lo que sí se va a guardar (solo campos input)
        $cleanAnswers = [];

        // ---- validación por field ----
        foreach ($fields as $f) {
            if (!is_array($f)) continue;

            $id = $f['id'] ?? null;
            if (!$id) continue;

            $label = $f['label'] ?? $id;
            $type = (string) ($f['type'] ?? 'text');
            $required = (bool) ($f['required'] ?? false);

            // Si el field es NO-input, lo ignoramos totalmente
            if (in_array($type, $nonInputTypes, true)) {
                continue;
            }

            // Si llega un type desconocido, lo tratamos como text (para no romper)
            if (!in_array($type, $inputTypes, true)) {
                $type = 'text';
            }

            $val = $answers[$id] ?? null;

            // --- Checkbox ---
            if ($type === 'checkbox') {
                // Normaliza checkbox a boolean (si viene "1"/1/"0"/0/true/false/"true"/"false")
                if ($val === '1' || $val === 1) $val = true;
                if ($val === '0' || $val === 0) $val = false;
                if ($val === 'true') $val = true;
                if ($val === 'false') $val = false;

                // Si viene null y no requerido, lo dejamos false
                if ($val === null) $val = false;

                $val = (bool) $val;

                // Checkbox requerido: debe ser TRUE
                if ($required && $val !== true) {
                    return response()->json(['message' => "Debes aceptar: {$label}"], 422);
                }

                $cleanAnswers[$id] = $val;
                continue;
            }

            // --- required (para no-checkbox) ---
            if ($required) {
                $isEmpty = is_null($val) || (is_string($val) && trim($val) === '');
                if ($isEmpty) {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }
            }

            // Si viene vacío y no es requerido, no validamos tipo y NO guardamos nada
            $isEmpty = is_null($val) || (is_string($val) && trim($val) === '');
            if ($isEmpty) {
                continue;
            }

            // --- Validación por tipo ---
            if ($type === 'number') {
                if (!is_numeric($val)) {
                    return response()->json(['message' => "El campo {$label} debe ser numérico."], 422);
                }
                $cleanAnswers[$id] = 0 + $val;
                continue;
            }

            if ($type === 'date') {
                $ts = strtotime((string) $val);
                if ($ts === false) {
                    return response()->json(['message' => "El campo {$label} debe ser una fecha válida."], 422);
                }
                $cleanAnswers[$id] = date('Y-m-d', $ts);
                continue;
            }

            if ($type === 'datetime') {
                $ts = strtotime((string) $val);
                if ($ts === false) {
                    return response()->json(['message' => "El campo {$label} debe ser fecha y hora válida."], 422);
                }
                $cleanAnswers[$id] = date('Y-m-d H:i:s', $ts);
                continue;
            }

            if (in_array($type, $choiceTypes, true)) {
                $opts = $f['options'] ?? [];
                if (!is_array($opts)) $opts = [];

                $valStr = trim((string) $val);

                if ($required && $valStr === '') {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }

                // Si hay opciones definidas, validar que exista en la lista
                if ($valStr !== '' && count($opts) > 0 && !in_array($valStr, $opts, true)) {
                    return response()->json(['message' => "El campo {$label} tiene una opción inválida."], 422);
                }

                $cleanAnswers[$id] = $valStr;
                continue;
            }

            // text / textarea / fallback
            $cleanAnswers[$id] = is_string($val) ? trim($val) : (string) $val;
        }

        // Opcional: si quieres bloquear submissions vacías:
        // if (count($cleanAnswers) === 0) {
        //     return response()->json(['message' => "No hay respuestas para guardar."], 422);
        // }

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user?->id,
            'answers' => $cleanAnswers,
        ]);

        return response()->json(['ok' => true, 'submission' => $submission], 201);
    }

    public function index(Request $request, Form $form)
    {
        $user = $request->user();

        // Usuario normal: solo puede ver submissions de formularios PUBLICADOS
        if (!$user->hasRole('Administrador') && $form->status !== 'PUBLICADO') {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $subs = FormSubmission::query()
            ->where('form_id', $form->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'form_id', 'user_id', 'answers', 'created_at']);

        return response()->json(['submissions' => $subs]);
    }
}