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
            [
                'id' => 'evidencia_fotografica',
                'label' => 'Evidencia fotográfica',
                'type' => 'file',
                'required' => false,
                'multiple' => true,
                'accept' => 'image/*',
                // Verificar que el guardado de archivos dentro de row_schema
                // respete esta ruta al integrar el formulario.
                'save_path' => 'forms/files/SGIPOPFO05_ReporteMantenimientoPrensas/EvidenciaFotografica',
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
                    'text' => 'Fecha de Emisión: 11/09/2026',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 01',
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
                        'Apaxco', 'Aztecas', 'Cedis Pachuca',
                        'Cedis Pachuca Calidad/PTS', 'Cedis Pachuca Tip Top',
                        'Colima', 'Huichapan', 'Monterrey', 'Morelos',
                        'Orizaba', 'Peñasquito', 'San Luis Potosi', 'Tamuin',
                        'Tepeaca', 'Torreon', 'Vysisa Sureste (Merida)',
                        'Xoxtla', 'Zacatecas',
                    ],
                ],
                [
                    'id' => 'codigo',
                    'label' => 'Código',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'id' => 'tipo_prensa',
                    'label' => 'Tipo de prensa',
                    'type' => 'radio',
                    'required' => false,
                    'options' => ['H49', 'T79', 'E99', '1179', '1379', '1477', '1699', '1879'],
                ],
                [
                    'id' => 'tipo_voltaje',
                    'label' => 'Tipo de voltaje',
                    'type' => 'radio',
                    'required' => false,
                    'options' => ['220 VOLTS', '110 VOLTS'],
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
                    // El layout debe manejar este valor como un arreglo
                    // y la validación debe exigir al menos una selección.
                    'multiple' => true,
                    'options' => $accionesOptions,
                ],
                [
                    'id' => 'especifique_otra',
                    'label' => 'Especifique otra',
                    'type' => 'textarea',
                    // Obligatorio SOLO si acciones contiene "Otros".
                    // Implementar esta regla en el layout y en el servidor;
                    // required=false evita exigirlo incondicionalmente.
                    'required' => false,
                ],
                [
                    'id' => 'tabla_mantenimiento_prensas',
                    'label' => 'Detalle del mantenimiento',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Tipo de mantenimiento',
                        'Problema actual',
                        'Acciones realizadas',
                        'Evidencia fotográfica',
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
