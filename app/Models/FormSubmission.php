<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormSubmissionHistory;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'consecutive',
        'user_id',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Registros visibles para un usuario
    |--------------------------------------------------------------------------
    |
    | Administrador:
    | - Puede ver todos los registros.
    |
    | Usuario normal:
    | - Puede ver sus propios registros.
    | - Puede ver registros cuyo answers.taller coincida con alguna
    |   unidad de servicio que tenga asignada.
    |
    */
    public function scopeVisibleTo(
        Builder $query,
        User $user
    ): Builder {
        if ($user->hasRole('Administrador')) {
            return $query;
        }

        $unidadNombres = $user
            ->unidadesServicio()
            ->pluck('unidades_servicio.nombre')
            ->map(function ($nombre) {
                return trim((string) $nombre);
            })
            ->filter()
            ->unique()
            ->values();

        return $query->where(function (Builder $q) use (
            $user,
            $unidadNombres
        ) {
            /*
             * El usuario siempre puede ver los registros
             * creados por él mismo.
             */
            $q->where(
                'user_id',
                (int) $user->id
            );

            /*
             * También puede ver registros de otros usuarios
             * cuando el taller capturado coincide con alguna
             * de sus unidades de servicio.
             */
            if ($unidadNombres->isNotEmpty()) {
                $nombres = $unidadNombres->all();
            
                $q->orWhere(function (Builder $tallerQuery) use ($nombres) {
                    $tallerQuery
                        ->whereIn(
                            'answers->taller',
                            $nombres
                        )
                        ->orWhereIn(
                            'answers->taller_origen',
                            $nombres
                        )
                        ->orWhereIn(
                            'answers->taller_solicita',
                            $nombres
                        );
                });
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function histories()
    {
        return $this
            ->hasMany(
                FormSubmissionHistory::class,
                'form_submission_id'
            )
            ->orderBy(
                'created_at',
                'asc'
            );
    }
}