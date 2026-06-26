<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Línea de Vida' }}</title>

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
            margin: 7px auto 0 auto;
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
            margin-top: 3px;
            font-size: 7px;
            font-weight: bold;
            line-height: 1.4;
        }

        .criteria-table {
            width: 99.6%;
            margin: 5px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
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
            font-size: 6.5px;
        }

        .data-table {
            width: 99.6%;
            margin: 0 auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7px;
        }
        
        .data-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
            word-wrap: break-word;
            overflow-wrap: break-word;
        
            height: 14px; /* Si quiero mas alta la fila numero arriba 24 -> 30 */
            min-height: 14px; /* Si quiero mas alta la fila numero arriba 24 -> 30, Tambien debo bajar o subir esta si subo o bajo la de arriba y viceversa*/
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

        $nombreInspector = data_get($answers, 'nombre_inspector', '');
        $firmaInspector = data_get($answers, 'firma_inspector', '');
        
        $firmaSrc = null;
        
        if (!empty($firmaInspector)) {
            if (str_starts_with($firmaInspector, 'data:image')) {
                $firmaSrc = $firmaInspector;
            } else {
                $relativePath = ltrim(str_replace('\\', '/', $firmaInspector), '/');
        
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
        $rows = data_get($answers, 'tabla_linea_vida', []);

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
                    CODIFICACIÓN: SST-POP-TA-04-FO-03
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    NÚMERO DE REVISIÓN: 03
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    INSPECCIÓN DE LÍNEA DE VIDA
                </td>

                <td class="right-cell">
                    FECHA DE EMISIÓN: 27/03/2025
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
            Este checklist deberá llenarse cada que se use línea de vida y en caso de no usarse llenarse una vez al mes.
            Considerar los siguientes criterios de acuerdo a las condiciones de la línea de vida.
        </div>


        @php
            /*
             |--------------------------------------------------------------
             | ANCHOS DE COLUMNAS - TABLA PRINCIPAL
             |--------------------------------------------------------------
             | Puedes modificar estos valores para ajustar el ancho.
             | La columna de Observaciones queda más amplia.
             */
            $wNumeroLineaVida = '9%';

            $wLineaVida11 = '5%';
            $wLineaVida12 = '5%';
            $wLineaVida13 = '5%';

            $wAmortiguador21 = '5%';
            $wAmortiguador22 = '5%';
            $wAmortiguador23 = '5%';

            $wGanchos31 = '5%';
            $wGanchos32 = '5%';
            $wGanchos33 = '5%';
            $wGanchos34 = '5%';
            $wGanchos35 = '5%';

            $wEtiquetaFaltante = '5%';
            $wEtiquetaLegible  = '5%';

            $wAccionDaniada = '5%';
            $wAccionBuenas  = '5%';

            $wObservaciones = '15.96%';

            $columnWidths = [
                $wNumeroLineaVida,
                $wLineaVida11,
                $wLineaVida12,
                $wLineaVida13,
                $wAmortiguador21,
                $wAmortiguador22,
                $wAmortiguador23,
                $wGanchos31,
                $wGanchos32,
                $wGanchos33,
                $wGanchos34,
                $wGanchos35,
                $wEtiquetaFaltante,
                $wEtiquetaLegible,
                $wAccionDaniada,
                $wAccionBuenas,
                $wObservaciones,
            ];
        @endphp

        <!-- TABLA -->
        <table class="criteria-table">
            <colgroup>
                @foreach($columnWidths as $width)
                    <col style="width: {{ $width }};">
                @endforeach
            </colgroup>

            <!-- Fila técnica para que Dompdf respete los anchos también en los títulos -->
            <tr style="height:0; line-height:0;">
                <td style="width:{{ $wNumeroLineaVida }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wLineaVida11 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wLineaVida12 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wLineaVida13 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAmortiguador21 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAmortiguador22 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAmortiguador23 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wGanchos31 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wGanchos32 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wGanchos33 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wGanchos34 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wGanchos35 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wEtiquetaFaltante }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wEtiquetaLegible }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAccionDaniada }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wAccionBuenas }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $wObservaciones }}; padding:0; border:none; height:0;"></td>
            </tr>

            <tr>
                <td style="border:none;"></td>
                <td colspan="16" class="top-title">
                    Criterios (&nbsp; ( ✓ ) Buen Estado &nbsp;&nbsp; ( X ) Mal Estado &nbsp;&nbsp; ( NA ) No Aplica &nbsp;)
                </td>
            </tr>

            <tr>
                <td rowspan="2" class="group-title" style="width:{{ $wNumeroLineaVida }};">N° de Línea de vida:</td>

                <td colspan="3" class="group-title">1. LÍNEA DE VIDA</td>
                <td colspan="3" class="group-title">2. AMORTIGUADOR</td>
                <td colspan="5" class="group-title">3. GANCHOS</td>
                <td colspan="2" class="group-title">4. ETIQUETA</td>
                <td colspan="2" class="group-title">Acciones:</td>
                <td rowspan="2" class="group-title" style="width:{{ $wObservaciones }};">Observaciones</td>
            </tr>

            <tr>
                <td class="sub-title" style="width:{{ $wLineaVida11 }};">1.1. Costuras (Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas)</td>
                <td class="sub-title" style="width:{{ $wLineaVida12 }};">1.2. Terminación (Cortada, Quemada, Agujerada, Deshilachada, Decolorada, Empalmada)</td>
                <td class="sub-title" style="width:{{ $wLineaVida13 }};">1.3. Cuerpo de la Línea de Vida (Cortado, Quemado, Agujerado, Deshilachado, Decolorado, Empalmado)</td>
                <td class="sub-title" style="width:{{ $wAmortiguador21 }};">2.1. Daño en Cubierta</td>
                <td class="sub-title" style="width:{{ $wAmortiguador22 }};">2.2. Deformación</td>
                <td class="sub-title" style="width:{{ $wAmortiguador23 }};">2.3. Señales de Activación</td>
                <td class="sub-title" style="width:{{ $wGanchos31 }};">3.1. Desgaste Excesivo, Deformaciones</td>
                <td class="sub-title" style="width:{{ $wGanchos32 }};">3.2. Picaduras, Grietas</td>
                <td class="sub-title" style="width:{{ $wGanchos33 }};">3.3. Resorte con Fallas</td>
                <td class="sub-title" style="width:{{ $wGanchos34 }};">3.4. Función de Bloqueo de Conector</td>
                <td class="sub-title" style="width:{{ $wGanchos35 }};">3.5. Corrosión</td>
                <td class="sub-title" style="width:{{ $wEtiquetaFaltante }};">Faltante</td>
                <td class="sub-title" style="width:{{ $wEtiquetaLegible }};">Legible</td>
                <td class="sub-title" style="width:{{ $wAccionDaniada }};">La Línea de Vida se Marca como Dañada y es Sacado de Uso</td>
                <td class="sub-title" style="width:{{ $wAccionBuenas }};">La Línea de Vida está en Buenas Condiciones</td>
            </tr>
        </table>

        <table class="data-table" style="margin-top: 6px;">
            <colgroup>
                @foreach($columnWidths as $width)
                    <col style="width: {{ $width }};">
                @endforeach
            </colgroup>

            @for ($i = 0; $i < 6; $i++)
                @php
                    $row = $chunk[$i] ?? [];
                    $accion = data_get($row, 'acciones', '');
                @endphp

                <tr>
                    <td style="width:{{ $wNumeroLineaVida }};">{{ data_get($row, 'numero_linea_vida') }}</td>

                    <td style="width:{{ $wLineaVida11 }};">{{ data_get($row, 'linea_vida_1_1') }}</td>
                    <td style="width:{{ $wLineaVida12 }};">{{ data_get($row, 'linea_vida_1_2') }}</td>
                    <td style="width:{{ $wLineaVida13 }};">{{ data_get($row, 'linea_vida_1_3') }}</td>

                    <td style="width:{{ $wAmortiguador21 }};">{{ data_get($row, 'amortiguador_2_1') }}</td>
                    <td style="width:{{ $wAmortiguador22 }};">{{ data_get($row, 'amortiguador_2_2') }}</td>
                    <td style="width:{{ $wAmortiguador23 }};">{{ data_get($row, 'amortiguador_2_3') }}</td>

                    <td style="width:{{ $wGanchos31 }};">{{ data_get($row, 'ganchos_3_1') }}</td>
                    <td style="width:{{ $wGanchos32 }};">{{ data_get($row, 'ganchos_3_2') }}</td>
                    <td style="width:{{ $wGanchos33 }};">{{ data_get($row, 'ganchos_3_3') }}</td>
                    <td style="width:{{ $wGanchos34 }};">{{ data_get($row, 'ganchos_3_4') }}</td>
                    <td style="width:{{ $wGanchos35 }};">{{ data_get($row, 'ganchos_3_5') }}</td>

                    <td style="width:{{ $wEtiquetaFaltante }};">{{ data_get($row, 'etiqueta_faltante') }}</td>
                    <td style="width:{{ $wEtiquetaLegible }};">{{ data_get($row, 'etiqueta_legible') }}</td>

                    <td colspan="2" style="width:{{ ((float) $wAccionDaniada + (float) $wAccionBuenas) }}%; text-align: center;">
                        {{ $accion }}
                    </td>

                    <td style="width:{{ $wObservaciones }}; text-align:left; padding-left:4px;">{{ data_get($row, 'observaciones') }}</td>
                </tr>
            @endfor
        </table>

        <table style="
            width: 99.6%;
            margin: 6px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        ">
            <colgroup>
                @for ($i = 0; $i < 17; $i++)
                    <col style="width: {{ $i === 0 ? '8%' : '5.75%' }}">
                @endfor
            </colgroup>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="4" style="
                    border: 1px solid #000;
                    text-align: center;
                    font-weight: bold;
                ">
                    Guía para la Inspección
                </td>
        
                <td colspan="13" style="border: none;">
                    &nbsp;
                </td>
            </tr>
        
            <!-- FILA 2 (IMAGEN) -->
            <tr>
                <!-- IZQUIERDA (IMAGEN) -->
                <td colspan="4" style="
                    border: 1px solid #000;
                    border: none;
                    text-align: center;
                ">
                    <img
                        src="{{ public_path('images/forms/SST_POP_TA_04_FO_03_Inspeccion_de_Linea_de_Vida/InspeccLineaVida.png') }}"
                        style="
                            width: 160px;
                            height: 210px;
                            object-fit: contain;
                            display: block;
                            margin: 0 auto;
                        "
                    >
                </td>
            
                <!-- ESPACIO VACÍO -->
                <td colspan="2" style="border: none;"></td>
            
                <!-- FIRMA (COLUMNAS 8-9-10) -->
                <td colspan="4" style="
                    border: none;
                    text-align: center;
                    vertical-align: middle;
                ">
            
                    @if(!empty($firmaSrc))
                    <img
                        src="{{ $firmaSrc }}"
                        style="
                            width: 140px;
                            height: 55px;
                            object-fit: contain;
                            display: block;
                            margin: 0 auto 4px auto;
                        "
                    >
                    @endif
            
                    <!-- NOMBRE -->
                    <div style="
                        font-size: 8px;
                        margin-bottom: 0px;
                    ">
                        {{ $nombreInspector }}
                    </div>
            
                    <!-- LÍNEA -->
                    <div style="
                        width: 250px;
                        border-bottom: 1px solid #000;
                        margin: 0 auto;
                    "></div>
            
                    <!-- TEXTO -->
                    <div style="
                        font-size: 7px;
                        font-weight:bold;
                        margin-top: 2px;
                    ">
                        Nombre y Firma del colaborador que realiza el checklist
                    </div>
            
                </td>
            
                <!-- ESPACIO RESTANTE -->
                <td colspan="7" style="border: none;"></td>
            </tr>
        </table>

    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>