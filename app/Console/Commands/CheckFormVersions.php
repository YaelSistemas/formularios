<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\FormVersion;
use App\Support\Forms\FormVersionRegistry;
use Illuminate\Console\Command;

class CheckFormVersions extends Command
{
    protected $signature =
        'forms:check-versions';

    protected $description =
        'Comprueba el registro de versiones contra form_versions sin modificar la base de datos.';

    public function handle(
        FormVersionRegistry $registry
    ): int {
        $packages =
            $registry->all();

        if (count($packages) === 0) {
            $this->error(
                'FormVersionRegistry no contiene versiones.'
            );

            return self::FAILURE;
        }

        $rows = [];
        $errors = [];

        foreach ($packages as $package) {
            $formKey =
                (string) (
                    $package['form_key']
                    ?? ''
                );

            $version =
                (int) (
                    $package['version']
                    ?? 0
                );

            $versionKey =
                (string) (
                    $package['version_key']
                    ?? ''
                );

            $definitionClass =
                (string) (
                    $package['definition_class']
                    ?? ''
                );

            $layoutKey =
                (string) (
                    $package['layout_key']
                    ?? ''
                );

            $pdfView =
                (string) (
                    $package['pdf_view']
                    ?? ''
                );

            /*
            |--------------------------------------------------------------------------
            | Formulario base
            |--------------------------------------------------------------------------
            */

            $form = Form::query()
                ->where(
                    'payload->_code_key',
                    $formKey
                )
                ->first();

            if (!$form) {
                $errors[] =
                    "No existe en forms: {$formKey}";

                $rows[] = [
                    $formKey,
                    $version,
                    $versionKey,
                    'ERROR',
                    'Formulario no encontrado',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Definition
            |--------------------------------------------------------------------------
            */

            if (
                $definitionClass === '' ||
                !class_exists($definitionClass)
            ) {
                $errors[] =
                    "Definition inexistente: {$versionKey}";

                $rows[] = [
                    $formKey,
                    $version,
                    $versionKey,
                    'ERROR',
                    'Definition no encontrada',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            if (
                $pdfView === '' ||
                !view()->exists($pdfView)
            ) {
                $errors[] =
                    "PDF inexistente: {$versionKey}";

                $rows[] = [
                    $formKey,
                    $version,
                    $versionKey,
                    'ERROR',
                    'PDF no encontrado',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Versión guardada en BD
            |--------------------------------------------------------------------------
            */

            $dbVersion =
                FormVersion::query()
                    ->where(
                        'version_key',
                        $versionKey
                    )
                    ->first();

            /*
             * Esto NO es un error.
             *
             * Cuando registremos una futura V2 en código,
             * primero aparecerá como PENDIENTE hasta que
             * decidamos su fecha de activación.
             */
            if (!$dbVersion) {
                $rows[] = [
                    $formKey,
                    $version,
                    $versionKey,
                    'PENDIENTE',
                    'Existe en código, todavía no en BD',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Validaciones contra BD
            |--------------------------------------------------------------------------
            */

            $problems = [];

            if (
                (int) $dbVersion->form_id !==
                (int) $form->id
            ) {
                $problems[] =
                    'form_id diferente';
            }

            if (
                (int) $dbVersion->version !==
                $version
            ) {
                $problems[] =
                    'version diferente';
            }

            if (
                (string) $dbVersion->definition_class !==
                $definitionClass
            ) {
                $problems[] =
                    'definition_class diferente';
            }

            if (
                (string) $dbVersion->layout_key !==
                $layoutKey
            ) {
                $problems[] =
                    'layout_key diferente';
            }

            if (
                (string) $dbVersion->pdf_view !==
                $pdfView
            ) {
                $problems[] =
                    'pdf_view diferente';
            }

            /*
             * IMPORTANTE:
             *
             * NO comparamos ni actualizamos automáticamente
             * payload, fecha_desde o fecha_hasta.
             *
             * Esos datos forman parte del snapshot histórico.
             */

            if (count($problems) > 0) {
                $errors[] =
                    "{$versionKey}: " .
                    implode(
                        ', ',
                        $problems
                    );

                $rows[] = [
                    $formKey,
                    $version,
                    $versionKey,
                    'ERROR',
                    implode(
                        ', ',
                        $problems
                    ),
                ];

                continue;
            }

            $rows[] = [
                $formKey,
                $version,
                $versionKey,
                'OK',
                'Coincide con BD',
            ];
        }

        $this->table(
            [
                'Formulario',
                'V',
                'Version Key',
                'Estado',
                'Detalle',
            ],
            $rows
        );

        $this->newLine();

        $this->info(
            'Versiones declaradas en código: '
            . count($packages)
        );

        $this->info(
            'Versiones existentes en BD: '
            . collect($rows)
                ->where(3, 'OK')
                ->count()
        );

        $this->info(
            'Versiones pendientes de crear: '
            . collect($rows)
                ->where(3, 'PENDIENTE')
                ->count()
        );

        if (count($errors) > 0) {
            $this->newLine();

            $this->error(
                'Se encontraron inconsistencias.'
            );

            foreach ($errors as $error) {
                $this->line(
                    ' - ' . $error
                );
            }

            return self::FAILURE;
        }

        $this->newLine();

        $this->info(
            'Registro de versiones consistente.'
        );

        return self::SUCCESS;
    }
}