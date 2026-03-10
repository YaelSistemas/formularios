<?php

namespace App\Support\Forms\Contracts;

interface FormDefinition
{
    public static function key(): string;

    public static function title(): string;

    public static function payload(): array;
}