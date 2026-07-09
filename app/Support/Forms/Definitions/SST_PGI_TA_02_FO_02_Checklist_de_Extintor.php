<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_PGI_TA_02_FO_02_Checklist_de_Extintor implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pgi_ta_02_fo_02_checklist_de_extintor';
    }

    public static function title(): string
    {
        return 'SST-PGI-TA-02-FO-02 Checklist de Extintor';
    }

    public static function payload(): array
    {
        $condicionExtintorOptions = [
            '( ✓ ) Buenas Condiciones',
            '( X ) En Malas Condiciones',
            '( NA ) No Aplica',
        ];

        $tipoExtintorOptions = [
            'PQS',
            'CO2',
            'Espuma Afff',
            'Agente Limpio',
            'Agua H2O',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_extintor',
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
                    'text' => 'Checklist de Extintor',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-PGI-TA-02-FO-02',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 06',
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
                    'save_path' => 'forms/signatures/SSTPGITA02FO02_ChecklistExtintor/Inspector',
                ],

                [
                    'id' => 'indicaciones_llenado',
                    'type' => 'separator',
                    'label' => 'Indicaciones de llenado',
                ],

                [
                    'id' => 'indicacion_criterios',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a la Inspección del extintor',
                ],

                [
                    'id' => 'criterios_a_inspeccionar',
                    'type' => 'separator',
                    'label' => 'Criterios a inspeccionar',
                ],

                [
                    'id' => 'tabla_checklist_extintor',
                    'label' => 'Checklist de Extintor',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Condición',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_extintor',
                            'label' => 'Número de Extintor',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'numero_kilos',
                            'label' => 'Número de Kilos',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'tipo_extintor',
                            'label' => 'Tipo de Extintor',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $tipoExtintorOptions,
                        ],

                        [
                            'id' => 'componente_extintor',
                            'type' => 'static_text',
                            'text' => 'Componente de Extintor',
                        ],

                        [
                            'id' => 'anillo_verificacion',
                            'label' => 'Anillo de Verificación',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'etiqueta_inspeccion',
                            'label' => 'Etiqueta de Inspección',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'caducidad',
                            'label' => 'Caducidad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'pasador_seguridad',
                            'label' => 'Pasador de Seguridad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'cincho_seguridad',
                            'label' => 'Cincho de Seguridad',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'manometro',
                            'label' => 'Manómetro',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'presion',
                            'label' => 'Presión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'lleno',
                            'label' => 'Lleno',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'manguera_boquilla',
                            'label' => 'Manguera y Boquilla',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'senaletica',
                            'label' => 'Señalética',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'soportes_funda',
                            'label' => 'Soportes y Funda',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
                        ],
                        [
                            'id' => 'limpieza',
                            'label' => 'Limpieza',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $condicionExtintorOptions,
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