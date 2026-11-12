<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEntradaIncidencia extends Model
{
    protected $table = 'asistencia_entradas_incidencia';
    protected $fillable = ['asistencia_id','user_id','fecha','incidencia_id','incidencia_tipo','tipo_excepcion','incidencia_observaciones','motivo','estado','revisado_por_user_id','revisado_at','observaciones_revision'];
    protected $casts = ['fecha' => 'date', 'revisado_at' => 'datetime'];
    public function asistencia() { return $this->belongsTo(Asistencia::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function revisadoPor() { return $this->belongsTo(User::class, 'revisado_por_user_id'); }
    public function revisiones() { return $this->hasMany(AsistenciaEntradaIncidenciaRevision::class, 'entrada_incidencia_id')->orderBy('created_at')->orderBy('id'); }
}
