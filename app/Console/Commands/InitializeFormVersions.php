<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
use App\Support\Forms\FormRegistry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeFormVersions extends Command
{
    protected $signature =
        'forms:initialize-versions {--dry-run : Solo mostrar lo que se hará}';

    protected $description =
        'Crea la Versión 1 de los formularios actuales y relaciona los registros históricos.';

    public function handle(FormRegistry $formRegistry): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $registryItems = $formRegistry->all();

        if (count($registryItems) === 0) {
            $this->error(
                'No se encontraron formularios en FormRegistry.'
            );

            return self::FAILURE;
        }

        $plan = [];
        $errors = [];

        /*
        |--------------------------------------------------------------------------
        | PREVALIDACIÓN
        |--------------------------------------------------------------------------
        |
        | Primero revisamos TODO.
        | Si algo no coincide, no se modifica la base.
        |
        */
        foreach ($registryItems as $item) {
            $key = (string) ($item['key'] ?? '');
            $definitionClass =
                (string) ($item['definition_class'] ?? '');
            $layoutKey =
                (string) ($item['layout_key'] ?? '');
            $pdfView =
                (string) ($item['pdf_view'] ?? '');

            if ($key === '') {
                $errors[] =
                    'Existe un Definition sin key.';

                continue;
            }

            $form = Form::query()
                ->where(
                    'payload->_code_key',
                    $key
                )
                ->first();

            if (!$form) {
                $errors[] =
                    "No existe en forms el formulario: {$key}";

                continue;
            }

            if ($definitionClass === '') {
                $errors[] =
                    "No se encontró Definition para {$key}";

                continue;
            }

            if ($layoutKey === '') {
                $errors[] =
                    "No se encontró layout para {$key}";

                continue;
            }

            if ($pdfView === '') {
                $errors[] =
                    "No se encontró PDF para {$key}";

                continue;
            }

            if (!view()->exists($pdfView)) {
                $errors[] =
                    "No existe la vista PDF {$pdfView} para {$key}";

                continue;
            }

            /*
             * Tomamos como inicio de V1 la fecha más antigua entre:
             *
             * - creación del formulario
             * - primer registro existente
             *
             * De esta forma la V1 cubre todo el histórico actual.
             */
            $dates = [];

            if ($form->created_at) {
                $dates[] =
                    Carbon::parse(
                        $form->created_at
                    )->toDateString();
            }

            $firstSubmissionAt =
                FormSubmission::query()
                    ->where(
                        'form_id',
                        $form->id
                    )
                    ->min('created_at');

            if ($firstSubmissionAt) {
                $dates[] =
                    Carbon::parse(
                        $firstSubmissionAt
                    )->toDateString();
            }

            sort($dates);

            $fechaDesde =
                $dates[0]
                ?? now()->toDateString();

            $submissionCount =
                FormSubmission::query()
                    ->where(
                        'form_id',
                        $form->id
                    )
                    ->count();

            $plan[] = [
                'form' => $form,
                'key' => $key,
                'definition_class' =>
                    $definitionClass,
                'layout_key' =>
                    $layoutKey,
                'pdf_view' =>
                    $pdfView,
                'fecha_desde' =>
                    $fechaDesde,
                'submission_count' =>
                    $submissionCount,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Si existe cualquier problema, detener todo.
        |--------------------------------------------------------------------------
        */
        if (count($errors) > 0) {
            $this->error(
                'No se realizará ningún cambio.'
            );

            foreach ($errors as $error) {
                $this->line(
                    ' - ' . $error
                );
            }

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Mostrar plan
        |--------------------------------------------------------------------------
        */
        $this->table(
            [
                'Form ID',
                'Clave',
                'V',
                'Desde',
                'Registros',
            ],
            collect($plan)
                ->map(function ($row) {
                    return [
                        $row['form']->id,
                        $row['key'],
                        1,
                        $row['fecha_desde'],
                        $row['submission_count'],
                    ];
                })
                ->all()
        );

        $this->newLine();

        $this->info(
            'Formularios encontrados: ' .
            count($plan)
        );

        $this->info(
            'Registros existentes: ' .
            array_sum(
                array_column(
                    $plan,
                    'submission_count'
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | DRY RUN
        |--------------------------------------------------------------------------
        */
        if ($isDryRun) {
            $this->warn(
                'DRY RUN: no se realizó ningún cambio en la base de datos.'
            );

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | CREACIÓN REAL
        |--------------------------------------------------------------------------
        */
        DB::transaction(
            function () use ($plan) {
                foreach ($plan as $row) {
                    $form = $row['form'];

                    /*
                     * Snapshot exacto del payload actual guardado
                     * en la tabla forms.
                     *
                     * No utilizamos el Definition dinámicamente
                     * para los registros históricos.
                     */
                    $payloadSnapshot =
                        is_array($form->payload)
                            ? $form->payload
                            : [];

                    $version =
                        FormVersion::firstOrCreate(
                            [
                                'form_id' =>
                                    $form->id,

                                'version' =>
                                    1,
                            ],
                            [
                                'version_key' =>
                                    $row['key'] .
                                    '_v1',

                                'definition_class' =>
                                    $row[
                                        'definition_class'
                                    ],

                                'layout_key' =>
                                    $row[
                                        'layout_key'
                                    ],

                                'pdf_view' =>
                                    $row[
                                        'pdf_view'
                                    ],

                                'payload' =>
                                    $payloadSnapshot,

                                'fecha_desde' =>
                                    $row[
                                        'fecha_desde'
                                    ],

                                'fecha_hasta' =>
                                    null,

                                'descripcion' =>
                                    'Versión inicial del formulario.',
                            ]
                        );

                    /*
                     * MUY IMPORTANTE:
                     *
                     * Solo asignamos V1 a registros
                     * que todavía NO tienen versión.
                     *
                     * No modificamos:
                     * - answers
                     * - consecutive
                     * - user_id
                     * - created_at
                     * - updated_at
                     */
                    DB::table('form_submissions')
                        ->where(
                            'form_id',
                            $form->id
                        )
                        ->whereNull(
                            'form_version_id'
                        )
                        ->update([
                            'form_version_id' =>
                                $version->id,
                        ]);
                }
            }
        );

        $this->newLine();

        $this->info(
            'Versiones iniciales creadas correctamente.'
        );

        return self::SUCCESS;
    }
}