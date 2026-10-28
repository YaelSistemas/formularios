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
             * Firma realizada por el empleado
             * al momento de registrar su entrada.
             *
             * Se guardará la ruta del archivo,
             * no la imagen completa dentro de la BD.
             */
            $table->string(
                'firma_entrada',
                255
            )->nullable();

            /*
             * Ubicación obtenida desde el dispositivo
             * donde se registra la entrada.
             */
            $table->decimal(
                'entrada_latitud',
                10,
                7
            )->nullable();

            $table->decimal(
                'entrada_longitud',
                10,
                7
            )->nullable();

            /*
             * Precisión reportada por el navegador
             * en metros.
             *
             * Ejemplo:
             * 8.35 = aproximadamente ±8.35 metros.
             */
            $table->decimal(
                'entrada_precision_metros',
                10,
                2
            )->nullable();

            /*
             * Momento en el que el servidor recibió
             * y guardó la ubicación de entrada.
             */
            $table->dateTime(
                'entrada_ubicacion_capturada_at'
            )->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn([
                'firma_entrada',
                'entrada_latitud',
                'entrada_longitud',
                'entrada_precision_metros',
                'entrada_ubicacion_capturada_at',
            ]);
        });
    }
};