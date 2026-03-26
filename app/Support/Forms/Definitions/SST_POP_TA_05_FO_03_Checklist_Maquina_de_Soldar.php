<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_05_fo_03_checklist_maquina_de_soldar';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-05-FO-03 Checklist Máquina de Soldar';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'checklist_maquina_de_soldar',
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
                    'text' => 'Checklist Máquina de Soldar',
                ],
                [
                    'id' => 'header_line_4',
                    'label' => 'Código',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Codigo: SST-POP-TA-05-FO-03',
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
                    'text' => 'Número de Revisión: 02',
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
                    'id' => 'nombre_supervisor',
                    'label' => 'Nombre del supervisor',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_supervisor',
                    'label' => 'Firma del supervisor',
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
                    'id' => 'imagen_maquina_soldar',
                    'label' => 'Imagen máquina de soldar',
                    'type' => 'fixed_image',
                    'required' => false,
                    'url' => '/images/forms/SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar/Imagen_Maquina_Soldar.png',
                ],
                [
                    'id' => 'indicaciones_line_1',
                    'label' => 'Indicacion 1',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Marque según lo que aplique.',
                ],

                [
                    'id' => 'numero_serie_maquina',
                    'label' => 'No. de Serie',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'tipo_modelo_maquina',
                    'label' => 'Tipo y modelo de maquina',
                    'type' => 'text',
                    'required' => true,
                ],

                ...self::buildChecklistFields(),
            ],
        ];
    }

    private static function buildChecklistFields(): array
    {
        $items = [
            'voltimetro' => '1. Voltímetro',
            'interruptor_encendido_apagado' => '2. Interruptor de Encendido y Apagado',
            'control_inductancia' => '3. Control de Inductancia',
            'selector_rotativo_proceso' => '4. Selector Rotativo de Proceso',
            'amperimetro' => '5. Amperímetro',
            'control_ajuste_amperaje_voltaje' => '6. Control de Ajuste de Amperaje/Voltaje',
            'sector_control_amperaje_voltaje' => '7. Sector de Control de Amperaje/Voltaje',
            'carcasa_metalica_proteccion' => '8. Carcasa Metálica de Protección',
            'pantalla' => '9. Pantalla',
            'dispositivo_bloqueo' => '10. Dispositivo de Bloqueo',
            'cable_tierra' => '11. Cable a Tierra',
            'pinza_cable_tierra' => '12. Pinza de Cable a Tierra',
            'cable_porta_electrodos' => '13. Cable Porta Electrodos',
            'pinza_porta_electrodos' => '14. Pinza Porta Electrodos',
            'cables_alimentacion_aislados' => '15. Cables de Alimentación Aislados',
            'aislamiento_humedad' => '16. Aislamiento de Humedad',
            'limpieza' => '17. Limpieza',
        ];

        $fields = [];

        foreach ($items as $key => $label) {
            $fields[] = [
                'id' => "{$key}_estado",
                'label' => $label,
                'type' => 'radio',
                'required' => true,
                'options' => ['(B) Bueno', '(M) Malo', '(NA) No Aplica'],
            ];

            $fields[] = [
                'id' => "{$key}_observaciones",
                'label' => 'Observaciones',
                'type' => 'textarea',
                'required' => true,
            ];
        }

        return $fields;
    }
}