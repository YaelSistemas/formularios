<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'asistencia_incidencia_histories',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Identificador lógico de la incidencia.
                 *
                 * Un rango puede estar compuesto por varias
                 * filas en asistencia_incidencias, pero todas
                 * comparten el mismo grupo_uuid.
                 *
                 * No usamos FK porque al editar o eliminar
                 * una incidencia las filas originales pueden
                 * desaparecer físicamente.
                 */
                $table->uuid('grupo_uuid')
                    ->index();

                /*
                 * created
                 * updated
                 * deleted
                 */
                $table->string(
                    'action',
                    30
                );

                /*
                 * Campos modificados:
                 *
                 * {
                 *   "tipo": {
                 *      "before": "vacaciones",
                 *      "after": "incapacidad"
                 *   }
                 * }
                 */
                $table->json('changes')
                    ->nullable();

                /*
                 * Estado completo de la incidencia
                 * en el momento del movimiento.
                 */
                $table->json('snapshot');

                /*
                 * Usuario administrador que realizó
                 * el movimiento.
                 */
                $table->foreignId(
                    'actor_user_id'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index([
                    'grupo_uuid',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'asistencia_incidencia_histories'
        );
    }
};