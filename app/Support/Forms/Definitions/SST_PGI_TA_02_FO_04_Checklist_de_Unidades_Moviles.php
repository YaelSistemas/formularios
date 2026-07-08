<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class SST_PGI_TA_02_FO_04_Checklist_de_Unidades_Moviles implements FormDefinition
{
    public static function key(): string
    {
        return 'sst_pgi_ta_02_fo_04_checklist_de_unidades_moviles';
    }

    public static function title(): string
    {
        return 'SST-PGI-TA-02-FO-04 Checklist de Unidades Móviles';
    }

    public static function payload(): array
    {
        $estadoOptions = [
            'Buena',
            'Mala',
            'Reposición',
            'Reparación',
        ];

        $estadoConObservaciones = function (string $id, string $label) use ($estadoOptions): array {
            return [
                [
                    'id' => $id,
                    'label' => $label,
                    'type' => 'radio',
                    'required' => true,
                    'options' => $estadoOptions,
                ],
                [
                    'id' => $id . '_observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ];
        };

        return [
            'meta' => [
                'layout' => 'checklist_de_unidades_moviles',
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
                    'text' => 'Checklist de Unidades Móviles',
                ],
                [
                    'id' => 'header_line_4',
                    'type' => 'static_text',
                    'text' => 'Código: SST-PGI-TA-02-FO-04',
                ],
                [
                    'id' => 'header_line_5',
                    'type' => 'static_text',
                    'text' => 'Fecha de Emisión: 27/03/2025',
                ],
                [
                    'id' => 'header_line_6',
                    'type' => 'static_text',
                    'text' => 'Número de Revisión: 06',
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
                    'id' => 'nombre_responsable_inspeccion',
                    'label' => 'Nombre de Responsable de Inspección',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'firma_responsable_inspeccion',
                    'label' => 'Firma del Responsable de Inspección',
                    'type' => 'signature',
                    'required' => true,
                ],

                [
                    'id' => 'criterios_inspeccion_unidades_moviles',
                    'type' => 'static_text',
                    'text' => 'Considerar los siguientes criterios de acuerdo a la Inspección de Unidades Móviles',
                ],
                [
                    'id' => 'placas',
                    'label' => 'Placas',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'kilometraje',
                    'label' => 'Kilometraje',
                    'type' => 'text',
                    'required' => true,
                ],

                [
                    'id' => 'subtitulo_motor',
                    'type' => 'subtitle',
                    'label' => 'Motor',
                ],
                ...$estadoConObservaciones('cables_bujias', 'Cables de Bujías'),
                ...$estadoConObservaciones('nivel_anticongelante', 'Nivel de Anticongelante'),
                ...$estadoConObservaciones('nivel_liquido_frenos', 'Nivel de Líquido para Frenos'),
                ...$estadoConObservaciones('nivel_aceite_motor', 'Nivel de Aceite para Motor'),
                ...$estadoConObservaciones('nivel_aceite_transmision', 'Nivel de Aceite para Transmisión'),
                ...$estadoConObservaciones('liquido_limpia_parabrisas', 'Líquido Limpia Parabrisas'),

                [
                    'id' => 'subtitulo_chasis',
                    'type' => 'subtitle',
                    'label' => 'Chasis',
                ],
                ...$estadoConObservaciones('golpes_carroceria', 'Golpes en Carrocería'),
                ...$estadoConObservaciones('vidrios_estrellados', 'Vidrios Estrellados'),
                ...$estadoConObservaciones('espejo_retrovisor', 'Espejo Retrovisor'),
                ...$estadoConObservaciones('espejos_laterales', 'Espejos Laterales'),
                ...$estadoConObservaciones('llantas_vehiculo', 'Llantas del Vehículo'),
                ...$estadoConObservaciones('luces_unidad_altas_bajas', 'Luces de la Unidad (Altas y Bajas)'),
                ...$estadoConObservaciones('intermitentes', 'Intermitentes'),
                ...$estadoConObservaciones('direccionales', 'Direccionales'),
                ...$estadoConObservaciones('foco_reversa', 'Foco de Reversa'),
                ...$estadoConObservaciones('llanta_auxiliar', 'Llanta Auxiliar'),
                ...$estadoConObservaciones('alarma_reversa', 'Alarma de Reversa'),
                ...$estadoConObservaciones('torreta', 'Torreta'),
                ...$estadoConObservaciones('calzas_seguridad', 'Calzas de Seguridad'),
                ...$estadoConObservaciones('banderola', 'Banderola'),

                [
                    'id' => 'subtitulo_interior',
                    'type' => 'subtitle',
                    'label' => 'Interior',
                ],
                ...$estadoConObservaciones('vestiduras_asientos', 'Vestiduras de Asientos'),
                ...$estadoConObservaciones('cinturones_seguridad', 'Cinturones de Seguridad'),
                ...$estadoConObservaciones('tablero', 'Tablero'),
                ...$estadoConObservaciones('herramienta_retirar_refaccion', 'Herramienta para Retirar Refacción'),
                ...$estadoConObservaciones('gato_hidraulico', 'Gato Hidráulico'),
                ...$estadoConObservaciones('triangulos_precaucion', 'Triángulos de Precaución'),
                ...$estadoConObservaciones('cable_pasar_corriente', 'Cable para Pasar Corriente'),
                ...$estadoConObservaciones('botiquin_movil', 'Botiquín Móvil'),
                ...$estadoConObservaciones('camara_reversa', 'Cámara de Reversa'),

                [
                    'id' => 'subtitulo_extintor',
                    'type' => 'subtitle',
                    'label' => 'Extintor',
                ],
                [
                    'id' => 'vigencia_extintor',
                    'label' => 'Vigencia',
                    'type' => 'text',
                    'required' => true,
                ],
                ...$estadoConObservaciones('condicion_extintor', 'Condición'),

                [
                    'id' => 'subtitulo_documentacion',
                    'type' => 'subtitle',
                    'label' => 'Documentación',
                ],
                [
                    'id' => 'vigencia_tarjeta_circulacion',
                    'label' => 'Vigencia de Tarjeta de Circulación',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'id' => 'vigencia_tarjeta_circulacion_observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'vigencia_licencia_conducir',
                    'label' => 'Vigencia de Licencia de Conducir',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'id' => 'vigencia_licencia_conducir_observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'vigencia_tipo_licencia',
                    'label' => 'Vigencia de Tipo de Licencia',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'id' => 'vigencia_tipo_licencia_observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'poliza_seguro',
                    'label' => 'Póliza de Seguro',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'poliza_seguro_observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],

                [
                    'id' => 'subtitulo_tarjeta_efecticar',
                    'type' => 'subtitle',
                    'label' => 'Tarjeta Efecticar',
                ],
                [
                    'id' => 'vigencia_tarjeta_efecticar',
                    'label' => 'Vigencia de Tarjeta Efecticar',
                    'type' => 'date',
                    'required' => true,
                ],
                ...$estadoConObservaciones('condicion_tarjeta_efecticar', 'Condición'),

                [
                    'id' => 'evidencia_fotografica',
                    'label' => 'Evidencia Fotográfica',
                    'type' => 'file',
                    'required' => false,
                    'multiple' => true,
                    'accept' => 'image/*',
                ],
                [
                    'id' => 'prohibido_conducir',
                    'type' => 'static_text',
                    'text' => 'PROHIBIDO CONDUCIR CUALQUIER VEHICULO A EXCESO DE VELOCIDAD BAJO LOS EFECTOS DEL ALCOHOL O ALGUNA DROGA',
                ],
                [
                    'id' => 'notas',
                    'label' => 'Notas',
                    'type' => 'textarea',
                    'required' => false,
                ],
            ],
        ];
    }
}