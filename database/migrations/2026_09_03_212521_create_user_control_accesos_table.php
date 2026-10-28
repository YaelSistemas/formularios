<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_control_accesos', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Usuario
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Unidad laboral
            |--------------------------------------------------------------------------
            |
            | Esta NO es la relación utilizada para el alcance de Formularios.
            | Representa la unidad a la que laboralmente pertenece el usuario
            | para efectos de Control de acceso y reportes de asistencia.
            |
            */

            $table->foreignId('unidad_servicio_id')
                ->constrained('unidades_servicio')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Turno
            |--------------------------------------------------------------------------
            */

            $table->foreignId('turno_id')
                ->constrained('turnos')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tipo de trabajo
            |--------------------------------------------------------------------------
            |
            | Valores que utilizaremos:
            |
            | fijo
            | mixto_salidas
            |
            */

            $table->string('tipo_trabajo', 30);

            /*
            |--------------------------------------------------------------------------
            | Kiosco
            |--------------------------------------------------------------------------
            |
            | Si está habilitado, el usuario podrá aparecer en la vista
            | Kiosco para que otro usuario autorizado registre su
            | entrada o salida.
            |
            */

            $table->boolean('kiosco_habilitado')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Vigencia de esta configuración laboral
            |--------------------------------------------------------------------------
            |
            | Cuando cambie turno, unidad, tipo de trabajo, etc.,
            | conservaremos el registro anterior y crearemos uno nuevo.
            |
            */

            $table->date('fecha_inicio');

            $table->date('fecha_fin')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'user_id',
                    'fecha_inicio',
                    'fecha_fin',
                ],
                'uca_user_fechas_idx'
            );
            
            $table->index(
                [
                    'unidad_servicio_id',
                    'fecha_inicio',
                    'fecha_fin',
                ],
                'uca_unidad_fechas_idx'
            );
            
            $table->index(
                [
                    'turno_id',
                    'fecha_inicio',
                    'fecha_fin',
                ],
                'uca_turno_fechas_idx'
            );
            
            $table->index(
                'tipo_trabajo',
                'uca_tipo_idx'
            );
            
            $table->index(
                'kiosco_habilitado',
                'uca_kiosco_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_control_accesos');
    }
};