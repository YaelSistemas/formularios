<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedBigInteger('last_consecutive')
                ->default(0);
        });

        /*
         * Inicializa el contador de cada formulario con el consecutivo
         * más alto que exista actualmente.
         *
         * A partir de esta migración, el contador ya no depende de que
         * los registros sigan existiendo o hayan sido eliminados.
         */
        DB::table('forms')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($forms) {
                foreach ($forms as $form) {
                    $maxConsecutive = DB::table('form_submissions')
                        ->where('form_id', $form->id)
                        ->whereNotNull('consecutive')
                        ->max('consecutive');

                    DB::table('forms')
                        ->where('id', $form->id)
                        ->update([
                            'last_consecutive' => (int) ($maxConsecutive ?? 0),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('last_consecutive');
        });
    }
};
