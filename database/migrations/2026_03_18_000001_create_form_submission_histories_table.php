<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submission_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_submission_id')
                ->constrained('form_submissions')
                ->cascadeOnDelete();

            $table->foreignId('form_id')
                ->nullable()
                ->constrained('forms')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action'); // created, updated, deleted

            $table->json('snapshot')->nullable(); // estado completo del answers en ese momento
            $table->json('changes')->nullable();  // solo cambios detectados

            $table->timestamps();

            $table->index(['form_submission_id', 'created_at']);
            $table->index(['form_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_histories');
    }
};