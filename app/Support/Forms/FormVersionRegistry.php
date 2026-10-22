<?php

namespace App\Support\Forms;

use RuntimeException;

class FormVersionRegistry
{
    public function __construct(
        protected FormRegistry $formRegistry
    ) {
    }

    /**
     * Devuelve todos los paquetes de versiones
     * conocidos por el código.
     *
     * Las Definitions actuales de FormCatalog
     * representan permanentemente V1.
     *
     * V2, V3, etc. se registrarán en
     * additionalVersions().
     */
    public function all(): array
    {
        $packages = [];

        /*
        |--------------------------------------------------------------------------
        | Versiones V1
        |--------------------------------------------------------------------------
        |
        | Las 31 Definitions actuales son la versión inicial
        | de cada formulario.
        |
        | IMPORTANTE:
        | Una Definition existente de FormCatalog nunca debe
        | sustituirse por una V2/V3.
        |
        */

        foreach ($this->formRegistry->all() as $form) {
            $formKey = trim(
                (string) (
                    $form['key'] ?? ''
                )
            );

            if ($formKey === '') {
                continue;
            }

            $packages[] =
                $this->buildPackage(
                    formKey:
                        $formKey,

                    version:
                        1,

                    definitionClass:
                        (string) (
                            $form['definition_class']
                            ?? ''
                        ),

                    layoutKey:
                        (string) (
                            $form['layout_key']
                            ?? ''
                        ),

                    pdfView:
                        (string) (
                            $form['pdf_view']
                            ?? ''
                        )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Versiones posteriores
        |--------------------------------------------------------------------------
        */

        foreach (
            $this->additionalVersions()
            as $version
        ) {
            $packages[] =
                $this->buildPackage(
                    formKey:
                        (string) (
                            $version['form_key']
                            ?? ''
                        ),

                    version:
                        (int) (
                            $version['version']
                            ?? 0
                        ),

                    definitionClass:
                        (string) (
                            $version['definition_class']
                            ?? ''
                        ),

                    layoutKey:
                        (string) (
                            $version['layout_key']
                            ?? ''
                        ),

                    pdfView:
                        (string) (
                            $version['pdf_view']
                            ?? ''
                        )
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar duplicados
        |--------------------------------------------------------------------------
        */

        $versionKeys = array_map(
            fn (array $package) =>
                $package['version_key'],
            $packages
        );

        if (
            count($versionKeys) !==
            count(array_unique($versionKeys))
        ) {
            throw new RuntimeException(
                'Existen version_key duplicados en FormVersionRegistry.'
            );
        }

        $formVersions = array_map(
            fn (array $package) =>
                $package['form_key']
                . '#'
                . $package['version'],
            $packages
        );

        if (
            count($formVersions) !==
            count(array_unique($formVersions))
        ) {
            throw new RuntimeException(
                'Existe más de una definición para la misma versión de un formulario.'
            );
        }

        usort(
            $packages,
            function (
                array $a,
                array $b
            ) {
                $formCompare =
                    strcmp(
                        $a['form_key'],
                        $b['form_key']
                    );

                if ($formCompare !== 0) {
                    return $formCompare;
                }

                return
                    $a['version']
                    <=>
                    $b['version'];
            }
        );

        return $packages;
    }

    /**
     * Aquí se registrarán únicamente
     * V2, V3, V4...
     *
     * V1 NO se agrega aquí porque se obtiene
     * automáticamente de las Definitions
     * originales de FormCatalog.
     */
    private function additionalVersions(): array
    {
        return [
            /*
             * Ejemplo futuro:
             *
             * [
             *     'form_key' =>
             *         'sst_pop_ta_07_fo_01_inspeccion_de_compresor',
             *
             *     'version' =>
             *         2,
             *
             *     'definition_class' =>
             *         \App\Support\Forms\Definitions\
             *         SST_POP_TA_07_FO_01_Inspeccion_de_Compresor_V2::class,
             *
             *     'layout_key' =>
             *         'inspeccion_compresor_v2',
             *
             *     'pdf_view' =>
             *         'pdf.forms.SST_POP_TA_07_FO_01_Inspeccion_de_Compresor_V2',
             * ],
             */
        ];
    }

    /**
     * Construye y valida un paquete completo
     * Definition + JSX + PDF.
     */
    private function buildPackage(
        string $formKey,
        int $version,
        string $definitionClass,
        string $layoutKey,
        string $pdfView
    ): array {
        $formKey =
            trim($formKey);

        $definitionClass =
            trim($definitionClass);

        $layoutKey =
            trim($layoutKey);

        $pdfView =
            trim($pdfView);

        if ($formKey === '') {
            throw new RuntimeException(
                'Una versión no tiene form_key.'
            );
        }

        if ($version < 1) {
            throw new RuntimeException(
                "La versión de {$formKey} debe ser mayor o igual a 1."
            );
        }

        if (
            $definitionClass === '' ||
            !class_exists(
                $definitionClass
            )
        ) {
            throw new RuntimeException(
                "La Definition de {$formKey} V{$version} no existe."
            );
        }

        foreach (
            [
                'key',
                'title',
                'payload',
            ]
            as $method
        ) {
            if (
                !method_exists(
                    $definitionClass,
                    $method
                )
            ) {
                throw new RuntimeException(
                    "La Definition {$definitionClass} no contiene el método {$method}()."
                );
            }
        }

        /*
         * La Definition de una V2/V3 debe conservar
         * el mismo key estable del formulario.
         */
        $definitionKey =
            trim(
                (string)
                $definitionClass::key()
            );

        if (
            $definitionKey !==
            $formKey
        ) {
            throw new RuntimeException(
                "La Definition {$definitionClass} pertenece a '{$definitionKey}' y no a '{$formKey}'."
            );
        }

        $payload =
            $definitionClass::payload();

        if (!is_array($payload)) {
            throw new RuntimeException(
                "El payload de {$formKey} V{$version} no es válido."
            );
        }

        if ($layoutKey === '') {
            throw new RuntimeException(
                "No se definió layout_key para {$formKey} V{$version}."
            );
        }

        $payloadLayout =
            trim(
                (string)
                data_get(
                    $payload,
                    'meta.layout',
                    ''
                )
            );

        if (
            $payloadLayout !==
            $layoutKey
        ) {
            throw new RuntimeException(
                "El layout del payload de {$formKey} V{$version} no coincide con '{$layoutKey}'."
            );
        }

        if ($pdfView === '') {
            throw new RuntimeException(
                "No se definió pdf_view para {$formKey} V{$version}."
            );
        }

        return [
            'form_key' =>
                $formKey,

            'version' =>
                $version,

            'version_key' =>
                $formKey
                . '_v'
                . $version,

            'title' =>
                (string)
                $definitionClass::title(),

            'definition_class' =>
                $definitionClass,

            'layout_key' =>
                $layoutKey,

            'pdf_view' =>
                $pdfView,

            /*
             * Snapshot generado desde esa
             * Definition específica.
             */
            'payload' =>
                $payload,
        ];
    }

    /**
     * Todas las versiones conocidas
     * de un formulario.
     */
    public function forForm(
        string $formKey
    ): array {
        $formKey =
            trim($formKey);

        return array_values(
            array_filter(
                $this->all(),
                fn (array $package) =>
                    $package['form_key']
                    ===
                    $formKey
            )
        );
    }

    /**
     * Obtener un paquete por version_key.
     */
    public function find(
        string $versionKey
    ): ?array {
        $versionKey =
            trim($versionKey);

        foreach (
            $this->all()
            as $package
        ) {
            if (
                $package['version_key']
                ===
                $versionKey
            ) {
                return $package;
            }
        }

        return null;
    }
}