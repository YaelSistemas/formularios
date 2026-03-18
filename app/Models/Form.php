<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['user_id', 'title', 'status', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'form_user')->withTimestamps();
    }
}