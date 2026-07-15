<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_FO_01_Checklist_de_Prensas_para_Pasamanos implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_fo_01_checklist_de_prensas_para_pasamanos';
    }

    public static function title(): string
    {
        return 'SGI-POP-FO-01 Checklist de Prensas para Pasamanos';
    }

    public static function payload(): array
    {
        $accionOptions = [
            'Inspección',
            'Mantenimiento',
        ];

        $tipoPrensaOptions = [
            'H49',
            'T79',
            'E99',
            '1179',
            '1379',
            '1477',
            '1699',
            '1879',
        ];

        $voltajeOptions = [
            '220 VOLTS',
            '110 VOLTS',
        ];

        $estadoOptions = [
            '( C ) Cumple',
            '( NC ) No Cumple',
            '( F ) Faltante',
            '( MM ) Mantenimiento',
        ];

        $items = [
            '1. Caja de control',
            '2. Platina superior',
            '3. Platina inferior',
            '4. Termómetro de aguja platina superior',
            '5. Termómetro de aguja platina inferior',
            '6. Cable maestro platina superior',
            '7. Cable maestro platina inferior',
            '8. Extensión de alimentación principal',
            '9. Termopar de caja de control',
            '10. Tornillos y/o tuercas',
            '11. Riel interno (molde)',
            '12. Tornillería general',
        ];

        $rowSchema = [
            [
                'id' => 'imagen_prensa_pasamanos',
                'type' => 'fixed_image',
                'url' => '/images/forms/SGI_POP_FO_01_Checklist_de_Prensas_para_Pasamanos/prensa_pasamanos.jpg',
            ],
            [
                'id' => 'accion_realizar',
                'label' => 'Especificar con una ( X ) la acción a realizar',
                'type' => 'radio',
                'required' => true,
                'options' => $accionOptions,
            ],
            [
                'id' => 'subtitulo_criterios',
                'type' => 'static_text',
                'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones de la prensa',
            ],
            [
                'id' => 'codigo_identificacion_prensa',
                'label' => 'Código de identificación de la prensa',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'tipo_prensa',
                'label' => 'Tipo de prensa',
                'type' => 'radio',
                'required' => true,
                'options' => $tipoPrensaOptions,
            ],
            [
                'id' => 'tipo_voltaje',
                'label' => 'Tipo de voltaje',
                'type' => 'radio',
                'required' => true,
                'options' => $voltajeOptions,
            ],
            [
                'id' => 'subtitulo_estado',
                'type' => 'static_text',
                'text' => 'Marque según corresponda el estado:',
            ],
        ];

        foreach ($items as $index => $item) {
            $num = $index + 1;

            $rowSchema[] = [
                'id' => 'estado_item_' . $num,
                'label' => $item,
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ];

            $rowSchema[] = [
                'id' => 'observaciones_item_' . $num,
                'label' => 'Observaciones',
                'type' => 'textarea',
                'required' => false,
            ];
        }

        $rowSchema[] = [
            'id' => 'notas',
            'label' => 'Notas',
            'type' => 'textarea',
            'required' => false,
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_prensas_para_pasamanos',
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
                    'text' => 'Checklist de Prensas para Pasamanos',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-FO-01',
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
                    'id' => 'nombre_inspecciona_mantenimiento',
                    'label' => 'Nombre de quien Inspecciona o da Mantenimiento',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspecciona_mantenimiento',
                    'label' => 'Firma de quien Inspecciona o da Mantenimiento',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPFO01_ChecklistPrensasPasamanos/Inspecciona_Mantenimiento',
                ],
                [
                    'id' => 'nombre_responsable_area_pasamanos',
                    'label' => 'Nombre del Responsable del Área de Pasamanos',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_responsable_area_pasamanos',
                    'label' => 'Firma del Responsable del Área de Pasamanos',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPFO01_ChecklistPrensasPasamanos/Responsable_Area_Pasamanos',
                ],
                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'tabla_prensas_pasamanos',
                    'label' => 'Checklist de Prensas para Pasamanos',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Acción a realizar',
                        'Código de identificación de la prensa',
                        'Tipo de prensa',
                        'Tipo de voltaje',
                        'Estado',
                        'Observaciones',
                        'Notas',
                    ],
                    'row_schema' => $rowSchema,
                ],
            ],
        ];
    }
}
