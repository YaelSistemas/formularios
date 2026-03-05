<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionsController extends Controller
{
    public function show(Role $role)
    {
        if ($role->name === 'Administrador') {
            return response()->json([
                'role' => ['id' => $role->id, 'name' => $role->name],
                'is_admin_role' => true,
                'permissions' => ['*'],
                'all_permissions' => Permission::query()->orderBy('name')->pluck('name'),
            ]);
        }

        return response()->json([
            'role' => ['id' => $role->id, 'name' => $role->name],
            'is_admin_role' => false,
            'permissions' => $role->permissions()->pluck('name'),
            'all_permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Administrador') {
            return response()->json([
                'message' => 'El rol Administrador tiene acceso total por rol. No requiere permisos.'
            ], 422);
        }

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json([
            'ok' => true,
            'role' => ['id' => $role->id, 'name' => $role->name],
            'permissions' => $role->permissions()->pluck('name'),
        ]);
    }
}