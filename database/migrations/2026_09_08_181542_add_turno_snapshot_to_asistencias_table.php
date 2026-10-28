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
            /*
             * Copia histórica del turno utilizado
             * al momento de crear la asistencia.
             *
             * turno_id_snapshot NO lleva foreign key.
             * Aunque el turno sea eliminado en el futuro,
             * queremos conservar el dato histórico.
             */
            $table->unsignedBigInteger('turno_id_snapshot')
                ->nullable()
                ->index();

            $table->string('turno_nombre_snapshot', 150)
                ->nullable();

            $table->time('turno_hora_inicio_snapshot')
                ->nullable();

            $table->time('turno_hora_fin_snapshot')
                ->nullable();

            $table->decimal(
                'turno_horas_jornada_snapshot',
                5,
                2
            )->nullable();

            $table->unsignedSmallInteger(
                'turno_minutos_comida_snapshot'
            )->nullable();

            $table->boolean(
                'turno_comida_cuenta_jornada_snapshot'
            )->nullable();
        });

        /*
         * Recuperar el turno para las asistencias
         * que ya existen actualmente.
         */
        DB::statement("
            UPDATE asistencias AS a

            INNER JOIN user_control_accesos AS uca
                ON uca.id = a.user_control_acceso_id

            INNER JOIN turnos AS t
                ON t.id = uca.turno_id

            SET
                a.turno_id_snapshot =
                    t.id,

                a.turno_nombre_snapshot =
                    t.nombre,

                a.turno_hora_inicio_snapshot =
                    t.hora_inicio,

                a.turno_hora_fin_snapshot =
                    t.hora_fin,

                a.turno_horas_jornada_snapshot =
                    t.horas_jornada,

                a.turno_minutos_comida_snapshot =
                    t.minutos_comida,

                a.turno_comida_cuenta_jornada_snapshot =
                    t.comida_cuenta_jornada

            WHERE
                a.turno_id_snapshot IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex([
                'turno_id_snapshot',
            ]);

            $table->dropColumn([
                'turno_id_snapshot',
                'turno_nombre_snapshot',
                'turno_hora_inicio_snapshot',
                'turno_hora_fin_snapshot',
                'turno_horas_jornada_snapshot',
                'turno_minutos_comida_snapshot',
                'turno_comida_cuenta_jornada_snapshot',
            ]);
        });
    }
};