<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GruposController extends Controller
{
    protected function isAdminUser($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('Administrador');
    }

    protected function serializeGrupoSnapshot(Grupo $grupo): array
    {
        return [
            'nombre' => trim((string) $grupo->nombre),
            'nombre_mostrar' => filled($grupo->nombre_mostrar)
                ? trim((string) $grupo->nombre_mostrar)
                : null,
            'descripcion' => filled($grupo->descripcion)
                ? trim((string) $grupo->descripcion)
                : null,
            'estado' => (bool) $grupo->activo ? 'Activo' : 'Inactivo',
        ];
    }

    protected function buildGrupoChanges(array $before, array $after): array
    {
        $fields = [
            'nombre' => [
                'label' => 'Nombre',
                'type' => 'text',
            ],
            'nombre_mostrar' => [
                'label' => 'Nombre a mostrar',
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
        int $grupoId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        GrupoHistory::create([
            'grupo_id' => $grupoId,
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

        $query = Grupo::query()->withCount('users');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('nombre_mostrar', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");
            });
        }

        $pag = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $pag->items(),
            'current_page' => $pag->currentPage(),
            'last_page' => $pag->lastPage(),
            'total' => $pag->total(),
        ]);
    }

    public function history(Request $request, Grupo $grupo)
    {
        $authUser = $request->user();

        if (!$this->isAdminUser($authUser)) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de grupos.',
            ], 403);
        }

        $items = GrupoHistory::query()
            ->with(['actor:id,name,email'])
            ->where('grupo_id', $grupo->id)
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
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos', 'nombre')],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $authUser = $request->user();

        $grupo = DB::transaction(function () use ($data, $authUser) {
            $grupo = Grupo::create([
                'nombre' => trim($data['nombre']),
                'nombre_mostrar' => trim($data['nombre_mostrar']),
                'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
                'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
            ]);

            $this->createHistoryEntry(
                grupoId: (int) $grupo->id,
                actorId: $authUser?->id,
                action: 'created',
                snapshot: $this->serializeGrupoSnapshot($grupo),
                changes: null
            );

            return $grupo;
        });

        return response()->json([
            'grupo' => $grupo->fresh()->loadCount('users'),
        ], 201);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupos', 'nombre')->ignore($grupo->id),
            ],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $authUser = $request->user();

        DB::transaction(function () use ($grupo, $data, $authUser) {
            $before = $this->serializeGrupoSnapshot($grupo);

            $grupo->update([
                'nombre' => trim($data['nombre']),
                'nombre_mostrar' => trim($data['nombre_mostrar']),
                'descripcion' => filled($data['descripcion'] ?? null) ? trim($data['descripcion']) : null,
                'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : $grupo->activo,
            ]);

            $fresh = $grupo->fresh();
            $after = $this->serializeGrupoSnapshot($fresh);
            $changes = $this->buildGrupoChanges($before, $after);

            if (!empty($changes)) {
                $this->createHistoryEntry(
                    grupoId: (int) $fresh->id,
                    actorId: $authUser?->id,
                    action: 'updated',
                    snapshot: $after,
                    changes: $changes
                );
            }
        });

        return response()->json([
            'grupo' => $grupo->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(Request $request, Grupo $grupo)
    {
        $grupo->loadCount('users');

        if ((int) $grupo->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar un grupo que ya está asignado a uno o más usuarios.',
            ], 422);
        }

        $authUser = $request->user();

        DB::transaction(function () use ($grupo, $authUser) {
            $snapshot = $this->serializeGrupoSnapshot($grupo);

            $this->createHistoryEntry(
                grupoId: (int) $grupo->id,
                actorId: $authUser?->id,
                action: 'deleted',
                snapshot: $snapshot,
                changes: null
            );

            $grupo->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Grupo eliminado correctamente.',
        ]);
    }
}