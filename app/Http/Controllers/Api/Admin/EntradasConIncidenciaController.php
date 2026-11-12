<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaEntradaIncidencia;
use App\Models\AsistenciaEntradaIncidenciaRevision;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EntradasConIncidenciaController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'estado' => ['nullable', Rule::in(['todos','pendiente','visto_bueno','observada','no_avalada'])],
            'desde' => ['nullable','date'], 'hasta' => ['nullable','date','after_or_equal:desde'],
            'buscar' => ['nullable','string','max:150'], 'per_page' => ['nullable','integer','between:1,100'],
        ]);
        $query = AsistenciaEntradaIncidencia::with(['user:id,name,email', 'revisadoPor:id,name', 'asistencia', 'revisiones.actor:id,name']);
        if (($data['estado'] ?? 'todos') !== 'todos') $query->where('estado', $data['estado']);
        if (!empty($data['desde'])) $query->whereDate('fecha', '>=', $data['desde']);
        if (!empty($data['hasta'])) $query->whereDate('fecha', '<=', $data['hasta']);
        if (!empty($data['buscar'])) {
            $term = $data['buscar'];
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }
        $pagina = $query->orderByDesc('fecha')->orderByDesc('id')->paginate($data['per_page'] ?? 25);
        $pagina->getCollection()->each(function ($registro) {
            $asistencia = $registro->asistencia;
        
            $registro->setAttribute(
                'asistencia_eliminada',
                $asistencia === null
            );
            $registro->setAttribute('entrada_hora', $asistencia?->getRawOriginal('hora_entrada')
                ? Carbon::parse($asistencia->getRawOriginal('hora_entrada'), 'America/Mexico_City')->format('H:i:s') : null);
            $registro->setAttribute('salida_hora', $asistencia?->getRawOriginal('hora_salida')
                ? Carbon::parse($asistencia->getRawOriginal('hora_salida'), 'America/Mexico_City')->format('H:i:s') : null);
        });
        return response()->json([
            'registros' => $pagina,
            'pendientes' => AsistenciaEntradaIncidencia::where('estado', 'pendiente')
                ->whereHas('asistencia')
                ->count(),
        ]);
    }

    public function revisar(Request $request, AsistenciaEntradaIncidencia $entradaIncidencia)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['visto_bueno','observada','no_avalada'])],
            'observaciones' => ['required','string','min:5','max:2000'],
        ]);
        DB::transaction(function () use ($entradaIncidencia, $request, $data) {
        
            $registro = AsistenciaEntradaIncidencia::whereKey($entradaIncidencia->id)
                ->lockForUpdate()
                ->firstOrFail();
        
            if (!$registro->asistencia_id || !$registro->asistencia()->exists()) {
                abort(
                    409,
                    'No se puede revisar esta entrada extraordinaria porque la asistencia fue eliminada. El historial permanece disponible para consulta.'
                );
            }
        
            $anterior = $registro->estado;
            $registro->update([
                'estado' => $data['estado'], 'observaciones_revision' => trim($data['observaciones']),
                'revisado_por_user_id' => $request->user()->id, 'revisado_at' => now(),
            ]);
            AsistenciaEntradaIncidenciaRevision::create([
                'entrada_incidencia_id' => $registro->id, 'actor_user_id' => $request->user()->id,
                'estado_anterior' => $anterior, 'estado_nuevo' => $data['estado'],
                'observaciones' => trim($data['observaciones']),
            ]);
        });
        return response()->json(['message' => 'Revisión registrada. La asistencia y la incidencia permanecen intactas.']);
    }
}
