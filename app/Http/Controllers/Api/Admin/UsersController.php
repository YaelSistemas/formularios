<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(50, $perPage));

        $users = User::query()
            ->with(['empresas:id,nombre,razon_social,activo', 'grupos:id,nombre,nombre_mostrar,activo'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // adjunta roles + empresas + grupos
        $users->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'activo' => (bool) ($u->activo ?? true),
                'roles' => $u->getRoleNames(),

                // ✅ para precargar en el frontend (AdminUsers.jsx)
                'empresas' => $u->empresas?->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'razon_social' => $e->razon_social,
                    'activo' => (bool) $e->activo,
                ])->values() ?? [],

                'grupos' => $u->grupos?->map(fn ($g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'nombre_mostrar' => $g->nombre_mostrar,
                    'activo' => (bool) $g->activo,
                ])->values() ?? [],

                'created_at' => $u->created_at,
            ];
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],

            // ✅ roles
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            // ✅ activo
            'activo' => ['nullable', 'boolean'],

            // ✅ relaciones
            'empresa_ids' => ['array'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['array'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'activo' => array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
            'password' => Hash::make($data['password']),
        ]);

        // roles
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // ✅ empresas / grupos
        if (array_key_exists('empresa_ids', $data)) {
            $user->empresas()->sync($data['empresa_ids'] ?? []);
        }
        if (array_key_exists('grupo_ids', $data)) {
            $user->grupos()->sync($data['grupo_ids'] ?? []);
        }

        $user->load(['empresas:id,nombre,razon_social,activo', 'grupos:id,nombre,nombre_mostrar,activo']);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'activo' => (bool) ($user->activo ?? true),
                'roles' => $user->getRoleNames(),
                'empresas' => $user->empresas->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'razon_social' => $e->razon_social,
                    'activo' => (bool) $e->activo,
                ])->values(),
                'grupos' => $user->grupos->map(fn ($g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'nombre_mostrar' => $g->nombre_mostrar,
                    'activo' => (bool) $g->activo,
                ])->values(),
            ],
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],

            // ✅ roles
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            // ✅ activo
            'activo' => ['nullable', 'boolean'],

            // ✅ relaciones
            'empresa_ids' => ['array'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['array'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        // ✅ activo (si viene)
        if (array_key_exists('activo', $data)) {
            $user->activo = (bool) $data['activo'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // roles (si viene)
        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        // ✅ empresas / grupos (si vienen)
        if (array_key_exists('empresa_ids', $data)) {
            $user->empresas()->sync($data['empresa_ids'] ?? []);
        }
        if (array_key_exists('grupo_ids', $data)) {
            $user->grupos()->sync($data['grupo_ids'] ?? []);
        }

        $user->load(['empresas:id,nombre,razon_social,activo', 'grupos:id,nombre,nombre_mostrar,activo']);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'activo' => (bool) ($user->activo ?? true),
                'roles' => $user->getRoleNames(),
                'empresas' => $user->empresas->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'razon_social' => $e->razon_social,
                    'activo' => (bool) $e->activo,
                ])->values(),
                'grupos' => $user->grupos->map(fn ($g) => [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'nombre_mostrar' => $g->nombre_mostrar,
                    'activo' => (bool) $g->activo,
                ])->values(),
            ],
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['ok' => true]);
    }

    public function roles()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        return response()->json(['roles' => $roles]);
    }
}