<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_07_Checklist_Mantenimiento_Sistema_Electrico implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_07_checklist_mantenimiento_sistema_electrico';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-07 Checklist Mantenimiento Sistema Eléctrico';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Buenas',
            'Malas',
            'N/A',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_mantenimiento_sistema_electrico',
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
                    'text' => 'Vulcanización y Servicios Industriales, SA de CV',
                ],
                [
                    'id' => 'header_line_2',
                    'type' => 'static_text',
                    'text' => 'Sistema de Gestión Integral',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'Checklist Mantenimiento Sistema Eléctrico',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-07',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 00',
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
                    'save_path' => 'forms/signatures/SGIPOPLG0107_ChecklistMantenimientoSistemaElectrico/Responsable_Mantenimiento',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Considerar las siguientes condiciones de acuerdo al estado del sistema eléctrico.',
                ],
                [
                    'id' => 'descripcion',
                    'type' => 'static_text',
                    'text' => 'Descripción',
                ],

                [
                    'id' => 'tabla_sistema_electrico',
                    'label' => 'CONDICIONES ELÉCTRICAS',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Descripción',
                        'Resultado',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'subestacion_titulo',
                            'type' => 'static_text',
                            'text' => '1 Subestación',
                        ],
                        [
                            'id' => 'electrico_1_1_estado',
                            'label' => '1.1 Interruptores termomagnéticos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_1_1_observaciones',
                            'label' => 'Observaciones - Interruptores termomagnéticos',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_1_2_estado',
                            'label' => '1.2 Cableado y conexiones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_1_2_observaciones',
                            'label' => 'Observaciones - Cableado y conexiones',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_1_3_estado',
                            'label' => '1.3 Tablero eléctrico / pastillas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_1_3_observaciones',
                            'label' => 'Observaciones - Tablero eléctrico / pastillas',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'banco_capacitores_titulo',
                            'type' => 'static_text',
                            'text' => '2 Banco de Capacitores',
                        ],
                        [
                            'id' => 'electrico_2_1_estado',
                            'label' => '2.1 Capacitor',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_2_1_observaciones',
                            'label' => 'Observaciones - Capacitor',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_2_2_estado',
                            'label' => '2.2 Conexiones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_2_2_observaciones',
                            'label' => 'Observaciones - Conexiones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'transformador_titulo',
                            'type' => 'static_text',
                            'text' => '3 Transformador de 400 a 200 y 110 volt',
                        ],
                        [
                            'id' => 'electrico_3_1_estado',
                            'label' => '3.1 Barras de conexión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_3_1_observaciones',
                            'label' => 'Observaciones - Barras de conexión',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_3_2_estado',
                            'label' => '3.2 Cableado y conexiones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_3_2_observaciones',
                            'label' => 'Observaciones - Cableado y conexiones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'tableros_contactos_titulo',
                            'type' => 'static_text',
                            'text' => '4 Tableros de contactos',
                        ],
                        [
                            'id' => 'electrico_4_1_estado',
                            'label' => '4.1 Conectores de ploga a 440 y 220 V',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_4_1_observaciones',
                            'label' => 'Observaciones - Conectores de ploga a 440 y 220 V',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_4_2_estado',
                            'label' => '4.2 Contactos polarizados a 110 volts',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_4_2_observaciones',
                            'label' => 'Observaciones - Contactos polarizados a 110 volts',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_4_3_estado',
                            'label' => '4.3 Cableado y conexiones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_4_3_observaciones',
                            'label' => 'Observaciones - Cableado y conexiones',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'alumbrado_titulo',
                            'type' => 'static_text',
                            'text' => '5 Alumbrado',
                        ],
                        [
                            'id' => 'electrico_5_1_estado',
                            'label' => '5.1 Lámparas leds almacén',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_5_1_observaciones',
                            'label' => 'Observaciones - Lámparas leds almacén',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_5_2_estado',
                            'label' => '5.2 Reflectores almacén',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_5_2_observaciones',
                            'label' => 'Observaciones - Reflectores almacén',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_5_3_estado',
                            'label' => '5.3 Alumbrado vestidores y WC',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_5_3_observaciones',
                            'label' => 'Observaciones - Alumbrado vestidores y WC',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_5_4_estado',
                            'label' => '5.4 Hidroneumático y calentador',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_5_4_observaciones',
                            'label' => 'Observaciones - Hidroneumático y calentador',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'almacen_frios_titulo',
                            'type' => 'static_text',
                            'text' => '6 Almacén de fríos',
                        ],
                        [
                            'id' => 'electrico_6_1_estado',
                            'label' => '6.1 Alumbrado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_6_1_observaciones',
                            'label' => 'Observaciones - Alumbrado',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_6_2_estado',
                            'label' => '6.2 Aterrizaje de racks',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_6_2_observaciones',
                            'label' => 'Observaciones - Aterrizaje de racks',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'oficinas_administrativas_titulo',
                            'type' => 'static_text',
                            'text' => '7 Oficinas administrativas',
                        ],
                        [
                            'id' => 'electrico_7_1_estado',
                            'label' => '7.1 Alumbrado y contactos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_7_1_observaciones',
                            'label' => 'Observaciones - Alumbrado y contactos',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_7_2_estado',
                            'label' => '7.2 Red de datos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_7_2_observaciones',
                            'label' => 'Observaciones - Red de datos',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'electrico_8_estado',
                            'label' => '8 Alumbrado de patios',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_8_observaciones',
                            'label' => 'Observaciones - Alumbrado de patios',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'pararrayos_titulo',
                            'type' => 'static_text',
                            'text' => '9 Pararrayos',
                        ],
                        [
                            'id' => 'electrico_9_1_estado',
                            'label' => '9.1 Sistema de tierras y conexiones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_9_1_observaciones',
                            'label' => 'Observaciones - Sistema de tierras y conexiones',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                        [
                            'id' => 'electrico_9_2_estado',
                            'label' => '9.2 Acoplador y disparador electromagnético',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_9_2_observaciones',
                            'label' => 'Observaciones - Acoplador y disparador electromagnético',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'electrico_10_estado',
                            'label' => '10 Sistema de bombeo cisterna',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_10_observaciones',
                            'label' => 'Observaciones - Sistema de bombeo cisterna',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'electrico_11_estado',
                            'label' => '11 Sistema de video vigilancia',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_11_observaciones',
                            'label' => 'Observaciones - Sistema de video vigilancia',
                            'type' => 'textarea',
                            'required' => false,
                        ],

                        [
                            'id' => 'electrico_12_estado',
                            'label' => '12 Extractores ambientales',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $estadoOptions,
                        ],
                        [
                            'id' => 'electrico_12_observaciones',
                            'label' => 'Observaciones - Extractores ambientales',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],

                [
                    'id' => 'observaciones_generales',
                    'label' => 'Observaciones Generales',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ];
    }
}