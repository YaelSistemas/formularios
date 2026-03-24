<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesController extends Controller
{
    protected function countUsersByRoleId(int $roleId): int
    {
        return DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->where('model_type', \App\Models\User::class)
            ->count();
    }

    protected function isAdminUser($user): bool
    {
        return $user && method_exists($user, 'hasRole') && $user->hasRole('Administrador');
    }

    protected function normalizePermissionNames($permissions): array
    {
        $items = collect($permissions)
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->values()
            ->all();

        sort($items);

        return array_values($items);
    }

    protected function serializeRoleSnapshot(Role $role): array
    {
        $role->loadMissing('permissions:id,name');

        return [
            'name' => $role->name,
            'nombre_mostrar' => $role->nombre_mostrar,
            'descripcion' => $role->descripcion,
            'permissions' => $this->normalizePermissionNames(
                $role->permissions->pluck('name')->values()->all()
            ),
        ];
    }

    protected function buildRoleChanges(array $before, array $after): array
    {
        $fields = [
            'name' => [
                'label' => 'Nombre interno',
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
            'permissions' => [
                'label' => 'Permisos',
                'type' => 'list',
            ],
        ];

        $changes = [];

        foreach ($fields as $field => $meta) {
            $old = $before[$field] ?? ($meta['type'] === 'list' ? [] : null);
            $new = $after[$field] ?? ($meta['type'] === 'list' ? [] : null);

            if ($meta['type'] === 'list') {
                $old = $this->normalizePermissionNames((array) $old);
                $new = $this->normalizePermissionNames((array) $new);
            } else {
                $old = $old === null ? null : trim((string) $old);
                $new = $new === null ? null : trim((string) $new);
            }

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
        int $roleId,
        ?int $actorId,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        RoleHistory::create([
            'role_id' => $roleId,
            'user_id' => $actorId,
            'action' => $action,
            'snapshot' => $snapshot,
            'changes' => $changes,
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min(50, $perPage));

        $roles = Role::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('nombre_mostrar', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $roles->getCollection()->transform(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'nombre_mostrar' => $r->nombre_mostrar,
                'descripcion' => $r->descripcion,
                'permissions' => $r->permissions()->pluck('name')->values(),
                'users_count' => $this->countUsersByRoleId((int) $r->id),
                'created_at' => $r->created_at,
            ];
        });

        return response()->json($roles);
    }

    public function history(Request $request, Role $role)
    {
        $authUser = $request->user();

        if (!$this->isAdminUser($authUser)) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de roles.',
            ], 403);
        }

        $items = RoleHistory::query()
            ->with(['actor:id,name,email'])
            ->where('role_id', $role->id)
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
            'name.required' => 'El nombre interno del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'nombre_mostrar.required' => 'El nombre a mostrar es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'permissions.array' => 'El formato de permisos no es válido.',
            'permissions.*.exists' => 'Uno o más permisos no existen.',
        ];
    
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ], $messages);
    
        $authUser = $request->user();
    
        $createdRole = DB::transaction(function () use ($data, $authUser) {
            $role = Role::create([
                'name' => trim($data['name']),
                'guard_name' => 'sanctum',
                'nombre_mostrar' => trim($data['nombre_mostrar']),
                'descripcion' => trim($data['descripcion']),
            ]);
    
            $role->syncPermissions($data['permissions'] ?? []);
            $role->load('permissions:id,name');
    
            $this->createHistoryEntry(
                roleId: (int) $role->id,
                actorId: $authUser?->id,
                action: 'created',
                snapshot: $this->serializeRoleSnapshot($role),
                changes: null
            );
    
            return $role;
        });
    
        return response()->json([
            'ok' => true,
            'role' => [
                'id' => $createdRole->id,
                'name' => $createdRole->name,
                'nombre_mostrar' => $createdRole->nombre_mostrar,
                'descripcion' => $createdRole->descripcion,
                'permissions' => $createdRole->permissions->pluck('name')->values(),
                'users_count' => $this->countUsersByRoleId((int) $createdRole->id),
                'created_at' => $createdRole->created_at,
            ],
        ], 201);
    }

    public function show(Role $role)
    {
        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'nombre_mostrar' => $role->nombre_mostrar,
                'descripcion' => $role->descripcion,
                'permissions' => $role->permissions()->pluck('name')->values(),
                'users_count' => $this->countUsersByRoleId((int) $role->id),
            ],
            'all_permissions' => Permission::query()
                ->where('guard_name', 'sanctum')
                ->orderBy('name')
                ->pluck('name')
                ->values(),
            'is_admin_role' => $role->name === 'Administrador',
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Administrador') {
            return response()->json([
                'message' => 'No puedes modificar el rol Administrador.'
            ], 422);
        }

        $messages = [
            'name.required' => 'El nombre interno del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'nombre_mostrar.required' => 'El nombre a mostrar es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'permissions.array' => 'El formato de permisos no es válido.',
            'permissions.*.exists' => 'Uno o más permisos no existen.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ], $messages);

        $authUser = $request->user();

        DB::transaction(function () use ($role, $data, $authUser) {
            $role->load('permissions:id,name');
            $before = $this->serializeRoleSnapshot($role);

            $role->name = trim($data['name']);
            $role->nombre_mostrar = trim($data['nombre_mostrar']);
            $role->descripcion = trim($data['descripcion']);
            $role->save();

            $role->syncPermissions($data['permissions'] ?? []);
            $role->load('permissions:id,name');

            $after = $this->serializeRoleSnapshot($role);
            $changes = $this->buildRoleChanges($before, $after);

            if (!empty($changes)) {
                $this->createHistoryEntry(
                    roleId: (int) $role->id,
                    actorId: $authUser?->id,
                    action: 'updated',
                    snapshot: $after,
                    changes: $changes
                );
            }
        });

        return response()->json([
            'ok' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'nombre_mostrar' => $role->nombre_mostrar,
                'descripcion' => $role->descripcion,
                'permissions' => $role->permissions()->pluck('name')->values(),
                'users_count' => $this->countUsersByRoleId((int) $role->id),
                'created_at' => $role->created_at,
            ],
        ]);
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->guard_name !== 'sanctum') {
            return response()->json([
                'message' => 'Solo se pueden eliminar roles del guard sanctum.'
            ], 422);
        }
    
        if ($role->name === 'Administrador') {
            return response()->json([
                'message' => 'No se puede eliminar el rol Administrador.'
            ], 422);
        }
    
        $usersCount = $this->countUsersByRoleId((int) $role->id);
    
        if ($usersCount > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque está asignado a uno o más usuarios.'
            ], 422);
        }
    
        $authUser = $request->user();
    
        DB::transaction(function () use ($role, $authUser) {
            $snapshot = $this->serializeRoleSnapshot($role);
    
            $this->createHistoryEntry(
                roleId: (int) $role->id,
                actorId: $authUser?->id,
                action: 'deleted',
                snapshot: $snapshot,
                changes: null
            );
    
            DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->delete();
    
            DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->delete();
    
            DB::table('roles')
                ->where('id', $role->id)
                ->delete();
        });
    
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    
        return response()->json(['ok' => true]);
    }
}