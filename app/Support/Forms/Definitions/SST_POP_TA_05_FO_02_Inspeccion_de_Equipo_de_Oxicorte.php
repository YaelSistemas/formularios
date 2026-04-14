<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_05_fo_02_inspeccion_de_equipo_de_oxicorte';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-05-FO-02 Inspección de Equipo de Oxicorte';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'inspeccion_equipo_oxicorte',
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
                    'text' => 'SISTEMA DE GESTIÓN INTEGRAL',
                ],
                [
                    'id' => 'header_line_3',
                    'label' => 'Nombre del formato',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Inspección de Equipo de Oxicorte',
                ],
                [
                    'id' => 'header_line_4',
                    'label' => 'Código',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Código: SST-POP-TA-05-FO-02',
                ],
                [
                    'id' => 'header_line_5',
                    'label' => 'Fecha de emisión',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Fecha de Emisión: 27/03/2025',
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
                    'id' => 'guia_inspeccion_text',
                    'label' => 'Guía de inspección',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Guía de Inspección',
                ],
                [
                    'id' => 'imagen_equipo_oxicorte',
                    'label' => 'Imagen equipo de oxicorte',
                    'type' => 'fixed_image',
                    'required' => false,
                    'url' => '/images/forms/SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte/Imagen_Oxicorte.png',
                ],
                [
                    'id' => 'indicaciones_line_1',
                    'label' => 'Indicacion 1',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Marque según lo que aplique.',
                ],
                [
                    'id' => 'numero_identificacion_equipo_oxicorte',
                    'label' => 'Número de Identificación de Equipo de Oxicorte',
                    'type' => 'text',
                    'required' => true,
                ],

                ...self::buildChecklistFields(),

                [
                    'id' => 'verificar_jabonadura_text',
                    'label' => 'Texto jabonadura',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Verificar con Jabonadura Todas las Conexiones del Equipo',
                ],
                [
                    'id' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'nota_final',
                    'label' => 'Nota final',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'NOTA: SI EL EQUIPO TIENE DEFICIENCIAS, SUSPENDER SU USO DE INMEDIATO.',
                ],
            ],
        ];
    }

    private static function buildChecklistFields(): array
    {
        $items = [
            'carro_porta_cilindros_cadena' => '1. Carro Porta Cilindros con Cadena',
            'estado_fisico_cilindros' => '2. Estado Físico de los Cilindros',
            'regulador_oxigeno' => '3. Regulador de Oxígeno',
            'manometro_alta_presion_oxigeno' => '4. Manómetro de Alta Presión, Contenido',
            'manometro_baja_presion_oxigeno' => '5. Manómetro de Baja Presión, Trabajo',
            'valvula_check_regulador_oxigeno' => '6. Válvula Check Regulador de Oxígeno',
            'regulador_acetileno' => '7. Regulador de Acetileno',
            'manometro_alta_presion_acetileno' => '8. Manómetro de Alta Presión, Contenido',
            'manometro_baja_presion_acetileno' => '9. Manómetro de Baja Presión, Trabajo',
            'valvula_check_regulador_acetileno' => '10. Válvula Check Regulador de Acetileno',
            'manguera_oxigeno' => '11. Manguera de Oxígeno',
            'valvula_check_maneral_oxigeno' => '12. Válvula Check Maneral de Oxígeno',
            'manguera_acetileno' => '13. Manguera de Acetileno',
            'valvula_check_maneral_acetileno' => '14. Válvula Check Maneral de Acetileno',
            'abrazaderas' => '15. Abrazaderas',
            'maneral_mezclador_gases' => '16. Maneral Mezclador de Gases',
            'llave_dosificadora_oxigeno' => '17. Llave Dosificadora de Oxígeno',
            'llave_dosificadora_acetileno' => '18. Llave Dosificadora de Acetileno',
            'boquilla_corte_soldadura' => '19. Boquilla de Corte o Soldadura',
            'tuercas_roscadas_union_empaques' => '20. Tuercas Roscadas de Unión y Empaques',
            'limpia_boquillas' => '21. Limpia Boquillas',
            'chispero' => '22. Chispero',
            'llave_cuadro_acetileno' => '23. Llave de Cuadro de Acetileno',
            'extintor_cercano_area_trabajo' => '24. Extintor Cercano al Área de Trabajo',
        ];

        $fields = [];

        foreach ($items as $key => $label) {
            $fields[] = [
                'id' => "{$key}_estado",
                'label' => $label,
                'type' => 'radio',
                'required' => true,
                'options' => ['Bien', 'Mal'],
            ];
        }

        return $fields;
    }
}