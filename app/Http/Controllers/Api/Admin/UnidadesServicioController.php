<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadServicio;
use App\Models\UnidadServicioHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnidadesServicioController extends Controller
{
    protected function isAdminUser($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('Administrador');
    }

    protected function serializeUnidadServicioSnapshot(UnidadServicio $unidadServicio): array
    {
        return [
            'nombre' => trim((string) $unidadServicio->nombre),
            'descripcion' => filled($unidadServicio->descripcion)
                ? trim((string) $unidadServicio->descripcion)
                : null,
            'estado' => (bool) $unidadServicio->activo ? 'Activo' : 'Inactivo',
        ];
    }

    protected function buildUnidadServicioChanges(array $before, array $after): array
    {
        $fields = [
            'nombre' => [
                'label' => 'Nombre',
                'type' => 'text',
            ],
            'descripcion' => [
                'label' => 'Descripción',
                'type' => 'text',
            ],
            'estado' => [
                'label' => 'Estado',
                'type' => 'text',
            ],
        ];

        $changes = [];

        foreach ($fields as $field => $meta) {
            $old = array_key_exists($field, $before) ? $before[$field] : null;
            $new = array_key_exists($field, $after) ? $after[$field] : null;

            $old = $old === null ? null : trim((string) $old);
            $new = $new === null ? null : trim((string) $new);

            if ($old !== $new) {
                $changes[] = [
                    'field' => $field,
                    'label' => $meta['label'],
                    'type' => $meta['type'],
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        return $changes;
    }

    protected function createHistoryEntry(
        int $unidadServicioId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        UnidadServicioHistory::create([
            'unidad_servicio_id' => $unidadServicioId,
            'user_id' => $actorId,
            'action' => $action,
            'snapshot' => $snapshot,
            'changes' => $changes,
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        $rows = UnidadServicio::query()
            ->withCount('users')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nombre', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function history(Request $request, UnidadServicio $unidades_servicio)
    {
        $authUser = $request->user();

        if (!$this->isAdminUser($authUser)) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de unidades de servicio.',
            ], 403);
        }

        $items = UnidadServicioHistory::query()
            ->with(['actor:id,name,email'])
            ->where('unidad_servicio_id', $unidades_servicio->id)
            ->oldest()
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'action' => $row->action,
                    'snapshot' => $row->snapshot,
                    'changes' => $row->changes ?? [],
                    'actor' => $row->actor ? [
                        'id' => $row->actor->id,
                        'name' => $row->actor->name,
                        'email' => $row->actor->email,
                    ] : null,
                    'created_at' => $row->created_at,
                ];
            })
            ->values();

        return response()->json([
            'history' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'nombre.required' => 'No se puede crear la unidad de servicio porque falta el nombre.',
            'nombre.unique' => 'Ya existe una unidad de servicio con ese nombre.',
        ];

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('unidades_servicio', 'nombre')],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ], $messages);

        $authUser = $request->user();

        $row = DB::transaction(function () use ($data, $authUser) {
            $unidadServicio = UnidadServicio::create([
                'nombre' => trim($data['nombre']),
                'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
                'activo' => (bool) $data['activo'],
            ]);

            $this->createHistoryEntry(
                unidadServicioId: (int) $unidadServicio->id,
                actorId: $authUser?->id,
                action: 'created',
                snapshot: $this->serializeUnidadServicioSnapshot($unidadServicio),
                changes: null
            );

            return $unidadServicio;
        });

        $row->loadCount('users');

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $row,
        ], 201);
    }

    public function show(UnidadServicio $unidades_servicio)
    {
        $unidades_servicio->loadCount('users');

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $unidades_servicio,
        ]);
    }

    public function update(Request $request, UnidadServicio $unidades_servicio)
    {
        $messages = [
            'nombre.required' => 'No se puede actualizar la unidad de servicio porque falta el nombre.',
            'nombre.unique' => 'Ya existe una unidad de servicio con ese nombre.',
        ];

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unidades_servicio', 'nombre')->ignore($unidades_servicio->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ], $messages);

        $authUser = $request->user();

        DB::transaction(function () use ($unidades_servicio, $data, $authUser) {
            $before = $this->serializeUnidadServicioSnapshot($unidades_servicio);

            $unidades_servicio->update([
                'nombre' => trim($data['nombre']),
                'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
                'activo' => (bool) $data['activo'],
            ]);

            $fresh = $unidades_servicio->fresh();
            $after = $this->serializeUnidadServicioSnapshot($fresh);
            $changes = $this->buildUnidadServicioChanges($before, $after);

            if (!empty($changes)) {
                $this->createHistoryEntry(
                    unidadServicioId: (int) $fresh->id,
                    actorId: $authUser?->id,
                    action: 'updated',
                    snapshot: $after,
                    changes: $changes
                );
            }
        });

        return response()->json([
            'ok' => true,
            'unidad_servicio' => $unidades_servicio->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(Request $request, UnidadServicio $unidades_servicio)
    {
        $unidades_servicio->loadCount('users');

        if ((int) $unidades_servicio->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar una unidad de servicio que ya está asignada a uno o más usuarios.',
            ], 422);
        }

        $authUser = $request->user();

        DB::transaction(function () use ($unidades_servicio, $authUser) {
            $snapshot = $this->serializeUnidadServicioSnapshot($unidades_servicio);

            $this->createHistoryEntry(
                unidadServicioId: (int) $unidades_servicio->id,
                actorId: $authUser?->id,
                action: 'deleted',
                snapshot: $snapshot,
                changes: null
            );

            $unidades_servicio->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Unidad de servicio eliminada correctamente.',
        ]);
    }
}