<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_FO_05_Reporte_de_Mantenimiento_de_Prensas implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_fo_05_reporte_de_mantenimiento_de_prensas';
    }

    public static function title(): string
    {
        return 'SGI-POP-FO-05 Reporte de Mantenimiento de Prensas';
    }

    public static function payload(): array
    {
        $accionesOptions = [
            'Limpieza de cajas de control',
            'Revisión de conexiones eléctricas',
            'Revisión de contactores y termostatos',
            'Medición de resistencia',
            'Revisión de extensión y cables maestros',
            'Ajuste de tornillería',
            'Inspección de cámara de presión',
            'Revisión de conexiones hidráulicas y neumáticas',
            'Revisión de mangueras de llenado y enfriamiento',
            'Limpieza y lubricación de tornillos de ajuste',
            'Inspección de rieles o cajas que no estén dobladas',
            'Revisión de calentamiento de platinas 150° C a 165° C',
            'Otros',
        ];

        /*
         * El Tipo de mantenimiento ya se captura como campo independiente.
         *
         * Se conserva también dentro del row_schema únicamente como dato
         * interno de compatibilidad para que cada fila siga guardando
         * tipo_mantenimiento y no se afecte el PDF actual.
         *
         * El layout NO lo muestra dentro de la tabla.
         *
         * acciones_realizadas se maneja en el layout como un arreglo:
         *
         * [
         *     [
         *         'accion' => 'Texto libre obligatorio',
         *         'evidencia_fotografica' => [...], // opcional
         *     ],
         * ]
         */
        $rowSchema = [
            [
                'id' => 'tipo_mantenimiento',
                'label' => 'Tipo de mantenimiento',
                'type' => 'radio',
                'required' => true,
                'options' => ['Preventivo', 'Correctivo', 'Correctivo Preventivo'],
            ],
            [
                'id' => 'problema_actual',
                'label' => 'Problema actual',
                'type' => 'textarea',
                'required' => true,
            ],
            [
                'id' => 'acciones_realizadas',
                'label' => 'Acciones realizadas',
                'type' => 'textarea',
                'required' => true,
            ],
        ];

        return [
            'meta' => [
                'layout' => 'reporte_de_mantenimiento_de_prensas',
            ],
            'fields' => [
                [
                    'id' => 'encabezado_logo',
                    'type' => 'fixed_image',
                    'url' => '/images/forms/Encabezado-vysisa.png',
                ],
                [
                    'id' => 'header_line_1',
                    'type' => 'static_text',
                    'text' => 'Vulcanización y Servicios Industriales S.A. de C.V.',
                ],
                [
                    'id' => 'header_line_2',
                    'type' => 'static_text',
                    'text' => 'Sistema de Gestión Integral',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'Reporte de Mantenimiento de Prensas',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-FO-05',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 28/09/2026',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 02',
                ],
                [
                    'id' => 'numero_reporte',
                    'label' => 'No. Reporte',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'taller',
                    'label' => 'Taller',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        'Apaxco',
                        'Aztecas',
                        'Cedis Pachuca',
                        'Cedis Pachuca Calidad/PTS',
                        'Cedis Pachuca Tip Top',
                        'Colima',
                        'Huichapan',
                        'Monterrey',
                        'Morelos',
                        'Orizaba',
                        'Peñasquito',
                        'San Luis Potosi',
                        'Tamuin',
                        'Tepeaca',
                        'Torreon',
                        'Vysisa Sureste (Merida)',
                        'Xoxtla',
                        'Zacatecas',
                    ],
                ],
                [
                    'id' => 'codigo',
                    'label' => 'Código',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'tipo_prensa',
                    'label' => 'Tipo de prensa',
                    'type' => 'radio',
                    'required' => false,
                    'options' => [
                        'Caja',
                        'Rieles',
                    ],
                ],
                [
                    'id' => 'tipo_voltaje',
                    'label' => 'Tipo de voltaje',
                    'type' => 'radio',
                    'required' => false,
                    'options' => [
                        '440 VOLTS',
                        '220 VOLTS',
                        '110 VOLTS',
                    ],
                ],
                [
                    'id' => 'numero_serie',
                    'label' => 'No. de Serie',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'id' => 'acciones',
                    'label' => 'Acciones',
                    'type' => 'select',
                    'required' => true,
                    // El layout maneja este valor como un arreglo
                    // y la validación exige al menos una selección.
                    'multiple' => true,
                    'options' => $accionesOptions,
                ],
                [
                    'id' => 'especifique_otra',
                    'label' => 'Especifique otra',
                    'type' => 'textarea',
                    // Obligatorio SOLO si acciones contiene "Otros".
                    'required' => false,
                ],
                [
                    'id' => 'tipo_mantenimiento',
                    'label' => 'Tipo de mantenimiento',
                    'type' => 'radio',
                    'required' => true,
                    'options' => [
                        'Preventivo',
                        'Correctivo',
                        'Correctivo Preventivo',
                    ],
                ],
                [
                    'id' => 'tabla_mantenimiento_prensas',
                    'label' => 'Detalle del mantenimiento',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Problema actual',
                        'Acciones realizadas',
                    ],
                    'row_schema' => $rowSchema,
                ],
                [
                    'id' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'recomendaciones',
                    'label' => 'Recomendaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'nombre_inspecciona_mantenimiento',
                    'label' => 'Nombre de quien Inspecciona o da Mantenimiento',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspecciona_mantenimiento',
                    'label' => 'Firma de quien Inspecciona o da Mantenimiento',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPFO05_ReporteMantenimientoPrensas/Inspecciona',
                ],
                [
                    'id' => 'nombre_autoriza',
                    'label' => 'Nombre de quien autoriza',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_autoriza',
                    'label' => 'Firma de quien autoriza',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPFO05_ReporteMantenimientoPrensas/Autoriza',
                ],
            ],
        ];
    }
}
