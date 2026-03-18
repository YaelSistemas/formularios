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
            ->with([
                'empresas:id,nombre,razon_social,activo',
                'grupos:id,nombre,nombre_mostrar,activo',
                'unidadesServicio:id,nombre,descripcion,activo',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('roles', function ($r) use ($q) {
                            $r->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('unidadesServicio', function ($us) use ($q) {
                            $us->where('nombre', 'like', "%{$q}%")
                               ->orWhere('descripcion', 'like', "%{$q}%");
                        })
                        ->orWhereHas('empresas', function ($e) use ($q) {
                            $e->where('nombre', 'like', "%{$q}%")
                              ->orWhere('razon_social', 'like', "%{$q}%");
                        })
                        ->orWhereHas('grupos', function ($g) use ($q) {
                            $g->where('nombre', 'like', "%{$q}%")
                              ->orWhere('nombre_mostrar', 'like', "%{$q}%")
                              ->orWhere('descripcion', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $users->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'activo' => (bool) ($u->activo ?? true),
                'roles' => $u->getRoleNames()->values(),

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

                'unidades_servicio' => $u->unidadesServicio?->map(fn ($us) => [
                    'id' => $us->id,
                    'nombre' => $us->nombre,
                    'descripcion' => $us->descripcion,
                    'activo' => (bool) $us->activo,
                ])->values() ?? [],

                'created_at' => $u->created_at,
            ];
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'No se puede crear el usuario porque falta el nombre.',
            'email.required' => 'No se puede crear el usuario porque falta el correo.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'El correo ya está registrado.',
            'password.required' => 'No se puede crear el usuario porque falta la contraseña.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',

            'roles.required' => 'No se puede crear el usuario porque falta el rol.',
            'roles.array' => 'El rol enviado no es válido.',
            'roles.min' => 'No se puede crear el usuario porque falta el rol.',
            'roles.*.exists' => 'El rol seleccionado no existe.',

            'activo.required' => 'No se puede crear el usuario porque falta el estado.',
            'activo.boolean' => 'El estado enviado no es válido.',

            'empresa_ids.required' => 'No se puede crear el usuario porque falta la empresa.',
            'empresa_ids.array' => 'La empresa enviada no es válida.',
            'empresa_ids.min' => 'No se puede crear el usuario porque falta la empresa.',
            'empresa_ids.*.exists' => 'La empresa seleccionada no existe.',

            'grupo_ids.required' => 'No se puede crear el usuario porque falta el grupo.',
            'grupo_ids.array' => 'El grupo enviado no es válido.',
            'grupo_ids.min' => 'No se puede crear el usuario porque falta el grupo.',
            'grupo_ids.*.exists' => 'El grupo seleccionado no existe.',

            'unidad_servicio_ids.required' => 'No se puede crear el usuario porque falta la unidad de servicio.',
            'unidad_servicio_ids.array' => 'La unidad de servicio enviada no es válida.',
            'unidad_servicio_ids.min' => 'No se puede crear el usuario porque falta la unidad de servicio.',
            'unidad_servicio_ids.*.exists' => 'La unidad de servicio seleccionada no existe.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],

            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            'activo' => ['required', 'boolean'],

            'empresa_ids' => ['required', 'array', 'min:1'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['required', 'array', 'min:1'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],

            'unidad_servicio_ids' => ['required', 'array', 'min:1'],
            'unidad_servicio_ids.*' => ['integer', Rule::exists('unidades_servicio', 'id')],
        ], $messages);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'activo' => (bool) $data['activo'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles']);
        $user->empresas()->sync($data['empresa_ids']);
        $user->grupos()->sync($data['grupo_ids']);
        $user->unidadesServicio()->sync($data['unidad_servicio_ids']);

        $user->load([
            'empresas:id,nombre,razon_social,activo',
            'grupos:id,nombre,nombre_mostrar,activo',
            'unidadesServicio:id,nombre,descripcion,activo',
        ]);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'activo' => (bool) ($user->activo ?? true),
                'roles' => $user->getRoleNames()->values(),

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

                'unidades_servicio' => $user->unidadesServicio->map(fn ($us) => [
                    'id' => $us->id,
                    'nombre' => $us->nombre,
                    'descripcion' => $us->descripcion,
                    'activo' => (bool) $us->activo,
                ])->values(),
            ],
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $messages = [
            'name.required' => 'No se puede actualizar el usuario porque falta el nombre.',
            'email.required' => 'No se puede actualizar el usuario porque falta el correo.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'El correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',

            'roles.required' => 'No se puede actualizar el usuario porque falta el rol.',
            'roles.array' => 'El rol enviado no es válido.',
            'roles.min' => 'No se puede actualizar el usuario porque falta el rol.',
            'roles.*.exists' => 'El rol seleccionado no existe.',

            'activo.required' => 'No se puede actualizar el usuario porque falta el estado.',
            'activo.boolean' => 'El estado enviado no es válido.',

            'empresa_ids.required' => 'No se puede actualizar el usuario porque falta la empresa.',
            'empresa_ids.array' => 'La empresa enviada no es válida.',
            'empresa_ids.min' => 'No se puede actualizar el usuario porque falta la empresa.',
            'empresa_ids.*.exists' => 'La empresa seleccionada no existe.',

            'grupo_ids.required' => 'No se puede actualizar el usuario porque falta el grupo.',
            'grupo_ids.array' => 'El grupo enviado no es válido.',
            'grupo_ids.min' => 'No se puede actualizar el usuario porque falta el grupo.',
            'grupo_ids.*.exists' => 'El grupo seleccionado no existe.',

            'unidad_servicio_ids.required' => 'No se puede actualizar el usuario porque falta la unidad de servicio.',
            'unidad_servicio_ids.array' => 'La unidad de servicio enviada no es válida.',
            'unidad_servicio_ids.min' => 'No se puede actualizar el usuario porque falta la unidad de servicio.',
            'unidad_servicio_ids.*.exists' => 'La unidad de servicio seleccionada no existe.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],

            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            'activo' => ['required', 'boolean'],

            'empresa_ids' => ['required', 'array', 'min:1'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['required', 'array', 'min:1'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],

            'unidad_servicio_ids' => ['required', 'array', 'min:1'],
            'unidad_servicio_ids.*' => ['integer', Rule::exists('unidades_servicio', 'id')],
        ], $messages);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->activo = (bool) $data['activo'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $user->syncRoles($data['roles']);
        $user->empresas()->sync($data['empresa_ids']);
        $user->grupos()->sync($data['grupo_ids']);
        $user->unidadesServicio()->sync($data['unidad_servicio_ids']);

        $user->load([
            'empresas:id,nombre,razon_social,activo',
            'grupos:id,nombre,nombre_mostrar,activo',
            'unidadesServicio:id,nombre,descripcion,activo',
        ]);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'activo' => (bool) ($user->activo ?? true),
                'roles' => $user->getRoleNames()->values(),

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

                'unidades_servicio' => $user->unidadesServicio->map(fn ($us) => [
                    'id' => $us->id,
                    'nombre' => $us->nombre,
                    'descripcion' => $us->descripcion,
                    'activo' => (bool) $us->activo,
                ])->values(),
            ],
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }

    public function roles()
    {
        $roles = Role::query()->orderBy('name')->pluck('name');

        return response()->json([
            'roles' => $roles,
        ]);
    }
}