<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_01_FO_04_Checklist_de_Inspeccion_de_Escaleras_Portatiles implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_01_fo_04_checklist_de_inspeccion_de_escaleras_portatiles';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-01-FO-04 Checklist de Inspección de Escaleras Portátiles';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Necesita Reparación',
            'Buen Estado',
        ];

        $estadoOpcionalOptions = [
            'Necesita Reparación',
            'Buen Estado',
            'No Aplica',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_escaleras_portatiles',
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
                    'text' => 'CHECKLIST DE INSPECCIÓN DE ESCALERAS PORTÁTILES',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-01-FO-04',
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
                    'label' => 'Nombre del Inspector',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'firma_inspector',
                    'label' => 'Firma del Inspector',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPOPTA01FO04_ChecklistInspeccionEscalerasPortatiles/Inspector',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de llenado',
                ],

                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Marque según lo que aplique.',
                ],

                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a inspeccionar',
                ],

                [
                    'id' => 'tabla_escaleras_portatiles',
                    'label' => 'Criterios a inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Condición',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'tipo_escalera',
                            'label' => 'Tipo de Escalera',
                            'type' => 'select',
                            'required' => true,
                            'options' => [
                                'Fija',
                                'Tijera',
                                'Extensión',
                                'Doble',
                                'Otro',
                            ],
                        ],

                        [
                            'id' => 'numero_identificacion_escalera',
                            'label' => 'Número de Identificación de la Escalera',
                            'type' => 'text',
                            'required' => true,
                        ],

                        [
                            'id' => 'zapatas_patas_estado',
                            'label' => 'Zapatas/Patas',
                            'description' => 'Gastado, Suelto, Rajado o Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'rieles_planos_verticales_estado',
                            'label' => 'Rieles/Planos Verticales',
                            'description' => 'Bordes Afilados, Rajados o Doblados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'escalones_peldanos_estado',
                            'label' => 'Escalones/Peldaños',
                            'description' => 'Sueltos, Roto, Gastado o Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'tope_superior_estado',
                            'label' => 'Tope Superior',
                            'description' => 'Rajado, Suelto o Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'ferreteria_estado',
                            'label' => 'Ferretería',
                            'description' => 'Difícil de Operar',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'limpieza_estado',
                            'label' => 'Limpieza',
                            'description' => 'Materiales Grasos, Aceitosos o Resbaladizos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'general_estado',
                            'label' => 'General',
                            'description' => 'Partes Oxidadas, Corroídas, Rajadas, Sueltas o Faltantes',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'etiquetas_estado',
                            'label' => 'Etiquetas',
                            'description' => 'Faltante o No Legible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'seguros_peldanos_estado',
                            'label' => 'Seguros de Peldaños',
                            'description' => 'Suelto, Roto o Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'escalera_libre_grietas_estado',
                            'label' => 'La Escalera está Libre de Grietas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],

                        [
                            'id' => 'cuerda_polea_estado',
                            'label' => 'Cuerda/Polea (Opcional)',
                            'description' => 'Gastado, Raído o Faltante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOpcionalOptions,
                        ],

                        [
                            'id' => 'brazos_union_buenas_condiciones',
                            'label' => 'Los Brazos de Unión se encuentran en Buenas Condiciones (Escalera de Tijera)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOpcionalOptions,
                        ],

                        [
                            'id' => 'seguros_buenas_condiciones',
                            'label' => 'Los Seguros están en Buenas Condiciones (Escalera Extensión)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOpcionalOptions,
                        ],

                        [
                            'id' => 'polea_buenas_condiciones',
                            'label' => 'La Polea está en Buenas Condiciones (Escalera de Extensión)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOpcionalOptions,
                        ],
                    ],
                ],

                [
                    'id' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ];
    }
}