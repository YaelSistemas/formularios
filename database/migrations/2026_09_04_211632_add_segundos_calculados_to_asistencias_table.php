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
            $table->unsignedInteger('segundos_presencia')
                ->nullable()
                ->after('minutos_presencia');

            $table->unsignedInteger('segundos_comida')
                ->nullable()
                ->after('minutos_comida');

            $table->unsignedInteger('segundos_trabajados')
                ->nullable()
                ->after('minutos_trabajados');
        });

        /*
         * Recuperar con precisión los registros que ya existen.
         *
         * Se utilizan las horas originales guardadas en BD,
         * por lo que no dependemos de los minutos redondeados.
         */
        DB::statement("
            UPDATE asistencias AS a
            LEFT JOIN user_control_accesos AS uca
                ON uca.id = a.user_control_acceso_id
            LEFT JOIN turnos AS t
                ON t.id = uca.turno_id
            SET
                a.segundos_presencia =
                    CASE
                        WHEN a.hora_salida IS NOT NULL
                        THEN GREATEST(
                            TIMESTAMPDIFF(
                                SECOND,
                                a.hora_entrada,
                                a.hora_salida
                            ),
                            0
                        )
                        ELSE NULL
                    END,

                a.segundos_comida =
                    CASE
                        WHEN
                            a.hora_inicio_comida IS NOT NULL
                            AND a.hora_fin_comida IS NOT NULL
                        THEN GREATEST(
                            TIMESTAMPDIFF(
                                SECOND,
                                a.hora_inicio_comida,
                                a.hora_fin_comida
                            ),
                            0
                        )
                        WHEN a.hora_salida IS NOT NULL
                        THEN 0
                        ELSE NULL
                    END,

                a.segundos_trabajados =
                    CASE
                        WHEN a.hora_salida IS NULL
                        THEN NULL

                        WHEN COALESCE(
                            t.comida_cuenta_jornada,
                            0
                        ) = 1
                        THEN GREATEST(
                            TIMESTAMPDIFF(
                                SECOND,
                                a.hora_entrada,
                                a.hora_salida
                            ),
                            0
                        )

                        ELSE GREATEST(
                            TIMESTAMPDIFF(
                                SECOND,
                                a.hora_entrada,
                                a.hora_salida
                            )
                            -
                            CASE
                                WHEN
                                    a.hora_inicio_comida IS NOT NULL
                                    AND a.hora_fin_comida IS NOT NULL
                                THEN GREATEST(
                                    TIMESTAMPDIFF(
                                        SECOND,
                                        a.hora_inicio_comida,
                                        a.hora_fin_comida
                                    ),
                                    0
                                )
                                ELSE 0
                            END,
                            0
                        )
                    END
        ");
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn([
                'segundos_presencia',
                'segundos_comida',
                'segundos_trabajados',
            ]);
        });
    }
};