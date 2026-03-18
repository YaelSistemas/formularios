<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-08-FO-01 Checklist de Herramienta Eléctrica Portátil';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'checklist_herramienta_electrica_portatil',
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
                    'text' => 'Checklist de Herramienta Eléctrica Portátil',
                ],
                [
                    'id' => 'header_line_4',
                    'label' => 'Código',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Codigo: SST-POP-TA-08-FO-01',
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
                    'options' => ['Apaxco','Aztecas', 'Cedis Pachuca', 'Cedis Pachuca Calidad/PTS', 'Cedis Pachuca Tip Top', 'Colima', 'Huichapan', 'Monterrey',
                                  'Peñasquito', 'San Luis Potosi', 'Tamuin', 'Tepeaca', 'Torreon', 'Xoxtla', 'Zacatecas'],
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
                    'required' => false,
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
                    'text' => 'Este formato debera llenarse',
                ],
                [
                    'id' => 'indicaciones_line_2',
                    'label' => 'Indicacion 2',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Marque segun lo que aplique',
                ],

                [
                    'id' => 'tabla_herramientas',
                    'label' => 'Herramientas',
                    'type' => 'table',
                    'required' => false,
                    'columns' => [
                        'Tipo de Herramienta',
                        '# Serie',
                        'Conexiones electricas',
                        'Interruptores',
                        'Condiciones fisicas',
                        'Mango de sujecion',
                        'Aditamientos',
                        'Prueba de funcionamiento',
                        'Acciones',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'tipo_herramienta',
                            'label' => 'Tipo de Herramienta',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Taladro', 'Martillo', 'Pulidora'],
                        ],
                        [
                            'id' => 'serie',
                            'label' => '# Serie',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'conexiones_electricas',
                            'label' => 'Conexiones electricas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'interruptores',
                            'label' => 'Interruptores',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'condiciones_fisicas',
                            'label' => 'Condiciones fisicas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'mango_sujecion',
                            'label' => 'Mango de sujecion',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'aditamientos',
                            'label' => 'Aditamientos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'prueba_funcionamiento',
                            'label' => 'Prueba de funcionamiento',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Si', 'No', 'NA'],
                        ],
                        [
                            'id' => 'acciones',
                            'label' => 'Acciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => [
                                'La Herramienta esta en buenas condiciones',
                                'La Herramienta se identifica como dañada',
                            ],
                        ],
                        [
                            'id' => 'observaciones',
                            'label' => 'Observaciones',
                            'type' => 'text',
                            'required' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}