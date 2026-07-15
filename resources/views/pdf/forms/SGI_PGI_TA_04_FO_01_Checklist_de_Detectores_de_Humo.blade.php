<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Detectores de Humo' }}</title>

    <style>
        @page {
            margin: 18px;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .sheet {
            width: 100%;
            margin: 0;
        }

        .page-break {
            page-break-after: always;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            text-align: center;
            line-height: 1.1;
        }

        .logo-cell {
            width: 25%;
            padding: 4px 5px;
        }

        .logo-cell img {
            max-width: 100%;
            max-height: 62px;
            object-fit: contain;
        }

        .center-cell {
            font-weight: bold;
        }

        .header-table td.right-cell {
            font-weight: bold;
            text-align: center !important;
            padding-left: 8px;
        }
    </style>
</head>

<body>

@php
    $fieldsCollection = collect($fields ?? []);
    $getField = fn($id) => $fieldsCollection->firstWhere('id', $id);

    $logo = $getField('encabezado_logo');
    $logoSrc = null;

    if ($logo && !empty($logo['url'])) {
        $path = public_path(ltrim($logo['url'], '/'));

        if (file_exists($path)) {
            $logoSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($path));
        }
    }

    $tablaDetectores = $answers['tabla_detectores_humo'] ?? [];

    if (!is_array($tablaDetectores)) {
        $tablaDetectores = [];
    }

    $tablaDetectores = array_values($tablaDetectores);

    $registrosPorPagina = 10;
    $paginasDetectores = array_chunk(
        $tablaDetectores,
        $registrosPorPagina
    );

    // Si no hay registros, se conserva una hoja con 10 filas vacías.
    if (count($paginasDetectores) === 0) {
        $paginasDetectores = [[]];
    }

    $totalPaginas = count($paginasDetectores);
@endphp

