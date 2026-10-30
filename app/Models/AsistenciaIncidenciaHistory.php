<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaIncidenciaHistory extends Model
{
    protected $fillable = [
        'grupo_uuid',
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
     * Usuario que realizó
     * el movimiento.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actor_user_id'
        );
    }
}