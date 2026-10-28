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
             * Estado del día en que se registró
             * la asistencia.
             */
            $table->string('estado_dia', 30)
                ->nullable()
                ->index();

            /*
             * Snapshot histórico de los días
             * configurados en el turno.
             */
            $table->boolean('turno_lunes_snapshot')
                ->nullable();

            $table->boolean('turno_martes_snapshot')
                ->nullable();

            $table->boolean('turno_miercoles_snapshot')
                ->nullable();

            $table->boolean('turno_jueves_snapshot')
                ->nullable();

            $table->boolean('turno_viernes_snapshot')
                ->nullable();

            $table->boolean('turno_sabado_snapshot')
                ->nullable();

            $table->boolean('turno_domingo_snapshot')
                ->nullable();

            /*
             * También conservar la vigencia
             * y estado que tenía el turno.
             */
            $table->date('turno_vigente_desde_snapshot')
                ->nullable();

            $table->date('turno_vigente_hasta_snapshot')
                ->nullable();

            $table->boolean('turno_activo_snapshot')
                ->nullable();
        });

        /*
         * Completar el snapshot de asistencias
         * que ya existen.
         */
        DB::statement("
            UPDATE asistencias AS a

            INNER JOIN user_control_accesos AS uca
                ON uca.id = a.user_control_acceso_id

            INNER JOIN turnos AS t
                ON t.id = uca.turno_id

            SET
                a.turno_lunes_snapshot =
                    t.lunes,

                a.turno_martes_snapshot =
                    t.martes,

                a.turno_miercoles_snapshot =
                    t.miercoles,

                a.turno_jueves_snapshot =
                    t.jueves,

                a.turno_viernes_snapshot =
                    t.viernes,

                a.turno_sabado_snapshot =
                    t.sabado,

                a.turno_domingo_snapshot =
                    t.domingo,

                a.turno_vigente_desde_snapshot =
                    t.vigente_desde,

                a.turno_vigente_hasta_snapshot =
                    t.vigente_hasta,

                a.turno_activo_snapshot =
                    t.activo,

                a.estado_dia =
                    CASE DAYOFWEEK(a.fecha)

                        WHEN 1 THEN
                            CASE
                                WHEN t.domingo = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 2 THEN
                            CASE
                                WHEN t.lunes = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 3 THEN
                            CASE
                                WHEN t.martes = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 4 THEN
                            CASE
                                WHEN t.miercoles = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 5 THEN
                            CASE
                                WHEN t.jueves = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 6 THEN
                            CASE
                                WHEN t.viernes = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        WHEN 7 THEN
                            CASE
                                WHEN t.sabado = 1
                                    THEN 'laborable'
                                ELSE 'fuera_dia_laborable'
                            END

                        ELSE NULL
                    END
        ");
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex([
                'estado_dia',
            ]);

            $table->dropColumn([
                'estado_dia',

                'turno_lunes_snapshot',
                'turno_martes_snapshot',
                'turno_miercoles_snapshot',
                'turno_jueves_snapshot',
                'turno_viernes_snapshot',
                'turno_sabado_snapshot',
                'turno_domingo_snapshot',

                'turno_vigente_desde_snapshot',
                'turno_vigente_hasta_snapshot',
                'turno_activo_snapshot',
            ]);
        });
    }
};