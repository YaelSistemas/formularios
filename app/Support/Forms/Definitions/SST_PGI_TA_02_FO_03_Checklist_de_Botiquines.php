<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_PGI_TA_02_FO_03_Checklist_de_Botiquines implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pgi_ta_02_fo_03_checklist_de_botiquines';
    }

    public static function title(): string
    {
        return 'SST-PGI-TA-02-FO-03 Checklist de Botiquines';
    }

    public static function payload(): array
    {
        $estadoMaterialOptions = [
            '( ✓ ) Se cuenta con Material',
            '( X ) Falta Material',
            '( F ) Requiere Material Caduco o Faltante',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_botiquines',
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
                    'text' => 'Checklist de Botiquines',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-PGI-TA-02-FO-03',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 09',
                ],

                [
                    'id' => 'datos_generales',
                    'type' => 'separator',
                    'label' => 'Datos generales',
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
                    'label' => 'Nombre de Inspector',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'firma_inspector',
                    'label' => 'Firma del Inspector',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPGITA02FO03ChecklistBotiquines/Inspector',
                ],

                [
                    'id' => 'separacion_tabla',
                    'type' => 'separator',
                    'label' => 'Checklist de Botiquines',
                ],

                [
                    'id' => 'indicacion_criterios',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado del botiquín',
                ],

                [
                    'id' => 'tabla_checklist_botiquines',
                    'label' => 'Checklist de Botiquines',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Condición',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_botiquin',
                            'label' => 'N° de Botiquín',
                            'type' => 'text',
                            'required' => true,
                        ],

                        [
                            'id' => 'material_seco',
                            'type' => 'static_text',
                            'text' => 'Material seco',
                        ],
                        [
                            'id' => 'torundas_jabon_quirurgico',
                            'label' => 'Torundas C/Jabón Quirúrgico (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'gasas_10_x_10',
                            'label' => 'Gasas de 10 x 10 cm (10 paq.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'venda_elastica_5_cm',
                            'label' => 'Venda Elástica de 5 cm (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'venda_elastica_10_cm',
                            'label' => 'Venda Elástica de 10 cm (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'cinta_adhesiva',
                            'label' => 'Cinta Adhesiva (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'cinta_micropore',
                            'label' => 'Cinta Micropore (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'abatelenguas',
                            'label' => 'Abatelenguas (5 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'curitas_vendas_adhesivas',
                            'label' => 'Curitas Vendas Adhesivas (10 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'copa_lavaojos',
                            'label' => 'Copa Lavaojos (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],

                        [
                            'id' => 'material_liquido',
                            'type' => 'static_text',
                            'text' => 'Material Líquido',
                        ],
                        [
                            'id' => 'merthiolate',
                            'label' => 'Merthiolate (1 fco.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'agua_oxigenada',
                            'label' => 'Agua Oxigenada (1 fco.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'alcohol',
                            'label' => 'Alcohol (1 fco.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'agua_esteril',
                            'label' => 'Agua Estéril (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],

                        [
                            'id' => 'material_instrumental',
                            'type' => 'static_text',
                            'text' => 'Material Instrumental',
                        ],
                        [
                            'id' => 'tijeras_punta_redonda',
                            'label' => 'Tijeras de Punta Redonda (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'termometro',
                            'label' => 'Termómetro (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'torniquete_compresor_elastico',
                            'label' => 'Torniquete o Compresor Elástico (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'jeringa_desechable',
                            'label' => 'Jeringa Desechable (2 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],

                        [
                            'id' => 'material_brigadista',
                            'type' => 'static_text',
                            'text' => 'Material Brigadista',
                        ],
                        [
                            'id' => 'kit_rcp_barrera',
                            'label' => 'Kit de RCP / Barrera (1 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'guantes_latex',
                            'label' => 'Guantes Latex (2 pr.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],
                        [
                            'id' => 'cubre_bocas',
                            'label' => 'Cubre bocas (2 pza.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoMaterialOptions,
                        ],

                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}