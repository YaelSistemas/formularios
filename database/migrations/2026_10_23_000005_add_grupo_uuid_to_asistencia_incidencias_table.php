<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            /*
             * Identifica los registros creados
             * desde una misma captura por rango.
             *
             * Ejemplo:
             * Vacaciones del 28 al 31 de octubre
             * tendrán 4 filas con el mismo UUID.
             */
            $table->uuid('grupo_uuid')
                ->nullable()
                ->after('user_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropColumn('grupo_uuid');
        });
    }
};