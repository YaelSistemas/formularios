<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadServicioHistory extends Model
{
    protected $table = 'unidad_servicio_histories';

    protected $fillable = [
        'unidad_servicio_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function unidadServicio()
    {
        return $this->belongsTo(UnidadServicio::class, 'unidad_servicio_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}