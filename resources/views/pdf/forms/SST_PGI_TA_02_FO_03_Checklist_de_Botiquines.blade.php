<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Botiquines' }}</title>

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
            width: 92%;
            margin: 15px auto 0 auto;
        }

        .inspection-row {
            width: 100%;
            font-size: 0;
            text-align: center;
        }

        .inspection-item {
            display: inline-block;
            width: 42%;
            vertical-align: middle;
            font-size: 10px;
            box-sizing: border-box;
        }

        .inspection-item.left {
            margin-right: 3%;
        }

        .inspection-item.right {
            margin-left: 3%;
        }

        .inspection-label {
            display: inline-block;
            font-size: 8px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 8px;
            position: relative;
            top: 4px;
        }

        .inspection-line-wrap {
            display: inline-block;
            width: 160px;
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

        .botiquin-header-vertical {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 0;
            height: 65px;
            overflow: hidden;
        }

        .botiquin-vertical-wrap {
            position: relative;
            width: 100%;
            height: 65px;
            overflow: hidden;
        }

        .botiquin-vertical-text {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 90px;
            transform: translate(-50%, -50%) rotate(270deg);
            transform-origin: center center;
            font-size: 6px;
            line-height: 1;
            text-align: center;
            white-space: normal;
        }


        .botiquin-data-cell {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 1px 2px;
            height: 18px;
            font-size: 7px;
            line-height: 1;
        }

        .botiquin-separator-cell {
            border: 1px solid #000;
            background: #e5e7eb;
            padding: 0;
            height: 18px;
            font-size: 0;
            line-height: 0;
        }

        .botiquin-observaciones-cell {
            border: 1px solid #000;
            text-align: left;
            vertical-align: middle;
            padding: 1px 2px;
            height: 18px;
            font-size: 6px;
            line-height: 1;
            overflow: hidden;
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

    $nombreInspector =
        data_get($answers, 'nombre_inspector', '') ?: '';

    $firmaInspector =
        data_get($answers, 'firma_inspector');
    
    $firmaInspectorSrc = null;
    
    if ($firmaInspector) {
        $pathFirma = storage_path('app/public/' . $firmaInspector);
    
        if (file_exists($pathFirma)) {
            $firmaInspectorSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($pathFirma));
        }
    }


    $tablaBotiquines = collect(data_get($answers, 'tabla_checklist_botiquines', []))->values();
    $registrosPorHojaBotiquin = 8;
    $paginasBotiquines = $tablaBotiquines
        ->chunk($registrosPorHojaBotiquin)
        ->map(fn($pagina) => $pagina->values());

    if ($paginasBotiquines->isEmpty()) {
        $paginasBotiquines = collect([collect()]);
    }
@endphp

@foreach($paginasBotiquines as $paginaBotiquinIndex => $registrosPaginaBotiquin)
<div class="sheet" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">

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
                PÁGINA: {{ str_pad($paginaBotiquinIndex + 1, 2, '0', STR_PAD_LEFT) }}
            </td>
        </tr>

        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>

            <td class="right-cell">
                CODIFICACIÓN: SST-PGI-TA-02-FO-03
            </td>
        </tr>

        <tr>
            <td rowspan="2" class="center-cell">
                CHECKLIST DE BOTIQUINES
            </td>

            <td class="right-cell">
                NÚMERO DE REVISIÓN: 09
            </td>
        </tr>

        <tr>
            <td class="right-cell">
                FECHA DE EMISIÓN: 27/03/2025
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <div class="inspection-area">
    
        <!-- FILA 1 -->
        <div class="inspection-row">
    
            <!-- TALLER -->
            <div class="inspection-item left">
                <span class="inspection-label">Taller:</span>
    
                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $tallerValor }}
                    </span>
    
                    <span class="inspection-underline"></span>
                </span>
            </div>
    
            <!-- FECHA -->
            <div class="inspection-item right">
                <span class="inspection-label">Fecha:</span>
    
                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $fechaInspeccion }}
                    </span>
    
                    <span class="inspection-underline"></span>
                </span>
            </div>
        </div>
       
        <!-- FILA 2 -->
        <div class="inspection-row" style="margin-top:15px;">
        
            <div class="inspection-item" style="
                width:100%;
                text-align:center;
            ">
                <span class="inspection-label">
                    Nombre y Firma del Inspector:
                </span>
        
                <span class="inspection-line-wrap" style="
                    width:420px;
                    height:26px;
                ">
        
                    <!-- NOMBRE -->
                    <span class="inspection-value" style="
                        left:0;
                        right:120px;
                        text-align:center;
                    ">
                        {{ $nombreInspector }}
                    </span>
        
                    <!-- FIRMA -->
                    @if($firmaInspectorSrc)
                        <img
                            src="{{ $firmaInspectorSrc }}"
                            style="
                                position:absolute;
                                right:40px;
                                bottom:-2px;
                                max-width:110px;
                                max-height:32px;
                                object-fit:contain;
                            "
                        >
                    @endif
        
                    <span class="inspection-underline"></span>
                </span>
            </div>
        </div>
    </div>

    @php
        $botiquinColWidths = [
            '14%',   // 1
            '0.5%',  // 2
        
            '3.70%', '3.70%', '3.70%', '3.70%', '3.70%', '3.70%', '3.70%', '3.70%', '3.70%', // 3-11
        
            '0.5%',  // 12
        
            '3.70%', '3.70%', '3.70%', '3.70%', // 13-16
        
            '0.5%',  // 17
        
            '3.70%', '3.70%', '3.70%', '3.70%', // 18-21
        
            '0.5%',  // 22
        
            '3.70%', '3.70%', '3.70%', // 23-25
        
            '10%',   // 26
        ];

        $unidadBotiquin = [
            2  => 'pza.', // columna 3
            3  => 'pza.', // columna 4
            4  => 'pza.', // columna 5
            5  => 'pza.', // columna 6
            6  => 'pza.', // columna 7
            7  => 'pza.', // columna 8
            8  => 'pza.', // columna 9
            9  => 'pza.', // columna 10
            10 => 'pza.', // columna 11
    
            12 => 'fco.', // columna 13
            13 => 'fco.', // columna 14
            14 => 'fco.', // columna 15
            15 => 'pza.', // columna 16
    
            17 => 'pza.', // columna 18
            18 => 'pza.', // columna 19
            19 => 'pza.', // columna 20
            20 => 'pza.', // columna 21
    
            22 => 'pza.', // columna 23
            23 => 'pr.', // columna 24
            24 => 'pza.', // columna 25
        ];

        $estadoBotiquin = function ($valor) {
            $texto = trim((string) $valor);
        
            if ($texto === '') {
                return '';
            }
        
            $textoMayus = mb_strtoupper($texto, 'UTF-8');
        
            if (
                str_contains($texto, '✓') ||
                str_contains($textoMayus, 'SE CUENTA CON MATERIAL')
            ) {
                return '( ✓ ) Se cuenta con Material';
            }
        
            if (
                str_contains($textoMayus, '( X )') ||
                str_contains($textoMayus, 'FALTA MATERIAL')
            ) {
                return '( X ) Falta Material';
            }
        
            if (
                str_contains($textoMayus, '( F )') ||
                str_contains($textoMayus, 'CADUCO') ||
                str_contains($textoMayus, 'FALTANTE')
            ) {
                return '( F ) Requiere Material Caduco o Faltante';
            }
        
            return $texto;
        };

        $camposBotiquinPorColumna = [
            2  => 'torundas_jabon_quirurgico',
            3  => 'gasas_10_x_10',
            4  => 'venda_elastica_5_cm',
            5  => 'venda_elastica_10_cm',
            6  => 'cinta_adhesiva',
            7  => 'cinta_micropore',
            8  => 'abatelenguas',
            9  => 'curitas_vendas_adhesivas',
            10 => 'copa_lavaojos',

            12 => 'merthiolate',
            13 => 'agua_oxigenada',
            14 => 'alcohol',
            15 => 'agua_esteril',

            17 => 'tijeras_punta_redonda',
            18 => 'termometro',
            19 => 'torniquete_compresor_elastico',
            20 => 'jeringa_desechable',

            22 => 'kit_rcp_barrera',
            23 => 'guantes_latex',
            24 => 'cubre_bocas',
        ];

        $columnasSeparadorasBotiquin = [1, 11, 16, 21];
    @endphp

    <!-- TABLA 26 COLUMNAS X 5 FILAS -->
    <table style="
        width:100%;
        margin-top:10px;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:7px;
    ">
        <tr style="height:0; line-height:0;">
            @foreach($botiquinColWidths as $colWidth)
                <td style="
                    width:{{ $colWidth }};
                    padding:0;
                    border:none;
                    height:0;
                    line-height:0;
                    font-size:0;
                "></td>
            @endforeach
        </tr>

        @for($fila = 0; $fila < 5; $fila++)
            <tr style="{{ $fila === 1 ? 'height:65px;' : '' }}">
                @for($columna = 0; $columna < 26; $columna++)

                    {{-- COLUMNA 1 --}}
                    @if($columna === 0 && $fila === 0)
                        <td style="border:1px solid #000; text-align:left; vertical-align:middle; padding:2px; font-weight:bold;">
                            Marque lo siguiente donde aplique:
                        </td>

                    @elseif($columna === 0 && $fila === 1)
                        <td style="border:1px solid #000; text-align:left; vertical-align:middle; padding:2px; font-weight:bold; line-height:1.2;">
                            ( ✓ ) Se cuenta con Material<br>
                            ( X ) Falta Material<br>
                            ( F ) Requiere Material Caduco o Faltante
                        </td>

                    @elseif($columna === 0 && $fila === 2)
                        <td style="border:1px solid #000; background:#e5e7eb; text-align:center; vertical-align:middle; padding:2px; font-weight:bold;">
                            Cantidad
                        </td>

                    @elseif($columna === 0 && $fila === 3)
                        <td style="border:1px solid #000; background:#e5e7eb; text-align:center; vertical-align:middle; padding:2px; font-weight:bold;">
                            Unidad
                        </td>

                    @elseif($columna === 0 && $fila === 4)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px; font-weight:bold;">
                            N° de Botiquín:
                        </td>

                    {{-- COLUMNA 2 --}}
                    @elseif($columna === 1 && $fila === 0)
                        <td rowspan="13" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:0;
                        ">
                            &nbsp;
                        </td>

                    {{-- OMITIR COLUMNA 2 EN FILAS 2 A 5 POR EL ROWSPAN --}}
                    @elseif($columna === 1 && $fila > 0)

                    {{-- FILA 1 - MATERIAL SECO --}}
                    @elseif($fila === 0 && $columna === 2)
                        <td colspan="9" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                            font-weight:bold;
                        ">
                            Material Seco
                        </td>

                    {{-- OMITIR COLUMNAS 4 A 11 EN FILA 1 POR EL COLSPAN --}}
                    @elseif($fila === 0 && $columna >= 3 && $columna <= 10)

                    {{-- FILA 2 - ENCABEZADOS MATERIAL SECO --}}
                    @elseif($fila === 1 && $columna === 2)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Torundas<br>C/Jabón Quirúrgico
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 3)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Gasas<br>de 10 x 10 cm.
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 4)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Venda Elástica de<br>5 cm.
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 5)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Venda Elástica de<br>10 cm.
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 6)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Cinta Adhesiva
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 7)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Cinta Micropore
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 8)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Abatelenguas
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 9)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Curitas Vendas<br>Adhesivas
                                </div>
                            </div>
                        </td>

                    @elseif($fila === 1 && $columna === 10)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Copa Lavaojos
                                </div>
                            </div>
                        </td>

                    {{-- COLUMNA 12 --}}
                    @elseif($columna === 11 && $fila === 0)
                        <td rowspan="13" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:0;
                        ">
                            &nbsp;
                        </td>

                    {{-- OMITIR COLUMNA 12 EN FILAS 2 A 5 POR EL ROWSPAN --}}
                    @elseif($columna === 11 && $fila > 0)

                    {{-- FILA 1 - MATERIAL LÍQUIDO --}}
                    @elseif($fila === 0 && $columna === 12)
                        <td colspan="4" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                            font-weight:bold;
                        ">
                            Material Líquido
                        </td>
                    
                    {{-- OMITIR COLUMNAS 14 A 16 EN FILA 1 POR EL COLSPAN --}}
                    @elseif($fila === 0 && $columna >= 13 && $columna <= 15)
                    
                    {{-- FILA 2 - MATERIAL LÍQUIDO --}}
                    @elseif($fila === 1 && $columna === 12)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Merthiolate
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 13)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Agua Oxigenada
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 14)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Alcohol
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 15)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Agua Estéril
                                </div>
                            </div>
                        </td>

                    {{-- COLUMNA 17 --}}
                    @elseif($columna === 16 && $fila === 0)
                        <td rowspan="13" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:0;
                        ">
                            &nbsp;
                        </td>
                    
                    {{-- OMITIR COLUMNA 17 EN FILAS 2 A 5 POR EL ROWSPAN --}}
                    @elseif($columna === 16 && $fila > 0)

                    {{-- FILA 1 - MATERIAL INSTRUMENTAL --}}
                    @elseif($fila === 0 && $columna === 17)
                        <td colspan="4" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                            font-weight:bold;
                        ">
                            Material Instrumental
                        </td>
                    
                    {{-- OMITIR COLUMNAS 19 A 21 EN FILA 1 POR EL COLSPAN --}}
                    @elseif($fila === 0 && $columna >= 18 && $columna <= 20)
                    
                    {{-- FILA 2 - MATERIAL INSTRUMENTAL --}}
                    @elseif($fila === 1 && $columna === 17)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Tijeras de Punta<br>Redonda
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 18)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Termómetro
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 19)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Torniquete o<br>Compresor Elástico
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 20)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Jeringa Desechable
                                </div>
                            </div>
                        </td>
                    
                    {{-- COLUMNA 22 --}}
                    @elseif($columna === 21 && $fila === 0)
                        <td rowspan="13" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:0;
                        ">
                            &nbsp;
                        </td>
                    
                    {{-- OMITIR COLUMNA 22 EN FILAS 2 A 5 POR EL ROWSPAN --}}
                    @elseif($columna === 21 && $fila > 0)
                    
                    {{-- FILA 1 - MATERIAL BRIGADISTA --}}
                    @elseif($fila === 0 && $columna === 22)
                        <td colspan="3" style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                            font-weight:bold;
                        ">
                            Material Brigadista
                        </td>
                    
                    {{-- OMITIR COLUMNAS 24 Y 25 EN FILA 1 POR EL COLSPAN --}}
                    @elseif($fila === 0 && $columna >= 23 && $columna <= 24)
                    
                    {{-- FILA 2 - MATERIAL BRIGADISTA --}}
                    @elseif($fila === 1 && $columna === 22)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Kit de RCP / Barrera
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 23)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Guantes Latex
                                </div>
                            </div>
                        </td>
                    
                    @elseif($fila === 1 && $columna === 24)
                        <td class="botiquin-header-vertical">
                            <div class="botiquin-vertical-wrap">
                                <div class="botiquin-vertical-text">
                                    Cubre bocas
                                </div>
                            </div>
                        </td>

                    {{-- COLUMNA 26 - FILA 1 VACÍA SIN BORDES --}}
                    @elseif($fila === 0 && $columna === 25)
                        <td style="
                            border:none;
                            padding:0;
                        ">
                            &nbsp;
                        </td>
                    
                    {{-- COLUMNA 26 - OBSERVACIONES --}}
                    @elseif($fila === 1 && $columna === 25)
                        <td rowspan="4" style="
                            border:1px solid #000;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                        ">
                            Observaciones
                        </td>
                    
                    {{-- OMITIR COLUMNA 26 EN FILAS 3 A 5 POR EL ROWSPAN --}}
                    @elseif($columna === 25 && $fila > 1)
                    
                    {{-- FILA 3 - CANTIDADES MATERIAL SECO --}}
                    @elseif($fila === 2 && $columna === 2)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 3)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">10</td>
                    
                    @elseif($fila === 2 && $columna === 4)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 5)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 6)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 7)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 8)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">5</td>
                    
                    @elseif($fila === 2 && $columna === 9)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">10</td>
                    
                    @elseif($fila === 2 && $columna === 10)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    {{-- FILA 3 - CANTIDADES MATERIAL LÍQUIDO --}}
                    @elseif($fila === 2 && $columna === 12)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 13)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 14)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 15)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    {{-- FILA 3 - CANTIDADES MATERIAL INSTRUMENTAL --}}
                    @elseif($fila === 2 && $columna === 17)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 18)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 19)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">1</td>
                    
                    @elseif($fila === 2 && $columna === 20)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">2</td>
                    
                    {{-- FILA 3 - CANTIDADES MATERIAL BRIGADISTA --}}
                    @elseif($fila === 2 && $columna === 22)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">2</td>
                    
                    @elseif($fila === 2 && $columna === 23)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">2</td>
                    
                    @elseif($fila === 2 && $columna === 24)
                        <td style="border:1px solid #000; text-align:center; vertical-align:middle;">2</td>

                    {{-- FILAS 4 Y 5 - UNIDADES INDIVIDUALES --}}
                    @elseif($fila === 3 && array_key_exists($columna, $unidadBotiquin))
                        <td rowspan="2" style="
                            border:1px solid #000;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ $unidadBotiquin[$columna] }}
                        </td>
                    
                    {{-- OMITIR FILA 5 POR EL ROWSPAN DE UNIDADES --}}
                    @elseif($fila === 4 && array_key_exists($columna, $unidadBotiquin))

                    {{-- RESTO DE CELDAS --}}
                    @else
                        <td style="
                            border:1px solid #000;
                            text-align:center;
                            vertical-align:middle;
                            padding:2px;
                        ">
                            &nbsp;
                        </td>
                    @endif

                @endfor
            </tr>
        @endfor


        {{-- 8 FILAS DE DATOS DEL REGISTRO POR HOJA --}}
        @for($registroIndex = 0; $registroIndex < 8; $registroIndex++)
            @php
                $registroBotiquin = $registrosPaginaBotiquin->get($registroIndex, []);
            @endphp

            <tr style="height:18px;">
                @for($columna = 0; $columna < 26; $columna++)

                    {{-- COLUMNA 1 - NÚMERO DE BOTIQUÍN --}}
                    @if($columna === 0)
                        <td class="botiquin-data-cell" style="font-weight:bold;">
                            {{ data_get($registroBotiquin, 'numero_botiquin', '') }}
                        </td>

                    {{-- COLUMNAS SEPARADORAS YA UNIDAS DESDE ARRIBA --}}
                    @elseif(in_array($columna, $columnasSeparadorasBotiquin, true))
                        {{-- No se imprime celda porque las columnas 2, 12, 17 y 22 vienen con rowspan="13" --}}

                    {{-- COLUMNAS DE MATERIALES --}}
                    @elseif(array_key_exists($columna, $camposBotiquinPorColumna))
                        <td class="botiquin-data-cell">
                            {{ $estadoBotiquin(data_get($registroBotiquin, $camposBotiquinPorColumna[$columna], '')) }}
                        </td>

                    {{-- COLUMNA 26 - OBSERVACIONES --}}
                    @elseif($columna === 25)
                        <td class="botiquin-observaciones-cell">
                            {{ data_get($registroBotiquin, 'observaciones', '') }}
                        </td>

                    {{-- RESTO --}}
                    @else
                        <td class="botiquin-data-cell">
                            &nbsp;
                        </td>
                    @endif

                @endfor
            </tr>
        @endfor
    </table>

</div>
@endforeach

</body>
</html>
