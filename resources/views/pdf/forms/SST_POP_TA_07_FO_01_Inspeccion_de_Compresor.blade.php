<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Compresor' }}</title>
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
            width: 110%;
            transform: scale(0.90);
            transform-origin: top left;
        }

        .header-row {
            width: 99.6%;
            display: table;
            table-layout: fixed;
        }

        .header-left {
            display: table-cell;
            width: 78%;
            vertical-align: top;
        }

        .header-right-image {
            display: table-cell;
            width: 21.6%;
            vertical-align: top;
            text-align: center;
            padding-left: 10px;
            padding-top: 40px;
            box-sizing: border-box;
        }

        .header-right-image img {
            width: 100%;
            max-width: 260px;
            max-height: 180px;
            object-fit: contain;
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
            font-weight: normal;
            text-align: left !important;
            vertical-align: middle;
            font-size: 9px;
            padding-left: 6px;
        }

        .row-1-center {
            font-size: 11px;
        }

        .row-2-center {
            font-size: 10px;
        }

        .row-3-center {
            font-size: 10px;
        }

        .row-1-right,
        .row-2-right,
        .row-3-right {
            font-size: 9px;
        }

        .inspection-area-left {
            width: 500px;
            margin-top: 2px;
            margin-left: 0;
        }

        .inspection-line-inline {
            width: 100%;
            margin-bottom: 8px;
            font-size: 0;
            white-space: nowrap;
        }

        .inspection-label-inline {
            display: inline-block;
            width: 88px;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            vertical-align: bottom;
            line-height: 1.2;
            padding-right: 6px;
            box-sizing: border-box;
        }

        .inspection-inline-wrap {
            display: inline-block;
            width: 240px;
            position: relative;
            vertical-align: bottom;
            height: 16px;
        }

        .inspection-inline-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 2px;
            font-size: 10px;
            font-weight: normal;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            line-height: 1.1;
        }

        .inspection-inline-underline {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            border-bottom: 1px solid #000;
            height: 1px;
        }

        .pressure-block-left {
            width: 420px;
            margin-top: 30px;
            margin-left: -30px;
        }

        .pressure-note {
            margin-top: 0;
            margin-left: 0;
            width: 420px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            line-height: 1.15;
        }

        .monthly-row {
            margin-top: 10px;
            margin-left: 0;
            width: 420px;
            display: table;
            table-layout: fixed;
        }

        .monthly-text {
            display: table-cell;
            width: 78%;
            font-size: 9px;
            font-style: italic;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding-right: 6px;
        }

        .monthly-options {
            display: table-cell;
            width: 22%;
            vertical-align: middle;
            text-align: left;
        }

        .option-line {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.15;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .mini-table-wrap {
            width: 48%;
            margin-top: 0;
            margin-left: 484px;
            position: relative;
            top: -88px;
        }

        .mini-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }

        .mini-table td,
        .mini-table th {
            border: 1px solid #000;
            padding: 1px 2px;
            vertical-align: middle;
            line-height: 1.05;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .mini-col-1 {
            width: 12%;
            max-width: 12%;
        }

        .mini-col-2 {
            width: 5%;
            max-width: 5%;
            text-align: center;
        }

        .mini-table th {
            text-align: center;
            font-weight: bold;
        }

        .mini-no-border {
            border: none !important;
            background: transparent;
        }

        .mini-tipo {
            text-align: center;
            font-weight: bold;
            line-height: 1.15;
        }

        .mini-center {
            text-align: center;
        }

        .mini-left {
            text-align: left;
        }

        .main-grid-wrap {
            width: 99.6%;
            margin-top: -80px;
        }

        .main-grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
        }

        .main-grid-table td,
        .main-grid-table th {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
        }

        .main-grid-table .shade {
            background: #d9d9d9;
            font-weight: bold;
        }

        .main-grid-table .item-title {
            font-weight: bold;
            background: #d9d9d9;
        }

        .main-grid-table .obs-cell {
            text-align: center;
            padding: 0 2px;
            vertical-align: middle;
        }

        .main-grid-table .data-row td {
            height: 32px;
            vertical-align: middle;
            text-align: center;
            padding: 0 2px;
        }

        /* AJUSTE DE FIRMAS SUBIDAS PARA EVITAR SALTO DE HOJA */
        .signature-section {
            width: 99.6%;
            margin-top: 55px; /* Reducido para que suban y no salten de hoja */
            display: table;
            table-layout: fixed;
        }

        .signature-box {
            display: table-cell;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-space {
            height: 52px;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-space img {
            max-width: 160px;
            max-height: 48px;
            object-fit: contain;
        }

        .signature-name {
            font-size: 8px;
            font-weight: normal;
            text-transform: uppercase;
            line-height: 1.05;
            margin: 0 0 1px 0;
            padding: 0;
        }

        .signature-line {
            width: 35%;
            border-top: 1px solid #000;
            margin: 0 auto 4px auto;
        }

        .signature-text {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @php
        $fieldsCollection = collect($fields ?? []);
        $getField = function ($id) use ($fieldsCollection) {
            return $fieldsCollection->firstWhere('id', $id);
        };

        $logo = $getField('encabezado_logo');

        $logoSrc = null;
        $logoUrl = data_get($logo, 'url');

        if (is_string($logoUrl) && $logoUrl !== '') {
            if (
                str_starts_with($logoUrl, 'http://') ||
                str_starts_with($logoUrl, 'https://') ||
                str_starts_with($logoUrl, 'data:image')
            ) {
                $logoSrc = $logoUrl;
            } else {
                $normalizedLogo = ltrim($logoUrl, '/');
                $possiblePath = public_path($normalizedLogo);

                if (file_exists($possiblePath)) {
                    $mime = mime_content_type($possiblePath) ?: 'image/png';
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($possiblePath));
                }
            }
        }

        $compresorImgSrc = null;
        $compresorImgPath = public_path(
            'images/forms/SST_POP_TA_07_FO_01_Inspeccion_de_Compresor/Imagen_Compresor.png'
        );

        if (file_exists($compresorImgPath)) {
            $mime = mime_content_type($compresorImgPath) ?: 'image/png';
            $compresorImgSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($compresorImgPath));
        }

        $fechaInspeccion = optional($submission->created_at)->format('d/m/Y') ?: '';
        $tallerValor = data_get($answers, 'taller', '') ?: '';

        $rows = is_array(data_get($answers, 'tabla_compresor')) ? data_get($answers, 'tabla_compresor') : [];

        $emptyRow = [
            'tipo' => '',
            'numero_serie' => '',
            'marca' => '',
            'modelo' => '',
            'interruptor_on_off' => '',
            'manometro_tanque' => '',
            'manometro_salida' => '',
            'regulador' => '',
            'conectores_rapidos_universales' => '',
            'valvula_seguridad' => '',
            'valvula_drenaje' => '',
            'enrolla_cable_electrico' => '',
            'valvula_control' => '',
            'cable_alimentacion_electrica' => '',
            'contenedor' => '',
            'carcasa' => '',
            'manguera_alimentacion' => '',
            'observaciones' => '',
        ];
        
        $minRows = 7; // 7 filas de datos + 2 filas de encabezado = 9 visibles por defecto
        $totalRows = max(count($rows), $minRows);
        
        $rowsToRender = $rows;
        
        while (count($rowsToRender) < $totalRows) {
            $rowsToRender[] = $emptyRow;
        }

        $cellValue = function ($value) {
            if ($value === null || $value === '') return '';
            if (is_bool($value)) return $value ? 'Sí' : 'No';
            return (string) $value;
        };

        $firmaInspectorSrc = null;
        $firmaInspectorPath = data_get($answers, 'firma_inspector');
        if (is_string($firmaInspectorPath) && $firmaInspectorPath !== '') {
            if (str_starts_with($firmaInspectorPath, 'data:image')) {
                $firmaInspectorSrc = $firmaInspectorPath;
            } else {
                $normalizedPath = ltrim($firmaInspectorPath, '/');
                $fullPath = str_starts_with($normalizedPath, 'storage/')
                    ? public_path($normalizedPath)
                    : public_path('storage/' . $normalizedPath);

                if (file_exists($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $firmaInspectorSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                }
            }
        }

        $firmaResponsableSrc = null;
        $firmaResponsablePath = data_get($answers, 'firma_responsable_seguridad');
        if (is_string($firmaResponsablePath) && $firmaResponsablePath !== '') {
            if (str_starts_with($firmaResponsablePath, 'data:image')) {
                $firmaResponsableSrc = $firmaResponsablePath;
            } else {
                $normalizedPath = ltrim($firmaResponsablePath, '/');
                $fullPath = str_starts_with($normalizedPath, 'storage/')
                    ? public_path($normalizedPath)
                    : public_path('storage/' . $normalizedPath);

                if (file_exists($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $firmaResponsableSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                }
            }
        }

        $nombreInspector = data_get($answers, 'nombre_inspector')
            ?? data_get($answers, 'inspector')
            ?? data_get($answers, 'nombre_del_inspector')
            ?? '';

        $nombreResponsable = data_get($answers, 'nombre_responsable_seguridad')
            ?? data_get($answers, 'responsable_seguridad')
            ?? data_get($answers, 'nombre_del_responsable_de_seguridad')
            ?? '';
    @endphp

    <div class="sheet">
        <div class="header-row">
            <div class="header-left">
                <table class="header-table">
                    <colgroup>
                        <col style="width: 20%"><col style="width: 27%"><col style="width: 27%"><col style="width: 13%"><col style="width: 13%">
                    </colgroup>
                    <tr>
                        <td rowspan="3" class="logo-cell">
                            @if($logoSrc) <img src="{{ $logoSrc }}" alt="Logo"> @endif
                        </td>
                        <td colspan="2" class="center-cell row-1-center">VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.</td>
                        <td colspan="2" class="right-cell row-1-right">Código:&nbsp; SST-POP-TA-07-FO-01</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="center-cell row-2-center">SISTEMA DE GESTIÓN INTEGRAL</td>
                        <td colspan="2" class="right-cell row-2-right">Fecha de Emisión:&nbsp; 27/03/2025</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="center-cell row-3-center">Inspección de compresor</td>
                        <td colspan="2" class="right-cell row-3-right">Revisión:&nbsp; 03</td>
                    </tr>
                </table>

                <div class="inspection-area-left">
                    <div class="inspection-line-inline">
                        <div class="inspection-label-inline">FECHA:</div>
                        <div class="inspection-inline-wrap">
                            <div class="inspection-inline-value">{{ $fechaInspeccion }}</div>
                            <div class="inspection-inline-underline"></div>
                        </div>
                    </div>
                    <div class="inspection-line-inline">
                        <div class="inspection-label-inline">TALLER:</div>
                        <div class="inspection-inline-wrap">
                            <div class="inspection-inline-value">{{ $tallerValor }}</div>
                            <div class="inspection-inline-underline"></div>
                        </div>
                    </div>
                </div>

                <div class="pressure-block-left">
                    <div class="pressure-note">
                        Presion **: PRESION DE CALIBRACIÓN EN SUS DISPOSITIVOS DE RELEVO DE<br>PRESIÓN
                    </div>
                    <div class="monthly-row">
                        <div class="monthly-text">Este formato deberá llenarse una vez al mes.</div>
                        <div class="monthly-options">
                            <div class="option-line">☑ Bien</div>
                            <div class="option-line">☒ Mal</div>
                        </div>
                    </div>
                </div>

                <div class="mini-table-wrap">
                    <table class="mini-table">
                        <colgroup>
                            <col style="width: 12%"><col style="width: 5%"><col style="width: 48%"><col style="width: 35%">
                        </colgroup>
                        <tr>
                            <td class="mini-no-border"></td><td class="mini-no-border"></td>
                            <th>Presión**</th><th>Volumen</th>
                        </tr>
                        <tr>
                            <td rowspan="5" class="mini-tipo">Tipo de<br>Categoria</td>
                            <td class="mini-center">I</td><td class="mini-left">Menor o igual a 71 PSI</td><td class="mini-left">Menor o igual a 0.5m3</td>
                        </tr>
                        <tr>
                            <td class="mini-center">II</td><td class="mini-left">Mayor a 71 PSI y menor o igual a 113 PSI</td><td class="mini-left">Mayor a 0.5m3</td>
                        </tr>
                        <tr>
                            <td class="mini-center">II</td><td class="mini-left">Menor o igual a 71 PSI</td><td class="mini-left">Menor o igual a 1m3</td>
                        </tr>
                        <tr>
                            <td class="mini-center">III</td><td class="mini-left">Mayor a 71 PSI y menor o igual a 113 PSI</td><td class="mini-left">Mayor a 1m3</td>
                        </tr>
                        <tr>
                            <td class="mini-center">III</td><td class="mini-left">Mayor a 113 PSI</td><td class="mini-left">Cualquier volumen</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="header-right-image">
                @if($compresorImgSrc) <img src="{{ $compresorImgSrc }}" alt="Imagen compresor"> @endif
            </div>
        </div>

        <div class="main-grid-wrap">
            <table class="main-grid-table">
                <tr>
                    <td colspan="4" class="item-title">ITEM</td>
                    <td rowspan="2" class="shade">A) INTERRUPTOR DE ON (I) OFF (O)</td>
                    <td rowspan="2" class="shade">B) MANÓMETRO DE TANQUE</td>
                    <td rowspan="2" class="shade">C) MANÓMETRO (MEDIDOR DE PRESIÓN) DE SALIDA</td>
                    <td rowspan="2" class="shade">D) REGULADOR</td>
                    <td rowspan="2" class="shade">E) CONECTORES RÁPIDOS UNIVERSALES</td>
                    <td rowspan="2" class="shade">F) VÁLVULA DE SEGURIDAD</td>
                    <td rowspan="2" class="shade">G) VÁLVULA DE DRENAJE</td>
                    <td rowspan="2" class="shade">H) ENROLLA CABLE ELÉCTRICO</td>
                    <td rowspan="2" class="shade">I) VÁLVULA DE CONTROL</td>
                    <td rowspan="2" class="shade">J) CABLE DE ALIMENTACIÓN ELÉCTRICA</td>
                    <td rowspan="2" class="shade">K) CONTENEDOR</td>
                    <td rowspan="2" class="shade">L) CARCASA</td>
                    <td rowspan="2" class="shade">M) MANGUERA DE ALIMENTACIÓN</td>
                    <td rowspan="2" class="shade">Observaciones</td>
                </tr>
                <tr>
                    <td class="shade">Tipo</td><td class="shade"># Serie</td><td class="shade">Marca</td><td class="shade">Modelo</td>
                </tr>
                @foreach($rowsToRender as $row)
                    <tr class="data-row">
                        <td>{{ $cellValue($row['tipo'] ?? '') }}</td>
                        <td>{{ $cellValue($row['numero_serie'] ?? '') }}</td>
                        <td>{{ $cellValue($row['marca'] ?? '') }}</td>
                        <td>{{ $cellValue($row['modelo'] ?? '') }}</td>
                        <td>{{ $cellValue($row['interruptor_on_off'] ?? '') }}</td>
                        <td>{{ $cellValue($row['manometro_tanque'] ?? '') }}</td>
                        <td>{{ $cellValue($row['manometro_salida'] ?? '') }}</td>
                        <td>{{ $cellValue($row['regulador'] ?? '') }}</td>
                        <td>{{ $cellValue($row['conectores_rapidos_universales'] ?? '') }}</td>
                        <td>{{ $cellValue($row['valvula_seguridad'] ?? '') }}</td>
                        <td>{{ $cellValue($row['valvula_drenaje'] ?? '') }}</td>
                        <td>{{ $cellValue($row['enrolla_cable_electrico'] ?? '') }}</td>
                        <td>{{ $cellValue($row['valvula_control'] ?? '') }}</td>
                        <td>{{ $cellValue($row['cable_alimentacion_electrica'] ?? '') }}</td>
                        <td>{{ $cellValue($row['contenedor'] ?? '') }}</td>
                        <td>{{ $cellValue($row['carcasa'] ?? '') }}</td>
                        <td>{{ $cellValue($row['manguera_alimentacion'] ?? '') }}</td>
                        <td class="obs-cell">{{ $cellValue($row['observaciones'] ?? '') }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-space">
                    @if($firmaInspectorSrc)
                        <img src="{{ $firmaInspectorSrc }}" alt="Firma inspector">
                    @endif
                </div>
                @if($nombreInspector !== '')
                    <div class="signature-name">{{ $nombreInspector }}</div>
                @endif
                <div class="signature-line"></div>
                <div class="signature-text">Nombre y firma de Inspector</div>
            </div>

            <div class="signature-box">
                <div class="signature-space">
                    @if($firmaResponsableSrc)
                        <img src="{{ $firmaResponsableSrc }}" alt="Firma responsable de seguridad">
                    @endif
                </div>
                @if($nombreResponsable !== '')
                    <div class="signature-name">{{ $nombreResponsable }}</div>
                @endif
                <div class="signature-line"></div>
                <div class="signature-text">Nombre y firma del Supervisor</div>
            </div>
        </div>
    </div>
</body>
</html>