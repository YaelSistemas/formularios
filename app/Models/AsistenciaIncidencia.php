<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaIncidencia extends Model
{
    protected $table =
        'asistencia_incidencias';

    protected $fillable = [
        'user_id',
        'grupo_uuid',
        'es_masiva',
        'alcance',
        'personalizada',
        'fecha',
        'tipo',
        'observaciones',
        'registrado_por_user_id',
        'snapshot_laboral_registrado',
        'empresa_id_snapshot',
        'empresa_nombre_snapshot',
        'unidad_servicio_id_snapshot',
        'unidad_servicio_nombre_snapshot',
        'turno_id_snapshot',
        'turno_nombre_snapshot',
        'tipo_trabajo_snapshot',
        'turno_dias_snapshot',
        'snapshot_laboral_registrado',
        'empresa_id_snapshot',
        'empresa_nombre_snapshot',
        'unidad_servicio_id_snapshot',
        'unidad_servicio_nombre_snapshot',
        'turno_id_snapshot',
        'turno_nombre_snapshot',
        'tipo_trabajo_snapshot',
        'turno_dias_snapshot',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_masiva' => 'boolean',
        'personalizada' => 'boolean',
        'snapshot_laboral_registrado' => 'boolean',
        'turno_dias_snapshot' => 'array',
        'snapshot_laboral_registrado' => 'boolean',
        'turno_dias_snapshot' => 'array',
    ];

    /**
     * Empleado al que pertenece
     * la incidencia.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Usuario que registró
     * la incidencia.
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registrado_por_user_id'
        );
    }
}