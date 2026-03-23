<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Support\Forms\FormRegistry;
use Illuminate\Http\Request;

class FormsController extends Controller
{
    public function __construct(
        protected FormRegistry $formRegistry
    ) {
    }

    /**
     * LISTA (Usuario autenticado)
     * - Admin: ve todos los formularios de código
     * - Usuario normal: solo PUBLICADO
     * - Si el formulario NO tiene usuarios asignados, no lo ve nadie excepto admin
     * - Si tiene asignaciones, solo lo ve el usuario asignado
     */
    public function index(Request $request)
    {
        $this->syncCodeForms($request->user()?->id);

        $user = $request->user();

        $query = Form::query()
            ->withCount('submissions')
            ->withCount(['assignedUsers as assignments_count'])
            ->orderByDesc('id');

        if (!($user && $user->hasRole('Administrador'))) {
            $query->where('status', 'PUBLICADO')
                ->whereHas('assignedUsers', function ($sub) use ($user) {
                    $sub->where('users.id', $user->id);
                });
        }

        $forms = $query
            ->get(['id', 'title', 'status', 'created_at', 'payload'])
            ->filter(fn ($form) => filled(data_get($form->payload, '_code_key')))
            ->values();

        return response()->json(['forms' => $forms]);
    }

    /**
     * VER DETALLE
     */
    public function show(Request $request, Form $form)
    {
        $this->syncCodeForms($request->user()?->id);

        if (!filled(data_get($form->payload, '_code_key'))) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $user = $request->user();

        if (!($user && $user->hasRole('Administrador'))) {
            if ($form->status !== 'PUBLICADO') {
                return response()->json(['message' => 'No encontrado.'], 404);
            }

            if (!$this->userCanAccessForm($user->id, $form)) {
                return response()->json(['message' => 'No autorizado para este formulario.'], 403);
            }
        }

        $payload = $this->normalizePayload($form->payload ?? []);

        $resp = $form->loadCount([
            'submissions',
            'assignedUsers as assignments_count',
        ])->toArray();

        $resp['payload'] = $payload;

        return response()->json(['form' => $resp]);
    }

    /**
     * DESHABILITADO
     * Los formularios ahora se crean por código.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'La creación desde panel está deshabilitada. Los formularios ahora se definen por código.',
        ], 405);
    }

    /**
     * DESHABILITADO
     * Los formularios ahora se editan por código.
     */
    public function update(Request $request, Form $form)
    {
        return response()->json([
            'message' => 'La edición desde panel está deshabilitada. Los formularios ahora se definen por código.',
        ], 405);
    }

    /**
     * DESHABILITADO
     * Los formularios ahora se eliminan quitándolos del catálogo en código.
     */
    public function destroy(Request $request, Form $form)
    {
        return response()->json([
            'message' => 'La eliminación desde panel está deshabilitada. Quita el formulario del catálogo en código.',
        ], 405);
    }

    public function publish(Request $request, Form $form)
    {
        $this->syncCodeForms($request->user()?->id);

        if (!filled(data_get($form->payload, '_code_key'))) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $payload = $this->normalizePayload($form->payload ?? []);

        $choiceErr = $this->validateChoiceFields($payload);
        if ($choiceErr) {
            return response()->json(['message' => $choiceErr], 422);
        }

        $fixedErr = $this->validateFixedFields($payload);
        if ($fixedErr) {
            return response()->json(['message' => $fixedErr], 422);
        }

        $tableErr = $this->validateTableFields($payload);
        if ($tableErr) {
            return response()->json(['message' => $tableErr], 422);
        }

        $pubErr = $this->validatePublishable($payload);
        if ($pubErr) {
            return response()->json(['message' => $pubErr], 422);
        }

        $form->payload = $payload;
        $form->status = 'PUBLICADO';
        $form->save();

        $resp = $form->loadCount([
            'submissions',
            'assignedUsers as assignments_count',
        ])->toArray();

        $resp['payload'] = $payload;

        return response()->json(['ok' => true, 'form' => $resp]);
    }

