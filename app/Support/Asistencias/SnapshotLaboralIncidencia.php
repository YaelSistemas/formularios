<?php

namespace App\Support\Asistencias;

use App\Models\AsistenciaIncidencia;
use App\Models\UserControlAcceso;

/**
 * Fotografía laboral individual, congelada al crear la incidencia.
 * No altera los datos históricos de incidencias preexistentes.
 */
class SnapshotLaboralIncidencia
{
    public static function paraFecha(int $userId, string $fecha): array
    {
        $config = UserControlAcceso::with(['empresa', 'unidadServicio', 'turno'])
            ->where('user_id', $userId)
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha))
            ->orderByDesc('fecha_inicio')->orderByDesc('id')->first();

        $turno = $config?->turno;
        $dias = null;
        if ($turno) {
            $dias = [];
            foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'] as $dia) {
                $dias[$dia] = (bool) $turno->{$dia};
            }
        }

        return [
            'snapshot_laboral_registrado' => true,
            'empresa_id_snapshot' => $config?->empresa_id,
            'empresa_nombre_snapshot' => $config?->empresa?->nombre,
            'unidad_servicio_id_snapshot' => $config?->unidad_servicio_id,
            'unidad_servicio_nombre_snapshot' => $config?->unidadServicio?->nombre,
            'turno_id_snapshot' => $config?->turno_id,
            'turno_nombre_snapshot' => $turno?->nombre,
            'tipo_trabajo_snapshot' => $config?->tipo_trabajo,
            'turno_dias_snapshot' => $dias,
        ];
    }

    /** En una edición conserva la fotografía original para el mismo empleado. */
    public static function existente(AsistenciaIncidencia $origen, int $userId, string $fecha): array
    {
        if (!$origen->snapshot_laboral_registrado) {
            // Nunca convertir silenciosamente un registro legado a uno histórico.
            return [
                'snapshot_laboral_registrado' => false,
                'empresa_id_snapshot' => null,
                'empresa_nombre_snapshot' => null,
                'unidad_servicio_id_snapshot' => null,
                'unidad_servicio_nombre_snapshot' => null,
                'turno_id_snapshot' => null,
                'turno_nombre_snapshot' => null,
                'tipo_trabajo_snapshot' => null,
                'turno_dias_snapshot' => null,
            ];
        }

        return [
            'snapshot_laboral_registrado' => true,
            'empresa_id_snapshot' => $origen->empresa_id_snapshot,
            'empresa_nombre_snapshot' => $origen->empresa_nombre_snapshot,
            'unidad_servicio_id_snapshot' => $origen->unidad_servicio_id_snapshot,
            'unidad_servicio_nombre_snapshot' => $origen->unidad_servicio_nombre_snapshot,
            'turno_id_snapshot' => $origen->turno_id_snapshot,
            'turno_nombre_snapshot' => $origen->turno_nombre_snapshot,
            'tipo_trabajo_snapshot' => $origen->tipo_trabajo_snapshot,
            'turno_dias_snapshot' => $origen->turno_dias_snapshot,
        ];
    }
}
