<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormVersion;
use App\Support\Forms\FormVersionRegistry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFormVersionsController extends Controller
{
    public function index(
        Request $request,
        Form $form,
        FormVersionRegistry $registry
    ) {
        $formKey = trim(
            (string) data_get(
                $form->payload,
                '_code_key',
                ''
            )
        );

        if ($formKey === '') {
            return response()->json([
                'message' =>
                    'Este formulario no está vinculado al catálogo de código.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha actual de negocio
        |--------------------------------------------------------------------------
        */

        $today =
            Carbon::now(
                'America/Mexico_City'
            )->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Paquetes disponibles en código
        |--------------------------------------------------------------------------
        */

        $codePackages =
            collect(
                $registry->forForm(
                    $formKey
                )
            )
            ->keyBy(
                'version_key'
            );

        /*
        |--------------------------------------------------------------------------
        | Versiones existentes en BD
        |--------------------------------------------------------------------------
        */

        $versions =
            FormVersion::query()
                ->withCount(
                    'submissions'
                )
                ->where(
                    'form_id',
                    $form->id
                )
                ->orderBy(
                    'version'
                )
                ->get()
                ->map(
                    function (
                        FormVersion $version
                    ) use (
                        $today,
                        $codePackages
                    ) {
                        $fechaDesde =
                            Carbon::parse(
                                $version->fecha_desde
                            )->toDateString();

                        $fechaHasta =
                            $version->fecha_hasta
                                ? Carbon::parse(
                                    $version->fecha_hasta
                                )->toDateString()
                                : null;

                        /*
                         * Estado temporal.
                         */
                        if (
                            $today <
                            $fechaDesde
                        ) {
                            $status =
                                'PROGRAMADA';
                        } elseif (
                            $fechaHasta !== null &&
                            $today >
                            $fechaHasta
                        ) {
                            $status =
                                'HISTORICA';
                        } else {
                            $status =
                                'VIGENTE';
                        }

                        $codePackage =
                            $codePackages->get(
                                $version->version_key
                            );

                        /*
                         * También comprobamos que la versión
                         * almacenada coincida con su paquete
                         * actualmente disponible en código.
                         */
                        $codeMatches =
                            $codePackage &&
                            (string) $codePackage[
                                'definition_class'
                            ] ===
                                (string) $version
                                    ->definition_class &&
                            (string) $codePackage[
                                'layout_key'
                            ] ===
                                (string) $version
                                    ->layout_key &&
                            (string) $codePackage[
                                'pdf_view'
                            ] ===
                                (string) $version
                                    ->pdf_view;

                        return [
                            'id' =>
                                (int) $version->id,

                            'form_id' =>
                                (int) $version->form_id,

                            'version' =>
                                (int) $version->version,

                            'version_key' =>
                                $version->version_key,

                            'definition_class' =>
                                $version->definition_class,

                            'layout_key' =>
                                $version->layout_key,

                            'pdf_view' =>
                                $version->pdf_view,

                            'fecha_desde' =>
                                $fechaDesde,

                            'fecha_hasta' =>
                                $fechaHasta,

                            'descripcion' =>
                                $version->descripcion,

                            'submissions_count' =>
                                (int) $version
                                    ->submissions_count,

                            'status' =>
                                $status,

                            'registered_in_code' =>
                                $codePackage !== null,

                            'code_matches' =>
                                (bool) $codeMatches,

                            'created_at' =>
                                $version->created_at,

                            'updated_at' =>
                                $version->updated_at,
                        ];
                    }
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Versiones declaradas en código pero todavía no creadas en BD
        |--------------------------------------------------------------------------
        */

        $dbVersionKeys =
            $versions
                ->pluck(
                    'version_key'
                )
                ->all();

        $pendingVersions =
            $codePackages
                ->filter(
                    fn (array $package) =>
                        !in_array(
                            $package[
                                'version_key'
                            ],
                            $dbVersionKeys,
                            true
                        )
                )
                ->map(
                    function (
                        array $package
                    ) {
                        return [
                            'version' =>
                                (int) $package[
                                    'version'
                                ],

                            'version_key' =>
                                $package[
                                    'version_key'
                                ],

                            'definition_class' =>
                                $package[
                                    'definition_class'
                                ],

                            'layout_key' =>
                                $package[
                                    'layout_key'
                                ],

                            'pdf_view' =>
                                $package[
                                    'pdf_view'
                                ],

                            'status' =>
                                'PENDIENTE_BD',
                        ];
                    }
                )
                ->values();

        return response()->json([
            'form' => [
                'id' =>
                    (int) $form->id,

                'title' =>
                    $form->title,

                'form_key' =>
                    $formKey,
            ],

            'today' =>
                $today,

            'versions' =>
                $versions,

            'pending_versions' =>
                $pendingVersions,
        ]);
    }

    public function store(
        Request $request,
        Form $form,
        FormVersionRegistry $registry
    ) {
        /*
        |--------------------------------------------------------------------------
        | Solo Administrador
        |--------------------------------------------------------------------------
        */
    
        $user = $request->user();
    
        if (
            !$user ||
            !method_exists($user, 'hasRole') ||
            !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para crear versiones de formularios.',
            ], 403);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Validación de entrada
        |--------------------------------------------------------------------------
        */
    
        $data = $request->validate([
            'version_key' => [
                'required',
                'string',
                'max:255',
            ],
    
            'fecha_desde' => [
                'required',
                'date_format:Y-m-d',
            ],
    
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    
        $versionKey =
            trim(
                (string) $data[
                    'version_key'
                ]
            );
    
        $description =
            isset($data['descripcion'])
                ? trim(
                    (string) $data[
                        'descripcion'
                    ]
                )
                : null;
    
        if ($description === '') {
            $description = null;
        }
    
        /*
        |--------------------------------------------------------------------------
        | Formulario base
        |--------------------------------------------------------------------------
        */
    
        $formKey =
            trim(
                (string) data_get(
                    $form->payload,
                    '_code_key',
                    ''
                )
            );
    
        if ($formKey === '') {
            return response()->json([
                'message' =>
                    'Este formulario no está vinculado al catálogo de código.',
            ], 422);
        }
    
        /*
        |--------------------------------------------------------------------------
        | La versión DEBE existir en código
        |--------------------------------------------------------------------------
        */
    
        $package =
            $registry->find(
                $versionKey
            );
    
        if (!$package) {
            return response()->json([
                'message' =>
                    'La versión indicada no está registrada en código.',
            ], 422);
        }
    
        if (
            (string) (
                $package[
                    'form_key'
                ] ?? ''
            ) !==
            $formKey
        ) {
            return response()->json([
                'message' =>
                    'La versión indicada pertenece a otro formulario.',
            ], 422);
        }
    
        $newVersion =
            (int) (
                $package[
                    'version'
                ] ?? 0
            );
    
        /*
         * V1 nunca se crea desde esta pantalla.
         * V1 pertenece a la inicialización histórica.
         */
        if ($newVersion < 2) {
            return response()->json([
                'message' =>
                    'La versión inicial V1 no puede crearse desde el panel.',
            ], 422);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Validación del paquete
        |--------------------------------------------------------------------------
        */
    
        $definitionClass =
            trim(
                (string) (
                    $package[
                        'definition_class'
                    ] ?? ''
                )
            );
    
        $layoutKey =
            trim(
                (string) (
                    $package[
                        'layout_key'
                    ] ?? ''
                )
            );
    
        $pdfView =
            trim(
                (string) (
                    $package[
                        'pdf_view'
                    ] ?? ''
                )
            );
    
        $payload =
            is_array(
                $package[
                    'payload'
                ] ?? null
            )
                ? $package[
                    'payload'
                ]
                : [];
    
        if (
            $definitionClass === '' ||
            $layoutKey === '' ||
            $pdfView === '' ||
            empty($payload)
        ) {
            return response()->json([
                'message' =>
                    'El paquete de esta versión está incompleto.',
            ], 422);
        }
    
        if (
            !class_exists(
                $definitionClass
            )
        ) {
            return response()->json([
                'message' =>
                    'No existe la clase Definition registrada para esta versión.',
            ], 422);
        }
    
        if (
            !view()->exists(
                $pdfView
            )
        ) {
            return response()->json([
                'message' =>
                    "No existe la vista PDF registrada: {$pdfView}",
            ], 422);
        }
    
        /*
         * Garantizamos la clave base dentro
         * del snapshot congelado.
         */
        $payload[
            '_code_key'
        ] = $formKey;
    
        $newStart =
            Carbon::createFromFormat(
                'Y-m-d',
                $data[
                    'fecha_desde'
                ],
                config(
                    'app.timezone',
                    'America/Mexico_City'
                )
            )->startOfDay();
    
        /*
        |--------------------------------------------------------------------------
        | Creación transaccional
        |--------------------------------------------------------------------------
        */
    
        $createdVersion =
            DB::transaction(
                function () use (
                    $form,
                    $versionKey,
                    $newVersion,
                    $newStart,
                    $description,
                    $definitionClass,
                    $layoutKey,
                    $pdfView,
                    $payload
                ) {
                    /*
                     * Protección contra doble clic,
                     * dos pestañas o solicitudes simultáneas.
                     */
                    $duplicate =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->where(
                                function (
                                    $query
                                ) use (
                                    $versionKey,
                                    $newVersion
                                ) {
                                    $query
                                        ->where(
                                            'version_key',
                                            $versionKey
                                        )
                                        ->orWhere(
                                            'version',
                                            $newVersion
                                        );
                                }
                            )
                            ->lockForUpdate()
                            ->first();
    
                    if ($duplicate) {
                        abort(
                            422,
                            'Esta versión ya existe en la base de datos.'
                        );
                    }
    
                    /*
                     * Bloqueamos la versión más reciente.
                     */
                    $previous =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->orderByDesc(
                                'version'
                            )
                            ->lockForUpdate()
                            ->first();
    
                    if (!$previous) {
                        abort(
                            422,
                            'El formulario no tiene una versión inicial registrada.'
                        );
                    }
    
                    /*
                     * No permitimos saltos:
                     *
                     * V1 -> V2
                     * V2 -> V3
                     *
                     * pero nunca V1 -> V3.
                     */
                    $expectedVersion =
                        (int) $previous->version +
                        1;
    
                    if (
                        $newVersion !==
                        $expectedVersion
                    ) {
                        abort(
                            422,
                            "La siguiente versión debe ser V{$expectedVersion}."
                        );
                    }
    
                    $previousStart =
                        Carbon::parse(
                            $previous
                                ->fecha_desde
                        )
                            ->startOfDay();
    
                    /*
                     * La nueva versión debe comenzar
                     * después del inicio de la anterior.
                     */
                    if (
                        !$newStart->gt(
                            $previousStart
                        )
                    ) {
                        abort(
                            422,
                            'La fecha de inicio de la nueva versión debe ser posterior al inicio de la versión anterior.'
                        );
                    }
    
                    $expectedPreviousEnd =
                        $newStart
                            ->copy()
                            ->subDay()
                            ->toDateString();
    
                    /*
                     * Si por algún motivo la versión anterior
                     * ya tiene fecha final, debe coincidir
                     * exactamente con el día anterior.
                     */
                    if (
                        $previous
                            ->fecha_hasta
                    ) {
                        $currentPreviousEnd =
                            Carbon::parse(
                                $previous
                                    ->fecha_hasta
                            )->toDateString();
    
                        if (
                            $currentPreviousEnd !==
                            $expectedPreviousEnd
                        ) {
                            abort(
                                422,
                                'La fecha final de la versión anterior no coincide con el rango requerido.'
                            );
                        }
                    }
    
                    /*
                     * Revisamos que ninguna OTRA versión
                     * cubra la fecha donde iniciará
                     * la nueva versión.
                     *
                     * Excluimos la anterior porque
                     * precisamente será cerrada un día antes.
                     */
                    $overlap =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->where(
                                'id',
                                '!=',
                                $previous->id
                            )
                            ->whereDate(
                                'fecha_desde',
                                '<=',
                                $newStart
                                    ->toDateString()
                            )
                            ->where(
                                function (
                                    $query
                                ) use (
                                    $newStart
                                ) {
                                    $date =
                                        $newStart
                                            ->toDateString();
    
                                    $query
                                        ->whereNull(
                                            'fecha_hasta'
                                        )
                                        ->orWhereDate(
                                            'fecha_hasta',
                                            '>=',
                                            $date
                                        );
                                }
                            )
                            ->exists();
    
                    if ($overlap) {
                        abort(
                            422,
                            'La fecha indicada se traslapa con otra versión.'
                        );
                    }
    
                    /*
                     * Cerramos exactamente la versión anterior.
                     *
                     * NO tocamos:
                     * - payload
                     * - Definition
                     * - layout
                     * - PDF
                     * - submissions históricos.
                     */
                    $previous
                        ->fecha_hasta =
                        $expectedPreviousEnd;
    
                    $previous->save();
    
                    /*
                     * Creamos el snapshot inmutable
                     * de la nueva versión.
                     */
                    return FormVersion::create([
                        'form_id' =>
                            $form->id,
    
                        'version' =>
                            $newVersion,
    
                        'version_key' =>
                            $versionKey,
    
                        'definition_class' =>
                            $definitionClass,
    
                        'layout_key' =>
                            $layoutKey,
    
                        'pdf_view' =>
                            $pdfView,
    
                        'payload' =>
                            $payload,
    
                        'fecha_desde' =>
                            $newStart
                                ->toDateString(),
    
                        'fecha_hasta' =>
                            null,
    
                        'descripcion' =>
                            $description,
                    ]);
                }
            );
    
        return response()->json([
            'ok' => true,
    
            'message' =>
                "Versión V{$createdVersion->version} creada correctamente.",
    
            'version' => [
                'id' =>
                    (int) $createdVersion->id,
    
                'form_id' =>
                    (int) $createdVersion->form_id,
    
                'version' =>
                    (int) $createdVersion->version,
    
                'version_key' =>
                    $createdVersion->version_key,
    
                'definition_class' =>
                    $createdVersion->definition_class,
    
                'layout_key' =>
                    $createdVersion->layout_key,
    
                'pdf_view' =>
                    $createdVersion->pdf_view,
    
                'fecha_desde' =>
                    $createdVersion
                        ->fecha_desde
                        ? Carbon::parse(
                            $createdVersion
                                ->fecha_desde
                        )->toDateString()
                        : null,
    
                'fecha_hasta' =>
                    $createdVersion
                        ->fecha_hasta
                        ? Carbon::parse(
                            $createdVersion
                                ->fecha_hasta
                        )->toDateString()
                        : null,
    
                'descripcion' =>
                    $createdVersion->descripcion,
            ],
        ], 201);
    }

    public function update(
        Request $request,
        Form $form,
        FormVersion $version
    ) {
        /*
        |--------------------------------------------------------------------------
        | Solo Administrador
        |--------------------------------------------------------------------------
        */
    
        $user = $request->user();
    
        if (
            !$user ||
            !method_exists($user, 'hasRole') ||
            !$user->hasRole('Administrador')
        ) {
            return response()->json([
                'message' =>
                    'No tienes permiso para editar versiones de formularios.',
            ], 403);
        }
    
        /*
        |--------------------------------------------------------------------------
        | La versión debe pertenecer al formulario
        |--------------------------------------------------------------------------
        */
    
        if (
            (int) $version->form_id !==
            (int) $form->id
        ) {
            return response()->json([
                'message' =>
                    'La versión indicada no pertenece a este formulario.',
            ], 404);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Datos permitidos
        |--------------------------------------------------------------------------
        |
        | Desde el panel SOLO se puede modificar:
        |
        | - fecha_desde
        | - descripcion
        |
        | Nunca:
        | - payload
        | - definition_class
        | - layout_key
        | - pdf_view
        | - version
        | - version_key
        |
        */
    
        $data = $request->validate([
            'fecha_desde' => [
                'required',
                'date_format:Y-m-d',
            ],
    
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    
        $description =
            isset($data['descripcion'])
                ? trim(
                    (string) $data['descripcion']
                )
                : null;
    
        if ($description === '') {
            $description = null;
        }
    
        $timezone =
            config(
                'app.timezone',
                'America/Mexico_City'
            );
    
        $today =
            Carbon::now($timezone)
                ->startOfDay();
    
        $newStart =
            Carbon::createFromFormat(
                'Y-m-d',
                $data['fecha_desde'],
                $timezone
            )->startOfDay();
    
        /*
        |--------------------------------------------------------------------------
        | Actualización protegida
        |--------------------------------------------------------------------------
        */
    
        $updatedVersion =
            DB::transaction(
                function () use (
                    $form,
                    $version,
                    $description,
                    $today,
                    $newStart
                ) {
                    /*
                     * Volvemos a leer y bloqueamos la versión
                     * dentro de la transacción.
                     */
                    $lockedVersion =
                        FormVersion::query()
                            ->whereKey(
                                $version->id
                            )
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();
    
                    /*
                     * V1 no se puede modificar desde aquí.
                     */
                    if (
                        (int) $lockedVersion->version <= 1
                    ) {
                        abort(
                            422,
                            'La versión inicial V1 no puede modificar su fecha desde el panel.'
                        );
                    }
    
                    /*
                     * Solo una versión que todavía está
                     * programada puede cambiar su fecha.
                     */
                    $currentStart =
                        Carbon::parse(
                            $lockedVersion
                                ->fecha_desde
                        )
                            ->setTimezone(
                                config(
                                    'app.timezone',
                                    'America/Mexico_City'
                                )
                            )
                            ->startOfDay();
    
                    if (
                        !$currentStart->gt(
                            $today
                        )
                    ) {
                        abort(
                            422,
                            'Solo se puede modificar una versión que todavía está programada.'
                        );
                    }
    
                    /*
                     * Si ya fue utilizada por cualquier
                     * registro, su fecha queda congelada.
                     */
                    $submissionCount =
                        $lockedVersion
                            ->submissions()
                            ->count();
    
                    if (
                        $submissionCount > 0
                    ) {
                        abort(
                            422,
                            "Esta versión ya tiene {$submissionCount} registro(s) y su fecha ya no puede modificarse."
                        );
                    }
    
                    /*
                     * Primera implementación:
                     * únicamente permitimos modificar
                     * la última versión de la cadena.
                     *
                     * Así no tenemos que desplazar varias
                     * versiones posteriores.
                     */
                    $hasLaterVersion =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->where(
                                'version',
                                '>',
                                $lockedVersion
                                    ->version
                            )
                            ->exists();
    
                    if ($hasLaterVersion) {
                        abort(
                            422,
                            'Solo se puede modificar la última versión programada del formulario.'
                        );
                    }
    
                    /*
                     * La nueva fecha también debe seguir
                     * estando en el futuro.
                     */
                    if (
                        !$newStart->gt(
                            $today
                        )
                    ) {
                        abort(
                            422,
                            'La nueva fecha de inicio debe ser posterior a la fecha actual.'
                        );
                    }
    
                    /*
                     * Obtenemos y bloqueamos la versión
                     * inmediatamente anterior.
                     */
                    $previous =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->where(
                                'version',
                                (int) $lockedVersion
                                        ->version
                                    - 1
                            )
                            ->lockForUpdate()
                            ->first();
    
                    if (!$previous) {
                        abort(
                            422,
                            'No se encontró la versión anterior.'
                        );
                    }
    
                    $previousStart =
                        Carbon::parse(
                            $previous
                                ->fecha_desde
                        )->startOfDay();
    
                    /*
                     * No permitimos invertir el orden
                     * de las versiones.
                     */
                    if (
                        !$newStart->gt(
                            $previousStart
                        )
                    ) {
                        abort(
                            422,
                            'La fecha de inicio debe ser posterior al inicio de la versión anterior.'
                        );
                    }
    
                    /*
                     * La versión anterior siempre termina
                     * exactamente un día antes.
                     */
                    $newPreviousEnd =
                        $newStart
                            ->copy()
                            ->subDay()
                            ->toDateString();
    
                    /*
                     * Protección adicional contra algún
                     * rango inconsistente previo.
                     */
                    $overlap =
                        FormVersion::query()
                            ->where(
                                'form_id',
                                $form->id
                            )
                            ->whereNotIn(
                                'id',
                                [
                                    $previous->id,
                                    $lockedVersion->id,
                                ]
                            )
                            ->whereDate(
                                'fecha_desde',
                                '<=',
                                $newStart
                                    ->toDateString()
                            )
                            ->where(
                                function (
                                    $query
                                ) use (
                                    $newStart
                                ) {
                                    $date =
                                        $newStart
                                            ->toDateString();
    
                                    $query
                                        ->whereNull(
                                            'fecha_hasta'
                                        )
                                        ->orWhereDate(
                                            'fecha_hasta',
                                            '>=',
                                            $date
                                        );
                                }
                            )
                            ->exists();
    
                    if ($overlap) {
                        abort(
                            422,
                            'La nueva fecha se traslapa con otra versión.'
                        );
                    }
    
                    /*
                     * Ajustamos solamente las fechas.
                     *
                     * La versión anterior conserva:
                     * payload, Definition, JSX/PDF y registros.
                     */
                    $previous->fecha_hasta =
                        $newPreviousEnd;
    
                    $previous->save();
    
                    /*
                     * La versión programada conserva toda
                     * su identidad técnica.
                     */
                    $lockedVersion->fecha_desde =
                        $newStart
                            ->toDateString();
    
                    $lockedVersion->descripcion =
                        $description;
    
                    $lockedVersion->save();
    
                    return
                        $lockedVersion->fresh();
                }
            );
    
        return response()->json([
            'ok' => true,
    
            'message' =>
                "Versión V{$updatedVersion->version} actualizada correctamente.",
    
            'version' => [
                'id' =>
                    (int) $updatedVersion->id,
    
                'form_id' =>
                    (int) $updatedVersion->form_id,
    
                'version' =>
                    (int) $updatedVersion->version,
    
                'version_key' =>
                    $updatedVersion->version_key,
    
                'fecha_desde' =>
                    Carbon::parse(
                        $updatedVersion
                            ->fecha_desde
                    )->toDateString(),
    
                'fecha_hasta' =>
                    $updatedVersion
                        ->fecha_hasta
                        ? Carbon::parse(
                            $updatedVersion
                                ->fecha_hasta
                        )->toDateString()
                        : null,
    
                'descripcion' =>
                    $updatedVersion->descripcion,
            ],
        ]);
    }
}