<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Extintor' }}</title>

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

        .inspection-area {
            width: 96%;
            margin: 22px auto 0 auto;
            text-align: center;
        }

        .inspection-row {
            width: 100%;
            font-size: 0;
            text-align: center;
        }

        .inspection-item {
            display: inline-block;
            width: 31%;
            vertical-align: middle;
            font-size: 8px;
            box-sizing: border-box;
            text-align: center;
        }

        .inspection-label {
            display: inline-block;
            font-size: 8px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 6px;
            position: relative;
            top: 4px;
        }

        .inspection-line-wrap {
            display: inline-block;
            width: 170px;
            position: relative;
            vertical-align: middle;
            height: 22px;
        }

        .inspection-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 3px;
            text-align: center;
            font-size: 8px;
            font-weight: normal;
            white-space: nowrap;
            overflow: hidden;
        }

        .inspection-underline {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 3px;
            border-bottom: 1px solid #000;
            height: 1px;
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

    $fechaInspeccion =
        optional($submission->created_at)->format('d/m/Y') ?: '';

    $tallerValor =
        data_get($answers, 'taller', '') ?: '';

    $nombreResponsableInspeccion =
        data_get($answers, 'nombre_responsable_inspeccion', '') ?: '';

    $firmaResponsableInspeccion =
    data_get($answers, 'firma_responsable_inspeccion');

    $firmaResponsableInspeccionSrc = null;
    
    if ($firmaResponsableInspeccion) {
        $pathFirma = storage_path('app/public/' . $firmaResponsableInspeccion);
    
        if (file_exists($pathFirma)) {
            $firmaResponsableInspeccionSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($pathFirma));
        }
    }

    $registrosPorPaginaExtintor = 15;
    $registrosExtintor = collect(data_get($answers, 'tabla_checklist_extintor', []))->values();

    $paginasExtintor = $registrosExtintor
        ->chunk($registrosPorPaginaExtintor)
        ->map(fn($pagina) => $pagina->values());

    if ($paginasExtintor->isEmpty()) {
        $paginasExtintor = collect([collect()]);
    }

    $totalPaginasExtintor = $paginasExtintor->count();
@endphp