    public function unpublish(Request $request, Form $form)
    {
        $this->syncCodeForms($request->user()?->id);

        if (!filled(data_get($form->payload, '_code_key'))) {
            return response()->json(['message' => 'No encontrado.'], 404);
        }

        $form->status = 'BORRADOR';
        $form->save();

        return response()->json(['ok' => true, 'form' => $form]);
    }

    public function adminIndex(Request $request)
    {
        $this->syncCodeForms($request->user()?->id);

        $forms = Form::query()
            ->withCount('submissions')
            ->withCount(['assignedUsers as assignments_count'])
            ->orderByDesc('id')
            ->get(['id', 'title', 'status', 'created_at', 'payload'])
            ->filter(fn ($form) => filled(data_get($form->payload, '_code_key')))
            ->values();

        return response()->json(['forms' => $forms]);
    }

    /**
     * Determina si el usuario puede acceder al formulario.
     * - Si no hay asignaciones, nadie excepto admin puede acceder
     * - Si hay asignaciones, solo usuarios asignados
     */
    private function userCanAccessForm(int $userId, Form $form): bool
    {
        $hasAssignments = $form->assignedUsers()->exists();

        if (!$hasAssignments) {
            return false;
        }

        return $form->assignedUsers()
            ->where('users.id', $userId)
            ->exists();
    }

    /**
     * Sincroniza el catálogo en código hacia la tabla forms.
     * - Crea si no existe
     * - Actualiza title/payload si ya existe
     * - Conserva status actual
     */
    private function syncCodeForms(?int $userId = null): void
    {
        foreach ($this->formRegistry->all() as $item) {
            $key = (string) ($item['key'] ?? '');
            $title = (string) ($item['title'] ?? '');
            $payload = $item['payload'] ?? [];

            if ($key === '' || $title === '' || !is_array($payload)) {
                continue;
            }

            $payload['_code_key'] = $key;
            $normalizedPayload = $this->normalizePayload($payload);

            $existing = Form::query()
                ->where('payload->_code_key', $key)
                ->first();

            if ($existing) {
                $existing->title = $title;
                $existing->payload = $normalizedPayload;
                $existing->save();
                continue;
            }

            Form::create([
                'user_id' => $userId,
                'title'   => $title,
                'payload' => $normalizedPayload,
                'status'  => 'BORRADOR',
            ]);
        }
    }

