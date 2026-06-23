<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_PGI_TA_04_FO_02_Checklist_de_Inspeccion_de_Lavaojos_de_Emergencia implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pgi_ta_04_fo_02_checklist_de_inspeccion_de_lavaojos_de_emergencia';
    }

    public static function title(): string
    {
        return 'SGI-PGI-TA-04-FO-02 Checklist de Inspección de Lavaojos de Emergencia';
    }

    public static function payload(): array
    {
        $siNoOptions = [
            'Si',
            'No',
        ];

        $rowSchema = [
            [
                'id' => 'numero_lavaojos_emergencia',
                'label' => 'N° de Lavaojos de Emergencia',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 'ducha_estacion_limpia_equipada',
                'label' => '¿Ducha o Estación lavaojos limpia y equipada?',
                'type' => 'radio',
                'required' => true,
                'options' => $siNoOptions,
            ],
            [
                'id' => 'flujo_continuo',
                'label' => '¿Se garantiza flujo continuo?',
                'type' => 'radio',
                'required' => true,
                'options' => $siNoOptions,
            ],
            [
                'id' => 'debidamente_senalizado',
                'label' => '¿Se encuentra debidamente señalizada?',
                'type' => 'radio',
                'required' => true,
                'options' => $siNoOptions,
            ],
            [
                'id' => 'agua_limpia_acondicionada',
                'label' => '¿Agua limpia y acondicionada?',
                'type' => 'radio',
                'required' => true,
                'options' => $siNoOptions,
            ],
            [
                'id' => 'acceso_libre_obstaculos',
                'label' => '¿El acceso se encuentra libre de obstáculos?',
                'type' => 'radio',
                'required' => true,
                'options' => $siNoOptions,
            ],
            [
                'id' => 'observaciones',
                'label' => 'Observaciones',
                'type' => 'textarea',
                'required' => false,
            ],
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_inspeccion_de_lavaojos_de_emergencia',
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
                    'text' => 'Checklist de Inspección de Lavaojos de Emergencia',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-PGI-TA-04-FO-02',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión:',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión:',
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
                    'save_path' => 'forms/signatures/SGIPGITA04FO02_ChecklistInspeccionLavaojosEmergencia/Inspector',
                ],
                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de llenado',
                ],
                [
                    'id' => 'texto_criterios_lavaojos',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado del lavaojos de emergencia',
                ],
                [
                    'id' => 'tabla_lavaojos_emergencia',
                    'label' => 'Checklist de Inspección de Lavaojos de Emergencia',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'N° de Lavaojos de Emergencia',
                        '¿Ducha o Estación lavaojos limpia y equipada?',
                        '¿Se garantiza flujo continuo?',
                        '¿Se encuentra debidamente señalizado?',
                        '¿Agua limpia y acondicionada?',
                        '¿El acceso se encuentra libre de obstáculos?',
                        'Observaciones',
                    ],
                    'row_schema' => $rowSchema,
                ],
                [
                    'id' => 'observaciones_generales',
                    'label' => 'Observaciones generales',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ];
    }
}