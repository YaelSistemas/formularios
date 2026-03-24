<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormHistory extends Model
{
    protected $table = 'form_histories';

    protected $fillable = [
        'form_id',
        'user_id',
        'action',
        'snapshot',
        'details',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'details' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}