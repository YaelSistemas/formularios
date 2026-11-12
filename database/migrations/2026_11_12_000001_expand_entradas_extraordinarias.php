<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asistencia_entradas_incidencia', function (Blueprint $table) {
            // Valores: incidencia, dia_no_laborable, ambas.
            // Las filas existentes se conservan como incidencia.
            $table->string('tipo_excepcion', 30)->default('incidencia');
            $table->string('incidencia_tipo', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_entradas_incidencia', function (Blueprint $table) {
            $table->dropColumn('tipo_excepcion');
        });
        // No volver a NOT NULL: podría haber registros válidos de descanso sin incidencia.
    }
};
