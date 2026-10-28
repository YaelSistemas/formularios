<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('empresa_id_snapshot')
                ->nullable()
                ->after('turno_activo_snapshot');

            $table
                ->string('empresa_nombre_snapshot')
                ->nullable()
                ->after('empresa_id_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn([
                'empresa_id_snapshot',
                'empresa_nombre_snapshot',
            ]);
        });
    }
};