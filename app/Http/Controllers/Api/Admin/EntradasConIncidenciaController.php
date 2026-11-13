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
            'empresa_id' => ['nullable','integer','exists:empresas,id'],
            'unidad_servicio_id' => ['nullable','integer','exists:unidades_servicio,id'],
        ]);
        $query = AsistenciaEntradaIncidencia::with(['user:id,name,email', 'revisadoPor:id,name', 'asistencia', 'revisiones.actor:id,name']);
        if (($data['estado'] ?? 'todos') !== 'todos') $query->where('estado', $data['estado']);
        if (!empty($data['desde'])) $query->whereDate('fecha', '>=', $data['desde']);
        if (!empty($data['hasta'])) $query->whereDate('fecha', '<=', $data['hasta']);
        if (!empty($data['buscar'])) {
            $term = $data['buscar'];
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }
        // Usar snapshots laborales de la asistencia. Si fue eliminada,
        // consultar la configuración laboral histórica de ese día.
        $empresaId = $data['empresa_id'] ?? null;
        $unidadId = $data['unidad_servicio_id'] ?? null;
        if ($empresaId || $unidadId) {
            $query->where(function ($q) use ($empresaId, $unidadId) {
                $q->whereHas('asistencia', function ($asistencia) use ($empresaId, $unidadId) {
                    if ($empresaId) {
                        $asistencia->where('empresa_id_snapshot', (int) $empresaId);
                    }
                    if ($unidadId) {
                        $asistencia->where('unidad_servicio_id_snapshot', (int) $unidadId);
                    }
                })->orWhere(function ($deleted) use ($empresaId, $unidadId) {
                    $deleted->whereDoesntHave('asistencia')
                        ->whereExists(function ($sub) use ($empresaId, $unidadId) {
                            $sub->selectRaw('1')
                                ->from('user_control_accesos as uca_filtro')
                                ->whereColumn('uca_filtro.user_id', 'asistencia_entradas_incidencia.user_id')
                                ->whereColumn('uca_filtro.fecha_inicio', '<=', 'asistencia_entradas_incidencia.fecha')
                                ->where(function ($vigencia) {
                                    $vigencia->whereNull('uca_filtro.fecha_fin')
                                        ->orWhereColumn('uca_filtro.fecha_fin', '>=', 'asistencia_entradas_incidencia.fecha');
                                });
                            if ($empresaId) {
                                $sub->where('uca_filtro.empresa_id', (int) $empresaId);
                            }
                            if ($unidadId) {
                                $sub->where('uca_filtro.unidad_servicio_id', (int) $unidadId);
                            }
                        });
                });
            });
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
            'filtros' => [
                'empresas' => \App\Models\Empresa::query()->orderBy('nombre')->get(['id', 'nombre']),
                'unidades' => \App\Models\UnidadServicio::query()->orderBy('nombre')->get(['id', 'nombre']),
            ],
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
