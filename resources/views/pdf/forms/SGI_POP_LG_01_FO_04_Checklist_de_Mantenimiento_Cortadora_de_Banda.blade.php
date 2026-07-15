<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Mantenimiento Cortadora de Banda' }}</title>

    <style>
        @page {
            margin: 18px;
            size: A4 portrait;
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

        $taller = $answers['taller'] ?? '';
    @endphp

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
                    CODIFICACIÓN: SGI-POP-LG-01-FO-04
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    CHECKLIST MANTENIMIENTO DE CORTADORA DE BANDA
                </td>

                <td class="right-cell">
                    REVISIÓN: 01
                </td>
            </tr>
        </table>

        <!-- FECHA Y TALLER -->
        <table class="header-table" style="margin-top: 15px;">
            <tr>
                <td style="width:35%; text-align:center; font-weight:bold;">
                    FECHA: {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                </td>

                <td style="width:20%; border:none !important; background:#fff;"></td>

                <td style="width:45%; text-align:center; font-weight:bold;">
                    TALLER / US: {{ $taller }}
                </td>
            </tr>
        </table>

        <!-- INDICACIÓN -->
        <div style="margin-top: 10px; text-align: center; font-weight: bold; font-style: italic; font-size: 8px;">
            Considerar los siguientes criterios de acuerdo a las condiciones de la cortadora de banda.
        </div>

        @php
            $rowHeight = 10; // ALTO DE FILAS
        @endphp
        
        <!-- TABLA 1 - 5 COLUMNAS X 12 FILAS -->
        <table style="
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="2" rowspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    DESCRIPCIÓN
                </td>
        
                <td colspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    CONDICIONES
                </td>
        
                <td rowspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    OBSERVACIONES
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    BUENAS
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    MALAS
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#e5e7eb;
                ">
                    CONDICIONES MECÁNICAS:
                </td>
            </tr>
        
            <!-- FILA 4 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle; font-weight:bold;">
                    1.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">
                    Moto-reductores
                </td>
        
                <td colspan="3" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px;"></td>
            </tr>
        
            <!-- FILA 5 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    1.1.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Niveles de aceite
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    {{ ($answers['mecanicas_1_1_niveles_aceite_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    {{ ($answers['mecanicas_1_1_niveles_aceite_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $answers['mecanicas_1_1_niveles_aceite_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 6 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    1.2.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Alineamiento de cadenas
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_2_alineamiento_cadenas_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_2_alineamiento_cadenas_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_1_2_alineamiento_cadenas_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 7 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    1.3.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Baleros de los motores
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_3_baleros_motores_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_3_baleros_motores_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_1_3_baleros_motores_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 8 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    1.4.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Chumaceras
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_4_chumaceras_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_4_chumaceras_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_1_4_chumaceras_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 9 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    1.5.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Tren de engranaje
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_5_tren_engranaje_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_1_5_tren_engranaje_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_1_5_tren_engranaje_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 10 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle; font-weight:bold;">
                    2.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">
                    Mesa de Corte
                </td>
        
                <td colspan="3" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px;"></td>
            </tr>
        
            <!-- FILA 11 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    2.1.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Balero de chumaceras
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_2_1_balero_chumaceras_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_2_1_balero_chumaceras_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_2_1_balero_chumaceras_observaciones'] ?? '' }}
                </td>
            </tr>
        
            <!-- FILA 12 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                    2.2.
                </td>
        
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                    Tornillería
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_2_2_tornilleria_estado'] ?? '') === 'Buenas' ? '✔' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">
                    {{ ($answers['mecanicas_2_2_tornilleria_estado'] ?? '') === 'Malas' ? 'x' : '' }}
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">
                    {{ $answers['mecanicas_2_2_tornilleria_observaciones'] ?? '' }}
                </td>
            </tr>
        </table>
        
        <!-- TABLA 2 - 5 COLUMNAS X 11 FILAS -->
        <table style="
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="5" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold; background:#e5e7eb;">
                    CONDICIONES ELECTRICAS:
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle; font-weight:bold;">1.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">Tablero de Control</td>
                <td colspan="3" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px;"></td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">1.1.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Variadores de velocidad</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_1_variadores_velocidad_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_1_variadores_velocidad_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_1_1_variadores_velocidad_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 4 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">1.2.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Contactores</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_2_contactores_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_2_contactores_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_1_2_contactores_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 5 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">1.3.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Conexiones</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_3_conexiones_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_3_conexiones_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_1_3_conexiones_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 6 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">1.4.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Selector de motor</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_4_selector_motor_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_1_4_selector_motor_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_1_4_selector_motor_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 7 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle; font-weight:bold;">2.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">Motores</td>
                <td colspan="3" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px;"></td>
            </tr>
        
            <!-- FILA 8 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">2.1.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Conexiones</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_2_1_conexiones_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_2_1_conexiones_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_2_1_conexiones_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 9 -->
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">2.2.</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">Aislantes</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_2_2_aislantes_estado'] ?? '') === 'Buenas' ? '✔' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold;">{{ ($answers['electricas_2_2_aislantes_estado'] ?? '') === 'Malas' ? 'x' : '' }}</td>
                <td style="border:1px solid #000; text-align:center; vertical-align:middle;">{{ $answers['electricas_2_2_aislantes_observaciones'] ?? '' }}</td>
            </tr>
        
            <!-- FILA 10 -->
            <tr>
                <td colspan="5" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">
                    OBSERVACIONES:
                </td>
            </tr>
        
            <!-- FILA 11 -->
            <tr>
                <td colspan="5" style="border:1px solid #000; height:40px; padding:3px; text-align:left; vertical-align:middle;">
                    {{ $answers['observaciones_generales'] ?? '' }}
                </td>
            </tr>
        </table>
        
        <!-- TABLA 3 -->
        <table style="
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:50%; padding:0; border:none; height:0;"></td>
                <td style="width:50%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    RESPONSABLE MANTENIMIENTO
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    FIRMA RESPONSABLE MANTENIMIENTO
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <!-- NOMBRE -->
                <td style="
                    border:1px solid #000;
                    height:60px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $answers['nombre_responsable_mantenimiento'] ?? '' }}
                </td>
        
                <!-- FIRMA -->
                <td style="
                    border:1px solid #000;
                    height:60px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    @php
                        $firmaResponsable = $answers['firma_responsable_mantenimiento'] ?? null;
                        $firmaResponsableSrc = null;
        
                        if ($firmaResponsable) {
                            $firmaPath = storage_path('app/public/' . $firmaResponsable);
        
                            if (file_exists($firmaPath)) {
                                $firmaResponsableSrc =
                                    'data:image/png;base64,' .
                                    base64_encode(file_get_contents($firmaPath));
                            }
                        }
                    @endphp
        
                    @if($firmaResponsableSrc)
                        <img
                            src="{{ $firmaResponsableSrc }}"
                            style="
                                max-width: 180px;
                                max-height: 50px;
                                object-fit: contain;
                            "
                        >
                    @endif
                </td>
            </tr>
        </table>

    </div>
</body>
</html>