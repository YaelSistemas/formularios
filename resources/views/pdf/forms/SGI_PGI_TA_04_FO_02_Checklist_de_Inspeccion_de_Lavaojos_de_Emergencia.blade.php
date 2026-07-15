<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Inspección de Lavaojos de Emergencia' }}</title>

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
            text-align: left !important;
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

    $tablaLavaojos = $answers['tabla_lavaojos_emergencia'] ?? [];

    if (!is_array($tablaLavaojos)) {
        $tablaLavaojos = [];
    }

    $tablaLavaojos = array_values($tablaLavaojos);

    $registrosPorPagina = 10;
    $paginasLavaojos = array_chunk($tablaLavaojos, $registrosPorPagina);

    // Si no existen registros, se conserva una hoja con 10 filas vacías.
    if (count($paginasLavaojos) === 0) {
        $paginasLavaojos = [[]];
    }

    $totalPaginas = count($paginasLavaojos);

    $observacionesGenerales = $answers['observaciones_generales'] ?? '';
    $nombreInspector = $answers['nombre_inspector'] ?? '';
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

@foreach($paginasLavaojos as $paginaIndex => $registrosPagina)
<div class="sheet">

    <!-- HEADER -->
    <table class="header-table">
        <!-- CONTROL DE ANCHOS -->
        <tr style="height:0; line-height:0;">
            <td style="width:25%; padding:0; border:none; height:0;"></td>
            <td style="width:45%; padding:0; border:none; height:0;"></td>
            <td style="width:30%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <!-- FILA 1: CÓDIGO -->
        <tr>
            <!-- LOGO ABARCA LAS 4 FILAS -->
            <td rowspan="4" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>
    
            <td class="center-cell">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>
    
            <td class="right-cell">
                CÓDIGO: SGI-PGI-TA-04-FO-02
            </td>
        </tr>
    
        <!-- FILA 2: FECHA DE EMISIÓN -->
        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>
    
            <td class="right-cell">
                FECHA EMISIÓN: 27/03/2025
            </td>
        </tr>
    
        <!-- FILA 3: REVISIÓN -->
        <tr>
            <!-- EL TÍTULO ABARCA REVISIÓN Y PÁGINA -->
            <td rowspan="2" class="center-cell">
                INSPECCIÓN DE LAVAOJOS DE EMERGENCIA
            </td>
    
            <td class="right-cell">
                REVISIÓN: 03
            </td>
        </tr>
    
        <!-- FILA 4: PÁGINA -->
        <tr>
            <td class="right-cell">
                PÁGINA: {{ str_pad($paginaIndex + 1, 2, '0', STR_PAD_LEFT) }}
                DE {{ str_pad($totalPaginas, 2, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

    <!-- FECHA - TALLER -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:8px;
        margin-top:10px;
    ">
        <tr>
            <!-- COLUMNA 1 -->
            <td style="
                width:15%;
                border:1px solid #000;
                border:none;
                padding:6px 8px;
                text-align:center;
            ">
            </td>
    
            <!-- TALLER -->
            <td style="
                width:30%;
                border:1px solid #000;
                padding:6px 8px;
                text-align:center;
            ">
                <strong>TALLER/UNIDA DE SERVICIO:</strong>
                {{ $answers['taller'] ?? '' }}
            </td>
    
            <!-- COLUMNA 3 -->
            <td style="
                width:10%;
                border:1px solid #000;
                border:none;
                padding:6px 8px;
                text-align:center;
            ">
            </td>
    
            <!-- FECHA -->
            <td style="
                width:30%;
                border:1px solid #000;
                padding:6px 8px;
                text-align:center;
            ">
                <strong>FECHA:</strong>
                {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
            </td>
    
            <!-- COLUMNA 5 -->
            <td style="
                width:15%;
                border:1px solid #000;
                border:none;
                padding:6px 8px;
                text-align:center;
            ">
            </td>
        </tr>
    </table>

    <!-- TABLA LAVAOJOS -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:7px;
        margin-top:10px;
    ">
        <!-- FILA FANTASMA PARA 12 COLUMNAS -->
        <tr style="height:0; line-height:0;">
            <td style="width:15%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:6%; padding:0; border:none; height:0;"></td>
            <td style="width:25%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <!-- FILA 1 -->
        <tr style="height:34px;">
            <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                Lavaojos de Emergencia
            </td>
    
            <td colspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                ¿Ducha o Estación lavaojos limpia y equipada?
            </td>
    
            <td colspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                ¿Se garantiza flujo continuo?
            </td>
    
            <td colspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                ¿Se encuentra debidamente señalizada?
            </td>
    
            <td colspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                ¿Agua limpia y acondicionada?
            </td>
    
            <td colspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                ¿El acceso se encuentra libre de Obstáculos?
            </td>
    
            <td rowspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                Observaciones
            </td>
        </tr>
    
        <!-- FILA 2 -->
        <tr style="height:22px;">
            <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle;">
                N°
            </td>
    
            @for($i = 1; $i <= 5; $i++)
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle;">
                    Si
                </td>
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle;">
                    No
                </td>
            @endfor
        </tr>
    
        <!-- FILAS 3 A 12 -->
        @php
            $altoFila = 20;
        @endphp
        
        @for($i = 0; $i < 10; $i++)
            @php
                $row = $registrosPagina[$i] ?? [];
        
                $numero = $row['numero_lavaojos_emergencia'] ?? '';
        
                $limpiaEquipada = $row['ducha_estacion_limpia_equipada'] ?? '';
                $flujoContinuo = $row['flujo_continuo'] ?? '';
                $senalizado = $row['debidamente_senalizado'] ?? '';
                $aguaLimpia = $row['agua_limpia_acondicionada'] ?? '';
                $accesoLibre = $row['acceso_libre_obstaculos'] ?? '';
        
                $observaciones = $row['observaciones'] ?? '';
            @endphp
        
            <tr>
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:3px;
                    height:{{ $altoFila }}px;
                ">
                    {!! $numero ?: '&nbsp;' !!}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $limpiaEquipada === 'Si' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $limpiaEquipada === 'No' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $flujoContinuo === 'Si' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $flujoContinuo === 'No' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $senalizado === 'Si' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $senalizado === 'No' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $aguaLimpia === 'Si' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $aguaLimpia === 'No' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $accesoLibre === 'Si' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $altoFila }}px;
                ">
                    {{ $accesoLibre === 'No' ? 'X' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:3px;
                    height:{{ $altoFila }}px;
                ">
                    {!! $observaciones ?: '&nbsp;' !!}
                </td>
            </tr>
        @endfor
    </table>

    <!-- OBSERVACIONES GENERALES Y FIRMA -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:8px;
        margin-top:10px;
    ">
        <!-- FILA 1 -->
        <tr style="height:18px;">
            <td style="
                width:60%;
                border:1px solid #000;
                border-bottom:none;
                text-align:left;
                vertical-align:middle;
                padding:2px 4px;
                font-weight:bold;
            ">
                Observaciones generales de Inspección:
            </td>
    
            <td colspan="2" style="
                width:40%;
                border:none;
                padding:0;
            ">
                &nbsp;
            </td>
        </tr>
    
        <!-- FILA 2 -->
        <tr style="height:40px;">
            <td rowspan="2" style="
                border:1px solid #000;
                border-top:none;
                text-align:left;
                vertical-align:top;
                padding:6px;
            ">
                {{ $observacionesGenerales }}
            </td>
    
            <td style="
                width:15%;
                border:1px solid #000;
                border:none;
                text-align:center;
                vertical-align:middle;
                padding:6px 8px;
                font-weight:bold;
            ">
                Nombre del Inspector:
            </td>
    
            <td style="
                width:25%;
                border:1px solid #000;
                border:none;
                text-align:center;
                vertical-align:bottom;
                padding:6px 8px;
            ">
                <div style="border-bottom:1px solid #000;">
                    {{ $nombreInspector }}
                </div>
            </td>
        </tr>
    
        <!-- FILA 3 -->
        <tr style="height:60px;">
            <td style="
                border:1px solid #000;
                border:none;
                text-align:center;
                vertical-align:bottom;
                padding:6px 8px;
                font-weight:bold;
            ">
                Firma del Inspector:
            </td>
    
            <td style="
                border:1px solid #000;
                border:none;
                text-align:center;
                vertical-align:bottom;
                padding:6px 8px;
            ">
                <div style="border-bottom:1px solid #000;">
                    @if($firmaInspectorSrc)
                        <img src="{{ $firmaInspectorSrc }}" style="
                            max-width:120px;
                            max-height:35px;
                            object-fit:contain;
                            display:block;
                            margin:0 auto;
                        ">
                    @endif
                </div>
            </td>
        </tr>
    </table>

</div>

@if(!$loop->last)
    <div class="page-break"></div>
@endif
@endforeach

</body>
</html>
