<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsController extends Controller
{
    protected function isAdminUser($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('Administrador');
    }

    protected function serializePermissionSnapshot(Permission $permission): array
    {
        return [
            'name' => trim((string) $permission->name),
        ];
    }

    protected function buildPermissionChanges(array $before, array $after): array
    {
        $fields = [
            'name' => [
                'label' => 'Nombre',
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
        int $permissionId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        PermissionHistory::create([
            'permission_id' => $permissionId,
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

        $permissions = Permission::query()
            ->where('guard_name', 'sanctum')
            ->withCount('roles')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate($perPage);

        $permissions->getCollection()->transform(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'guard_name' => $p->guard_name,
            'roles_count' => (int) ($p->roles_count ?? 0),
            'created_at' => $p->created_at,
        ]);

        return response()->json($permissions);
    }

    public function history(Request $request, Permission $permission)
    {
        $authUser = $request->user();

        if (!$this->isAdminUser($authUser)) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de permisos.',
            ], 403);
        }

        $items = PermissionHistory::query()
            ->with(['actor:id,name,email'])
            ->where('permission_id', $permission->id)
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
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.unique' => 'Ya existe un permiso con ese nombre.',
            'name.max' => 'El nombre del permiso es demasiado largo.',
        ];

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where(fn ($q) => $q->where('guard_name', 'sanctum')),
            ],
        ], $messages);

        $authUser = $request->user();

        $permission = DB::transaction(function () use ($data, $authUser) {
            $permission = Permission::create([
                'name' => trim($data['name']),
                'guard_name' => 'sanctum',
            ]);

            $this->createHistoryEntry(
                permissionId: (int) $permission->id,
                actorId: $authUser?->id,
                action: 'created',
                snapshot: $this->serializePermissionSnapshot($permission),
                changes: null
            );

            return $permission;
        });

        return response()->json([
            'ok' => true,
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'roles_count' => 0,
                'created_at' => $permission->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, Permission $permission)
    {
        if ($permission->guard_name !== 'sanctum') {
            return response()->json([
                'message' => 'Solo se pueden editar permisos del guard sanctum.'
            ], 422);
        }

        $messages = [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.unique' => 'Ya existe un permiso con ese nombre.',
            'name.max' => 'El nombre del permiso es demasiado largo.',
        ];

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where(fn ($q) => $q->where('guard_name', 'sanctum'))
                    ->ignore($permission->id),
            ],
        ], $messages);

        $authUser = $request->user();

        DB::transaction(function () use ($permission, $data, $authUser) {
            $before = $this->serializePermissionSnapshot($permission);

            $permission->name = trim($data['name']);
            $permission->guard_name = 'sanctum';
            $permission->save();

            $after = $this->serializePermissionSnapshot($permission);
            $changes = $this->buildPermissionChanges($before, $after);

            if (!empty($changes)) {
                $this->createHistoryEntry(
                    permissionId: (int) $permission->id,
                    actorId: $authUser?->id,
                    action: 'updated',
                    snapshot: $after,
                    changes: $changes
                );
            }
        });

        $permission->loadCount('roles');

        return response()->json([
            'ok' => true,
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'roles_count' => (int) ($permission->roles_count ?? 0),
                'created_at' => $permission->created_at,
            ],
        ]);
    }

    public function destroy(Request $request, Permission $permission)
    {
        if ($permission->guard_name !== 'sanctum') {
            return response()->json([
                'message' => 'Solo se pueden eliminar permisos del guard sanctum.'
            ], 422);
        }
    
        $permission->loadCount('roles');
    
        if ((int) ($permission->roles_count ?? 0) > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el permiso porque está asignado a uno o más roles.'
            ], 422);
        }
    
        $authUser = $request->user();
    
        DB::transaction(function () use ($permission, $authUser) {
            $snapshot = $this->serializePermissionSnapshot($permission);
    
            $this->createHistoryEntry(
                permissionId: (int) $permission->id,
                actorId: $authUser?->id,
                action: 'deleted',
                snapshot: $snapshot,
                changes: null
            );
    
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();
    
            DB::table('model_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();
    
            DB::table('permissions')
                ->where('id', $permission->id)
                ->delete();
        });
    
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    
        return response()->json(['ok' => true]);
    }
}