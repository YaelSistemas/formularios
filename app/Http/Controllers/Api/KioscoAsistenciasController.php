<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaHistory;
use App\Models\User;
use App\Models\UserControlAcceso;
use App\Models\AsistenciaIncidencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KioscoAsistenciasController extends Controller
{
    private const TIMEZONE = 'America/Mexico_City';

    /**
     * Empleados habilitados para Kiosco.
     */
    public function index(Request $request)
    {
        $today = now(self::TIMEZONE)->toDateString();

        $configuraciones = UserControlAcceso::query()
            ->with([
                'user:id,name,email,activo',
                'unidadServicio:id,nombre',

                'turno:id,nombre,hora_inicio,hora_fin,horas_jornada,minutos_comida,comida_cuenta_jornada,activo',
            ])
            ->where('kiosco_habilitado', true)
            ->whereDate('fecha_inicio', '<=', $today)
            ->where(function ($query) use ($today) {
                $query
                    ->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $today);
            })
            ->whereHas('user', function ($query) {
                $query->where('activo', true);
            })
            ->orderBy('user_id')
            ->get();

        $usuarios = $configuraciones
            ->map(function (UserControlAcceso $config) use ($today) {
                $user = $config->user;

                if (!$user) {
                    return null;
                }

                $actual = Asistencia::query()
                    ->where('user_id', $user->id)
                    ->whereNull('hora_salida')
                    ->orderByDesc('hora_entrada')
                    ->first();

                /*
                 * Asistencia registrada durante
                 * el día de hoy, aunque ya tenga
                 * hora de salida.
                 */
                $asistenciaHoy = Asistencia::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->whereDate(
                        'fecha',
                        $today
                    )
                    ->whereNotNull(
                        'hora_entrada'
                    )
                    ->orderByDesc(
                        'hora_entrada'
                    )
                    ->first();
                
                /*
                 * Incidencia asignada
                 * al empleado para hoy.
                 */
                $incidenciaHoy =
                    AsistenciaIncidencia::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->whereDate(
                            'fecha',
                            $today
                        )
                        ->first();

                $unidadLaboral =
                    $actual
                    && (
                        $actual
                            ->unidad_servicio_id_snapshot !== null
                        ||
                        $actual
                            ->unidad_servicio_nombre_snapshot !== null
                    )
                        ? [
                            'id' =>
                                $actual
                                    ->unidad_servicio_id_snapshot !== null
                                    ? (int) $actual
                                        ->unidad_servicio_id_snapshot
                                    : null,
                
                            'nombre' =>
                                $actual
                                    ->unidad_servicio_nombre_snapshot,
                        ]
                        : (
                            $config->unidadServicio
                                ? [
                                    'id' =>
                                        $config
                                            ->unidadServicio
                                            ->id,
                
                                    'nombre' =>
                                        $config
                                            ->unidadServicio
                                            ->nombre,
                                ]
                                : null
                        );
                
                $tipoTrabajo =
                    $actual
                        ? (
                            $actual
                                ->tipo_trabajo_snapshot
                            ?? $config->tipo_trabajo
                        )
                        : $config->tipo_trabajo;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,

                    'unidad_laboral' =>
                        $unidadLaboral,

                    'turno' => $config->turno
                        ? [
                            'id' => $config->turno->id,
                            'nombre' => $config->turno->nombre,
                            'hora_inicio' => $config->turno->hora_inicio,
                            'hora_fin' => $config->turno->hora_fin,

                            'horas_jornada' =>
                                (float) $config->turno->horas_jornada,

                            'minutos_comida' =>
                                (int) $config->turno->minutos_comida,

                            'comida_cuenta_jornada' =>
                                (bool) $config->turno->comida_cuenta_jornada,
                        ]
                        : null,

                    'tipo_trabajo' =>
                        $tipoTrabajo,

                    'asistencia_actual' => $actual
                        ? $this->formatAsistencia($actual)
                        : null,

                    'asistencia_hoy' =>
                        $asistenciaHoy
                            ? $this->formatAsistencia(
                                $asistenciaHoy
                            )
                            : null,
                    
                    'incidencia_hoy' =>
                        $incidenciaHoy
                            ? [
                                'id' =>
                                    $incidenciaHoy->id,
                    
                                'fecha' =>
                                    $incidenciaHoy
                                        ->fecha
                                        ->toDateString(),
                    
                                'tipo' =>
                                    $incidenciaHoy->tipo,
                    
                                'observaciones' =>
                                    $incidenciaHoy
                                        ->observaciones,
                            ]
                            : null,
                ];
            })
            ->filter()
            ->values();

            /*
             * También incluir jornadas que fueron
             * iniciadas por Kiosco y siguen abiertas,
             * aunque la configuración actual del
             * empleado ya haya cambiado.
             */
            $asistenciasAbiertasKiosco =
                Asistencia::query()
                    ->whereNull(
                        'hora_salida'
                    )
                    ->where(
                        'metodo_entrada',
                        'kiosco'
                    )
                    ->get();
            
            foreach (
                $asistenciasAbiertasKiosco
                as $asistencia
            ) {
                /*
                 * Si ya apareció mediante su
                 * configuración vigente, no duplicarlo.
                 */
                $yaExiste =
                    $usuarios->contains(
                        function ($item) use (
                            $asistencia
                        ) {
                            return
                                (int) $item['id']
                                ===
                                (int) $asistencia->user_id;
                        }
                    );
            
                if ($yaExiste) {
                    continue;
                }
            
                $user = User::query()
                    ->select([
                        'id',
                        'name',
                        'email',
                        'activo',
                    ])
                    ->find(
                        $asistencia->user_id
                    );
            
                if (!$user) {
                    continue;
                }
            
                $asistencia->loadMissing(
                    'controlAcceso.unidadServicio'
                );
            
                $controlAcceso =
                    $asistencia
                        ->controlAcceso;
            
                /*
                 * El turno se obtiene del snapshot
                 * histórico de la asistencia.
                 */
                $turno =
                    $this->getTurnoAsistencia(
                        $asistencia
                    );
            
                $usuarios->push([
                    'id' =>
                        $user->id,
            
                    'name' =>
                        $user->name,
            
                    'email' =>
                        $user->email,
            
                    'unidad_laboral' =>
                        (
                            $asistencia
                                ->unidad_servicio_id_snapshot !== null
                            ||
                            $asistencia
                                ->unidad_servicio_nombre_snapshot !== null
                        )
                            ? [
                                'id' =>
                                    $asistencia
                                        ->unidad_servicio_id_snapshot !== null
                                        ? (int) $asistencia
                                            ->unidad_servicio_id_snapshot
                                        : null,
                    
                                'nombre' =>
                                    $asistencia
                                        ->unidad_servicio_nombre_snapshot,
                            ]
                            : (
                                $controlAcceso
                                    ?->unidadServicio
                                    ? [
                                        'id' =>
                                            $controlAcceso
                                                ->unidadServicio
                                                ->id,
                    
                                        'nombre' =>
                                            $controlAcceso
                                                ->unidadServicio
                                                ->nombre,
                                    ]
                                    : null
                            ),
            
                    /*
                     * Como ya existe jornada,
                     * aquí usamos también el turno
                     * histórico.
                     */
                    'turno' =>
                        $turno
                            ? [
                                'id' =>
                                    $turno->id,
            
                                'nombre' =>
                                    $turno->nombre,
            
                                'hora_inicio' =>
                                    $turno->hora_inicio,
            
                                'hora_fin' =>
                                    $turno->hora_fin,
            
                                'horas_jornada' =>
                                    (float)
                                    $turno->horas_jornada,
            
                                'minutos_comida' =>
                                    (int)
                                    $turno->minutos_comida,
            
                                'comida_cuenta_jornada' =>
                                    (bool)
                                    $turno
                                        ->comida_cuenta_jornada,
                            ]
                            : null,
            
                    'tipo_trabajo' =>
                        $asistencia
                            ->tipo_trabajo_snapshot
                        ?? $controlAcceso
                            ?->tipo_trabajo,
            
                    'asistencia_actual' =>
                        $this->formatAsistencia(
                            $asistencia
                        ),
                ]);
            }
            
            /*
             * Mantener orden alfabético.
             */
            $usuarios =
                $usuarios
                    ->sortBy(
                        fn ($item) =>
                            mb_strtolower(
                                $item['name']
                                ?? ''
                            )
                    )
                    ->values();

        return response()->json([
            'usuarios' => $usuarios,
        ]);
    }

    private function guardarFirmaEntrada(
        string $firmaBase64
    ): string {
        if (
            !str_starts_with(
                $firmaBase64,
                'data:image/png;base64,'
            )
        ) {
            throw ValidationException::withMessages([
                'firma_entrada' =>
                    'La firma de entrada no tiene un formato válido.',
            ]);
        }
    
        $contenidoBase64 =
            substr(
                $firmaBase64,
                strpos(
                    $firmaBase64,
                    ','
                ) + 1
            );
    
        $contenido =
            base64_decode(
                $contenidoBase64,
                true
            );
    
        if (
            $contenido === false
            || strlen($contenido) === 0
        ) {
            throw ValidationException::withMessages([
                'firma_entrada' =>
                    'No fue posible procesar la firma de entrada.',
            ]);
        }
    
        if (
            strlen($contenido)
            > 2 * 1024 * 1024
        ) {
            throw ValidationException::withMessages([
                'firma_entrada' =>
                    'La firma de entrada es demasiado grande.',
            ]);
        }
    
        $ruta =
            'asistencias/firmas/entrada/'
            . Str::uuid()
            . '.png';
    
        $guardado =
            Storage::disk('public')
                ->put(
                    $ruta,
                    $contenido
                );
    
        if (!$guardado) {
            throw ValidationException::withMessages([
                'firma_entrada' =>
                    'No fue posible guardar la firma de entrada.',
            ]);
        }
    
        return $ruta;
    }

    private function guardarFotoEntrada(
        string $fotoBase64
    ): string {
        if (
            !str_starts_with(
                $fotoBase64,
                'data:image/jpeg;base64,'
            )
        ) {
            throw ValidationException::withMessages([
                'foto_entrada' =>
                    'La fotografía de entrada no tiene un formato válido.',
            ]);
        }
    
        $contenidoBase64 =
            substr(
                $fotoBase64,
                strpos(
                    $fotoBase64,
                    ','
                ) + 1
            );
    
        $contenido =
            base64_decode(
                $contenidoBase64,
                true
            );
    
        if (
            $contenido === false
            || strlen($contenido) === 0
        ) {
            throw ValidationException::withMessages([
                'foto_entrada' =>
                    'No fue posible procesar la fotografía de entrada.',
            ]);
        }
    
        if (
            strlen($contenido)
            > 5 * 1024 * 1024
        ) {
            throw ValidationException::withMessages([
                'foto_entrada' =>
                    'La fotografía de entrada es demasiado grande.',
            ]);
        }
    
        $ruta =
            'asistencias/fotos/entrada/'
            . Str::uuid()
            . '.jpg';
    
        $guardado =
            Storage::disk('public')
                ->put(
                    $ruta,
                    $contenido
                );
    
        if (!$guardado) {
            throw ValidationException::withMessages([
                'foto_entrada' =>
                    'No fue posible guardar la fotografía de entrada.',
            ]);
        }
    
        return $ruta;
    }

    /**
     * Registrar entrada mediante Kiosco.
     */
    public function entrada(Request $request, User $user)
    {
        $actor = $request->user();

        $data = $request->validate([
            'firma_entrada' => [
                'required',
                'string',
            ],
        
            'firma_capturada_at' => [
                'required',
                'date',
            ],
        
            'foto_entrada' => [
                'required',
                'string',
            ],
        
            'foto_capturada_at' => [
                'required',
                'date',
            ],
        
            'entrada_latitud' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
        
            'entrada_longitud' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        
            'entrada_precision_metros' => [
                'required',
                'numeric',
                'min:0',
                'max:100000',
            ],
        ]);

        $controlAcceso =
            $this->validateKioscoUser($user);

        $controlAcceso->loadMissing([
            'turno',
            'unidadServicio',
            'empresa',
        ]);
        
        $turno =
            $controlAcceso->turno;

        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado al usuario.',
            ], 422);
        }

        $asistenciaAbierta = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->exists();

        if ($asistenciaAbierta) {
            return response()->json([
                'message' =>
                    'El usuario ya tiene una asistencia en curso.',
            ], 409);
        }

        $ahora =
            now(self::TIMEZONE);
        
        $fechaActual =
            $ahora->toDateString();
        
        /*
         * No permitir entrada desde Kiosco
         * cuando el empleado tenga una
         * incidencia asignada para hoy.
         */
        $incidenciaHoy =
            AsistenciaIncidencia::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->whereDate(
                    'fecha',
                    $fechaActual
                )
                ->first();
        
        if ($incidenciaHoy) {
            $tipoLabel =
                match (
                    $incidenciaHoy->tipo
                ) {
                    'vacaciones' =>
                        'Vacaciones',
        
                    'incapacidad' =>
                        'Incapacidad',
        
                    'festivo' =>
                        'Día festivo',
        
                    default =>
                        'Incidencia',
                };
        
            return response()->json([
                'message' =>
                    "{$user->name} tiene registrada la incidencia {$tipoLabel} para el día de hoy. No puede registrar entrada.",
            ], 409);
        }

        /*
         * No permitir más de una entrada
         * por usuario en el mismo día,
         * incluso desde Kiosco.
         */
        $yaTieneEntradaHoy = Asistencia::query()
            ->where(
                'user_id',
                $user->id
            )
            ->whereDate(
                'fecha',
                $fechaActual
            )
            ->whereNotNull(
                'hora_entrada'
            )
            ->exists();
        
        if ($yaTieneEntradaHoy) {
            return response()->json([
                'message' =>
                    "El usuario {$user->name} ya registró una entrada el día de hoy. No puede registrar otra entrada.",
            ], 409);
        }

        /*
         * Un turno inactivo sí bloquea
         * una nueva entrada por Kiosco.
         */
        if (!(bool) $turno->activo) {
            return response()->json([
                'message' =>
                    "El turno de {$user->name} se encuentra inactivo.",
            ], 422);
        }
        
        /*
         * Validar vigencia del turno.
         */
        $vigenteDesde =
            $turno->vigente_desde
                ? Carbon::parse(
                    $turno->vigente_desde,
                    self::TIMEZONE
                )->toDateString()
                : null;
        
        $vigenteHasta =
            $turno->vigente_hasta
                ? Carbon::parse(
                    $turno->vigente_hasta,
                    self::TIMEZONE
                )->toDateString()
                : null;
        
        if (
            $vigenteDesde
            && $fechaActual < $vigenteDesde
        ) {
            return response()->json([
                'message' =>
                    "El turno de {$user->name} todavía no se encuentra vigente.",
            ], 422);
        }
        
        if (
            $vigenteHasta
            && $fechaActual > $vigenteHasta
        ) {
            return response()->json([
                'message' =>
                    "La vigencia del turno de {$user->name} ha finalizado.",
            ], 422);
        }
        
        /*
         * Estado según la ventana horaria.
         */
        $estadoEntrada =
            $this->getEstadoHorario(
                $ahora,
                $turno
            );
        
        /*
         * Estado según el día configurado.
         *
         * Si no es día laborable,
         * la entrada SÍ se permite.
         */
        $estadoDia =
            $this->getEstadoDia(
                $ahora,
                $turno
            );

        $firmaEntradaPath =
            $this->guardarFirmaEntrada(
                $data['firma_entrada']
            );

        $fotoEntradaPath =
            $this->guardarFotoEntrada(
                $data['foto_entrada']
            );

        $asistencia = Asistencia::create([
            'user_id' =>
                $user->id,

            'user_control_acceso_id' =>
                $controlAcceso->id,
            
            /*
             * Snapshot histórico de la unidad laboral
             * y del tipo de trabajo.
             */
            'unidad_servicio_id_snapshot' =>
                $controlAcceso
                    ->unidad_servicio_id,
            
            'unidad_servicio_nombre_snapshot' =>
                $controlAcceso
                    ->unidadServicio
                    ?->nombre,
            
            'empresa_id_snapshot' =>
                $controlAcceso
                    ->empresa_id,
            
            'empresa_nombre_snapshot' =>
                $controlAcceso
                    ->empresa
                    ?->nombre,
            
            'tipo_trabajo_snapshot' =>
                $controlAcceso
                    ->tipo_trabajo,

            /*
             * Snapshot histórico del turno.
             */
            'turno_id_snapshot' =>
                $turno->id,
            
            'turno_nombre_snapshot' =>
                $turno->nombre,
            
            'turno_hora_inicio_snapshot' =>
                $turno->hora_inicio,
            
            'turno_hora_fin_snapshot' =>
                $turno->hora_fin,
            
            'turno_horas_jornada_snapshot' =>
                $turno->horas_jornada,
            
            'turno_minutos_comida_snapshot' =>
                $turno->minutos_comida,
            
            'turno_comida_cuenta_jornada_snapshot' =>
                $turno->comida_cuenta_jornada,

            'turno_lunes_snapshot' =>
                $turno->lunes,
            
            'turno_martes_snapshot' =>
                $turno->martes,
            
            'turno_miercoles_snapshot' =>
                $turno->miercoles,
            
            'turno_jueves_snapshot' =>
                $turno->jueves,
            
            'turno_viernes_snapshot' =>
                $turno->viernes,
            
            'turno_sabado_snapshot' =>
                $turno->sabado,
            
            'turno_domingo_snapshot' =>
                $turno->domingo,
            
            'turno_vigente_desde_snapshot' =>
                $turno->vigente_desde,
            
            'turno_vigente_hasta_snapshot' =>
                $turno->vigente_hasta,
            
            'turno_activo_snapshot' =>
                $turno->activo,
            
            'estado_dia' =>
                $estadoDia,

            /*
             * Evidencia de entrada realizada
             * físicamente por el empleado.
             */
            'firma_entrada' =>
                $firmaEntradaPath,
            
            'firma_capturada_at' =>
                $data['firma_capturada_at'],
            
            'foto_entrada' =>
                $fotoEntradaPath,
            
            'foto_capturada_at' =>
                $data['foto_capturada_at'],
            
            'entrada_latitud' =>
                $data['entrada_latitud'],
            
            'entrada_longitud' =>
                $data['entrada_longitud'],
            
            'entrada_precision_metros' =>
                $data['entrada_precision_metros'],
            
            'entrada_ubicacion_capturada_at' =>
                $ahora,

            'fecha' =>
                $ahora->toDateString(),

            'hora_entrada' =>
                $ahora,

            'entrada_registrada_por_user_id' =>
                $actor->id,

            'metodo_entrada' =>
                'kiosco',

            'estado_entrada' =>
                $estadoEntrada,

            'hora_salida' =>
                null,

            'salida_registrada_por_user_id' =>
                null,

            'metodo_salida' =>
                null,
        ]);

        $asistencia =
            $asistencia->fresh();
        
        /*
         * Registrar creación en el historial
         * de asistencias.
         *
         * En Kiosco, el actor es el usuario
         * autenticado que está operando
         * el Kiosco, no el empleado cuya
         * asistencia se está registrando.
         */
        AsistenciaHistory::create([
            'asistencia_id' =>
                $asistencia->id,
        
            'actor_user_id' =>
                $actor->id,
        
            'action' =>
                'created',
        
            'changes' =>
                null,
        
            'snapshot' =>
                $this->buildHistorySnapshot(
                    $asistencia
                ),
        ]);

        return response()->json([
            'message' =>
                "Entrada registrada correctamente para {$user->name}.",

            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia
                ),
        ], 201);
    }

    /**
     * Iniciar comida mediante Kiosco.
     */
    public function inicioComida(
        Request $request,
        User $user
    ) {
        $actor =
            $request->user();
        
        $asistencia =
            $this->getAsistenciaAbiertaParaKiosco(
                $user
            );

        if (!$asistencia) {
            return response()->json([
                'message' =>
                    'El usuario no tiene una asistencia en curso.',
            ], 409);
        }

        $turno =
            $this->getTurnoAsistencia(
                $asistencia
            );

        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a esta asistencia.',
            ], 422);
        }

        if (
            (int) $turno->minutos_comida
            <= 0
        ) {
            return response()->json([
                'message' =>
                    'El turno del usuario no contempla tiempo de comida.',
            ], 422);
        }

        if (
            $asistencia->hora_inicio_comida
        ) {
            return response()->json([
                'message' =>
                    'La comida ya fue iniciada para esta jornada.',
            ], 409);
        }

        $ahora =
            now(self::TIMEZONE);

        $asistencia->hora_inicio_comida =
            $ahora;

        $asistencia->inicio_comida_registrada_por_user_id =
            $actor->id;

        $asistencia->metodo_inicio_comida =
            'kiosco';

        $asistencia->save();

        return response()->json([
            'message' =>
                "Inicio de comida registrado correctamente para {$user->name}.",

            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia->fresh()
                ),
        ]);
    }

    /**
     * Finalizar comida mediante Kiosco.
     */
    public function finComida(
        Request $request,
        User $user
    ) {
        $actor =
            $request->user();
        
        $asistencia =
            $this->getAsistenciaAbiertaParaKiosco(
                $user
            );

        if (!$asistencia) {
            return response()->json([
                'message' =>
                    'El usuario no tiene una asistencia en curso.',
            ], 409);
        }

        if (
            !$asistencia->hora_inicio_comida
        ) {
            return response()->json([
                'message' =>
                    'El usuario no ha iniciado su tiempo de comida.',
            ], 409);
        }

        if (
            $asistencia->hora_fin_comida
        ) {
            return response()->json([
                'message' =>
                    'El tiempo de comida ya fue finalizado.',
            ], 409);
        }

        $turno =
            $this->getTurnoAsistencia(
                $asistencia
            );

        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a esta asistencia.',
            ], 422);
        }

        $ahora =
            now(self::TIMEZONE);

        $inicioRaw =
            $asistencia->getRawOriginal(
                'hora_inicio_comida'
            );

        $inicio =
            Carbon::parse(
                $inicioRaw,
                self::TIMEZONE
            );

        /*
         * Tiempo exacto de comida.
         */
        $segundosComida =
            (int) $inicio->diffInSeconds(
                $ahora,
                true
            );

        $segundosPermitidos =
            ((int) $turno->minutos_comida)
            * 60;

        if (
            $segundosComida
            < $segundosPermitidos
        ) {
            $estadoComida =
                'menor';
        } elseif (
            $segundosComida
            === $segundosPermitidos
        ) {
            $estadoComida =
                'completa';
        } else {
            $estadoComida =
                'excedida';
        }

        $asistencia->hora_fin_comida =
            $ahora;

        $asistencia->fin_comida_registrada_por_user_id =
            $actor->id;

        $asistencia->metodo_fin_comida =
            'kiosco';

        $asistencia->segundos_comida =
            $segundosComida;

        /*
         * Compatibilidad temporal.
         */
        $asistencia->minutos_comida =
            (int) floor(
                $segundosComida / 60
            );

        $asistencia->estado_comida =
            $estadoComida;

        $asistencia->save();

        return response()->json([
            'message' =>
                "Fin de comida registrado correctamente para {$user->name}.",

            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia->fresh()
                ),
        ]);
    }

    /**
     * Registrar salida mediante Kiosco.
     */
    public function salida(Request $request, User $user)
    {
        $actor =
            $request->user();
        
        $asistencia =
            $this->getAsistenciaAbiertaParaKiosco(
                $user
            );

        if (!$asistencia) {
            return response()->json([
                'message' =>
                    'El usuario no tiene una asistencia en curso.',
            ], 409);
        }

        $turno =
            $this->getTurnoAsistencia(
                $asistencia
            );

        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a esta asistencia.',
            ], 422);
        }

        $ahora =
            now(self::TIMEZONE);

        /*
         * Si la comida continúa abierta,
         * cerrarla automáticamente usando
         * la misma hora de salida.
         */
        if (
            $asistencia->hora_inicio_comida
            && !$asistencia->hora_fin_comida
        ) {
            $inicioComidaRaw =
                $asistencia->getRawOriginal(
                    'hora_inicio_comida'
                );

            $inicioComida =
                Carbon::parse(
                    $inicioComidaRaw,
                    self::TIMEZONE
                );

            $segundosComidaAuto =
                (int) $inicioComida->diffInSeconds(
                    $ahora,
                    true
                );

            $asistencia->hora_fin_comida =
                $ahora;

            $asistencia->fin_comida_registrada_por_user_id =
                $actor->id;

            $asistencia->metodo_fin_comida =
                'kiosco';

            $asistencia->segundos_comida =
                $segundosComidaAuto;

            $asistencia->minutos_comida =
                (int) floor(
                    $segundosComidaAuto
                    / 60
                );
        }

        $horaEntradaRaw =
            $asistencia->getRawOriginal(
                'hora_entrada'
            );

        $horaEntrada =
            Carbon::parse(
                $horaEntradaRaw,
                self::TIMEZONE
            );

        /*
         * PRESENCIA EXACTA
         */
        $segundosPresencia =
            (int) $horaEntrada->diffInSeconds(
                $ahora,
                true
            );

        /*
         * COMIDA EXACTA
         */
        $segundosComida =
            (int) (
                $asistencia->segundos_comida
                ?? 0
            );

        /*
         * TRABAJO EFECTIVO
         */
        if (
            (bool)
            $turno->comida_cuenta_jornada
        ) {
            $segundosTrabajados =
                $segundosPresencia;
        } else {
            $segundosTrabajados =
                max(
                    0,
                    $segundosPresencia
                    - $segundosComida
                );
        }

        /*
         * JORNADA REQUERIDA
         *
         * Para determinar el estado de la jornada,
         * la comida siempre se descuenta,
         * independientemente de si el turno tiene
         * activado "comida cuenta dentro de la jornada".
         */
        $segundosJornada =
            (int) round(
                ((float)
                $turno->horas_jornada)
                * 3600
            );
        
        /*
         * Tiempo utilizado únicamente para
         * clasificar el estado de la jornada.
         */
        $segundosParaEstadoJornada =
            max(
                0,
                $segundosPresencia
                - $segundosComida
            );
        
        /*
         * Se permiten hasta 59:59 minutos
         * adicionales sin marcar la jornada
         * como excedida.
         *
         * Al alcanzar una hora extra completa,
         * pasa a estado "excedida".
         */
        $limiteExcedido =
            $segundosJornada
            + 3600;
        
        if (
            $segundosParaEstadoJornada
            < $segundosJornada
        ) {
            $estadoJornada =
                'incompleta';
        } elseif (
            $segundosParaEstadoJornada
            < $limiteExcedido
        ) {
            $estadoJornada =
                'cumplida';
        } else {
            $estadoJornada =
                'excedida';
        }

        /*
         * ESTADO DE COMIDA
         */
        $segundosComidaPermitidos =
            ((int)
            $turno->minutos_comida)
            * 60;

        if (
            $segundosComidaPermitidos
            > 0
        ) {
            if (
                $segundosComida
                < $segundosComidaPermitidos
            ) {
                $estadoComida =
                    'menor';
            } elseif (
                $segundosComida
                === $segundosComidaPermitidos
            ) {
                $estadoComida =
                    'completa';
            } else {
                $estadoComida =
                    'excedida';
            }
        } else {
            $estadoComida =
                null;
        }

        /*
         * ESTADO ENTRADA / SALIDA
         */
        $estadoEntrada =
            $this->getEstadoHorario(
                $horaEntrada,
                $turno
            );

        $estadoSalida =
            $this->getEstadoHorario(
                $ahora,
                $turno
            );

        /*
         * GUARDAR RESULTADOS
         */
        $asistencia->hora_salida =
            $ahora;

        $asistencia->segundos_presencia =
            $segundosPresencia;

        $asistencia->segundos_comida =
            $segundosComida;

        $asistencia->segundos_trabajados =
            $segundosTrabajados;

        /*
         * Compatibilidad temporal.
         */
        $asistencia->minutos_presencia =
            (int) floor(
                $segundosPresencia / 60
            );

        $asistencia->minutos_comida =
            (int) floor(
                $segundosComida / 60
            );

        $asistencia->minutos_trabajados =
            (int) floor(
                $segundosTrabajados / 60
            );

        $asistencia->estado_entrada =
            $estadoEntrada;

        $asistencia->estado_salida =
            $estadoSalida;

        $asistencia->estado_jornada =
            $estadoJornada;

        $asistencia->estado_comida =
            $estadoComida;

        $asistencia->salida_registrada_por_user_id =
            $actor->id;

        $asistencia->metodo_salida =
            'kiosco';

        $asistencia->save();

        return response()->json([
            'message' =>
                "Salida registrada correctamente para {$user->name}.",

            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia->fresh()
                ),
        ]);
    }

    /**
     * Obtener una asistencia abierta que pueda
     * continuar administrándose desde Kiosco.
     *
     * Si la jornada fue iniciada por Kiosco,
     * se permite continuar aunque posteriormente
     * cambie la configuración del empleado.
     *
     * Si la jornada no inició por Kiosco,
     * se exige que el empleado siga actualmente
     * habilitado para Kiosco.
     */
    private function getAsistenciaAbiertaParaKiosco(
        User $user
    ): ?Asistencia {
        $asistencia = Asistencia::query()
            ->where(
                'user_id',
                $user->id
            )
            ->whereNull(
                'hora_salida'
            )
            ->orderByDesc(
                'hora_entrada'
            )
            ->first();
    
        if (!$asistencia) {
            return null;
        }
    
        /*
         * Si la jornada nació desde Kiosco,
         * ya tiene derecho histórico a terminar
         * desde Kiosco.
         */
        if (
            $asistencia->metodo_entrada
            === 'kiosco'
        ) {
            return $asistencia;
        }
    
        /*
         * Si originalmente fue personal,
         * solamente puede manejarse desde
         * Kiosco mientras el usuario siga
         * habilitado actualmente.
         */
        $this->validateKioscoUser(
            $user
        );
    
        return $asistencia;
    }

    /**
     * Validar que el empleado pueda utilizarse en Kiosco.
     */
    private function validateKioscoUser(User $user): UserControlAcceso
    {
        if (!$user->activo) {
            abort(
                response()->json([
                    'message' =>
                        'El usuario está inactivo.',
                ], 422)
            );
        }

        $today =
            now(self::TIMEZONE)
                ->toDateString();

        $controlAcceso =
            UserControlAcceso::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'kiosco_habilitado',
                    true
                )
                ->whereDate(
                    'fecha_inicio',
                    '<=',
                    $today
                )
                ->where(function ($query) use ($today) {
                    $query
                        ->whereNull(
                            'fecha_fin'
                        )
                        ->orWhereDate(
                            'fecha_fin',
                            '>=',
                            $today
                        );
                })
                ->orderByDesc(
                    'fecha_inicio'
                )
                ->orderByDesc('id')
                ->first();

        if (!$controlAcceso) {
            abort(
                response()->json([
                    'message' =>
                        'El usuario no está habilitado para Kiosco.',
                ], 422)
            );
        }

        return $controlAcceso;
    }

    /**
     * Obtener el turno asociado específicamente
     * a la asistencia.
     */
    private function getTurnoAsistencia(
        Asistencia $asistencia
    ) {
        /*
         * Usar primero el snapshot histórico
         * guardado dentro de la asistencia.
         */
        if (
            $asistencia->turno_id_snapshot !== null
        ) {
            return (object) [
                'id' =>
                    (int) $asistencia
                        ->turno_id_snapshot,
    
                'nombre' =>
                    $asistencia
                        ->turno_nombre_snapshot,
    
                'hora_inicio' =>
                    $asistencia
                        ->turno_hora_inicio_snapshot,
    
                'hora_fin' =>
                    $asistencia
                        ->turno_hora_fin_snapshot,
    
                'horas_jornada' =>
                    (float) $asistencia
                        ->turno_horas_jornada_snapshot,
    
                'minutos_comida' =>
                    (int) $asistencia
                        ->turno_minutos_comida_snapshot,
    
                'comida_cuenta_jornada' =>
                    (bool) $asistencia
                        ->turno_comida_cuenta_jornada_snapshot,
                
                'lunes' =>
                    (bool) $asistencia
                        ->turno_lunes_snapshot,
                
                'martes' =>
                    (bool) $asistencia
                        ->turno_martes_snapshot,
                
                'miercoles' =>
                    (bool) $asistencia
                        ->turno_miercoles_snapshot,
                
                'jueves' =>
                    (bool) $asistencia
                        ->turno_jueves_snapshot,
                
                'viernes' =>
                    (bool) $asistencia
                        ->turno_viernes_snapshot,
                
                'sabado' =>
                    (bool) $asistencia
                        ->turno_sabado_snapshot,
                
                'domingo' =>
                    (bool) $asistencia
                        ->turno_domingo_snapshot,
                
                'vigente_desde' =>
                    $asistencia
                        ->turno_vigente_desde_snapshot,
                
                'vigente_hasta' =>
                    $asistencia
                        ->turno_vigente_hasta_snapshot,
                
                'activo' =>
                    (bool) $asistencia
                        ->turno_activo_snapshot,
            ];
        }
    
        /*
         * Compatibilidad con registros antiguos
         * que no tengan snapshot.
         */
        $asistencia->loadMissing(
            'controlAcceso.turno'
        );
    
        return
            $asistencia
                ->controlAcceso
                ?->turno;
    }

    /**
     * Determinar si una hora está dentro
     * de la ventana permitida del turno.
     */
    private function getEstadoHorario(
        Carbon $momento,
        $turno
    ): string {
        $hora =
            $momento->format('H:i:s');

        $horaInicio =
            substr(
                (string)
                $turno->hora_inicio,
                0,
                8
            );

        $horaFin =
            substr(
                (string)
                $turno->hora_fin,
                0,
                8
            );

        if ($horaInicio <= $horaFin) {
            $dentro =
                $hora >= $horaInicio
                && $hora <= $horaFin;
        } else {
            /*
             * Turno nocturno.
             * Ejemplo: 20:00 - 06:00
             */
            $dentro =
                $hora >= $horaInicio
                || $hora <= $horaFin;
        }

        return $dentro
            ? 'dentro_horario'
            : 'fuera_horario';
    }

    private function getEstadoDia(
        Carbon $momento,
        $turno
    ): string {
        /*
         * dayOfWeekIso:
         * 1 = lunes
         * ...
         * 7 = domingo
         */
        $campoDia = match (
            (int) $momento->dayOfWeekIso
        ) {
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        };
    
        $esLaborable =
            (bool) (
                $turno->{$campoDia}
                ?? false
            );
    
        return $esLaborable
            ? 'laborable'
            : 'fuera_dia_laborable';
    }

    /**
     * Formato enviado al frontend.
     */
    private function formatAsistencia(
        Asistencia $asistencia
    ): array {
        $turno =
            $this->getTurnoAsistencia(
                $asistencia
            );

        $entradaRaw =
            $asistencia->getRawOriginal(
                'hora_entrada'
            );

        $salidaRaw =
            $asistencia->getRawOriginal(
                'hora_salida'
            );

        $inicioComidaRaw =
            $asistencia->getRawOriginal(
                'hora_inicio_comida'
            );

        $finComidaRaw =
            $asistencia->getRawOriginal(
                'hora_fin_comida'
            );

        $entrada = $entradaRaw
            ? Carbon::parse(
                $entradaRaw,
                self::TIMEZONE
            )
            : null;

        $salida = $salidaRaw
            ? Carbon::parse(
                $salidaRaw,
                self::TIMEZONE
            )
            : null;

        $inicioComida =
            $inicioComidaRaw
                ? Carbon::parse(
                    $inicioComidaRaw,
                    self::TIMEZONE
                )
                : null;

        $finComida =
            $finComidaRaw
                ? Carbon::parse(
                    $finComidaRaw,
                    self::TIMEZONE
                )
                : null;

        $usuarioSolicitante =
            request()->user();
        
        $puedeVerUbicacion =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole('Administrador')
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.ubicacion.view'
                    )
            );

        return [
            'id' =>
                $asistencia->id,

            'user_id' =>
                $asistencia->user_id,

            'user_control_acceso_id' =>
                $asistencia->user_control_acceso_id,

            'firma_entrada' =>
                $asistencia->firma_entrada,
            
            'firma_entrada_url' =>
                $asistencia->firma_entrada
                    ? '/storage/' .
                        ltrim(
                            $asistencia->firma_entrada,
                            '/'
                        )
                    : null,
            
            'entrada_latitud' =>
                $puedeVerUbicacion
                && $asistencia->entrada_latitud !== null
                    ? (float) $asistencia->entrada_latitud
                    : null,
            
            'entrada_longitud' =>
                $puedeVerUbicacion
                && $asistencia->entrada_longitud !== null
                    ? (float) $asistencia->entrada_longitud
                    : null,
            
            'entrada_precision_metros' =>
                $puedeVerUbicacion
                && $asistencia->entrada_precision_metros !== null
                    ? (float) $asistencia->entrada_precision_metros
                    : null,
            
            'entrada_ubicacion_capturada_at' =>
                $puedeVerUbicacion
                    ? $asistencia
                        ->entrada_ubicacion_capturada_at
                    : null,
            
            'puede_ver_ubicacion' =>
                $puedeVerUbicacion,

            'unidad_laboral' =>
                (
                    $asistencia
                        ->unidad_servicio_id_snapshot !== null
                    ||
                    $asistencia
                        ->unidad_servicio_nombre_snapshot !== null
                )
                    ? [
                        'id' =>
                            $asistencia
                                ->unidad_servicio_id_snapshot !== null
                                ? (int) $asistencia
                                    ->unidad_servicio_id_snapshot
                                : null,
            
                        'nombre' =>
                            $asistencia
                                ->unidad_servicio_nombre_snapshot,
                    ]
                    : null,
            
            'empresa_laboral' =>
                (
                    $asistencia
                        ->empresa_id_snapshot !== null
                    ||
                    $asistencia
                        ->empresa_nombre_snapshot !== null
                )
                    ? [
                        'id' =>
                            $asistencia
                                ->empresa_id_snapshot !== null
                                ? (int) $asistencia
                                    ->empresa_id_snapshot
                                : null,
            
                        'nombre' =>
                            $asistencia
                                ->empresa_nombre_snapshot,
                    ]
                    : null,

            'tipo_trabajo' =>
                $asistencia
                    ->tipo_trabajo_snapshot,

            'fecha' =>
                $asistencia->getRawOriginal(
                    'fecha'
                ),

            'turno' => $turno
                ? [
                    'id' =>
                        $turno->id,

                    'nombre' =>
                        $turno->nombre,

                    'hora_inicio' =>
                        $turno->hora_inicio,

                    'hora_fin' =>
                        $turno->hora_fin,

                    'horas_jornada' =>
                        (float)
                        $turno->horas_jornada,

                    'minutos_comida' =>
                        (int)
                        $turno->minutos_comida,

                    'comida_cuenta_jornada' =>
                        (bool)
                        $turno->comida_cuenta_jornada,
                ]
                : null,

            'hora_entrada' =>
                $entrada
                    ? $entrada
                        ->toIso8601String()
                    : null,

            'entrada_registrada_por_user_id' =>
                $asistencia
                    ->entrada_registrada_por_user_id,

            'metodo_entrada' =>
                $asistencia
                    ->metodo_entrada,

            'estado_entrada' =>
                $asistencia
                    ->estado_entrada,

            'estado_dia' =>
                $asistencia
                    ->estado_dia,

            'hora_inicio_comida' =>
                $inicioComida
                    ? $inicioComida
                        ->toIso8601String()
                    : null,

            'hora_fin_comida' =>
                $finComida
                    ? $finComida
                        ->toIso8601String()
                    : null,

            'inicio_comida_registrada_por_user_id' =>
                $asistencia
                    ->inicio_comida_registrada_por_user_id,

            'metodo_inicio_comida' =>
                $asistencia
                    ->metodo_inicio_comida,

            'fin_comida_registrada_por_user_id' =>
                $asistencia
                    ->fin_comida_registrada_por_user_id,

            'metodo_fin_comida' =>
                $asistencia
                    ->metodo_fin_comida,

            'segundos_comida' =>
                $asistencia
                    ->segundos_comida,

            'minutos_comida' =>
                $asistencia
                    ->minutos_comida,

            'estado_comida' =>
                $asistencia
                    ->estado_comida,

            'hora_salida' =>
                $salida
                    ? $salida
                        ->toIso8601String()
                    : null,

            'segundos_presencia' =>
                $asistencia
                    ->segundos_presencia,

            'segundos_trabajados' =>
                $asistencia
                    ->segundos_trabajados,

            'minutos_presencia' =>
                $asistencia
                    ->minutos_presencia,

            'minutos_trabajados' =>
                $asistencia
                    ->minutos_trabajados,

            'estado_jornada' =>
                $asistencia
                    ->estado_jornada,

            'salida_registrada_por_user_id' =>
                $asistencia
                    ->salida_registrada_por_user_id,

            'metodo_salida' =>
                $asistencia
                    ->metodo_salida,

            'estado_salida' =>
                $asistencia
                    ->estado_salida,
        ];
    }

    private function buildHistorySnapshot(
        Asistencia $asistencia
    ): array {
        $asistencia->loadMissing('user');
    
        return [
            'user_id' =>
                $asistencia->user_id,
    
            'empleado_nombre' =>
                $asistencia->user?->name,
    
            'empleado_email' =>
                $asistencia->user?->email,
    
            'fecha' =>
                $asistencia->fecha
                    ? Carbon::parse(
                        $asistencia->fecha
                    )->format('Y-m-d')
                    : null,
    
            'hora_entrada' =>
                $this->formatHistoryTime(
                    $asistencia->hora_entrada
                ),
    
            'hora_inicio_comida' =>
                $this->formatHistoryTime(
                    $asistencia->hora_inicio_comida
                ),
    
            'hora_fin_comida' =>
                $this->formatHistoryTime(
                    $asistencia->hora_fin_comida
                ),
    
            'hora_salida' =>
                $this->formatHistoryTime(
                    $asistencia->hora_salida
                ),
    
            'segundos_presencia' =>
                $asistencia->segundos_presencia,
    
            'segundos_comida' =>
                $asistencia->segundos_comida,
    
            'segundos_trabajados' =>
                $asistencia->segundos_trabajados,
    
            'estado_dia' =>
                $asistencia->estado_dia,
    
            'estado_entrada' =>
                $asistencia->estado_entrada,
    
            'estado_salida' =>
                $asistencia->estado_salida,
    
            'estado_comida' =>
                $asistencia->estado_comida,
    
            'estado_jornada' =>
                $asistencia->estado_jornada,
    
            'metodo_entrada' =>
                $asistencia->metodo_entrada,
    
            'unidad_servicio_id' =>
                $asistencia
                    ->unidad_servicio_id_snapshot,
            
            'unidad_servicio_nombre' =>
                $asistencia
                    ->unidad_servicio_nombre_snapshot,
            
            'empresa_id' =>
                $asistencia
                    ->empresa_id_snapshot,
            
            'empresa_nombre' =>
                $asistencia
                    ->empresa_nombre_snapshot,
            
            'tipo_trabajo' =>
                $asistencia
                    ->tipo_trabajo_snapshot,
    
            'turno_nombre' =>
                $asistencia
                    ->turno_nombre_snapshot,
    
            'turno_hora_inicio' =>
                $asistencia
                    ->turno_hora_inicio_snapshot,
    
            'turno_hora_fin' =>
                $asistencia
                    ->turno_hora_fin_snapshot,
    
            'turno_horas_jornada' =>
                $asistencia
                    ->turno_horas_jornada_snapshot,
    
            'turno_minutos_comida' =>
                $asistencia
                    ->turno_minutos_comida_snapshot,
        ];
    }
    
    private function formatHistoryTime(
        $value
    ): ?string {
        if (!$value) {
            return null;
        }
    
        if (
            $value instanceof
            \DateTimeInterface
        ) {
            return $value->format(
                'H:i:s'
            );
        }
    
        return Carbon::parse(
            $value
        )->format(
            'H:i:s'
        );
    }

}