<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(5, min(100, $perPage));

        $users = User::query()
            ->with([
                'empresas:id,nombre,razon_social,activo',
                'grupos:id,nombre,nombre_mostrar,activo',
                'unidadesServicio:id,nombre,descripcion,activo',
                'roles:id,name',
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

    public function history(Request $request, User $user)
    {
        $authUser = $request->user();

        if (!$authUser || !$authUser->hasRole('Administrador')) {
            return response()->json([
                'message' => 'No tienes permiso para ver el historial de usuarios.',
            ], 403);
        }

        $items = UserHistory::query()
            ->with(['actor:id,name,email'])
            ->where('user_id', $user->id)
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
            'name' => trim($data['name']),
            'email' => trim($data['email']),
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

        $this->recordUserHistory(
            user: $user,
            actor: $request->user(),
            action: 'created',
            snapshot: $this->buildUserSnapshot($user),
            changes: [
                [
                    'field' => 'password',
                    'label' => 'Contraseña',
                    'type' => 'password',
                    'old' => null,
                    'new' => 'Establecida',
                ],
            ],
        );

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

        $this->loadUserRelations($user);
        $beforeSnapshot = $this->buildUserSnapshot($user);
        $passwordChanged = !empty($data['password'] ?? null);

        $user->name = trim($data['name']);
        $user->email = trim($data['email']);
        $user->activo = (bool) $data['activo'];

        if ($passwordChanged) {
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

        $afterSnapshot = $this->buildUserSnapshot($user);
        $changes = $this->buildUserChanges($beforeSnapshot, $afterSnapshot, $passwordChanged);

        if (!empty($changes)) {
            $this->recordUserHistory(
                user: $user,
                actor: $request->user(),
                action: 'updated',
                snapshot: $afterSnapshot,
                changes: $changes,
            );
        }

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

    public function destroy(Request $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser && (int) $authUser->id === (int) $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario.',
            ], 422);
        }

        if ($user->hasRole('Administrador')) {
            return response()->json([
                'message' => 'No se puede eliminar un usuario con rol Administrador.',
            ], 422);
        }

        $this->loadUserRelations($user);
        $beforeDeleteSnapshot = $this->buildUserSnapshot($user);

        $this->recordUserHistory(
            user: $user,
            actor: $request->user(),
            action: 'deleted',
            snapshot: $beforeDeleteSnapshot,
            changes: null,
        );

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

    private function loadUserRelations(User $user): User
    {
        return $user->load([
            'empresas:id,nombre,razon_social,activo',
            'grupos:id,nombre,nombre_mostrar,activo',
            'unidadesServicio:id,nombre,descripcion,activo',
            'roles:id,name',
        ]);
    }

    private function buildUserSnapshot(User $user): array
    {
        $this->loadUserRelations($user);

        return [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->sort()->values()->all(),
            'unidades_servicio' => $user->unidadesServicio
                ->map(fn ($us) => $us->nombre)
                ->filter()
                ->sort()
                ->values()
                ->all(),
            'empresas' => $user->empresas
                ->map(fn ($e) => $e->nombre)
                ->filter()
                ->sort()
                ->values()
                ->all(),
            'grupos' => $user->grupos
                ->map(fn ($g) => $g->nombre_mostrar ?: $g->nombre)
                ->filter()
                ->sort()
                ->values()
                ->all(),
            'activo' => (bool) ($user->activo ?? true),
        ];
    }

    private function userFieldMeta(): array
    {
        return [
            'name' => ['label' => 'Nombre', 'type' => 'text'],
            'email' => ['label' => 'Correo', 'type' => 'text'],
            'roles' => ['label' => 'Rol', 'type' => 'list'],
            'unidades_servicio' => ['label' => 'Unidad de servicio', 'type' => 'list'],
            'empresas' => ['label' => 'Empresa', 'type' => 'list'],
            'grupos' => ['label' => 'Grupo', 'type' => 'list'],
            'activo' => ['label' => 'Estado', 'type' => 'boolean'],
        ];
    }

    private function normalizeForComparison($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = array_map(fn ($item) => $this->normalizeForComparison($item), $value);

        if (array_is_list($normalized)) {
            usort($normalized, function ($a, $b) {
                return strcmp(json_encode($a), json_encode($b));
            });
        } else {
            ksort($normalized);
        }

        return $normalized;
    }

    private function valuesDiffer($old, $new): bool
    {
        return json_encode($this->normalizeForComparison($old)) !==
            json_encode($this->normalizeForComparison($new));
    }

    private function buildUserChanges(array $before, array $after, bool $passwordChanged = false): array
    {
        $changes = [];
        $meta = $this->userFieldMeta();

        foreach ($meta as $field => $config) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            if ($this->valuesDiffer($old, $new)) {
                $changes[] = [
                    'field' => $field,
                    'label' => $config['label'],
                    'type' => $config['type'],
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        if ($passwordChanged) {
            $changes[] = [
                'field' => 'password',
                'label' => 'Contraseña',
                'type' => 'password',
                'old' => null,
                'new' => 'Actualizada',
            ];
        }

        return $changes;
    }

    private function recordUserHistory(
        User $user,
        ?User $actor,
        string $action,
        array $snapshot,
        ?array $changes = null
    ): void {
        UserHistory::create([
            'user_id' => $user->id,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'snapshot' => $snapshot,
            'changes' => $changes,
        ]);
    }
}