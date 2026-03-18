<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadServicio extends Model
{
    protected $table = 'unidades_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'unidad_servicio_user',
            'unidad_servicio_id',
            'user_id'
        );
    }
}