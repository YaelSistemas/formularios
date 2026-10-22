<?php

namespace App\Support\Forms;

class FormRegistry
{
    public function all(): array
    {
        return collect(FormCatalog::definitions())
            ->map(function ($class) {
                $payload = $class::payload();

                return [
                    'key' => $class::key(),
                    'title' => $class::title(),
                    'payload' => $payload,

                    /*
                     * Información necesaria para el versionado.
                     */
                    'definition_class' => $class,

                    'layout_key' => (string) data_get(
                        $payload,
                        'meta.layout',
                        ''
                    ),

                    /*
                     * Actualmente los PDF Blade utilizan el mismo nombre
                     * base que su Definition.
                     *
                     * Ejemplo:
                     * App\Support\Forms\Definitions\...
                     *
                     * se convierte en:
                     * pdf.forms.NombreDelDefinition
                     */
                    'pdf_view' =>
                        'pdf.forms.' . class_basename($class),
                ];
            })
            ->values()
            ->all();
    }
}