<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_FO_08_Inspeccion_de_Grua_Viajera implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_fo_08_inspeccion_de_grua_viajera';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-FO-08 Inspección de Grúa Viajera';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            '( S ) Satisfactorio',
            '( X ) No Satisfactorio',
            '( N ) No Aplica',
        ];

        return [
            'meta' => [
                'layout' => 'inspeccion_de_grua_viajera',
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
                    'text' => 'Inspección de Grúa Viajera',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-FO-08',
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
                    'save_path' => 'forms/signatures/SGIPOPLG01FO08_InspeccionGruaViajera/Responsable_Inspeccion',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones de la grúa',
                ],

                [
                    'id' => 'tabla_grua_viajera',
                    'label' => 'Inspección de Grúa Viajera',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Resultado',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_revision',
                            'label' => 'Número de Revisión',
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
                            'id' => 'observaciones_capacidad',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_1_estado',
                            'label' => 'Revisión total del gancho (pestillo, grietas, fatiga, sin desgaste)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_1_observaciones',
                            'label' => 'Observaciones - Revisión total del gancho',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_2_estado',
                            'label' => 'Revisión de cable (sin roturas, aplastamientos, cocas, desgaste)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_2_observaciones',
                            'label' => 'Observaciones - Revisión de cable',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_3_estado',
                            'label' => 'Inspección que cable no se enrede en el tambor',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_3_observaciones',
                            'label' => 'Observaciones - Inspección que cable no se enrede en el tambor',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_4_estado',
                            'label' => 'Estado físico y operación de botonera (Limpieza, estado físico y sin falso contacto, carcasa de botonera sin fisuras)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_4_observaciones',
                            'label' => 'Observaciones - Estado físico y operación de botonera',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_5_estado',
                            'label' => 'Comprobar botones de elevación y descenso (Limpieza, estado físico y sin falso contacto)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_5_observaciones',
                            'label' => 'Observaciones - Botones de Elevación y Descenso',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_6_estado',
                            'label' => 'Comprobar botones de movimientos a través del puente izq.- der. (Limpieza, estado físico y sin falso contacto)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_6_observaciones',
                            'label' => 'Observaciones - Botones de Movimientos a través del puente',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_7_estado',
                            'label' => 'Comprobar botones de movimiento longitudinal o transversal (Limpieza, estado físico y sin falso contacto)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_7_observaciones',
                            'label' => 'Observaciones - Botones de Movimiento Longitudinal o Transversal',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_8_estado',
                            'label' => 'Comprobar botón de paro de emergencia (Limpieza, estado físico y sin falso contacto)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_8_observaciones',
                            'label' => 'Observaciones - Botón de Paro de Emergencia',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_9_estado',
                            'label' => 'Comprobar botones adicionales si aplican (Limpieza, estado físico y sin falso contacto)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_9_observaciones',
                            'label' => 'Observaciones - Botones Adicionales',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_10_estado',
                            'label' => 'Comprobar alarma acústica',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_10_observaciones',
                            'label' => 'Observaciones - Alarma Acústica',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_11_estado',
                            'label' => 'Comprobar luz de torreta o estroboótica',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_11_observaciones',
                            'label' => 'Observaciones - Luz de Torreta o Estroboótica',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_12_estado',
                            'label' => 'Comprobar final de carrera de puente',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_12_observaciones',
                            'label' => 'Observaciones - Final de Carrera de Puente',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_13_estado',
                            'label' => 'Comprobar final de carrera de carro',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_13_observaciones',
                            'label' => 'Observaciones - Final de Carrera de Carro',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_14_estado',
                            'label' => 'Comprobar final de cerrera de gancho',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_14_observaciones',
                            'label' => 'Observaciones - Final de Carrera de Gancho',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_15_estado',
                            'label' => 'Comprobar funcionamiento de medidor de carga',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_15_observaciones',
                            'label' => 'Observaciones - Medidor de Carga',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_16_estado',
                            'label' => 'Comprobar los accesos (que no haya obstáculos)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_16_observaciones',
                            'label' => 'Observaciones - Accesos',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_17_estado',
                            'label' => 'Revisar que los topes de grúa se encuentren bien',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_17_observaciones',
                            'label' => 'Observaciones - Topes de Grúa',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_18_estado',
                            'label' => 'Revisar buen estado de todas las advertencias de riegos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_18_observaciones',
                            'label' => 'Observaciones - Advertencias de Riesgos',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'grua_19_estado',
                            'label' => 'Ruidos extraños',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'grua_19_observaciones',
                            'label' => 'Observaciones - Ruidos Extraños',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}