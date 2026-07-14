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
                    'text' => 'Boleta de Observaciones',
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
                    'type' => 'textarea',
                    'required' => true,
                    'placeholder' => 'Escriba uno o más nombres, uno por línea',
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
                    'id' => 'falta_cometida_seleccionada',
                    'label' => 'Seleccione la Falta Cometida',
                    'type' => 'radio',
                    'required' => true,
                    'depends_on' => 'tipo_observacion',
                    'options_by_value' => [
                        'Acto Inseguro' => [
                            'Bromas o Distracciones en Área de Trabajo',
                            'No Portar EPP Específico por Actividad',
                            'Trabajar con Equipo en Movimiento',
                            'Uso de Herramientas en Mal Estado',
                            'Exceso de Velocidad o Movimiento Inapropiado',
                            'Trabajar en Alturas sin Medidas de Seguridad',
                            'Uso Inadecuado de EPP',
                            'No Realizar Bloqueos y Etiquetados',
                            'Daño a la Maquinaria',
                            'Daño a las Instalaciones',
                            'Otros, especifique',
                        ],
                        'Condición Peligrosa' => [
                            'Áreas sin Delimitacion o Señalización Adecuada',
                            'Equipos o Maquinaria con Matenimiento Deficiente',
                            'Instalaciones Eléctricas Expuestas o en Mal Estado',
                            'Piso Resbaladizo o con Obstaculos',
                            'Iluminación Insuficiente',
                            'Almacenamiento Inadecuado de Materiales',
                            'Falta de Señalización de Emergencia o Rutas de Evacuación',
                            'Otros, especifique',
                        ],
                        'Desviación' => [
                            'No Aplicar Procedimientos de Seguridad',
                            'No Aplicar Procedimientos Operativos',
                            'No Portar Credenciales o documentos de Acceso',
                            'No Traer Tarjeta y Candado P/Bloqueo',
                            'No Informar Situaciones Anormales o Riesgos Detectados',
                            'No Desbloquear Equipos de los Clientes',
                            'Otros, especifique',
                        ],
                    ],
                ],
                [
                    'id' => 'descripcion_falta_cometida',
                    'label' => 'Descripción de la Falta Cometida',
                    'type' => 'textarea',
                    'required' => true,
                ],
                [
                    'id' => 'evidencia_fotografica',
                    'label' => 'Evidencia Fotográfica',
                    'type' => 'file',
                    'required' => false,
                    'multiple' => true,
                    'accept' => 'image/*',
                ],
                [
                    'id' => 'acciones_preventivas_correctivas',
                    'label' => 'Acciones Correctivas',
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
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Puede agregar, modificar o eliminar nombres',
                ],
                [
                    'id' => 'firma_observado',
                    'label' => 'Firma del Observado',
                    'type' => 'signature',
                    'required' => false,
                    'multiple' => true,
                    'save_path' => 'forms/signatures/SSTPGITA01FO01_BoletaObservaciones/Observado',
                ],
            ],
        ];
    }
}