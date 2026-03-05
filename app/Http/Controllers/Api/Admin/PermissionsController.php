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

        $permissions = Permission::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'created_at' => $p->created_at,
            ]);

        return response()->json(['permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')],
        ]);

        $permission = Permission::create(['name' => trim($data['name'])]);

        return response()->json([
            'ok' => true,
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        $permission->name = trim($data['name']);
        $permission->save();

        return response()->json([
            'ok' => true,
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at,
            ],
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['ok' => true]);
    }
}