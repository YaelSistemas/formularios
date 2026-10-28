<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_histories', function (Blueprint $table) {
            $table->id();

            /*
             * ID de la asistencia relacionada.
             *
             * NO usamos foreign key porque queremos conservar
             * el historial incluso después de eliminar
             * físicamente la asistencia original.
             */
            $table
                ->unsignedBigInteger('asistencia_id')
                ->index();

            /*
             * Usuario que realizó la acción administrativa.
             *
             * Puede ser null para movimientos realizados
             * automáticamente por el sistema.
             */
            $table
                ->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * created
             * updated
             * deleted
             */
            $table
                ->string('action', 30)
                ->index();

            /*
             * Para ediciones:
             *
             * [
             *   {
             *     "field": "hora_salida",
             *     "label": "Hora de salida",
             *     "type": "time",
             *     "old": "17:00:00",
             *     "new": "18:00:00"
             *   }
             * ]
             */
            $table
                ->json('changes')
                ->nullable();

            /*
             * Estado completo del registro.
             *
             * Se utilizará principalmente en:
             * - creación
             * - eliminación
             */
            $table
                ->json('snapshot')
                ->nullable();

            $table->timestamps();

            $table->index([
                'asistencia_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'asistencia_histories'
        );
    }
};