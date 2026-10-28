<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $fillable = [
        'user_id',
        'user_control_acceso_id',
        'fecha',
    
        'hora_entrada',
        'entrada_registrada_por_user_id',
        'metodo_entrada',
    
        'hora_salida',
        'salida_registrada_por_user_id',
        'metodo_salida',

        'minutos_trabajados',
        'estado_entrada',
        'estado_salida',

        'hora_inicio_comida',
        'inicio_comida_registrada_por_user_id',
        'metodo_inicio_comida',
        
        'hora_fin_comida',
        'fin_comida_registrada_por_user_id',
        'metodo_fin_comida',
        
        'minutos_comida',
        'estado_comida',
        
        'minutos_presencia',
        'estado_jornada',

        'segundos_presencia',
        'segundos_comida',
        'segundos_trabajados',

        'turno_id_snapshot',
        'turno_nombre_snapshot',
        'turno_hora_inicio_snapshot',
        'turno_hora_fin_snapshot',
        'turno_horas_jornada_snapshot',
        'turno_minutos_comida_snapshot',
        'turno_comida_cuenta_jornada_snapshot',

        'estado_dia',

        'turno_lunes_snapshot',
        'turno_martes_snapshot',
        'turno_miercoles_snapshot',
        'turno_jueves_snapshot',
        'turno_viernes_snapshot',
        'turno_sabado_snapshot',
        'turno_domingo_snapshot',
        
        'turno_vigente_desde_snapshot',
        'turno_vigente_hasta_snapshot',
        'turno_activo_snapshot',

        'unidad_servicio_id_snapshot',
        'unidad_servicio_nombre_snapshot',
        
        'empresa_id_snapshot',
        'empresa_nombre_snapshot',
        
        'tipo_trabajo_snapshot',

        'firma_entrada',
        'firma_capturada_at',
        
        'foto_entrada',
        'foto_capturada_at',
        
        'entrada_latitud',
        'entrada_longitud',
        'entrada_precision_metros',
        'entrada_ubicacion_capturada_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
    
        'firma_capturada_at' => 'datetime',
        'foto_capturada_at' => 'datetime',
        'entrada_ubicacion_capturada_at' => 'datetime',
    ];

    /**
     * Usuario al que pertenece la asistencia.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function controlAcceso(): BelongsTo
    {
        return $this->belongsTo(
            UserControlAcceso::class,
            'user_control_acceso_id'
        );
    }
}