    /**
     * Normaliza payload.fields y conserva props extra por tipo.
     */
    private function normalizePayload($payload): array
    {
        if (!is_array($payload)) {
            return ['fields' => []];
        }

        if (array_key_exists('fields', $payload) && is_array($payload['fields'])) {
            $payload['fields'] = array_values(array_filter(array_map(function ($f) {
                if (!is_array($f)) {
                    return null;
                }

                $id = isset($f['id']) ? (string) $f['id'] : null;
                $type = isset($f['type']) ? (string) $f['type'] : null;
                $label = isset($f['label']) ? trim((string) $f['label']) : '';

                if (!$id || !$type) {
                    return null;
                }

                $field = [
                    'id'       => $id,
                    'label'    => $label,
                    'type'     => $type,
                    'required' => (bool) ($f['required'] ?? false),
                ];

                if (in_array($type, ['select', 'radio'], true)) {
                    $opts = $f['options'] ?? [];
                    if (!is_array($opts)) {
                        $opts = [];
                    }

                    $field['options'] = array_values(array_filter(array_map(function ($o) {
                        $s = trim((string) $o);
                        return $s !== '' ? $s : null;
                    }, $opts)));
                }

                if ($type === 'static_text') {
                    $field['text'] = (string) ($f['text'] ?? '');
                }

                if (in_array($type, ['fixed_image', 'fixed_file'], true)) {
                    $field['url'] = (string) ($f['url'] ?? '');
                }

                if ($type === 'table') {
                    $cols = $f['columns'] ?? [];
                    if (!is_array($cols)) {
                        $cols = [];
                    }

                    $field['columns'] = array_values(array_filter(array_map(function ($c) {
                        $s = trim((string) $c);
                        return $s !== '' ? $s : null;
                    }, $cols)));

                    $rowSchema = $f['row_schema'] ?? [];
                    if (!is_array($rowSchema)) {
                        $rowSchema = [];
                    }

                    $field['row_schema'] = array_values(array_filter(array_map(function ($col) {
                        if (!is_array($col)) {
                            return null;
                        }

                        $colId = isset($col['id']) ? (string) $col['id'] : null;
                        $colType = isset($col['type']) ? (string) $col['type'] : null;
                        $colLabel = isset($col['label']) ? trim((string) $col['label']) : '';

                        if (!$colId || !$colType) {
                            return null;
                        }

                        $normalizedCol = [
                            'id' => $colId,
                            'label' => $colLabel,
                            'type' => $colType,
                            'required' => (bool) ($col['required'] ?? false),
                        ];

                        if (in_array($colType, ['select', 'radio'], true)) {
                            $colOpts = $col['options'] ?? [];
                            if (!is_array($colOpts)) {
                                $colOpts = [];
                            }

                            $normalizedCol['options'] = array_values(array_filter(array_map(function ($o) {
                                $s = trim((string) $o);
                                return $s !== '' ? $s : null;
                            }, $colOpts)));
                        }

                        return $normalizedCol;
                    }, $rowSchema)));
                }

                return $field;
            }, $payload['fields'])));

            return $payload;
        }

        return ['fields' => [], '_legacy' => $payload];
    }

    /**
     * select/radio: mínimo 2 opciones
     */
    private function validateChoiceFields(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields)) {
            return null;
        }

        foreach ($fields as $f) {
            $type = $f['type'] ?? null;

            if (in_array($type, ['select', 'radio'], true)) {
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
     * static_text/fixed_*: contenido obligatorio
     */
    private function validateFixedFields(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields)) {
            return null;
        }

        foreach ($fields as $f) {
            $type = $f['type'] ?? null;
            $label = $f['label'] ?? '(sin etiqueta)';

            if ($type === 'static_text') {
                if (trim((string) ($f['text'] ?? '')) === '') {
                    return "El campo \"{$label}\" (texto fijo) requiere contenido.";
                }
            }

            if (in_array($type, ['fixed_image', 'fixed_file'], true)) {
                if (trim((string) ($f['url'] ?? '')) === '') {
                    return "El campo \"{$label}\" ({$type}) requiere URL.";
                }
            }
        }

        return null;
    }

    /**
     * table: mínimo 1 columna y normaliza row_schema
     */
    private function validateTableFields(array $payload): ?string
    {
        $fields = $payload['fields'] ?? [];
        if (!is_array($fields)) {
            return null;
        }

        foreach ($fields as $f) {
            if (($f['type'] ?? null) !== 'table') {
                continue;
            }

            $label = $f['label'] ?? '(sin etiqueta)';
            $cols = $f['columns'] ?? [];
            if (!is_array($cols) || count($cols) < 1) {
                return "El campo \"{$label}\" (tabla) debe tener al menos 1 columna.";
            }

            $rowSchema = $f['row_schema'] ?? [];
            if (!is_array($rowSchema) || count($rowSchema) < 1) {
                return "El campo \"{$label}\" (tabla) debe tener row_schema definido.";
            }
        }

        return null;
    }

    /**
     * Validación para permitir PUBLICAR
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
            $label = isset($f['label']) ? trim((string) $f['label']) : '';

            if (!$id || !$type) {
                return 'El formulario tiene campos inválidos. Revisa el catálogo en código.';
            }

            if (
                !in_array($type, ['separator', 'static_text', 'fixed_image', 'fixed_file'], true)
                && $label === ''
            ) {
                return 'El formulario tiene campos sin etiqueta. Revisa el catálogo en código.';
            }
        }

        return null;
    }
}