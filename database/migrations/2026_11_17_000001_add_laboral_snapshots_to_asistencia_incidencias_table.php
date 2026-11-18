<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            // false = registro legado, nunca se le atribuye una empresa inventada.
            $table->boolean('snapshot_laboral_registrado')->default(false);
            $table->unsignedBigInteger('empresa_id_snapshot')->nullable();
            $table->string('empresa_nombre_snapshot')->nullable();
            $table->unsignedBigInteger('unidad_servicio_id_snapshot')->nullable();
            $table->string('unidad_servicio_nombre_snapshot')->nullable();
            $table->unsignedBigInteger('turno_id_snapshot')->nullable();
            $table->string('turno_nombre_snapshot')->nullable();
            $table->string('tipo_trabajo_snapshot')->nullable();
            $table->json('turno_dias_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropColumn([
                'snapshot_laboral_registrado', 'empresa_id_snapshot',
                'empresa_nombre_snapshot', 'unidad_servicio_id_snapshot',
                'unidad_servicio_nombre_snapshot', 'turno_id_snapshot',
                'turno_nombre_snapshot', 'tipo_trabajo_snapshot', 'turno_dias_snapshot',
            ]);
        });
    }
};
