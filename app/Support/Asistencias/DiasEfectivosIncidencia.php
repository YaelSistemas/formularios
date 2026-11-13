<?php

namespace App\Support\Asistencias;

use App\Models\AsistenciaIncidencia;
use App\Models\UserControlAcceso;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Cuenta los días efectivos sin alterar las fechas o filas de incidencia.
 * Para vacaciones exige un turno vigente para cada día: nunca asume
 * automáticamente que domingo es descanso cuando falta configuración.
 */
class DiasEfectivosIncidencia
{
    private static array $configuraciones = [];
    private static array $festivos = [];

    public static function paraGrupo(Collection $registros): ?int
    {
        if ($registros->isEmpty()) {
            return 0;
        }

        $porEmpleado = $registros->groupBy('user_id');
        $conteos = [];

        foreach ($porEmpleado as $userId => $items) {
            // Un grupo puede incluir periodos personalizados de distinto tipo.
            $total = 0;
            foreach ($items->groupBy('tipo') as $tipo => $porTipo) {
                $fechas = $porTipo->pluck('fecha')
                    ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
                    ->unique();

                foreach ($fechas as $fecha) {
                    $cuenta = self::cuentaFecha((int) $userId, $tipo, $fecha);
                    if ($cuenta === null) {
                        $total = null;
                        break 2;
                    }
                    $total += $cuenta;
                }
            }
            $conteos[] = $total;
        }

        // Para incidencias masivas con distintos turnos, un único número
        // sería engañoso: se expone null y el detalle sí muestra cada conteo.
        return count(array_unique($conteos, SORT_REGULAR)) === 1
            ? $conteos[0]
            : null;
    }

    public static function paraPeriodo(int $userId, string $tipo, string $desde, string $hasta): ?int
    {
        $total = 0;
        for ($fecha = Carbon::parse($desde)->startOfDay(), $fin = Carbon::parse($hasta)->startOfDay();
             $fecha->lte($fin);
             $fecha->addDay()) {
            $cuenta = self::cuentaFecha($userId, $tipo, $fecha->toDateString());
            if ($cuenta === null) {
                return null;
            }
            $total += $cuenta;
        }
        return $total;
    }

    private static function cuentaFecha(int $userId, string $tipo, string $fecha): ?int
    {
        if ($tipo !== 'vacaciones') {
            return 1;
        }

        if (!array_key_exists($userId, self::$festivos)) {
            self::$festivos[$userId] = AsistenciaIncidencia::query()
                ->where('user_id', $userId)
                ->where('tipo', 'festivo')
                ->pluck('fecha')
                ->mapWithKeys(fn ($dia) => [Carbon::parse($dia)->toDateString() => true])
                ->all();
        }

        if (isset(self::$festivos[$userId][$fecha])) {
            return 0;
        }

        if (!array_key_exists($userId, self::$configuraciones)) {
            self::$configuraciones[$userId] = UserControlAcceso::query()
                ->with('turno')
                ->where('user_id', $userId)
                ->orderByDesc('fecha_inicio')
                ->orderByDesc('id')
                ->get();
        }

        $config = self::$configuraciones[$userId]->first(function ($registro) use ($fecha) {
            $inicio = Carbon::parse($registro->fecha_inicio)->toDateString();
            $fin = $registro->fecha_fin
                ? Carbon::parse($registro->fecha_fin)->toDateString()
                : null;

            return $inicio <= $fecha && (!$fin || $fin >= $fecha);
        });

        $turno = $config?->turno;
        if (!$turno) {
            return null;
        }

        $desdeTurno = $turno->vigente_desde
            ? Carbon::parse($turno->vigente_desde)->toDateString()
            : null;
        $hastaTurno = $turno->vigente_hasta
            ? Carbon::parse($turno->vigente_hasta)->toDateString()
            : null;
        if (($desdeTurno && $fecha < $desdeTurno) || ($hastaTurno && $fecha > $hastaTurno)) {
            return null;
        }

        $diaSemana = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
            5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];

        return (bool) $turno->{$diaSemana[Carbon::parse($fecha)->dayOfWeekIso]} ? 1 : 0;
    }
}
