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
        'fecha',
        'tipo',
        'observaciones',
        'registrado_por_user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
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