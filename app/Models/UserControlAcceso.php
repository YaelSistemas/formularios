<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserControlAcceso extends Model
{
    protected $fillable = [
        'user_id',
        'empresa_id',
        'unidad_servicio_id',
        'turno_id',
        'tipo_trabajo',
        'kiosco_habilitado',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'kiosco_habilitado' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Usuario al que pertenece esta configuración.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Empresa laboral utilizada para Control de acceso.
     *
     * Es independiente de las empresas que el usuario
     * tenga asignadas para Formularios.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'empresa_id'
        );
    }
    
    /**
     * Unidad laboral utilizada para Control de acceso.
     *
     * Es independiente de las unidades que el usuario
     * tenga asignadas para Formularios.
     */
    public function unidadServicio(): BelongsTo
    {
        return $this->belongsTo(
            UnidadServicio::class,
            'unidad_servicio_id'
        );
    }

    /**
     * Turno asignado al usuario durante esta vigencia.
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }
}