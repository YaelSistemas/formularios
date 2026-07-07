<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Polipasto Manual de Cadena' }}</title>

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
            transform: scale(1);
            transform-origin: top left;
            margin-left: 0px;
        }

        .page-break {
            page-break-before: always;
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


        .main-polipasto-table {
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-polipasto-table td {
            overflow: hidden;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            box-sizing: border-box;
        }

        .polipasto-sizing-row td {
            height: 0 !important;
            line-height: 0 !important;
            font-size: 0 !important;
            padding: 0 !important;
            border: none !important;
            overflow: hidden !important;
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
                $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            }
        }

        $fechaInspeccion = optional($submission->created_at)->format('d/m/Y') ?: '';
        $tallerValor = data_get($answers, 'taller', '') ?: '';

        $rows = data_get($answers, 'tabla_polipasto_manual_cadena', []);

        $filasConDatos = collect($rows)->filter(function ($row) {
            return !empty(array_filter(
                $row,
                fn($value) => $value !== null && $value !== ''
            ));
        })->values();

        $filasPorPagina = 6;
        $paginasPolipasto = $filasConDatos->count()
            ? $filasConDatos->chunk($filasPorPagina)->values()
            : collect([collect([])]);
    @endphp

    @foreach($paginasPolipasto as $pageIndex => $filasPagina)
    <div class="sheet {{ $pageIndex > 0 ? 'page-break' : '' }}">

        <!-- HEADER -->
        <table class="header-table">
            <tr style="height:0; line-height:0;">
                <td style="width:25%; padding:0; border:none; height:0;"></td>
                <td style="width:22.5%; padding:0; border:none; height:0;"></td>
                <td style="width:22.5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
            </tr>

            <tr>
                <td rowspan="3" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>

                <td colspan="2" class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <td class="right-cell">
                    CÓDIGO: SST-POP-TA-01-FO-06
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell">
                    CHECKLIST DE POLIPASTO MANUAL DE CADENA
                </td>

                <td class="right-cell">
                    REVISIÓN: 02
                </td>
            </tr>
        </table>

        <!-- DATOS -->
        <div class="inspection-area">
            <div class="inspection-row">
                <div class="inspection-item left">
                    <span class="inspection-label">Fecha de inspección:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $fechaInspeccion }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>

                <div class="inspection-item right">
                    <span class="inspection-label">Taller:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $tallerValor }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- TEXTO -->
        <div style="
            text-align:center;
            margin-top:3px;
            font-size:8px;
            font-weight:bold;
            line-height:1.4;
        ">
            Este formato deberá llenarse cada que se use el polipasto y en caso de no usarse, debe llenarse una vez al mes.
        </div>

        @php
            /*
             |--------------------------------------------------------------
             | CONFIGURACIÓN DE ANCHOS DE LA TABLA PRINCIPAL
             |--------------------------------------------------------------
             | Modifica estos valores para cambiar el ancho de cada columna.
             | Se usan en la tabla de títulos y también en la tabla de registros.
             | La suma debe dar aproximadamente 100%.
             */
            $wNoSeriePolipasto = '8%';
            $wPolipasto1 = '8%';
            
            $wPolipasto2 = '6.1667%';
            $wPolipasto3 = '6.1667%';
            $wPolipasto4 = '6.1667%';
            $wPolipasto5 = '6.1667%';
            $wPolipasto6 = '6.1667%';
            $wPolipasto7 = '6.1667%';
            $wPolipasto8 = '6.1667%';
            $wPolipasto9 = '6.1667%';
            
            $wCadenaDesplazaPolipasto = '6.1667%';
            $wSonidoInusualPolipasto = '6.1667%';
            $wEngranesLubricadosPolipasto = '6.1667%';
            $wCondicionesGeneralesPolipasto = '6.1667%';
            
            $wObservacionesPolipasto = '10%';

            $anchosTablaPolipasto = [
                $wNoSeriePolipasto,
                $wPolipasto1,
                $wPolipasto2,
                $wPolipasto3,
                $wPolipasto4,
                $wPolipasto5,
                $wPolipasto6,
                $wPolipasto7,
                $wPolipasto8,
                $wPolipasto9,
                $wCadenaDesplazaPolipasto,
                $wSonidoInusualPolipasto,
                $wEngranesLubricadosPolipasto,
                $wCondicionesGeneralesPolipasto,
                $wObservacionesPolipasto,
            ];
        @endphp

        <!-- TABLA ENCABEZADO DE CRITERIOS -->
        <table class="main-polipasto-table" style="
            width: 85%;
            margin: 10px 0 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6px;
        ">
            <colgroup>
                @foreach($anchosTablaPolipasto as $anchoColumnaPolipasto)
                    <col style="width: {{ $anchoColumnaPolipasto }};">
                @endforeach
            </colgroup>

            <!-- FILA OCULTA PARA FORZAR ANCHOS EN DOMPDF -->
            <tr class="polipasto-sizing-row">
                @foreach($anchosTablaPolipasto as $anchoColumnaPolipasto)
                    <td style="width: {{ $anchoColumnaPolipasto }}; max-width: {{ $anchoColumnaPolipasto }}; min-width: {{ $anchoColumnaPolipasto }};"></td>
                @endforeach
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td rowspan="3" style="
                    width:{{ $anchosTablaPolipasto[0] }}; max-width:{{ $anchosTablaPolipasto[0] }}; min-width:{{ $anchosTablaPolipasto[0] }};
                    border:1px solid #000;
                    background:#f3f4f6;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    line-height:1.15;
                    padding:4px 3px;
                ">
                    No. de Serie/<br>Identificación
                </td>
        
                <td colspan="14" style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    font-size:8px;
                    padding:4px 3px;
                ">
                    Criterios o inspeccionar (Buenas condiciones: B o Malas condiciones: M)
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td style="width:{{ $anchosTablaPolipasto[1] }}; max-width:{{ $anchosTablaPolipasto[1] }}; min-width:{{ $anchosTablaPolipasto[1] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">1. Ganchos<br>(Superior e Inferior)</td>
                <td style="width:{{ $anchosTablaPolipasto[2] }}; max-width:{{ $anchosTablaPolipasto[2] }}; min-width:{{ $anchosTablaPolipasto[2] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">2. Seguros de los ganchos<br>(Superior e Inferior)</td>
                <td style="width:{{ $anchosTablaPolipasto[3] }}; max-width:{{ $anchosTablaPolipasto[3] }}; min-width:{{ $anchosTablaPolipasto[3] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">3. Tornillos</td>
                <td style="width:{{ $anchosTablaPolipasto[4] }}; max-width:{{ $anchosTablaPolipasto[4] }}; min-width:{{ $anchosTablaPolipasto[4] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">4. Perno del gancho</td>
                <td style="width:{{ $anchosTablaPolipasto[5] }}; max-width:{{ $anchosTablaPolipasto[5] }}; min-width:{{ $anchosTablaPolipasto[5] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">5. Marco</td>
                <td style="width:{{ $anchosTablaPolipasto[6] }}; max-width:{{ $anchosTablaPolipasto[6] }}; min-width:{{ $anchosTablaPolipasto[6] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">6. Placa del fabricante</td>
                <td style="width:{{ $anchosTablaPolipasto[7] }}; max-width:{{ $anchosTablaPolipasto[7] }}; min-width:{{ $anchosTablaPolipasto[7] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">7. Rótulo de la capacidad</td>
                <td style="width:{{ $anchosTablaPolipasto[8] }}; max-width:{{ $anchosTablaPolipasto[8] }}; min-width:{{ $anchosTablaPolipasto[8] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">8. Cadena manual</td>
                <td style="width:{{ $anchosTablaPolipasto[9] }}; max-width:{{ $anchosTablaPolipasto[9] }}; min-width:{{ $anchosTablaPolipasto[9] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">9. Cadena de carga</td>
        
                <td rowspan="2" style="width:{{ $anchosTablaPolipasto[10] }}; max-width:{{ $anchosTablaPolipasto[10] }}; min-width:{{ $anchosTablaPolipasto[10] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">¿La cadena se desplaza adecuadamente?</td>
                <td rowspan="2" style="width:{{ $anchosTablaPolipasto[11] }}; max-width:{{ $anchosTablaPolipasto[11] }}; min-width:{{ $anchosTablaPolipasto[11] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">¿Se escucha algún sonido inusual en los engranes?</td>
                <td rowspan="2" style="width:{{ $anchosTablaPolipasto[12] }}; max-width:{{ $anchosTablaPolipasto[12] }}; min-width:{{ $anchosTablaPolipasto[12] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">¿Los engranes están lubricados?</td>
                <td rowspan="2" style="width:{{ $anchosTablaPolipasto[13] }}; max-width:{{ $anchosTablaPolipasto[13] }}; min-width:{{ $anchosTablaPolipasto[13] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">Condiciones generales del polipasto manual de cadena</td>
                <td rowspan="2" style="width:{{ $anchosTablaPolipasto[14] }}; max-width:{{ $anchosTablaPolipasto[14] }}; min-width:{{ $anchosTablaPolipasto[14] }}; border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 3px;">Observaciones</td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td style="width:{{ $anchosTablaPolipasto[1] }}; max-width:{{ $anchosTablaPolipasto[1] }}; min-width:{{ $anchosTablaPolipasto[1] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Torcidos, flexionados, con demasiada apertura en la garganta, gastados, agrietados, con muescas, con estrías, oxidado</td>
                <td style="width:{{ $anchosTablaPolipasto[2] }}; max-width:{{ $anchosTablaPolipasto[2] }}; min-width:{{ $anchosTablaPolipasto[2] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Torcidos, gastados, faltante, oxidado, no funciona</td>
                <td style="width:{{ $anchosTablaPolipasto[3] }}; max-width:{{ $anchosTablaPolipasto[3] }}; min-width:{{ $anchosTablaPolipasto[3] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Gastados, flojos, oxidados, faltantes</td>
                <td style="width:{{ $anchosTablaPolipasto[4] }}; max-width:{{ $anchosTablaPolipasto[4] }}; min-width:{{ $anchosTablaPolipasto[4] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Gastados, flojos, oxidados, faltantes</td>
                <td style="width:{{ $anchosTablaPolipasto[5] }}; max-width:{{ $anchosTablaPolipasto[5] }}; min-width:{{ $anchosTablaPolipasto[5] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Decolorado, gastado, agrietado</td>
                <td style="width:{{ $anchosTablaPolipasto[6] }}; max-width:{{ $anchosTablaPolipasto[6] }}; min-width:{{ $anchosTablaPolipasto[6] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Faltante o Ilegible</td>
                <td style="width:{{ $anchosTablaPolipasto[7] }}; max-width:{{ $anchosTablaPolipasto[7] }}; min-width:{{ $anchosTablaPolipasto[7] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Faltante o Ilegible</td>
                <td style="width:{{ $anchosTablaPolipasto[8] }}; max-width:{{ $anchosTablaPolipasto[8] }}; min-width:{{ $anchosTablaPolipasto[8] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Gastada, oxidada, golpeada</td>
                <td style="width:{{ $anchosTablaPolipasto[9] }}; max-width:{{ $anchosTablaPolipasto[9] }}; min-width:{{ $anchosTablaPolipasto[9] }}; border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 3px; line-height:1.15;">Gastada, oxidada, golpeada</td>
            </tr>
        </table>

        <!-- GUÍA DE INSPECCIÓN -->
        <table style="
            width: 13%;
            margin-top: -225px;
            margin-left: 86%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
        
            <!-- TITULO -->
            <tr>
                <td style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 3px;
                ">
                    Guía de Inspección
                </td>
            </tr>
        
            <!-- IMAGEN -->
            <tr>
                <td style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    padding:8px 4px;
                ">
                    <img
                        src="{{ public_path('images/forms/SST_POP_TA_01_FO_06_Checklist_de_Polipasto_Manual_de_Cadena/Polipasto_Manual_Cadena.png') }}"
                        style="
                            width: 120px;
                            height: 290px;
                            object-fit: contain;
                            display:block;
                            margin:0 auto;
                        "
                    >
                </td>
            </tr>
        </table>

        <!-- TABLA DATOS -->
        <table class="main-polipasto-table" style="
            width: 85%;
            margin: -182px 0 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6px;
        ">
            <colgroup>
                @foreach($anchosTablaPolipasto as $anchoColumnaPolipasto)
                    <col style="width: {{ $anchoColumnaPolipasto }};">
                @endforeach
            </colgroup>

            <!-- FILA OCULTA PARA FORZAR ANCHOS EN DOMPDF -->
            <tr class="polipasto-sizing-row">
                @foreach($anchosTablaPolipasto as $anchoColumnaPolipasto)
                    <td style="width: {{ $anchoColumnaPolipasto }}; max-width: {{ $anchoColumnaPolipasto }}; min-width: {{ $anchoColumnaPolipasto }};"></td>
                @endforeach
            </tr>
        
            @php
                $totalFilas = $filasPorPagina;
            @endphp
        
            @for ($i = 0; $i < $totalFilas; $i++)
        
                @php
                    $row = $filasPagina->values()->get($i, []);
                @endphp
        
                <tr>
        
                    <!-- NO SERIE -->
                    <td style="
                        width:{{ $anchosTablaPolipasto[0] }}; max-width:{{ $anchosTablaPolipasto[0] }}; min-width:{{ $anchosTablaPolipasto[0] }};
                        border:1px solid #000;
                        text-align:center;
                        vertical-align:middle;
                        padding:4px 3px;
                        height:24px;
                    ">
                        {{ data_get($row, 'numero_serie_identificacion') }}
                    </td>
        
                    <!-- 1 -->
                    <td style="width:{{ $anchosTablaPolipasto[1] }}; max-width:{{ $anchosTablaPolipasto[1] }}; min-width:{{ $anchosTablaPolipasto[1] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_1_estado') }}
                    </td>
        
                    <!-- 2 -->
                    <td style="width:{{ $anchosTablaPolipasto[2] }}; max-width:{{ $anchosTablaPolipasto[2] }}; min-width:{{ $anchosTablaPolipasto[2] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_2_estado') }}
                    </td>
        
                    <!-- 3 -->
                    <td style="width:{{ $anchosTablaPolipasto[3] }}; max-width:{{ $anchosTablaPolipasto[3] }}; min-width:{{ $anchosTablaPolipasto[3] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_3_estado') }}
                    </td>
        
                    <!-- 4 -->
                    <td style="width:{{ $anchosTablaPolipasto[4] }}; max-width:{{ $anchosTablaPolipasto[4] }}; min-width:{{ $anchosTablaPolipasto[4] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_4_estado') }}
                    </td>
        
                    <!-- 5 -->
                    <td style="width:{{ $anchosTablaPolipasto[5] }}; max-width:{{ $anchosTablaPolipasto[5] }}; min-width:{{ $anchosTablaPolipasto[5] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_5_estado') }}
                    </td>
        
                    <!-- 6 -->
                    <td style="width:{{ $anchosTablaPolipasto[6] }}; max-width:{{ $anchosTablaPolipasto[6] }}; min-width:{{ $anchosTablaPolipasto[6] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_6_estado') }}
                    </td>
        
                    <!-- 7 -->
                    <td style="width:{{ $anchosTablaPolipasto[7] }}; max-width:{{ $anchosTablaPolipasto[7] }}; min-width:{{ $anchosTablaPolipasto[7] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_7_estado') }}
                    </td>
        
                    <!-- 8 -->
                    <td style="width:{{ $anchosTablaPolipasto[8] }}; max-width:{{ $anchosTablaPolipasto[8] }}; min-width:{{ $anchosTablaPolipasto[8] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_8_estado') }}
                    </td>
        
                    <!-- 9 -->
                    <td style="width:{{ $anchosTablaPolipasto[9] }}; max-width:{{ $anchosTablaPolipasto[9] }}; min-width:{{ $anchosTablaPolipasto[9] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'polipasto_9_estado') }}
                    </td>
                    
                    <!-- CADENA -->
                    <td style="width:{{ $anchosTablaPolipasto[10] }}; max-width:{{ $anchosTablaPolipasto[10] }}; min-width:{{ $anchosTablaPolipasto[10] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'cadena_desplaza_adecuadamente') }}
                    </td>
        
                    <!-- SONIDO -->
                    <td style="width:{{ $anchosTablaPolipasto[11] }}; max-width:{{ $anchosTablaPolipasto[11] }}; min-width:{{ $anchosTablaPolipasto[11] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'sonido_inusual_engranes') }}
                    </td>
        
                    <!-- LUBRICADOS -->
                    <td style="width:{{ $anchosTablaPolipasto[12] }}; max-width:{{ $anchosTablaPolipasto[12] }}; min-width:{{ $anchosTablaPolipasto[12] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'engranes_lubricados') }}
                    </td>
        
                    <!-- CONDICIONES -->
                    <td style="width:{{ $anchosTablaPolipasto[13] }}; max-width:{{ $anchosTablaPolipasto[13] }}; min-width:{{ $anchosTablaPolipasto[13] }}; border:1px solid #000; text-align:center;">
                        {{ data_get($row, 'condiciones_generales_polipasto') }}
                    </td>
        
                    <!-- OBS -->
                    <td style="
                        width:{{ $anchosTablaPolipasto[14] }}; max-width:{{ $anchosTablaPolipasto[14] }}; min-width:{{ $anchosTablaPolipasto[14] }};
                        border:1px solid #000;
                        text-align:center;
                        vertical-align:middle;
                        padding:4px 3px;
                    ">
                        {{ data_get($row, 'observaciones') }}
                    </td>
                </tr>
            @endfor
        </table>

        @php
            $firmaTrabajador = data_get($answers, 'firma_trabajador_elabora_checklist');
            $firmaSupervisor = data_get($answers, 'firma_supervisor_trabajador');
        
            $firmaTrabajadorSrc = null;
            $firmaSupervisorSrc = null;
        
            if ($firmaTrabajador) {
                $pathTrabajador = storage_path('app/public/' . $firmaTrabajador);
        
                if (file_exists($pathTrabajador)) {
                    $firmaTrabajadorSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($pathTrabajador));
                }
            }
        
            if ($firmaSupervisor) {
                $pathSupervisor = storage_path('app/public/' . $firmaSupervisor);
        
                if (file_exists($pathSupervisor)) {
                    $firmaSupervisorSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($pathSupervisor));
                }
            }
        @endphp
        
        <!-- FIRMAS -->
        <table style="
            width: 60%;
            margin: 15px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        ">
            <tr>
                <!-- TRABAJADOR -->
                <td style="
                    width:48%;
                    border:none;
                    vertical-align:top;
                ">
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                    ">
        
                        <!-- TEXTO -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                background:#f3f4f6;
                                font-weight:bold;
                                text-align:center;
                                vertical-align:middle;
                                padding:6px 4px;
                                font-size:9px;
                            ">
                                Nombre y firma del trabajador que elabora el checklist
                            </td>
                        </tr>
                        
                        <!-- NOMBRE -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                text-align:center;
                                vertical-align:middle;
                                padding:6px 4px;
                                font-size:9px;
                            ">
                                {{ data_get($answers, 'nombre_trabajador_elabora_checklist') }}
                            </td>
                        </tr>
                        
                        <!-- FIRMA -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                height:90px;
                                text-align:center;
                                vertical-align:middle;
                            ">
                        
                                @if($firmaTrabajadorSrc)
                                    <img
                                        src="{{ $firmaTrabajadorSrc }}"
                                        style="
                                            max-width:160px;
                                            max-height:70px;
                                            object-fit:contain;
                                        "
                                    >
                                @endif
                        
                            </td>
                        </tr>
                    </table>
                </td>
        
                <td style="width:4%; border:none;"></td>
        
                <!-- SUPERVISOR -->
                <td style="
                    width:48%;
                    border:none;
                    vertical-align:top;
                ">
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                    ">
        
                        <!-- TEXTO -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                background:#f3f4f6;
                                font-weight:bold;
                                text-align:center;
                                vertical-align:middle;
                                padding:6px 4px;
                                font-size:9px;
                            ">
                                Nombre y firma del supervisor del trabajador
                            </td>
                        </tr>
                        
                        <!-- NOMBRE -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                text-align:center;
                                vertical-align:middle;
                                padding:6px 4px;
                                font-size:9px;
                            ">
                                {{ data_get($answers, 'nombre_supervisor_trabajador') }}
                            </td>
                        </tr>
                        
                        <!-- FIRMA -->
                        <tr>
                            <td style="
                                border:1px solid #000;
                                height:90px;
                                text-align:center;
                                vertical-align:middle;
                            ">
                        
                                @if($firmaSupervisorSrc)
                                    <img
                                        src="{{ $firmaSupervisorSrc }}"
                                        style="
                                            max-width:160px;
                                            max-height:70px;
                                            object-fit:contain;
                                        "
                                    >
                                @endif
                        
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
    @endforeach
</body>
</html>