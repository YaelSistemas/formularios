<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Equipo de Protección Personal' }}</title>

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
            width: 100%;
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
            margin: 22px 0 0 -150px;
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
            margin-left: -15%;
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

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(270deg);
            text-align: center;
            white-space: nowrap;
            line-height: 1.1;
            width: 100%;
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
                CODIFICACIÓN: SST-POP-TA-01-FO-03
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
                INSPECCIÓN DE EQUIPO DE PROTECCIÓN PERSONAL
            </td>

            <td colspan="2" class="right-cell">
                NÚMERO DE REVISIÓN: 05
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:15px;
    ">
        <tr>

            <td style="
                width:100%;
                vertical-align:top;
            ">

                <div class="inspection-area">

                    <!-- FILA 1 -->
                    <div class="inspection-row">

                        <!-- FECHA -->
                        <div class="inspection-item left" style="text-align:left;">
                            <span class="inspection-label">Fecha:</span>

                            <span class="inspection-line-wrap">
                                <span class="inspection-value">
                                    {{ $fechaInspeccion }}
                                </span>

                                <span class="inspection-underline"></span>
                            </span>
                        </div>

                        <!-- INSPECTOR -->
                        <div class="inspection-item right" style="text-align:left;">
                            <span class="inspection-label">Inspector:</span>

                            <span class="inspection-line-wrap" style="width:250px;">
                                <span class="inspection-value">
                                    {{ $nombreInspector }}
                                </span>

                                <span class="inspection-underline"></span>
                            </span>
                        </div>
                    </div>

                    <!-- FILA 2 -->
                    <div class="inspection-row" style="margin-top:50px;">

                        <!-- TALLER -->
                        <div class="inspection-item left" style="text-align:left;">
                            <span class="inspection-label">Taller:</span>

                            <span class="inspection-line-wrap">
                                <span class="inspection-value">
                                    {{ $tallerValor }}
                                </span>

                                <span class="inspection-underline"></span>
                            </span>
                        </div>

                        <!-- FIRMA -->
                        <div class="inspection-item right" style="text-align:left;">
                            <span class="inspection-label">Firma:</span>

                            <span class="inspection-line-wrap" style="height:20px; width:270px;">

                                @if($firmaInspectorSrc)
                                    <img
                                        src="{{ $firmaInspectorSrc }}"
                                        style="
                                            position:absolute;
                                            left:50%;
                                            transform:translateX(-50%);
                                            bottom:0px;
                                            max-width:120px;
                                            max-height:35px;
                                            object-fit:contain;
                                            display:block;
                                        "
                                    >
                                @endif

                                <span class="inspection-underline"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TEXTO CRITERIOS -->
    <div style="
        width:99.6%;
        text-align:center;
        font-size:9px;
        font-weight:bold;
        margin-top:18px;
        line-height:1.5;
    ">
        Considerar los siguientes criterios de acuerdo al estado del EPP
        ( ✓ ) Buenas condiciones,
        ( X ) En malas condiciones,
        ( C ) Requiere cambio,
        ( NT ) No tiene,
        ( NA ) No aplica
    </div>

    <!-- TABLA ENCABEZADOS EPP -->
    <table style="
        width:99.6%;
        margin-top:12px;
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
        font-size:5px;
    ">
    
        <colgroup>
            @for ($i = 1; $i <= 26; $i++)
                <col style="width:3.8461%;">
            @endfor
        </colgroup>
    
        <!-- FILA 1 -->
        <tr>
    
            <!-- NOMBRE -->
            <td rowspan="2" colspan="2" style="
                border:1px solid #000;
                background:#f3f4f6;
                text-align:center;
                vertical-align:middle;
                font-weight:bold;
                padding:4px 2px;
            ">
                Nombre
            </td>
    
            <!-- FIRMA -->
            <td rowspan="2" colspan="2" style="
                border:1px solid #000;
                background:#f3f4f6;
                text-align:center;
                vertical-align:middle;
                font-weight:bold;
                padding:4px 2px;
            ">
                Firma
            </td>
    
            <!-- GUANTE CARNAZA --> 
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Guante de<br>Carnaza</div>
            </td>
    
            <!-- IMPACTO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Guantes de<br>Impacto</div>
            </td>
    
            <!-- NITRILO MEDIA PALMA -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Guantes de<br>Nitrilo Media<br>Palma</div>
            </td>
    
            <!-- NITRILO SQP -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Guantes de<br>Nitrilo SQP</div>
            </td>
    
            <!-- CORTE -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Guantes de<br>Corte</div>
            </td>
    
            <!-- TAPONES -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Tapones o<br>Conchas</div>
            </td>
    
            <!-- LENTES -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Lentes de<br>Seguridad /<br>Goggles</div>
            </td>
    
            <!-- CASCO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Casco de<br>Seguridad<br>(Carcaza,<br>Suspension)</div>
            </td>
    
            <!-- BARBIQUEJO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Barbiquejo</div>
            </td>
    
            <!-- RODILLERAS -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Rodilleras<br>(Desgastes, Roturas)</div>
            </td>
    
            <!-- ZAPATO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Zapato de<br>Seguridad<br>(Estado de Punta<br>y Suela)</div>
            </td>
    
            <!-- UNIFORME -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Uniforme Completo<br>(Estado de<br>Costuras)</div>
            </td>
    
            <!-- RESPIRADOR -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Respirador de<br>Media Cara</div>
            </td>
    
            <!-- FILTROS VO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Filtros Vapores<br>Orgánicos</div>
            </td>
    
            <!-- FILTROS PARTICULAS -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Filtros para<br>Partículas</div>
            </td>
    
            <!-- NAVAJAS -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Navajas Stanley<br>(Funcional)</div>
            </td>
    
            <!-- FLEXOMETRO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Flexometro<br>(Funcional)</div>
            </td>
    
            <!-- CANDADO -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Candado<br>(Funcional)</div>
            </td>
    
            <!-- TARJETA -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Tarjeta de<br>Bloqueo<br>(Legible con<br>Fotografía)</div>
            </td>
    
            <!-- CREDENCIAL -->
            <td rowspan="2" style="border:1px solid #000;vertical-align:middle;text-align:center;font-weight:bold;padding:0;height:80px;">
                <div class="vertical-text">Credencial VYSISA<br>(Vigente)</div>
            </td>
    
            <!-- OBSERVACIONES -->
            <td rowspan="2" colspan="2" style="
                border:1px solid #000;
                background:#f3f4f6;
                text-align:center;
                vertical-align:middle;
                font-weight:bold;
                padding:4px 2px;
            ">
                Observaciones
            </td>
        </tr>
    
        <!-- FILA 2 VACÍA -->
        <tr></tr>

        @php
            $rows = data_get($answers, 'tabla_inspeccion_epp', []);
        
            $rows = is_array($rows) ? $rows : [];
        
            $totalRows = max(count($rows), 10);
        @endphp
        
        @for ($row = 0; $row < $totalRows; $row++)
        
        @php
            $item = $rows[$row] ?? [];
        
            $firmaColaborador = data_get($item, 'firma_colaborador');
        
            $firmaColaboradorSrc = null;

            if ($firmaColaborador) {
                if (str_starts_with($firmaColaborador, 'data:image/')) {
                    $firmaColaboradorSrc = $firmaColaborador;
                } else {
                    $firmaColaborador = str_replace('/storage/', '', $firmaColaborador);
                    $firmaColaborador = ltrim($firmaColaborador, '/');
            
                    $firmaPath = storage_path('app/public/' . $firmaColaborador);
            
                    if (file_exists($firmaPath)) {
                        $firmaColaboradorSrc =
                            'data:image/png;base64,' .
                            base64_encode(file_get_contents($firmaPath));
                    }
                }
            }
        
            $criterios = [
                'guante_carnaza',
                'guantes_impacto',
                'guantes_nitrilo_media_palma',
                'guantes_nitrilo_sqp',
                'guantes_corte',
                'tapones_conchas',
                'lentes_seguridad_goggles',
                'casco_seguridad',
                'barbiquejo',
                'rodilleras',
                'zapato_seguridad',
                'uniforme_completo',
                'respirador_media_cara',
                'filtros_vapores_organicos',
                'filtros_particulas',
                'navajas_stanley',
                'flexometro',
                'candado',
                'tarjeta_bloqueo',
                'credencial_vysisa',
            ];
        @endphp
        
        <tr>
        
            <!-- NOMBRE -->
            <td colspan="2" style="
                border:1px solid #000;
                height:28px;
                padding:2px 4px;
                font-size:6px;
                vertical-align:middle;
                text-align:center;
            ">
                {{ data_get($item, 'nombre_colaborador', '') }}
            </td>
        
            <!-- FIRMA -->
            <td colspan="2" style="
                border:1px solid #000;
                height:28px;
                padding:0;
                text-align:center;
                vertical-align:middle;
                position:relative;
            ">
        
                @if($firmaColaboradorSrc)
                    <img
                        src="{{ $firmaColaboradorSrc }}"
                        style="
                            position:absolute;
                            top:50%;
                            left:50%;
                            transform:translate(-50%, -50%);
                            max-width:80px;
                            max-height:25px;
                            object-fit:contain;
                        "
                    >
                @endif
        
            </td>
        
            <!-- COLUMNAS EPP -->
            @foreach ($criterios as $criterio)
        
                <td style="
                    border:1px solid #000;
                    height:28px;
                    text-align:center;
                    vertical-align:middle;
                    font-size:7px;
                ">
                    {{ data_get($item, $criterio, '') }}
                </td>
        
            @endforeach
        
            <!-- OBSERVACIONES -->
            <td colspan="2" style="
                border:1px solid #000;
                height:28px;
                padding:2px 4px;
                font-size:6px;
                vertical-align:middle;
                text-align:center;
            ">
                {{ data_get($item, 'observaciones', '') }}
            </td>
        
        </tr>
        
        @endfor
    </table>

</div>

</body>
</html>