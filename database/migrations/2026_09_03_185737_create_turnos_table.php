<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */

            $table->string('nombre', 100);

            $table->string('descripcion', 255)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Horario
            |--------------------------------------------------------------------------
            |
            | hora_inicio / hora_fin representan la ventana del turno.
            |
            | Ejemplo:
            | Inicio: 06:00
            | Fin:    20:00
            |
            | Esto NO significa necesariamente que la jornada sea de 14 horas.
            | Las horas ordinarias se configuran independientemente.
            |
            */

            $table->time('hora_inicio');

            $table->time('hora_fin');

            /*
             * Horas ordinarias que corresponden a la jornada.
             *
             * Ejemplos:
             * 8.00
             * 7.50
             * 7.00
             */
            $table->decimal(
                'horas_jornada',
                5,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Comida
            |--------------------------------------------------------------------------
            */

            $table->unsignedSmallInteger(
                'minutos_comida'
            )->default(0);

            /*
             * true  = la comida forma parte de la jornada.
             * false = el tiempo de comida se descuenta.
             */
            $table->boolean(
                'comida_cuenta_jornada'
            )->default(false);

            /*
            |--------------------------------------------------------------------------
            | Días laborables
            |--------------------------------------------------------------------------
            */

            $table->boolean('lunes')
                ->default(false);

            $table->boolean('martes')
                ->default(false);

            $table->boolean('miercoles')
                ->default(false);

            $table->boolean('jueves')
                ->default(false);

            $table->boolean('viernes')
                ->default(false);

            $table->boolean('sabado')
                ->default(false);

            $table->boolean('domingo')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Vigencia
            |--------------------------------------------------------------------------
            |
            | Así podremos conservar turnos históricos.
            |
            | Ejemplo:
            | Diurno 2026 → 8 horas
            | Diurno 2027 → 7.5 horas
            |
            */

            $table->date(
                'vigente_desde'
            );

            $table->date(
                'vigente_hasta'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index('nombre');

            $table->index('activo');

            $table->index([
                'vigente_desde',
                'vigente_hasta',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};