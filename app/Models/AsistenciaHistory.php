<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaHistory extends Model
{
    protected $fillable = [
        'asistencia_id',
        'actor_user_id',
        'action',
        'changes',
        'snapshot',
    ];

    protected $casts = [
        'changes' => 'array',
        'snapshot' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(
            User::class,
            'actor_user_id'
        );
    }

    public function asistencia()
    {
        return $this->belongsTo(
            Asistencia::class,
            'asistencia_id'
        );
    }
}