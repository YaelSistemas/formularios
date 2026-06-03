<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas implements FormDefinition
{
    public static function key(): string
    {
        return 'sgi_pop_lg_01_fo_09_checklist_eslingas_de_cadenas';
    }

    public static function title(): string
    {
        return 'SGI-POP-LG-01-FO-09 Checklist Eslingas de Cadenas';
    }

    public static function payload(): array
    {
        $siNoOptions = [
            'Si',
            'No',
        ];

        return [
            'meta' => [
                'layout' => 'checklist_eslingas_de_cadenas',
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
                    'text' => 'CHECKLIST ESLINGAS DE CADENAS',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SGI-POP-LG-01-FO-09',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: ',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: ',
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
                    'id' => 'separacion_tabla',
                    'type' => 'separator',
                    'label' => 'Criterios de revisión',
                ],

                [
                    'id' => 'indicacion_criterios',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo al estado de la eslinga de cadena',
                ],

                [
                    'id' => 'tabla_checklist_eslingas_cadenas',
                    'label' => 'Criterios de revisión',
                    'type' => 'table',
                    'required' => true,
                    'columns' => [
                        'Criterio',
                        'Condición',
                        'Observaciones',
                    ],
                    'row_schema' => [
                        [
                            'id' => 'numero_eslinga',
                            'label' => 'N° de Eslinga',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'diametro',
                            'label' => 'Diámetro',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'capacidad',
                            'label' => 'Capacidad',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'longitud',
                            'label' => 'Longitud',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'id' => 'elongacion_causada_por_estiramiento',
                            'label' => 'Elongación Causada por Estiramiento',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'eslabones_distorsionados_o_danados',
                            'label' => 'Eslabones Distorsionados o Dañados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'presenta_muescas_o_estrias',
                            'label' => 'Presenta Muescas o Estrías',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'presenta_corrosion_general',
                            'label' => 'Presenta Corrosión General',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'eslabones_torcidos',
                            'label' => 'Eslabones Torcidos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'trizaduras_en_partes_soldadas',
                            'label' => 'Trizaduras en Partes Soldadas',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'ojos_o_eslabones_desgastados',
                            'label' => 'Ojos o Eslabones Desgastados',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'se_realiza_revision_de_ganchos',
                            'label' => 'Se Realiza Revisión de Ganchos',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'cuenta_con_seguro_de_gancho',
                            'label' => 'Cuenta con Seguro de Gancho',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'tiene_rotulacion_carga_maxima',
                            'label' => 'Tiene Rotulación Carga Máxima',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'almacenamiento_correcto',
                            'label' => 'Almacenamiento Correcto',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'libre_de_aceite_o_grasas_libre_de_quimicos',
                            'label' => 'Libre de Aceite o Grasas (Libre de Químicos)',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'etiqueta_visible',
                            'label' => 'Etiqueta Visible',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'cuenta_con_certificado_de_fabricante',
                            'label' => 'Cuenta con Certificado de Fabricante',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                        [
                            'id' => 'la_eslinga_esta_en_buenas_condiciones',
                            'label' => 'La Eslinga está en Buenas Condiciones',
                            'type' => 'radio',
                            'required' => true,
                            'options' => $siNoOptions,
                        ],
                    ],
                ],

                [
                    'id' => 'notas',
                    'label' => 'Notas',
                    'type' => 'textarea',
                    'required' => false,
                ],

                [
                    'id' => 'nombre_colaborador_inspecciono',
                    'label' => 'Nombre del Colaborador que Inspecciono',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'firma_colaborador_inspecciono',
                    'label' => 'Firma del Colaborador que Inspecciono',
                    'type' => 'signature',
                    'required' => false,
                    'save_path' => 'forms/signatures/SGIPOPLG01FO09_ChecklistEslingasCadenas/Colaborador_Inspecciono',
                ],
            ],
        ];
    }
}