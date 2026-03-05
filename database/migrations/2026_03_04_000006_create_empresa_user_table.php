<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresa_user', function (Blueprint $table) {
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Opcional: por si quieres una empresa "por defecto" o principal
            $table->boolean('principal')->default(false);

            $table->timestamps();

            $table->primary(['empresa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_user');
    }
};