<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaIncidencia;
use App\Models\AsistenciaIncidenciaHistory;
use App\Models\User;
use App\Support\Asistencias\DiasEfectivosIncidencia;
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
        $grupos = $registros->groupBy(fn ($item) => $item->grupo_uuid ?: 'registro-'.$item->id)
            ->map(function ($items) {
                $primero = $items->sortBy('fecha')->first();
                $ultimo = $items->sortBy('fecha')->last();
                $masiva = (bool) $primero->es_masiva;
                return [
                    'grupo_uuid' => $primero->grupo_uuid,
                    'user_id' => $masiva ? null : $primero->user_id,
                    'user' => $masiva ? null : $primero->user,
                    'es_masiva' => $masiva,
                    'alcance' => $primero->alcance,
                    'empleados' => $items->pluck('user_id')->unique()->count(),
                    'personalizados' => $items->where('personalizada', true)->pluck('user_id')->unique()->count(),
                    'tipo' => $items->pluck('tipo')->unique()->count() === 1 ? $primero->tipo : 'mixto',
                    'observaciones' => $items->pluck('observaciones')->unique()->count() === 1 ? $primero->observaciones : 'Valores diferentes por empleado',
                    'fecha_desde' => Carbon::parse($primero->fecha)->format('Y-m-d'),
                    'fecha_hasta' => Carbon::parse($ultimo->fecha)->format('Y-m-d'),
                    'dias' => DiasEfectivosIncidencia::paraGrupo($items),
                    'registrado_por' => $primero->registradoPor,
                    'created_at' => $primero->created_at,
                    'updated_at' => $items->max('updated_at'),
                ];
            })->values();

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
            'unidades' => \App\Models\UnidadServicio::query()->orderBy('nombre')->get(['id', 'nombre']),

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
     * Exportaciones: resumen agrupado o detalle real por empleado.
     * Las fechas y filtros se aplican sobre los registros individuales.
     */
    private function exportRows(Request $request): array
    {
        $formato = $request->validate([
            'formato' => 'nullable|in:resumen,detalle',
        ])['formato'] ?? 'resumen';

        if ($formato === 'resumen') {
            $grupos = $this->getFilteredIncidenciaGroups($request);
            $rows = $grupos->map(function ($grupo) {
                return [
                    'empleado' => $grupo['nombre_alcance'] ?? ($grupo['user']?->name ?? ''),
                    'correo' => $grupo['user']?->email ?? '',
                    'tipo' => $this->labelTipo($grupo['tipo'] ?? ''),
                    'fecha_desde' => $this->formatDateExport($grupo['fecha_desde'] ?? null),
                    'fecha_hasta' => $this->formatDateExport($grupo['fecha_hasta'] ?? null),
                    'dias' => $grupo['dias'] ?? ($grupo['es_masiva'] ? 'Por empleado' : 'Sin turno'),
                    'empleados' => $grupo['es_masiva'] ? ($grupo['empleados'] ?? 0) : 1,
                    'observaciones' => $grupo['observaciones'] ?? '',
                    'registrado_por' => $grupo['registrado_por']?->name ?? '',
                ];
            })->values()->all();
            return [$formato, $rows];
        }

        $data = $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'user_id' => 'nullable|integer|exists:users,id',
            'tipo' => 'nullable|in:vacaciones,incapacidad,festivo',
            'search' => 'nullable|string|max:100',
        ]);
        $query = AsistenciaIncidencia::query()->with([
            'user:id,name,email,activo',
            'registradoPor:id,name,email',
        ]);
        if (!empty($data['fecha_desde'])) $query->whereDate('fecha', '>=', $data['fecha_desde']);
        if (!empty($data['fecha_hasta'])) $query->whereDate('fecha', '<=', $data['fecha_hasta']);
        if (!empty($data['user_id'])) $query->where('user_id', $data['user_id']);
        if (!empty($data['tipo'])) $query->where('tipo', $data['tipo']);
        if (!empty($data['search'])) {
            $search = trim($data['search']);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Un periodo sólo puede reunir fechas consecutivas del mismo empleado,
        // grupo, tipo, observaciones y registrador. Nunca sumar días de otros usuarios.
        $records = $query->orderBy('user_id')->orderBy('fecha')->orderBy('id')->get();
        $rows = [];
        $lastKey = null;
        $previousDate = null;
        foreach ($records as $item) {
            $date = Carbon::parse($item->fecha)->toDateString();

            // El detalle exportado sólo debe abarcar fechas efectivas.
            // Si un día de vacaciones coincide con un festivo o descanso,
            // se omite antes de formar los tramos consecutivos. La incidencia
            // original y su historial permanecen intactos.
            if ($item->tipo === 'vacaciones') {
                $diasDeLaFecha = DiasEfectivosIncidencia::paraPeriodo(
                    (int) $item->user_id,
                    $item->tipo,
                    $date,
                    $date
                );

                if ($diasDeLaFecha === 0) {
                    $lastKey = null;
                    $previousDate = null;
                    continue;
                }
            }

            $key = implode('|', [
                $item->grupo_uuid ?: 'registro-'.$item->id,
                $item->user_id, $item->tipo,
                json_encode($item->observaciones, JSON_UNESCAPED_UNICODE),
                $item->registrado_por_user_id,
            ]);
            $consecutive = $previousDate !== null
                && Carbon::parse($previousDate)->addDay()->toDateString() === $date;
            if ($rows && $key === $lastKey && $consecutive) {
                $rows[count($rows) - 1]['fecha_hasta'] = $this->formatDateExport($date);
                $rows[count($rows) - 1]['dias']++;
            } else {
                $rows[] = [
                    'empleado' => $item->user?->name ?? 'Empleado no disponible',
                    'correo' => $item->user?->email ?? '',
                    'tipo' => $this->labelTipo($item->tipo),
                    'fecha_desde' => $this->formatDateExport($date),
                    'fecha_hasta' => $this->formatDateExport($date),
                    'dias' => 1,
                    '_user_id' => (int) $item->user_id,
                    '_tipo' => $item->tipo,
                    'empleados' => 1,
                    'observaciones' => $item->observaciones ?? '',
                    'registrado_por' => $item->registradoPor?->name ?? '',
                ];
            }
            $lastKey = $key;
            $previousDate = $date;
        }
        foreach ($rows as &$row) {
            $userId = (int) ($row['_user_id'] ?? 0);
            if ($userId > 0) {
                $row['dias'] = DiasEfectivosIncidencia::paraPeriodo(
                    $userId,
                    $row['_tipo'],
                    Carbon::createFromFormat('d/m/Y', $row['fecha_desde'])->toDateString(),
                    Carbon::createFromFormat('d/m/Y', $row['fecha_hasta'])->toDateString()
                );
            }
            $row['dias'] ??= 'Sin turno';
            unset($row['_user_id'], $row['_tipo']);
        }
        unset($row);
        return [$formato, $rows];
    }

    private function labelTipo(?string $tipo): string
    {
        return match ($tipo) {
            'vacaciones' => 'Vacaciones',
            'incapacidad' => 'Incapacidad',
            'festivo' => 'Día festivo',
            'mixto' => 'Mixto',
            default => $tipo ?? '',
        };
    }

    private function formatDateExport($date): string
    {
        return $date ? Carbon::parse($date)->format('d/m/Y') : '';
    }

    public function exportExcel(Request $request)
    {
        [$formato, $rows] = $this->exportRows($request);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($formato === 'detalle' ? 'Detalle empleados' : 'Resumen incidencias');
        $headers = ['Empleado / alcance', 'Correo', 'Tipo', 'Desde', 'Hasta', 'Días'];
        if ($formato === 'resumen') $headers[] = 'Empleados';
        $headers[] = 'Observaciones';
        $headers[] = 'Registrado por';
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REPORTE DE INCIDENCIAS DE ASISTENCIA - '.strtoupper($formato));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Generado: '.Carbon::now(self::TIMEZONE)->format('d/m/Y H:i:s'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach ($headers as $i => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$column}3", $header);
        }
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastCol}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle("A3:{$lastCol}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach ($rows as $i => $row) {
            $values = [$row['empleado'], $row['correo'], $row['tipo'], $row['fecha_desde'], $row['fecha_hasta'], $row['dias']];
            if ($formato === 'resumen') $values[] = $row['empleados'];
            $values[] = $row['observaciones'];
            $values[] = $row['registrado_por'];
            foreach ($values as $j => $value) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1);
                $cell = "{$column}".($i + 4);
                if (is_numeric($value) && !is_string($value)) {
                    $sheet->setCellValue($cell, $value);
                } else {
                    $sheet->setCellValueExplicit($cell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
        }
        $lastRow = max(3, count($rows) + 3);
        $sheet->getStyle("A3:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A3:{$lastCol}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->freezePane('A4');
        $fileName = 'incidencias_'.$formato.'_'.Carbon::now(self::TIMEZONE)->format('Ymd_His').'.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportPdf(Request $request)
    {
        [$formato, $rows] = $this->exportRows($request);
        $pdf = Pdf::loadView('admin.asistencia_incidencias.export_pdf', [
            'rows' => $rows,
            'formato' => $formato,
            'generatedAt' => Carbon::now(self::TIMEZONE)->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');
        $fileName = 'incidencias_'.$formato.'_'.Carbon::now(self::TIMEZONE)->format('Ymd_His').'.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Crear una incidencia
     * para una fecha o rango.
     */
    public function store(Request $request)
    {
        if ($request->input('alcance', 'individual') !== 'individual') {
            return $this->storeMasiva($request);
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
                ->where(function ($q) use ($data) {
                    $data['tipo'] === 'festivo'
                        ? $q->where('tipo', 'festivo')
                        : $q->where('tipo', '!=', 'festivo');
                })
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
        $masiva = AsistenciaIncidencia::where('grupo_uuid', $grupoUuid)->where('es_masiva', true)->exists();
        if ($masiva) {
            return response()->json($this->getMasivaDetail($grupoUuid));
        }

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
                DiasEfectivosIncidencia::paraGrupo($registros),

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
    
        $esMasivaHistorica = $history->contains(fn ($h) =>
            (bool) data_get($h->snapshot, 'es_masiva', false)
        );
        $creacion = $history->first(fn ($h) => $h->action === 'created');
        $snapshotCreacion = $creacion?->snapshot ?? [];
        $miembrosOriginales = $snapshotCreacion['miembros'] ?? [];
        $miembrosActuales = $latestSnapshot['miembros'] ?? [];
        $totalOriginal = $esMasivaHistorica
            ? ($snapshotCreacion['empleados'] ?? data_get($creacion?->changes, 'empleados'))
            : null;
        $totalActual = $esMasivaHistorica
            ? ($history->last()?->action === 'deleted' ? 0 : ($latestSnapshot['empleados'] ?? null))
            : null;

        // El snapshot es la fotografía histórica; el total efectivo
        // debe reflejar los turnos y festivos vigentes al consultar.
        // Si la incidencia fue eliminada, no hay días actuales que
        // recalcular y se conserva el conteo histórico del snapshot.
        $diasOriginales = ($latestSnapshot['es_masiva'] ?? false)
            && !empty($latestSnapshot['fecha_desde'])
            && !empty($latestSnapshot['fecha_hasta'])
                ? (int) Carbon::parse($latestSnapshot['fecha_desde'])
                    ->diffInDays(Carbon::parse($latestSnapshot['fecha_hasta'])) + 1
                : ($latestSnapshot['dias'] ?? null);

        $eliminada = $history->last()?->action === 'deleted';
        $registrosActuales = $eliminada
            ? collect()
            : AsistenciaIncidencia::query()
                ->where('grupo_uuid', $grupoUuid)
                ->get();
        $diasEfectivos = $registrosActuales->isNotEmpty()
            ? DiasEfectivosIncidencia::paraGrupo($registrosActuales)
            : null;

        return response()->json([
            'incidencia' => [
                'grupo_uuid' =>
                    $grupoUuid,
    
                'es_masiva' => $esMasivaHistorica,
                'empleados' => $latestSnapshot['empleados'] ?? null,
                'empleados_originales' => $totalOriginal,
                'empleados_actuales' => $totalActual,
                'miembros_originales' => $miembrosOriginales,
                'miembros_actuales' => $miembrosActuales,
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
    
                // "dias" sigue disponible para compatibilidad; los campos
                // adicionales permiten distinguir el dato original del actual.
                'dias' => $registrosActuales->isNotEmpty()
                    ? $diasEfectivos
                    : $diasOriginales,
                'dias_originales' => $diasOriginales,
                'dias_efectivos' => $diasEfectivos,
                'dias_recalculados' => $registrosActuales->isNotEmpty(),
    
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
    
                        $esMasiva = $items->contains(fn ($h) =>
                            (bool) data_get($h->snapshot, 'es_masiva', false)
                        );
                        $creacion = $items->first(fn ($h) => $h->action === 'created');
                        $origen = $creacion?->snapshot ?? [];
                        $originales = $origen['miembros'] ?? [];
                        $actuales = $snapshot['miembros'] ?? [];
                        $cantidadOriginal = $esMasiva
                            ? ($origen['empleados'] ?? data_get($creacion?->changes, 'empleados'))
                            : null;
                        $cantidadActual = $esMasiva
                            ? ($deleted ? 0 : ($snapshot['empleados'] ?? null))
                            : null;
                        // Una edición puede alterar el tipo o periodo de parte
                        // del grupo: indicar 'mixto' en vez de atribuir el tipo
                        // o el periodo individual a todos sus integrantes.
                        $tipos = $items->flatMap(fn ($h) =>
                            collect(data_get($h->changes, 'detalle_empleados', []))
                                ->pluck('despues.tipo')
                        )->filter()->unique();
                        $tipoResumen = $snapshot['tipo'] ?? null;
                        if ($esMasiva && $tipos->count() > 1) {
                            $tipoResumen = 'mixto';
                        }

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
                            'es_masiva' => $esMasiva,
                            'alcance' => $origen['alcance'] ?? $snapshot['alcance'] ?? null,
                            'empleados_originales' => $cantidadOriginal,
                            'empleados_actuales' => $cantidadActual,
                            'miembros_originales' => $originales,
                            'miembros_actuales' => $actuales,
    
                            'empleado' =>
                                $snapshot[
                                    'empleado'
                                ] ?? null,
    
                            'tipo' => $tipoResumen,
    
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
        if (AsistenciaIncidencia::where('grupo_uuid', $grupoUuid)->where('es_masiva', true)->exists()) {
            return response()->json(['message' => 'Usa la edición por empleados para incidencias masivas.'], 422);
        }

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
                ->where(function ($q) use ($data) {
                    $data['tipo'] === 'festivo'
                        ? $q->where('tipo', 'festivo')
                        : $q->where('tipo', '!=', 'festivo');
                })
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

    /** Captura masiva: conserva una fila por empleado y fecha, con UUID común. */
    private function storeMasiva(Request $request)
    {
        $data = $request->validate([
            'alcance' => 'required|in:todos,unidad,seleccion',
            'unidad_id' => 'required_if:alcance,unidad|nullable|integer|exists:unidades_servicio,id',
            'user_ids' => 'required_if:alcance,seleccion|array',
            'user_ids.*' => 'integer|distinct|exists:users,id',
            'tipo' => 'required|in:vacaciones,incapacidad,festivo',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'observaciones' => 'nullable|string|max:2000',
        ]);
        $desde = Carbon::parse($data['fecha_desde'])->startOfDay();
        $hasta = Carbon::parse($data['fecha_hasta'])->startOfDay();
        $dias = (int) $desde->diffInDays($hasta) + 1;
        if ($dias > 366) throw ValidationException::withMessages(['fecha_hasta' => 'Máximo 366 días.']);
        $usuarios = User::where('activo', true);
        if ($data['alcance'] === 'unidad') {
            $usuarios->whereHas('unidadesServicio', fn ($q) => $q->where('unidades_servicio.id', $data['unidad_id']));
        } elseif ($data['alcance'] === 'seleccion') {
            $usuarios->whereIn('id', $data['user_ids'] ?? []);
        }
        $ids = $usuarios->pluck('id')->all();
        if (!$ids) throw ValidationException::withMessages(['alcance' => 'No hay empleados activos para el alcance seleccionado.']);
        $ocupados = AsistenciaIncidencia::whereIn('user_id', $ids)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where(function ($q) use ($data) {
                $data['tipo'] === 'festivo'
                    ? $q->where('tipo', 'festivo')
                    : $q->where('tipo', '!=', 'festivo');
            })->get(['user_id', 'fecha']);
        $asistencias = Asistencia::whereIn('user_id', $ids)->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])->get(['user_id', 'fecha']);
        // Política segura: no reemplazar registros ni asistencias ya existentes.
        if ($ocupados->isNotEmpty() || $asistencias->isNotEmpty()) {
            throw ValidationException::withMessages(['fecha_desde' => 'No se registró ninguna incidencia: existen '. $ocupados->count().' incidencias y '.$asistencias->count().' asistencias en el rango para los empleados seleccionados. Ajusta el alcance o las fechas.']);
        }
        $uuid = (string) Str::uuid();
        DB::transaction(function () use ($ids, $data, $desde, $hasta, $uuid, $request) {
            $now = now();
            $rows = [];
            foreach ($ids as $id) {
                for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
                    $rows[] = [
                        'user_id' => $id, 'grupo_uuid' => $uuid, 'es_masiva' => true,
                        'alcance' => $data['alcance'], 'personalizada' => false,
                        'fecha' => $dia->toDateString(), 'tipo' => $data['tipo'],
                        'observaciones' => $data['observaciones'] ?? null,
                        'registrado_por_user_id' => $request->user()->id,
                        'created_at' => $now, 'updated_at' => $now,
                    ];
                    if (count($rows) >= 500) { AsistenciaIncidencia::insert($rows); $rows = []; }
                }
            }
            if ($rows) AsistenciaIncidencia::insert($rows);
            AsistenciaIncidenciaHistory::create([
                'grupo_uuid' => $uuid, 'action' => 'created', 'changes' => ['alcance' => $data['alcance'], 'empleados' => count($ids)],
                'snapshot' => $this->buildGroupSnapshot($uuid), 'actor_user_id' => $request->user()->id,
            ]);
        });
        return response()->json(['message' => 'Incidencia masiva registrada para '.count($ids).' empleados.', 'grupo_uuid' => $uuid], 201);
    }

    private function getMasivaDetail(string $uuid): array
    {
        $rows = AsistenciaIncidencia::with('user:id,name,email,activo')->where('grupo_uuid', $uuid)->orderBy('fecha')->get();
        if ($rows->isEmpty()) abort(404);
        $first = $rows->first();
        $members = $rows->groupBy('user_id')->map(function ($entries, $id) {
            $first = $entries->first();
            // Los periodos del empleado pueden no ser consecutivos ni tener el mismo tipo.
            // No presentar simplemente MIN/MAX como un periodo continuo.
            $periodos = [];
            foreach ($entries->sortBy('fecha') as $entry) {
                $fecha = Carbon::parse($entry->fecha)->format('Y-m-d');
                $lastIndex = count($periodos) - 1;
                $sePuedeUnir = $lastIndex >= 0
                    && Carbon::parse($periodos[$lastIndex]['fecha_hasta'])->addDay()->format('Y-m-d') === $fecha
                    && $periodos[$lastIndex]['tipo'] === $entry->tipo
                    && $periodos[$lastIndex]['observaciones'] === $entry->observaciones;
                if ($sePuedeUnir) {
                    $periodos[$lastIndex]['fecha_hasta'] = $fecha;
                    $periodos[$lastIndex]['dias'] = DiasEfectivosIncidencia::paraPeriodo((int) $id, $entry->tipo, $periodos[$lastIndex]['fecha_desde'], $fecha);
                } else {
                    $periodos[] = [
                        'tipo' => $entry->tipo,
                        'fecha_desde' => $fecha,
                        'fecha_hasta' => $fecha,
                        'dias' => DiasEfectivosIncidencia::paraPeriodo((int) $id, $entry->tipo, $fecha, $fecha),
                        'observaciones' => $entry->observaciones,
                    ];
                }
            }
            return [
                'user_id' => (int) $id, 'user' => $first->user,
                'periodos' => $periodos,
                'personalizada' => $entries->contains(fn ($r) => $r->personalizada),
                'tipo' => $entries->pluck('tipo')->unique()->count() === 1 ? $first->tipo : 'mixto',
                'fecha_desde' => Carbon::parse($entries->min('fecha'))->format('Y-m-d'),
                'fecha_hasta' => Carbon::parse($entries->max('fecha'))->format('Y-m-d'),
                'observaciones' => $first->observaciones,
            ];
        })->values();
        return [
            'grupo_uuid' => $uuid, 'es_masiva' => true, 'alcance' => $first->alcance,
            'tipo' => $first->tipo, 'fecha_desde' => Carbon::parse($rows->min('fecha'))->format('Y-m-d'),
            'fecha_hasta' => Carbon::parse($rows->max('fecha'))->format('Y-m-d'),
            'observaciones' => $first->observaciones, 'empleados' => $members,
        ];
    }

    /** Editar solo miembros seleccionados, todos, o solo miembros sin personalizar. */
    public function updateMasiva(Request $request, string $grupoUuid)
    {
        $data = $request->validate([
            'modo' => 'required|in:seleccion,todos,originales',
            'user_ids' => 'required_if:modo,seleccion|array',
            'user_ids.*' => 'integer|distinct|exists:users,id',
            'tipo' => 'required|in:vacaciones,incapacidad,festivo',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'observaciones' => 'nullable|string|max:2000',
        ]);
        $desde = Carbon::parse($data['fecha_desde'])->startOfDay();
        $hasta = Carbon::parse($data['fecha_hasta'])->startOfDay();
        if ($desde->diffInDays($hasta) + 1 > 366) throw ValidationException::withMessages(['fecha_hasta' => 'Máximo 366 días.']);
        $group = AsistenciaIncidencia::where('grupo_uuid', $grupoUuid)->where('es_masiva', true);
        if (!$group->exists()) abort(404);
        $memberIds = (clone $group)->when($data['modo'] === 'originales', fn ($q) => $q->where('personalizada', false))
            ->pluck('user_id')->unique()->values();
        if ($data['modo'] === 'seleccion') $memberIds = $memberIds->intersect($data['user_ids'] ?? [])->values();
        if ($memberIds->isEmpty()) throw ValidationException::withMessages(['user_ids' => 'No hay empleados seleccionados para modificar.']);
        $conflicts = AsistenciaIncidencia::whereIn('user_id', $memberIds)->where('grupo_uuid', '!=', $grupoUuid)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where(function ($q) use ($data) {
                $data['tipo'] === 'festivo'
                    ? $q->where('tipo', 'festivo')
                    : $q->where('tipo', '!=', 'festivo');
            })->exists();
        $attendance = Asistencia::whereIn('user_id', $memberIds)->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])->exists();
        if ($conflicts || $attendance) throw ValidationException::withMessages(['fecha_desde' => 'Hay asistencias u otras incidencias en las fechas elegidas. No se modificó ningún empleado.']);
        DB::transaction(function () use ($group, $memberIds, $data, $desde, $hasta, $grupoUuid, $request) {
            $before = $this->buildGroupSnapshot($grupoUuid);
            $old = (clone $group)->whereIn('user_id', $memberIds)->get()->groupBy('user_id');
            (clone $group)->whereIn('user_id', $memberIds)->delete();
            $rows = []; $now = now();
            foreach ($memberIds as $id) {
                $initial = $old[$id]->first();
                for ($day = $desde->copy(); $day->lte($hasta); $day->addDay()) {
                    $rows[] = [
                        'user_id' => $id, 'grupo_uuid' => $grupoUuid, 'es_masiva' => true,
                        'alcance' => $initial->alcance, 'personalizada' => true,
                        'fecha' => $day->toDateString(), 'tipo' => $data['tipo'],
                        'observaciones' => $data['observaciones'] ?? null,
                        'registrado_por_user_id' => $initial->registrado_por_user_id,
                        'created_at' => $initial->created_at, 'updated_at' => $now,
                    ];
                    if (count($rows) >= 500) { AsistenciaIncidencia::insert($rows); $rows = []; }
                }
            }
            if ($rows) AsistenciaIncidencia::insert($rows);
            // Historial legible por empleado: registrar estado anterior y nuevo.
            $nombres = User::whereIn('id', $memberIds)->get(['id', 'name'])->keyBy('id');
            $detalleCambios = [];
            foreach ($memberIds as $id) {
                $anteriores = $old->get($id, collect());
                $detalleCambios[] = [
                    'user_id' => (int) $id,
                    'empleado' => $nombres->get($id)?->name ?? ('Empleado #'.$id),
                    'antes' => [
                        'tipo' => $anteriores->first()?->tipo,
                        'fecha_desde' => $anteriores->min('fecha'),
                        'fecha_hasta' => $anteriores->max('fecha'),
                        'observaciones' => $anteriores->first()?->observaciones,
                    ],
                    'despues' => [
                        'tipo' => $data['tipo'],
                        'fecha_desde' => $desde->toDateString(),
                        'fecha_hasta' => $hasta->toDateString(),
                        'observaciones' => $data['observaciones'] ?? null,
                    ],
                ];
            }
            AsistenciaIncidenciaHistory::create([
                'grupo_uuid' => $grupoUuid, 'action' => 'updated',
                'changes' => ['modo' => $data['modo'], 'empleados_modificados' => $memberIds->count(), 'user_ids' => $memberIds->all(), 'detalle_empleados' => $detalleCambios],
                'snapshot' => $this->buildGroupSnapshot($grupoUuid), 'actor_user_id' => $request->user()->id,
            ]);
        });
        return response()->json(['message' => 'Se actualizaron '. $memberIds->count().' empleados sin modificar los demás.']);
    }

    /** Quitar uno o más miembros de una captura masiva, manteniendo el resto. */
    public function deleteMiembros(Request $request, string $grupoUuid)
    {
        $data = $request->validate(['user_ids' => 'required|array|min:1', 'user_ids.*' => 'integer|distinct|exists:users,id']);
        $query = AsistenciaIncidencia::where('grupo_uuid', $grupoUuid)->where('es_masiva', true)->whereIn('user_id', $data['user_ids']);
        if (!$query->exists()) abort(404);
        DB::transaction(function () use ($query, $grupoUuid, $data, $request) {
            $removed = (clone $query)->pluck('user_id')->unique()->values()->all();
            $query->delete();
            AsistenciaIncidenciaHistory::create([
                'grupo_uuid' => $grupoUuid, 'action' => 'updated',
                'changes' => ['empleados_retirados' => count($removed), 'user_ids' => $removed, 'nombres_empleados' => User::whereIn('id', $removed)->pluck('name')->all()],
                'snapshot' => $this->buildGroupSnapshot($grupoUuid), 'actor_user_id' => $request->user()->id,
            ]);
        });
        return response()->json(['message' => 'Empleados retirados de la incidencia masiva.']);
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
    
        return $registros->groupBy(fn ($item) => $item->grupo_uuid ?: 'registro-'.$item->id)
            ->map(function ($items) {
                $first = $items->sortBy('fecha')->first();
                $last = $items->sortBy('fecha')->last();
                $bulk = (bool) $first->es_masiva;
                $memberCount = $items->pluck('user_id')->unique()->count();
                return [
                    'grupo_uuid' => $first->grupo_uuid,
                    'user_id' => $bulk ? null : $first->user_id,
                    'user' => $bulk ? null : $first->user,
                    'empleados' => $memberCount,
                    'nombre_alcance' => $bulk ? ($first->alcance === 'todos' ? 'Todos los empleados' : ($first->alcance === 'unidad' ? 'Unidad de servicio' : 'Empleados seleccionados')).' ('.$memberCount.')' : null,
                    'es_masiva' => $bulk,
                    'tipo' => $items->pluck('tipo')->unique()->count() === 1 ? $first->tipo : 'mixto',
                    'observaciones' => $items->pluck('observaciones')->unique()->count() === 1 ? $first->observaciones : 'Valores diferentes por empleado',
                    'fecha_desde' => Carbon::parse($first->fecha)->format('Y-m-d'),
                    'fecha_hasta' => Carbon::parse($last->fecha)->format('Y-m-d'),
                    'dias' => DiasEfectivosIncidencia::paraGrupo($items),
                    'registrado_por' => $first->registradoPor,
                ];
            })->values();
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
            'es_masiva' => (bool) $primero->es_masiva,
            'alcance' => $primero->alcance,
            'empleados' => $registros->pluck('user_id')->unique()->count(),
            'personalizados' => $registros->where('personalizada', true)->pluck('user_id')->unique()->count(),
            // Guardar la nómina en cada snapshot masivo permite recuperar
            // los integrantes aunque posteriormente se eliminen sus filas.
            'miembros' => (bool) $primero->es_masiva
                ? $registros->unique('user_id')->map(fn ($r) => [
                    'id' => (int) $r->user_id,
                    'name' => $r->user?->name,
                    'email' => $r->user?->email,
                ])->values()->all()
                : [],
    
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
                DiasEfectivosIncidencia::paraGrupo($registros),
    
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