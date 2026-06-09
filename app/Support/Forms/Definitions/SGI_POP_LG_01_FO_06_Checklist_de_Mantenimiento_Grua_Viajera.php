<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_FO_06_Checklist_de_Mantenimiento_Grua_Viajera implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_fo_06_checklist_de_mantenimiento_grua_viajera';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-FO-06 Checklist de Mantenimiento Grúa Viajera';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Buenas condiciones',
            'Malas condiciones',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_de_mantenimiento_grua_viajera',
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
                    'text' => 'Checklist de Mantenimiento Grúa Viajera',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-FO-06',
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
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado de la grúa viajera',
                ],

                [
                    'id' => 'titulo_condiciones_mecanicas',
                    'type' => 'static_text',
                    'text' => 'CONDICIONES MECÁNICAS',
                ],

                [
                    'id' => 'subtitulo_transmision_longitudinal',
                    'type' => 'static_text',
                    'text' => '1. Transmisión Longitudinal',
                ],

                ...self::criterio('mecanicas_1_1_rodajas', '1.1. Rodajas', $estadoOptions),
                ...self::criterio('mecanicas_1_2_baleros', '1.2. Baleros', $estadoOptions),
                ...self::criterio('mecanicas_1_3_tren_engrane', '1.3. Tren de engrane', $estadoOptions),
                ...self::criterio('mecanicas_1_4_vias_desplazamiento', '1.4. Vías de desplazamiento', $estadoOptions),
                ...self::criterio('mecanicas_1_5_tornilleria', '1.5. Tornillería', $estadoOptions),

                [
                    'id' => 'subtitulo_transmision_transversal',
                    'type' => 'static_text',
                    'text' => '2. Transmisión Transversal',
                ],

                ...self::criterio('mecanicas_2_1_rodajas', '2.1. Rodajas', $estadoOptions),
                ...self::criterio('mecanicas_2_2_baleros', '2.2. Baleros', $estadoOptions),
                ...self::criterio('mecanicas_2_3_tren_engrane', '2.3. Tren de engrane', $estadoOptions),
                ...self::criterio('mecanicas_2_4_vias_desplazamiento', '2.4. Vías de desplazamiento', $estadoOptions),
                ...self::criterio('mecanicas_2_5_tornilleria', '2.5. Tornillería', $estadoOptions),

                ...self::criterio('mecanicas_3_niveles_aceite_motorreductores', '3. Niveles aceite de motorreductores', $estadoOptions),

                [
                    'id' => 'subtitulo_embrague_freno',
                    'type' => 'static_text',
                    'text' => '4. Embrague y Freno',
                ],

                ...self::criterio('mecanicas_4_1_discos', '4.1. Discos', $estadoOptions),
                ...self::criterio('mecanicas_4_2_pastas', '4.2. Pastas', $estadoOptions),
                ...self::criterio('mecanicas_4_3_tornilleria', '4.3. Tornillería', $estadoOptions),

                [
                    'id' => 'subtitulo_tambor_transmision',
                    'type' => 'static_text',
                    'text' => '5. Tambor de Transmisión',
                ],

                ...self::criterio('mecanicas_5_1_cable_acero', '5.1. Cable de acero', $estadoOptions),
                ...self::criterio('mecanicas_5_2_ganchos', '5.2. Ganchos 22.5 y 8.5 Ton.', $estadoOptions),

                [
                    'id' => 'titulo_condiciones_electricas',
                    'type' => 'static_text',
                    'text' => 'CONDICIONES ELÉCTRICAS',
                ],

                ...self::criterio('electricas_1_conexiones_aislantes_motores', '1. Conexiones y aislantes de motores', $estadoOptions),

                [
                    'id' => 'subtitulo_sensores_paro',
                    'type' => 'static_text',
                    'text' => '2. Sensores de paro',
                ],

                ...self::criterio('electricas_2_1_longitudinal', '2.1. Longitudinal', $estadoOptions),
                ...self::criterio('electricas_2_2_transversal', '2.2. Transversal', $estadoOptions),
                ...self::criterio('electricas_2_3_ganchos_subida', '2.3. Ganchos de subida', $estadoOptions),

                ...self::criterio('electricas_3_variadores_velocidad', '3. Variadores de Velocidad', $estadoOptions),
                ...self::criterio('electricas_4_tableros_electricos', '4. Tableros Eléctricos', $estadoOptions),
                ...self::criterio('electricas_5_botonera', '5. Botonera', $estadoOptions),
                ...self::criterio('electricas_6_toma_corriente', '6. Toma Corriente', $estadoOptions),

                [
                    'id' => 'observaciones_generales',
                    'label' => 'Observaciones Generales',
                    'type' => 'textarea',
                    'required' => false,
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
                    'save_path' => 'forms/signatures/SGIPOPLG01FO06_ChecklistMantenimientoGruaViajera/Responsable_Mantenimiento',
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