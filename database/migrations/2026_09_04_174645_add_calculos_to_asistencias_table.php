<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            /*
             * Tiempo transcurrido entre entrada y salida.
             * Se guarda en minutos para facilitar reportes y cálculos.
             */
            $table->unsignedInteger('minutos_trabajados')
                ->nullable()
                ->after('hora_salida');

            /*
             * Se dejan preparados para cuando definamos
             * las reglas exactas de puntualidad.
             */
            $table->string('estado_entrada', 30)
                ->nullable()
                ->after('metodo_entrada');

            $table->string('estado_salida', 30)
                ->nullable()
                ->after('metodo_salida');

            $table->index(
                'estado_entrada',
                'asis_estado_entrada_idx'
            );

            $table->index(
                'estado_salida',
                'asis_estado_salida_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex(
                'asis_estado_entrada_idx'
            );

            $table->dropIndex(
                'asis_estado_salida_idx'
            );

            $table->dropColumn([
                'minutos_trabajados',
                'estado_entrada',
                'estado_salida',
            ]);
        });
    }
};