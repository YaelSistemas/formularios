<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pop_ta_04_fo_04_checklist_linea_retractil_y_puntos_fijos';
    }

    public static function title(): string
    {
        return 'SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos';
    }

    public static function payload(): array
    {
        return [
            'meta' => [
                'layout' => 'checklist_linea_retractil_y_puntos_fijos',
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
                    'text' => 'SISTEMA DE GESTION INTEGRAL',
                ],
                [
                    'id' => 'header_line_3',
                    'type' => 'static_text',
                    'text' => 'Checklist Línea Retráctil y Puntos Fijos',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-POP-TA-04-FO-04',
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
                    'save_path' => 'forms/signatures/SSTPOPTA04FO04_CheckListLineaRetractilPuntosFijos/Inspector',
                ],

                [
                    'id' => 'indicaciones_toggle',
                    'type' => 'static_text',
                    'text' => 'Indicaciones de Llenado',
                ],
                [
                    'id' => 'indicacion_1',
                    'type' => 'static_text',
                    'text' => 'Este checklist deberá llenarse cada que se use el equipo y en caso de no usarse llenarse una vez al mes.',
                ],
                [
                    'id' => 'indicacion_2',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a las condiciones del equipo',
                ],
                [
                    'id' => 'criterios_titulo',
                    'type' => 'static_text',
                    'text' => 'Criterios a inspeccionar',
                ],

                [
                    'id' => 'tabla_linea_retractil',
                    'label' => 'Criterios a inspeccionar',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Imagen',
                        'N° de Identificación',
                        'Marca / Modelo del Arnés',
                        'Condiciones Generales',
                        '1. Mosquetón',
                        '2. Gancho de Seguridad de Cierre Automático',
                        '3. Conector de Punto Fijo / Punto de Anclaje Fijo',
                        'Acciones',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'imagen_linea',
                            'label' => 'Imagen',
                            'type' => 'fixed_image',
                            'required' => false,
                            'url' => '/images/forms/SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos/LineaRetractil.png',
                        ],
                        [
                            'id' => 'numero_identificacion',
                            'label' => 'N° de Identificación',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'marca_modelo',
                            'label' => 'Marca / Modelo del Arnés',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'condiciones_generales',
                            'label' => 'Condiciones Generales',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => 'Manija de Anclaje | Carcaza Termoplástica | Línea de Vida Acero Galvanizado o Textil | Activación de Sistema de Bloqueo',
                        ],
                        [
                            'id' => 'manija_anclaje',
                            'label' => 'Manija de Anclaje',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'carcaza_termoplastica',
                            'label' => 'Carcaza Termoplástica',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'linea_vida_acero_textil',
                            'label' => 'Línea de Vida Acero Galvanizado o Textil',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'activacion_sistema_bloqueo',
                            'label' => 'Activación de Sistema de Bloqueo',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],

                        [
                            'id' => 'mosqueton_titulo',
                            'label' => '1. MOSQUETÓN',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '1.1. Desgaste, Deformaciones | 1.2. Picaduras, Grietas | 1.3. Corrosión',
                        ],
                        [
                            'id' => 'mosqueton_1_1',
                            'label' => '1.1. Desgaste, Deformaciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'mosqueton_1_2',
                            'label' => '1.2. Picaduras, Grietas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'mosqueton_1_3',
                            'label' => '1.3. Corrosión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],

                        [
                            'id' => 'gancho_titulo',
                            'label' => '2. GANCHO DE SEGURIDAD DE CIERRE AUTOMATICO',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '2.1. Desgaste, Deformaciones | 2.2. Picadura, Grietas | 2.3. Ajuste Inadecuado o Incorrecto de los Cierres de Seguridad (Enganches) | 2.4. Corrosión',
                        ],
                        [
                            'id' => 'gancho_2_1',
                            'label' => '2.1. Desgaste, Deformaciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'gancho_2_2',
                            'label' => '2.2. Picadura, Grietas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'gancho_2_3',
                            'label' => '2.3. Ajuste Inadecuado o Incorrecto de los Cierres de Seguridad (Enganches)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],
                        [
                            'id' => 'gancho_2_4',
                            'label' => '2.4. Corrosión',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['(✓) Buen Estado', '(X) Mal Estado', '(NA) No Aplica'],
                        ],

                        [
                            'id' => 'conector_titulo',
                            'label' => '3. CONECTOR DE PUNTO FIJO/PUNTO DE ANCLAJE FIJO',
                            'type' => 'static_text',
                            'required' => false,
                            'text' => '3.1. Forro del Cable se Encuentra Desgastado | 3.2. Cuerpo de línea Presencia de Daño | 3.3. Costuras Rotas o Dañadas | 3.4. Argollas o Deformaciones | 3.5. Presencia de Aceites, Grasas o Químicos',
                        ],
                        [
                            'id' => 'conector_3_1',
                            'label' => '3.1. Forro del Cable se Encuentra Desgastado',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],
                        [
                            'id' => 'conector_3_2',
                            'label' => '3.2. Cuerpo de línea Presencia de Daño',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],
                        [
                            'id' => 'conector_3_3',
                            'label' => '3.3. Costuras Rotas o Dañadas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],
                        [
                            'id' => 'conector_3_4',
                            'label' => '3.4. Argollas o Deformaciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],
                        [
                            'id' => 'conector_3_5',
                            'label' => '3.5. Presencia de Aceites, Grasas o Químicos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Sí', 'No', 'NA'],
                        ],

                        [
                            'id' => 'acciones',
                            'label' => 'Acciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => [
                                'El Equipo se Marca como Dañado y es Sacado de Uso',
                                'El Equipo está en Buenas Condiciones',
                            ],
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