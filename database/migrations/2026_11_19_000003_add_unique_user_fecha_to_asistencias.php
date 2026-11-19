<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No eliminar registros automáticamente si se detectan duplicados.
        $duplicados = DB::table('asistencias')
            ->select('user_id', 'fecha')
            ->groupBy('user_id', 'fecha')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicados) {
            throw new RuntimeException('Existen asistencias duplicadas por usuario y fecha. Revisarlas antes de aplicar el índice único.');
        }

        Schema::table('asistencias', function (Blueprint $table) {
            $table->unique(['user_id', 'fecha'], 'asistencias_user_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropUnique('asistencias_user_fecha_unique');
        });
    }
};
