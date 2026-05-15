<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_01_FO_06_Checklist_de_Polipasto_Manual_de_Cadena implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_01_fo_06_checklist_de_polipasto_manual_de_cadena';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-01-FO-06 Checklist de Polipasto Manual de Cadena';
    }

    public static function payload(): array
    {
        $condicionesOptions = [
            'Buenas Condiciones',
            'Malas Condiciones',
        ];

        $siNoOptions = [
            'Si',
            'No',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_polipasto',
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
                    'text' => 'CHECKLIST DE POLIPASTO MANUAL DE CADENA',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-01-FO-06',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 02',
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
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Este formato deberá llenarse cada que se use el polipasto/tecle y en caso de no usarse, llenarse una vez al mes.',
                ],
                [
                    'id' => 'indicacion_2',
                    'type' => 'static_text',
                    'text' => 'Marque según lo que aplique.',
                ],

                [
                    'id' => 'tabla_polipasto_manual_cadena',
                    'label' => 'Criterios a Inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Condición',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_serie_identificacion',
                            'label' => 'No. de Serie/Identificación',
                            'type' => 'text',
                            'required' => true,
                        ],

                        [
                            'id' => 'polipasto_1_estado',
                            'label' => '1. Ganchos (Superior e Inferior)',
                            'description' => 'Torcidos, flexionados, con demasiada apertura en la garganta, gastados, agrietados, con muescas, con estrías, oxidado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_2_estado',
                            'label' => '2. Seguros de los ganchos (Superior e Inferior)',
                            'description' => 'Torcidos, gastados, faltante, oxidado, no funciona',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_3_estado',
                            'label' => '3. Tornillos',
                            'description' => 'Gastados, flojos, oxidados, faltantes',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_4_estado',
                            'label' => '4. Perno del gancho',
                            'description' => 'Gastados, flojos, oxidados, faltantes',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_5_estado',
                            'label' => '5. Marco',
                            'description' => 'Decolorado, gastado, agrietado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_6_estado',
                            'label' => '6. Placa del fabricante',
                            'description' => 'Faltante o Ilegible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_7_estado',
                            'label' => '7. Rótulo de la capacidad',
                            'description' => 'Faltante o Ilegible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_8_estado',
                            'label' => '8. Cadena manual',
                            'description' => 'Gastada, oxidada, golpeada',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'polipasto_9_estado',
                            'label' => '9. Cadena de carga',
                            'description' => 'Gastada, oxidada, golpeada',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],

                        [
                            'id' => 'cadena_desplaza_adecuadamente',
                            'label' => '¿La cadena se desplaza adecuadamente?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'sonido_inusual_engranes',
                            'label' => '¿Se escucha algún sonido inusual en los engranes?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'engranes_lubricados',
                            'label' => '¿Los engranes están lubricados?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],

                        [
                            'id' => 'condiciones_generales_polipasto',
                            'label' => 'Condiciones generales del polipasto manual de cadena',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                    ],
                ],

                [
                    'id' => 'nombre_trabajador_elabora_checklist',
                    'label' => 'Nombre del trabajador que elabora el checklist',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_trabajador_elabora_checklist',
                    'label' => 'Firma del trabajador que elabora el checklist',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA01FO06ChecklistPolipastoManualCadena/Trabajador_Elabora_Checklist',
                ],
                [
                    'id' => 'nombre_supervisor_trabajador',
                    'label' => 'Nombre del supervisor del trabajador',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_supervisor_trabajador',
                    'label' => 'Firma del supervisor del trabajador',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA01FO06ChecklistPolipastoManualCadena/Supervisor_del_Trabajador',
                ],
            ],
        ];
    }
}