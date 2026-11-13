<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La llave foránea hacia users necesita un índice que comience por user_id.
        // No se puede eliminar el índice único original si es el único que la respalda.
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->index('user_id', 'asistencia_incidencias_user_id_idx');
        });

        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropUnique('asistencia_incidencias_user_fecha_unique');
        });

        // Permite festivo y vacaciones el mismo día, pero evita duplicar el mismo tipo.
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'fecha', 'tipo'],
                'incidencias_usuario_fecha_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        // Solo es reversible si no hay varios tipos de incidencia
        // para un mismo empleado y fecha.
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropUnique('incidencias_usuario_fecha_tipo_unique');
        });

        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'fecha'],
                'asistencia_incidencias_user_fecha_unique'
            );
        });

        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropIndex('asistencia_incidencias_user_id_idx');
        });
    }
};
