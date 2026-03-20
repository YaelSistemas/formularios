<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_07_FO_01_Inspeccion_de_Compresor implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_07_fo_01_inspeccion_de_compresor';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-07-FO-01 Inspección de Compresor';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'inspeccion_compresor',
            ],

            'fields' => [
                [
                    'id' => 'encabezado_logo',
                    'label' => 'Encabezado',
                    'type' => 'fixed_image',
                    'required' => false,
                    'url' => '/images/forms/Encabezado-vysisa.png',
                ],

                [
                    'id' => 'header_line_1',
                    'label' => 'Empresa',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.',
                ],
                [
                    'id' => 'header_line_2',
                    'label' => 'Sistema',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'SISTEMA DE GESTION INTEGRAL',
                ],
                [
                    'id' => 'header_line_3',
                    'label' => 'Nombre del formato',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Inspección de Compresor',
                ],
                [
                    'id' => 'header_line_4',
                    'label' => 'Código',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Codigo: SST-POP-TA-07-FO-01',
                ],
                [
                    'id' => 'header_line_5',
                    'label' => 'Fecha de emisión',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Fecha de Emisión. 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'label' => 'Número de revisión',
                    'type' => 'static_text',
                    'required' => false,
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
                    'label' => 'Nombre del inspector',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspector',
                    'label' => 'Firma del inspector',
                    'type' => 'signature',
                    'required' => true,
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'label' => 'Indicaciones de llenado',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Indicaciones de llenado',
                ],
                [
                    'id' => 'indicaciones_line_1',
                    'label' => 'Indicacion 1',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Presion **: PRESIÓN DE CALIBRACIÓN EN SUS DISPOSITIVOS DE RELEVO DE PRESIÓN',
                ],
                [
                    'id' => 'indicaciones_line_2',
                    'label' => 'Indicacion 2',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Este formato deberá llenarse',
                ],
                [
                    'id' => 'indicaciones_line_3',
                    'label' => 'Indicacion 3',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Marque según lo que aplique',
                ],

                [
                    'id' => 'tabla_compresor',
                    'label' => 'Criterios a inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Tipo',
                        'Número de serie',
                        'Marca',
                        'Modelo',
                        'A) INTERRUPTOR DE ON (I) OFF (O)',
                        'B) MANÓMETRO DE TANQUE',
                        'C) MANÓMETRO (MEDIDOR DE PRESIÓN) DE SALIDA',
                        'D) REGULADOR',
                        'E) CONECTORES RÁPIDOS UNIVERSALES',
                        'F) VÁLVULA DE SEGURIDAD',
                        'G) VÁLVULA DE DRENAJE',
                        'H) ENROLLA CABLE ELÉCTRICO',
                        'I) VÁLVULA DE CONTROL',
                        'J) CABLE DE ALIMENTACIÓN ELÉCTRICA',
                        'K) CONTENEDOR',
                        'L) CARCASA',
                        'M) MANGUERA DE ALIMENTACIÓN',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'tipo',
                            'label' => 'Tipo',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'numero_serie',
                            'label' => 'Número de serie',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'marca',
                            'label' => 'Marca',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'modelo',
                            'label' => 'Modelo',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'interruptor_on_off',
                            'label' => 'A) INTERRUPTOR DE ON (I) OFF (O)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'manometro_tanque',
                            'label' => 'B) MANÓMETRO DE TANQUE',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'manometro_salida',
                            'label' => 'C) MANÓMETRO (MEDIDOR DE PRESIÓN) DE SALIDA',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'regulador',
                            'label' => 'D) REGULADOR',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'conectores_rapidos_universales',
                            'label' => 'E) CONECTORES RÁPIDOS UNIVERSALES',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'valvula_seguridad',
                            'label' => 'F) VÁLVULA DE SEGURIDAD',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'valvula_drenaje',
                            'label' => 'G) VÁLVULA DE DRENAJE',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'enrolla_cable_electrico',
                            'label' => 'H) ENROLLA CABLE ELÉCTRICO',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'valvula_control',
                            'label' => 'I) VÁLVULA DE CONTROL',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'cable_alimentacion_electrica',
                            'label' => 'J) CABLE DE ALIMENTACIÓN ELÉCTRICA',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'contenedor',
                            'label' => 'K) CONTENEDOR',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'carcasa',
                            'label' => 'L) CARCASA',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'manguera_alimentacion',
                            'label' => 'M) MANGUERA DE ALIMENTACIÓN',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Bien', 'Mal'],
                        ],
                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'text',
                            'required' => false,
                        ],
                    ],
                ],

                [
                    'id' => 'responsable_seguridad',
                    'label' => 'Nombre de Supervisor',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_responsable_seguridad',
                    'label' => 'Firma de Supervisor',
                    'type' => 'signature',
                    'required' => true,
                ],
            ],
        ];
    }
}