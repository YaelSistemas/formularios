<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Versiones de formularios
        |--------------------------------------------------------------------------
        |
        | Cada fila representa una versión específica de un formulario.
        |
        | Ejemplo:
        | Formulario 31
        |   V1
        |   V2
        |   V3
        |
        | El payload se guarda como snapshot para que una versión histórica
        | no dependa del Definition actual.
        |
        */
        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_id')
                ->constrained('forms')
                ->restrictOnDelete();

            $table->unsignedInteger('version');

            /*
             * Identificador estable de la versión.
             *
             * Ejemplo:
             * sgi_pop_fo_05_reporte_de_mantenimiento_de_prensas_v1
             */
            $table->string('version_key')->unique();

            /*
             * Clase PHP Definition correspondiente a esta versión.
             */
            $table->string('definition_class');

            /*
             * Clave utilizada por React para elegir el JSX.
             */
            $table->string('layout_key');

            /*
             * Vista Blade utilizada para generar el PDF.
             */
            $table->string('pdf_view');

            /*
             * Snapshot completo del payload/Definition.
             *
             * Esto es fundamental:
             * aunque después exista V2, V3, etc.,
             * V1 conserva sus campos originales.
             */
            $table->json('payload');

            /*
             * Rango de vigencia.
             */
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();

            $table->text('descripcion')->nullable();

            $table->timestamps();

            /*
             * No puede existir dos veces la misma versión
             * dentro del mismo formulario.
             */
            $table->unique([
                'form_id',
                'version',
            ]);

            $table->index([
                'form_id',
                'fecha_desde',
                'fecha_hasta',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Relación del registro con su versión
        |--------------------------------------------------------------------------
        |
        | Nullable inicialmente porque ya existen registros históricos.
        |
        | En una etapa posterior todos esos registros serán asociados
        | cuidadosamente con V1.
        |
        */
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignId('form_version_id')
                ->nullable()
                ->after('form_id')
                ->constrained('form_versions')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropForeign([
                'form_version_id',
            ]);

            $table->dropColumn(
                'form_version_id'
            );
        });

        Schema::dropIfExists(
            'form_versions'
        );
    }
};