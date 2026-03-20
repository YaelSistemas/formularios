<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FormSubmissionHistory;

class FormSubmission extends Model
{
    protected $fillable = ['form_id', 'consecutive', 'user_id', 'answers'];

    protected $casts = [
        'answers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->hasMany(FormSubmissionHistory::class, 'form_submission_id')->orderBy('created_at', 'asc');
    }
}