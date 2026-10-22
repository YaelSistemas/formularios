<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormVersion extends Model
{
    protected $fillable = [
        'form_id',
        'version',
        'version_key',
        'definition_class',
        'layout_key',
        'pdf_view',
        'payload',
        'fecha_desde',
        'fecha_hasta',
        'descripcion',
    ];

    protected $casts = [
        'payload' => 'array',
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function submissions()
    {
        return $this->hasMany(
            FormSubmission::class,
            'form_version_id'
        );
    }
}