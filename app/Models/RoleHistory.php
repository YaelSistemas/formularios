<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleHistory extends Model
{
    protected $table = 'role_histories';

    protected $fillable = [
        'role_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}