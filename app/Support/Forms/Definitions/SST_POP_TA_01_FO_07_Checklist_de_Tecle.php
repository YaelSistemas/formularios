<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_01_FO_07_Checklist_de_Tecle implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_01_fo_07_checklist_de_tecle';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-01-FO-07 Checklist de Tecle';
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
                'layout' => 'checklist_de_tecle',
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
                    'text' => 'Checklist de Tecle',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-01-FO-07',
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
                    'text' => 'Este formato deberá llenarse cada que se use el tecle y en caso de no usarse, llenarse una vez al mes.',
                ],
                [
                    'id' => 'indicacion_2',
                    'type' => 'static_text',
                    'text' => 'Marque según lo que aplique.',
                ],

                [
                    'id' => 'tabla_tecle',
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
                            'id' => 'tecle_1_estado',
                            'label' => '1. Ganchos (Superior e Inferior)',
                            'description' => 'Torcidos, flexionados, con demasiada apertura en la garganta, gastados, agrietados, con muescas, con estrías, oxidado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_2_estado',
                            'label' => '2. Seguros de los ganchos (Superior e Inferior)',
                            'description' => 'Torcidos, gastados, faltante, oxidado, no funciona',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_3_estado',
                            'label' => '3. Tornillos',
                            'description' => 'Gastados, flojos, oxidados, faltantes',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_4_estado',
                            'label' => '4. Perno del gancho',
                            'description' => 'Gastados, flojos, oxidados, faltantes',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_5_estado',
                            'label' => '5. Marco',
                            'description' => 'Decolorado, gastado, agrietado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_6_estado',
                            'label' => '6. Placa del fabricante',
                            'description' => 'Faltante o Ilegible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_7_estado',
                            'label' => '7. Rótulo de la capacidad',
                            'description' => 'Faltante o Ilegible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_8_estado',
                            'label' => '8. Cadena manual',
                            'description' => 'Gastada, oxidada, golpeada',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_9_estado',
                            'label' => '9. Cadena de carga',
                            'description' => 'Gastada, oxidada, golpeada',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_10_estado',
                            'label' => '10. Perilla de ajuste',
                            'description' => 'Se atora o faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_11_estado',
                            'label' => '11. Selector de dirección',
                            'description' => 'Se atora, no cambia de dirección, falta el tornillo del selector',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionesOptions,
                        ],
                        [
                            'id' => 'tecle_12_estado',
                            'label' => '12. Maneral',
                            'description' => 'Roto, torcido o quebrado',
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
                            'id' => 'condiciones_generales_tecle',
                            'label' => 'Condiciones generales del tecle',
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
                    'save_path' => 'forms/signatures/SSTPOPTA01FO07ChecklistTecle/Trabajador_Elabora_Checklist',
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
                    'save_path' => 'forms/signatures/SSTPOPTA01FO07ChecklistTecle/Supervisor_del_Trabajador',
                ],
            ],
        ];
    }
}