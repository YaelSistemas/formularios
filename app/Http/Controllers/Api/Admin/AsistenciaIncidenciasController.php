<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaIncidencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AsistenciaIncidenciasController extends Controller
{
    private const TIMEZONE =
        'America/Mexico_City';

    private const TIPOS = [
        'vacaciones',
        'incapacidad',
        'festivo',
    ];

    /**
     * Listado administrativo
     * de incidencias.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'fecha_desde' => [
                'nullable',
                'date',
            ],

            'fecha_hasta' => [
                'nullable',
                'date',
                'after_or_equal:fecha_desde',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'tipo' => [
                'nullable',
                'string',
                'in:' . implode(
                    ',',
                    self::TIPOS
                ),
            ],

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $query =
            AsistenciaIncidencia::query()
                ->with([
                    'user:id,name,email,activo',
                    'registradoPor:id,name,email',
                ]);

        if (!empty($data['fecha_desde'])) {
            $query->whereDate(
                'fecha',
                '>=',
                $data['fecha_desde']
            );
        }

        if (!empty($data['fecha_hasta'])) {
            $query->whereDate(
                'fecha',
                '<=',
                $data['fecha_hasta']
            );
        }

        if (!empty($data['user_id'])) {
            $query->where(
                'user_id',
                $data['user_id']
            );
        }

        if (!empty($data['tipo'])) {
            $query->where(
                'tipo',
                $data['tipo']
            );
        }

        if (!empty($data['search'])) {
            $search =
                trim($data['search']);

            $query->whereHas(
                'user',
                function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }

        $registros =
            $query
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->get();

        /*
         * Agrupar registros pertenecientes
         * a la misma captura por rango.
         */
        $grupos =
            $registros
                ->groupBy(
                    function (
                        AsistenciaIncidencia $item
                    ) {
                        return
                            $item->grupo_uuid
                            ?: 'registro-'
                                . $item->id;
                    }
                )
                ->map(
                    function ($items) {
                        $primero =
                            $items
                                ->sortBy('fecha')
                                ->first();

                        $ultimo =
                            $items
                                ->sortBy('fecha')
                                ->last();

                        return [
                            'grupo_uuid' =>
                                $primero->grupo_uuid,

                            'user_id' =>
                                $primero->user_id,

                            'user' =>
                                $primero->user,

                            'tipo' =>
                                $primero->tipo,

                            'observaciones' =>
                                $primero->observaciones,

                            'fecha_desde' =>
                                $primero->fecha
                                    ? Carbon::parse(
                                        $primero->fecha
                                    )->format(
                                        'Y-m-d'
                                    )
                                    : null,

                            'fecha_hasta' =>
                                $ultimo->fecha
                                    ? Carbon::parse(
                                        $ultimo->fecha
                                    )->format(
                                        'Y-m-d'
                                    )
                                    : null,

                            'dias' =>
                                $items->count(),

                            'registrado_por' =>
                                $primero
                                    ->registradoPor,

                            'created_at' =>
                                $primero
                                    ->created_at,

                            'updated_at' =>
                                $items
                                    ->max(
                                        'updated_at'
                                    ),
                        ];
                    }
                )
                ->values();

        /*
         * Empleados disponibles
         * para el selector.
         */
        $usuarios =
            User::query()
                ->select([
                    'id',
                    'name',
                    'email',
                    'activo',
                ])
                ->where(
                    'activo',
                    true
                )
                ->orderBy('name')
                ->get();

        return response()->json([
            'incidencias' =>
                $grupos,

            'usuarios' =>
                $usuarios,

            'tipos' => [
                [
                    'value' =>
                        'vacaciones',
                    'label' =>
                        'Vacaciones',
                ],
                [
                    'value' =>
                        'incapacidad',
                    'label' =>
                        'Incapacidad',
                ],
                [
                    'value' =>
                        'festivo',
                    'label' =>
                        'Día festivo',
                ],
            ],
        ]);
    }

    /**
     * Crear una incidencia
     * para una fecha o rango.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'tipo' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::TIPOS
                ),
            ],

            'fecha_desde' => [
                'required',
                'date',
            ],

            'fecha_hasta' => [
                'required',
                'date',
                'after_or_equal:fecha_desde',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $fechaDesde =
            Carbon::parse(
                $data['fecha_desde'],
                self::TIMEZONE
            )->startOfDay();

        $fechaHasta =
            Carbon::parse(
                $data['fecha_hasta'],
                self::TIMEZONE
            )->startOfDay();

        $cantidadDias =
            $fechaDesde->diffInDays(
                $fechaHasta
            ) + 1;

        if ($cantidadDias > 366) {
            throw ValidationException::withMessages([
                'fecha_hasta' =>
                    'El rango no puede ser mayor a 366 días.',
            ]);
        }

        /*
         * No permitir crear una incidencia
         * donde ya exista asistencia.
         */
        $asistenciaExistente =
            Asistencia::query()
                ->where(
                    'user_id',
                    $data['user_id']
                )
                ->whereBetween(
                    'fecha',
                    [
                        $fechaDesde
                            ->toDateString(),
                        $fechaHasta
                            ->toDateString(),
                    ]
                )
                ->orderBy('fecha')
                ->first();

        if ($asistenciaExistente) {
            throw ValidationException::withMessages([
                'fecha_desde' =>
                    'No se puede registrar la incidencia porque el empleado ya tiene una asistencia registrada el '
                    . Carbon::parse(
                        $asistenciaExistente->fecha
                    )->format(
                        'd/m/Y'
                    )
                    . '.',
            ]);
        }

        /*
         * No permitir que el rango
         * se empalme con otra incidencia.
         */
        $incidenciaExistente =
            AsistenciaIncidencia::query()
                ->where(
                    'user_id',
                    $data['user_id']
                )
                ->whereBetween(
                    'fecha',
                    [
                        $fechaDesde
                            ->toDateString(),
                        $fechaHasta
                            ->toDateString(),
                    ]
                )
                ->orderBy('fecha')
                ->first();

        if ($incidenciaExistente) {
            throw ValidationException::withMessages([
                'fecha_desde' =>
                    'El empleado ya tiene una incidencia registrada el '
                    . Carbon::parse(
                        $incidenciaExistente->fecha
                    )->format(
                        'd/m/Y'
                    )
                    . '.',
            ]);
        }

        $grupoUuid =
            (string) Str::uuid();

        DB::transaction(
            function () use (
                $request,
                $data,
                $fechaDesde,
                $fechaHasta,
                $grupoUuid
            ) {
                $fecha =
                    $fechaDesde->copy();

                while (
                    $fecha->lte(
                        $fechaHasta
                    )
                ) {
                    AsistenciaIncidencia::create([
                        'user_id' =>
                            $data['user_id'],

                        'grupo_uuid' =>
                            $grupoUuid,

                        'fecha' =>
                            $fecha
                                ->toDateString(),

                        'tipo' =>
                            $data['tipo'],

                        'observaciones' =>
                            $data[
                                'observaciones'
                            ] ?? null,

                        'registrado_por_user_id' =>
                            $request
                                ->user()
                                ->id,
                    ]);

                    $fecha->addDay();
                }
            }
        );

        return response()->json([
            'message' =>
                'Incidencia registrada correctamente.',

            'grupo_uuid' =>
                $grupoUuid,
        ], 201);
    }

    /**
     * Mostrar un grupo de incidencias.
     */
    public function show(
        string $grupoUuid
    ) {
        $registros =
            AsistenciaIncidencia::query()
                ->with([
                    'user:id,name,email,activo',
                    'registradoPor:id,name,email',
                ])
                ->where(
                    'grupo_uuid',
                    $grupoUuid
                )
                ->orderBy('fecha')
                ->get();

        if ($registros->isEmpty()) {
            abort(404);
        }

        $primero =
            $registros->first();

        $ultimo =
            $registros->last();

        return response()->json([
            'grupo_uuid' =>
                $grupoUuid,

            'user_id' =>
                $primero->user_id,

            'user' =>
                $primero->user,

            'tipo' =>
                $primero->tipo,

            'observaciones' =>
                $primero->observaciones,

            'fecha_desde' =>
                Carbon::parse(
                    $primero->fecha
                )->format(
                    'Y-m-d'
                ),

            'fecha_hasta' =>
                Carbon::parse(
                    $ultimo->fecha
                )->format(
                    'Y-m-d'
                ),

            'dias' =>
                $registros->count(),

            'registrado_por' =>
                $primero
                    ->registradoPor,
        ]);
    }

    /**
     * Editar un rango completo.
     */
    public function update(
        Request $request,
        string $grupoUuid
    ) {
        $actuales =
            AsistenciaIncidencia::query()
                ->where(
                    'grupo_uuid',
                    $grupoUuid
                )
                ->get();

        if ($actuales->isEmpty()) {
            abort(404);
        }

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'tipo' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::TIPOS
                ),
            ],

            'fecha_desde' => [
                'required',
                'date',
            ],

            'fecha_hasta' => [
                'required',
                'date',
                'after_or_equal:fecha_desde',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $fechaDesde =
            Carbon::parse(
                $data['fecha_desde'],
                self::TIMEZONE
            )->startOfDay();

        $fechaHasta =
            Carbon::parse(
                $data['fecha_hasta'],
                self::TIMEZONE
            )->startOfDay();

        $cantidadDias =
            $fechaDesde->diffInDays(
                $fechaHasta
            ) + 1;

        if ($cantidadDias > 366) {
            throw ValidationException::withMessages([
                'fecha_hasta' =>
                    'El rango no puede ser mayor a 366 días.',
            ]);
        }

        /*
         * No permitir una incidencia
         * sobre días con asistencia.
         */
        $asistenciaExistente =
            Asistencia::query()
                ->where(
                    'user_id',
                    $data['user_id']
                )
                ->whereBetween(
                    'fecha',
                    [
                        $fechaDesde
                            ->toDateString(),
                        $fechaHasta
                            ->toDateString(),
                    ]
                )
                ->orderBy('fecha')
                ->first();

        if ($asistenciaExistente) {
            throw ValidationException::withMessages([
                'fecha_desde' =>
                    'No se puede guardar la incidencia porque el empleado ya tiene una asistencia registrada el '
                    . Carbon::parse(
                        $asistenciaExistente->fecha
                    )->format(
                        'd/m/Y'
                    )
                    . '.',
            ]);
        }

        /*
         * Buscar conflictos con otras
         * incidencias, excluyendo
         * el grupo que estamos editando.
         */
        $incidenciaExistente =
            AsistenciaIncidencia::query()
                ->where(
                    'user_id',
                    $data['user_id']
                )
                ->where(
                    'grupo_uuid',
                    '!=',
                    $grupoUuid
                )
                ->whereBetween(
                    'fecha',
                    [
                        $fechaDesde
                            ->toDateString(),
                        $fechaHasta
                            ->toDateString(),
                    ]
                )
                ->orderBy('fecha')
                ->first();

        if ($incidenciaExistente) {
            throw ValidationException::withMessages([
                'fecha_desde' =>
                    'El empleado ya tiene otra incidencia registrada el '
                    . Carbon::parse(
                        $incidenciaExistente->fecha
                    )->format(
                        'd/m/Y'
                    )
                    . '.',
            ]);
        }

        DB::transaction(
            function () use (
                $request,
                $data,
                $fechaDesde,
                $fechaHasta,
                $grupoUuid
            ) {
                AsistenciaIncidencia::query()
                    ->where(
                        'grupo_uuid',
                        $grupoUuid
                    )
                    ->delete();

                $fecha =
                    $fechaDesde->copy();

                while (
                    $fecha->lte(
                        $fechaHasta
                    )
                ) {
                    AsistenciaIncidencia::create([
                        'user_id' =>
                            $data['user_id'],

                        'grupo_uuid' =>
                            $grupoUuid,

                        'fecha' =>
                            $fecha
                                ->toDateString(),

                        'tipo' =>
                            $data['tipo'],

                        'observaciones' =>
                            $data[
                                'observaciones'
                            ] ?? null,

                        'registrado_por_user_id' =>
                            $request
                                ->user()
                                ->id,
                    ]);

                    $fecha->addDay();
                }
            }
        );

        return response()->json([
            'message' =>
                'Incidencia actualizada correctamente.',
        ]);
    }

    /**
     * Eliminar un rango completo.
     */
    public function destroy(
        string $grupoUuid
    ) {
        $cantidad =
            AsistenciaIncidencia::query()
                ->where(
                    'grupo_uuid',
                    $grupoUuid
                )
                ->count();

        if ($cantidad === 0) {
            abort(404);
        }

        AsistenciaIncidencia::query()
            ->where(
                'grupo_uuid',
                $grupoUuid
            )
            ->delete();

        return response()->json([
            'message' =>
                'Incidencia eliminada correctamente.',
        ]);
    }
}