<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoHistory extends Model
{
    protected $table = 'grupo_histories';

    protected $fillable = [
        'grupo_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}