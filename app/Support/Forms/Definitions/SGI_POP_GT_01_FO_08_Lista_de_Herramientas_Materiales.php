<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_GT_01_FO_08_Lista_de_Herramientas_Materiales implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_gt_01_fo_08_lista_de_herramientas_materiales';
    }

    public static function title(): string
    {
        return 'SGI-POP-GT-01-FO-08 Lista de Herramientas Materiales';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Cumple',
            'No Cumple',
        ];

        return [
            'meta' => [
                'layout' => 'lista_de_herramientas_materiales',
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
                    'text' => 'Lista de Herramientas Materiales',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-GT-01-FO-08',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 01',
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
                    'id' => 'nombre_elabora',
                    'label' => 'Nombre de quien elabora',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_elabora',
                    'label' => 'Firma de quien elabora',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO08_ListaHerramientasMateriales/Elabora',
                ],

                [
                    'id' => 'nombre_revisa',
                    'label' => 'Nombre de quien revisa',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_revisa',
                    'label' => 'Firma de quien revisa',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO08_ListaHerramientasMateriales/Revisa',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado de la herramienta',
                ],

                [
                    'id' => 'tabla_herramientas_materiales',
                    'label' => 'Tabla de Herramientas Materiales',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'N° de Item',
                        'Descripción (Nombre de la herramienta)',
                        'Marca',
                        'N° de serie',
                        'N° de piezas',
                        'Estado',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_item',
                            'label' => 'N° de Item',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'descripcion_herramienta',
                            'label' => 'Descripción (Nombre de la herramienta)',
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
                            'id' => 'numero_serie',
                            'label' => 'N° de serie',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'numero_piezas',
                            'label' => 'N° de piezas',
                            'type' => 'number',
                            'required' => true,
                        ],
                        [
                            'id' => 'estado',
                            'label' => 'Estado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
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