@foreach($paginasExtintor as $paginaExtintorIndex => $registrosPaginaExtintor)
<div class="sheet">

    <!-- HEADER -->
    <table class="header-table">
        <tr style="height:0; line-height:0;">
            <td style="width:25%; padding:0; border:none; height:0;"></td>
            <td style="width:45%; padding:0; border:none; height:0;"></td>
            <td style="width:30%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <tr>
            <td rowspan="4" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>
    
            <td class="center-cell">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>
    
            <td class="right-cell">
                CODIFICACIÓN: SST-PGI-TA-02-FO-02
            </td>
        </tr>
    
        <tr>
            <td rowspan="2" class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>
    
            <td class="right-cell">
                FECHA DE EMISIÓN: 27/03/2025
            </td>
        </tr>
    
        <tr>
            <td class="right-cell">
                REVISIÓN: 06
            </td>
        </tr>
    
        <tr>
            <td class="center-cell">
                CHECKLIST DE EXTINTOR
            </td>
    
            <td class="right-cell">
                PÁGINA: {{ $paginaExtintorIndex + 1 }} DE {{ $totalPaginasExtintor }}
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <div class="inspection-area">
        <div class="inspection-row">

            <!-- FECHA -->
            <div class="inspection-item" style="width:27%;">
                <span class="inspection-label">Fecha de Inspección:</span>

                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $fechaInspeccion }}
                    </span>

                    <span class="inspection-underline"></span>
                </span>
            </div>

            <!-- TALLER -->
            <div class="inspection-item" style="width:20%; margin-left:15px;">
                <span class="inspection-label">Taller:</span>

                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $tallerValor }}
                    </span>

                    <span class="inspection-underline"></span>
                </span>
            </div>

            <!-- NOMBRE -->
            <div class="inspection-item" style="width:45%;">
                <span class="inspection-label" style="
                    line-height:1.1;
                    text-align:right;
                    white-space:normal;
                    width:120px;
                    position:relative;
                    top:-6px;
                ">
                    Nombre y Firma del<br>
                    Resp. de Inspección:
                </span>
            
                <span class="inspection-line-wrap" style="
                    width:240px;
                    padding-left:75px;
                ">
            
                    <!-- FIRMA -->
                    @if($firmaResponsableInspeccionSrc)
                        <img
                            src="{{ $firmaResponsableInspeccionSrc }}"
                            style="
                                position:absolute;
                                left:0;
                                bottom:-2px;
                                width:95px;
                                height:38px;
                                object-fit:contain;
                                filter: contrast(180%) brightness(0.7);
                            "
                        >
                    @endif
            
                    <!-- NOMBRE -->
                    <span class="inspection-value">
                        {{ $nombreResponsableInspeccion }}
                    </span>
            
                    <span class="inspection-underline"></span>
                </span>
            </div>
        </div>
    </div> <!-- Datos -->

    <!-- INDICACIONES -->
    <div style="
        width:100%;
        text-align:center;
        margin-top:5px;
        font-size:8px;
        font-weight:bold;
        line-height:1.5;
    ">
        <div>
            Marque lo siguiente de acuerdo a la Inspección del extintor:
        </div>
    
        <div style="margin-top:4px;">
            Tipo de Extintor,
            ( ✔︎ ) Se Encuentra en Buenas Condiciones,
            ( X ) No Esta En Condiciones,
            ( NA ) No Aplica
        </div>
    </div> <!-- INDICACIONES -->

    @php
        $extintorColWidths = [
            '8%', // 1  No. de Extintor
            '4%', // 2  No. Kilos
    
            '3%', // 3  PQS
            '3%', // 4  CO2
            '3%', // 5  Espuma Afff
            '3%', // 6  Agente Limpio
            '3%', // 7  Agua H2O
    
            '5%', // 8  Anillo de Verificación
            '5%', // 9  Etiqueta de Inspección
            '5%', // 10 Caducidad
            '5%', // 11 Pasador de Seguridad
            '5%', // 12 Cincho de Seguridad
            '5%', // 13 Manómetro
            '5%', // 14 Presión
            '5%', // 15 Lleno
            '5%', // 16 Manguera y Boquilla
            '5%', // 17 Señalética
            '5%', // 18 Soportes y Funda
            '5%', // 19 Limpieza
    
            '13%', // 20 Observaciones
        ];
    @endphp

    <table style="
        width:100%;
        margin-top:10px;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:7px;
    ">
        <colgroup>
            @foreach($extintorColWidths as $width)
                <col style="width:{{ $width }};">
            @endforeach
        </colgroup>
    
        <tr style="height:0; line-height:0;">
            @foreach($extintorColWidths as $width)
                <td style="
                    width:{{ $width }};
                    padding:0;
                    border:none;
                    height:0;
                    font-size:0;
                    line-height:0;
                "></td>
            @endforeach
        </tr>

        <!-- FILA 1 -->
        <tr style="height:22px;">
            <!-- COLUMNA 1 -->
            <td rowspan="2" style="border:1px solid #000; background:#b91c1c; color:#fff; font-weight:bold; text-align:center; vertical-align:middle;">
                No. de Extintor
            </td>
    
            <!-- COLUMNA 2 -->
            <td rowspan="2" style="
                border:1px solid #000;
                font-weight:bold;
                text-align:center;
                vertical-align:middle;
                padding:0;
            ">
                <div style="
                    transform:rotate(270deg);
                    white-space:nowrap;
                    line-height:1;
                ">
                    No. Kilos
                </div>
            </td>
    
            <!-- COLUMNAS 3 A 7 -->
            <td colspan="5" style="border:1px solid #000; background:#b91c1c; color:#fff; font-weight:bold; text-align:center; vertical-align:middle;">
                Tipo de Extintor
            </td>
    
            <!-- COLUMNAS 8 A 19 -->
            <td colspan="12" style="border:1px solid #000; background:#b91c1c; color:#fff; font-weight:bold; text-align:center; vertical-align:middle;">
                Componentes de Extintor
            </td>
    
            <!-- COLUMNA 20 -->
            <td rowspan="2" style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle;">
                Observaciones
            </td>
        </tr>
    
        <!-- FILA 2 -->
        <tr>
        
            @foreach ([
                'PQS',
                'CO2',
                'Espuma<br>Afff',
                'Agente Limpio',
                'Agua<br>H2O'
            ] as $tipo)
                <td style="
                    border:1px solid #000;
                    background:#DDD9C4;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:0;
                    height:65px;
                ">
                    <div style="
                        transform:rotate(270deg);
                        white-space:nowrap;
                        line-height:1;
                    ">
                        {!! $tipo !!}
                    </div>
                </td>
            @endforeach
        
            @foreach ([
                'Anillo de<br>Verificación',
                'Etiqueta de<br>Inspección',
                'Caducidad',
                'Pasador de<br>Seguridad',
                'Cincho de<br>Seguridad',
                'Manómetro',
                'Presión',
                'Lleno',
                'Manguera y<br>Boquilla',
                'Señalética',
                'Soportes y<br>Funda',
                'Limpieza'
            ] as $componente)
                <td style="
                    border:1px solid #000;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:0;
                ">
                    <div style="
                        transform:rotate(270deg);
                        white-space:nowrap;
                        line-height:1;
                    ">
                        {!! $componente !!}
                    </div>
                </td>
            @endforeach
        </tr>

        @php
            $altoFilaDatos = '20px';
        @endphp

        @for($i = 0; $i < $registrosPorPaginaExtintor; $i++)
            @php
                $fila = $registrosPaginaExtintor->get($i, []);
            @endphp

            <tr style="height:{{ $altoFilaDatos }};">

                <!-- 1 -->
                <td style="
                    border:1px solid #000;
                    height:{{ $altoFilaDatos }};
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ data_get($fila, 'numero_extintor', '') }}
                </td>

                <!-- 2 -->
                <td style="
                    border:1px solid #000;
                    height:{{ $altoFilaDatos }};
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ data_get($fila, 'numero_kilos', '') }}
                </td>

                <!-- 3-7 Tipo de Extintor -->
                <td colspan="5" style="
                    border:1px solid #000;
                    height:{{ $altoFilaDatos }};
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ data_get($fila, 'tipo_extintor', '') }}
                </td>

                @foreach([
                    'anillo_verificacion',
                    'etiqueta_inspeccion',
                    'caducidad',
                    'pasador_seguridad',
                    'cincho_seguridad',
                    'manometro',
                    'presion',
                    'lleno',
                    'manguera_boquilla',
                    'senaletica',
                    'soportes_funda',
                    'limpieza'
                ] as $campo)

                    <td style="
                        border:1px solid #000;
                        height:{{ $altoFilaDatos }};
                        text-align:center;
                        vertical-align:middle;
                        padding:2px;
                    ">
                        {{ data_get($fila, $campo, '') }}
                    </td>

                @endforeach

                <!-- 20 -->
                <td style="
                    border:1px solid #000;
                    height:{{ $altoFilaDatos }};
                    text-align:left;
                    vertical-align:middle;
                    padding:2px;
                ">
                    {{ data_get($fila, 'observaciones', '') }}
                </td>

            </tr>
        @endfor
    </table>

</div>

@if(!$loop->last)
    <div style="page-break-after:always;"></div>
@endif
@endforeach

</body>
</html>