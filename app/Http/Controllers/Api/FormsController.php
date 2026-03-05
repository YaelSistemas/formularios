<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class FormsController extends Controller
{
    /**
     * LISTA (Usuario autenticado)
     * - Usuario normal: solo PUBLICADO
     * - Admin: todos
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->hasRole('Administrador')) {
            $forms = Form::query()
                ->orderByDesc('id')
                ->get(['id', 'title', 'status', 'created_at']);
        } else {
            $forms = Form::query()
                ->where('status', 'PUBLICADO')
                ->orderByDesc('id')
                ->get(['id', 'title', 'status', 'created_at']);
        }

        return response()->json(['forms' => $forms]);
    }

    /**
     * CREAR (Admin)
     * - Se llama desde /api/admin/forms
     * - payload estándar: { fields: [...] }
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'array'],
            'status'  => ['nullable', 'in:BORRADOR,PUBLICADO,INACTIVO'],

            // validación suave del builder (si viene payload.fields)
            'payload.fields' => ['nullable', 'array'],
            'payload.fields.*.id' => ['required_with:payload.fields', 'string', 'max:120'],

            // label puede venir vacío en tipos como separator (lo validamos después)
            'payload.fields.*.label' => ['nullable', 'string', 'max:150'],

            // ✅ NUEVOS TYPES
            'payload.fields.*.type' => ['required_with:payload.fields', 'in:text,textarea,number,date,datetime,select,list,radio,checkbox,static_text,separator,fixed_image,fixed_file'],
            'payload.fields.*.required' => ['nullable', 'boolean'],

            // opciones para select/list/radio
            'payload.fields.*.options' => ['nullable', 'array'],
            'payload.fields.*.options.*' => ['nullable', 'string', 'max:150'],

            // ✅ extra props para nuevos tipos
            'payload.fields.*.text' => ['nullable', 'string', 'max:5000'], // static_text
            'payload.fields.*.url'  => ['nullable', 'string', 'max:2048'], // fixed_image / fixed_file
        ]);

        $payload = $this->normalizePayload($data['payload'] ?? []);

        // Validación adicional de selects/list/radio (mínimo 2 opciones)
        $selectErr = $this->validateChoiceFields($payload);
        if ($selectErr) {
            return response()->json(['message' => $selectErr], 422);
        }

        // Validación extra de tipos "fijos"
        $extraErr = $this->validateFixedFields($payload);
        if ($extraErr) {
            return response()->json(['message' => $extraErr], 422);
        }

        // Si intentan crear ya PUBLICADO, validamos que sea publicable
        $status = $data['status'] ?? 'BORRADOR';
        if ($status === 'PUBLICADO') {
            $pubErr = $this->validatePublishable($payload);
            if ($pubErr) {
                return response()->json(['message' => $pubErr], 422);
            }
        }

        $form = Form::create([
            'user_id' => $user ? $user->id : null,
            'title'   => $data['title'],
            'payload' => $payload,
            'status'  => $status,
        ]);

        $resp = $form->toArray();
        $resp['payload'] = $payload;

        return response()->json(['ok' => true, 'form' => $resp], 201);
    }

    /**
     * VER DETALLE (Usuario autenticado)
     * - Usuario normal: solo si está PUBLICADO
     * - Admin: cualquiera
     */
    public function show(Request $request, Form $form)
    {
        $user = $request->user();

        if (!($user && $user->hasRole('Administrador'))) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }
        }

        $payload = $this->normalizePayload($form->payload ?? []);

        $resp = $form->toArray();
        $resp['payload'] = $payload;

        return response()->json(['form' => $resp]);
    }

    /**
     * EDITAR (Admin)
     * - PUT /api/admin/forms/{form}
     */
    public function update(Request $request, Form $form)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'array'],
            'status'  => ['required', 'in:BORRADOR,PUBLICADO,INACTIVO'],

            'payload.fields' => ['nullable', 'array'],
            'payload.fields.*.id' => ['required_with:payload.fields', 'string', 'max:120'],
            'payload.fields.*.label' => ['nullable', 'string', 'max:150'],
            'payload.fields.*.type' => ['required_with:payload.fields', 'in:text,textarea,number,date,datetime,select,list,radio,checkbox,static_text,separator,fixed_image,fixed_file'],
            'payload.fields.*.required' => ['nullable', 'boolean'],

            'payload.fields.*.options' => ['nullable', 'array'],
            'payload.fields.*.options.*' => ['nullable', 'string', 'max:150'],

            'payload.fields.*.text' => ['nullable', 'string', 'max:5000'],
            'payload.fields.*.url'  => ['nullable', 'string', 'max:2048'],
        ]);

        $payload = $this->normalizePayload($data['payload'] ?? []);

        $selectErr = $this->validateChoiceFields($payload);
        if ($selectErr) {
            return response()->json(['message' => $selectErr], 422);
        }

        $extraErr = $this->validateFixedFields($payload);
        if ($extraErr) {
            return response()->json(['message' => $extraErr], 422);
        }

        // Si lo dejan PUBLICADO desde update, validamos que sea publicable
        if (($data['status'] ?? 'BORRADOR') === 'PUBLICADO') {
            $pubErr = $this->validatePublishable($payload);
            if ($pubErr) {
                return response()->json(['message' => $pubErr], 422);
            }
        }

        $form->title = $data['title'];
        $form->payload = $payload;
        $form->status = $data['status'];
        $form->save();

        $resp = $form->toArray();
        $resp['payload'] = $payload;

        return response()->json(['ok' => true, 'form' => $resp]);
    }

    /**
     * ELIMINAR (Admin)
     * - DELETE /api/admin/forms/{form}
     */
    public function destroy(Request $request, Form $form)
    {
        $form->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * PUBLICAR (Admin)
     * - POST /api/admin/forms/{form}/publish
     */
    public function publish(Request $request, Form $form)
    {
        $payload = $this->normalizePayload($form->payload ?? []);

        $selectErr = $this->validateChoiceFields($payload);
        if ($selectErr) {
            return response()->json(['message' => $selectErr], 422);
        }

        $extraErr = $this->validateFixedFields($payload);
        if ($extraErr) {
            return response()->json(['message' => $extraErr], 422);
        }

        $pubErr = $this->validatePublishable($payload);
        if ($pubErr) {
            return response()->json(['message' => $pubErr], 422);
        }

        $form->payload = $payload;
        $form->status = 'PUBLICADO';
        $form->save();

        $resp = $form->toArray();
        $resp['payload'] = $payload;

        return response()->json(['ok' => true, 'form' => $resp]);
    }

    /**
     * DESPUBLICAR (Admin)
     * - POST /api/admin/forms/{form}/unpublish
     */
    public function unpublish(Request $request, Form $form)
    {
        $form->status = 'BORRADOR';
        $form->save();

        return response()->json(['ok' => true, 'form' => $form]);
    }

    /**
     * Normaliza el payload para que el builder quede estándar:
     * - Si viene { fields: [...] } => lo deja igual (limpio)
     * - Si viene payload viejo => fields vacío y se guarda en _legacy
     */
    private function normalizePayload($payload): array
    {
        if (!is_array($payload)) {
            return ['fields' => []];
        }

        if (array_key_exists('fields', $payload) && is_array($payload['fields'])) {
            $payload['fields'] = array_values(array_filter(array_map(function ($f) {
                if (!is_array($f)) return null;

                $id = isset($f['id']) ? (string) $f['id'] : null;
                $type = isset($f['type']) ? (string) $f['type'] : null;

                $label = isset($f['label']) ? trim((string) $f['label']) : '';

                if (!$id || !$type) return null;

                $field = [
                    'id'       => $id,
                    'label'    => $label, // puede ir vacío en separator
                    'type'     => $type,
                    'required' => (bool) ($f['required'] ?? false),
                ];

                // select/list/radio => options
                if (in_array($field['type'], ['select', 'list', 'radio'], true)) {
                    $opts = $f['options'] ?? [];
                    if (!is_array($opts)) $opts = [];
                    $field['options'] = array_values(array_filter(array_map(function ($o) {
                        $s = trim((string) $o);
                        return $s !== '' ? $s : null;
                    }, $opts)));
                }

                // static_text => text
                if ($field['type'] === 'static_text') {
                    $field['text'] = isset($f['text']) ? (string) $f['text'] : '';
                }

                // fixed_image / fixed_file => url
                if (in_array($field['type'], ['fixed_image', 'fixed_file'], true)) {
                    $field['url'] = isset($f['url']) ? (string) $f['url'] : '';
                }

                // separator: no requiere nada extra

                return $field;
            }, $payload['fields'])));

            return $payload;
        }

        return [
            'fields' => [],
            '_legacy' => $payload,
        ];
    }

    /**
     * Reglas extra para select/list/radio:
     * - options mínimo 2
     */
    private function validateChoiceFields(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields)) return null;

        foreach ($fields as $f) {
            $type = $f['type'] ?? null;
            if (in_array($type, ['select', 'list', 'radio'], true)) {
                $options = $f['options'] ?? [];
                if (!is_array($options) || count($options) < 2) {
                    $label = $f['label'] ?? '(sin etiqueta)';
                    return "El campo \"{$label}\" ({$type}) debe tener al menos 2 opciones.";
                }
            }
        }

        return null;
    }

    /**
     * Validación extra para tipos fijos:
     * - static_text => text no vacío
     * - fixed_image/fixed_file => url no vacío
     */
    private function validateFixedFields(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields)) return null;

        foreach ($fields as $f) {
            $type = $f['type'] ?? null;
            $label = $f['label'] ?? '(sin etiqueta)';

            if ($type === 'static_text') {
                $text = trim((string)($f['text'] ?? ''));
                if ($text === '') return "El campo \"{$label}\" (texto fijo) requiere contenido.";
            }

            if ($type === 'fixed_image') {
                $url = trim((string)($f['url'] ?? ''));
                if ($url === '') return "El campo \"{$label}\" (imagen fija) requiere URL.";
            }

            if ($type === 'fixed_file') {
                $url = trim((string)($f['url'] ?? ''));
                if ($url === '') return "El campo \"{$label}\" (archivo fijo) requiere URL.";
            }
        }

        return null;
    }

    /**
     * Validación para permitir PUBLICAR:
     * - Debe tener al menos 1 field
     * - Campos deben ser válidos según su tipo
     */
    private function validatePublishable(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields) || count($fields) < 1) {
            return 'No puedes publicar un formulario sin campos.';
        }

        foreach ($fields as $f) {
            $id = $f['id'] ?? null;
            $type = $f['type'] ?? null;
            $label = isset($f['label']) ? trim((string)$f['label']) : '';

            if (!$id || !$type) {
                return 'El formulario tiene campos inválidos. Revisa el builder.';
            }

            // separator: label puede estar vacío
            if ($type !== 'separator' && $label === '') {
                return 'El formulario tiene campos sin etiqueta. Revisa el builder.';
            }

            // select/list/radio: opciones ya validadas, pero protegemos:
            if (in_array($type, ['select', 'list', 'radio'], true)) {
                $opts = $f['options'] ?? [];
                if (!is_array($opts) || count($opts) < 2) {
                    return 'Hay campos de selección sin suficientes opciones.';
                }
            }

            // static_text / fixed_*: ya validados, pero protegemos:
            if ($type === 'static_text' && trim((string)($f['text'] ?? '')) === '') {
                return 'Hay textos fijos vacíos.';
            }
            if (in_array($type, ['fixed_image', 'fixed_file'], true) && trim((string)($f['url'] ?? '')) === '') {
                return 'Hay archivos/imagenes fijas sin URL.';
            }
        }

        return null;
    }

    public function adminIndex(Request $request)
    {
        $forms = Form::query()
            ->orderByDesc('id')
            ->get(['id', 'title', 'status', 'created_at', 'payload']);

        return response()->json(['forms' => $forms]);
    }
}