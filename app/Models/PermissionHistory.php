<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionHistory extends Model
{
    protected $table = 'permission_histories';

    protected $fillable = [
        'permission_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}