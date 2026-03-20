<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->unsignedInteger('consecutive')->nullable()->after('form_id');
            $table->unique(['form_id', 'consecutive'], 'form_submissions_form_id_consecutive_unique');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropUnique('form_submissions_form_id_consecutive_unique');
            $table->dropColumn('consecutive');
        });
    }
};