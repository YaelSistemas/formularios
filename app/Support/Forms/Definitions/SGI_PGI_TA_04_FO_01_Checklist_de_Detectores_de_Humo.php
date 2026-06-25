<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_PGI_TA_04_FO_01_Checklist_de_Detectores_de_Humo implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pgi_ta_04_fo_01_checklist_de_detectores_de_humo';
    }

    public static function title(): string
    {
        return 'SGI-PGI-TA-04-FO-01 Checklist de Detectores de Humo';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            '( ✓ ) Buenas condiciones',
            '( X ) En malas condiciones',
            '( NA ) No aplica',
        ];

        $rowSchema = [
            [
                'id' => 'numero_detectores_humo',
                'label' => 'Numero de detectores de humo',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'ubicacion_detector_humo',
                'label' => 'Ubicación de detector de humo',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'estado_fisico_detector',
                'label' => 'Estado físico del detector',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'detectores_sin_obstrucciones',
                'label' => 'Detectores sin obstrucciones',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'prueba_boton_alarma',
                'label' => 'Prueba botón de alarma',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'bateria_detector',
                'label' => 'Batería del detector',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'separacion_detectores',
                'label' => 'Separación de detectores',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'limpieza_detector',
                'label' => 'Limpieza del detector',
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => 'observaciones',
                'label' => 'Observaciones',
                'type' => 'textarea',
                'required' => false,
            ],
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_detectores_de_humo',
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
                    'text' => 'Vulcanización y Servicios Industriales SA de CV',
                ],
                [
                    'id' => 'header_line_2',
                    'type' => 'static_text',
                    'text' => 'Sistema de Gestión Integral',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'Checklist de Detectores de Humo',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-PGI-TA-04-FO-01',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión:',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión:',
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
                    'id' => 'nombre_inspector',
                    'label' => 'Nombre del Inspector',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspector',
                    'label' => 'Firma del Inspector',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPGITA04FO01_ChecklistDetectoresHumo/Inspector',
                ],
                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de llenado',
                ],
                [
                    'id' => 'texto_criterios_detectores_humo',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones de detectores de humo',
                ],
                [
                    'id' => 'tabla_detectores_humo',
                    'label' => 'Checklist de Detectores de Humo',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Numero de detectores de humo',
                        'Ubicación de detector de humo',
                        'Estado físico del detector',
                        'Detectores sin obstrucciones',
                        'Prueba botón de alarma',
                        'Batería del detector',
                        'Separación de detectores',
                        'Limpieza del detector',
                        'Observaciones',
                    ],
                    'row_schema' => $rowSchema,
                ],
            ],
        ];
    }
}