@foreach($paginasDetectores as $paginaIndex => $registrosPagina)
<div class="sheet">

    <!-- HEADER -->
    <table class="header-table">
        <!-- CONTROL DE ANCHOS: 4 COLUMNAS -->
        <tr style="height:0; line-height:0;">
            <td style="width:25%; padding:0; border:none; height:0;"></td>
            <td style="width:45%; padding:0; border:none; height:0;"></td>
            <td style="width:15%; padding:0; border:none; height:0;"></td>
            <td style="width:15%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <!-- FILA 1 -->
        <tr>
            <!-- LOGO UTILIZA LAS 4 FILAS -->
            <td rowspan="4" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>
    
            <td class="center-cell">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>
    
            <!-- PÁGINA USA COLUMNAS 3 Y 4 -->
            <td colspan="2" class="right-cell">
                PÁGINA:
            </td>
        </tr>
    
        <!-- FILA 2 -->
        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>
    
            <!-- PÁGINA {{ $paginaIndex + 1 }} DE {{ $totalPaginas }} USA COLUMNAS 3 Y 4 -->
            <td colspan="2" class="right-cell">
                PÁGINA {{ $paginaIndex + 1 }} DE {{ $totalPaginas }}
            </td>
        </tr>
    
        <!-- FILA 3: CODIFICACIÓN -->
        <tr>
            <!-- EL TÍTULO USA LAS FILAS 3 Y 4 -->
            <td rowspan="2" class="center-cell">
                CHECKLIST DE DETECTORES DE HUMO
            </td>
    
            <!-- CODIFICACIÓN USA COLUMNAS 3 Y 4 -->
            <td colspan="2" class="right-cell">
                CODIFICACIÓN: SGI-PGI-TA-04-FO-01
            </td>
        </tr>
    
        <!-- FILA 4: REVISIÓN Y FECHA DE EMISIÓN -->
        <tr>
            <!-- COLUMNA 3 -->
            <td class="right-cell">
                NÚMERO DE REVISIÓN: 03
            </td>
    
            <!-- COLUMNA 4 -->
            <td class="right-cell">
                FECHA DE EMISIÓN:<br>27/03/2025
            </td>
        </tr>
    </table>

    <!-- TALLER - FECHA -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:8px;
        margin-top:12px;
    ">
        <tr>
            <!-- ESPACIO IZQUIERDO -->
            <td style="
                width:15%;
                border:none;
            ">
            </td>
    
            <!-- TALLER -->
            <td style="
                width:30%;
                border:none;
                text-align:center;
                vertical-align:bottom;
            ">
                <strong>US / EMPRESA:</strong>
    
                <span style="
                    display:inline-block;
                    min-width:180px;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                ">
                    {{ $answers['taller'] ?? '' }}
                </span>
            </td>
    
            <!-- ESPACIO CENTRAL -->
            <td style="
                width:7%;
                border:none;
            ">
            </td>
    
            <!-- FECHA -->
            <td style="
                width:34%;
                border:none;
                text-align:center;
                vertical-align:bottom;
            ">
                <strong>FECHA DE INSPECCIÓN:</strong>
    
                <span style="
                    display:inline-block;
                    min-width:180px;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                ">
                    {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                </span>
            </td>
    
            <!-- ESPACIO DERECHO -->
            <td style="
                width:14%;
                border:none;
            ">
            </td>
        </tr>
    </table>

    <!-- NOMBRE INSPECTOR - FIRMA -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:8px;
        margin-top:30px;
    ">
        <tr>
            <!-- ESPACIO IZQUIERDO -->
            <td style="
                width:10%;
                border:none;
            ">
            </td>
    
            <!-- NOMBRE INSPECTOR -->
            <td style="
                width:35%;
                border:none;
                text-align:center;
                vertical-align:bottom;
            ">
                <strong>NOMBRE DEL INSPECTOR:</strong>
    
                <span style="
                    display:inline-block;
                    min-width:180px;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                ">
                    {{ $answers['nombre_inspector'] ?? '' }}
                </span>
            </td>
    
            <!-- ESPACIO CENTRAL -->
            <td style="
                width:10%;
                border:none;
            ">
            </td>
    
            <!-- FIRMA -->
            <td style="
                width:35%;
                border:none;
                text-align:center;
                vertical-align:bottom;
            ">
                <strong style="
                    display:inline-block;
                    position:relative;
                    top:10px;
                ">
                    FIRMA:
                </strong>
            
                @php
                    $firmaInspector = $answers['firma_inspector'] ?? '';
                    $firmaInspectorSrc = null;
            
                    if (!empty($firmaInspector)) {
                        $firmaPath = storage_path('app/public/' . $firmaInspector);
            
                        if (file_exists($firmaPath)) {
                            $firmaInspectorSrc =
                                'data:image/png;base64,' .
                                base64_encode(file_get_contents($firmaPath));
                        }
                    }
                @endphp
            
                <div style="
                    display:inline-block;
                    width:180px;
                    text-align:center;
                    vertical-align:bottom;
                ">
                    @if($firmaInspectorSrc)
                        <img src="{{ $firmaInspectorSrc }}" style="
                            width:150px;
                            height:35px;
                            object-fit:contain;
                            display:block;
                            margin:0 auto;
                            margin-bottom:-5px;
                        ">
                    @endif
            
                    <div style="
                        border-bottom:1px solid #000;
                        width:100%;
                        height:1px;
                    "></div>
                </div>
            </td>
    
            <!-- ESPACIO DERECHO -->
            <td style="
                width:10%;
                border:none;
            ">
            </td>
        </tr>
    </table>

    <!-- INSTRUCCIONES -->
    <div style="
        width:100%;
        text-align:center;
        margin-top:20px;
        font-size:8px;
    ">
        <strong>INSTRUCCIONES:</strong>
    
        <strong>( ✔ )</strong> SI, BUENAS CONDICIONES,
    
        <strong>( X )</strong> NO, MALAS CONDICIONES,
    
        <strong>( N/A )</strong> NO APLICA
    </div>

    @php
        // Alturas
        $altoEncabezado = 20;
        $altoFila = 14;
    
        // Anchos
        $wNo            = '5%';
        $wUbicacion     = '20%';
        $wEstado        = '9%';
        $wObstrucciones = '9%';
        $wPrueba        = '9%';
        $wBateria       = '9%';
        $wSeparacion    = '9%';
        $wLimpieza      = '9%';
        $wObservaciones = '21%';
    @endphp
    
    <!-- TABLA DETECTORES DE HUMO -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:7px;
        margin-top:5px;
    ">
        <!-- ENCABEZADOS -->
        <tr style="height:{{ $altoEncabezado }}px;">
    
            <td style="
                width:{{ $wNo }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                No.
            </td>
    
            <td style="
                width:{{ $wUbicacion }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Ubicación
            </td>
    
            <td style="
                width:{{ $wEstado }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Estado físico del detector
            </td>
    
            <td style="
                width:{{ $wObstrucciones }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Detectores sin obstrucciones
            </td>
    
            <td style="
                width:{{ $wPrueba }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Prueba botón de alarma
            </td>
    
            <td style="
                width:{{ $wBateria }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Batería del detector
            </td>
    
            <td style="
                width:{{ $wSeparacion }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Separación de detectores
            </td>
    
            <td style="
                width:{{ $wLimpieza }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Limpieza del detector
            </td>
    
            <td style="
                width:{{ $wObservaciones }};
                height:{{ $altoEncabezado }}px;
                border:1px solid #000;
                background:#C0EEFA;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
            ">
                Observaciones
            </td>
        </tr>
    
        <!-- FILAS DE DATOS -->
        @for($i = 0; $i < 10; $i++)
    
            @php
                $row = $registrosPagina[$i] ?? [];
            @endphp
    
            <tr style="height:{{ $altoFila }}px;">
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['numero_detectores_humo'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['ubicacion_detector_humo'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['estado_fisico_detector'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['detectores_sin_obstrucciones'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['prueba_boton_alarma'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['bateria_detector'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['separacion_detectores'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['limpieza_detector'] ?? '' }}
                </td>
    
                <td style="
                    height:{{ $altoFila }}px;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ $row['observaciones'] ?? '' }}
                </td>
            </tr>
        @endfor
    </table>

    @php
        // CONFIGURACIÓN TABLA DISTANCIAS
        $anchoTablaDistancias = '70%';
    
        $altoDistHeader1 = 20;
        $altoDistHeader2 = 18;
        $altoDistFila = 18;
    
        $c1 = '12%';
        $c2 = '12%';
        $c3 = '9.33%';
    
        $c4 = '12%';
        $c5 = '12%';
        $c6 = '9.33%';
    
        $c7 = '12%';
        $c8 = '12%';
        $c9 = '9.33%';
    
        $distancias = [
            ['0', '3', '9', '4.89', '5.94', '6.39', '7.33', '7.92', '4.14'],
            ['3.01', '3.66', '8.19', '5.95', '6.1', '5.76', '7.93', '8.53', '3.6'],
            ['3.67', '4.27', '7.56', '6.11', '6.71', '5.22', '9.15', 'en adelante', 'detección lineal'],
            ['4.28', '4.88', '6.93', '6.72', '7.32', '4.68', '', '', ''],
        ];
    
        $anchosDistancias = [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9];
    @endphp
    
    <!-- TABLA DISTANCIA MÁXIMA ENTRE DETECTORES -->
    <table style="
        width:{{ $anchoTablaDistancias }};
        margin:20px auto 0;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:7px;
    ">
        <tr style="height:{{ $altoDistHeader1 }}px;">
            <td colspan="2" style="width:{{ $c1 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">
                ALTURA DE TECHO (MTS)
            </td>
    
            <td rowspan="2" style="width:{{ $c3 }}; border:1px solid #000; background:#5E9DD6; font-weight:bold; text-align:center; vertical-align:middle;">
                DISTANCIA MÁXIMA ENTRE DETECTORES
            </td>
    
            <td colspan="2" style="width:{{ $c4 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">
                ALTURA DE TECHO (MTS)
            </td>
    
            <td rowspan="2" style="width:{{ $c6 }}; border:1px solid #000; background:#5E9DD6; font-weight:bold; text-align:center; vertical-align:middle;">
                DISTANCIA MÁXIMA ENTRE DETECTORES
            </td>
    
            <td colspan="2" style="width:{{ $c7 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">
                ALTURA DE TECHO (MTS)
            </td>
    
            <td rowspan="2" style="width:{{ $c9 }}; border:1px solid #000; background:#5E9DD6; font-weight:bold; text-align:center; vertical-align:middle;">
                DISTANCIA MÁXIMA ENTRE DETECTORES
            </td>
        </tr>
    
        <tr style="height:{{ $altoDistHeader2 }}px;">
            <td style="width:{{ $c1 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">DE</td>
            <td style="width:{{ $c2 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">HASTA</td>
            <td style="width:{{ $c4 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">DE</td>
            <td style="width:{{ $c5 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">HASTA</td>
            <td style="width:{{ $c7 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">DE</td>
            <td style="width:{{ $c8 }}; border:1px solid #000; background:#dbdcdf; font-weight:bold; text-align:center; vertical-align:middle;">HASTA</td>
        </tr>
    
        @foreach($distancias as $fila)
            <tr style="height:{{ $altoDistFila }}px;">
                @foreach($fila as $index => $valor)
                    <td style="
                        width:{{ $anchosDistancias[$index] }};
                        border:1px solid #000;
                        font-weight:bold;
                        text-align:center;
                        vertical-align:middle;
                    ">
                        {{ $valor }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>


</div>

@if(!$loop->last)
    <div class="page-break"></div>
@endif
@endforeach

</body>
</html>
