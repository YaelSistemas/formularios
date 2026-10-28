<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TurnosController extends Controller
{
    /**
     * Listado de turnos.
     */
    public function index(Request $request)
    {
        $query = Turno::query()
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->orderByDesc('vigente_desde');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'turnos' => $query->get(),
        ]);
    }

    /**
     * Ver un turno.
     */
    public function show(Turno $turno)
    {
        return response()->json([
            'turno' => $turno,
        ]);
    }

    /**
     * Crear turno.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $turno = Turno::create($data);

        return response()->json([
            'message' => 'Turno creado correctamente.',
            'turno' => $turno,
        ], 201);
    }

    /**
     * Actualizar turno.
     */
    public function update(
        Request $request,
        Turno $turno
    ) {
        $data = $this->validateData($request);

        $turno->update($data);

        return response()->json([
            'message' => 'Turno actualizado correctamente.',
            'turno' => $turno->fresh(),
        ]);
    }

    /**
     * Eliminar turno.
     */
    public function destroy(Turno $turno)
    {
        $turno->delete();

        return response()->json([
            'message' => 'Turno eliminado correctamente.',
        ]);
    }

    /**
     * Validaciones comunes de crear / editar.
     */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hora_inicio' => [
                'required',
                'date_format:H:i',
            ],

            'hora_fin' => [
                'required',
                'date_format:H:i',
            ],

            'horas_jornada' => [
                'required',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            'minutos_comida' => [
                'required',
                'integer',
                'min:0',
                'max:600',
            ],

            'comida_cuenta_jornada' => [
                'required',
                'boolean',
            ],

            'lunes' => [
                'required',
                'boolean',
            ],

            'martes' => [
                'required',
                'boolean',
            ],

            'miercoles' => [
                'required',
                'boolean',
            ],

            'jueves' => [
                'required',
                'boolean',
            ],

            'viernes' => [
                'required',
                'boolean',
            ],

            'sabado' => [
                'required',
                'boolean',
            ],

            'domingo' => [
                'required',
                'boolean',
            ],

            'vigente_desde' => [
                'required',
                'date',
            ],

            'vigente_hasta' => [
                'nullable',
                'date',
                'after_or_equal:vigente_desde',
            ],

            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        /*
         * El turno debe tener por lo menos
         * un día laborable.
         */
        $dias = [
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo',
        ];

        $tieneDiaLaborable = collect($dias)
            ->contains(
                fn ($dia) =>
                    (bool) ($data[$dia] ?? false)
            );

        if (!$tieneDiaLaborable) {
            throw ValidationException::withMessages([
                'dias' =>
                    'Selecciona al menos un día laborable.',
            ]);
        }

        return $data;
    }
}