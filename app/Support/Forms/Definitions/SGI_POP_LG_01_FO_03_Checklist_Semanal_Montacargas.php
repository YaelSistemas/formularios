<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_FO_03_Checklist_Semanal_Montacargas implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_fo_03_checklist_semanal_montacargas';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-FO-03 Checklist Semanal Montacargas';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Buen Estado',
            'Mal Estado',
        ];

        $nivelOptions = [
            'A Nivel',
            'Le Falta',
        ];
        
        $fugasOptions = [
            'Sin Fuga',
            'Fuga Menor',
            'Fuga Mayor',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_semanal_montacargas',
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
                    'text' => 'Checklist Semanal Montacargas',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-FO-03',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
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
                    'label' => 'Nombre del Inspector',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_inspector',
                    'label' => 'Firma del Inspector',
                    'type' => 'signature',
                    'required' => true,
                    'save_path' => 'forms/signatures/SGIPOPLG01FO03_ChecklistSemanalMontacargas/Inspector',
                ],

                [
                    'id' => 'separacion_indicaciones_llenado',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de LLenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado del montacargas',
                ],

                [
                    'id' => 'equipo',
                    'label' => 'Equipo',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'horometro',
                    'label' => 'Horómetro',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'fecha_ultimo_mantenimiento',
                    'label' => 'Fecha de ultimo Mantenimiento',
                    'type' => 'date',
                    'required' => true,
                ],

                [
                    'id' => 'titulo_condicion_equipo',
                    'type' => 'static_text',
                    'text' => 'CONDICIÓN DEL EQUIPO',
                ],

                [
                    'id' => 'subtitulo_llantas',
                    'type' => 'static_text',
                    'text' => 'LLANTAS (REVESTIMIENTO Y PRESION)',
                ],
                ...self::criterio('condicion_equipo_llantas', 'No presenta fisuras, grietas, cortaduras, chipotes, ni desgaste excesivo.', $estadoOptions),

                [
                    'id' => 'subtitulo_carroceria',
                    'type' => 'static_text',
                    'text' => 'CARROCERIA',
                ],
                ...self::criterio('condicion_equipo_carroceria', 'Libre de golpes, fisuras o corrosión. No tiene partes sueltas ni deformaciones que afecten la estructura.', $estadoOptions),

                [
                    'id' => 'subtitulo_volante',
                    'type' => 'static_text',
                    'text' => 'VOLANTE',
                ],
                ...self::criterio('condicion_equipo_volante', 'Gira correctamente sin dificultad. No presenta daños visibles.', $estadoOptions),

                [
                    'id' => 'subtitulo_asiento',
                    'type' => 'static_text',
                    'text' => 'ASIENTO',
                ],
                ...self::criterio('condicion_equipo_asiento', 'En buen estado, sin roturas ni desgaste excesivo. Ajuste funcional y cinturón de seguridad operativo.', $estadoOptions),

                [
                    'id' => 'subtitulo_relojes_indicadores',
                    'type' => 'static_text',
                    'text' => 'RELOJES INDICADORES',
                ],
                ...self::criterio('condicion_equipo_relojes_indicadores', 'Todos los indicadores funcionan correctamente (combustible, temperatura, presión de aceite, horómetro, etc.).', $estadoOptions),

                [
                    'id' => 'subtitulo_freno',
                    'type' => 'static_text',
                    'text' => 'FRENO',
                ],
                ...self::criterio('condicion_equipo_freno', 'Responde de manera efectiva al aplicar presión. Sin ruidos extraños ni recorrido excesivo del pedal.', $estadoOptions),

                [
                    'id' => 'subtitulo_freno_emergencia',
                    'type' => 'static_text',
                    'text' => 'FRENO DE EMERGENCIA',
                ],
                ...self::criterio('condicion_equipo_freno_emergencia', 'Se activa correctamente y mantiene el montacargas fijo.', $estadoOptions),

                [
                    'id' => 'subtitulo_carro_portahorquillas',
                    'type' => 'static_text',
                    'text' => 'CARRO PORTAHORQUILLAS',
                ],
                ...self::criterio('condicion_equipo_carro_portahorquillas', 'Sin deformaciones ni daños estructurales. Movimiento fluido y seguro.', $estadoOptions),

                [
                    'id' => 'subtitulo_inclinacion_horquillas',
                    'type' => 'static_text',
                    'text' => 'INCLINACION DE HORQUILLAS',
                ],
                ...self::criterio('condicion_equipo_inclinacion_horquillas', 'Se inclina sin obstrucciones ni movimientos bruscos.', $estadoOptions),

                [
                    'id' => 'subtitulo_subir_bajar_horquillas',
                    'type' => 'static_text',
                    'text' => 'SUBIR Y BAJAR HORQUILLAS',
                ],
                ...self::criterio('condicion_equipo_subir_bajar_horquillas', 'Movimiento fluido y sin bloques.', $estadoOptions),

                [
                    'id' => 'subtitulo_estado_seguro_horquillas',
                    'type' => 'static_text',
                    'text' => 'ESTADO Y SEGURO DE HORQUILLAS',
                ],
                ...self::criterio('condicion_equipo_estado_seguro_horquillas', 'No presenta fisuras, grietas, cortaduras, chipotes, ni desgaste excesivo.', $estadoOptions),

                [
                    'id' => 'subtitulo_etiqueta_carga_maxima',
                    'type' => 'static_text',
                    'text' => 'ETIQUETA DE CARGA MÁXIMA',
                ],
                ...self::criterio('condicion_equipo_etiqueta_carga_maxima', 'Visible y en buenas condiciones. No esta borrosa ni deteriorada.', $estadoOptions),

                [
                    'id' => 'subtitulo_senalamiento_palancas',
                    'type' => 'static_text',
                    'text' => 'SEÑALAMIENTO EN PALANCAS',
                ],
                ...self::criterio('condicion_equipo_senalamiento_palancas', 'No estas desgastadas.', $estadoOptions),

                [
                    'id' => 'subtitulo_etiqueta_tipo_combustible',
                    'type' => 'static_text',
                    'text' => 'ETIQUETA DE TIPO COMBUSTIBLE',
                ],
                ...self::criterio('condicion_equipo_etiqueta_tipo_combustible', 'Visible y en buen estado.', $estadoOptions),

                [
                    'id' => 'subtitulo_manual_procedimiento',
                    'type' => 'static_text',
                    'text' => 'MANUAL DE PROCEDIMIENTO',
                ],
                ...self::criterio('condicion_equipo_manual_procedimiento', 'Disponible en la unidad.', $estadoOptions),

                [
                    'id' => 'titulo_sistema_seguridad',
                    'type' => 'static_text',
                    'text' => 'SISTEMA DE SEGURIDAD',
                ],

                [
                    'id' => 'subtitulo_torreta',
                    'type' => 'static_text',
                    'text' => 'TORRETA',
                ],
                ...self::criterio('sistema_seguridad_torreta', 'Funciona correctamente.', $estadoOptions),

                [
                    'id' => 'subtitulo_espejos',
                    'type' => 'static_text',
                    'text' => 'ESPEJOS',
                ],
                ...self::criterio('sistema_seguridad_espejos', 'No están rotos ni sucios. Proporcionan una visión clara del entorno.', $estadoOptions),

                [
                    'id' => 'subtitulo_claxon',
                    'type' => 'static_text',
                    'text' => 'CLAXÓN',
                ],
                ...self::criterio('sistema_seguridad_claxon', 'Funciona adecuadamente y titne una buena potencia sonora.', $estadoOptions),

                [
                    'id' => 'subtitulo_cinturon_seguridad',
                    'type' => 'static_text',
                    'text' => 'CINTURON DE SEGURIDAD',
                ],
                ...self::criterio('sistema_seguridad_cinturon_seguridad', 'Buen estado de las correas y seguros, se ajusta correctamente.', $estadoOptions),

                [
                    'id' => 'subtitulo_extintor',
                    'type' => 'static_text',
                    'text' => 'EXTINTOR',
                ],
                ...self::criterio('sistema_seguridad_extintor', 'Presión adecuada, sin caducar y en sitio correcto.', $estadoOptions),

                [
                    'id' => 'subtitulo_alarma_reversa',
                    'type' => 'static_text',
                    'text' => 'ALARAMA DE REVERSA',
                ],
                ...self::criterio('sistema_seguridad_alarma_reversa', 'Emite un sonido fuerte y claro al retroceder.', $estadoOptions),

                [
                    'id' => 'subtitulo_faros_delanteros',
                    'type' => 'static_text',
                    'text' => 'FAROS DELANTEROS',
                ],
                ...self::criterio('sistema_seguridad_faros_delanteros', 'Funciona correctamente y brindan iluminación adecuada.', $estadoOptions),

                [
                    'id' => 'subtitulo_faros_cuartos_traseros',
                    'type' => 'static_text',
                    'text' => 'FAROS Y CUARTOS TRASEROS',
                ],
                ...self::criterio('sistema_seguridad_faros_cuartos_traseros', 'Funcionan correctamente y están libres de daños.', $estadoOptions),

                [
                    'id' => 'subtitulo_cadena',
                    'type' => 'static_text',
                    'text' => 'CADENA',
                ],
                ...self::criterio('sistema_seguridad_cadena', 'en buen estado, sin desgaste excesivo ni oxidación.', $estadoOptions),

                [
                    'id' => 'subtitulo_etiquetas_izaje_velocidad',
                    'type' => 'static_text',
                    'text' => 'ETIQUETA DE PUNTO DE IZAJE, INDICADOR DE VELOCIDAD MÁXIMA',
                ],
                ...self::criterio('sistema_seguridad_etiquetas_izaje_velocidad', 'Las etiquetas estan en buen estado y son legibles.', $estadoOptions),

                [
                    'id' => 'titulo_sistema_refrigeracion',
                    'type' => 'static_text',
                    'text' => 'SISTEMA DE REFRIGERACIÓN',
                ],

                [
                    'id' => 'subtitulo_anticongelante',
                    'type' => 'static_text',
                    'text' => 'ANTICONGELANTE',
                ],
                ...self::criterio('sistema_refrigeracion_anticongelante', 'Nivel adecuado y sin fugas.', $nivelOptions),

                [
                    'id' => 'subtitulo_sistema_hidraulico',
                    'type' => 'static_text',
                    'text' => 'SISTEMA HIDRAULICO (PISTÓN/MANGUERAS)',
                ],
                ...self::criterio('sistema_refrigeracion_sistema_hidraulico', 'Sin fugas ni daños visibles en pistón o mangueras. Revisión de conexiones y limpieza.', $nivelOptions),

                [
                    'id' => 'subtitulo_aceite_transmision_automatica',
                    'type' => 'static_text',
                    'text' => 'ACEITE DE TRANSMISIÓN AUTOMATICA',
                ],
                ...self::criterio('sistema_refrigeracion_aceite_transmision_automatica', 'Nivel adecuado y sin fugas. No presenta olores extraños.', $nivelOptions),

                [
                    'id' => 'subtitulo_aceite_motor',
                    'type' => 'static_text',
                    'text' => 'ACEITE DE MOTOR',
                ],
                ...self::criterio('sistema_refrigeracion_aceite_motor', 'Nivel adecuado y sin fugas. Sin presencia de residuos o contaminación.', $nivelOptions),

                [
                    'id' => 'subtitulo_fugas',
                    'type' => 'static_text',
                    'text' => 'FUGAS DE ACEITE/FLUIDO/COMBUSTIBLE/AGUA',
                ],
                ...self::criterio('sistema_refrigeracion_fugas', 'No se detectan fugas en el sistema.', $fugasOptions),

                [
                    'id' => 'subtitulo_mangueras',
                    'type' => 'static_text',
                    'text' => 'MANGUERAS',
                ],
                ...self::criterio('sistema_refrigeracion_mangueras', 'Sin rayones, golpes, grietas ni fugas. Conexiones firmes y en buen estado.', $estadoOptions),

                [
                    'id' => 'subtitulo_tapon_combustibles',
                    'type' => 'static_text',
                    'text' => 'TAPÓN DE COMBUSTIBLE',
                ],
                ...self::criterio('sistema_refrigeracion_tapon_combustibles', 'Correctamente colocado y en bunas condiciones (Sin trapos u objetos improvisados).', $estadoOptions),

                [
                    'id' => 'notas',
                    'label' => 'Notas',
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