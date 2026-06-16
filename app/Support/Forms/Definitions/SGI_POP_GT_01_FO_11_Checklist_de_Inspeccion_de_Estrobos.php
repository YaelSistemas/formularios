<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_GT_01_FO_11_Checklist_de_Inspeccion_de_Estrobos implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_gt_01_fo_11_checklist_de_inspeccion_de_estrobos';
    }

    public static function title(): string
    {
        return 'SGI-POP-GT-01-FO-11 Checklist de Inspección de Estrobos';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            '( ✓ ) Bien',
            '( X ) Mal',
            '( N/A ) No Aplica',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_inspeccion_de_estrobos',
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
                    'text' => 'Checklist de Inspección de Estrobos',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-GT-01-FO-11',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 01',
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
                    'save_path' => 'forms/signatures/SGIPOPGT01FO11_ChecklistInspeccionEstrobos/Firma_Inspector',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Marque según las condiciones del estrobo',
                ],

                [
                    'id' => 'tabla_estrobos',
                    'label' => 'Tabla de Inspección de Estrobos',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'N° de identificación',
                        'Capacidad',
                        'Diámetro de cable',
                        'Cocas',
                        'Cables',
                        'Torones',
                        'Alambres',
                        'Casquillos',
                        'Condición de alma o soporte central',
                        'Lubricación',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'imagen_estrobo',
                            'type' => 'fixed_image',
                            'label' => 'Guía visual',
                            'url' => '/images/forms/SGI_POP_GT_01_FO_11_Checklist_de_Inspeccion_de_Estrobos/estrobos.png',
                        ],
                        [
                            'id' => 'numero_identificacion',
                            'label' => 'N° de identificación',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'capacidad',
                            'label' => 'Capacidad',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'diametro_cable',
                            'label' => 'Diámetro de cable',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'cocas_estado',
                            'label' => 'Cocas (distorsionadas, dobladas, oxidadas, corroídas)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'cables_estado',
                            'label' => 'Cables (distorsionados, oxidados, corroídos)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'torones_estado',
                            'label' => 'Torones (desgastados o cortados)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'alambres_estado',
                            'label' => 'Alambres (desgastados o cortados)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'casquillos_estado',
                            'label' => 'Casquillos (deformaciones, abiertos, oxidados, corroídos)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'condicion_alma_soporte_central_estado',
                            'label' => 'Condición de alma o soporte central',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'lubricacion_estado',
                            'label' => 'Lubricación (sequedad)',
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
                    ],
                ],

                [
                    'id' => 'nombre_supervisor',
                    'label' => 'Nombre del Supervisor',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_supervisor',
                    'label' => 'Firma del Supervisor',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO11_ChecklistInspeccionEstrobos/Firma_Supervisor',
                ],
            ],
        ];
    }
}
