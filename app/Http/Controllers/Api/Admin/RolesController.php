<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
        ]);

        $role = Role::create(['name' => trim($data['name'])]);

        return response()->json([
            'ok' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'created_at' => $role->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        // Protege rol crítico (opcional, pero recomendado)
        if ($role->name === 'Administrador') {
            return response()->json(['message' => 'No puedes renombrar el rol Administrador.'], 422);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->name = trim($data['name']);
        $role->save();

        return response()->json([
            'ok' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'created_at' => $role->created_at,
            ],
        ]);
    }

    public function destroy(Role $role)
    {
        // Protege rol crítico
        if ($role->name === 'Administrador') {
            return response()->json(['message' => 'No puedes eliminar el rol Administrador.'], 422);
        }

        $role->delete();

        return response()->json(['ok' => true]);
    }
}