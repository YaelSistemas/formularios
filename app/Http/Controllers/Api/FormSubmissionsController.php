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

        $formCodeKey = data_get($form->payload, '_code_key');

        // Tipos que NO son de entrada
        $nonInputTypes = ['static_text', 'separator', 'fixed_image', 'fixed_file'];

        // Tipos choice
        $choiceTypes = ['select', 'radio', 'list'];

        // Tipos soportados
        $inputTypes = array_merge(
            ['text', 'textarea', 'number', 'date', 'datetime', 'checkbox', 'contact', 'address', 'table', 'photo', 'file', 'signature'],
            $choiceTypes
        );

        $cleanAnswers = [];

        foreach ($fields as $f) {
            if (!is_array($f)) continue;

            $id = $f['id'] ?? null;
            if (!$id) continue;

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
                if (is_array($val) && count($val) === 0) $isEmpty = true;

                if ($isEmpty) {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }
            }

            $isEmpty = is_null($val) || (is_string($val) && trim($val) === '');
            if (is_array($val) && count($val) === 0) $isEmpty = true;

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
                if (!is_array($opts)) $opts = [];

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
                    return response()->json(['message' => "El campo {$label} (tabla) debe ser un arreglo de filas."], 422);
                }

                $cols = $f['columns'] ?? [];
                if (!is_array($cols)) $cols = [];

                $rows = array_values(array_filter($val, function ($row) {
                    if (is_array($row)) return count($row) > 0;
                    return $row !== null && $row !== '';
                }));

                if ($required && count($rows) < 1) {
                    return response()->json(['message' => "Falta responder: {$label}"], 422);
                }

                if (count($cols) > 0) {
                    foreach ($rows as $row) {
                        if (!is_array($row)) {
                            return response()->json(['message' => "El campo {$label} (tabla) tiene filas inválidas."], 422);
                        }

                        $isAssoc = array_keys($row) !== range(0, count($row) - 1);
                        if ($isAssoc) {
                            foreach ($cols as $c) {
                                if (!array_key_exists($c, $row)) {
                                    $row[$c] = $row[$c] ?? '';
                                }
                            }
                        }
                    }
                }

                $cleanAnswers[$id] = $rows;
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

                    // ✅ guardar PNG físico solo para este formulario
                    if (
                        $formCodeKey === 'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil' &&
                        str_starts_with($v, 'data:image/')
                    ) {
                        $storedPath = $this->storeSignatureForChecklistHerramienta($v, $user?->id, $id);

                        if (!$storedPath) {
                            return response()->json(['message' => "No se pudo guardar la firma del campo {$label}."], 422);
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

        if (!$user->hasRole('Administrador') && $form->status !== 'PUBLICADO') {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $subs = FormSubmission::query()
            ->with(['user:id,name'])
            ->where('form_id', $form->id)
            ->orderByDesc('id')
            ->limit(10)
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
}