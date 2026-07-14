<!doctype html> 
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Boleta de Observaciones' }}</title>

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
            width: 120%;
            transform: scale(0.80);
            transform-origin: top left;
            margin-left: 18px;
        }

                .header-table {
            width: 99.6%;
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

        .main-table-frame {
            width: 99.6%;
            margin-top: 8px;
            border-right: 1px solid #000;
            box-sizing: border-box;
        }

        .main-table {
            width: 100%;
            margin-top: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table td {
            border: 1px solid #000;
            height: 12px;
            padding: 2px 4px;
            vertical-align: middle;
            font-size: 8px;
            line-height: 1.1;
        }


        .evidence-wrapper {
            width: 100%;
            height: 144px;
            overflow: hidden;
        }

        .evidence-table {
            width: 100%;
            height: 144px;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }

        .evidence-table td {
            border: none;
            padding: 0;
            height: 144px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }

                        .evidence-table img {
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        /* Una imagen normal */
        .evidence-image-normal-single {
            width: 300px;
            height: 136px;
        }

        /* Dos imágenes normales o de proporciones diferentes */
        .evidence-image-normal-double {
            width: 96%;
            height: 136px;
        }

        /* Una imagen extremadamente panorámica */
        .evidence-image-panoramic-single {
            width: 96%;
            height: 136px;
            object-fit: contain;
        }

        /* Dos imágenes extremadamente panorámicas, apiladas */
        .evidence-image-stacked {
            width: 96%;
            height: 66px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        .evidence-row-stacked td {
            height: 72px;
            max-height: 72px;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }

        .bold-center {
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
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

    $fechaRegistro =
        optional($submission->created_at)->format('d/m/Y') ?: '';

    $tallerValor =
        data_get($answers, 'taller', '') ?: '';

    /*
     * Firmas de los observados.
     *
     * Formato nuevo:
     * [
     *     [
     *         'nombre' => 'Nombre',
     *         'firma' => 'forms/signatures/...png',
     *     ],
     * ]
     *
     * También conserva compatibilidad con registros antiguos,
     * donde firma_observado era una sola ruta.
     */
    $firmasObservadosRaw =
        data_get($answers, 'firma_observado', []);

    $nombreObservadoRaw =
        data_get($answers, 'nombre_observado', '') ?: '';

    if (is_string($firmasObservadosRaw)) {
        $firmasObservadosRaw = [
            [
                'nombre' => $nombreObservadoRaw,
                'firma' => $firmasObservadosRaw,
            ],
        ];
    }

    if (!is_array($firmasObservadosRaw)) {
        $firmasObservadosRaw = [];
    }

    $firmasObservados = [];

    foreach ($firmasObservadosRaw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $nombre = trim((string) ($item['nombre'] ?? ''));
        $firma = $item['firma'] ?? '';
        $firmaSrc = null;

        if (is_string($firma) && trim($firma) !== '') {
            $firmaPath = storage_path(
                'app/public/' . ltrim($firma, '/')
            );

            if (file_exists($firmaPath)) {
                $mime =
                    mime_content_type($firmaPath)
                    ?: 'image/png';

                $firmaSrc =
                    'data:' .
                    $mime .
                    ';base64,' .
                    base64_encode(
                        file_get_contents($firmaPath)
                    );
            }
        }

        $firmasObservados[] = [
            'nombre' => $nombre,
            'firma_src' => $firmaSrc,
        ];
    }

    /*
     * Si no existe el arreglo, pero sí hay nombres,
     * mostrarlos aunque todavía no tengan firma.
     */
    if (count($firmasObservados) === 0 && trim($nombreObservadoRaw) !== '') {
        $nombresSinFirma = preg_split(
            '/\r\n|\r|\n/',
            $nombreObservadoRaw
        );

        foreach ($nombresSinFirma as $nombre) {
            $nombre = trim((string) $nombre);

            if ($nombre === '') {
                continue;
            }

            $firmasObservados[] = [
                'nombre' => $nombre,
                'firma_src' => null,
            ];
        }
    }

    /*
     * Evidencias fotográficas.
     * Solo se muestran las primeras 2 imágenes para conservar
     * el espacio fijo de las columnas 9 a 13, filas 25 a 36.
     */
    $evidenciasRaw = data_get($answers, 'evidencia_fotografica', []);

    if (is_string($evidenciasRaw)) {
        $evidenciasRaw = [$evidenciasRaw];
    }

    if (!is_array($evidenciasRaw)) {
        $evidenciasRaw = [];
    }

    $evidenciasSrc = [];

    foreach ($evidenciasRaw as $evidencia) {
        if (count($evidenciasSrc) >= 2) {
            break;
        }

        if (is_array($evidencia)) {
            $evidencia =
                $evidencia['path']
                ?? $evidencia['url']
                ?? $evidencia['file']
                ?? $evidencia['ruta']
                ?? '';
        }

        if (!is_string($evidencia) || trim($evidencia) === '') {
            continue;
        }

        $rutaEvidencia = ltrim(trim($evidencia), '/');
        $evidenciaPath = storage_path('app/public/' . $rutaEvidencia);

        if (!file_exists($evidenciaPath)) {
            $evidenciaPath = public_path($rutaEvidencia);
        }

        if (!file_exists($evidenciaPath)) {
            continue;
        }

        $mimeEvidencia =
            mime_content_type($evidenciaPath)
            ?: 'image/jpeg';

        if (strpos($mimeEvidencia, 'image/') !== 0) {
            continue;
        }

                $dimensionesEvidencia = @getimagesize($evidenciaPath);

        $anchoEvidencia = (int) (
            $dimensionesEvidencia[0] ?? 0
        );

        $altoEvidencia = (int) (
            $dimensionesEvidencia[1] ?? 0
        );

        $relacionEvidencia =
            $altoEvidencia > 0
                ? $anchoEvidencia / $altoEvidencia
                : 1;

        /*
         * Una imagen se considera panorámica solamente cuando
         * su ancho es por lo menos cinco veces mayor que su altura.
         */
        $esPanoramica = $relacionEvidencia >= 5;

        $evidenciasSrc[] = [
            'src' =>
                'data:' .
                $mimeEvidencia .
                ';base64,' .
                base64_encode(
                    file_get_contents($evidenciaPath)
                ),

            'es_panoramica' => $esPanoramica,
        ];
    }

    $cantidadEvidencias = count($evidenciasSrc);

        $cantidadPanoramicas = 0;

    foreach ($evidenciasSrc as $evidenciaPreparada) {
        if (!empty($evidenciaPreparada['es_panoramica'])) {
            $cantidadPanoramicas++;
        }
    }

    /*
     * Solamente se apilan cuando las DOS evidencias
     * son extremadamente panorámicas.
     */
    $apilarEvidencias =
        $cantidadEvidencias === 2
        && $cantidadPanoramicas === 2;
@endphp

<div class="sheet">

    <!-- HEADER -->
    <table class="header-table">
        <!-- ANCHOS DE LAS 3 COLUMNAS -->
        <tr style="height:0; line-height:0;">
            <td style="width:25%; padding:0; border:none; height:0;"></td>
            <td style="width:45%; padding:0; border:none; height:0;"></td>
            <td style="width:30%; padding:0; border:none; height:0;"></td>
        </tr>

        <!-- FILA 1 -->
        <tr>
            <!-- LOGO: OCUPA LAS 4 FILAS -->
            <td rowspan="4" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>

            <!-- VULCANIZACIÓN: OCUPA LAS FILAS DE CÓDIGO Y FECHA -->
            <td rowspan="2" class="center-cell">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>

            <td class="right-cell">
                CÓDIGO: SST-PGI-TA-01-FO-01
            </td>
        </tr>

        <!-- FILA 2 -->
        <tr>
            <td class="right-cell">
                FECHA DE EMISIÓN: 06/06/2025
            </td>
        </tr>

        <!-- FILA 3 -->
        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>

            <td class="right-cell">
                NÚMERO DE REVISIÓN: 00
            </td>
        </tr>

        <!-- FILA 4 -->
        <tr>
            <td class="center-cell">
                BOLETA DE OBSERVACIONES
            </td>

            <td class="right-cell">
                PÁGINA: 01
            </td>
        </tr>
    </table>

    <!-- TABLA BASE 14 x 25 -->
    <div class="main-table-frame">
        <table class="main-table">
    
        <!-- ANCHOS DE COLUMNAS -->
        <tr style="height:0; line-height:0;">
        
            <!-- Columna 1 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 2 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 3 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 4 -->
            <td style="width:10.2%; padding:0; border:none; height:0;"></td>
            <!-- Columna 5 -->
            <td style="width:10.2%; padding:0; border:none; height:0;"></td>
            <!-- Columna 6 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 7 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 8 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 9 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 10 -->
            <td style="width:17.25%; padding:0; border:none; height:0;"></td>
            <!-- Columna 11 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 12 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 13 -->
            <td style="width:17.25%; padding:0; border:none; height:0;"></td>
            <!-- Columna 14 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
        
        </tr>
    
        <!-- FILA 1 -->
        <tr>
            <!-- Columna 1 unida -->
            <td rowspan="23" style="border-right:none;"></td>
    
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 7 unida -->
            <td rowspan="23" style="border-left:none;"></td>
    
            <!-- Columna 8 unida -->
            <td rowspan="23" style="border-right:none;"></td>
    
            <!-- Columnas 9 a 13 -->
            <td colspan="5" class="bold-center" style="border-bottom:none; border-left:none; border-right:none;">
                Falta Cometida
            </td>
    
            <!-- Columna 14 unida -->
            <td rowspan="23" style="border-left:none; border-right:none;"></td>
        </tr>
    
        <!-- FILA 2 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-bottom:none; border-left:none;"></td>
        
            <!-- Columnas 9 y 10 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Acto Inseguro
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 12 y 13 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Desviaciones
            </td>
        </tr>
    
        <!-- FILA 3 -->
        <tr>
            <!-- Columna 2 -->
            <td class="bold-center" style="border-top:none; border-right:none; border-bottom:none; border-left:none;">
                Fecha:
            </td>
    
            <!-- Columna 3 -->
            <td class="center" style="font-weight:bold;">
                {{ $fechaRegistro }}
            </td>
    
            <!-- Columna 4 -->
            <td style="border:none;"></td>
    
            <!-- Columna 5 -->
            <td class="bold-center" style="border-top:none; border-right:none; border-bottom:none; border-left:none;">
                Taller
            </td>
    
            <!-- Columna 6 -->
            <td class="center" style="font-weight:bold;">
                {{ $tallerValor }}
            </td>
    
            <!-- Columna 9 -->
            <td style="border:none;"></td>

            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                BROMAS O DISTRACCIONES EN ÁREA DE TRABAJO
            </td>
            
            <!-- Columna 11 -->
            <td style="border:none;"></td>
            
            <!-- Columna 12 -->
            <td style="border:none;"></td>
            
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO APLICAR PROCEDIMIENTOS DE SEGURIDAD
            </td>
        </tr>

        <!-- FILA 4 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO PORTAR EPP ESPECÍFICO POR ACTIVIDAD
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO APLICAR PROCEDIMIENTOS OPERATIVOS
            </td>
        </tr>

        <!-- FILA 5 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                TRABAJAR CON EQUIPO EN MOVIMIENTO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO PORTAR CREDENCIALES O DOCUMENTOS DE ACCESO
            </td>
        </tr>

        <!-- FILA 6 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-20px; text-align:center;">
                    PLANTA O ÁREA DE TRABAJO:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:0px; text-align:center; font-weight:bold;">
                    {{ data_get($answers, 'planta_area_trabajo', '') }}
                </div>
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                USO DE HERRAMIENTAS EN MAL ESTADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO TRAER TARJETA Y CANDADO P/BLOQUEO
            </td>
        </tr>

        <!-- FILA 7 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                EXCESO DE VELOCIDAD O MOVIMIENTO INAPROPIADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO INFORMAR SITUACIONES ANORMALES O RIESGOS DETECTADOS
            </td>
        </tr>

        <!-- FILA 8 -->
        <tr>
        
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columnas 4 y 5 -->
            <td
                rowspan="2"
                colspan="2"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                    text-align:left;
                    vertical-align:bottom;
                    font-size:7px;
                    padding:0 6px 1px 6px;
                    font-weight:bold;
                "
            >
                @php
                    $nombresPersonal = preg_split(
                        '/\r\n|\r|\n/',
                        data_get($answers, 'nombre_personal_observado', '')
                    );
            
                    $nombresPersonal = array_values(
                        array_filter(
                            array_map(
                                fn ($nombre) => trim((string) $nombre),
                                $nombresPersonal
                            ),
                            fn ($nombre) => $nombre !== ''
                        )
                    );
            
                    $cantidadNombres = count($nombresPersonal);
                @endphp
            
                @if($cantidadNombres === 1)
            
                    <div style="
                        margin:0;
                        line-height:10px;
                        text-align:left;
                    ">
                        {{ $nombresPersonal[0] }}
                    </div>
            
                @elseif($cantidadNombres === 2)
            
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 4px 0 0;
                                vertical-align:bottom;
                                text-align:left;
                                font-size:7px;
                                font-weight:bold;
                                line-height:10px;
                            ">
                                {{ $nombresPersonal[0] }}
                            </td>
            
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 4px;
                                vertical-align:bottom;
                                text-align:left;
                                font-size:7px;
                                font-weight:bold;
                                line-height:10px;
                            ">
                                {{ $nombresPersonal[1] }}
                            </td>
                        </tr>
                    </table>
            
                @elseif($cantidadNombres >= 3)
            
                    @php
                        $columnaIzquierda = [];
                        $columnaDerecha = [];
            
                        foreach ($nombresPersonal as $index => $nombre) {
                            if ($index % 2 === 0) {
                                $columnaIzquierda[] = $nombre;
                            } else {
                                $columnaDerecha[] = $nombre;
                            }
                        }
                        
                        /*
                         * Invertimos cada lado para que los nombres nuevos
                         * se agreguen arriba y los primeros permanezcan abajo.
                         */
                        $columnaIzquierda = array_reverse($columnaIzquierda);
                        $columnaDerecha = array_reverse($columnaDerecha);
                    @endphp
            
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 4px 0 0;
                                vertical-align:bottom;
                                text-align:left;
                                font-size:7px;
                                font-weight:bold;
                                line-height:10px;
                            ">
                                @foreach($columnaIzquierda as $nombre)
                                    <div style="margin:0; line-height:10px;">
                                        {{ $nombre }}
                                    </div>
                                @endforeach
                            </td>
            
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 4px;
                                vertical-align:bottom;
                                text-align:left;
                                font-size:7px;
                                font-weight:bold;
                                line-height:10px;
                            ">
                                @foreach($columnaDerecha as $nombre)
                                    <div style="margin:0; line-height:10px;">
                                        {{ $nombre }}
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    </table>
            
                @endif
            </td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- resto de la fila igual -->

        <!-- FILA 9 -->
        <tr>
        
            <!-- Columnas 2 y 3 -->
            <td colspan="2"
                style="
                    border:none;
                    text-align:center;
                    font-size:7px;
                    padding:0;
                    vertical-align:bottom;
                ">
                <div style="position:relative; top:1px; left:-3px;">
                    NOMBRE DEL PERSONAL OBSERVADO:
                </div>
            </td>
        
            <!-- AQUÍ YA NO VA LA CELDA DE COLUMNAS 4 Y 5 -->
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- resto de la fila igual -->

        <!-- FILA 10 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO REALIZAR BLOQUEOS Y ETIQUETADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 11 -->
        <tr>
            <!-- Columnas 2 a 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- Columnas 4 y 5-->
            <td colspan="2" class="bold-center" style="border:none; font-size:6px; text-align:center;">
                ACTO INSEGURO / CONDICIÓN PELIGROSA / DESVIACIÓN
            </td>
        
            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                DAÑO A LA MAQUINARIA
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 12 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-29px; text-align:center;">
                    TIPO DE OBSERVACION:
                </div>
            </td>
            
            <!-- Columnas 4 y 5 -->
            <td colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:0px; text-align:center; font-weight:bold;">
                    {{ data_get($answers, 'tipo_observacion', '') }}
                </div>
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                DAÑO A INSTALACIONES
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 13 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                OTROS, ESPECIFIQUE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 14 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-bottom:none; border-left:none;"></td>
        
            <!-- Columnas 9 y 10 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Condición Peligrosa
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 12 y 13 -->
            <td colspan="2" class="bold-center">
                {{ data_get($answers, 'tipo_observacion', '') }}
            </td>
        </tr>

        <!-- FILA 15 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-5px; text-align:center;">
                    DESCRIPCIÓN DE LA OBSERVACION:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td rowspan="8" colspan="2" style="vertical-align:middle; text-align:center; font-size:8px; padding:4px;">
                {{ data_get($answers, 'descripcion_observacion', '') }}
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
            
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ÁREAS SIN DELIMITACIÓN O SEÑALIZACIÓN ADECUADA
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- Columnas 12 y 13: FALTA COMETIDA SELECCIONADA -->
            <td
                colspan="2"
                style="
                    vertical-align:middle;
                    text-align:center;
                    font-size:7px;
                    font-weight:bold;
                    padding:2px 4px;
                    border-bottom:none;
                "
            >
                {{ data_get($answers, 'falta_cometida_seleccionada', '') }}
            </td>
        </tr>

        <!-- FILA 16 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                EQUIPOS O MAQUINARIA CON MANTENIMIENTO DEFICIENTE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>

            <!-- Columnas 12 y 13: DESCRIPCIÓN DE LA FALTA COMETIDA -->
            <td
                rowspan="7"
                colspan="2"
                style="
                    vertical-align:middle;
                    text-align:center;
                    font-size:8px;
                    font-weight:bold;
                    padding:4px;
                    border-top:none;
                "
            >
                {{ data_get($answers, 'descripcion_falta_cometida', '') }}
            </td>
        </tr>

        <!-- FILA 17 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                INSTALACIONES ELÉCTRICAS EXPUESTAS O EN MAL ESTADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 18 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                PISO RESBALADIZO O CON OBSTÁCULOS
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 19 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ILUMINACIÓN INSUFICIENTE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 20 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ALMACENAMIENTO INADECUADO DE MATERIALES
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 21 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                FALTA DE SEÑALIZACIÓN DE EMERGENCIA O RUTAS DE EVACUACIÓN
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 22 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                OTROS, ESPECIFIQUE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 23 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        </tr>

        <!-- FILA 24 -->
        <tr>
            <!-- Columna 1 unida -->
            <td rowspan="14" style="border-right:none;"></td>
    
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 7 unida -->
            <td rowspan="14" style="border-left:none;"></td>
    
            <!-- Columna 8 unida -->
            <td rowspan="14" style="border-right:none;"></td>
    
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 14 unida -->
            <td rowspan="14" style="border-left:none; border-right:none;"></td>
        </tr>

        <!-- FILA 25 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-27px; text-align:center;">
                    ACCIÓN CORRECTIVA:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td rowspan="4" colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; font-size:7px; padding:4px; font-weight:bold;">
                {{ data_get($answers, 'acciones_preventivas_correctivas', '') }}
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 9 a 13, filas 25 a 36: EVIDENCIAS -->
            <td
                rowspan="12"
                colspan="5"
                style="
                    border:none;
                    padding:0;
                    height:144px;
                    text-align:center;
                    vertical-align:middle;
                "
            >
                                @if($cantidadEvidencias > 0)
                    <div class="evidence-wrapper">
                        <table class="evidence-table">

                            {{-- UNA SOLA EVIDENCIA --}}
                            @if($cantidadEvidencias === 1)

                                @php
                                    $evidencia = $evidenciasSrc[0];
                                @endphp

                                <tr>
                                    <td style="width:100%;">
                                        <img
                                            src="{{ $evidencia['src'] }}"
                                            class="{{
                                                $evidencia['es_panoramica']
                                                    ? 'evidence-image-panoramic-single'
                                                    : 'evidence-image-normal-single'
                                            }}"
                                        >
                                    </td>
                                </tr>

                            {{-- DOS EVIDENCIAS Y AL MENOS UNA PANORÁMICA --}}
                            @elseif($apilarEvidencias)

                                @foreach($evidenciasSrc as $evidencia)
                                    <tr class="evidence-row-stacked">
                                        <td style="width:100%;">
                                            <img
                                                src="{{ $evidencia['src'] }}"
                                                class="evidence-image-stacked"
                                            >
                                        </td>
                                    </tr>
                                @endforeach

                            {{-- DOS EVIDENCIAS NORMALES: LADO A LADO --}}
                            @else

                                <tr>
                                    @foreach($evidenciasSrc as $evidencia)
                                        <td style="width:50%;">
                                            <img
                                                src="{{ $evidencia['src'] }}"
                                                class="evidence-image-normal-double"
                                            >
                                        </td>
                                    @endforeach
                                </tr>

                            @endif

                        </table>
                    </div>
                @endif
            </td>
        </tr>

        <!-- FILA 26 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        

        </tr>
        
        <!-- FILA 27 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        

        </tr>

        <!-- FILA 28 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        

        </tr>

        <!-- FILA 29 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 30 -->
        <tr>
        
            @php
                $firmaReporta = data_get($answers, 'firma_reporta_observacion');
                $firmaReportaSrc = null;
        
                if ($firmaReporta) {
                    $firmaPath = storage_path(
                        'app/public/' . ltrim($firmaReporta, '/')
                    );
        
                    if (file_exists($firmaPath)) {
                        $firmaReportaSrc =
                            'data:image/png;base64,' .
                            base64_encode(file_get_contents($firmaPath));
                    }
                }
            @endphp
        
            <!-- Columnas 2, 3 y 4 -->
            <td rowspan="2" colspan="3" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:bottom; font-size:8px; font-weight:bold; padding-bottom:2px;">
                {{ data_get($answers, 'nombre_reporta_observacion', '') }}
            </td>
        
            <!-- Columnas 5 y 6 -->
            <td rowspan="2" colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:middle; padding:0;">
                @if($firmaReportaSrc)
                    <img src="{{ $firmaReportaSrc }}" style="
                        max-height:40px;
                        max-width:100px;
                        display:block;
                        margin:0 auto;
                        object-fit:contain;
                    ">
                @endif
        
            </td>
        </tr>

        <!-- FILA 31 -->
        <tr>
        
        </tr>

        <!-- FILA 32 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:top;">
                <div style="position:relative; top:0px; text-align:center;">
                    NOMBRE Y FIRMA DE QUIEN REPORTA OBSERVACIÓN
                </div>
            </td>
        </tr>

        <!-- FILA 33 -->
        <tr>
        
            @php
                $cantidadObservados = count($firmasObservados);
        
                $observadosIzquierda = [];
                $observadosDerecha = [];
        
                if ($cantidadObservados >= 3) {
                    foreach ($firmasObservados as $index => $observado) {
                        if ($index % 2 === 0) {
                            $observadosIzquierda[] = $observado;
                        } else {
                            $observadosDerecha[] = $observado;
                        }
                    }
        
                    /*
                     * Los nuevos nombres y firmas se muestran arriba.
                     * Los primeros permanecen junto al borde inferior.
                     */
                    $observadosIzquierda = array_reverse($observadosIzquierda);
                    $observadosDerecha = array_reverse($observadosDerecha);
                }
            @endphp
        
            <!-- NOMBRES DE LOS OBSERVADOS -->
            <td
                rowspan="3"
                colspan="3"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                    font-size:6px;
                    font-weight:bold;
                    padding:0 4px 2px 4px;
                "
            >
                @if($cantidadObservados === 1)
        
                    <div style="
                        margin:0;
                        line-height:12px;
                        text-align:center;
                    ">
                        {{ $firmasObservados[0]['nombre'] ?: '—' }}
                    </div>
        
                @elseif($cantidadObservados === 2)
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 4px 0 0;
                                vertical-align:bottom;
                                text-align:center;
                                font-size:6px;
                                font-weight:bold;
                                line-height:8px;
                            ">
                                {{ $firmasObservados[0]['nombre'] ?: '—' }}
                            </td>
        
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 4px;
                                vertical-align:bottom;
                                text-align:center;
                                font-size:6px;
                                font-weight:bold;
                                line-height:8px;
                            ">
                                {{ $firmasObservados[1]['nombre'] ?: '—' }}
                            </td>
                        </tr>
                    </table>
        
                @elseif($cantidadObservados >= 3)
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <!-- LADO IZQUIERDO -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 4px 0 0;
                                vertical-align:bottom;
                                text-align:center;
                                font-size:6px;
                                font-weight:bold;
                            ">
                                @foreach($observadosIzquierda as $observado)
                                    <div style="
                                        margin:0;
                                        padding:0;
                                        line-height:8px;
                                        min-height:8px;
                                        text-align:center;
                                    ">
                                        {{ $observado['nombre'] ?: '—' }}
                                    </div>
                                @endforeach
                            </td>
        
                            <!-- LADO DERECHO -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 4px;
                                vertical-align:bottom;
                                text-align:center;
                                font-size:6px;
                                font-weight:bold;
                            ">
                                @foreach($observadosDerecha as $observado)
                                    <div style="
                                        margin:0;
                                        padding:0;
                                        line-height:8px;
                                        min-height:8px;
                                        text-align:center;
                                    ">
                                        {{ $observado['nombre'] ?: '—' }}
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    </table>
        
                @else
        
                    {{ $nombreObservadoRaw }}
        
                @endif
            </td>
        
            <!-- FIRMAS DE LOS OBSERVADOS -->
            <td
                rowspan="3"
                colspan="2"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                    padding:0 2px 2px 2px;
                "
            >
                @if($cantidadObservados === 1)
        
                    @if(!empty($firmasObservados[0]['firma_src']))
                        <img
                            src="{{ $firmasObservados[0]['firma_src'] }}"
                            style="
                                max-height:80px;
                                max-width:100px;
                                display:block;
                                margin:0 auto;
                                object-fit:contain;
                            "
                        >
                    @endif
        
                @elseif($cantidadObservados === 2)
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 2px 0 0;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                                @if(!empty($firmasObservados[0]['firma_src']))
                                    <img
                                        src="{{ $firmasObservados[0]['firma_src'] }}"
                                        style="
                                            max-height:80px;
                                            max-width:100px;
                                            display:block;
                                            margin:0 auto;
                                            object-fit:contain;
                                        "
                                    >
                                @endif
                            </td>
        
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 2px;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                                @if(!empty($firmasObservados[1]['firma_src']))
                                    <img
                                        src="{{ $firmasObservados[1]['firma_src'] }}"
                                        style="
                                            max-height:80px;
                                            max-width:100px;
                                            display:block;
                                            margin:0 auto;
                                            object-fit:contain;
                                        "
                                    >
                                @endif
                            </td>
                        </tr>
                    </table>
        
                @elseif($cantidadObservados >= 3)
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                            <!-- FIRMAS DEL LADO IZQUIERDO -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 2px 0 0;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                                @foreach($observadosIzquierda as $observado)
                                    <div style="
                                        height:16px;
                                        line-height:16px;
                                        text-align:center;
                                        margin:0;
                                        padding:0;
                                        overflow:hidden;
                                    ">
                                        @if(!empty($observado['firma_src']))
                                            <img
                                                src="{{ $observado['firma_src'] }}"
                                                style="
                                                    max-height:80px;
                                                    max-width:100px;
                                                    display:block;
                                                    margin:0 auto;
                                                    object-fit:contain;
                                                "
                                            >
                                        @endif
                                    </div>
                                @endforeach
                            </td>
        
                            <!-- FIRMAS DEL LADO DERECHO -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 2px;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                                @foreach($observadosDerecha as $observado)
                                    <div style="
                                        height:16px;
                                        line-height:16px;
                                        text-align:center;
                                        margin:0;
                                        padding:0;
                                        overflow:hidden;
                                    ">
                                        @if(!empty($observado['firma_src']))
                                            <img
                                                src="{{ $observado['firma_src'] }}"
                                                style="
                                                    max-height:80px;
                                                    max-width:100px;
                                                    display:block;
                                                    margin:0 auto;
                                                    object-fit:contain;
                                                "
                                            >
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    </table>
        
                @endif
            </td>
        </tr>
        
        <!-- FILA 34 -->
        <tr>
        </tr>
        
        <!-- FILA 35 -->
        <tr>
        </tr>

        <!-- FILA 36 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td
                colspan="5"
                style="
                    border:none;
                    text-align:center;
                    font-size:7px;
                    padding:0;
                    vertical-align:top;
                "
            >
                <div
                    style="
                        position:relative;
                        top:0;
                        text-align:center;
                    "
                >
                    NOMBRE Y FIRMA DEL OBSERVADO
                </div>
            </td>
        </tr>

        <!-- FILA 37 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        </tr>

        </table>
    </div>

</div>

</body>
</html>