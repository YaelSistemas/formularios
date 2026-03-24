<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_servicio_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unidad_servicio_id')
                ->nullable()
                ->constrained('unidades_servicio')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 20); // created, updated, deleted
            $table->json('snapshot')->nullable();
            $table->json('changes')->nullable();

            $table->timestamps();

            $table->index(['unidad_servicio_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_servicio_histories');
    }
};