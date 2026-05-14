<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_04_FO_01_Checklist_de_Sand_Blast implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_04_fo_01_checklist_de_sand_blast';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-04-FO-01 Checklist de Sand Blast';
    }

    public static function payload(): array
    {
        $estadoOptions = ['Buen estado', 'Mal estado'];

        return [
            'meta' => [
                'layout' => 'checklist_de_sand_blast',
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
                    'text' => 'SISTEMA DE GESTION INTEGRAL',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'CHECKLIST DE SAND BLAST',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-04-FO-01',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 04',
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
                    'text' => 'Este checklist deberá llenarse cada que use el equipo y en caso de no usarse llenarse una vez al mes.',
                ],
                [
                    'id' => 'indicacion_2',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones del equipo',
                ],
                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a Inspeccionar',
                ],

                [
                    'id' => 'tabla_sand_blast',
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
                            'id' => 'sand_blast_1_estado',
                            'label' => '1. Tanque Receptor de Abrasivo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_1_observaciones',
                            'label' => 'Observaciones - Tanque Receptor de Abrasivo',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_2_estado',
                            'label' => '2. Trampa Tortuga',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_2_observaciones',
                            'label' => 'Observaciones - Trampa Tortuga',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_3_estado',
                            'label' => '3. Maneral, Patas y Ruedas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_3_observaciones',
                            'label' => 'Observaciones - Maneral, Patas y Ruedas',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_4_estado',
                            'label' => '4. Trampa Humedad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_4_observaciones',
                            'label' => 'Observaciones - Trampa Humedad',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_5_estado',
                            'label' => '5. Manómetro',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_5_observaciones',
                            'label' => 'Observaciones - Manómetro',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_6_estado',
                            'label' => '6. Purga de Humedad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_6_observaciones',
                            'label' => 'Observaciones - Purga de Humedad',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_7_estado',
                            'label' => '7. Válvula para Desfogue con Silenciador',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_7_observaciones',
                            'label' => 'Observaciones - Válvula para Desfogue con Silenciador',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_8_estado',
                            'label' => '8. Oring',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_8_observaciones',
                            'label' => 'Observaciones - Oring',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_9_estado',
                            'label' => '9. Válvula Cónica',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_9_observaciones',
                            'label' => 'Observaciones - Válvula Cónica',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_10_estado',
                            'label' => '10. Válvula Mezcladora',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_10_observaciones',
                            'label' => 'Observaciones - Válvula Mezcladora',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_11_estado',
                            'label' => '11. Empaque para Válvula Mezcladora',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_11_observaciones',
                            'label' => 'Observaciones - Empaque para Válvula Mezcladora',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_12_estado',
                            'label' => '12. Conector Garra para Válvula Mezcladora',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_12_observaciones',
                            'label' => 'Observaciones - Conector Garra para Válvula Mezcladora',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_13_estado',
                            'label' => '13. Manguera paso de Aire',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_13_observaciones',
                            'label' => 'Observaciones - Manguera paso de Aire',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_14_estado',
                            'label' => '14. Conectores de Aire',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_14_observaciones',
                            'label' => 'Observaciones - Conectores de Aire',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_15_estado',
                            'label' => '15. Válvula paso de Aire a Abrasivo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_15_observaciones',
                            'label' => 'Observaciones - Válvula paso de Aire a Abrasivo',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_16_estado',
                            'label' => '16. Conector Garra Manguera Abrasivo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_16_observaciones',
                            'label' => 'Observaciones - Conector Garra Manguera Abrasivo',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_17_estado',
                            'label' => '17. Manguera de Abrasivo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_17_observaciones',
                            'label' => 'Observaciones - Manguera de Abrasivo',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_18_estado',
                            'label' => '18. Porta Boquilla',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_18_observaciones',
                            'label' => 'Observaciones - Porta Boquilla',
                            'type' => 'textarea',
                            'required' => true,
                        ],

                        [
                            'id' => 'sand_blast_19_estado',
                            'label' => '19. Boquilla',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'sand_blast_19_observaciones',
                            'label' => 'Observaciones - Boquilla',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                    ],
                ],

                [
                    'id' => 'nombre_inspecciona',
                    'label' => 'Nombre de quien inspecciona',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspecciona',
                    'label' => 'Firma de quien inspecciona',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA04FO01_ChecklistSandBlast/Inspecciona',
                ],
                [
                    'id' => 'nombre_supervisa',
                    'label' => 'Nombre de quien supervisa',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_supervisa',
                    'label' => 'Firma de quien supervisa',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA04FO01_ChecklistSandBlast/Supervisa',
                ],
            ],
        ];
    }
}