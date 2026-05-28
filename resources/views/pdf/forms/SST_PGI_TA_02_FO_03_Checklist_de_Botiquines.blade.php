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
            width: 108%;
            transform: scale(0.92);
            transform-origin: top left;
            margin-left: 5px;
        }

        .header-table {
            width: 99.6%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
            text-align: center;
            line-height: 1.05;
        }

        .logo-cell {
            padding: 3px 4px;
        }

        .logo-cell img {
            max-width: 100%;
            max-height: 60px;
            object-fit: contain;
        }

        .center-cell {
            font-weight: bold;
        }

        .right-cell {
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .row-1-center {
            font-size: 11px;
        }

        .inspection-area {
            width: 92%;
            margin: 22px auto 0 auto;
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
            font-size: 10px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 8px;
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
            font-size: 10px;
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
@endphp

<div class="sheet">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td rowspan="3" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>

            <td colspan="2" class="center-cell row-1-center">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>

            <td colspan="2" class="right-cell">
                CODIFICACIÓN: SST-PGI-TA-02-FO-03
            </td>
        </tr>

        <tr>
            <td colspan="2" class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>

            <td colspan="2" class="right-cell">
                FECHA DE EMISIÓN: 27/03/2025
            </td>
        </tr>

        <tr>
            <td colspan="2" class="center-cell">
                CHECKLIST DE INSPECCION DE BOTIQUINES DE PRIMEROS AUXILIOS
            </td>

            <td colspan="2" class="right-cell">
                NÚMERO DE REVISIÓN: 09
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

    <!-- TABLA BOTIQUÍN -->
<table style="
    width:99.6%;
    margin-top:25px;
    border-collapse:collapse;
    table-layout:fixed;
    font-size:7px;
">
    <tr>
        <!-- BLOQUE IZQUIERDO -->
        <td style="width:24%; padding:0; border:none; vertical-align:top;">

            <table style="
                width:100%;
                border-collapse:collapse;
                table-layout:fixed;
                font-size:7px;
            ">
                <tr>
                    <td style="
                        border:1px solid #000;
                        border-bottom:none;
                        height:12px;
                        text-align:center;
                        vertical-align:middle;
                        padding:0;
                        font-weight:bold;
                    ">
                        Marque lo siguiente donde aplique:
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:none;
                        border-bottom:none;
                        height:10px;
                        text-align:left;
                        vertical-align:middle;
                        padding:0 2px;
                    ">
                        ( ✓ ) Se cuenta con Material
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:none;
                        border-bottom:none;
                        height:10px;
                        text-align:left;
                        vertical-align:middle;
                        padding:0 2px;
                    ">
                        ( X ) Falta Material
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:none;
                        border-bottom:none;
                        height:10px;
                        text-align:left;
                        vertical-align:middle;
                        padding:0 2px;
                    ">
                        ( F ) Requiere Material Caduco o Faltante
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:none;
                        height:24px;
                        text-align:center;
                        vertical-align:middle;
                        padding:0;
                    ">
                        &nbsp;
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-bottom:none;
                        height:10px;
                        text-align:center;
                        vertical-align:middle;
                        padding:0;
                        font-weight:bold;
                        background:#f3f4f6;
                    ">
                        Cantidad
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:none;
                        height:10px;
                        text-align:center;
                        vertical-align:middle;
                        padding:0;
                        font-weight:bold;
                        background:#f3f4f6;
                    ">
                        Unidad
                    </td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        height:12px;
                        text-align:left;
                        vertical-align:middle;
                        padding:0 2px;
                        font-weight:bold;
                    ">
                        N° de Botiquín:
                    </td>
                </tr>
            </table>

        </td>

        <!-- COLUMNA SOMBREADA 1 -->
        <td style="
            width:1px;
            min-width:1px;
            max-width:1px;
            background:#d1d5db;
            border:1px solid #000;
            padding:0;
            font-size:0;
            line-height:0;
        ">
            &nbsp;
        </td>

        <!-- 9 COLUMNAS DERECHAS -->
        <td style="width:51%; padding:0; border:none; vertical-align:top;">

            <table style="
                width:100%;
                border-collapse:collapse;
                table-layout:fixed;
                font-size:7px;
            ">
                <colgroup>
                    @for ($i = 1; $i <= 9; $i++)
                        <col style="width:11.1111%;">
                    @endfor
                </colgroup>

                @for ($row = 1; $row <= 8; $row++)
                    <tr>
                        @for ($col = 1; $col <= 9; $col++)
                            <td style="
                                border:1px solid #000;
                                height:{{ $row == 1 ? '12px' : ($row == 5 ? '24px' : ($row == 8 ? '12px' : '10px')) }};
                                text-align:center;
                                vertical-align:middle;
                                padding:0;
                            ">
                                &nbsp;
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>

        </td>

        <!-- COLUMNA SOMBREADA 2 -->
        <td style="
            width:1px;
            min-width:1px;
            max-width:1px;
            background:#d1d5db;
            border:1px solid #000;
            padding:0;
            font-size:0;
            line-height:0;
        ">
            &nbsp;
        </td>
    </tr>
</table>

</div>

</body>
</html>

