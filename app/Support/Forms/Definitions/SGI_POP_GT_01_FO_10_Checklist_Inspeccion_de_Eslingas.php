<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_GT_01_FO_10_Checklist_Inspeccion_de_Eslingas implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_gt_01_fo_10_checklist_inspeccion_de_eslingas';
    }

    public static function title(): string
    {
        return 'SGI-POP-GT-01-FO-10 Checklist Inspección de Eslingas';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            '( ✓ ) Buenas condiciones',
            '( X ) En malas condiciones',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_inspeccion_de_eslingas',
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
                    'text' => 'Checklist Inspección de Eslingas',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-GT-01-FO-10',
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
                    'id' => 'nombre_colaborador_inspecciona',
                    'label' => 'Nombre del Colaborador que Inspecciona',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_colaborador_inspecciona',
                    'label' => 'Firma del Colaborador que Inspecciona',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO10_ChecklistInspeccionEslingas/Inspecciona',
                ],

                [
                    'id' => 'nombre_supervisor',
                    'label' => 'Nombre del Supervisor',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_supervisor',
                    'label' => 'Firma del Supervisor',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPGT01FO10_ChecklistInspeccionEslingas/Supervisor',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Responde según aplique al estado físico del equipo',
                ],

                [
                    'id' => 'tabla_eslingas',
                    'label' => 'Tabla de Inspección de Eslingas',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'N° de serie de eslinga',
                        'Se almacenan eslingas en ambiente seco, ventilado y protegido contra lluvia o medios agresivos que pueda dañarla',
                        'Se observan roturas de monofilamentos, hilos cortados o gastados sobre todo en los cantos, (ojo - cuerpo)',
                        'Etiquetas son legibles y contienen la información de carga máxima, código de trazabilidad, largo y ancho',
                        'Presentan desgaste y/o abrasión',
                        'Se han realizado nudos en las eslingas',
                        'Los accesorios que usan las eslingas en sus ojos (grilletes, ganchos, cáncamos, etc.)',
                        'Presentan quemaduras por soldadura, exposición al sol u otro factor similar',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_serie_eslinga',
                            'label' => 'N° de serie de eslinga',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'almacenamiento_estado',
                            'label' => 'Se almacenan eslingas en ambiente seco, ventilado y protegido contra lluvia o medios agresivos que pueda dañarla',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'roturas_monofilamentos_estado',
                            'label' => 'Se observan roturas de monofilamentos, hilos cortados o gastados sobre todo en los cantos, (ojo - cuerpo)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'etiquetas_legibles_estado',
                            'label' => 'Etiquetas son legibles y contienen la información de carga máxima, código de trazabilidad, largo y ancho',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'desgaste_abrasion_estado',
                            'label' => 'Presentan desgaste y/o abrasión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'nudos_estado',
                            'label' => 'Se han realizado nudos en las eslingas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'accesorios_ojos_estado',
                            'label' => 'Los accesorios que usan las eslingas en sus ojos (grilletes, ganchos, cáncamos, etc.)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'quemaduras_estado',
                            'label' => 'Presentan quemaduras por soldadura, exposición al sol u otro factor similar',
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