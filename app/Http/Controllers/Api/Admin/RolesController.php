<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    protected function countUsersByRoleId(int $roleId): int
    {
        return DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->where('model_type', \App\Models\User::class)
            ->count();
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

        $role = Role::create([
            'name' => trim($data['name']),
            'guard_name' => 'sanctum',
            'nombre_mostrar' => trim($data['nombre_mostrar']),
            'descripcion' => trim($data['descripcion']),
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

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

        $role->name = trim($data['name']);
        $role->nombre_mostrar = trim($data['nombre_mostrar']);
        $role->descripcion = trim($data['descripcion']);
        $role->save();

        $role->syncPermissions($data['permissions'] ?? []);

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

    public function destroy(Role $role)
    {
        if ($role->name === 'Administrador') {
            return response()->json([
                'message' => 'No puedes eliminar el rol Administrador.'
            ], 422);
        }

        $usersCount = $this->countUsersByRoleId((int) $role->id);

        if ($usersCount > 0) {
            return response()->json([
                'message' => 'No puedes eliminar un rol que ya está asignado a uno o más usuarios.'
            ], 422);
        }

        $role->delete();

        return response()->json(['ok' => true]);
    }
}