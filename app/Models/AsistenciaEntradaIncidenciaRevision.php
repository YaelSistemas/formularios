<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEntradaIncidenciaRevision extends Model
{
    protected $table = 
    'asistencia_entrada_incidencia_revisions';

    protected $fillable = [
        'entrada_incidencia_id',
        'actor_user_id',
        'estado_anterior',
        'estado_nuevo',
        'observaciones'
    ];
    
    public function actor() { 
        return $this->belongsTo(User::class, 'actor_user_id'); 
    }
}
