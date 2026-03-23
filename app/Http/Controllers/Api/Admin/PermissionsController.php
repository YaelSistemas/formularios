<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
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

        $permission = Permission::create([
            'name' => trim($data['name']),
            'guard_name' => 'sanctum',
        ]);

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

        $permission->name = trim($data['name']);
        $permission->guard_name = 'sanctum';
        $permission->save();

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

    public function destroy(Permission $permission)
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

        $permission->delete();

        return response()->json(['ok' => true]);
    }
}