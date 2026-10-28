<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\UserControlAcceso;
use Illuminate\Support\Facades\DB;
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
            
                'controlAccesos' => function ($query) {
                    $query
                        ->with([
                            'empresa:id,nombre,razon_social,activo',
                
                            'unidadServicio:id,nombre,descripcion,activo',
                
                            'turno:id,nombre,descripcion,hora_inicio,hora_fin,horas_jornada,minutos_comida,comida_cuenta_jornada,vigente_desde,vigente_hasta,activo',
                        ])
                        ->orderByDesc('fecha_inicio')
                        ->orderByDesc('id');
                },
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
                
                'control_acceso_actual' => $this->formatControlAcceso(
                    $this->findCurrentControlAcceso($u->controlAccesos)
                ),
                
                'control_acceso_configuracion' => $this->formatControlAcceso(
                    $u->controlAccesos->first()
                ),
                
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
        $request->merge([
            'email' => trim((string) $request->input('email', '')),
        ]);

        $messages = [
            'name.required' => 'No se puede crear el usuario porque falta el nombre.',
            'email.required' => 'No se puede crear el usuario porque falta el correo o nombre de usuario.',
            'email.string' => 'El correo o nombre de usuario no es válido.',
            'email.unique' => 'El correo o nombre de usuario ya está registrado.',
            'password.required' => 'No se puede crear el usuario porque falta la contraseña.',
            'password.min' => 'La contraseña debe tener al menos 1 caracteres.',

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

            'control_acceso.empresa_id.required' =>
                'Falta seleccionar la empresa laboral.',
            
            'control_acceso.empresa_id.exists' =>
                'La empresa laboral seleccionada no existe.',

            'control_acceso.unidad_servicio_id.required' =>
                'Falta seleccionar la unidad laboral.',
            
            'control_acceso.unidad_servicio_id.exists' =>
                'La unidad laboral seleccionada no existe.',
            
            'control_acceso.turno_id.required' =>
                'Falta seleccionar el turno.',
            
            'control_acceso.turno_id.exists' =>
                'El turno seleccionado no existe.',
            
            'control_acceso.tipo_trabajo.required' =>
                'Falta seleccionar el tipo de trabajo.',
            
            'control_acceso.tipo_trabajo.in' =>
                'El tipo de trabajo seleccionado no es válido.',
            
            'control_acceso.kiosco_habilitado.required' =>
                'Falta indicar si el usuario estará habilitado en Kiosco.',
            
            'control_acceso.fecha_inicio.required' =>
                'Falta indicar la fecha inicial de la configuración de Control de acceso.',
            
            'control_acceso.fecha_fin.after_or_equal' =>
                'La fecha final de Control de acceso no puede ser anterior a la fecha inicial.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email'),
                function ($attribute, $value, $fail) {
                    /*
                     * Si contiene @, se considera correo y debe tener
                     * un formato válido. Si no contiene @, se considera
                     * nombre de usuario.
                     */
                    if (
                        str_contains($value, '@') &&
                        !filter_var($value, FILTER_VALIDATE_EMAIL)
                    ) {
                        $fail('El correo no tiene un formato válido.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:1'],

            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            'activo' => ['required', 'boolean'],

            'empresa_ids' => ['required', 'array', 'min:1'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['required', 'array', 'min:1'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],

            'unidad_servicio_ids' => ['required', 'array', 'min:1'],
            'unidad_servicio_ids.*' => ['integer', Rule::exists('unidades_servicio', 'id')],

            'control_acceso' => [
                'nullable',
                'array',
            ],

            'control_acceso.id' => [
                'nullable',
                'integer',
                Rule::exists('user_control_accesos', 'id'),
            ],
            
            'control_acceso.empresa_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('empresas', 'id'),
            ],

            'control_acceso.unidad_servicio_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('unidades_servicio', 'id'),
            ],
            
            'control_acceso.turno_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('turnos', 'id'),
            ],
            
            'control_acceso.tipo_trabajo' => [
                'required_with:control_acceso',
                Rule::in([
                    'fijo',
                    'mixto_salidas',
                ]),
            ],
            
            'control_acceso.kiosco_habilitado' => [
                'required_with:control_acceso',
                'boolean',
            ],
            
            'control_acceso.fecha_inicio' => [
                'required_with:control_acceso',
                'date',
            ],
            
            'control_acceso.fecha_fin' => [
                'nullable',
                'date',
                'after_or_equal:control_acceso.fecha_inicio',
            ],
        ], $messages);

        $user = DB::transaction(function () use ($data, $request) {
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
        
            if (!empty($data['control_acceso'])) {
                $this->createControlAcceso(
                    $user,
                    $data['control_acceso']
                );
            }
        
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
        
            return $user;
        });

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
        $request->merge([
            'email' => trim((string) $request->input('email', '')),
        ]);

        $messages = [
            'name.required' => 'No se puede actualizar el usuario porque falta el nombre.',
            'email.required' => 'No se puede actualizar el usuario porque falta el correo o nombre de usuario.',
            'email.string' => 'El correo o nombre de usuario no es válido.',
            'email.unique' => 'El correo o nombre de usuario ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 1 caracteres.',

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

            'control_acceso.empresa_id.required' =>
                'Falta seleccionar la empresa laboral.',
            
            'control_acceso.empresa_id.exists' =>
                'La empresa laboral seleccionada no existe.',

            'control_acceso.unidad_servicio_id.required' =>
                'Falta seleccionar la unidad laboral.',
            
            'control_acceso.unidad_servicio_id.exists' =>
                'La unidad laboral seleccionada no existe.',
            
            'control_acceso.turno_id.required' =>
                'Falta seleccionar el turno.',
            
            'control_acceso.turno_id.exists' =>
                'El turno seleccionado no existe.',
            
            'control_acceso.tipo_trabajo.required' =>
                'Falta seleccionar el tipo de trabajo.',
            
            'control_acceso.tipo_trabajo.in' =>
                'El tipo de trabajo seleccionado no es válido.',
            
            'control_acceso.kiosco_habilitado.required' =>
                'Falta indicar si el usuario estará habilitado en Kiosco.',
            
            'control_acceso.fecha_inicio.required' =>
                'Falta indicar la fecha inicial de la configuración de Control de acceso.',
            
            'control_acceso.fecha_fin.after_or_equal' =>
                'La fecha final de Control de acceso no puede ser anterior a la fecha inicial.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                function ($attribute, $value, $fail) {
                    /*
                     * Si contiene @, debe ser un correo válido.
                     * Si no contiene @, se acepta como nombre de usuario.
                     */
                    if (
                        str_contains($value, '@') &&
                        !filter_var($value, FILTER_VALIDATE_EMAIL)
                    ) {
                        $fail('El correo no tiene un formato válido.');
                    }
                },
            ],
            'password' => ['nullable', 'string', 'min:1'],

            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            'activo' => ['required', 'boolean'],

            'empresa_ids' => ['required', 'array', 'min:1'],
            'empresa_ids.*' => ['integer', Rule::exists('empresas', 'id')],

            'grupo_ids' => ['required', 'array', 'min:1'],
            'grupo_ids.*' => ['integer', Rule::exists('grupos', 'id')],

            'unidad_servicio_ids' => ['required', 'array', 'min:1'],
            'unidad_servicio_ids.*' => ['integer', Rule::exists('unidades_servicio', 'id')],

            'control_acceso' => [
                'nullable',
                'array',
            ],
            
            'control_acceso.id' => [
                'nullable',
                'integer',
                Rule::exists('user_control_accesos', 'id'),
            ],

            'control_acceso.empresa_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('empresas', 'id'),
            ],

            'control_acceso.unidad_servicio_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('unidades_servicio', 'id'),
            ],
            
            'control_acceso.turno_id' => [
                'required_with:control_acceso',
                'integer',
                Rule::exists('turnos', 'id'),
            ],
            
            'control_acceso.tipo_trabajo' => [
                'required_with:control_acceso',
                Rule::in([
                    'fijo',
                    'mixto_salidas',
                ]),
            ],
            
            'control_acceso.kiosco_habilitado' => [
                'required_with:control_acceso',
                'boolean',
            ],
            
            'control_acceso.fecha_inicio' => [
                'required_with:control_acceso',
                'date',
            ],
            
            'control_acceso.fecha_fin' => [
                'nullable',
                'date',
                'after_or_equal:control_acceso.fecha_inicio',
            ],
        ], $messages);

        $this->loadUserRelations($user);
        $beforeSnapshot = $this->buildUserSnapshot($user);
        $passwordChanged = !empty($data['password'] ?? null);

        DB::transaction(function () use (
            $user,
            $data,
            $request,
            $beforeSnapshot,
            $passwordChanged
        ) {
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
        
            if (!empty($data['control_acceso'])) {
                $this->updateControlAcceso(
                    $user,
                    $data['control_acceso']
                );
            }
        
            $user->load([
                'empresas:id,nombre,razon_social,activo',
                'grupos:id,nombre,nombre_mostrar,activo',
                'unidadesServicio:id,nombre,descripcion,activo',
            ]);
        
            $afterSnapshot =
                $this->buildUserSnapshot($user);
        
            $changes =
                $this->buildUserChanges(
                    $beforeSnapshot,
                    $afterSnapshot,
                    $passwordChanged
                );
        
            if (!empty($changes)) {
                $this->recordUserHistory(
                    user: $user,
                    actor: $request->user(),
                    action: 'updated',
                    snapshot: $afterSnapshot,
                    changes: $changes,
                );
            }
        });

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
    
            'controlAccesos' => function ($query) {
                $query
                    ->with([
                        'empresa:id,nombre,razon_social',
                        'unidadServicio:id,nombre',
                        'turno:id,nombre',
                    ])
                    ->orderByDesc('fecha_inicio')
                    ->orderByDesc('id');
            },
        ]);
    }

    private function buildUserSnapshot(User $user): array
    {
        $this->loadUserRelations($user);
    
        /*
         * La relación viene ordenada por fecha_inicio DESC e id DESC,
         * así que el primer elemento representa la configuración
         * laboral más reciente.
         */
        $control = $user->controlAccesos->first();
    
        $tipoTrabajo = null;
    
        if ($control) {
            $tipoTrabajo = match ($control->tipo_trabajo) {
                'fijo' => 'Fijo',
                'mixto_salidas' => 'Mixto / Salidas',
                default => $control->tipo_trabajo,
            };
        }
    
        $vigenciaControl = null;
    
        if ($control) {
            $inicio = $control->fecha_inicio
                ? $control->fecha_inicio->format('d/m/Y')
                : '—';
    
            $fin = $control->fecha_fin
                ? $control->fecha_fin->format('d/m/Y')
                : 'Sin fecha final';
    
            $vigenciaControl = "{$inicio} → {$fin}";
        }
    
        return [
            'name' => $user->name,
    
            'email' => $user->email,
    
            'roles' => $user->getRoleNames()
                ->sort()
                ->values()
                ->all(),
    
            /*
             * Estas siguen siendo exclusivamente las unidades
             * utilizadas para Formularios.
             */
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
    
            /*
             * Control de acceso
             */
            'control_empresa_laboral' =>
                $control?->empresa?->nombre,
            
            'control_unidad_laboral' =>
                $control?->unidadServicio?->nombre,
            
            'control_turno' =>
                $control?->turno?->nombre,
    
            'control_tipo_trabajo' =>
                $tipoTrabajo,
    
            'control_kiosco' =>
                $control
                    ? (
                        $control->kiosco_habilitado
                            ? 'Habilitado'
                            : 'No habilitado'
                    )
                    : null,
    
            'control_vigencia' =>
                $vigenciaControl,
    
            'activo' => (bool) ($user->activo ?? true),
        ];
    }

    private function userFieldMeta(): array
    {
        return [
            'name' => [
                'label' => 'Nombre',
                'type' => 'text',
            ],
    
            'email' => [
                'label' => 'Correo / Usuario',
                'type' => 'text',
            ],
    
            'roles' => [
                'label' => 'Rol',
                'type' => 'list',
            ],
    
            'unidades_servicio' => [
                'label' => 'Unidad de servicio',
                'type' => 'list',
            ],
    
            'empresas' => [
                'label' => 'Empresa',
                'type' => 'list',
            ],
    
            'grupos' => [
                'label' => 'Grupo',
                'type' => 'list',
            ],
    
            /*
             * Control de acceso
             */
            'control_empresa_laboral' => [
                'label' => 'Empresa laboral',
                'type' => 'text',
            ],

            'control_unidad_laboral' => [
                'label' => 'Unidad laboral',
                'type' => 'text',
            ],
    
            'control_turno' => [
                'label' => 'Turno',
                'type' => 'text',
            ],
    
            'control_tipo_trabajo' => [
                'label' => 'Tipo de trabajo',
                'type' => 'text',
            ],
    
            'control_kiosco' => [
                'label' => 'Kiosco',
                'type' => 'text',
            ],
    
            'control_vigencia' => [
                'label' => 'Vigencia de Control de acceso',
                'type' => 'text',
            ],
    
            'activo' => [
                'label' => 'Estado',
                'type' => 'boolean',
            ],
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

    private function findCurrentControlAcceso($items)
    {
        $today = now('America/Mexico_City')
            ->toDateString();
    
        return collect($items)->first(function ($item) use ($today) {
            $fechaInicio = $item->fecha_inicio
                ? $item->fecha_inicio->format('Y-m-d')
                : null;
    
            $fechaFin = $item->fecha_fin
                ? $item->fecha_fin->format('Y-m-d')
                : null;
    
            if (!$fechaInicio) {
                return false;
            }
    
            return $fechaInicio <= $today
                && ($fechaFin === null || $fechaFin >= $today);
        });
    }
    
    private function formatControlAcceso($config): ?array
    {
        if (!$config) {
            return null;
        }
    
        return [
            'id' => $config->id,
        
            'empresa_id' => $config->empresa_id,
        
            'unidad_servicio_id' => $config->unidad_servicio_id,
        
            'turno_id' => $config->turno_id,
            
            'tipo_trabajo' => $config->tipo_trabajo,
    
            'kiosco_habilitado' => (bool) $config->kiosco_habilitado,
    
            'fecha_inicio' => $config->fecha_inicio
                ? $config->fecha_inicio->format('Y-m-d')
                : null,
    
            'fecha_fin' => $config->fecha_fin
                ? $config->fecha_fin->format('Y-m-d')
                : null,
    
            'empresa_laboral' => $config->empresa
                ? [
                    'id' => $config->empresa->id,
                    'nombre' => $config->empresa->nombre,
                    'razon_social' => $config->empresa->razon_social,
                    'activo' => (bool) $config->empresa->activo,
                ]
                : null,

            'unidad_laboral' => $config->unidadServicio
                ? [
                    'id' => $config->unidadServicio->id,
                    'nombre' => $config->unidadServicio->nombre,
                    'descripcion' => $config->unidadServicio->descripcion,
                    'activo' => (bool) $config->unidadServicio->activo,
                ]
                : null,
    
            'turno' => $config->turno
                ? [
                    'id' => $config->turno->id,
                    'nombre' => $config->turno->nombre,
                    'descripcion' => $config->turno->descripcion,
                    'hora_inicio' => $config->turno->hora_inicio,
                    'hora_fin' => $config->turno->hora_fin,
                    'horas_jornada' => $config->turno->horas_jornada,
                    'minutos_comida' => $config->turno->minutos_comida,
                    'comida_cuenta_jornada' => (bool) $config->turno->comida_cuenta_jornada,
                    'activo' => (bool) $config->turno->activo,
                ]
                : null,
        ];
    }

    private function createControlAcceso(
        User $user,
        array $data
    ): UserControlAcceso {
        $this->validateControlAccesoOverlap(
            $user,
            $data
        );
    
        return UserControlAcceso::create([
            'user_id' => $user->id,
        
            'empresa_id' =>
                $data['empresa_id'],
        
            'unidad_servicio_id' =>
                $data['unidad_servicio_id'],
    
            'turno_id' =>
                $data['turno_id'],
    
            'tipo_trabajo' =>
                $data['tipo_trabajo'],
    
            'kiosco_habilitado' =>
                (bool) $data['kiosco_habilitado'],
    
            'fecha_inicio' =>
                $data['fecha_inicio'],
    
            'fecha_fin' =>
                $data['fecha_fin'] ?? null,
        ]);
    }
    
    private function updateControlAcceso(
        User $user,
        array $data
    ): UserControlAcceso {
        $actual = null;
        
        /*
         * Si el formulario nos indicó exactamente qué
         * configuración estamos editando, usamos esa.
         */
        if (!empty($data['id'])) {
            $actual = UserControlAcceso::query()
                ->where('id', $data['id'])
                ->where('user_id', $user->id)
                ->first();
        
            if (!$actual) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'control_acceso.id' =>
                        'La configuración de Control de acceso no pertenece a este usuario.',
                ]);
            }
        }
        
        /*
         * Compatibilidad por si alguna petición antigua
         * todavía no envía el ID.
         */
        if (!$actual) {
            $actual = UserControlAcceso::query()
                ->where('user_id', $user->id)
                ->orderByDesc('fecha_inicio')
                ->orderByDesc('id')
                ->first();
        }
    
        /*
         * Si todavía no existe configuración,
         * simplemente creamos la primera.
         */
        if (!$actual) {
            return $this->createControlAcceso(
                $user,
                $data
            );
        }
    
        $nuevaFechaInicio =
            $data['fecha_inicio'];
    
        $fechaInicioActual =
            $actual->fecha_inicio
                ->format('Y-m-d');
    
        /*
         * Si mantiene exactamente la misma fecha inicial,
         * interpretamos que está corrigiendo esa misma
         * configuración y actualizamos el registro.
         */
        if (
            $nuevaFechaInicio ===
            $fechaInicioActual
        ) {
            $this->validateControlAccesoOverlap(
                $user,
                $data,
                $actual->id
            );
    
            $actual->update([
                'empresa_id' =>
                    $data['empresa_id'],
            
                'unidad_servicio_id' =>
                    $data['unidad_servicio_id'],
            
                'turno_id' =>
                    $data['turno_id'],
    
                'tipo_trabajo' =>
                    $data['tipo_trabajo'],
    
                'kiosco_habilitado' =>
                    (bool) $data['kiosco_habilitado'],
    
                'fecha_inicio' =>
                    $data['fecha_inicio'],
    
                'fecha_fin' =>
                    $data['fecha_fin'] ?? null,
            ]);
    
            return $actual->fresh();
        }
    
        /*
         * Para conservar historial, una nueva configuración
         * debe iniciar después de la configuración vigente.
         */
        if (
            $nuevaFechaInicio <
            $fechaInicioActual
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'control_acceso.fecha_inicio' =>
                    'La nueva configuración no puede comenzar antes de la configuración laboral vigente.',
            ]);
        }
    
        /*
         * Cerramos automáticamente la configuración anterior
         * un día antes de que empiece la nueva.
         */
        $finAnterior = \Carbon\Carbon::parse(
            $nuevaFechaInicio,
            'America/Mexico_City'
        )
            ->subDay()
            ->toDateString();
    
        $actual->fecha_fin =
            $finAnterior;
    
        $actual->save();
    
        return $this->createControlAcceso(
            $user,
            $data
        );
    }
    
    private function validateControlAccesoOverlap(
        User $user,
        array $data,
        ?int $ignoreId = null
    ): void {
        $inicio =
            $data['fecha_inicio'];
    
        $fin =
            $data['fecha_fin'] ?? null;
    
        $query = UserControlAcceso::query()
            ->where(
                'user_id',
                $user->id
            );
    
        if ($ignoreId) {
            $query->where(
                'id',
                '!=',
                $ignoreId
            );
        }
    
        /*
         * Dos periodos se cruzan cuando:
         *
         * inicio_existente <= fin_nuevo
         * Y
         * fin_existente >= inicio_nuevo
         *
         * considerando NULL como "sin fecha final".
         */
        $query->where(function ($q) use ($inicio, $fin) {
            if ($fin) {
                $q->whereDate(
                    'fecha_inicio',
                    '<=',
                    $fin
                );
            }
    
            $q->where(function ($qq) use ($inicio) {
                $qq
                    ->whereNull('fecha_fin')
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        $inicio
                    );
            });
        });
    
        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'control_acceso.fecha_inicio' =>
                    'La vigencia de esta configuración se cruza con otra configuración laboral del usuario.',
            ]);
        }
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