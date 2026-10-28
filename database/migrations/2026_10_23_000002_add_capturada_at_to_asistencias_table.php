<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->timestamp('firma_capturada_at')
                ->nullable()
                ->after('firma_entrada');

            $table->timestamp('foto_capturada_at')
                ->nullable()
                ->after('foto_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn([
                'firma_capturada_at',
                'foto_capturada_at',
            ]);
        });
    }
};