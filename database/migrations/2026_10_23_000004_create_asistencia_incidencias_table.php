<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_incidencias', function (Blueprint $table) {
            $table->id();

            /*
             * Empleado al que corresponde
             * la incidencia.
             */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Día específico al que aplica.
             *
             * Si desde pantalla se captura un rango,
             * por ejemplo del 28 al 30 de octubre,
             * se guardará una fila por cada día.
             */
            $table->date('fecha');

            /*
             * Tipos iniciales:
             * - vacaciones
             * - incapacidad
             * - festivo
             *
             * Se deja como string para poder agregar
             * más tipos en el futuro sin modificar
             * la estructura de la tabla.
             */
            $table->string('tipo', 50);

            /*
             * Comentario opcional.
             */
            $table->text('observaciones')
                ->nullable();

            /*
             * Usuario que realizó el registro.
             * Por ejemplo, el usuario de Nóminas.
             */
            $table->foreignId('registrado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Un usuario solamente puede tener
             * una incidencia por día.
             */
            $table->unique(
                ['user_id', 'fecha'],
                'asistencia_incidencias_user_fecha_unique'
            );

            /*
             * Índices útiles para consultas
             * y filtros posteriores.
             */
            $table->index('fecha');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_incidencias');
    }
};