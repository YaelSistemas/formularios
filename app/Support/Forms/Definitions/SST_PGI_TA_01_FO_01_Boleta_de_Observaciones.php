<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_PGI_TA_01_FO_01_Boleta_de_Observaciones implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pgi_ta_01_fo_01_boleta_de_observaciones';
    }

    public static function title(): string
    {
        return 'SST-PGI-TA-01-FO-01 Boleta de Observaciones';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'boleta_de_observaciones',
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
                    'text' => 'BOLETA DE OBSERVACIONES',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-PGI-TA-01-FO-01',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 06/06/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 00',
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
                    'id' => 'planta_area_trabajo',
                    'label' => 'Planta o Área de Trabajo',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'nombre_personal_observado',
                    'label' => 'Nombre del Personal Observado',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'tipo_observacion',
                    'label' => 'Tipo de Observación',
                    'type' => 'radio',
                    'required' => true,
                    'options' => [
                        'Acto Inseguro',
                        'Condición Peligrosa',
                        'Desviación',
                    ],
                ],

                [
                    'id' => 'descripcion_observacion',
                    'label' => 'Descripción de la Observación',
                    'type' => 'textarea',
                    'required' => true,
                ],

                [
                    'id' => 'falta_cometida',
                    'type' => 'separator',
                    'label' => 'Falta Cometida',
                ],

                [
                    'id' => 'evidencia_fotografica',
                    'label' => 'Evidencia Fotográfica',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'acciones_preventivas_correctivas',
                    'label' => 'Acciones Preventivas y Correctivas',
                    'type' => 'textarea',
                    'required' => true,
                ],

                [
                    'id' => 'nombre_reporta_observacion',
                    'label' => 'Nombre de quien Reporta Observación',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'firma_reporta_observacion',
                    'label' => 'Firma de quien Reporta Observación',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SSTPGITA01FO01_BoletaObservaciones/Reporta_Observacion',
                ],

                [
                    'id' => 'nombre_observado',
                    'label' => 'Nombre del Observado',
                    'type' => 'text',
                    'required' => false,
                ],

                [
                    'id' => 'firma_observado',
                    'label' => 'Firma del Observado',
                    'type' => 'signature',
                    'required' => false,
                    'save_path' => 'forms/signatures/SSTPGITA01FO01_BoletaObservaciones/Observado',
                ],
            ],
        ];
    }
}