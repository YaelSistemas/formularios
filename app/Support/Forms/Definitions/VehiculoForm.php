<?php

namespace App\Support\Forms\Definitions;

use App\Support\Forms\Contracts\FormDefinition;

class VehiculoForm implements FormDefinition
{
    public static function key(): string
    {
        return 'vehiculo';
    }

    public static function title(): string
    {
        return 'Formulario de Vehículo';
    }

    public static function payload(): array
    {
        return [
            'fields' => [
                [
                    'id' => 'titulo_info',
                    'label' => 'Información',
                    'type' => 'static_text',
                    'required' => false,
                    'text' => 'Completa la revisión del vehículo antes de enviar el formulario.',
                ],
                [
                    'id' => 'imagen_referencia',
                    'label' => 'Referencia visual',
                    'type' => 'fixed_image',
                    'required' => false,
                    'url' => '/images/forms/vehiculo-referencia.jpg',
                ],
                [
                    'id' => 'separador_1',
                    'label' => '',
                    'type' => 'separator',
                    'required' => false,
                ],
                [
                    'id' => 'responsable',
                    'label' => 'Nombre del responsable',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'area',
                    'label' => 'Área / Departamento',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'fecha_revision',
                    'label' => 'Fecha de revisión',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'id' => 'placas',
                    'label' => 'Placas',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'marca',
                    'label' => 'Marca',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'modelo',
                    'label' => 'Modelo',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'id' => 'anio',
                    'label' => 'Año',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'id' => 'kilometraje',
                    'label' => 'Kilometraje',
                    'type' => 'number',
                    'required' => true,
                ],
                [
                    'id' => 'combustible',
                    'label' => 'Nivel de combustible',
                    'type' => 'select',
                    'required' => true,
                    'options' => ['VACÍO', '1/4', '1/2', '3/4', 'LLENO'],
                ],
                [
                    'id' => 'estado_general',
                    'label' => 'Estado general del vehículo',
                    'type' => 'radio',
                    'required' => true,
                    'options' => ['BUENO', 'REGULAR', 'MALO'],
                ],
                [
                    'id' => 'llantas_ok',
                    'label' => 'Llantas en buen estado',
                    'type' => 'checkbox',
                    'required' => false,
                ],
                [
                    'id' => 'luces_ok',
                    'label' => 'Luces funcionando correctamente',
                    'type' => 'checkbox',
                    'required' => false,
                ],
                [
                    'id' => 'documentos_ok',
                    'label' => 'Documentación completa',
                    'type' => 'checkbox',
                    'required' => false,
                ],
                [
                    'id' => 'evidencia_unidad',
                    'label' => 'Foto de la unidad',
                    'type' => 'photo',
                    'required' => false,
                ],
                [
                    'id' => 'observaciones',
                    'label' => 'Observaciones',
                    'type' => 'textarea',
                    'required' => false,
                ],
                [
                    'id' => 'firma_responsable',
                    'label' => 'Firma del responsable',
                    'type' => 'signature',
                    'required' => false,
                ],
            ],
        ];
    }
}