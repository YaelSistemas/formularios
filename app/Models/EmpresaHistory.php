<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaHistory extends Model
{
    protected $table = 'empresa_histories';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}