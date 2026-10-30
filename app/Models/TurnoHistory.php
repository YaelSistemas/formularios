<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TurnoHistory extends Model
{
    protected $fillable = [
        'turno_id',
        'action',
        'changes',
        'snapshot',
        'actor_user_id',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'snapshot' => 'array',
        ];
    }

    /**
     * Usuario que realizó el cambio.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_user_id'
        );
    }

    /**
     * Turno relacionado.
     *
     * Puede devolver null si el turno
     * fue eliminado posteriormente.
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(
            Turno::class,
            'turno_id'
        );
    }
}