<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmissionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'form_id',
        'user_id',
        'action',
        'snapshot',
        'changes',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changes' => 'array',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}