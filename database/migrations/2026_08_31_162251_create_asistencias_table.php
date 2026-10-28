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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();

            /*
             * Usuario al que pertenece la asistencia.
             *
             * Si algún día se elimina el usuario, conservamos
             * el registro de asistencia y user_id queda en null.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Fecha correspondiente a la asistencia.
             */
            $table->date('fecha');

            /*
             * Hora exacta registrada por el servidor
             * cuando el usuario inicia su asistencia.
             */
            $table->dateTime('hora_entrada');

            /*
             * Se queda en null mientras la asistencia
             * esté en curso.
             */
            $table->dateTime('hora_salida')->nullable();

            $table->timestamps();

            /*
             * Índices para consultas por usuario y fecha.
             */
            $table->index('fecha');
            $table->index(['user_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};