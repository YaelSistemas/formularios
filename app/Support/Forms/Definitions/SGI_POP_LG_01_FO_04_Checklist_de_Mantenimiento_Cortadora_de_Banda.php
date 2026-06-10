<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_FO_04_Checklist_de_Mantenimiento_Cortadora_de_Banda implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_fo_04_checklist_de_mantenimiento_cortadora_de_banda';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-FO-04 Checklist de Mantenimiento Cortadora de Banda';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Buenas',
            'Malas',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_mantenimiento_cortadora_de_banda',
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
                    'text' => 'Checklist de Mantenimiento de Cortadora de Banda',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-FO-04',
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
                    'id' => 'nombre_responsable_mantenimiento',
                    'label' => 'Nombre del Responsable de Mantenimiento',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_responsable_mantenimiento',
                    'label' => 'Firma del Responsable de Mantenimiento',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPLG01FO04_ChecklistMantenimientoCortadoraBanda/Responsable_Mantenimiento',
                ],

                [
                    'id' => 'separacion_indicaciones_llenado',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de LLenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones de la cortadora de banda',
                ],

                [
                    'id' => 'titulo_condiciones_mecanicas',
                    'type' => 'static_text',
                    'text' => 'CONDICIONES MÉCANICAS',
                ],

                [
                    'id' => 'subtitulo_moto_reductores',
                    'type' => 'static_text',
                    'text' => '1. Moto-reductores',
                ],

                ...self::criterio('mecanicas_1_1_niveles_aceite', '1.1. Niveles de aceite', $estadoOptions),
                ...self::criterio('mecanicas_1_2_alineamiento_cadenas', '1.2. Alineamiento de cadenas', $estadoOptions),
                ...self::criterio('mecanicas_1_3_baleros_motores', '1.3. Baleros de los motores', $estadoOptions),
                ...self::criterio('mecanicas_1_4_chumaceras', '1.4. Chumaceras', $estadoOptions),
                ...self::criterio('mecanicas_1_5_tren_engranaje', '1.5. Tren de engranaje', $estadoOptions),

                [
                    'id' => 'subtitulo_mesa_corte',
                    'type' => 'static_text',
                    'text' => '2. Mesa de Corte',
                ],

                ...self::criterio('mecanicas_2_1_balero_chumaceras', '2.1. Balero de chumaceras', $estadoOptions),
                ...self::criterio('mecanicas_2_2_tornilleria', '2.2. Tornillería', $estadoOptions),

                [
                    'id' => 'titulo_condiciones_electricas',
                    'type' => 'static_text',
                    'text' => 'CONDICIONES ELECTRICAS',
                ],

                [
                    'id' => 'subtitulo_tablero_control',
                    'type' => 'static_text',
                    'text' => '1. Tablero de Control',
                ],

                ...self::criterio('electricas_1_1_variadores_velocidad', '1.1. Variadores de velocidad', $estadoOptions),
                ...self::criterio('electricas_1_2_contactores', '1.2. Contactores', $estadoOptions),
                ...self::criterio('electricas_1_3_conexiones', '1.3. Conexiones', $estadoOptions),
                ...self::criterio('electricas_1_4_selector_motor', '1.4. Selector de motor', $estadoOptions),

                [
                    'id' => 'subtitulo_motores',
                    'type' => 'static_text',
                    'text' => '2. Motores',
                ],

                ...self::criterio('electricas_2_1_conexiones', '2.1. Conexiones', $estadoOptions),
                ...self::criterio('electricas_2_2_aislantes', '2.2. Aislantes', $estadoOptions),

                [
                    'id' => 'observaciones_generales',
                    'label' => 'Observaciones Generales',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ];
    }

    private static function criterio(string $id, string $label, array $estadoOptions): array
    {
        return [
            [
                'id' => $id . '_estado',
                'label' => $label,
                'type' => 'radio',
                'required' => true,
                'options' => $estadoOptions,
            ],
            [
                'id' => $id . '_observaciones',
                'label' => 'Observaciones - ' . $label,
                'type' => 'textarea',
                'required' => false,
            ],
        ];
    }
}