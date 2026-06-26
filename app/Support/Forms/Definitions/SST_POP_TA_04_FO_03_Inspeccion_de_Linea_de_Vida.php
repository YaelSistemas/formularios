<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_04_FO_03_Inspeccion_de_Linea_de_Vida implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_04_fo_03_inspeccion_de_linea_de_vida';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-04-FO-03 Inspección de Línea de Vida';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'inspeccion_de_linea_de_vida',
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
                    'text' => 'Inspección de Línea de Vida',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-04-FO-03',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 03',
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
                    'save_path' => 'forms/signatures/SSTPOPTA04FO03_InspeccionLineaVida/Inspector',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Este checklist deberá llenarse cada que se use línea de vida y en caso de no usarse llenarse una vez al mes.',
                ],
                [
                    'id' => 'indicacion_2',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones de la línea de vida.',
                ],
                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a inspeccionar',
                ],

                [
                    'id' => 'tabla_linea_vida',
                    'label' => 'Criterios a inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Número de Línea de Vida',
                        '1. Línea de Vida',
                        '2. Amortiguador',
                        '3. Ganchos',
                        'Etiqueta',
                        'Acciones',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_linea_vida',
                            'label' => 'Número de Línea de Vida',
                            'type' => 'text',
                            'required' => true,
                        ],

                        [
                            'id' => 'linea_vida_titulo',
                            'label' => '1. Línea de Vida',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '1.1. Costuras | 1.2. Terminación | 1.3. Cuerpo de la Línea de Vida',
                        ],
                        [
                            'id' => 'linea_vida_1_1',
                            'label' => '1.1. Costuras (Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'linea_vida_1_2',
                            'label' => '1.2. Terminación (Cortada, Quemada, Agujerada, Deshilachada, Decolorada, Empalmada)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'linea_vida_1_3',
                            'label' => '1.3. Cuerpo de la Línea de Vida (Cortado, Quemado, Agujerado, Deshilachado, Decolorado, Empalmado)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],

                        [
                            'id' => 'amortiguador_titulo',
                            'label' => '2. Amortiguador',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '2.1. Daño en Cubierta | 2.2. Deformación | 2.3. Señales de Activación',
                        ],
                        [
                            'id' => 'amortiguador_2_1',
                            'label' => '2.1. Daño en Cubierta',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'amortiguador_2_2',
                            'label' => '2.2. Deformación',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'amortiguador_2_3',
                            'label' => '2.3. Señales de Activación',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],

                        [
                            'id' => 'ganchos_titulo',
                            'label' => '3. Ganchos',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '3.1. Desgaste Excesivo, Deformaciones | 3.2. Picaduras, Grietas | 3.3. Resorte con Fallas | 3.4. Función de Bloqueo de Conector | 3.5. Corrosión',
                        ],
                        [
                            'id' => 'ganchos_3_1',
                            'label' => '3.1. Desgaste Excesivo, Deformaciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'ganchos_3_2',
                            'label' => '3.2. Picaduras, Grietas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'ganchos_3_3',
                            'label' => '3.3. Resorte con Fallas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'ganchos_3_4',
                            'label' => '3.4. Función de Bloqueo de Conector',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],
                        [
                            'id' => 'ganchos_3_5',
                            'label' => '3.5. Corrosión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['( ✓ ) Buen Estado', '( X ) Mal Estado', '( NA ) No Aplica'],
                        ],

                        [
                            'id' => 'etiqueta_titulo',
                            'label' => 'Etiqueta',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'Faltante | Legible',
                        ],
                        [
                            'id' => 'etiqueta_faltante',
                            'label' => 'Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],
                        [
                            'id' => 'etiqueta_legible',
                            'label' => 'Legible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],

                        [
                            'id' => 'acciones',
                            'label' => 'Acciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => [
                                'La Línea de Vida se Marca como Dañada y es Sacado de Uso',
                                'La Línea de Vida está en Buenas Condiciones',
                            ],
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