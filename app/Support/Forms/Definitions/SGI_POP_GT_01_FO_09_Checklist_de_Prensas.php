<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_GT_01_FO_09_Checklist_de_Prensas implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_gt_01_fo_09_checklist_de_prensas';
    }

    public static function title(): string
    {
        return 'SGI-POP-GT-01-FO-09 Checklist de Prensas';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Cumple',
            'No cumple',
            'Faltante',
            'Mantenimiento',
            'No Aplica',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_prensas',
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
                    'text' => 'Vulcanización y Servicios Industriales S.A. de C.V.',
                ],
                [
                    'id' => 'header_line_2',
                    'type' => 'static_text',
                    'text' => 'Sistema de Gestión Integral',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'Checklist de Prensas',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-GT-01-FO-09',
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
                    'id' => 'codigo',
                    'label' => 'Código',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'no_serie',
                    'label' => 'No. de serie',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'separacion_indicaciones_llenado',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],

                [
                    'id' => 'titulo_tipo_prensa_corriente',
                    'type' => 'static_text',
                    'text' => 'MARCAR TIPO DE PRENSA A REVISIÓN Y TIPO DE CORRIENTE',
                ],
                [
                    'id' => 'imagen_prensas',
                    'type' => 'fixed_image',
                    'url' => '/images/forms/SGI_POP_GT_01_FO_09_Checklist_de_Prensas/prensas.png',
                ],
                [
                    'id' => 'tipo',
                    'label' => 'Tipo',
                    'type' => 'radio',
                    'required' => true,
                    'options' => [
                        'Caja',
                        'Rieles',
                    ],
                ],
                [
                    'id' => 'corriente',
                    'label' => 'Corriente',
                    'type' => 'radio',
                    'required' => true,
                    'options' => [
                        '220 Volts',
                        '440 Volts',
                    ],
                ],

                [
                    'id' => 'titulo_unidad_servicio',
                    'type' => 'static_text',
                    'text' => 'ESPECIFICAR UNIDAD DE SERVICIO',
                ],
                [
                    'id' => 'taller_origen',
                    'label' => 'Taller de origen',
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
                    'id' => 'taller_solicita',
                    'label' => 'Taller que solicita',
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
                    'id' => 'accion_realizar',
                    'label' => 'Especificar con una ( X ) la acción a realizar',
                    'type' => 'radio',
                    'required' => true,
                    'options' => [
                        'Préstamo',
                        'Devolución',
                        'Inspección',
                        'Mantenimiento',
                    ],
                ],
                [
                    'id' => 'texto_estado',
                    'type' => 'static_text',
                    'text' => 'Marque según corresponda el estado:',
                ],

                ...self::criterio('caja_control_cabezas_control', 'CAJA DE CONTROL Y/O CABEZAS DE CONTROL', $estadoOptions),
                ...self::criterio('plato_superior', 'PLATO SUPERIOR', $estadoOptions),
                ...self::criterio('plato_inferior', 'PLATO INFERIOR', $estadoOptions),
                ...self::criterio('termostato_superior', 'TERMOSTATO SUPERIOR', $estadoOptions),
                ...self::criterio('termostato_inferior', 'TERMOSTATO INFERIOR', $estadoOptions),
                ...self::criterio('termometro_plato_superior', 'TERMOMETRO PLATO SUPERIOR', $estadoOptions),
                ...self::criterio('termometro_plato_inferior', 'TERMOMETRO PLATO INFERIOR', $estadoOptions),
                ...self::criterio('cable_maestro_plato_superior', 'CABLE MAESTRO PLATO SUPERIOR', $estadoOptions),
                ...self::criterio('cable_maestro_plato_inferior', 'CABLE MAESTRO PLATO INFERIOR', $estadoOptions),
                ...self::criterio('ploga_alimentacion_principal', 'PLOGA DE ALIMENTACIÓN PRINCIPAL', $estadoOptions),
                ...self::criterio('extension_alimentacion_principal', 'EXTENSIÓN DE ALIMENTACIÓN PRINCIPAL', $estadoOptions),
                ...self::criterio('puente_interconector', 'PUENTE INTERCONECTOR', $estadoOptions),
                ...self::criterio('camara_presion', 'CAMARA DE PRESIÓN', $estadoOptions),
                ...self::criterio('acople_rapido', 'ACOPLE RAPIDO', $estadoOptions),
                ...self::criterio('verificador_presion', 'VERIFICADOR DE PRESIÓN', $estadoOptions),
                ...self::criterio('manguera_llenado', 'MANGUERA DE LLENADO', $estadoOptions),
                ...self::criterio('tornillos_pernos', 'TORNILLOS Y/O PERNOS', $estadoOptions),
                ...self::criterio('platos_compensadores_calor', 'PLATOS COMPENSADORES DE CALOR', $estadoOptions),
                ...self::criterio('rieles', 'RIELES', $estadoOptions),
                ...self::criterio('mangueras_enfriamiento', 'MANGUERAS PARA ENFRIAMIENTO', $estadoOptions),
                ...self::criterio('seguros_rieles', 'SEGUROS DE RIELES', $estadoOptions),
                ...self::criterio('sistema_presion_bomba_compresor', 'SISTEMA DE PRESIÓN: BOMBA / COMPRESOR', $estadoOptions),

                [
                    'id' => 'notas',
                    'label' => 'Notas',
                    'type' => 'textarea',
                    'required' => false,
                ],

                [
                    'id' => 'titulo_prestamo_devolucion',
                    'type' => 'static_text',
                    'text' => 'EN CASO DE PRESTAMO O DEVOLUCIÓN: INTEGRAR LOS DATOS DEL EQUIPO DE MEDICIÓN COMO COMPLEMENTO DE LA PRENSA',
                ],

                [
                    'id' => 'tabla_equipos_medicion',
                    'label' => 'Equipos de medición',
                    'type' => 'table',
                    'required' => false,
                
                    'row_schema' => [
                        [
                            'id' => 'cantidad',
                            'label' => 'Cantidad',
                            'type' => 'number',
                            'required' => false,
                        ],
                        [
                            'id' => 'nombre_equipo',
                            'label' => 'Nombre del equipo',
                            'type' => 'text',
                            'required' => false,
                        ],
                        [
                            'id' => 'numero_serie',
                            'label' => 'Número de serie',
                            'type' => 'text',
                            'required' => false,
                        ],
                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],

                [
                    'id' => 'titulo_firmas',
                    'type' => 'static_text',
                    'text' => 'COLOCAR FIRMAS QUE CORRESPONDAN',
                ],
                [
                    'id' => 'nombre_entrega_prensa',
                    'label' => 'Nombre de quien entrega prensa',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'id' => 'firma_entrega_prensa',
                    'label' => 'Firma de quien entrega prensa',
                    'type' => 'signature',
                    'required' => false,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO09_ChecklistPrensas/Entrega_Prensa',
                ],
                [
                    'id' => 'nombre_recibe_prensa',
                    'label' => 'Nombre de quien recibe prensa',
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'id' => 'firma_recibe_prensa',
                    'label' => 'Firma de quien recibe prensa',
                    'type' => 'signature',
                    'required' => false,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO09_ChecklistPrensas/Recibe_Prensa',
                ],
                [
                    'id' => 'nombre_inspecciona_mantenimiento',
                    'label' => 'Nombre de quien inspecciona o da mantenimiento',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspecciona_mantenimiento',
                    'label' => 'Firma de quien inspecciona o da mantenimiento',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO09_ChecklistPrensas/Inspecciona_Mantenimiento',
                ],
            ],
        ];
    }

    private static function criterio(string $id, string $titulo, array $estadoOptions): array
    {
        return [
            [
                'id' => 'subtitulo_' . $id,
                'type' => 'static_text',
                'text' => $titulo,
            ],
            [
                'id' => $id . '_estado',
                'label' => $titulo,
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => $id . '_cantidad',
                'label' => 'Cantidad (pza)',
                'type' => 'number',
                'required' => true,
            ],
            [
                'id' => $id . '_comentarios',
                'label' => 'Comentarios',
                'type' => 'textarea',
                'required' => false,
            ],
        ];
    }
}