<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asistencia_entradas_incidencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->unique()->constrained('asistencias')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('fecha');
            $table->unsignedBigInteger('incidencia_id')->nullable(); // Se preserva el snapshot si se borra la incidencia.
            $table->string('incidencia_tipo', 50);
            $table->text('incidencia_observaciones')->nullable();
            $table->text('motivo');
            $table->string('estado', 30)->default('pendiente');
            $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->text('observaciones_revision')->nullable();
            $table->timestamps();
            $table->index(['estado', 'fecha']);
            $table->index(['user_id', 'fecha']);
        });
        Schema::create('asistencia_entrada_incidencia_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrada_incidencia_id');
            $table->foreign('entrada_incidencia_id', 'aei_revision_entrada_fk')->references('id')->on('asistencia_entradas_incidencia')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('asistencia_entrada_incidencia_revisions');
        Schema::dropIfExists('asistencia_entradas_incidencia');
    }
};
