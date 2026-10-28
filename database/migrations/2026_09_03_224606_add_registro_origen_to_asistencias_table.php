<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->foreignId('entrada_registrada_por_user_id')
                ->nullable()
                ->after('hora_entrada')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('metodo_entrada', 20)
                ->nullable()
                ->after('entrada_registrada_por_user_id');

            $table->foreignId('salida_registrada_por_user_id')
                ->nullable()
                ->after('hora_salida')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('metodo_salida', 20)
                ->nullable()
                ->after('salida_registrada_por_user_id');

            $table->index(
                'entrada_registrada_por_user_id',
                'asis_entrada_actor_idx'
            );

            $table->index(
                'salida_registrada_por_user_id',
                'asis_salida_actor_idx'
            );

            $table->index(
                'metodo_entrada',
                'asis_metodo_entrada_idx'
            );

            $table->index(
                'metodo_salida',
                'asis_metodo_salida_idx'
            );
        });

        /*
         * Los registros que ya existían antes de implementar
         * Kiosco fueron registros personales.
         */
        DB::table('asistencias')
            ->whereNotNull('hora_entrada')
            ->update([
                'entrada_registrada_por_user_id' => DB::raw('user_id'),
                'metodo_entrada' => 'personal',
            ]);

        DB::table('asistencias')
            ->whereNotNull('hora_salida')
            ->update([
                'salida_registrada_por_user_id' => DB::raw('user_id'),
                'metodo_salida' => 'personal',
            ]);
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign([
                'entrada_registrada_por_user_id',
            ]);

            $table->dropForeign([
                'salida_registrada_por_user_id',
            ]);

            $table->dropIndex(
                'asis_entrada_actor_idx'
            );

            $table->dropIndex(
                'asis_salida_actor_idx'
            );

            $table->dropIndex(
                'asis_metodo_entrada_idx'
            );

            $table->dropIndex(
                'asis_metodo_salida_idx'
            );

            $table->dropColumn([
                'entrada_registrada_por_user_id',
                'metodo_entrada',
                'salida_registrada_por_user_id',
                'metodo_salida',
            ]);
        });
    }
};