<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\EmpresaHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpresasController extends Controller
{
    protected function isAdminUser($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('Administrador');
    }

    protected function serializeEmpresaSnapshot(Empresa $empresa): array
    {
        return [
            'nombre' => trim((string) $empresa->nombre),
            'razon_social' => filled($empresa->razon_social)
                ? trim((string) $empresa->razon_social)
                : null,
            'estado' => (bool) $empresa->activo ? 'Activo' : 'Inactivo',
        ];
    }

    protected function buildEmpresaChanges(array $before, array $after): array
    {
        $fields = [
            'nombre' => [
                'label' => 'Nombre',
                'type' => 'text',
            ],
            'razon_social' => [
                'label' => 'Razón social',
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
        int $empresaId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        EmpresaHistory::create([
            'empresa_id' => $empresaId,
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

        $query = Empresa::query()->withCount('users');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('razon_social', 'like', "%{$q}%");
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

    public function history(Request $request, Empresa $empresa)
    {
        $authUser = $request->user();

        if (!$this->isAdminUser($authUser)) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de empresas.',
            ], 403);
        }

        $items = EmpresaHistory::query()
            ->with(['actor:id,name,email'])
            ->where('empresa_id', $empresa->id)
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
            'nombre' => ['required', 'string', 'max:255', Rule::unique('empresas', 'nombre')],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $authUser = $request->user();

        $empresa = DB::transaction(function () use ($data, $authUser) {
            $empresa = Empresa::create([
                'nombre' => trim($data['nombre']),
                'razon_social' => filled($data['razon_social'] ?? null) ? trim($data['razon_social']) : null,
                'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
            ]);

            $this->createHistoryEntry(
                empresaId: (int) $empresa->id,
                actorId: $authUser?->id,
                action: 'created',
                snapshot: $this->serializeEmpresaSnapshot($empresa),
                changes: null
            );

            return $empresa;
        });

        return response()->json([
            'empresa' => $empresa->fresh()->loadCount('users'),
        ], 201);
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('empresas', 'nombre')->ignore($empresa->id),
            ],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $authUser = $request->user();

        DB::transaction(function () use ($request, $empresa, $data, $authUser) {
            $before = $this->serializeEmpresaSnapshot($empresa);

            $empresa->update([
                'nombre' => trim($data['nombre']),
                'razon_social' => filled($data['razon_social'] ?? null) ? trim($data['razon_social']) : null,
                'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : $empresa->activo,
            ]);

            $fresh = $empresa->fresh();

            $after = $this->serializeEmpresaSnapshot($fresh);
            $changes = $this->buildEmpresaChanges($before, $after);

            if (!empty($changes)) {
                $this->createHistoryEntry(
                    empresaId: (int) $fresh->id,
                    actorId: $authUser?->id,
                    action: 'updated',
                    snapshot: $after,
                    changes: $changes
                );
            }
        });

        return response()->json([
            'empresa' => $empresa->fresh()->loadCount('users'),
        ]);
    }

    public function destroy(Request $request, Empresa $empresa)
    {
        $empresa->loadCount('users');

        if ((int) $empresa->users_count > 0) {
            return response()->json([
                'message' => 'No puedes eliminar una empresa que ya está asignada a uno o más usuarios.',
            ], 422);
        }

        $authUser = $request->user();

        DB::transaction(function () use ($empresa, $authUser) {
            $snapshot = $this->serializeEmpresaSnapshot($empresa);

            $this->createHistoryEntry(
                empresaId: (int) $empresa->id,
                actorId: $authUser?->id,
                action: 'deleted',
                snapshot: $snapshot,
                changes: null
            );

            $empresa->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Empresa eliminada correctamente.',
        ]);
    }
}