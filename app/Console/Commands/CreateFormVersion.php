<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\FormVersion;
use App\Support\Forms\FormVersionRegistry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateFormVersion extends Command
{
    protected $signature =
        'forms:create-version
        {version_key : Version registrada en FormVersionRegistry}
        {--from= : Fecha de inicio YYYY-MM-DD}
        {--description= : Descripción de la versión}
        {--dry-run : Mostrar lo que se hará sin modificar la base}';

    protected $description =
        'Crea una nueva versión de un formulario a partir de FormVersionRegistry.';

    public function handle(
        FormVersionRegistry $registry
    ): int {
        $versionKey = trim(
            (string)
            $this->argument(
                'version_key'
            )
        );

        $fromInput = trim(
            (string)
            $this->option(
                'from'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Fecha obligatoria
        |--------------------------------------------------------------------------
        */

        if ($fromInput === '') {
            $this->error(
                'Debes indicar --from=YYYY-MM-DD.'
            );

            return self::FAILURE;
        }

        try {
            $fechaDesde =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $fromInput
                )
                ->startOfDay();

            if (
                $fechaDesde->format(
                    'Y-m-d'
                ) !==
                $fromInput
            ) {
                throw new RuntimeException(
                    'Fecha inválida.'
                );
            }
        } catch (\Throwable) {
            $this->error(
                'La fecha --from debe tener formato YYYY-MM-DD.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Paquete registrado en código
        |--------------------------------------------------------------------------
        */

        try {
            $package =
                $registry->find(
                    $versionKey
                );
        } catch (\Throwable $e) {
            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }

        if (!$package) {
            $this->error(
                "La versión {$versionKey} no está registrada en FormVersionRegistry."
            );

            return self::FAILURE;
        }

        $formKey =
            (string)
            $package['form_key'];

        $version =
            (int)
            $package['version'];

        /*
         * V1 se administra únicamente mediante
         * forms:initialize-versions.
         */
        if ($version < 2) {
            $this->error(
                'Este comando solamente puede crear V2 o superiores.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Formulario
        |--------------------------------------------------------------------------
        */

        $form =
            Form::query()
                ->where(
                    'payload->_code_key',
                    $formKey
                )
                ->first();

        if (!$form) {
            $this->error(
                "No existe el formulario {$formKey} en la tabla forms."
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | No permitir duplicados
        |--------------------------------------------------------------------------
        */

        $existingByKey =
            FormVersion::query()
                ->where(
                    'version_key',
                    $versionKey
                )
                ->exists();

        if ($existingByKey) {
            $this->error(
                "La versión {$versionKey} ya existe en la base de datos."
            );

            return self::FAILURE;
        }

        $existingByNumber =
            FormVersion::query()
                ->where(
                    'form_id',
                    $form->id
                )
                ->where(
                    'version',
                    $version
                )
                ->exists();

        if ($existingByNumber) {
            $this->error(
                "El formulario ya tiene registrada la V{$version}."
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Debe existir la versión inmediatamente anterior
        |--------------------------------------------------------------------------
        */

        $previousVersionNumber =
            $version - 1;

        $previous =
            FormVersion::query()
                ->where(
                    'form_id',
                    $form->id
                )
                ->where(
                    'version',
                    $previousVersionNumber
                )
                ->first();

        if (!$previous) {
            $this->error(
                "No existe V{$previousVersionNumber}. No se puede crear V{$version}."
            );

            return self::FAILURE;
        }

        /*
         * No permitimos saltos.
         *
         * Si la última versión existente es V1,
         * únicamente puede crearse V2.
         */
        $maxVersion =
            (int)
            FormVersion::query()
                ->where(
                    'form_id',
                    $form->id
                )
                ->max(
                    'version'
                );

        if (
            $maxVersion !==
            $previousVersionNumber
        ) {
            $this->error(
                "La última versión existente es V{$maxVersion}; no corresponde crear V{$version}."
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Rango de fechas
        |--------------------------------------------------------------------------
        */

        $previousFechaDesde =
            Carbon::parse(
                $previous->fecha_desde
            )
            ->startOfDay();

        if (
            $fechaDesde->lessThanOrEqualTo(
                $previousFechaDesde
            )
        ) {
            $this->error(
                'La nueva versión debe comenzar después de la fecha de inicio de la versión anterior.'
            );

            return self::FAILURE;
        }

        /*
         * La versión anterior termina exactamente
         * un día antes de iniciar la nueva.
         *
         * Así no existen huecos entre versiones.
         */
        $previousFechaHasta =
            $fechaDesde
                ->copy()
                ->subDay();

        /*
         * Si la versión anterior ya tenía fecha_hasta,
         * debe coincidir exactamente con el nuevo rango.
         */
        if ($previous->fecha_hasta) {
            $existingEnd =
                Carbon::parse(
                    $previous->fecha_hasta
                )
                ->startOfDay();

            if (
                !$existingEnd->equalTo(
                    $previousFechaHasta
                )
            ) {
                $this->error(
                    'La fecha final de la versión anterior no coincide con la nueva fecha de inicio.'
                );

                return self::FAILURE;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Comprobar que no exista otra versión superpuesta
        |--------------------------------------------------------------------------
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
                    $fechaDesde->toDateString()
                )
                ->where(
                    function ($query) use (
                        $fechaDesde
                    ) {
                        $query
                            ->whereNull(
                                'fecha_hasta'
                            )
                            ->orWhereDate(
                                'fecha_hasta',
                                '>=',
                                $fechaDesde
                                    ->toDateString()
                            );
                    }
                )
                ->exists();

        if ($overlap) {
            $this->error(
                'Existe otra versión cuyo rango de fechas se superpone.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Validaciones del paquete
        |--------------------------------------------------------------------------
        */

        $pdfView =
            (string)
            $package['pdf_view'];

        if (
            !view()->exists(
                $pdfView
            )
        ) {
            $this->error(
                "No existe la vista PDF {$pdfView}."
            );

            return self::FAILURE;
        }

        $payload =
            $package['payload']
            ?? [];

        if (!is_array($payload)) {
            $this->error(
                'El payload de la nueva versión no es válido.'
            );

            return self::FAILURE;
        }

        /*
         * Las Definitions mantienen el mismo key
         * lógico del formulario.
         *
         * El snapshot debe conservarlo igual que V1.
         */
        $payload['_code_key'] =
            $formKey;

        $description =
            trim(
                (string)
                $this->option(
                    'description'
                )
            );

        if ($description === '') {
            $description =
                "Versión {$version} del formulario.";
        }

        /*
        |--------------------------------------------------------------------------
        | Mostrar plan
        |--------------------------------------------------------------------------
        */

        $this->table(
            [
                'Dato',
                'Valor',
            ],
            [
                [
                    'Formulario',
                    $formKey,
                ],
                [
                    'Form ID',
                    $form->id,
                ],
                [
                    'Nueva versión',
                    'V' . $version,
                ],
                [
                    'Version Key',
                    $versionKey,
                ],
                [
                    'Definition',
                    $package[
                        'definition_class'
                    ],
                ],
                [
                    'Layout',
                    $package[
                        'layout_key'
                    ],
                ],
                [
                    'PDF',
                    $pdfView,
                ],
                [
                    'Inicio nueva versión',
                    $fechaDesde
                        ->toDateString(),
                ],
                [
                    'Fin V' .
                    $previousVersionNumber,
                    $previousFechaHasta
                        ->toDateString(),
                ],
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Dry Run
        |--------------------------------------------------------------------------
        */

        if (
            (bool)
            $this->option(
                'dry-run'
            )
        ) {
            $this->newLine();

            $this->warn(
                'DRY RUN: no se realizó ningún cambio en la base de datos.'
            );

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Crear versión
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $form,
                $package,
                $payload,
                $version,
                $versionKey,
                $fechaDesde,
                $previous,
                $previousFechaHasta,
                $description
            ) {
                /*
                 * Bloqueamos la versión anterior para impedir
                 * dos altas simultáneas.
                 */
                $lockedPrevious =
                    FormVersion::query()
                        ->where(
                            'id',
                            $previous->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * Segunda protección contra duplicados
                 * dentro de la transacción.
                 */
                $alreadyExists =
                    FormVersion::query()
                        ->where(
                            'form_id',
                            $form->id
                        )
                        ->where(
                            'version',
                            $version
                        )
                        ->exists();

                if ($alreadyExists) {
                    throw new RuntimeException(
                        "La V{$version} ya fue creada."
                    );
                }

                /*
                 * Cerramos únicamente el rango temporal
                 * de la versión anterior.
                 *
                 * NO modificamos:
                 * - payload
                 * - definition_class
                 * - layout_key
                 * - pdf_view
                 * - submissions
                 */
                $lockedPrevious
                    ->fecha_hasta =
                    $previousFechaHasta
                        ->toDateString();

                $lockedPrevious->save();

                /*
                 * Snapshot independiente de la nueva versión.
                 */
                FormVersion::create([
                    'form_id' =>
                        $form->id,

                    'version' =>
                        $version,

                    'version_key' =>
                        $versionKey,

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

                    'payload' =>
                        $payload,

                    'fecha_desde' =>
                        $fechaDesde
                            ->toDateString(),

                    'fecha_hasta' =>
                        null,

                    'descripcion' =>
                        $description,
                ]);
            }
        );

        $this->newLine();

        $this->info(
            "V{$version} creada correctamente."
        );

        $this->line(
            "V{$previousVersionNumber}: hasta " .
            $previousFechaHasta
                ->toDateString()
        );

        $this->line(
            "V{$version}: desde " .
            $fechaDesde
                ->toDateString()
        );

        return self::SUCCESS;
    }
}