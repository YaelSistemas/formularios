<?php

namespace App\Support\Forms;

class FormRegistry
{
    public function all(): array
    {
        return collect(FormCatalog::definitions())
            ->map(function ($class) {
                return [
                    'key' => $class::key(),
                    'title' => $class::title(),
                    'payload' => $class::payload(),
                ];
            })
            ->values()
            ->all();
    }
}