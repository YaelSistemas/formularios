<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->foreignId('user_control_acceso_id')
                ->nullable()
                ->after('user_id')
                ->constrained('user_control_accesos')
                ->nullOnDelete();

            $table->index(
                'user_control_acceso_id',
                'asis_control_acceso_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign([
                'user_control_acceso_id',
            ]);

            $table->dropIndex(
                'asis_control_acceso_idx'
            );

            $table->dropColumn(
                'user_control_acceso_id'
            );
        });
    }
};