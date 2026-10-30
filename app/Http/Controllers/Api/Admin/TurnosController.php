<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\TurnoHistory;
use App\Models\UserControlAcceso;
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
     * Historial de un turno.
     */
    public function history(Turno $turno)
    {
        $history = TurnoHistory::query()
            ->where(
                'turno_id',
                $turno->id
            )
            ->with([
                'actor:id,name,email',
            ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function (TurnoHistory $item) {
                return [
                    'id' =>
                        $item->id,
    
                    'action' =>
                        $item->action,
    
                    'changes' =>
                        $item->changes ?? [],
    
                    'snapshot' =>
                        $item->snapshot,
    
                    'created_at' =>
                        $item->created_at
                            ?->format(
                                'Y-m-d H:i:s'
                            ),
    
                    'actor' =>
                        $item->actor
                            ? [
                                'id' =>
                                    $item->actor->id,
    
                                'name' =>
                                    $item->actor->name,
    
                                'email' =>
                                    $item->actor->email,
                            ]
                            : null,
                ];
            })
            ->values();
    
        return response()->json([
            'turno' => [
                'id' =>
                    $turno->id,
    
                'nombre' =>
                    $turno->nombre,
            ],
    
            'history' =>
                $history,
        ]);
    }

    /**
     * Crear turno.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
    
        $turno = Turno::create($data);
    
        TurnoHistory::create([
            'turno_id' => $turno->id,
            'action' => 'created',
            'changes' => null,
            'snapshot' => $this->buildSnapshot($turno),
            'actor_user_id' => $request->user()?->id,
        ]);
    
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
    
        $before = $this->buildSnapshot($turno);
    
        $turno->update($data);
    
        $turno->refresh();
    
        $after = $this->buildSnapshot($turno);
    
        $changes = $this->buildChanges(
            $before,
            $after
        );
    
        if (!empty($changes)) {
            TurnoHistory::create([
                'turno_id' => $turno->id,
                'action' => 'updated',
                'changes' => $changes,
                'snapshot' => $after,
                'actor_user_id' => $request->user()?->id,
            ]);
        }
    
        return response()->json([
            'message' => 'Turno actualizado correctamente.',
            'turno' => $turno,
        ]);
    }

    /**
     * Eliminar turno.
     */
    public function destroy(Turno $turno)
    {
        $estaEnUso = UserControlAcceso::where(
            'turno_id',
            $turno->id
        )->exists();
    
        if ($estaEnUso) {
            return response()->json([
                'message' =>
                    'No se puede eliminar este turno porque está asociado a uno o más registros de control de acceso. Puedes desactivarlo si ya no deseas utilizarlo.',
            ], 422);
        }
    
        $turno->delete();
    
        return response()->json([
            'message' =>
                'Turno eliminado correctamente.',
        ]);
    }

    /**
     * Estado completo de un turno para historial.
     */
    private function buildSnapshot(Turno $turno): array
    {
        return [
            'nombre' => $turno->nombre,
            'descripcion' => $turno->descripcion,
            'hora_inicio' => $turno->hora_inicio,
            'hora_fin' => $turno->hora_fin,
            'horas_jornada' => $turno->horas_jornada,
            'minutos_comida' => $turno->minutos_comida,
            'comida_cuenta_jornada' =>
                (bool) $turno->comida_cuenta_jornada,
    
            'lunes' => (bool) $turno->lunes,
            'martes' => (bool) $turno->martes,
            'miercoles' => (bool) $turno->miercoles,
            'jueves' => (bool) $turno->jueves,
            'viernes' => (bool) $turno->viernes,
            'sabado' => (bool) $turno->sabado,
            'domingo' => (bool) $turno->domingo,
    
            'vigente_desde' =>
                $turno->vigente_desde?->format('Y-m-d'),
    
            'vigente_hasta' =>
                $turno->vigente_hasta?->format('Y-m-d'),
    
            'activo' => (bool) $turno->activo,
        ];
    }
    
    /**
     * Obtener únicamente los campos modificados.
     */
    private function buildChanges(
        array $before,
        array $after
    ): array {
        $changes = [];
    
        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
    
            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'before' => $oldValue,
                    'after' => $newValue,
                ];
            }
        }
    
        return $changes;
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