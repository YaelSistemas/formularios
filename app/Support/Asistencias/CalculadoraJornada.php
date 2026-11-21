<?php

namespace App\Support\Asistencias;

/**
 * Calcula el trabajo efectivo y el cumplimiento de la jornada.
 * Todos los tiempos se manejan en segundos para conservar la precisión.
 */
final class CalculadoraJornada
{
    public static function calcular(
        int $segundosPresencia,
        int $segundosComida,
        float $horasJornada,
        bool $comidaCuentaJornada,
        int $minutosComidaPermitidos
    ): array {
        $presencia = max(0, $segundosPresencia);
        $comidaReal = max(0, min($segundosComida, $presencia));
        $trabajoEfectivo = $presencia - $comidaReal;

        // Solo el tiempo de comida autorizado puede contar para cumplir la jornada.
        // La comida NO se convierte en trabajo efectivo ni genera tiempo excedido.
        $comidaReconocida = $comidaCuentaJornada
            ? min($comidaReal, max(0, $minutosComidaPermitidos) * 60)
            : 0;
        $computable = $trabajoEfectivo + $comidaReconocida;

        $requerido = max(0, (int) round($horasJornada * 3600));
        $limiteExcedido = $requerido + 3600; // Tolerancia existente: 60 minutos.

        if ($computable < $requerido) {
            $estado = 'incompleta';
        } elseif ($trabajoEfectivo >= $limiteExcedido) {
            $estado = 'excedida';
        } else {
            $estado = 'cumplida';
        }

        return [
            'segundos_trabajados' => $trabajoEfectivo,
            'segundos_computables' => $computable,
            'estado_jornada' => $estado,
        ];
    }
}
