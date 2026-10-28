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
             * Fotografía tomada al empleado
             * al momento de registrar su entrada.
             *
             * Se guarda únicamente la ruta del archivo.
             */
            $table->string(
                'foto_entrada',
                255
            )
                ->nullable()
                ->after('firma_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn(
                'foto_entrada'
            );
        });
    }
};