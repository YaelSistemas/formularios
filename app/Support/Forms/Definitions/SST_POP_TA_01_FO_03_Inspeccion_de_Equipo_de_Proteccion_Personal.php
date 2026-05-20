<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_01_FO_03_Inspeccion_de_Equipo_de_Proteccion_Personal implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_01_fo_03_inspeccion_de_equipo_de_proteccion_personal';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-01-FO-03 Inspección de Equipo de Protección Personal';
    }

    public static function payload(): array
    {
        $estadoEppOptions = [
            '( ✓ ) Buenas Condiciones',
            '( X ) En Malas Condiciones',
            '( C ) Requiere Cambio',
            '( NT ) No Tiene',
            '( NA ) No Aplica',
        ];

        return [
            'meta' => [
                'layout' => 'inspeccion_de_equipo_de_proteccion_personal',
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
                    'text' => 'INSPECCIÓN DE EQUIPO DE PROTECCIÓN PERSONAL',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-01-FO-03',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 05',
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
                    'save_path' => 'forms/signatures/SSTPOPTA01FO03_InspeccionEquipoProteccionPersonal/Inspector',
                ],

                [
                    'id' => 'separacion_tabla',
                    'type' => 'separator',
                    'label' => 'Inspección de EPP',
                ],

                [
                    'id' => 'indicacion_criterios',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado del EPP',
                ],

                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a Inspeccionar',
                ],

                [
                    'id' => 'tabla_inspeccion_epp',
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
                            'id' => 'nombre_colaborador',
                            'label' => 'Nombre de Colaborador a Inspeccionar',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'firma_colaborador',
                            'label' => 'Firma Colaborador',
                            'type' => 'signature',
                            'required' => true,
                            'save_path' => 'forms/signatures/SSTPOPTA01FO03_InspeccionEquipoProteccionPersonal/Colaborador',
                        ],
                        [
                            'id' => 'guante_carnaza',
                            'label' => 'Guante de Carnaza',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'guantes_impacto',
                            'label' => 'Guantes de Impacto',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'guantes_nitrilo_media_palma',
                            'label' => 'Guantes de Nitrilo Media Palma',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'guantes_nitrilo_sqp',
                            'label' => 'Guantes de Nitrilo SQP',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'guantes_corte',
                            'label' => 'Guantes de Corte',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'tapones_conchas',
                            'label' => 'Tapones o Conchas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'lentes_seguridad_goggles',
                            'label' => 'Lentes de Seguridad / Goggles',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'casco_seguridad',
                            'label' => 'Casco de Seguridad (Carcaza, Suspension)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'barbiquejo',
                            'label' => 'Barbiquejo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'rodilleras',
                            'label' => 'Rodilleras (Desgastes, Roturas)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'zapato_seguridad',
                            'label' => 'Zapato de Seguridad (Estado de Punta y Suela)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'uniforme_completo',
                            'label' => 'Uniforme Completo (Estado de Costuras)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'respirador_media_cara',
                            'label' => 'Respirador de Media Cara',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'filtros_vapores_organicos',
                            'label' => 'Filtros Vapores Orgánicos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'filtros_particulas',
                            'label' => 'Filtros para Particulas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'navajas_stanley',
                            'label' => 'Navajas Stanley (Funcional)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'flexometro',
                            'label' => 'Flexometro (Funcional)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'candado',
                            'label' => 'Candado (Funcional)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'tarjeta_bloqueo',
                            'label' => 'Tarjeta de Bloqueo (Legible con Fotografía)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
                        ],
                        [
                            'id' => 'credencial_vysisa',
                            'label' => 'Credencial VYSISA (Vigente)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoEppOptions,
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