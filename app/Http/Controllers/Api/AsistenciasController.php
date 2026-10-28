<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaHistory;
use App\Models\UserControlAcceso;
use App\Models\AsistenciaIncidencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AsistenciasController extends Controller
{
    private const TIMEZONE = 'America/Mexico_City';

    /**
     * Convertir una asistencia al formato que recibirá React.
     *
     * Se utiliza el valor original guardado en BD y se indica
     * explícitamente la zona horaria de Ciudad de México.
     */
    private function formatAsistencia(Asistencia $asistencia): array
    {
        $turno =
            $this->getTurnoAsistencia(
                $asistencia
            );

        $horaEntradaRaw = $asistencia->getRawOriginal('hora_entrada');
        $horaSalidaRaw = $asistencia->getRawOriginal('hora_salida');

        $horaEntrada = $horaEntradaRaw
            ? Carbon::parse($horaEntradaRaw, self::TIMEZONE)
            : null;

        $horaSalida = $horaSalidaRaw
            ? Carbon::parse($horaSalidaRaw, self::TIMEZONE)
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
        
        $puedeVerFirma =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole('Administrador')
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.firma.view'
                    )
            );

        $puedeVerFoto =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole('Administrador')
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.foto.view'
                    )
            );

        return [
            'id' => $asistencia->id,
            'user_id' => $asistencia->user_id,
            'user_control_acceso_id' => $asistencia->user_control_acceso_id,

            'firma_entrada' =>
                $puedeVerFirma
                    ? $asistencia->firma_entrada
                    : null,
            
            'firma_entrada_url' =>
                $puedeVerFirma
                && $asistencia->firma_entrada
                    ? '/storage/' .
                        ltrim(
                            $asistencia->firma_entrada,
                            '/'
                        )
                    : null,
            'firma_capturada_at' =>
                $puedeVerFirma
                    ? $asistencia->firma_capturada_at
                    : null,
            
            'puede_ver_firma' =>
                $puedeVerFirma,

            /*
             * Fotografía de entrada.
             */
            'foto_entrada_url' =>
                $puedeVerFoto
                && $asistencia->foto_entrada
                    ? '/storage/' .
                        ltrim(
                            $asistencia->foto_entrada,
                            '/'
                        )
                    : null,
            
            'foto_capturada_at' =>
                $puedeVerFoto
                    ? $asistencia->foto_capturada_at
                    : null,
            
            'puede_ver_foto' =>
                $puedeVerFoto,
            
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
                && $asistencia
                    ->entrada_ubicacion_capturada_at
                    ? Carbon::parse(
                        $asistencia->getRawOriginal(
                            'entrada_ubicacion_capturada_at'
                        ),
                        self::TIMEZONE
                    )->toIso8601String()
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

            'turno' => $turno
                ? [
                    'id' => $turno->id,
                    'nombre' => $turno->nombre,
                    'hora_inicio' => $turno->hora_inicio,
                    'hora_fin' => $turno->hora_fin,
            
                    'horas_jornada' =>
                        (float) $turno->horas_jornada,
            
                    'minutos_comida' =>
                        (int) $turno->minutos_comida,
            
                    'comida_cuenta_jornada' =>
                        (bool) $turno->comida_cuenta_jornada,
                ]
                : null,

            'fecha' => $asistencia->getRawOriginal('fecha'),

            /*
             * Ejemplo:
             * 2026-08-31T11:32:10-06:00
             *
             * De esta forma JavaScript sabe que corresponde
             * realmente a la hora de Ciudad de México.
             */
            'hora_entrada' => $horaEntrada
                ? $horaEntrada->toIso8601String()
                : null,

            'hora_salida' => $horaSalida
                ? $horaSalida->toIso8601String()
                : null,

            'minutos_trabajados' => $asistencia->minutos_trabajados,

            'segundos_trabajados' => $asistencia->segundos_trabajados,
            
            'estado_entrada' => $asistencia->estado_entrada,
            
            'estado_salida' => $asistencia->estado_salida,

            'estado_dia' => $asistencia->estado_dia,
            
            'entrada_registrada_por_user_id' =>
                $asistencia->entrada_registrada_por_user_id,
            
            'metodo_entrada' =>
                $asistencia->metodo_entrada,
            
            'hora_inicio_comida' =>
                $asistencia->hora_inicio_comida
                    ? Carbon::parse(
                        $asistencia->getRawOriginal('hora_inicio_comida'),
                        self::TIMEZONE
                    )->toIso8601String()
                    : null,
            
            'hora_fin_comida' =>
                $asistencia->hora_fin_comida
                    ? Carbon::parse(
                        $asistencia->getRawOriginal('hora_fin_comida'),
                        self::TIMEZONE
                    )->toIso8601String()
                    : null,
            
            'minutos_comida' =>
                $asistencia->minutos_comida,
            
            'segundos_comida' =>
                $asistencia->segundos_comida,
            
            'estado_comida' =>
                $asistencia->estado_comida,
            
            'minutos_presencia' =>
                $asistencia->minutos_presencia,
            
            'segundos_presencia' =>
                $asistencia->segundos_presencia,
            
            'estado_jornada' =>
                $asistencia->estado_jornada,
            
            'salida_registrada_por_user_id' =>
                $asistencia->salida_registrada_por_user_id,
            
            'metodo_salida' =>
                $asistencia->metodo_salida,
        ];
    }

    /**
     * Registros del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $fechaActual =
            now(self::TIMEZONE)
                ->toDateString();
        
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
        
        $actual = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->orderByDesc('hora_entrada')
            ->first();

        $registros = Asistencia::query()
            ->where('user_id', $user->id)
            ->orderByDesc('hora_entrada')
            ->limit(100)
            ->get();

        return response()->json([
            'actual' => $actual
                ? $this->formatAsistencia($actual)
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

            'registros' => $registros
                ->map(
                    fn (Asistencia $asistencia) =>
                        $this->formatAsistencia($asistencia)
                )
                ->values(),
        ]);
    }

    private function getCurrentControlAcceso(int $userId): ?UserControlAcceso
    {
        $today = now(self::TIMEZONE)
            ->toDateString();
    
        return UserControlAcceso::query()
            ->where('user_id', $userId)
            ->whereDate(
                'fecha_inicio',
                '<=',
                $today
            )
            ->where(function ($query) use ($today) {
                $query
                    ->whereNull('fecha_fin')
                    ->orWhereDate(
                        'fecha_fin',
                        '>=',
                        $today
                    );
            })
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->first();
    }

    private function getTurnoAsistencia(
        Asistencia $asistencia
    ) {
        /*
         * Prioridad absoluta:
         * utilizar el snapshot histórico guardado
         * dentro de la propia asistencia.
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
         * Compatibilidad con algún registro antiguo
         * que por alguna razón no tenga snapshot.
         */
        $asistencia->loadMissing(
            'controlAcceso.turno'
        );
    
        return
            $asistencia
                ->controlAcceso
                ?->turno;
    }

    private function getEstadoHorario(Carbon $momento, $turno): string
    {
        $hora = $momento->format('H:i:s');
    
        $horaInicio = substr(
            (string) $turno->hora_inicio,
            0,
            8
        );
    
        $horaFin = substr(
            (string) $turno->hora_fin,
            0,
            8
        );
    
        /*
         * Turno normal.
         * Ejemplo: 06:00 - 20:00
         */
        if ($horaInicio <= $horaFin) {
            $dentro =
                $hora >= $horaInicio
                && $hora <= $horaFin;
        } else {
            /*
             * También soporta turnos nocturnos.
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
         * Carbon:
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

    private function guardarFirmaEntrada(
        string $firmaBase64
    ): string {
        /*
         * La firma debe llegar desde el canvas
         * como PNG en Base64.
         */
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
    
        /*
         * Límite de seguridad:
         * máximo 2 MB por firma.
         */
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
        /*
         * La fotografía se recibirá desde la cámara
         * como JPEG en Base64.
         */
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
    
        /*
         * Máximo 5 MB por fotografía.
         */
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
     * Registrar hora de entrada.
     *
     * La hora se toma exclusivamente del servidor.
     */
    public function entrada(Request $request)
    {
        $user = $request->user();

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
            $this->getCurrentControlAcceso(
                $user->id
            );
        
        if (!$controlAcceso) {
            return response()->json([
                'message' =>
                    'No tienes una configuración laboral vigente de Control de acceso.',
            ], 422);
        }

        $controlAcceso->loadMissing([
            'turno',
            'unidadServicio',
            'empresa',
        ]);
        
        $turno = $controlAcceso->turno;
        
        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a tu configuración laboral.',
            ], 422);
        }

        /*
         * No permitir dos asistencias abiertas
         * simultáneamente para el mismo usuario.
         */
        $asistenciaAbierta = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->exists();

        if ($asistenciaAbierta) {
            return response()->json([
                'message' => 'Ya tienes una asistencia en curso.',
            ], 409);
        }

        $ahora =
            now(self::TIMEZONE);
        
        $fechaActual =
            $ahora->toDateString();
        
        /*
         * No permitir registrar entrada
         * si el usuario tiene una incidencia
         * asignada para el día de hoy.
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
                    "No puedes registrar entrada el día de hoy porque tienes registrada la incidencia: {$tipoLabel}.",
            ], 409);
        }
        
        /*
         * No permitir más de una entrada
         * por usuario en el mismo día.
         *
         * Esta regla aplica para todos,
         * incluido el Administrador.
         */
        $yaTieneEntradaHoy = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereDate(
                'fecha',
                $fechaActual
            )
            ->whereNotNull('hora_entrada')
            ->exists();
        
        if ($yaTieneEntradaHoy) {
            return response()->json([
                'message' =>
                    'Ya registraste una entrada el día de hoy. No puedes registrar otra entrada.',
            ], 409);
        }

        /*
         * Un turno inactivo sí bloquea
         * el registro de entrada.
         */
        if (!(bool) $turno->activo) {
            return response()->json([
                'message' =>
                    'Tu turno se encuentra inactivo.',
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
                    'Tu turno todavía no se encuentra vigente.',
            ], 422);
        }
        
        if (
            $vigenteHasta
            && $fechaActual > $vigenteHasta
        ) {
            return response()->json([
                'message' =>
                    'La vigencia de tu turno ha finalizado.',
            ], 422);
        }
        
        /*
         * Horario de entrada.
         */
        $estadoEntrada =
            $this->getEstadoHorario(
                $ahora,
                $turno
            );
        
        /*
         * Día laborable.
         *
         * Si no corresponde al calendario,
         * NO se bloquea la asistencia.
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
            'user_id' => $user->id,

            'user_control_acceso_id' => $controlAcceso->id,

            /*
             * Snapshot histórico de la unidad laboral
             * y del tipo de trabajo.
             *
             * Estos datos no cambiarán aunque posteriormente
             * se modifique la configuración del empleado.
             */
            'unidad_servicio_id_snapshot' =>
                $controlAcceso->unidad_servicio_id,
            
            'unidad_servicio_nombre_snapshot' =>
                $controlAcceso
                    ->unidadServicio
                    ?->nombre,
            
            'empresa_id_snapshot' =>
                $controlAcceso->empresa_id,
            
            'empresa_nombre_snapshot' =>
                $controlAcceso
                    ->empresa
                    ?->nombre,
            
            'tipo_trabajo_snapshot' =>
                $controlAcceso->tipo_trabajo,
        
            /*
             * Snapshot histórico del turno.
             *
             * Estos valores jamás dependerán de
             * modificaciones futuras del turno.
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
             * Evidencia de la entrada.
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

            'fecha' => $ahora->toDateString(),
        
            'hora_entrada' => $ahora,
        
            'entrada_registrada_por_user_id' =>
                $user->id,
        
            'metodo_entrada' => 'personal',
        
            'estado_entrada' => $estadoEntrada,

            'hora_salida' => null,
        
            'salida_registrada_por_user_id' =>
                null,
        
            'metodo_salida' => null,
        ]);

        $asistencia = $asistencia->fresh();

        /*
         * Registrar creación en el historial
         * de asistencias.
         */
        AsistenciaHistory::create([
            'asistencia_id' =>
                $asistencia->id,
        
            /*
             * En "Mi asistencia", quien crea
             * el registro es el propio empleado.
             */
            'actor_user_id' =>
                $user->id,
        
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
            'message' => 'Hora de entrada registrada correctamente.',
            'asistencia' => $this->formatAsistencia($asistencia),
        ], 201);
    }

    /**
     * Registrar hora de salida.
     *
     * La hora se toma exclusivamente del servidor.
     */
    public function salida(Request $request)
    {
        $user = $request->user();
    
        $asistencia = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->orderByDesc('hora_entrada')
            ->first();
    
        if (!$asistencia) {
            return response()->json([
                'message' =>
                    'No tienes una asistencia en curso.',
            ], 409);
        }
    
        /*
         * Usar el turno asociado a esta asistencia.
         */
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
    
        $ahora = now(self::TIMEZONE);

        /*
         * Si la comida sigue abierta al registrar salida,
         * finalizarla automáticamente usando la misma
         * hora de salida.
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
                $user->id;
            
            $asistencia->metodo_fin_comida =
                'personal';
            
            $asistencia->segundos_comida =
                $segundosComidaAuto;
            
            /*
             * Compatibilidad temporal.
             */
            $asistencia->minutos_comida =
                (int) floor(
                    $segundosComidaAuto / 60
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
         * PRESENCIA TOTAL
         * salida - entrada
         */
        $segundosPresencia =
            (int) $horaEntrada->diffInSeconds(
                $ahora,
                true
            );
    
        /*
         * COMIDA REAL
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
            (bool) $turno->comida_cuenta_jornada
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
         *
         * La jornada se considera cumplida desde
         * el mínimo requerido hasta 59:59 minutos extra.
         *
         * Al alcanzar 1 hora extra completa,
         * pasa a estado "excedida".
         */
        $segundosJornada =
            (int) round(
                ((float) $turno->horas_jornada)
                * 3600
            );
        
        /*
         * Tiempo efectivo utilizado únicamente
         * para clasificar el estado de la jornada.
         */
        $segundosParaEstadoJornada =
            max(
                0,
                $segundosPresencia
                - $segundosComida
            );
        
        /*
         * Una hora de tolerancia.
         *
         * Ejemplo jornada 8 h:
         *
         * 07:59:59 = incompleta
         * 08:00:00 = cumplida
         * 08:59:59 = cumplida
         * 09:00:00 = excedida
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
            ((int) $turno->minutos_comida)
            * 60;
        
        if ($segundosComidaPermitidos > 0) {
        
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
            $estadoComida = null;
        }
    
        /*
         * ESTADO DE ENTRADA Y SALIDA
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
    
        /*
         * Valores exactos.
         */
        $asistencia->segundos_presencia =
            $segundosPresencia;
        
        $asistencia->segundos_comida =
            $segundosComida;
        
        $asistencia->segundos_trabajados =
            $segundosTrabajados;
        
        /*
         * Valores anteriores.
         * Los conservamos temporalmente.
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
            $user->id;
    
        $asistencia->metodo_salida =
            'personal';
    
        $asistencia->save();
    
        $asistencia =
            $asistencia->fresh();
    
        return response()->json([
            'message' =>
                'Hora de salida registrada correctamente.',
    
            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia
                ),
        ]);
    }

    /**
     * Editar manualmente un registro.
     */
    public function update(
        Request $request,
        Asistencia $asistencia
    ) {
        $user = $request->user();
    
        $horaSalidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_salida'
            );
    
        $inicioComidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_inicio_comida'
            );
    
        $finComidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_fin_comida'
            );
    
        $teniaSalidaOriginal =
            !empty($horaSalidaOriginalRaw);
    
        /*
         * Un usuario solamente puede editar
         * sus propios registros.
         *
         * El Administrador puede editar cualquiera.
         */
        if (
            (int) $asistencia->user_id !==
                (int) $user->id
            && !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' =>
                    'No autorizado para editar este registro.',
            ], 403);
        }
    
        $data = $request->validate([
            'hora_entrada' => [
                'required',
                'date',
            ],
    
            'hora_inicio_comida' => [
                'nullable',
                'date',
            ],
    
            'hora_fin_comida' => [
                'nullable',
                'date',
            ],
    
            'hora_salida' => [
                'nullable',
                'date',
                'after_or_equal:hora_entrada',
            ],
        ]);
    
        /*
         * Utilizar siempre el turno histórico
         * de esta asistencia.
         */
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
    
        /*
         * ==========================
         * CONVERTIR HORAS
         * ==========================
         */
        $horaEntrada =
            Carbon::parse(
                $data['hora_entrada'],
                self::TIMEZONE
            );
    
        /*
         * No permitir que una edición
         * provoque dos entradas del mismo
         * usuario en la misma fecha.
         *
         * Se excluye la asistencia que
         * actualmente se está editando.
         */
        $fechaEntrada =
            $horaEntrada->toDateString();
        
        /*
         * No permitir mover una asistencia
         * a una fecha con incidencia.
         */
        $incidenciaFecha =
            AsistenciaIncidencia::query()
                ->where(
                    'user_id',
                    $asistencia->user_id
                )
                ->whereDate(
                    'fecha',
                    $fechaEntrada
                )
                ->first();
        
        if ($incidenciaFecha) {
            $tipoLabel =
                match (
                    $incidenciaFecha->tipo
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
                    "No se puede guardar la asistencia porque tienes registrada la incidencia {$tipoLabel} en esa fecha.",
            ], 409);
        }

        $yaExisteEntradaEseDia = Asistencia::query()
            ->where(
                'user_id',
                $asistencia->user_id
            )
            ->whereDate(
                'fecha',
                $fechaEntrada
            )
            ->where(
                'id',
                '!=',
                $asistencia->id
            )
            ->whereNotNull('hora_entrada')
            ->exists();
        
        if ($yaExisteEntradaEseDia) {
            return response()->json([
                'message' =>
                    'El usuario ya tiene una entrada registrada en esa fecha.',
            ], 409);
        }

        $horaSalida =
            !empty($data['hora_salida'])
                ? Carbon::parse(
                    $data['hora_salida'],
                    self::TIMEZONE
                )
                : null;
    
        $inicioComida =
            !empty(
                $data['hora_inicio_comida']
            )
                ? Carbon::parse(
                    $data['hora_inicio_comida'],
                    self::TIMEZONE
                )
                : null;
    
        $finComida =
            !empty(
                $data['hora_fin_comida']
            )
                ? Carbon::parse(
                    $data['hora_fin_comida'],
                    self::TIMEZONE
                )
                : null;
    
        /*
         * ==========================
         * VALIDAR COMIDA
         * ==========================
         */
    
        /*
         * No puede existir fin de comida
         * sin inicio de comida.
         */
        if (
            !$inicioComida
            && $finComida
        ) {
            return response()->json([
                'message' =>
                    'Debes indicar el inicio de comida antes del fin de comida.',
            ], 422);
        }
    
        /*
         * Si la jornada ya tiene salida,
         * no puede quedar una comida abierta.
         */
        if (
            $horaSalida
            && $inicioComida
            && !$finComida
        ) {
            return response()->json([
                'message' =>
                    'Debes indicar el fin de comida cuando la asistencia tiene hora de salida.',
            ], 422);
        }
    
        /*
         * No permitir crear una comida nueva
         * en un turno que históricamente
         * no contemplaba comida.
         *
         * Si es un registro antiguo que ya tenía
         * comida, sí permitimos corregirlo.
         */
        if (
            (int) $turno->minutos_comida <= 0
            && !$inicioComidaOriginalRaw
            && !$finComidaOriginalRaw
            && (
                $inicioComida
                || $finComida
            )
        ) {
            return response()->json([
                'message' =>
                    'El turno de esta asistencia no contempla tiempo de comida.',
            ], 422);
        }
    
        /*
         * Entrada <= inicio comida
         */
        if (
            $inicioComida
            && $inicioComida->lt(
                $horaEntrada
            )
        ) {
            return response()->json([
                'message' =>
                    'El inicio de comida no puede ser anterior a la hora de entrada.',
            ], 422);
        }
    
        /*
         * Inicio comida <= fin comida
         */
        if (
            $inicioComida
            && $finComida
            && $finComida->lt(
                $inicioComida
            )
        ) {
            return response()->json([
                'message' =>
                    'El fin de comida no puede ser anterior al inicio de comida.',
            ], 422);
        }
    
        /*
         * Si existe salida:
         *
         * inicio comida <= salida
         */
        if (
            $horaSalida
            && $inicioComida
            && $inicioComida->gt(
                $horaSalida
            )
        ) {
            return response()->json([
                'message' =>
                    'El inicio de comida no puede ser posterior a la hora de salida.',
            ], 422);
        }
    
        /*
         * fin comida <= salida
         */
        if (
            $horaSalida
            && $finComida
            && $finComida->gt(
                $horaSalida
            )
        ) {
            return response()->json([
                'message' =>
                    'El fin de comida no puede ser posterior a la hora de salida.',
            ], 422);
        }
    
        /*
         * ==========================
         * DETECTAR CAMBIOS DE COMIDA
         * ==========================
         */
        $inicioComidaOriginal =
            $inicioComidaOriginalRaw
                ? Carbon::parse(
                    $inicioComidaOriginalRaw,
                    self::TIMEZONE
                )
                : null;
    
        $finComidaOriginal =
            $finComidaOriginalRaw
                ? Carbon::parse(
                    $finComidaOriginalRaw,
                    self::TIMEZONE
                )
                : null;
    
        $inicioComidaCambio =
            (
                $inicioComidaOriginal
                    ? $inicioComidaOriginal
                        ->format(
                            'Y-m-d H:i:s'
                        )
                    : null
            )
            !==
            (
                $inicioComida
                    ? $inicioComida
                        ->format(
                            'Y-m-d H:i:s'
                        )
                    : null
            );
    
        $finComidaCambio =
            (
                $finComidaOriginal
                    ? $finComidaOriginal
                        ->format(
                            'Y-m-d H:i:s'
                        )
                    : null
            )
            !==
            (
                $finComida
                    ? $finComida
                        ->format(
                            'Y-m-d H:i:s'
                        )
                    : null
            );
    
        /*
         * ==========================
         * CALCULAR COMIDA
         * ==========================
         */
        $segundosComida = null;
    
        if (
            $inicioComida
            && $finComida
        ) {
            $segundosComida =
                (int) $inicioComida
                    ->diffInSeconds(
                        $finComida,
                        true
                    );
        }
    
        $segundosComidaPermitidos =
            ((int) $turno->minutos_comida)
            * 60;
    
        $estadoComida = null;
    
        if (
            $segundosComidaPermitidos > 0
            && $segundosComida !== null
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
        }
    
        /*
         * ==========================
         * ENTRADA Y DÍA
         * ==========================
         */
        $estadoEntrada =
            $this->getEstadoHorario(
                $horaEntrada,
                $turno
            );
    
        /*
         * Si se modifica la fecha de entrada,
         * también recalcular si ese día
         * correspondía al calendario histórico.
         */
        $estadoDia =
            $this->getEstadoDia(
                $horaEntrada,
                $turno
            );
    
        /*
         * Valores para una asistencia abierta.
         */
        $segundosPresencia = null;
        $segundosTrabajados = null;
    
        $estadoSalida = null;
        $estadoJornada = null;
    
        /*
         * ==========================
         * SI EXISTE SALIDA
         * ==========================
         */
        if ($horaSalida) {
            $segundosPresencia =
                (int) $horaEntrada
                    ->diffInSeconds(
                        $horaSalida,
                        true
                    );
    
            $segundosComidaCalculo =
                (int) (
                    $segundosComida
                    ?? 0
                );
    
            /*
             * Trabajo efectivo.
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
                        - $segundosComidaCalculo
                    );
            }
    
            /*
             * Jornada requerida.
             *
             * Para determinar el estado de la jornada,
             * la comida siempre se descuenta,
             * independientemente del valor de
             * comida_cuenta_jornada.
             */
            $segundosJornada =
                (int) round(
                    ((float)
                        $turno->horas_jornada
                    )
                    * 3600
                );
            
            /*
             * Tiempo utilizado únicamente para
             * clasificar el estado de jornada.
             */
            $segundosParaEstadoJornada =
                max(
                    0,
                    $segundosPresencia
                    - $segundosComidaCalculo
                );
            
            /*
             * Se permite hasta 59:59 minutos
             * adicionales sin marcar la jornada
             * como excedida.
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
    
            $estadoSalida =
                $this->getEstadoHorario(
                    $horaSalida,
                    $turno
                );
    
            /*
             * Si el turno contemplaba comida
             * pero no se registró ninguna,
             * queda como menor.
             */
            if (
                $segundosComidaPermitidos > 0
                && $segundosComida === null
            ) {
                $estadoComida =
                    'menor';
            }
        }
    
        /*
         * ==========================
         * GUARDAR HORAS
         * ==========================
         */
        $asistencia->fecha =
            $horaEntrada->toDateString();
    
        $asistencia->hora_entrada =
            $horaEntrada;
    
        $asistencia->hora_inicio_comida =
            $inicioComida;
    
        $asistencia->hora_fin_comida =
            $finComida;
    
        $asistencia->hora_salida =
            $horaSalida;
    
        /*
         * ==========================
         * AUDITORÍA DE COMIDA
         * ==========================
         */
    
        /*
         * Inicio de comida eliminado.
         */
        if (!$inicioComida) {
            $asistencia
                ->inicio_comida_registrada_por_user_id =
                null;
    
            $asistencia
                ->metodo_inicio_comida =
                null;
        } elseif ($inicioComidaCambio) {
            /*
             * Inicio agregado o corregido
             * manualmente.
             */
            $asistencia
                ->inicio_comida_registrada_por_user_id =
                $user->id;
    
            $asistencia
                ->metodo_inicio_comida =
                'manual';
        }
    
        /*
         * Fin de comida eliminado.
         */
        if (!$finComida) {
            $asistencia
                ->fin_comida_registrada_por_user_id =
                null;
    
            $asistencia
                ->metodo_fin_comida =
                null;
        } elseif ($finComidaCambio) {
            /*
             * Fin agregado o corregido
             * manualmente.
             */
            $asistencia
                ->fin_comida_registrada_por_user_id =
                $user->id;
    
            $asistencia
                ->metodo_fin_comida =
                'manual';
        }
    
        /*
         * ==========================
         * ESTADOS
         * ==========================
         */
        $asistencia->estado_dia =
            $estadoDia;
    
        $asistencia->estado_entrada =
            $estadoEntrada;
    
        $asistencia->estado_salida =
            $estadoSalida;
    
        $asistencia->estado_comida =
            $estadoComida;
    
        $asistencia->estado_jornada =
            $estadoJornada;
    
        /*
         * ==========================
         * SEGUNDOS EXACTOS
         * ==========================
         */
        $asistencia->segundos_presencia =
            $segundosPresencia;
    
        $asistencia->segundos_comida =
            $segundosComida;
    
        $asistencia->segundos_trabajados =
            $segundosTrabajados;
    
        /*
         * Compatibilidad temporal
         * con columnas de minutos.
         */
        $asistencia->minutos_presencia =
            $segundosPresencia !== null
                ? (int) floor(
                    $segundosPresencia
                    / 60
                )
                : null;
    
        $asistencia->minutos_comida =
            $segundosComida !== null
                ? (int) floor(
                    $segundosComida
                    / 60
                )
                : null;
    
        $asistencia->minutos_trabajados =
            $segundosTrabajados !== null
                ? (int) floor(
                    $segundosTrabajados
                    / 60
                )
                : null;
    
        /*
         * ==========================
         * AUDITORÍA DE SALIDA
         * ==========================
         */
        if (!$horaSalida) {
            $asistencia
                ->salida_registrada_por_user_id =
                null;
    
            $asistencia->metodo_salida =
                null;
        } elseif (!$teniaSalidaOriginal) {
            $asistencia
                ->salida_registrada_por_user_id =
                $user->id;
    
            $asistencia->metodo_salida =
                'manual';
        }
    
        $asistencia->save();
    
        $asistencia =
            $asistencia->fresh();
    
        return response()->json([
            'message' =>
                'Asistencia actualizada correctamente.',
    
            'asistencia' =>
                $this->formatAsistencia(
                    $asistencia
                ),
        ]);
    }

    /**
     * Eliminar un registro.
     */
    public function destroy(Request $request, Asistencia $asistencia)
    {
        $user = $request->user();

        /*
         * Un usuario solamente puede eliminar sus propios registros.
         * El Administrador puede eliminar cualquier registro.
         */
        if (
            (int) $asistencia->user_id !== (int) $user->id
            && !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' => 'No autorizado para eliminar este registro.',
            ], 403);
        }

        $asistencia->delete();

        return response()->json([
            'message' => 'Asistencia eliminada correctamente.',
        ]);
    }

    public function inicioComida(Request $request)
    {
        $user = $request->user();
    
        $asistencia = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->orderByDesc('hora_entrada')
            ->first();
    
        if (!$asistencia) {
            return response()->json([
                'message' => 'No tienes una asistencia en curso.',
            ], 409);
        }
    
        $turno = $this->getTurnoAsistencia($asistencia);
    
        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a esta asistencia.',
            ], 422);
        }
    
        if ((int) $turno->minutos_comida <= 0) {
            return response()->json([
                'message' =>
                    'Tu turno no contempla tiempo de comida.',
            ], 422);
        }
    
        if ($asistencia->hora_inicio_comida) {
            return response()->json([
                'message' =>
                    'La comida ya fue iniciada para esta jornada.',
            ], 409);
        }
    
        $ahora = now(self::TIMEZONE);
    
        $asistencia->hora_inicio_comida =
            $ahora;
    
        $asistencia->inicio_comida_registrada_por_user_id =
            $user->id;
    
        $asistencia->metodo_inicio_comida =
            'personal';
    
        $asistencia->save();
    
        return response()->json([
            'message' =>
                'Inicio de comida registrado correctamente.',
    
            'asistencia' =>
                $this->formatAsistencia($asistencia->fresh()),
        ]);
    }

    public function finComida(Request $request)
    {
        $user = $request->user();
    
        $asistencia = Asistencia::query()
            ->where('user_id', $user->id)
            ->whereNull('hora_salida')
            ->orderByDesc('hora_entrada')
            ->first();
    
        if (!$asistencia) {
            return response()->json([
                'message' => 'No tienes una asistencia en curso.',
            ], 409);
        }
    
        if (!$asistencia->hora_inicio_comida) {
            return response()->json([
                'message' =>
                    'No has iniciado tu tiempo de comida.',
            ], 409);
        }
    
        if ($asistencia->hora_fin_comida) {
            return response()->json([
                'message' =>
                    'El tiempo de comida ya fue finalizado.',
            ], 409);
        }
    
        $turno = $this->getTurnoAsistencia($asistencia);
    
        if (!$turno) {
            return response()->json([
                'message' =>
                    'No se encontró el turno asociado a esta asistencia.',
            ], 422);
        }
    
        $ahora = now(self::TIMEZONE);
    
        $inicioRaw =
            $asistencia->getRawOriginal('hora_inicio_comida');
    
        $inicio = Carbon::parse(
            $inicioRaw,
            self::TIMEZONE
        );
    
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
            $estado = 'menor';
        } elseif (
            $segundosComida
            === $segundosPermitidos
        ) {
            $estado = 'completa';
        } else {
            $estado = 'excedida';
        }
    
        $asistencia->hora_fin_comida =
            $ahora;
    
        $asistencia->fin_comida_registrada_por_user_id =
            $user->id;
    
        $asistencia->metodo_fin_comida =
            'personal';
    
        $asistencia->segundos_comida =
            $segundosComida;
        
        /*
         * Campo anterior.
         * Lo mantenemos temporalmente.
         */
        $asistencia->minutos_comida =
            (int) floor(
                $segundosComida / 60
            );
        
        $asistencia->estado_comida =
            $estado;
    
        $asistencia->save();
    
        return response()->json([
            'message' =>
                'Fin de comida registrado correctamente.',
    
            'asistencia' =>
                $this->formatAsistencia($asistencia->fresh()),
        ]);
    }

    private function buildHistorySnapshot(
        Asistencia $asistencia
    ): array {
        /*
         * Conservar también los datos del empleado
         * para que el historial no dependa de cambios
         * futuros en su cuenta.
         */
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
    
            /*
             * Snapshot laboral.
             */
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
    
            /*
             * Snapshot del turno.
             */
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