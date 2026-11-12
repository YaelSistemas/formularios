<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->boolean('es_masiva')->default(false)->after('grupo_uuid');
            $table->string('alcance', 30)->nullable()->after('es_masiva');
            $table->boolean('personalizada')->default(false)->after('alcance');
        });
    }
    public function down(): void {
        Schema::table('asistencia_incidencias', function (Blueprint $table) {
            $table->dropColumn(['es_masiva', 'alcance', 'personalizada']);
        });
    }
};
