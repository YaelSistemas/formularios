<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaIncidencia;
use App\Models\AsistenciaIncidenciaHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function exportExcel(
        Request $request
    ) {
        $grupos =
            $this->getFilteredIncidenciaGroups(
                $request
            );
    
        $spreadsheet =
            new Spreadsheet();
    
        $sheet =
            $spreadsheet
                ->getActiveSheet();
    
        $sheet->setTitle(
            'Incidencias'
        );
    
        /*
         * Título.
         */
        $sheet->mergeCells(
            'A1:H1'
        );
    
        $sheet->setCellValue(
            'A1',
            'REPORTE DE INCIDENCIAS DE ASISTENCIA'
        );
    
        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(16);
    
        $sheet
            ->getStyle('A1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );
    
        /*
         * Fecha de generación.
         */
        $sheet->mergeCells(
            'A2:H2'
        );
    
        $sheet->setCellValue(
            'A2',
            'Generado: '
            . Carbon::now(
                self::TIMEZONE
            )->format(
                'd/m/Y H:i:s'
            )
        );
    
        $sheet
            ->getStyle('A2')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );
    
        /*
         * Encabezados.
         */
        $headers = [
            'Empleado',
            'Correo',
            'Tipo',
            'Desde',
            'Hasta',
            'Días',
            'Observaciones',
            'Registrado por',
        ];
    
        foreach (
            $headers as
            $index => $header
        ) {
            $column =
                chr(
                    65 + $index
                );
    
            $sheet->setCellValue(
                "{$column}3",
                $header
            );
        }
    
        $sheet
            ->getStyle(
                'A3:H3'
            )
            ->getFont()
            ->setBold(true);
    
        $sheet
            ->getStyle(
                'A3:H3'
            )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB(
                'E2E8F0'
            );
    
        $sheet
            ->getStyle(
                'A3:H3'
            )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );
    
        /*
         * Registros.
         */
        $excelRow = 4;
    
        foreach (
            $grupos as $grupo
        ) {
            $tipoLabel =
                match (
                    $grupo['tipo']
                ) {
                    'vacaciones' =>
                        'Vacaciones',
    
                    'incapacidad' =>
                        'Incapacidad',
    
                    'festivo' =>
                        'Día festivo',
    
                    default =>
                        $grupo['tipo']
                        ?? '',
                };
    
            $sheet->setCellValue(
                "A{$excelRow}",
                $grupo['user']
                    ?->name
                    ?? ''
            );
    
            $sheet->setCellValue(
                "B{$excelRow}",
                $grupo['user']
                    ?->email
                    ?? ''
            );
    
            $sheet->setCellValue(
                "C{$excelRow}",
                $tipoLabel
            );
    
            $sheet->setCellValue(
                "D{$excelRow}",
                !empty(
                    $grupo[
                        'fecha_desde'
                    ]
                )
                    ? Carbon::parse(
                        $grupo[
                            'fecha_desde'
                        ]
                    )->format(
                        'd/m/Y'
                    )
                    : ''
            );
    
            $sheet->setCellValue(
                "E{$excelRow}",
                !empty(
                    $grupo[
                        'fecha_hasta'
                    ]
                )
                    ? Carbon::parse(
                        $grupo[
                            'fecha_hasta'
                        ]
                    )->format(
                        'd/m/Y'
                    )
                    : ''
            );
    
            $sheet->setCellValue(
                "F{$excelRow}",
                $grupo['dias']
                    ?? 0
            );
    
            $sheet->setCellValue(
                "G{$excelRow}",
                $grupo[
                    'observaciones'
                ] ?? ''
            );
    
            $sheet->setCellValue(
                "H{$excelRow}",
                $grupo[
                    'registrado_por'
                ]?->name
                    ?? ''
            );
    
            $excelRow++;
        }
    
        /*
         * Bordes.
         */
        if (
            $excelRow > 4
        ) {
            $sheet
                ->getStyle(
                    'A3:H'
                    . (
                        $excelRow - 1
                    )
                )
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(
                    Border::BORDER_THIN
                );
        }
    
        /*
         * Alineación.
         */
        $sheet
            ->getStyle(
                'C:F'
            )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );
    
        $sheet
            ->getStyle(
                'A:H'
            )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    
        /*
         * Ajustar columnas.
         */
        foreach (
            range(
                'A',
                'H'
            ) as $column
        ) {
            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setAutoSize(
                    true
                );
        }
    
        $sheet->freezePane(
            'A4'
        );
    
        $fileName =
            'incidencias_asistencia_'
            . Carbon::now(
                self::TIMEZONE
            )->format(
                'Ymd_His'
            )
            . '.xlsx';
    
        return response()
            ->streamDownload(
                function () use (
                    $spreadsheet
                ) {
                    $writer =
                        new Xlsx(
                            $spreadsheet
                        );
    
                    $writer->save(
                        'php://output'
                    );
    
                    $spreadsheet
                        ->disconnectWorksheets();
                },
                $fileName,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
    }

    public function exportPdf(
        Request $request
    ) {
        $grupos =
            $this->getFilteredIncidenciaGroups(
                $request
            );
    
        $rows =
            $grupos
                ->map(
                    function ($grupo) {
                        $tipoLabel =
                            match (
                                $grupo['tipo']
                            ) {
                                'vacaciones' =>
                                    'Vacaciones',
    
                                'incapacidad' =>
                                    'Incapacidad',
    
                                'festivo' =>
                                    'Día festivo',
    
                                default =>
                                    $grupo['tipo']
                                    ?? '',
                            };
    
                        return [
                            'empleado' =>
                                $grupo['user']
                                    ?->name
                                    ?? '',
    
                            'correo' =>
                                $grupo['user']
                                    ?->email
                                    ?? '',
    
                            'tipo' =>
                                $tipoLabel,
    
                            'fecha_desde' =>
                                !empty(
                                    $grupo[
                                        'fecha_desde'
                                    ]
                                )
                                    ? Carbon::parse(
                                        $grupo[
                                            'fecha_desde'
                                        ]
                                    )->format(
                                        'd/m/Y'
                                    )
                                    : '',
    
                            'fecha_hasta' =>
                                !empty(
                                    $grupo[
                                        'fecha_hasta'
                                    ]
                                )
                                    ? Carbon::parse(
                                        $grupo[
                                            'fecha_hasta'
                                        ]
                                    )->format(
                                        'd/m/Y'
                                    )
                                    : '',
    
                            'dias' =>
                                $grupo['dias']
                                ?? 0,
    
                            'observaciones' =>
                                $grupo[
                                    'observaciones'
                                ] ?? '',
    
                            'registrado_por' =>
                                $grupo[
                                    'registrado_por'
                                ]?->name
                                    ?? '',
                        ];
                    }
                )
                ->values();
    
        $generatedAt =
            Carbon::now(
                self::TIMEZONE
            )->format(
                'd/m/Y H:i:s'
            );
    
        $pdf =
            Pdf::loadView(
                'admin.asistencia_incidencias.export_pdf',
                [
                    'rows' =>
                        $rows,
    
                    'generatedAt' =>
                        $generatedAt,
                ]
            )
            ->setPaper(
                'a4',
                'landscape'
            );
    
        $fileName =
            'incidencias_asistencia_'
            . Carbon::now(
                self::TIMEZONE
            )->format(
                'Ymd_His'
            )
            . '.pdf';
    
        return $pdf->download(
            $fileName
        );
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
        
                $snapshot =
                    $this->buildGroupSnapshot(
                        $grupoUuid
                    );
        
                AsistenciaIncidenciaHistory::create([
                    'grupo_uuid' =>
                        $grupoUuid,
        
                    'action' =>
                        'created',
        
                    'changes' =>
                        null,
        
                    'snapshot' =>
                        $snapshot,
        
                    'actor_user_id' =>
                        $request
                            ->user()
                            ?->id,
                ]);
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
     * Historial de una incidencia agrupada.
     */
    public function history(
        Request $request,
        string $grupoUuid
    ) {
        $user =
            $request->user();
    
        /*
         * Mismo criterio utilizado actualmente
         * en el historial administrativo
         * de Asistencias.
         */
        if (
            !$user ||
            !$user->hasRole(
                'Administrador'
            )
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para consultar el historial de incidencias.',
            ], 403);
        }
    
        $history =
            AsistenciaIncidenciaHistory::query()
                ->where(
                    'grupo_uuid',
                    $grupoUuid
                )
                ->with([
                    'actor:id,name,email',
                ])
                ->orderBy(
                    'created_at'
                )
                ->orderBy(
                    'id'
                )
                ->get();
    
        if ($history->isEmpty()) {
            abort(404);
        }
    
        /*
         * Tomamos el último snapshot porque
         * representa el estado más reciente
         * conocido de la incidencia.
         *
         * Esto también funciona si la incidencia
         * ya fue eliminada físicamente.
         */
        $latestSnapshot =
            $history
                ->last()
                ?->snapshot
            ?? [];
    
        $items =
            $history
                ->map(
                    function (
                        AsistenciaIncidenciaHistory $item
                    ) {
                        return [
                            'id' =>
                                $item->id,
    
                            'action' =>
                                $item->action,
    
                            'changes' =>
                                $item->changes
                                    ?? [],
    
                            'snapshot' =>
                                $item->snapshot,
    
                            'created_at' =>
                                $item
                                    ->created_at
                                    ?->format(
                                        'Y-m-d H:i:s'
                                    ),
    
                            'actor' =>
                                $item->actor
                                    ? [
                                        'id' =>
                                            $item
                                                ->actor
                                                ->id,
    
                                        'name' =>
                                            $item
                                                ->actor
                                                ->name,
    
                                        'email' =>
                                            $item
                                                ->actor
                                                ->email,
                                    ]
                                    : null,
                        ];
                    }
                )
                ->values();
    
        return response()->json([
            'incidencia' => [
                'grupo_uuid' =>
                    $grupoUuid,
    
                'empleado' =>
                    $latestSnapshot[
                        'empleado'
                    ] ?? null,
    
                'tipo' =>
                    $latestSnapshot[
                        'tipo'
                    ] ?? null,
    
                'fecha_desde' =>
                    $latestSnapshot[
                        'fecha_desde'
                    ] ?? null,
    
                'fecha_hasta' =>
                    $latestSnapshot[
                        'fecha_hasta'
                    ] ?? null,
    
                'dias' =>
                    $latestSnapshot[
                        'dias'
                    ] ?? null,
    
                'observaciones' =>
                    $latestSnapshot[
                        'observaciones'
                    ] ?? null,
    
                'registrado_por' =>
                    $latestSnapshot[
                        'registrado_por'
                    ] ?? null,
            ],
    
            'history' =>
                $items,
        ]);
    }

    /**
     * Historial general de incidencias.
     *
     * Incluye también incidencias que ya
     * fueron eliminadas físicamente.
     */
    public function generalHistory(
        Request $request
    ) {
        $user =
            $request->user();
    
        /*
         * Mismo criterio que el historial
         * individual: solamente Administrador.
         */
        if (
            !$user ||
            !$user->hasRole(
                'Administrador'
            )
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para consultar el historial general de incidencias.',
            ], 403);
        }
    
        $data =
            $request->validate([
                'per_page' => [
                    'nullable',
                    'integer',
                    'in:10,25,50,100',
                ],
            ]);
    
        $perPage =
            (int) (
                $data['per_page']
                ?? 25
            );
    
        /*
         * Primero obtenemos una página de
         * incidencias únicas por grupo_uuid.
         *
         * Un mismo grupo puede tener:
         * created
         * updated
         * updated
         * deleted
         */
        $groups =
            AsistenciaIncidenciaHistory::query()
                ->selectRaw(
                    '
                        grupo_uuid,
                        MAX(id) AS latest_history_id,
                        MAX(created_at) AS last_action_at
                    '
                )
                ->groupBy(
                    'grupo_uuid'
                )
                ->orderByDesc(
                    'last_action_at'
                )
                ->orderByDesc(
                    'latest_history_id'
                )
                ->paginate(
                    $perPage
                );
    
        $grupoUuids =
            collect(
                $groups->items()
            )
                ->pluck(
                    'grupo_uuid'
                )
                ->filter()
                ->values();
    
        /*
         * Cargar todo el historial únicamente
         * de los grupos de esta página.
         */
        $histories =
            AsistenciaIncidenciaHistory::query()
                ->whereIn(
                    'grupo_uuid',
                    $grupoUuids
                )
                ->with([
                    'actor:id,name,email',
                ])
                ->orderBy(
                    'created_at'
                )
                ->orderBy(
                    'id'
                )
                ->get()
                ->groupBy(
                    'grupo_uuid'
                );
    
        $rows =
            $grupoUuids
                ->map(
                    function (
                        string $grupoUuid
                    ) use (
                        $histories
                    ) {
                        $items =
                            $histories->get(
                                $grupoUuid,
                                collect()
                            );
    
                        /*
                         * El último snapshot representa
                         * el estado más reciente conocido.
                         *
                         * Si fue eliminada, será el snapshot
                         * guardado justo antes del borrado.
                         */
                        $latest =
                            $items->last();
    
                        $snapshot =
                            $latest
                                ?->snapshot
                            ?? [];
    
                        $deleted =
                            $items->contains(
                                function (
                                    AsistenciaIncidenciaHistory $item
                                ) {
                                    return
                                        $item->action
                                        === 'deleted';
                                }
                            );
    
                        $history =
                            $items
                                ->map(
                                    function (
                                        AsistenciaIncidenciaHistory $item
                                    ) {
                                        return [
                                            'id' =>
                                                $item->id,
    
                                            'action' =>
                                                $item->action,
    
                                            'changes' =>
                                                $item->changes
                                                    ?? [],
    
                                            'snapshot' =>
                                                $item->snapshot,
    
                                            'created_at' =>
                                                $item
                                                    ->created_at
                                                    ?->format(
                                                        'Y-m-d H:i:s'
                                                    ),
    
                                            'actor' =>
                                                $item->actor
                                                    ? [
                                                        'id' =>
                                                            $item
                                                                ->actor
                                                                ->id,
    
                                                        'name' =>
                                                            $item
                                                                ->actor
                                                                ->name,
    
                                                        'email' =>
                                                            $item
                                                                ->actor
                                                                ->email,
                                                    ]
                                                    : null,
                                        ];
                                    }
                                )
                                ->values();
    
                        return [
                            'grupo_uuid' =>
                                $grupoUuid,
    
                            'deleted' =>
                                $deleted,
    
                            'empleado' =>
                                $snapshot[
                                    'empleado'
                                ] ?? null,
    
                            'tipo' =>
                                $snapshot[
                                    'tipo'
                                ] ?? null,
    
                            'fecha_desde' =>
                                $snapshot[
                                    'fecha_desde'
                                ] ?? null,
    
                            'fecha_hasta' =>
                                $snapshot[
                                    'fecha_hasta'
                                ] ?? null,
    
                            'dias' =>
                                $snapshot[
                                    'dias'
                                ] ?? null,
    
                            'observaciones' =>
                                $snapshot[
                                    'observaciones'
                                ] ?? null,
    
                            'registrado_por' =>
                                $snapshot[
                                    'registrado_por'
                                ] ?? null,
    
                            'movimientos' =>
                                $items->count(),
    
                            'last_action_at' =>
                                $latest
                                    ?->created_at
                                    ?->format(
                                        'Y-m-d H:i:s'
                                    ),
    
                            'history' =>
                                $history,
                        ];
                    }
                )
                ->values();
    
        /*
         * Conservamos la paginación original
         * pero sustituimos los registros internos
         * por la información ya preparada.
         */
        $groups->setCollection(
            $rows
        );
    
        return response()->json([
            'registros' =>
                $groups,
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

        $before =
            $this->buildGroupSnapshot(
                $grupoUuid
            );

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
                $grupoUuid,
                $before
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
                            $before[
                                'registrado_por'
                            ]['id']
                                ?? null,
                    ]);

                    $fecha->addDay();
                }

                $after =
                    $this->buildGroupSnapshot(
                        $grupoUuid
                    );
                
                $changes =
                    $this->buildChanges(
                        $before,
                        $after
                    );
                
                if (!empty($changes)) {
                    AsistenciaIncidenciaHistory::create([
                        'grupo_uuid' =>
                            $grupoUuid,
                
                        'action' =>
                            'updated',
                
                        'changes' =>
                            $changes,
                
                        'snapshot' =>
                            $after,
                
                        'actor_user_id' =>
                            $request
                                ->user()
                                ?->id,
                    ]);
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
        Request $request,
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
    
        $snapshot =
            $this->buildGroupSnapshot(
                $grupoUuid
            );
    
        DB::transaction(
            function () use (
                $request,
                $grupoUuid,
                $snapshot
            ) {
                AsistenciaIncidenciaHistory::create([
                    'grupo_uuid' =>
                        $grupoUuid,
    
                    'action' =>
                        'deleted',
    
                    'changes' =>
                        null,
    
                    'snapshot' =>
                        $snapshot,
    
                    'actor_user_id' =>
                        $request
                            ->user()
                            ?->id,
                ]);
    
                AsistenciaIncidencia::query()
                    ->where(
                        'grupo_uuid',
                        $grupoUuid
                    )
                    ->delete();
            }
        );
    
        return response()->json([
            'message' =>
                'Incidencia eliminada correctamente.',
        ]);
    }

    private function getFilteredIncidenciaGroups(
        Request $request
    ) {
        $data =
            $request->validate([
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
    
        if (
            !empty(
                $data['fecha_desde']
            )
        ) {
            $query->whereDate(
                'fecha',
                '>=',
                $data['fecha_desde']
            );
        }
    
        if (
            !empty(
                $data['fecha_hasta']
            )
        ) {
            $query->whereDate(
                'fecha',
                '<=',
                $data['fecha_hasta']
            );
        }
    
        if (
            !empty(
                $data['user_id']
            )
        ) {
            $query->where(
                'user_id',
                $data['user_id']
            );
        }
    
        if (
            !empty(
                $data['tipo']
            )
        ) {
            $query->where(
                'tipo',
                $data['tipo']
            );
        }
    
        if (
            !empty(
                $data['search']
            )
        ) {
            $search =
                trim(
                    $data['search']
                );
    
            $query->whereHas(
                'user',
                function (
                    $q
                ) use (
                    $search
                ) {
                    $q
                        ->where(
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
                ->orderByDesc(
                    'fecha'
                )
                ->orderByDesc(
                    'id'
                )
                ->get();
    
        return $registros
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
                    $ordenados =
                        $items
                            ->sortBy(
                                'fecha'
                            )
                            ->values();
    
                    $primero =
                        $ordenados
                            ->first();
    
                    $ultimo =
                        $ordenados
                            ->last();
    
                    return [
                        'grupo_uuid' =>
                            $primero
                                ->grupo_uuid,
    
                        'user_id' =>
                            $primero
                                ->user_id,
    
                        'user' =>
                            $primero
                                ->user,
    
                        'tipo' =>
                            $primero
                                ->tipo,
    
                        'observaciones' =>
                            $primero
                                ->observaciones,
    
                        'fecha_desde' =>
                            $primero->fecha
                                ? Carbon::parse(
                                    $primero
                                        ->fecha
                                )->format(
                                    'Y-m-d'
                                )
                                : null,
    
                        'fecha_hasta' =>
                            $ultimo->fecha
                                ? Carbon::parse(
                                    $ultimo
                                        ->fecha
                                )->format(
                                    'Y-m-d'
                                )
                                : null,
    
                        'dias' =>
                            $items
                                ->count(),
    
                        'registrado_por' =>
                            $primero
                                ->registradoPor,
                    ];
                }
            )
            ->values();
    }

    /**
     * Construye el estado lógico completo
     * de una incidencia agrupada.
     */
    private function buildGroupSnapshot(
        string $grupoUuid
    ): array {
        $registros =
            AsistenciaIncidencia::query()
                ->with([
                    'user:id,name,email',
                    'registradoPor:id,name,email',
                ])
                ->where(
                    'grupo_uuid',
                    $grupoUuid
                )
                ->orderBy('fecha')
                ->get();
    
        if ($registros->isEmpty()) {
            return [];
        }
    
        $primero =
            $registros->first();
    
        $ultimo =
            $registros->last();
    
        return [
            'grupo_uuid' =>
                $grupoUuid,
    
            'empleado' => [
                'id' =>
                    $primero->user_id,
    
                'name' =>
                    $primero
                        ->user
                        ?->name,
    
                'email' =>
                    $primero
                        ->user
                        ?->email,
            ],
    
            'tipo' =>
                $primero->tipo,
    
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
    
            'observaciones' =>
                $primero->observaciones,
    
            'registrado_por' => [
                'id' =>
                    $primero
                        ->registrado_por_user_id,
    
                'name' =>
                    $primero
                        ->registradoPor
                        ?->name,
    
                'email' =>
                    $primero
                        ->registradoPor
                        ?->email,
            ],
        ];
    }
    
    /**
     * Obtiene únicamente los campos
     * que cambiaron entre dos estados.
     */
    private function buildChanges(
        array $before,
        array $after
    ): array {
        $changes = [];
    
        /*
         * Estos campos sirven como metadatos,
         * no como cambios funcionales de la
         * incidencia.
         */
        $ignored = [
            'grupo_uuid',
            'registrado_por',
        ];
    
        foreach (
            $after as
            $field => $newValue
        ) {
            if (
                in_array(
                    $field,
                    $ignored,
                    true
                )
            ) {
                continue;
            }
    
            $oldValue =
                $before[$field]
                    ?? null;
    
            if (
                $oldValue !==
                $newValue
            ) {
                $changes[$field] = [
                    'before' =>
                        $oldValue,
    
                    'after' =>
                        $newValue,
                ];
            }
        }
    
        return $changes;
    }
}