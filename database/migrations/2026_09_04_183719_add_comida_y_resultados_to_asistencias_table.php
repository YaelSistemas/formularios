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
             * COMIDA
             */
            $table->dateTime('hora_inicio_comida')
                ->nullable()
                ->after('estado_entrada');

            $table->unsignedBigInteger(
                'inicio_comida_registrada_por_user_id'
            )
                ->nullable()
                ->after('hora_inicio_comida');

            $table->string('metodo_inicio_comida', 20)
                ->nullable()
                ->after('inicio_comida_registrada_por_user_id');

            $table->dateTime('hora_fin_comida')
                ->nullable()
                ->after('metodo_inicio_comida');

            $table->unsignedBigInteger(
                'fin_comida_registrada_por_user_id'
            )
                ->nullable()
                ->after('hora_fin_comida');

            $table->string('metodo_fin_comida', 20)
                ->nullable()
                ->after('fin_comida_registrada_por_user_id');

            $table->unsignedInteger('minutos_comida')
                ->nullable()
                ->after('metodo_fin_comida');

            $table->string('estado_comida', 30)
                ->nullable()
                ->after('minutos_comida');

            /*
             * RESULTADO GENERAL
             */
            $table->unsignedInteger('minutos_presencia')
                ->nullable()
                ->after('hora_salida');

            $table->string('estado_jornada', 30)
                ->nullable()
                ->after('minutos_trabajados');

            /*
             * Auditoría de comida
             */
            $table->foreign(
                'inicio_comida_registrada_por_user_id',
                'asis_comida_ini_actor_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'fin_comida_registrada_por_user_id',
                'asis_comida_fin_actor_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            /*
             * Índices
             */
            $table->index(
                'estado_comida',
                'asis_estado_comida_idx'
            );

            $table->index(
                'estado_jornada',
                'asis_estado_jornada_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {

            $table->dropForeign(
                'asis_comida_ini_actor_fk'
            );

            $table->dropForeign(
                'asis_comida_fin_actor_fk'
            );

            $table->dropIndex(
                'asis_estado_comida_idx'
            );

            $table->dropIndex(
                'asis_estado_jornada_idx'
            );

            $table->dropColumn([
                'hora_inicio_comida',
                'inicio_comida_registrada_por_user_id',
                'metodo_inicio_comida',

                'hora_fin_comida',
                'fin_comida_registrada_por_user_id',
                'metodo_fin_comida',

                'minutos_comida',
                'estado_comida',

                'minutos_presencia',
                'estado_jornada',
            ]);
        });
    }
};