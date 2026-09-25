<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <title>
        {{ $form->title ?? 'Reporte de Mantenimiento de Prensas' }}
    </title>

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
        /*
         * Obtiene los campos definidos en el Definition.
         */
        $fieldsCollection = collect($fields ?? []);

        $getField = fn($id) =>
            $fieldsCollection->firstWhere('id', $id);


        /*
         * LOGO
         */
        $logo = $getField('encabezado_logo');

        $logoSrc = null;

        if ($logo && !empty($logo['url'])) {
            $path = public_path(
                ltrim($logo['url'], '/')
            );

            if (file_exists($path)) {
                $logoSrc =
                    'data:image/png;base64,' .
                    base64_encode(
                        file_get_contents($path)
                    );
            }
        }
    @endphp


    <div class="sheet">

        <!-- ======================================= -->
        <!-- ENCABEZADO                              -->
        <!-- ======================================= -->

        <table class="header-table">

            <!-- DEFINICIÓN DE ANCHOS -->
            <tr style="height:0; line-height:0;">

                <td style="
                    width:25%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:45%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:30%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

            </tr>


            <!-- FILA 1 -->
            <tr>

                <!-- LOGO UTILIZA LAS 4 FILAS -->
                <td
                    rowspan="4"
                    class="logo-cell"
                >
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>


                <!-- EMPRESA UTILIZA FILAS 1 Y 2 -->
                <td
                    rowspan="2"
                    class="center-cell"
                >
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>


                <!-- PÁGINA -->
                <td class="right-cell">
                    PÁGINA: 1 DE 1
                </td>

            </tr>


            <!-- FILA 2 -->
            <tr>

                <!-- FECHA DE EMISIÓN -->
                <td class="right-cell">
                    FECHA EMISIÓN: 11/09/2026
                </td>

            </tr>


            <!-- FILA 3 -->
            <tr>

                <!-- SISTEMA -->
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>


                <!-- CÓDIGO -->
                <td class="right-cell">
                    CÓDIGO: SGI-POP-FO-05
                </td>

            </tr>


            <!-- FILA 4 -->
            <tr>

                <!-- NOMBRE DEL FORMATO -->
                <td class="center-cell">
                    REPORTE DE MANTENIMIENTO DE PRENSAS
                </td>


                <!-- REVISIÓN -->
                <td class="right-cell">
                    REVISIÓN: 01
                </td>

            </tr>

        </table>


        <!-- ======================================= -->
        <!-- TABLA BASE - 13 COLUMNAS X 4 FILAS      -->
        <!-- ======================================= -->

        @php
            /*
             * ==========================================================
             * ANCHO DE LAS 13 COLUMNAS
             * ==========================================================
             *
             * Modifica SOLAMENTE estos valores.
             *
             * Son porcentajes.
             * La suma de las 13 columnas debe ser 100.
             *
             * Actualmente todas tienen el mismo ancho:
             * 100 / 13 = 7.692307 %
             */

            $col1  = 2;
            $col2  = 5;
            $col3  = 9;
            $col4  = 2;
            $col5  = 10;
            $col6  = 13;
            $col7  = 2;
            $col8  = 15;
            $col9  = 15;
            $col10 = 2;
            $col11 = 10;
            $col12 = 13;
            $col13 = 2;


            /*
             * ==========================================================
             * ALTO DE LAS 4 FILAS
             * ==========================================================
             *
             * Modifica SOLAMENTE estos valores.
             * Están expresados en píxeles.
             */

            $altoFila1 = 2;
            $altoFila2 = 2;
            $altoFila3 = 30;
            $altoFila4 = 10;

            /*
             * ==========================================================
             * DATOS DEL REGISTRO
             * ==========================================================
             */
            $fechaReporte = $submission?->created_at
                ? \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y')
                : '';

            $numeroReporte = $answers['numero_reporte'] ?? '';
            $taller = $answers['taller'] ?? '';
            $numeroSerie = $answers['numero_serie'] ?? '';
        @endphp


        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
            margin-top:0;
        ">

            <!-- =================================== -->
            <!-- DEFINICIÓN DE LAS 13 COLUMNAS      -->
            <!-- =================================== -->

            <tr style="height:0; line-height:0;">

                <td style="
                    width:{{ $col1 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col2 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col3 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col4 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col5 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col6 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col7 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col8 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col9 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col10 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col11 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col12 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

                <td style="
                    width:{{ $col13 }}%;
                    padding:0;
                    border:none;
                    height:0;
                "></td>

            </tr>


            <!-- =================================== -->
            <!-- FILA 1                              -->
            <!-- =================================== -->

            <tr>

                @for($columna = 1; $columna <= 13; $columna++)

                    @php
                        $esPrimera = $columna === 1;
                        $esUltima  = $columna === 13;
                    @endphp

                    <td style="
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        border:none;

                        @if($esPrimera)
                            border-left:1px solid #000;
                        @elseif($esUltima)
                            border-right:1px solid #000;
                        @endif
                    ">
                        <div style="
                            height:{{ $altoFila1 }}px;
                            line-height:0;
                            padding:0;
                            margin:0;
                            font-size:0;
                        ">&nbsp;</div>
                    </td>

                @endfor

            </tr>


            <!-- =================================== -->
            <!-- FILA 2                              -->
            <!-- =================================== -->

            <tr>

                @for($columna = 1; $columna <= 13; $columna++)

                    @php
                        $esPrimera = $columna === 1;
                        $esUltima  = $columna === 13;
                    @endphp

                    <td style="
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        border:none;

                        @if($esPrimera)
                            border-left:1px solid #000;
                        @elseif($esUltima)
                            border-right:1px solid #000;
                        @endif
                    ">
                        <div style="
                            height:{{ $altoFila2 }}px;
                            line-height:0;
                            padding:0;
                            margin:0;
                            font-size:0;
                        ">&nbsp;</div>
                    </td>

                @endfor

            </tr>


            <!-- =================================== -->
            <!-- FILA 3                              -->
            <!-- =================================== -->

            <tr>

                <!-- COLUMNA 1 -->
                <td style="
                    padding:0;
                    border:none;
                    border-left:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:0;
                        padding:0;
                        margin:0;
                        font-size:0;
                    ">&nbsp;</div>
                </td>


                <!-- COLUMNA 2 - FECHA -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                    ">
                        Fecha:
                    </div>
                </td>


                <!-- COLUMNA 3 - DATO FECHA -->
                <td style="
                    padding:0;
                    border:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                        position:relative;
                        top:-2px;
                    ">
                        {{ $fechaReporte }}
                    </div>
                </td>


                <!-- COLUMNA 4 - BLANCO -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:0;
                        padding:0;
                        margin:0;
                        font-size:0;
                    ">&nbsp;</div>
                </td>


                <!-- COLUMNA 5 - NO. REPORTE -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                    ">
                        No. Reporte:
                    </div>
                </td>


                <!-- COLUMNA 6 - DATO NO. REPORTE -->
                <td style="
                    padding:0;
                    border:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                        position:relative;
                        top:-2px;
                    ">
                        {{ $numeroReporte }}
                    </div>
                </td>


                <!-- COLUMNA 7 - BLANCO -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:0;
                        padding:0;
                        margin:0;
                        font-size:0;
                    ">&nbsp;</div>
                </td>


                <!-- COLUMNA 8 - UNIDAD DE SERVICIO -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                    ">
                        Unidad de Servicio:
                    </div>
                </td>


                <!-- COLUMNA 9 - DATO TALLER -->
                <td style="
                    padding:0;
                    border:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                        position:relative;
                        top:-2px;
                    ">
                        {{ $taller }}
                    </div>
                </td>


                <!-- COLUMNA 10 - BLANCO -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:0;
                        padding:0;
                        margin:0;
                        font-size:0;
                    ">&nbsp;</div>
                </td>


                <!-- COLUMNA 11 - NO. DE SERIE -->
                <td style="
                    padding:0;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                    ">
                        No. de Serie:
                    </div>
                </td>


                <!-- COLUMNA 12 - DATO NO. DE SERIE -->
                <td style="
                    padding:0;
                    border:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    white-space:nowrap;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:{{ $altoFila3 }}px;
                        padding:0;
                        margin:0;
                        white-space:nowrap;
                        position:relative;
                        top:-2px;
                    ">
                        {{ $numeroSerie }}
                    </div>
                </td>


                <!-- COLUMNA 13 -->
                <td style="
                    padding:0;
                    border:none;
                    border-right:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <div style="
                        height:{{ $altoFila3 }}px;
                        line-height:0;
                        padding:0;
                        margin:0;
                        font-size:0;
                    ">&nbsp;</div>
                </td>

            </tr>


            <!-- =================================== -->
            <!-- FILA 4                              -->
            <!-- =================================== -->

            <tr>

                @for($columna = 1; $columna <= 13; $columna++)

                    @php
                        $esPrimera = $columna === 1;
                        $esUltima  = $columna === 13;
                        $esCentral = $columna >= 2 && $columna <= 12;
                    @endphp

                    <td style="
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        border:none;

                        @if($esPrimera)
                            border-left:1px solid #000;
                            border-bottom:1px solid #000;
                        @elseif($esUltima)
                            border-right:1px solid #000;
                            border-bottom:1px solid #000;
                        @elseif($esCentral)
                            border-bottom:1px solid #000;
                        @endif
                    ">
                        <div style="
                            height:{{ $altoFila4 }}px;
                            line-height:0;
                            padding:0;
                            margin:0;
                            font-size:0;
                        ">&nbsp;</div>
                    </td>

                @endfor

            </tr>

        </table>


        <!-- ======================================= -->
        <!-- SEGUNDA TABLA - 11 COLUMNAS X 9 FILAS  -->
        <!-- ======================================= -->

        @php
            /*
             * ==========================================================
             * ANCHO DE LAS 11 COLUMNAS DE LA SEGUNDA TABLA
             * ==========================================================
             * Modifica SOLAMENTE estos valores.
             * Son porcentajes y deben sumar 100.
             */
            $tabla2Col1  = 5;
            $tabla2Col2  = 5;
            $tabla2Col3  = 7.5;
            $tabla2Col4  = 7.5;
            $tabla2Col5  = 25;
            $tabla2Col6  = 2.5;
            $tabla2Col7  = 2.5;
            $tabla2Col8  = 7.5;
            $tabla2Col9  = 7.5;
            $tabla2Col10 = 25;
            $tabla2Col11 = 5;

            /*
             * ==========================================================
             * ALTO DE LAS 9 FILAS DE LA SEGUNDA TABLA
             * ==========================================================
             * Modifica SOLAMENTE estos valores.
             * Están expresados en píxeles.
             */
            $tabla2AltoFila1 = 28;
            $tabla2AltoFila2 = 26;
            $tabla2AltoFila3 = 26;
            $tabla2AltoFila4 = 26;
            $tabla2AltoFila5 = 26;
            $tabla2AltoFila6 = 26;
            $tabla2AltoFila7 = 26;
            $tabla2AltoFila8 = 26;
            $tabla2AltoFila9 = 30;

            $tabla2AltosFilas = [
                $tabla2AltoFila1,
                $tabla2AltoFila2,
                $tabla2AltoFila3,
                $tabla2AltoFila4,
                $tabla2AltoFila5,
                $tabla2AltoFila6,
                $tabla2AltoFila7,
                $tabla2AltoFila8,
                $tabla2AltoFila9,
            ];


            /*
             * ==========================================================
             * IMÁGENES TIPO DE PRENSA
             * ==========================================================
             */
            $tipoPrensa01Src = null;
            $tipoPrensa02Src = null;

            $tipoPrensa01Path = public_path(
                'images/forms/SGI_POP_FO_05_Reporte_de_Mantenimiento_de_Prensas/TipoPrensa01.png'
            );

            $tipoPrensa02Path = public_path(
                'images/forms/SGI_POP_FO_05_Reporte_de_Mantenimiento_de_Prensas/TipoPrensa02.png'
            );

            if (file_exists($tipoPrensa01Path)) {
                $tipoPrensa01Src =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($tipoPrensa01Path));
            }

            if (file_exists($tipoPrensa02Path)) {
                $tipoPrensa02Src =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($tipoPrensa02Path));
            }

            $altoImagenTipoPrensa =
                $tabla2AltoFila3 +
                $tabla2AltoFila4 +
                $tabla2AltoFila5 +
                $tabla2AltoFila6;
        @endphp


        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
            margin-top:0;
        ">

            <!-- DEFINICIÓN DE LAS 11 COLUMNAS -->
            <tr style="height:0; line-height:0;">

                <td style="width:{{ $tabla2Col1 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col2 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col3 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col4 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col5 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col6 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col7 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col8 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col9 }}%;  padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col10 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla2Col11 }}%; padding:0; border:none; height:0;"></td>

            </tr>


            <!-- =================================== -->
            <!-- FILA 1                              -->
            <!-- =================================== -->
            
            <tr>
                <td
                    colspan="11"
                    style="
                        border:1px solid #000;
                        border-top:none;
                        background:#e5e7eb;
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        font-weight:bold;
                        height:{{ $tabla2AltoFila1 }}px;
                    "
                >
                    ELIJA EL TIPO DE PRENSA Y EL TIPO DE VOLTAJE
                </td>
            </tr>


            <!-- =================================== -->
            <!-- FILAS 2 A 9                         -->
            <!-- =================================== -->

            @for($filaTabla2 = 2; $filaTabla2 <= 9; $filaTabla2++)

                @php
                    $altoFilaTabla2 = ${'tabla2AltoFila' . $filaTabla2};
                    $esUltimaFilaTabla2 = $filaTabla2 === 9;
                @endphp

                <tr>

                    <!-- =================================== -->
                    <!-- COLUMNA 1                           -->
                    <!-- VISUALMENTE UNIDA DE FILA 2 A 9    -->
                    <!-- SIN BORDE SUPERIOR NI DERECHO       -->
                    <!-- =================================== -->
                    <td style="
                        padding:0;
                        border:none;
                        border-left:1px solid #000;

                        @if($esUltimaFilaTabla2)
                            border-bottom:1px solid #000;
                        @endif

                        text-align:center;
                        vertical-align:middle;
                    ">
                        <div style="
                            height:{{ $altoFilaTabla2 }}px;
                            line-height:0;
                            padding:0;
                            margin:0;
                            font-size:0;
                        ">&nbsp;</div>
                    </td>


                    <!-- =================================== -->
                    <!-- FILAS 2 Y 7                         -->
                    <!-- COLUMNAS 2 A 10 UNIDAS              -->
                    <!-- EN BLANCO Y SIN BORDES              -->
                    <!-- =================================== -->
                    @if($filaTabla2 === 2 || $filaTabla2 === 7)

                        <td
                            colspan="9"
                            style="
                                border:none;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                            "
                        >
                            <div style="
                                height:{{ $altoFilaTabla2 }}px;
                                line-height:0;
                                padding:0;
                                margin:0;
                                font-size:0;
                            ">&nbsp;</div>
                        </td>


                    <!-- =================================== -->
                    <!-- FILA 3                              -->
                    <!-- =================================== -->
                    @elseif($filaTabla2 === 3)

                        <!-- COLUMNA 2 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 3 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 4 - CAJA -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            CAJA
                        </td>

                        <!-- COLUMNA 5 - FILAS 3 A 6 UNIDAS - IMAGEN TIPO PRENSA 01 -->
                        <td
                            rowspan="4"
                            style="
                                border:none;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                                height:{{ $altoImagenTipoPrensa }}px;
                            "
                        >
                            @if($tipoPrensa01Src)
                                <img
                                    src="{{ $tipoPrensa01Src }}"
                                    style="
                                        max-width:75%;
                                        max-height:{{ $altoImagenTipoPrensa - 4 }}px;
                                        display:block;
                                        margin:0 auto;
                                    "
                                >
                            @endif
                        </td>

                        <!-- COLUMNA 6 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNAS 7 Y 8 UNIDAS - BLANCO SIN BORDES -->
                        <td colspan="2" style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 9 - RIELES -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            RIELES
                        </td>

                        <!-- COLUMNA 10 - FILAS 3 A 6 UNIDAS - IMAGEN TIPO PRENSA 02 -->
                        <td
                            rowspan="4"
                            style="
                                border:none;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                                height:{{ $altoImagenTipoPrensa }}px;
                            "
                        >
                            @if($tipoPrensa02Src)
                                <img
                                    src="{{ $tipoPrensa02Src }}"
                                    style="
                                        max-width:75%;
                                        max-height:{{ $altoImagenTipoPrensa - 4 }}px;
                                        display:block;
                                        margin:0 auto;
                                    "
                                >
                            @endif
                        </td>


                    <!-- =================================== -->
                    <!-- FILA 4                              -->
                    <!-- =================================== -->
                    @elseif($filaTabla2 === 4)

                        <!-- COLUMNA 2 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 3 - BLANCO, UNIDA VISUALMENTE FILAS 4 A 6 -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 4 - 440 V -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            440 V
                        </td>


                        <!-- COLUMNA 6 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNAS 7 Y 8 UNIDAS - BLANCO SIN BORDES -->
                        <td colspan="2" style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 9 - 440 V -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            440 V
                        </td>



                    <!-- =================================== -->
                    <!-- FILA 5                              -->
                    <!-- =================================== -->
                    @elseif($filaTabla2 === 5)

                        <!-- COLUMNA 2 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 3 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 4 - 220 V -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            220 V
                        </td>


                        <!-- COLUMNA 6 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNAS 7 Y 8 UNIDAS - BLANCO SIN BORDES -->
                        <td colspan="2" style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 9 - 220 V -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            220 V
                        </td>



                    <!-- =================================== -->
                    <!-- FILA 6                              -->
                    <!-- =================================== -->
                    @elseif($filaTabla2 === 6)

                        <!-- COLUMNA 2 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 3 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 4 - 110 V -->
                        <td style="
                            border:none;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            110 V
                        </td>


                        <!-- COLUMNA 6 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNAS 7, 8 Y 9 UNIDAS - BLANCO SIN BORDES -->
                        <td colspan="3" style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>



                    <!-- =================================== -->
                    <!-- FILA 8                              -->
                    <!-- =================================== -->
                    @elseif($filaTabla2 === 8)

                        <!-- COLUMNAS 2 Y 3 UNIDAS - CÓDIGO -->
                        <td
                            colspan="2"
                            style="
                                border:none;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                                font-weight:bold;
                                height:{{ $altoFilaTabla2 }}px;
                            "
                        >
                            CÓDIGO
                        </td>

                        <!-- COLUMNA 4 -->
                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:{{ $altoFilaTabla2 }}px; padding:0; margin:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 5 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 6 - BLANCO -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNAS 7 Y 8 UNIDAS - CÓDIGO -->
                        <td
                            colspan="2"
                            style="
                                border:none;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                                font-weight:bold;
                                height:{{ $altoFilaTabla2 }}px;
                            "
                        >
                            CÓDIGO
                        </td>

                        <!-- COLUMNA 9 -->
                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:{{ $altoFilaTabla2 }}px; padding:0; margin:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 10 - BLANCO SIN BORDES -->
                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>


                    <!-- =================================== -->
                    <!-- FILA 9                              -->
                    <!-- COLUMNAS 2 A 10 UNIDAS              -->
                    <!-- EN BLANCO Y SIN BORDES              -->
                    <!-- =================================== -->
                    @else

                        <td
                            colspan="9"
                            style="
                                border:none;
                                border-bottom:1px solid #000;
                                padding:0;
                                text-align:center;
                                vertical-align:middle;
                            "
                        >
                            <div style="
                                height:{{ $altoFilaTabla2 }}px;
                                line-height:0;
                                padding:0;
                                margin:0;
                                font-size:0;
                            ">&nbsp;</div>
                        </td>

                    @endif


                    <!-- =================================== -->
                    <!-- COLUMNA 11                          -->
                    <!-- VISUALMENTE UNIDA DE FILA 2 A 9    -->
                    <!-- SIN BORDE SUPERIOR NI IZQUIERDO     -->
                    <!-- =================================== -->
                    <td style="
                        padding:0;
                        border:none;
                        border-right:1px solid #000;

                        @if($esUltimaFilaTabla2)
                            border-bottom:1px solid #000;
                        @endif

                        text-align:center;
                        vertical-align:middle;
                    ">
                        <div style="
                            height:{{ $altoFilaTabla2 }}px;
                            line-height:0;
                            padding:0;
                            margin:0;
                            font-size:0;
                        ">&nbsp;</div>
                    </td>

                </tr>

            @endfor

        </table>

    </div>

</body>
</html>