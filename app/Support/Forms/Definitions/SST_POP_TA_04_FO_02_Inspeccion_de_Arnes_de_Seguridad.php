<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_04_fo_02_inspeccion_de_arnes_de_seguridad';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-04-FO-02 Inspección de Arnés de Seguridad';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'inspeccion_de_arnes_de_seguridad',
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
                    'text' => 'INSPECCIÓN DE ARNÉS DE SEGURIDAD',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-04-FO-02',
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
                    'id' => 'nombre_responsable_inspeccion',
                    'label' => 'Nombre del Responsable de Inspección',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_responsable_inspeccion',
                    'label' => 'Firma del Responsable de Inspección',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA04FO02_InspeccionArnesSeguridad/Responsable_Inspeccion',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a la Inspección de Arnés.',
                ],
                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a Inspeccionar',
                ],

                [
                    'id' => 'tabla_arnes_seguridad',
                    'label' => 'Criterios a Inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Número de Arnés',
                        'Correas y Costuras',
                        'D-Ring',
                        'Hebillas',
                        'Etiqueta',
                        'Acciones',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_arnes',
                            'label' => 'Número de Arnés',
                            'type' => 'text',
                            'required' => true,
                        ],

                        [
                            'id' => 'correas_costuras_titulo',
                            'label' => 'CORREAS Y COSTURAS',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'CORREAS Y COSTURAS',
                        ],
                        [
                            'id' => 'correas_1_hombros',
                            'label' => '1. De Hombros: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'correas_2_pecho',
                            'label' => '2. Del Pecho: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'correas_3_espalda',
                            'label' => '3. De Espalda: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'correas_4_piernas',
                            'label' => '4. De Piernas: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'correas_5_cintura',
                            'label' => '5. De Cintura (Sí Aplica): Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],

                        [
                            'id' => 'd_ring_titulo',
                            'label' => 'D-RING',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'D-RING',
                        ],
                        [
                            'id' => 'd_ring_6_dorsal',
                            'label' => '6. Dorsal: Gastados, Oxidados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'd_ring_7_cintura',
                            'label' => '7. De Cintura (Sí Aplica): Gastados, Oxidados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'd_ring_8_esternon',
                            'label' => '8. De Esternón (Sí Aplica): Gastados, Oxidados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],

                        [
                            'id' => 'hebillas_titulo',
                            'label' => 'HEBILLAS',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'HEBILLAS',
                        ],
                        [
                            'id' => 'hebillas_9_ajuste_hombros',
                            'label' => '9. Ajuste en Hombros: Flojas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'hebillas_10_pecho_espalda',
                            'label' => '10. Pecho y Espalda: Flojas, Oxidadas, Gastadas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'hebillas_11_mosqueton_pecho',
                            'label' => '11. Mosquetón de Pecho (Sí Aplica): Flojas, Oxidadas, Gastadas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'hebillas_12_ajuste_piernas',
                            'label' => '12. Ajuste en Piernas: Flojas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],

                        [
                            'id' => 'etiqueta_titulo',
                            'label' => 'ETIQUETA',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'ETIQUETA',
                        ],
                        [
                            'id' => 'etiqueta_faltante',
                            'label' => 'Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],
                        [
                            'id' => 'etiqueta_legible',
                            'label' => 'Legible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['SI', 'NO', 'NA'],
                        ],

                        [
                            'id' => 'acciones',
                            'label' => 'Acciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => [
                                'El Arnés se marca como dañado y es sacado de uso',
                                'El Arnés está en buenas condiciones',
                            ],
                        ],
                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}