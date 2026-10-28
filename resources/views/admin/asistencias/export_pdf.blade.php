<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Reporte de Asistencias
    </title>

    <style>
        @page {
            margin: 18px 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family:
                DejaVu Sans,
                sans-serif;
            font-size: 7px;
            color: #111827;
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .title {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .subtitle {
            font-size: 8px;
            color: #475569;
            margin: 0;
        }

        .summary {
            margin-bottom: 8px;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 3px 2px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
        }

        td {
            text-align: center;
        }

        .employee {
            text-align: center;
        }

        .email {
            font-size: 6px;
            color: #475569;
            text-align: center;

        }

        .empty {
            text-align: center;
            padding: 14px;
            color: #64748b;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">
            REPORTE DE ASISTENCIAS
        </div>

        <p class="subtitle">
            Generado:
            {{ $generatedAt }}
        </p>
    </div>

    <div class="summary">
        Total de registros:
        <strong>
            {{ $rows->count() }}
        </strong>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">
                    Fecha
                </th>
                
                <th style="width: 8%;">
                    Empleado
                </th>
                
                <th style="width: 6%;">
                    Empresa laboral
                </th>
                
                <th style="width: 5%;">
                    Unidad
                </th>
                
                <th style="width: 4%;">
                    Turno
                </th>
        
                <th style="width: 4%;">
                    Entrada
                </th>
        
                <th style="width: 4%;">
                    Inicio comida
                </th>
        
                <th style="width: 4%;">
                    Fin comida
                </th>
        
                <th style="width: 4%;">
                    Salida
                </th>
        
                <th style="width: 4%;">
                    Presencia
                </th>
        
                <th style="width: 4%;">
                    Comida
                </th>
        
                <th style="width: 4%;">
                    Trabajado
                </th>
        
                <th style="width: 5%;">
                    Estado día
                </th>
                
                <th style="width: 5%;">
                    Entrada
                </th>
                
                <th style="width: 5%;">
                    Salida
                </th>
        
                <th style="width: 4%;">
                    Comida
                </th>
        
                <th style="width: 5%;">
                    Jornada
                </th>
        
                <th style="width: 5%;">
                    Método
                </th>
        
                <th style="width: 6%;">
                    Incidencia
                </th>
        
                <th style="width: 9%;">
                    Observaciones incidencia
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="nowrap">
                        {{ $row['fecha'] }}
                    </td>

                    <td class="employee">
                        <strong>
                            {{ $row['empleado'] }}
                        </strong>
                    
                        @if (
                            !empty($row['correo']) &&
                            $row['correo'] !== '—'
                        )
                            <div class="email">
                                {{ $row['correo'] }}
                            </div>
                        @endif
                    </td>
                    
                    <td>
                        {{ $row['empresa'] }}
                    </td>
                    
                    <td>
                        {{ $row['unidad'] }}
                    </td>

                    <td>
                        {{ $row['turno'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['entrada'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['inicio_comida'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['fin_comida'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['salida'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['presencia'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['comida'] }}
                    </td>

                    <td class="nowrap">
                        {{ $row['trabajado'] }}
                    </td>

                    <td>
                        {{ $row['estado_dia'] }}
                    </td>

                    <td>
                        {{ $row['estado_entrada'] }}
                    </td>

                    <td>
                        {{ $row['estado_salida'] }}
                    </td>

                    <td>
                        {{ $row['estado_comida'] }}
                    </td>

                    <td>
                        {{ $row['estado_jornada'] }}
                    </td>
                    
                    <td>
                        {{ $row['metodo_entrada'] }}
                    </td>
                    
                    <td>
                        {{ $row['incidencia_tipo'] }}
                    </td>
                    
                    <td>
                        {{ $row['incidencia_observaciones'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="20"
                        class="empty"
                    >
                        No se encontraron registros
                        con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>