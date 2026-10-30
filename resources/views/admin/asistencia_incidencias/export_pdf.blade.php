<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <title>
        Reporte de Incidencias de Asistencia
    </title>

    <style>
        @page {
            margin: 18px;
            size: A4 landscape;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            color: #0f172a;
        }

        .title {
            margin: 0;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
        }

        .generated {
            margin-top: 5px;
            margin-bottom: 12px;
            text-align: center;
            font-size: 8px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 5px 4px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background: #e2e8f0;
            text-align: center;
            font-weight: bold;
        }

        td {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .employee {
            font-weight: bold;
        }

        .empty {
            padding: 18px;
            text-align: center;
            color: #64748b;
        }

        .col-employee {
            width: 17%;
        }

        .col-email {
            width: 18%;
        }

        .col-type {
            width: 10%;
        }

        .col-date {
            width: 9%;
        }

        .col-days {
            width: 5%;
        }

        .col-observations {
            width: 20%;
        }

        .col-created-by {
            width: 12%;
        }
    </style>
</head>

<body>
    <h1 class="title">
        REPORTE DE INCIDENCIAS DE ASISTENCIA
    </h1>

    <div class="generated">
        Generado: {{ $generatedAt }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-employee">
                    Empleado
                </th>

                <th class="col-email">
                    Correo
                </th>

                <th class="col-type">
                    Tipo
                </th>

                <th class="col-date">
                    Desde
                </th>

                <th class="col-date">
                    Hasta
                </th>

                <th class="col-days">
                    Días
                </th>

                <th class="col-observations">
                    Observaciones
                </th>

                <th class="col-created-by">
                    Registrado por
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="employee">
                        {{ $row['empleado'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['correo'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['tipo'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['fecha_desde'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['fecha_hasta'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['dias'] }}
                    </td>

                    <td class="left">
                        {{ $row['observaciones'] ?: '—' }}
                    </td>

                    <td>
                        {{ $row['registrado_por'] ?: '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="8"
                        class="empty"
                    >
                        No hay incidencias para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>