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
             * Snapshot histórico de la unidad laboral.
             *
             * No usamos foreign key porque estos datos
             * deben permanecer aunque la unidad o la
             * configuración cambien posteriormente.
             */
            $table->unsignedBigInteger(
                'unidad_servicio_id_snapshot'
            )->nullable()->index();

            $table->string(
                'unidad_servicio_nombre_snapshot',
                150
            )->nullable();

            /*
             * Ejemplos actuales:
             * fijo
             * mixto_salidas
             */
            $table->string(
                'tipo_trabajo_snapshot',
                30
            )->nullable();
        });

        /*
         * Completar asistencias existentes con
         * la configuración a la que actualmente
         * están vinculadas.
         */
        DB::statement("
            UPDATE asistencias AS a

            LEFT JOIN user_control_accesos AS uca
                ON uca.id = a.user_control_acceso_id

            LEFT JOIN unidades_servicio AS us
                ON us.id = uca.unidad_servicio_id

            SET
                a.unidad_servicio_id_snapshot =
                    uca.unidad_servicio_id,

                a.unidad_servicio_nombre_snapshot =
                    us.nombre,

                a.tipo_trabajo_snapshot =
                    uca.tipo_trabajo

            WHERE
                a.user_control_acceso_id IS NOT NULL
                AND a.unidad_servicio_id_snapshot IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex([
                'unidad_servicio_id_snapshot',
            ]);

            $table->dropColumn([
                'unidad_servicio_id_snapshot',
                'unidad_servicio_nombre_snapshot',
                'tipo_trabajo_snapshot',
            ]);
        });
    }
};