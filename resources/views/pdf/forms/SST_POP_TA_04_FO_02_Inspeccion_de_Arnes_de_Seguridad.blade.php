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
            width: 110%;
            transform: scale(0.86);
            transform-origin: top left;
            margin-left: 30px;
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

        .text-block {
            text-align: center;
            margin-top: 12px;
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
                    CODIFICACIÓN: SST-POP-TA-04-FO-02
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
                    INSPECCIÓN DE ARNÉS DE SEGURIDAD
                </td>

                <td colspan="2" class="right-cell">
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

        <!-- TABLA -->
        <table class="criteria-table">
            <tr>
                <td style="border:none;"></td>
                <td colspan="17" class="top-title">
                    Criterios de Inspección (SI &nbsp;&nbsp; NO &nbsp;&nbsp; N/A)
                </td>
            </tr>

            <tr>
                <td rowspan="2" class="group-title">N° de Arnés:</td>

                <td colspan="5" class="group-title">CORREAS Y COSTURAS</td>
                <td colspan="3" class="group-title">D-RING</td>
                <td colspan="4" class="group-title">HEBILLAS</td>
                <td colspan="2" class="group-title">ETIQUETA</td>
                <td colspan="2" class="group-title">Acciones:</td>
                <td rowspan="2" class="group-title">Observaciones</td>
            </tr>

            <tr>
                <td class="sub-title">1. De Hombros: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title">2. Del Pecho: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title">3. De Espalda: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title">4. De Piernas: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>
                <td class="sub-title">5. De Cintura (Sí Aplica): Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas</td>

                <td class="sub-title">6. Dorsal: Gastados, Oxidados</td>
                <td class="sub-title">7. De Cintura (Sí Aplica): Gastados, Oxidados</td>
                <td class="sub-title">8. De Esternón (Sí Aplica): Gastados, Oxidados</td>

                <td class="sub-title">9. Ajuste en Hombros: Flojas</td>
                <td class="sub-title">10. Pecho y Espalda: Flojas, Oxidadas, Gastadas</td>
                <td class="sub-title">11. Mosquetón de Pecho (Sí Aplica): Flojas, Oxidadas, Gastadas</td>
                <td class="sub-title">12. Ajuste en Piernas: Flojas</td>

                <td class="sub-title">Faltante</td>
                <td class="sub-title">Legible</td>

                <td class="sub-title">El Arnés se marca como dañado y es sacado de uso</td>
                <td class="sub-title">El Arnés está en buenas condiciones</td>
            </tr>
        </table>

        @php
            $rows = data_get($answers, 'tabla_arnes_seguridad', []);

            $filasConDatos = collect($rows)->filter(function ($row) {
                return !empty(array_filter($row, fn($value) => $value !== null && $value !== ''));
            })->values();
        @endphp

        <table class="data-table" style="margin-top: 6px;">
            @php
                $minFilas = 7;
                $totalFilas = max($minFilas, $filasConDatos->count());
            @endphp

            @for ($i = 0; $i < $totalFilas; $i++)
                @php
                    $row = $filasConDatos[$i] ?? [];
                    $accion = data_get($row, 'acciones', '');
                @endphp

                <tr>
                    <td>{{ data_get($row, 'numero_arnes') }}</td>

                    <td>{{ data_get($row, 'correas_1_hombros') }}</td>
                    <td>{{ data_get($row, 'correas_2_pecho') }}</td>
                    <td>{{ data_get($row, 'correas_3_espalda') }}</td>
                    <td>{{ data_get($row, 'correas_4_piernas') }}</td>
                    <td>{{ data_get($row, 'correas_5_cintura') }}</td>

                    <td>{{ data_get($row, 'd_ring_6_dorsal') }}</td>
                    <td>{{ data_get($row, 'd_ring_7_cintura') }}</td>
                    <td>{{ data_get($row, 'd_ring_8_esternon') }}</td>

                    <td>{{ data_get($row, 'hebillas_9_ajuste_hombros') }}</td>
                    <td>{{ data_get($row, 'hebillas_10_pecho_espalda') }}</td>
                    <td>{{ data_get($row, 'hebillas_11_mosqueton_pecho') }}</td>
                    <td>{{ data_get($row, 'hebillas_12_ajuste_piernas') }}</td>

                    <td>{{ data_get($row, 'etiqueta_faltante') }}</td>
                    <td>{{ data_get($row, 'etiqueta_legible') }}</td>

                    <td colspan="2" style="text-align: center;">
                        {{ $accion }}
                    </td>

                    <td>{{ data_get($row, 'observaciones') }}</td>
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
                @for ($i = 0; $i < 18; $i++)
                    <col style="width: {{ $i === 0 ? '8%' : '5.4%' }}">
                @endfor
            </colgroup>

            <!-- FILA 1 -->
            <tr>
                <td colspan="8" class="group-title" style="
                    border: 1px solid #000;
                    text-align: center;
                    font-weight: bold;
                ">
                    Guía para la Inspección
                </td>

                <td colspan="14" style="border: none;">
                    &nbsp;
                </td>
            </tr>

            <!-- FILA 2 (IMAGEN + FIRMA) -->
            <tr>
            
                <!-- IMAGEN (COLUMNAS 1 A 8) -->
                <td colspan="8" style="
                    border: none;
                    text-align: center;
                ">
                    <img
                        src="{{ public_path('images/forms/SST_POP_TA_04_FO_02_Inspeccion_de_Arnes_de_Seguridad/ArnesSeguridad.png') }}"
                        style="
                            width: 220px;
                            height: 230px;
                            object-fit: contain;
                            display: block;
                            margin: 0 auto;
                        "
                    >
                </td>
            
                <!-- ESPACIO -->
                <td colspan="2" style="border: none;"></td>
            
                <!-- FIRMA (COLUMNAS 11-12-13) -->
                <td colspan="3" style="
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
            
                    <div style="font-size: 9px;">
                        {{ $nombreResponsable }}
                    </div>
            
                    <div style="
                        width: 160px;
                        border-bottom: 1px solid #000;
                        margin: 0 auto;
                    "></div>
            
                    <div style="font-size: 7px; margin-top: 2px;">
                        Nombre y Firma del trabajador que elabora el checklist
                    </div>
            
                </td>
            
                <!-- RESTO -->
                <td colspan="5" style="border: none;"></td>
            
            </tr>
        </table>

    </div>
</body>
</html>
