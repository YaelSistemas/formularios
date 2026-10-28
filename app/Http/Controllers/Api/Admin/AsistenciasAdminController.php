<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\AsistenciaHistory;
use App\Models\AsistenciaIncidencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AsistenciasAdminController extends Controller
{
    private const TIMEZONE = 'America/Mexico_City';

    /**
     * Consulta administrativa de asistencias.
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
    
            'unidad_servicio_id' => [
                'nullable',
                'integer',
            ],
            
            'empresa_id' => [
                'nullable',
                'integer',
            ],
            
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
    
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
    
        $query =
            $this->buildFilteredRegistrosQuery(
                $data
            );
    
    
        $registros =
            $query->paginate(
                $perPage
            );
    
        /*
         * Normalizar horarios para evitar
         * conversiones de zona horaria
         * en el navegador.
         */
        $registros->setCollection(
            $registros
                ->getCollection()
                ->map(
                    function ($registro) {
                        $item =
                            (array) $registro;
    
                        $item['hora_entrada'] =
                            $this->formatDateTime(
                                $registro
                                    ->hora_entrada
                            );
    
                        $item['hora_inicio_comida'] =
                            $this->formatDateTime(
                                $registro
                                    ->hora_inicio_comida
                            );
    
                        $item['hora_fin_comida'] =
                            $this->formatDateTime(
                                $registro
                                    ->hora_fin_comida
                            );
    
                        $item['hora_salida'] =
                            $this->formatDateTime(
                                $registro
                                    ->hora_salida
                            );
    
                        return $item;
                    }
                )
        );
    
        /*
         * Empleados que tienen al menos
         * una asistencia o una incidencia.
         */
        $empleados =
            DB::table('users')
                ->where(
                    function ($query) {
                        $query
                            ->whereExists(
                                function ($q) {
                                    $q->select(
                                        DB::raw(1)
                                    )
                                    ->from(
                                        'asistencias'
                                    )
                                    ->whereColumn(
                                        'asistencias.user_id',
                                        'users.id'
                                    );
                                }
                            )
                            ->orWhereExists(
                                function ($q) {
                                    $q->select(
                                        DB::raw(1)
                                    )
                                    ->from(
                                        'asistencia_incidencias'
                                    )
                                    ->whereColumn(
                                        'asistencia_incidencias.user_id',
                                        'users.id'
                                    );
                                }
                            );
                    }
                )
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                ])
                ->orderBy(
                    'users.name'
                )
                ->get();
    
        /*
         * Todas las unidades disponibles.
         *
         * Esto permite filtrar también
         * las incidencias por unidad.
         */
        $unidades =
            DB::table(
                'unidades_servicio'
            )
                ->select([
                    'id',
                    'nombre',
                ])
                ->orderBy(
                    'nombre'
                )
                ->get();
        
        /*
         * Todas las empresas disponibles
         * para filtrar por Empresa laboral.
         */
        $empresas =
            DB::table(
                'empresas'
            )
                ->select([
                    'id',
                    'nombre',
                ])
                ->orderBy(
                    'nombre'
                )
                ->get();
        
        return response()->json([
            'registros' =>
                $registros,
    
            'filtros' => [
                'empleados' =>
                    $empleados,
            
                'empresas' =>
                    $empresas,
            
                'unidades' =>
                    $unidades,
            ],
        ]);
    }

    public function exportExcel(
        Request $request
    ) {
        $data =
            $this->validateExportFilters(
                $request
            );
    
        $registros =
            $this
                ->buildFilteredRegistrosQuery(
                    $data
                )
                ->get();
    
        $rows =
            $this->mapExportRows(
                $registros
            );
    
        $spreadsheet =
            new Spreadsheet();
    
        $sheet =
            $spreadsheet
                ->getActiveSheet();
    
        $sheet->setTitle(
            'Asistencias'
        );
    
        $sheet->mergeCells(
            'A1:U1'
        );
    
        $sheet->setCellValue(
            'A1',
            'REPORTE DE ASISTENCIAS'
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
    
        $headers = [
            'Fecha',
            'Empleado',
            'Correo',
            'Empresa laboral',
            'Unidad laboral',
            'Turno',
            'Entrada',
            'Inicio comida',
            'Fin comida',
            'Salida',
            'Presencia',
            'Tiempo comida',
            'Tiempo trabajado',
            'Estado día',
            'Estado entrada',
            'Estado salida',
            'Estado comida',
            'Jornada',
            'Método entrada',
            'Incidencia',
            'Observaciones incidencia',
        ];
    
        $sheet->fromArray(
            $headers,
            null,
            'A3'
        );
    
        $sheet
            ->getStyle(
                'A3:U3'
            )
            ->getFont()
            ->setBold(true);
    
        $sheet
            ->getStyle(
                'A3:U3'
            )
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB(
                'FFE2E8F0'
            );
    
        $excelRow = 4;
    
        foreach (
            $rows as $row
        ) {
            $sheet->fromArray(
                [
                    $row['fecha'],
                    $row['empleado'],
                    $row['correo'],
                    $row['empresa'],
                    $row['unidad'],
                    $row['turno'],
                    $row['entrada'],
                    $row['inicio_comida'],
                    $row['fin_comida'],
                    $row['salida'],
                    $row['presencia'],
                    $row['comida'],
                    $row['trabajado'],
                    $row['estado_dia'],
                    $row['estado_entrada'],
                    $row['estado_salida'],
                    $row['estado_comida'],
                    $row['estado_jornada'],
                    $row['metodo_entrada'],
                    $row['incidencia_tipo'],
                    $row['incidencia_observaciones'],
                ],
                null,
                "A{$excelRow}"
            );
    
            $excelRow++;
        }
    
        if (
            $excelRow > 4
        ) {
            $sheet
                ->getStyle(
                    'A3:U' .
                    ($excelRow - 1)
                )
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(
                    Border::BORDER_THIN
                );
        }
    
        $sheet->freezePane(
            'A4'
        );
    
        foreach (
            range(
                'A',
                'U'
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
    
        $sheet
            ->getStyle(
                'A:U'
            )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    
        $fileName =
            'asistencias_' .
            Carbon::now(
                self::TIMEZONE
            )->format(
                'Ymd_His'
            ) .
            '.xlsx';
    
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
        $data =
            $this->validateExportFilters(
                $request
            );
    
        $registros =
            $this
                ->buildFilteredRegistrosQuery(
                    $data
                )
                ->get();
    
        $rows =
            $this->mapExportRows(
                $registros
            );
    
        $generatedAt =
            Carbon::now(
                self::TIMEZONE
            )->format(
                'd/m/Y H:i:s'
            );
    
        $pdf =
            Pdf::loadView(
                'admin.asistencias.export_pdf',
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
            'asistencias_' .
            Carbon::now(
                self::TIMEZONE
            )->format(
                'Ymd_His'
            ) .
            '.pdf';
    
        return $pdf->download(
            $fileName
        );
    }

    /**
     * Ver detalle de una asistencia.
     */
    public function show(
        Request $request,
        Asistencia $asistencia
    ) {
        $usuarioSolicitante =
            $request->user();
    
        /*
         * La ubicación solamente se entrega
         * a Administradores o usuarios con
         * asistencias.ubicacion.view.
         */
        $puedeVerUbicacion =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole(
                        'Administrador'
                    )
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.ubicacion.view'
                    )
            );

        /*
         * La fotografía solamente se entrega
         * a Administradores o usuarios con
         * asistencias.foto.view.
         */
        $puedeVerFoto =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole(
                        'Administrador'
                    )
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.foto.view'
                    )
            );
    
        /*
         * La firma solamente se entrega
         * a Administradores o usuarios con
         * asistencias.firma.view.
         */
        $puedeVerFirma =
            $usuarioSolicitante
            && (
                $usuarioSolicitante
                    ->hasRole(
                        'Administrador'
                    )
                ||
                $usuarioSolicitante
                    ->hasPermissionTo(
                        'asistencias.firma.view'
                    )
            );

        return response()->json([
            'asistencia' => [
                'id' =>
                    $asistencia->id,
    
                'user_id' =>
                    $asistencia->user_id,
    
                'fecha' =>
                    $asistencia->fecha,
    
                'empleado' => [
                    'id' =>
                        $asistencia->user?->id,
    
                    'name' =>
                        $asistencia->user?->name,
    
                    'email' =>
                        $asistencia->user?->email,
                ],
    
                /*
                 * Información histórica.
                 */
                'unidad_laboral' => [
                    'id' =>
                        $asistencia
                            ->unidad_servicio_id_snapshot,
                
                    'nombre' =>
                        $asistencia
                            ->unidad_servicio_nombre_snapshot,
                ],
                
                'empresa_laboral' => [
                    'id' =>
                        $asistencia
                            ->empresa_id_snapshot,
                
                    'nombre' =>
                        $asistencia
                            ->empresa_nombre_snapshot,
                ],
                
                'tipo_trabajo' =>
                    $asistencia
                        ->tipo_trabajo_snapshot,
    
                'turno' => [
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
                        $asistencia
                            ->turno_horas_jornada_snapshot,
    
                    'minutos_comida' =>
                        $asistencia
                            ->turno_minutos_comida_snapshot,
    
                    'comida_cuenta_jornada' =>
                        (bool) $asistencia
                            ->turno_comida_cuenta_jornada_snapshot,
                ],
    
                /*
                 * Horarios.
                 */
                'hora_entrada' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_entrada
                    ),
    
                'hora_inicio_comida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_inicio_comida
                    ),
    
                'hora_fin_comida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_fin_comida
                    ),
    
                'hora_salida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_salida
                    ),
    
                /*
                 * Duraciones.
                 */
                'segundos_presencia' =>
                    $asistencia
                        ->segundos_presencia,
    
                'segundos_comida' =>
                    $asistencia
                        ->segundos_comida,
    
                'segundos_trabajados' =>
                    $asistencia
                        ->segundos_trabajados,
    
                /*
                 * Estados.
                 */
                'estado_dia' =>
                    $asistencia
                        ->estado_dia,
    
                'estado_entrada' =>
                    $asistencia
                        ->estado_entrada,
    
                'estado_salida' =>
                    $asistencia
                        ->estado_salida,
    
                'estado_comida' =>
                    $asistencia
                        ->estado_comida,
    
                'estado_jornada' =>
                    $asistencia
                        ->estado_jornada,
    
                'metodo_entrada' =>
                    $asistencia
                        ->metodo_entrada,
    
                /*
                 * Evidencia.
                 */
                'firma_entrada_url' =>
                    $puedeVerFirma
                    && $asistencia
                        ->firma_entrada
                        ? '/storage/' .
                            ltrim(
                                $asistencia
                                    ->firma_entrada,
                                '/'
                            )
                        : null,
                
                'firma_capturada_at' =>
                    $puedeVerFirma
                        ? $asistencia
                            ->firma_capturada_at
                        : null,
                
                'puede_ver_firma' =>
                    $puedeVerFirma,
                
                'foto_entrada_url' =>
                    $puedeVerFoto
                    && $asistencia
                        ->foto_entrada
                        ? '/storage/' .
                            ltrim(
                                $asistencia
                                    ->foto_entrada,
                                '/'
                            )
                        : null,
                
                'foto_capturada_at' =>
                    $puedeVerFoto
                        ? $asistencia
                            ->foto_capturada_at
                        : null,
                
                'puede_ver_foto' =>
                    $puedeVerFoto,
    
                /*
                 * Ubicación protegida.
                 */
                'entrada_latitud' =>
                    $puedeVerUbicacion
                    && $asistencia
                        ->entrada_latitud
                        !== null
                        ? (float)
                            $asistencia
                                ->entrada_latitud
                        : null,
    
                'entrada_longitud' =>
                    $puedeVerUbicacion
                    && $asistencia
                        ->entrada_longitud
                        !== null
                        ? (float)
                            $asistencia
                                ->entrada_longitud
                        : null,
    
                'entrada_precision_metros' =>
                    $puedeVerUbicacion
                    && $asistencia
                        ->entrada_precision_metros
                        !== null
                        ? (float)
                            $asistencia
                                ->entrada_precision_metros
                        : null,
    
                'entrada_ubicacion_capturada_at' =>
                    $puedeVerUbicacion
                        ? $this->formatDateTime(
                            $asistencia
                                ->entrada_ubicacion_capturada_at
                        )
                        : null,
    
                'puede_ver_ubicacion' =>
                    $puedeVerUbicacion,
            ],
        ]);
    }

    /**
     * Historial administrativo de una asistencia.
     */
    public function history(
        Request $request,
        Asistencia $asistencia
    ) {
        $user =
            $request->user();
    
        /*
         * Por ahora mantenemos el mismo criterio
         * que en el historial de Roles:
         * solamente Administrador.
         */
        if (
            !$user ||
            !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para consultar el historial de asistencias.',
            ], 403);
        }
    
        $history =
            AsistenciaHistory::query()
                ->where(
                    'asistencia_id',
                    $asistencia->id
                )
                ->with([
                    'actor:id,name,email',
                ])
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->map(
                    function (
                        AsistenciaHistory $item
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
                                $this->formatDateTime(
                                    $item->created_at
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
            'asistencia' => [
                'id' =>
                    $asistencia->id,
    
                'empleado' => [
                    'id' =>
                        $asistencia
                            ->user?->id,
    
                    'name' =>
                        $asistencia
                            ->user?->name,
    
                    'email' =>
                        $asistencia
                            ->user?->email,
                ],
    
                'fecha' =>
                    $asistencia->fecha,
            ],
    
            'history' =>
                $history,
        ]);
    }

    /**
     * Historial general de asistencias.
     *
     * Incluye también asistencias que ya
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
            !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para consultar el historial general de asistencias.',
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
         * asistencias únicas.
         *
         * Una misma asistencia puede tener:
         * created
         * updated
         * updated
         * deleted
         */
        $groups =
            AsistenciaHistory::query()
                ->selectRaw(
                    '
                        asistencia_id,
                        MAX(id) AS latest_history_id,
                        MAX(created_at) AS last_action_at
                    '
                )
                ->groupBy(
                    'asistencia_id'
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
    
        $asistenciaIds =
            collect(
                $groups->items()
            )
                ->pluck(
                    'asistencia_id'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values();
    
        /*
         * Cargar todo el historial únicamente
         * de las asistencias de esta página.
         */
        $histories =
            AsistenciaHistory::query()
                ->whereIn(
                    'asistencia_id',
                    $asistenciaIds
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
                    'asistencia_id'
                );
    
        $rows =
            $asistenciaIds
                ->map(
                    function (
                        int $asistenciaId
                    ) use (
                        $histories
                    ) {
                        $items =
                            $histories->get(
                                $asistenciaId,
                                collect()
                            );
    
                        /*
                         * Si existe creación usamos su
                         * snapshot como información base.
                         *
                         * Para registros antiguos que no
                         * tengan created, usamos el primer
                         * snapshot disponible.
                         */
                        $created =
                            $items->firstWhere(
                                'action',
                                'created'
                            );
    
                        $first =
                            $items->first();
    
                        $latest =
                            $items->last();
    
                        $baseSnapshot =
                            $created?->snapshot
                            ?? $first?->snapshot
                            ?? [];
    
                        $latestSnapshot =
                            $latest?->snapshot
                            ?? $baseSnapshot;
    
                        $deleted =
                            $items->contains(
                                fn ($item) =>
                                    $item->action
                                    === 'deleted'
                            );
    
                        $history =
                            $items
                                ->map(
                                    function (
                                        AsistenciaHistory $item
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
                                                $this->formatDateTime(
                                                    $item->created_at
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
                            'asistencia_id' =>
                                $asistenciaId,
    
                            'empleado_nombre' =>
                                $baseSnapshot[
                                    'empleado_nombre'
                                ]
                                ?? $latestSnapshot[
                                    'empleado_nombre'
                                ]
                                ?? null,
    
                            'empleado_email' =>
                                $baseSnapshot[
                                    'empleado_email'
                                ]
                                ?? $latestSnapshot[
                                    'empleado_email'
                                ]
                                ?? null,
    
                            'fecha' =>
                                $baseSnapshot[
                                    'fecha'
                                ]
                                ?? $latestSnapshot[
                                    'fecha'
                                ]
                                ?? null,
                            
                            'empresa_laboral' =>
                                $baseSnapshot[
                                    'empresa_nombre'
                                ]
                                ?? $latestSnapshot[
                                    'empresa_nombre'
                                ]
                                ?? null,
                            
                            'unidad_laboral' =>
                                $baseSnapshot[
                                    'unidad_servicio_nombre'
                                ]
                                ?? $latestSnapshot[
                                    'unidad_servicio_nombre'
                                ]
                                ?? null,
                            
                            'turno' =>
                                $baseSnapshot[
                                    'turno_nombre'
                                ]
                                ?? $latestSnapshot[
                                    'turno_nombre'
                                ]
                                ?? null,
    
                            'deleted' =>
                                $deleted,
    
                            'movimientos' =>
                                $items->count(),
    
                            'last_action_at' =>
                                $latest
                                    ? $this->formatDateTime(
                                        $latest->created_at
                                    )
                                    : null,
    
                            /*
                             * Mandamos el historial completo
                             * para poder abrir el modal sin
                             * depender de que la asistencia
                             * todavía exista.
                             */
                            'history' =>
                                $history,
                        ];
                    }
                )
                ->values();
    
        return response()->json([
            'registros' => [
                'data' =>
                    $rows,
    
                'current_page' =>
                    $groups->currentPage(),
    
                'last_page' =>
                    $groups->lastPage(),
    
                'per_page' =>
                    $groups->perPage(),
    
                'total' =>
                    $groups->total(),
            ],
        ]);
    }

    /**
     * Editar administrativamente una asistencia.
     */
    public function update(
        Request $request,
        Asistencia $asistencia
    ) {
        $user = $request->user();
        
        /*
         * Guardar el estado original antes
         * de realizar cualquier modificación.
         */
        $historyBefore =
            $this->buildHistorySnapshot(
                $asistencia
            );
        
        $data = $request->validate([
            'fecha' => [
                'required',
                'date',
            ],
    
            'hora_entrada' => [
                'required',
                'date_format:H:i:s',
            ],
    
            'hora_inicio_comida' => [
                'nullable',
                'date_format:H:i:s',
            ],
    
            'hora_fin_comida' => [
                'nullable',
                'date_format:H:i:s',
            ],
    
            'hora_salida' => [
                'nullable',
                'date_format:H:i:s',
            ],
        ]);
    
        /*
         * No permitir mover una asistencia
         * a una fecha que tenga una incidencia.
         */
        $incidenciaFecha =
            AsistenciaIncidencia::query()
                ->where(
                    'user_id',
                    $asistencia->user_id
                )
                ->whereDate(
                    'fecha',
                    $data['fecha']
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
                    "No se puede guardar la asistencia porque el empleado tiene registrada la incidencia {$tipoLabel} en esa fecha.",
            ], 409);
        }

        /*
         * Utilizar siempre la configuración
         * histórica del turno guardada
         * en esta asistencia.
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
         * Valores originales para detectar
         * cambios manuales.
         */
        $inicioComidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_inicio_comida'
            );
    
        $finComidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_fin_comida'
            );
    
        $salidaOriginalRaw =
            $asistencia->getRawOriginal(
                'hora_salida'
            );
    
        /*
         * Crear una fecha/hora a partir
         * de la fecha seleccionada y
         * la hora del formulario.
         */
        $crearMomento =
            function (?string $hora) use ($data) {
                if (!$hora) {
                    return null;
                }
    
                return Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $data['fecha'] .
                        ' ' .
                        $hora,
                    self::TIMEZONE
                );
            };
    
        $horaEntrada =
            $crearMomento(
                $data['hora_entrada']
            );
    
        $inicioComida =
            $crearMomento(
                $data['hora_inicio_comida']
                    ?? null
            );
    
        /*
         * Soporte para turnos nocturnos.
         * Si la comida tiene una hora menor
         * que la entrada, se entiende que
         * corresponde al día siguiente.
         */
        if (
            $inicioComida
            && $inicioComida->lt(
                $horaEntrada
            )
        ) {
            $inicioComida->addDay();
        }
    
        $finComida =
            $crearMomento(
                $data['hora_fin_comida']
                    ?? null
            );
    
        if ($finComida) {
            $referencia =
                $inicioComida
                ?? $horaEntrada;
    
            if (
                $finComida->lt(
                    $referencia
                )
            ) {
                $finComida->addDay();
            }
        }
    
        $horaSalida =
            $crearMomento(
                $data['hora_salida']
                    ?? null
            );
    
        if ($horaSalida) {
            $referencia =
                $finComida
                ?? $inicioComida
                ?? $horaEntrada;
    
            if (
                $horaSalida->lt(
                    $referencia
                )
            ) {
                $horaSalida->addDay();
            }
        }
    
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
         * Si existe salida, una comida
         * iniciada debe tener fin.
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
         * No crear comida en un turno
         * que históricamente no la contemplaba.
         *
         * Un registro antiguo que ya tenía
         * comida sí puede corregirse.
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
         * ==========================
         * COMIDA
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
    
        $estadoDia =
            $this->getEstadoDia(
                $horaEntrada,
                $turno
            );
    
        /*
         * Valores para una asistencia
         * que todavía no tiene salida.
         */
        $segundosPresencia = null;
        $segundosTrabajados = null;
    
        $estadoSalida = null;
        $estadoJornada = null;
    
        /*
         * ==========================
         * SALIDA / JORNADA
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
    
            if (
                (bool)
                    $turno
                        ->comida_cuenta_jornada
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
             */
            $segundosJornada =
                (int) round(
                    ((float)
                        $turno->horas_jornada
                    )
                    * 3600
                );
    
            /*
             * Para determinar el estado de
             * la jornada, la comida siempre
             * se descuenta.
             */
            $segundosParaEstadoJornada =
                max(
                    0,
                    $segundosPresencia
                    - $segundosComidaCalculo
                );
    
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
             * Si el turno tiene comida,
             * pero la jornada terminó sin
             * registrarla, queda como menor.
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
         * Detectar cambios de comida
         * para mantener la auditoría.
         */
        $inicioOriginal =
            $inicioComidaOriginalRaw
                ? Carbon::parse(
                    $inicioComidaOriginalRaw,
                    self::TIMEZONE
                )->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        $finOriginal =
            $finComidaOriginalRaw
                ? Carbon::parse(
                    $finComidaOriginalRaw,
                    self::TIMEZONE
                )->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        $salidaOriginal =
            $salidaOriginalRaw
                ? Carbon::parse(
                    $salidaOriginalRaw,
                    self::TIMEZONE
                )->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        $inicioNuevo =
            $inicioComida
                ? $inicioComida->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        $finNuevo =
            $finComida
                ? $finComida->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        $salidaNueva =
            $horaSalida
                ? $horaSalida->format(
                    'Y-m-d H:i:s'
                )
                : null;
    
        /*
         * ==========================
         * GUARDAR HORARIOS
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
         * Auditoría de comida.
         */
        if (!$inicioComida) {
            $asistencia
                ->inicio_comida_registrada_por_user_id =
                null;
    
            $asistencia
                ->metodo_inicio_comida =
                null;
        } elseif (
            $inicioOriginal !==
            $inicioNuevo
        ) {
            $asistencia
                ->inicio_comida_registrada_por_user_id =
                $user->id;
    
            $asistencia
                ->metodo_inicio_comida =
                'manual';
        }
    
        if (!$finComida) {
            $asistencia
                ->fin_comida_registrada_por_user_id =
                null;
    
            $asistencia
                ->metodo_fin_comida =
                null;
        } elseif (
            $finOriginal !==
            $finNuevo
        ) {
            $asistencia
                ->fin_comida_registrada_por_user_id =
                $user->id;
    
            $asistencia
                ->metodo_fin_comida =
                'manual';
        }
    
        /*
         * Auditoría de salida.
         */
        if (!$horaSalida) {
            $asistencia
                ->salida_registrada_por_user_id =
                null;
    
            $asistencia
                ->metodo_salida =
                null;
        } elseif (
            $salidaOriginal !==
            $salidaNueva
        ) {
            $asistencia
                ->salida_registrada_por_user_id =
                $user->id;
    
            $asistencia
                ->metodo_salida =
                'manual';
        }
    
        /*
         * Estados recalculados.
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
         * Segundos exactos.
         */
        $asistencia->segundos_presencia =
            $segundosPresencia;
    
        $asistencia->segundos_comida =
            $segundosComida;
    
        $asistencia->segundos_trabajados =
            $segundosTrabajados;
    
        /*
         * Mantener compatibilidad con
         * las columnas antiguas de minutos.
         */
        $asistencia->minutos_presencia =
            $segundosPresencia !== null
                ? (int) floor(
                    $segundosPresencia / 60
                )
                : null;
    
        $asistencia->minutos_comida =
            $segundosComida !== null
                ? (int) floor(
                    $segundosComida / 60
                )
                : null;
    
        $asistencia->minutos_trabajados =
            $segundosTrabajados !== null
                ? (int) floor(
                    $segundosTrabajados / 60
                )
                : null;
    
        /*
         * Generar el estado final y detectar
         * exactamente qué campos cambiaron.
         */
        $historyAfter =
            $this->buildHistorySnapshot(
                $asistencia
            );
        
        $historyChanges =
            $this->buildHistoryChanges(
                $historyBefore,
                $historyAfter
            );
        
        /*
         * La actualización de la asistencia y
         * su auditoría forman una sola operación.
         */
        DB::transaction(
            function () use (
                $asistencia,
                $user,
                $historyAfter,
                $historyChanges
            ) {
                $asistencia->save();
        
                /*
                 * Si realmente hubo cambios,
                 * registrar la edición.
                 */
                if (!empty($historyChanges)) {
                    AsistenciaHistory::create([
                        'asistencia_id' =>
                            $asistencia->id,
        
                        'actor_user_id' =>
                            $user?->id,
        
                        'action' =>
                            'updated',
        
                        'changes' =>
                            $historyChanges,
        
                        'snapshot' =>
                            $historyAfter,
                    ]);
                }
            }
        );
    
        return response()->json([
            'message' =>
                'Asistencia actualizada correctamente.',
    
            'asistencia' => [
                'id' =>
                    $asistencia->id,
    
                'fecha' =>
                    $asistencia->fecha,
    
                'hora_entrada' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_entrada
                    ),
    
                'hora_inicio_comida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_inicio_comida
                    ),
    
                'hora_fin_comida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_fin_comida
                    ),
    
                'hora_salida' =>
                    $this->formatDateTime(
                        $asistencia
                            ->hora_salida
                    ),
    
                'segundos_presencia' =>
                    $asistencia
                        ->segundos_presencia,
    
                'segundos_comida' =>
                    $asistencia
                        ->segundos_comida,
    
                'segundos_trabajados' =>
                    $asistencia
                        ->segundos_trabajados,
    
                'estado_dia' =>
                    $asistencia
                        ->estado_dia,
    
                'estado_entrada' =>
                    $asistencia
                        ->estado_entrada,
    
                'estado_salida' =>
                    $asistencia
                        ->estado_salida,
    
                'estado_comida' =>
                    $asistencia
                        ->estado_comida,
    
                'estado_jornada' =>
                    $asistencia
                        ->estado_jornada,
            ],
        ]);
    }

    /**
     * Eliminar administrativamente
     * una asistencia.
     */
    public function destroy(
        Request $request,
        Asistencia $asistencia
    ) {
        $user =
            $request->user();
        
        /*
         * Guardar todos los datos antes de
         * eliminar físicamente la asistencia.
         */
        $historySnapshot =
            $this->buildHistorySnapshot(
                $asistencia
            );

        /*
         * Guardar las rutas de evidencia
         * antes de eliminar el registro.
         */
        $firmaEntrada =
            $asistencia->firma_entrada;
    
        $fotoEntrada =
            $asistencia->foto_entrada;
    
        $asistenciaId =
            $asistencia->id;
    
        /*
         * Primero eliminar el registro
         * de la base de datos.
         *
         * Si algo falla aquí, los archivos
         * de evidencia permanecen intactos.
         */
        DB::transaction(
            function () use (
                $asistencia,
                $user,
                $historySnapshot
            ) {
                /*
                 * Primero conservar el historial.
                 */
                AsistenciaHistory::create([
                    'asistencia_id' =>
                        $asistencia->id,
        
                    'actor_user_id' =>
                        $user?->id,
        
                    'action' =>
                        'deleted',
        
                    'changes' =>
                        null,
        
                    'snapshot' =>
                        $historySnapshot,
                ]);
        
                /*
                 * Después eliminar la asistencia.
                 */
                $asistencia->delete();
            }
        );
    
        /*
         * Una vez eliminada correctamente
         * la asistencia, borrar también
         * su firma y fotografía para no
         * dejar archivos huérfanos.
         */
        $archivos =
            array_values(
                array_filter([
                    $firmaEntrada,
                    $fotoEntrada,
                ])
            );
    
        if (!empty($archivos)) {
            Storage::disk('public')
                ->delete($archivos);
        }
    
        return response()->json([
            'message' =>
                'Asistencia eliminada correctamente.',
    
            'id' =>
                $asistenciaId,
        ]);
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
    
    private function getEstadoHorario(
        Carbon $momento,
        $turno
    ): string {
        $hora =
            $momento->format(
                'H:i:s'
            );
    
        $horaInicio =
            substr(
                (string) $turno->hora_inicio,
                0,
                8
            );
    
        $horaFin =
            substr(
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
             * Turnos nocturnos.
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

    private function buildFilteredRegistrosQuery(
        array $data
    ) {
        /*
         * ==========================
         * ASISTENCIAS NORMALES
         * ==========================
         */
        $asistenciasQuery =
            DB::table('asistencias')
                ->leftJoin(
                    'users',
                    'users.id',
                    '=',
                    'asistencias.user_id'
                )
                ->select([
                    'asistencias.id',
                    'asistencias.user_id',
    
                    'users.name as empleado_nombre',
                    'users.email as empleado_email',
    
                    'asistencias.fecha',
    
                    'asistencias.hora_entrada',
                    'asistencias.hora_inicio_comida',
                    'asistencias.hora_fin_comida',
                    'asistencias.hora_salida',
    
                    'asistencias.segundos_presencia',
                    'asistencias.segundos_comida',
                    'asistencias.segundos_trabajados',
    
                    'asistencias.metodo_entrada',
    
                    'asistencias.estado_dia',
                    'asistencias.estado_entrada',
                    'asistencias.estado_salida',
                    'asistencias.estado_comida',
                    'asistencias.estado_jornada',
    
                    'asistencias.unidad_servicio_id_snapshot',
                    'asistencias.unidad_servicio_nombre_snapshot',
                    
                    'asistencias.empresa_id_snapshot',
                    'asistencias.empresa_nombre_snapshot',
                    
                    'asistencias.tipo_trabajo_snapshot',
    
                    'asistencias.turno_nombre_snapshot',
                    'asistencias.turno_hora_inicio_snapshot',
                    'asistencias.turno_hora_fin_snapshot',
                    'asistencias.turno_horas_jornada_snapshot',
                    'asistencias.turno_minutos_comida_snapshot',
                    'asistencias.turno_comida_cuenta_jornada_snapshot',
    
                    DB::raw(
                        "'asistencia' as registro_tipo"
                    ),
    
                    DB::raw(
                        'NULL as incidencia_tipo'
                    ),
    
                    DB::raw(
                        'NULL as incidencia_observaciones'
                    ),
                ]);
    
        /*
         * ==========================
         * INCIDENCIAS
         * ==========================
         *
         * Se obtiene la unidad y turno que
         * correspondían al empleado en la
         * fecha de la incidencia.
         */
        $incidenciasQuery =
            DB::table(
                'asistencia_incidencias'
            )
                ->leftJoin(
                    'users',
                    'users.id',
                    '=',
                    'asistencia_incidencias.user_id'
                )
                ->select([
                    'asistencia_incidencias.id',
                    'asistencia_incidencias.user_id',
    
                    'users.name as empleado_nombre',
                    'users.email as empleado_email',
    
                    'asistencia_incidencias.fecha',
    
                    DB::raw(
                        'NULL as hora_entrada'
                    ),
    
                    DB::raw(
                        'NULL as hora_inicio_comida'
                    ),
    
                    DB::raw(
                        'NULL as hora_fin_comida'
                    ),
    
                    DB::raw(
                        'NULL as hora_salida'
                    ),
    
                    DB::raw(
                        'NULL as segundos_presencia'
                    ),
    
                    DB::raw(
                        'NULL as segundos_comida'
                    ),
    
                    DB::raw(
                        'NULL as segundos_trabajados'
                    ),
    
                    DB::raw(
                        'NULL as metodo_entrada'
                    ),
    
                    DB::raw(
                        'NULL as estado_dia'
                    ),
    
                    DB::raw(
                        'NULL as estado_entrada'
                    ),
    
                    DB::raw(
                        'NULL as estado_salida'
                    ),
    
                    DB::raw(
                        'NULL as estado_comida'
                    ),
    
                    DB::raw(
                        'NULL as estado_jornada'
                    ),
    
                    /*
                     * Unidad laboral correspondiente
                     * a la fecha de la incidencia.
                     */
                    DB::raw("
                        (
                            SELECT
                                uca.unidad_servicio_id
                            FROM
                                user_control_accesos uca
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as unidad_servicio_id_snapshot
                    "),
    
                    /*
                     * Nombre de la unidad laboral.
                     */
                    DB::raw("
                        (
                            SELECT
                                us.nombre
                            FROM
                                user_control_accesos uca
                            LEFT JOIN
                                unidades_servicio us
                                ON us.id =
                                    uca.unidad_servicio_id
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as unidad_servicio_nombre_snapshot
                    "),
    
                    /*
                     * Empresa laboral correspondiente
                     * a la fecha de la incidencia.
                     */
                    DB::raw("
                        (
                            SELECT
                                uca.empresa_id
                            FROM
                                user_control_accesos uca
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as empresa_id_snapshot
                    "),
                    
                    /*
                     * Nombre de la empresa laboral.
                     */
                    DB::raw("
                        (
                            SELECT
                                e.nombre
                            FROM
                                user_control_accesos uca
                            LEFT JOIN
                                empresas e
                                ON e.id =
                                    uca.empresa_id
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as empresa_nombre_snapshot
                    "),

                    /*
                     * Tipo de trabajo correspondiente
                     * a esa fecha.
                     */
                    DB::raw("
                        (
                            SELECT
                                uca.tipo_trabajo
                            FROM
                                user_control_accesos uca
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as tipo_trabajo_snapshot
                    "),
    
                    /*
                     * Turno correspondiente
                     * a esa fecha.
                     */
                    DB::raw("
                        (
                            SELECT
                                t.nombre
                            FROM
                                user_control_accesos uca
                            LEFT JOIN
                                turnos t
                                ON t.id =
                                    uca.turno_id
                            WHERE
                                uca.user_id =
                                    asistencia_incidencias.user_id
                                AND uca.fecha_inicio <=
                                    asistencia_incidencias.fecha
                                AND (
                                    uca.fecha_fin IS NULL
                                    OR uca.fecha_fin >=
                                        asistencia_incidencias.fecha
                                )
                            ORDER BY
                                uca.fecha_inicio DESC,
                                uca.id DESC
                            LIMIT 1
                        )
                        as turno_nombre_snapshot
                    "),
    
                    DB::raw(
                        'NULL as turno_hora_inicio_snapshot'
                    ),
    
                    DB::raw(
                        'NULL as turno_hora_fin_snapshot'
                    ),
    
                    DB::raw(
                        'NULL as turno_horas_jornada_snapshot'
                    ),
    
                    DB::raw(
                        'NULL as turno_minutos_comida_snapshot'
                    ),
    
                    DB::raw(
                        'NULL as turno_comida_cuenta_jornada_snapshot'
                    ),
    
                    DB::raw(
                        "'incidencia' as registro_tipo"
                    ),
    
                    'asistencia_incidencias.tipo as incidencia_tipo',
    
                    'asistencia_incidencias.observaciones as incidencia_observaciones',
                ]);
    
        /*
         * Unir Asistencias + Incidencias
         * manteniendo exactamente la misma
         * estructura de columnas.
         */
        $unionQuery =
            $asistenciasQuery
                ->unionAll(
                    $incidenciasQuery
                );
    
        $query =
            DB::query()
                ->fromSub(
                    $unionQuery,
                    'registros'
                );
    
        /*
         * ==========================
         * FILTROS
         * ==========================
         */
        if (
            !empty(
                $data['fecha_desde']
            )
        ) {
            $query->whereDate(
                'registros.fecha',
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
                'registros.fecha',
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
                'registros.user_id',
                $data['user_id']
            );
        }
    
        if (
            !empty(
                $data[
                    'unidad_servicio_id'
                ]
            )
        ) {
            $query->where(
                'registros.unidad_servicio_id_snapshot',
                $data[
                    'unidad_servicio_id'
                ]
            );
        }
        
        if (
            !empty(
                $data['empresa_id']
            )
        ) {
            $query->where(
                'registros.empresa_id_snapshot',
                $data['empresa_id']
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
    
            $query->where(
                function ($q) use (
                    $search
                ) {
                    $q->where(
                        'registros.empleado_nombre',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registros.empleado_email',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registros.unidad_servicio_nombre_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registros.empresa_nombre_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registros.turno_nombre_snapshot',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registros.incidencia_tipo',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }
    
        /*
         * Los registros más recientes
         * aparecen primero.
         */
        $query
            ->orderByDesc(
                'registros.fecha'
            )
            ->orderByRaw("
                CASE
                    WHEN registros.registro_tipo =
                        'asistencia'
                    THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc(
                'registros.hora_entrada'
            )
            ->orderByDesc(
                'registros.id'
            );
    
        return $query;
    }

    private function validateExportFilters(
        Request $request
    ): array {
        return $request->validate([
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
    
            'unidad_servicio_id' => [
                'nullable',
                'integer',
            ],
            
            'empresa_id' => [
                'nullable',
                'integer',
            ],
            
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);
    }

    private function formatExportTime(
        $value
    ): string {
        if (!$value) {
            return '—';
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
    
    private function formatExportDuration(
        $value
    ): string {
        if (
            $value === null ||
            $value === ''
        ) {
            return '—';
        }
    
        $seconds =
            max(
                0,
                (int) $value
            );
    
        $hours =
            intdiv(
                $seconds,
                3600
            );
    
        $minutes =
            intdiv(
                $seconds % 3600,
                60
            );
    
        $remainingSeconds =
            $seconds % 60;
    
        return sprintf(
            '%02d:%02d:%02d',
            $hours,
            $minutes,
            $remainingSeconds
        );
    }
    
    private function mapExportRows(
        $registros
    ) {
        return $registros->map(
            function ($registro) {
                $incidenciaLabels = [
                    'vacaciones' =>
                        'Vacaciones',
                
                    'incapacidad' =>
                        'Incapacidad',
                
                    'festivo' =>
                        'Día festivo',
                ];
                
                /*
                 * Las incidencias no tienen horarios
                 * ni cálculos de jornada.
                 */
                if (
                    ($registro->registro_tipo ?? null)
                    === 'incidencia'
                ) {
                    $incidenciaLabel =
                        $incidenciaLabels[
                            $registro->incidencia_tipo
                        ]
                        ?? 'Incidencia';
                
                    return [
                        'fecha' =>
                            $registro->fecha
                                ? Carbon::parse(
                                    $registro->fecha
                                )->format(
                                    'd/m/Y'
                                )
                                : '—',
                
                        'empleado' =>
                            $registro
                                ->empleado_nombre
                            ?? '—',
                
                        'correo' =>
                            $registro
                                ->empleado_email
                            ?? '—',
                        
                        'empresa' =>
                            $registro
                                ->empresa_nombre_snapshot
                            ?? '—',
                        
                        'unidad' =>
                            $registro
                                ->unidad_servicio_nombre_snapshot
                            ?? '—',
                
                        'turno' =>
                            $registro
                                ->turno_nombre_snapshot
                            ?? '—',
                
                        'entrada' =>
                            '—',
                
                        'inicio_comida' =>
                            '—',
                
                        'fin_comida' =>
                            '—',
                
                        'salida' =>
                            '—',
                
                        'presencia' =>
                            '—',
                
                        'comida' =>
                            '—',
                
                        'trabajado' =>
                            '—',
                
                        'estado_dia' =>
                            '—',
                
                        'estado_entrada' =>
                            '—',
                
                        'estado_salida' =>
                            '—',
                
                        'estado_comida' =>
                            '—',
                
                        'estado_jornada' =>
                            $incidenciaLabel,
                
                        'metodo_entrada' =>
                            'Incidencia',
                
                        'incidencia_tipo' =>
                            $incidenciaLabel,
                
                        'incidencia_observaciones' =>
                            $registro
                                ->incidencia_observaciones
                            ?: '—',
                    ];
                }

                $estadoDia = [
                    'laborable' =>
                        'Laborable',
    
                    'fuera_dia_laborable' =>
                        'Fuera de día laborable',
                ];
    
                $estadoHorario = [
                    'dentro_horario' =>
                        'Dentro de horario',
    
                    'fuera_horario' =>
                        'Fuera de horario',
                ];
    
                $estadoComida = [
                    'menor' =>
                        'Menor',
    
                    'completa' =>
                        'Completa',
    
                    'excedida' =>
                        'Excedida',
                ];
    
                $estadoJornada = [
                    'incompleta' =>
                        'Incompleta',
    
                    'cumplida' =>
                        'Cumplida',
    
                    'excedida' =>
                        'Excedida',
                ];
    
                $metodoEntrada = [
                    'personal' =>
                        'Mi asistencia',
    
                    'kiosco' =>
                        'Kiosco',
    
                    'manual' =>
                        'Manual',
                ];
    
                $minutosComida =
                    (int) (
                        $registro
                            ->turno_minutos_comida_snapshot
                        ?? 0
                    );
    
                return [
                    'fecha' =>
                        $registro->fecha
                            ? Carbon::parse(
                                $registro->fecha
                            )->format(
                                'd/m/Y'
                            )
                            : '—',
    
                    'empleado' =>
                        $registro
                            ->empleado_nombre
                        ?? '—',
    
                    'correo' =>
                        $registro
                            ->empleado_email
                        ?? '—',
                    
                    'empresa' =>
                        $registro
                            ->empresa_nombre_snapshot
                        ?? '—',
                    
                    'unidad' =>
                        $registro
                            ->unidad_servicio_nombre_snapshot
                        ?? '—',
    
                    'turno' =>
                        $registro
                            ->turno_nombre_snapshot
                        ?? '—',
    
                    'entrada' =>
                        $this->formatExportTime(
                            $registro
                                ->hora_entrada
                        ),
    
                    'inicio_comida' =>
                        $this->formatExportTime(
                            $registro
                                ->hora_inicio_comida
                        ),
    
                    'fin_comida' =>
                        $this->formatExportTime(
                            $registro
                                ->hora_fin_comida
                        ),
    
                    'salida' =>
                        $this->formatExportTime(
                            $registro
                                ->hora_salida
                        ),
    
                    'presencia' =>
                        $this->formatExportDuration(
                            $registro
                                ->segundos_presencia
                        ),
    
                    'comida' =>
                        $minutosComida <= 0
                            ? 'No aplica'
                            : $this
                                ->formatExportDuration(
                                    $registro
                                        ->segundos_comida
                                ),
    
                    'trabajado' =>
                        $this->formatExportDuration(
                            $registro
                                ->segundos_trabajados
                        ),
    
                    'estado_dia' =>
                        $estadoDia[
                            $registro->estado_dia
                        ]
                        ?? 'Sin cálculo',
    
                    'estado_entrada' =>
                        $estadoHorario[
                            $registro->estado_entrada
                        ]
                        ?? 'Sin cálculo',
    
                    'estado_salida' =>
                        !$registro
                            ->hora_salida
                            ? 'Sin salida'
                            : (
                                $estadoHorario[
                                    $registro
                                        ->estado_salida
                                ]
                                ?? 'Sin cálculo'
                            ),
    
                    'estado_comida' =>
                        $minutosComida <= 0
                            ? 'No aplica'
                            : (
                                $estadoComida[
                                    $registro
                                        ->estado_comida
                                ]
                                ?? 'Sin cálculo'
                            ),
    
                    'estado_jornada' =>
                        $estadoJornada[
                            $registro
                                ->estado_jornada
                        ]
                        ?? 'Sin cálculo',
    
                    'metodo_entrada' =>
                        $metodoEntrada[
                            $registro
                                ->metodo_entrada
                        ]
                        ?? (
                            $registro
                                ->metodo_entrada
                            ?: '—'
                        ),
                    
                    'incidencia_tipo' =>
                        '—',
                    
                    'incidencia_observaciones' =>
                        '—',
                    ];
            }
        );
    }

    /**
     * Devuelve una fecha/hora sin convertir
     * nuevamente la zona horaria.
     *
     * Se utiliza para enviar al frontend el
     * valor operativo guardado en la asistencia
     * con formato estable.
     */
    private function formatDateTime(
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
                'Y-m-d H:i:s'
            );
        }

        return Carbon::parse(
            $value
        )->format(
            'Y-m-d H:i:s'
        );
    }

    private function buildHistorySnapshot(
        Asistencia $asistencia
    ): array {
        /*
         * Necesitamos también identificar al
         * empleado aunque posteriormente se
         * elimine la asistencia.
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
                    $asistencia
                        ->hora_inicio_comida
                ),
    
            'hora_fin_comida' =>
                $this->formatHistoryTime(
                    $asistencia
                        ->hora_fin_comida
                ),
    
            'hora_salida' =>
                $this->formatHistoryTime(
                    $asistencia->hora_salida
                ),
    
            'segundos_presencia' =>
                $asistencia
                    ->segundos_presencia,
    
            'segundos_comida' =>
                $asistencia
                    ->segundos_comida,
    
            'segundos_trabajados' =>
                $asistencia
                    ->segundos_trabajados,
    
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
             * Información histórica laboral.
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
    
    private function buildHistoryChanges(
        array $before,
        array $after
    ): array {
        /*
         * Solamente mostrar en "Cambios realizados"
         * información útil para el administrador.
         */
        $fields = [
            'fecha' => [
                'label' => 'Fecha',
                'type' => 'date',
            ],
    
            'hora_entrada' => [
                'label' => 'Hora de entrada',
                'type' => 'time',
            ],
    
            'hora_inicio_comida' => [
                'label' => 'Inicio de comida',
                'type' => 'time',
            ],
    
            'hora_fin_comida' => [
                'label' => 'Fin de comida',
                'type' => 'time',
            ],
    
            'hora_salida' => [
                'label' => 'Hora de salida',
                'type' => 'time',
            ],
    
            'segundos_presencia' => [
                'label' => 'Presencia',
                'type' => 'duration',
            ],
    
            'segundos_comida' => [
                'label' => 'Tiempo de comida',
                'type' => 'duration',
            ],
    
            'segundos_trabajados' => [
                'label' => 'Tiempo trabajado',
                'type' => 'duration',
            ],
    
            'estado_dia' => [
                'label' => 'Estado del día',
                'type' => 'text',
            ],
    
            'estado_entrada' => [
                'label' => 'Estado de entrada',
                'type' => 'text',
            ],
    
            'estado_salida' => [
                'label' => 'Estado de salida',
                'type' => 'text',
            ],
    
            'estado_comida' => [
                'label' => 'Estado de comida',
                'type' => 'text',
            ],
    
            'estado_jornada' => [
                'label' => 'Estado de jornada',
                'type' => 'text',
            ],
        ];
    
        $changes = [];
    
        foreach ($fields as $field => $meta) {
            $old =
                $before[$field] ?? null;
    
            $new =
                $after[$field] ?? null;
    
            if ($old === $new) {
                continue;
            }
    
            $changes[] = [
                'field' =>
                    $field,
    
                'label' =>
                    $meta['label'],
    
                'type' =>
                    $meta['type'],
    
                'old' =>
                    $old,
    
                'new' =>
                    $new,
            ];
        }
    
        return $changes;
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
