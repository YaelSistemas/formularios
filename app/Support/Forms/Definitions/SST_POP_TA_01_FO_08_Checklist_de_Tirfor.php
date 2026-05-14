<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_01_FO_08_Checklist_de_Tirfor implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_01_fo_08_checklist_de_tirfor';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-01-FO-08 Checklist de Tirfor';
    }

    public static function payload(): array
    {
        $estadoOptions = ['Buen estado', 'Dañado', 'No Aplica'];

        return [
            'meta' => [
                'layout' => 'checklist_de_tirfor',
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
                    'text' => 'VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.',
                ],
                [
                    'id' => 'header_line_2',
                    'type' => 'static_text',
                    'text' => 'SISTEMA DE GESTIÓN INTEGRAL',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'CHECKLIST DE TIRFOR',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-01-FO-08',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 13/06/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 00',
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
                    'id' => 'numero_identificacion',
                    'label' => 'No. de Identificación',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'capacidad_tirfor',
                    'label' => 'Capacidad de Tirfor',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones del tirfor',
                ],

                [
                    'id' => 'tabla_tirfor',
                    'label' => 'Criterios a Inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Estado',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'tirfor_1_estado',
                            'label' => '1. Gancho',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_1_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_2_estado',
                            'label' => '2. Pestillo de Seguridad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_2_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_3_estado',
                            'label' => '3. Palanca Telescópica',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_3_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_4_estado',
                            'label' => '4. Palanca de Destrabe',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_4_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_5_estado',
                            'label' => '5. Cubierta',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_5_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_6_estado',
                            'label' => '6. Manija de Transporte',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_6_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_7_estado',
                            'label' => '7. Palanca de Tracción',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_7_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_8_estado',
                            'label' => '8. Guía de Cable',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_8_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_9_estado',
                            'label' => '9. Cable de Acero',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_9_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_10_estado',
                            'label' => '10. Pin de Seguridad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_10_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tirfor_11_estado',
                            'label' => '11. Placa de Identificación',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'tirfor_11_observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],

                [
                    'id' => 'nombre_trabajador_elabora_checklist',
                    'label' => 'Nombre del Trabajador que Elabora el Checklist',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_trabajador_elabora_checklist',
                    'label' => 'Firma del Trabajador que Elabora el Checklist',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA01FO08_ChecklistTirfor/TrabajadorElaboraChecklist',
                ],
            ],
        ];
    }
}