<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Arnés de Seguridad' }}</title>

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

        .text-block {
            text-align: center;
            margin-top: 5px;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
        }

        .criteria-table {
            width: 99.6%;
            margin: 14px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6.5px;
        }

        .criteria-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
        }

        .top-title {
            font-weight: bold;
            font-size: 9px;
            background-color: #f3f4f6;
        }

        .group-title {
            font-weight: bold;
            font-size: 7px;
            background-color: #f3f4f6;
        }

        .sub-title {
            font-size: 6px;
        }

        .data-table {
            width: 99.6%;
            margin: 0 auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6px;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
            word-wrap: break-word;
            overflow-wrap: break-word;
            height: 14px;
            min-height: 14px;
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

        $nombreResponsable = data_get($answers, 'nombre_responsable_inspeccion', '');
        $firmaResponsable = data_get($answers, 'firma_responsable_inspeccion', '');

        $firmaSrc = null;

        if (!empty($firmaResponsable)) {
            if (str_starts_with($firmaResponsable, 'data:image')) {
                $firmaSrc = $firmaResponsable;
            } else {
                $relativePath = ltrim(str_replace('\\', '/', $firmaResponsable), '/');

                if (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr($relativePath, 8);
                }

                $rutaFirma = storage_path('app/public/' . $relativePath);

                if (file_exists($rutaFirma)) {
                    $mime = mime_content_type($rutaFirma) ?: 'image/png';
                    $firmaSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($rutaFirma));
                }
            }
        }
    @endphp

    @php
        $rows = data_get($answers, 'tabla_arnes_seguridad', []);

        $filasConDatos = collect($rows)->filter(function ($row) {
            return !empty(array_filter($row, fn($value) => $value !== null && $value !== ''));
        })->values();

        $pages = $filasConDatos->chunk(6)->map(function ($chunk) {
            return $chunk->values();
        })->values();

        if ($pages->isEmpty()) {
            $pages = collect([collect()]);
        }
    @endphp

    @foreach($pages as $pageIndex => $chunk)
    <div class="sheet">

        <!-- HEADER -->
        <table class="header-table">
            <tr style="height:0; line-height:0;">
                <td style="width:25%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
            </tr>

            <tr>
                <td rowspan="3" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>

                <td class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <td class="right-cell">
                    CODIFICACIÓN: SST-POP-TA-04-FO-02
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    FECHA DE EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    INSPECCIÓN DE ARNÉS DE SEGURIDAD
                </td>

                <td class="right-cell">
                    NÚMERO DE REVISIÓN: 04
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
        <div class="text-block">
            Este formato deberá llenarse cada que se use el Arnés y en caso de no usarse llenarse una vez al mes.
        </div>

        @php
            /*
             |--------------------------------------------------------------
             | ANCHOS DE COLUMNAS - TABLA PRINCIPAL
             |--------------------------------------------------------------
             | Puedes modificar estos valores para ajustar el ancho de cada
             | columna. Se usan tanto en la tabla de títulos como en la tabla
             | de registros.
             |
             | Importante: procura que la suma sea cercana a 100%.
             */
            $wNoArnes       = '5%';

            $wCorrea1       = '5%';
            $wCorrea2       = '5%';
            $wCorrea3       = '5%';
            $wCorrea4       = '5%';
            $wCorrea5       = '5%';

            $wDRing1        = '5%';
            $wDRing2        = '5%';
            $wDRing3        = '5%';

            $wHebilla1      = '5%';
            $wHebilla2      = '5%';
            $wHebilla3      = '5%';
            $wHebilla4      = '5%';

            $wEtiqueta1     = '4%';
            $wEtiqueta2     = '4%';

            $wAccion1       = '7%';
            $wAccion2       = '7%';

            $wObservaciones = '13%';

            $columnWidths = [
                $wNoArnes,
                $wCorrea1,
                $wCorrea2,
                $wCorrea3,
                $wCorrea4,
                $wCorrea5,
                $wDRing1,
                $wDRing2,
                $wDRing3,
                $wHebilla1,
                $wHebilla2,
                $wHebilla3,
                $wHebilla4,
                $wEtiqueta1,
                $wEtiqueta2,
                $wAccion1,
                $wAccion2,
                $wObservaciones,
            ];
        @endphp

        <!-- TABLA -->
        <table class="criteria-table">
            <colgroup>
                @foreach($columnWidths as $width)
                    <col style="width:{{ $width }};">
                @endforeach
            </colgroup>

            <!-- Fila técnica para que Dompdf respete los anchos también en los títulos -->
            <tr style="height:0; line-height:0;">
                <td style="width:{{ $wNoArnes }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wCorrea1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wCorrea2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wCorrea3 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wCorrea4 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wCorrea5 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wDRing1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wDRing2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wDRing3 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wHebilla1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wHebilla2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wHebilla3 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wHebilla4 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wEtiqueta1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wEtiqueta2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAccion1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAccion2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wObservaciones }}; padding:0; border:none; height:0;"></td>
            </tr>

            <tr>
                <td style="border:none;"></td>
                <td colspan="17" class="top-title">
                    Criterios de Inspección (SI &nbsp;&nbsp; NO &nbsp;&nbsp; o &nbsp;&nbsp; N/A)
                </td>
            </tr>

            <tr>
                <td rowspan="2" class="group-title" style="width:{{ $wNoArnes }};">N° de Arnés:</td>

                <td colspan="5" class="group-title">CORREAS Y COSTURAS</td>
                <td colspan="3" class="group-title">D-RING</td>
                <td colspan="4" class="group-title">HEBILLAS</td>
                <td colspan="2" class="group-title">ETIQUETA</td>
                <td colspan="2" class="group-title">Acciones:</td>
                <td rowspan="2" class="group-title" style="width:{{ $wObservaciones }};">Observaciones</td>
            </tr>

            <tr>
                <td class="sub-title" style="width:{{ $wCorrea1 }};">1. De Hombros: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title" style="width:{{ $wCorrea2 }};">2. Del Pecho: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title" style="width:{{ $wCorrea3 }};">3. De Espalda: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title" style="width:{{ $wCorrea4 }};">4. De Piernas: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title" style="width:{{ $wCorrea5 }};">5. De Cintura (Sí Aplica): Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>

                <td class="sub-title" style="width:{{ $wDRing1 }};">6. Dorsal: Gastados, Oxidados</td>
                <td class="sub-title" style="width:{{ $wDRing2 }};">7. De Cintura (Sí Aplica): Gastados, Oxidados</td>
                <td class="sub-title" style="width:{{ $wDRing3 }};">8. De Esternón (Sí Aplica): Gastados, Oxidados</td>

                <td class="sub-title" style="width:{{ $wHebilla1 }};">9. Ajuste en Hombros: Flojas</td>
                <td class="sub-title" style="width:{{ $wHebilla2 }};">10. Pecho y Espalda: Flojas, Oxidadas, Gastadas</td>
                <td class="sub-title" style="width:{{ $wHebilla3 }};">11. Mosquetón de Pecho (Sí Aplica): Flojas, Oxidadas, Gastadas</td>
                <td class="sub-title" style="width:{{ $wHebilla4 }};">12. Ajuste en Piernas: Flojas</td>

                <td class="sub-title" style="width:{{ $wEtiqueta1 }};">Faltante</td>
                <td class="sub-title" style="width:{{ $wEtiqueta2 }};">Legible</td>

                <td class="sub-title" style="width:{{ $wAccion1 }};">El Arnés se marca como dañado y es sacado de uso</td>
                <td class="sub-title" style="width:{{ $wAccion2 }};">El Arnés está en buenas condiciones</td>
            </tr>
        </table>

        <table class="data-table" style="margin-top: 6px;">
            <colgroup>
                @foreach($columnWidths as $width)
                    <col style="width:{{ $width }};">
                @endforeach
            </colgroup>

            @for ($i = 0; $i < 6; $i++)
                @php
                    $row = $chunk[$i] ?? [];
                    $accion = data_get($row, 'acciones', '');
                @endphp

                <tr>
                    <td style="width:{{ $wNoArnes }};">{{ data_get($row, 'numero_arnes') }}</td>

                    <td style="width:{{ $wCorrea1 }};">{{ data_get($row, 'correas_1_hombros') }}</td>
                    <td style="width:{{ $wCorrea2 }};">{{ data_get($row, 'correas_2_pecho') }}</td>
                    <td style="width:{{ $wCorrea3 }};">{{ data_get($row, 'correas_3_espalda') }}</td>
                    <td style="width:{{ $wCorrea4 }};">{{ data_get($row, 'correas_4_piernas') }}</td>
                    <td style="width:{{ $wCorrea5 }};">{{ data_get($row, 'correas_5_cintura') }}</td>

                    <td style="width:{{ $wDRing1 }};">{{ data_get($row, 'd_ring_6_dorsal') }}</td>
                    <td style="width:{{ $wDRing2 }};">{{ data_get($row, 'd_ring_7_cintura') }}</td>
                    <td style="width:{{ $wDRing3 }};">{{ data_get($row, 'd_ring_8_esternon') }}</td>

                    <td style="width:{{ $wHebilla1 }};">{{ data_get($row, 'hebillas_9_ajuste_hombros') }}</td>
                    <td style="width:{{ $wHebilla2 }};">{{ data_get($row, 'hebillas_10_pecho_espalda') }}</td>
                    <td style="width:{{ $wHebilla3 }};">{{ data_get($row, 'hebillas_11_mosqueton_pecho') }}</td>
                    <td style="width:{{ $wHebilla4 }};">{{ data_get($row, 'hebillas_12_ajuste_piernas') }}</td>

                    <td style="width:{{ $wEtiqueta1 }};">{{ data_get($row, 'etiqueta_faltante') }}</td>
                    <td style="width:{{ $wEtiqueta2 }};">{{ data_get($row, 'etiqueta_legible') }}</td>

                    <td colspan="2" style="width:{{ ((float) $wAccion1 + (float) $wAccion2) }}%; text-align:center;">
                        {{ $accion }}
                    </td>

                    <td style="width:{{ $wObservaciones }}; text-align:left; padding-left:4px;">
                        {{ data_get($row, 'observaciones') }}
                    </td>
                </tr>
            @endfor
        </table>

        <table style="
            width: 99.6%;
            margin: 6px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
        ">
            <colgroup>
                @for ($i = 0; $i < 17; $i++)
                    <col style="width: {{ $i === 0 ? '8%' : '5.75%' }}">
                @endfor
            </colgroup>
        
            <!-- FILA 1 -->
            <tr>
                <!-- Guía ocupa 6 columnas -->
                <td colspan="6" style="
                    border: 1px solid #000;
                    background-color: #f3f4f6;
                    text-align: center;
                    font-weight: bold;
                ">
                    Guía para la Inspección
                </td>
        
                <!-- Restan 11 columnas -->
                <td colspan="11" style="border: none;">
                    &nbsp;
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
        
                <!-- Imagen ocupa 6 columnas -->
                <td colspan="6" style="
                    border: none;
                    text-align: center;
                ">
                    <img
                        src="{{ public_path('images/forms/SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad/ArnesSeguridad.png') }}"
                        style="
                            width: 160px;
                            height: 210px;
                            object-fit: contain;
                            display: block;
                            margin: 0 auto;
                        "
                    >
                </td>
        
                <!-- Espacio -->
                <td colspan="1" style="border:none;"></td>
        
                <!-- Firma -->
                <td colspan="4" style="
                    border: none;
                    text-align: center;
                    vertical-align: middle;
                ">
        
                    @if(!empty($firmaSrc))
                        <img
                            src="{{ $firmaSrc }}"
                            style="
                                width:150px;
                                height:60px;
                                object-fit:contain;
                                display:block;
                                margin:0 auto 4px auto;
                            "
                        >
                    @endif
        
                    <div style="font-size:8px;">
                        {{ $nombreResponsable }}
                    </div>
        
                    <div style="
                        width:250px;
                        border-bottom:1px solid #000;
                        margin:0 auto;
                    "></div>
        
                    <div style="
                        font-size:7px;
                        font-weight:bold;
                        margin-top:2px;
                    ">
                        Nombre y Firma del trabajador que elabora el checklist
                    </div>
        
                </td>
        
                <!-- Espacio restante -->
                <td colspan="6" style="border:none;"></td>
        
            </tr>
        </table>

    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>
