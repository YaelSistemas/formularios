<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asistencia_entradas_incidencia', function (Blueprint $table) {
            $table->dropForeign(['asistencia_id']);
        });

        Schema::table('asistencia_entradas_incidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('asistencia_id')->nullable()->change();
        });

        Schema::table('asistencia_entradas_incidencia', function (Blueprint $table) {
            $table->foreign('asistencia_id')
                ->references('id')
                ->on('asistencias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // No convertir a NOT NULL ni volver a RESTRICT aquí: tras borrar
        // asistencias válidamente, habrá registros con asistencia_id = NULL.
        // Revertir requeriría decidir primero cómo tratar esos historiales.
        throw new RuntimeException('Reversión manual requerida: existen posibles registros históricos sin asistencia.');
    }
};
