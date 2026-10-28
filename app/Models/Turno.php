<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'hora_inicio',
        'hora_fin',
        'horas_jornada',
        'minutos_comida',
        'comida_cuenta_jornada',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo',
        'vigente_desde',
        'vigente_hasta',
        'activo',
    ];

    protected $casts = [
        'horas_jornada' => 'decimal:2',
        'minutos_comida' => 'integer',

        'comida_cuenta_jornada' => 'boolean',

        'lunes' => 'boolean',
        'martes' => 'boolean',
        'miercoles' => 'boolean',
        'jueves' => 'boolean',
        'viernes' => 'boolean',
        'sabado' => 'boolean',
        'domingo' => 'boolean',

        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',

        'activo' => 'boolean',
    ];

    public function asignacionesUsuarios(): HasMany
    {
        return $this->hasMany(
            UserControlAcceso::class
        );
    }

}