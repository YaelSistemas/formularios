<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 20); // created, updated, deleted
            $table->json('snapshot')->nullable();
            $table->json('changes')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_histories');
    }
};