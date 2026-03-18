<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de herramienta eléctrica portátil' }}</title>
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

        .inspection-area {
            width: 76%;
            margin: 20px auto 0 auto;
        }

        .inspection-line {
            width: 100%;
            margin-bottom: 14px;
            font-size: 0;
        }

        .inspection-label {
            display: inline-block;
            width: 23%;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            vertical-align: bottom;
            padding-right: 18px;
            box-sizing: border-box;
        }

        .inspection-line-wrap {
            display: inline-block;
            width: 30%;
            position: relative;
            vertical-align: bottom;
            height: 24px;
        }

        .inspection-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 8px;
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

        .signature-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -6px;
            text-align: center;
            height: 64px;
        }

        .signature-value img {
            max-height: 64px;
            max-width: 270px;
            object-fit: contain;
        }

        .instruction-block {
            width: 99.6%;
            margin-top: 10px;
        }

        .instruction-text {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .instruction-line {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            height: 1px;
        }

        .tool-image-block {
            width: 99.6%;
            margin-top: 16px;
            text-align: center;
        }

        .tool-image-block img {
            max-width: 100%;
            max-height: 280px;
            object-fit: contain;
        }

        .inspection-grid-table,
        .data-grid-table {
            width: 99.6%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }

        .inspection-grid-table {
            margin-top: 14px;
        }

        .data-grid-table {
            margin-top: 6px;
        }

        .inspection-grid-table td,
        .inspection-grid-table th,
        .data-grid-table td {
            border: 1px solid #000;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
        }

        .inspection-grid-table td,
        .inspection-grid-table th {
            font-weight: bold;
        }

        .inspection-grid-table .normal {
            font-weight: normal;
        }

        .inspection-grid-table .gray-cell {
            background: #d9d9d9;
        }

        .data-grid-table .obs-cell {
            text-align: left;
            padding: 3px 5px;
            line-height: 1.2;
        }

        .data-grid-table tr {
            height: 36px;
        }
        
        .data-grid-table td {
            height: 24px;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .cell-fixed {
            display: block;
            width: 100%;
            margin: 0;
            padding: 0;
            line-height: 1.1;
            overflow: hidden;
            text-align: center;
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

        $firmaPath = data_get($answers, 'firma_inspector');
        $firmaSrc = null;

        if (is_string($firmaPath) && $firmaPath !== '') {
            if (str_starts_with($firmaPath, 'data:image')) {
                $firmaSrc = $firmaPath;
            } elseif (str_starts_with($firmaPath, 'http://') || str_starts_with($firmaPath, 'https://')) {
                $firmaSrc = $firmaPath;
            } else {
                $normalizedPath = ltrim($firmaPath, '/');

                if (str_starts_with($normalizedPath, 'storage/')) {
                    $fullPath = public_path($normalizedPath);
                } else {
                    $fullPath = public_path('storage/' . $normalizedPath);
                }

                if (file_exists($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $firmaSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                }
            }
        }

        $toolImageSrc = null;
        $toolImagePath = public_path('images/forms/SST_POP_TA_08_FO_01_Checklist_de_Herramienta_Electrica_Portatil/Imagen_Herramienta_Electrica_Portatil.png');

        if (file_exists($toolImagePath)) {
            $mime = mime_content_type($toolImagePath) ?: 'image/png';
            $toolImageSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($toolImagePath));
        }

        $fechaInspeccion = optional($submission->created_at)->format('d/m/Y') ?: '';
        $tallerValor = data_get($answers, 'taller', '') ?: '';
        $inspectorValor = data_get($answers, 'nombre_inspector', '') ?: '';

        $tableRows = is_array(data_get($answers, 'tabla_herramientas')) ? data_get($answers, 'tabla_herramientas') : [];

        $minRows = 7;
        
        $rowsToRender = $tableRows;
        
        while (count($rowsToRender) < $minRows) {
            $rowsToRender[] = [
                'tipo_herramienta' => '',
                'serie' => '',
                'conexiones_electricas' => '',
                'interruptores' => '',
                'condiciones_fisicas' => '',
                'mango_sujecion' => '',
                'aditamientos' => '',
                'prueba_funcionamiento' => '',
                'acciones' => '',
                'observaciones' => '',
            ];
        }

        $cellValue = function ($value) {
            if ($value === null || $value === '') {
                return '';
            }
            if (is_bool($value)) {
                return $value ? 'Sí' : 'No';
            }
            return (string) $value;
        };
    @endphp

    <div class="sheet">
        <table class="header-table">
            <colgroup>
                <col style="width: 20%">
                <col style="width: 25%">
                <col style="width: 25%">
                <col style="width: 15%">
                <col style="width: 15%">
            </colgroup>
            <tr>
                <td rowspan="3" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo">
                    @endif
                </td>

                <td colspan="2" class="center-cell row-1-center">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <td colspan="2" class="right-cell row-1-right">
                    CODIFICACIÓN: SST-POP-TA-08-FO-01
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell row-2-center">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td colspan="2" class="right-cell row-2-right">
                    FECHA DE EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell row-3-center">
                    Checklist de herramienta eléctrica portátil
                </td>

                <td colspan="2" class="right-cell row-3-right">
                    NÚMERO DE REVISIÓN: 03
                </td>
            </tr>
        </table>

        <div class="inspection-area">
            <div class="inspection-line">
                <div class="inspection-label">Fecha de inspección</div>
                <div class="inspection-line-wrap">
                    <div class="inspection-value">{{ $fechaInspeccion }}</div>
                    <div class="inspection-underline"></div>
                </div>
            </div>

            <div class="inspection-line">
                <div class="inspection-label">Taller</div>
                <div class="inspection-line-wrap">
                    <div class="inspection-value">{{ $tallerValor }}</div>
                    <div class="inspection-underline"></div>
                </div>
            </div>

            <div class="inspection-line">
                <div class="inspection-label">Inspector</div>
                <div class="inspection-line-wrap">
                    <div class="inspection-value">{{ $inspectorValor }}</div>
                    <div class="inspection-underline"></div>
                </div>

                <div class="inspection-label" style="width: 17%; padding-right: 14px;">Firma del inspector</div>
                <div class="inspection-line-wrap" style="width: 22%;">
                    @if($firmaSrc)
                        <div class="signature-value">
                            <img src="{{ $firmaSrc }}" alt="Firma del inspector">
                        </div>
                    @endif
                    <div class="inspection-underline"></div>
                </div>
            </div>
        </div>

        <div class="instruction-block">
            <div class="instruction-text">
                Este formato deberá llenarse una vez al mes. Marque según los criterios (SI) (NO) (NA)
            </div>
            <div class="instruction-line"></div>
        </div>

        @if($toolImageSrc)
            <div class="tool-image-block">
                <img src="{{ $toolImageSrc }}" alt="Imagen herramienta eléctrica portátil">
            </div>
        @endif

        <table class="inspection-grid-table">
            <colgroup>
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 8%">
                <col style="width: 8%">
                <col style="width: 12%">
                <col style="width: 12%">
            </colgroup>
            <tr>
                <td colspan="2" class="gray-cell"></td>
                <td colspan="8" class="gray-cell">ELEMENTOS DE INSPECCIÓN</td>
                <td colspan="2" rowspan="3">Observaciones</td>
            </tr>
            <tr>
                <td rowspan="2">Tipo</td>
                <td rowspan="2"># Serie</td>
                <td rowspan="2">Conexiones electricas<br>(Cables, Clavijas, Extensiones)</td>
                <td rowspan="2">Interruptores<br>(Gatillos)</td>
                <td rowspan="2">Condiciones fisicas<br>(Carcaza y Guarda de Proteccion)</td>
                <td rowspan="2">Mango de sujeción</td>
                <td rowspan="2">Aditamientos<br>(Discos, Brocas, Puntas, etc.)</td>
                <td rowspan="2">Prueba de funcionamiento</td>
                <td colspan="2">Acciones</td>
            </tr>
            <tr>
                <td colspan="2" class="normal">
                    La herramienta esta en buenas condiciones o se identifica como dañado
                </td>
            </tr>
        </table>

        <table class="data-grid-table">
            <colgroup>
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 7.5%">
                <col style="width: 8%">
                <col style="width: 8%">
                <col style="width: 12%">
                <col style="width: 12%">
            </colgroup>
            @foreach($rowsToRender as $row)
                <tr>
                    <td><div class="cell-fixed">{{ $cellValue($row['tipo_herramienta'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['serie'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['conexiones_electricas'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['interruptores'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['condiciones_fisicas'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['mango_sujecion'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['aditamientos'] ?? '') }}</div></td>
                    <td><div class="cell-fixed">{{ $cellValue($row['prueba_funcionamiento'] ?? '') }}</div></td>
                    <td colspan="2"><div class="cell-fixed">{{ $cellValue($row['acciones'] ?? '') }}</div></td>
                    <td colspan="2" class="obs-cell"><div class="cell-fixed">{{ $cellValue($row['observaciones'] ?? '') }}</div></td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>