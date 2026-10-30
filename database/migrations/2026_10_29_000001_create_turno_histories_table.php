<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_histories', function (Blueprint $table) {
            $table->id();

            /*
             * No colocamos FK hacia turnos porque queremos
             * conservar el historial incluso si el turno
             * llega a eliminarse.
             */
            $table->unsignedBigInteger('turno_id')
                ->index();

            $table->string('action', 30);

            $table->json('changes')
                ->nullable();

            $table->json('snapshot');

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'turno_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'turno_histories'
        );
    }
};