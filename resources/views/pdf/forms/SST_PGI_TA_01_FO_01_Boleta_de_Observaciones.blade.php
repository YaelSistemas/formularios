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


        /* Páginas adicionales de evidencias fotográficas */
        .evidence-page {
            page-break-before: always;
        }

        .evidence-page-table {
            width: 99.6%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .evidence-page-table td {
            width: 50%;
            height: 245px;
            border: none;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
        }

        .evidence-page-image {
            display: block;
            max-width: 96%;
            max-height: 230px;
            width: auto;
            height: auto;
            margin: 0 auto;
            object-fit: contain;
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
     * Falta seleccionada para resaltarla en el PDF.
     */
    $tipoObservacionSeleccionado = trim(
        (string) data_get($answers, 'tipo_observacion', '')
    );
    
    $faltaCometidaSeleccionada = trim(
        (string) data_get($answers, 'falta_cometida_seleccionada', '')
    );
    
    $marcarFalta = function (
        string $tipo,
        string $valor,
        string $texto
    ) use (
        $tipoObservacionSeleccionado,
        $faltaCometidaSeleccionada
    ) {
        $tipoCoincide =
            mb_strtolower(trim($tipoObservacionSeleccionado), 'UTF-8') ===
            mb_strtolower(trim($tipo), 'UTF-8');
    
        $faltaCoincide =
            mb_strtolower(trim($faltaCometidaSeleccionada), 'UTF-8') ===
            mb_strtolower(trim($valor), 'UTF-8');
    
        $style = '';
    
        if ($tipoCoincide && $faltaCoincide) {
            $style =
                'color:#b91c1c;' .
                'font-weight:bold;' .
                'text-decoration:underline;';
        }
    
        return '<span style="' . $style . '">' . e($texto) . '</span>';
    };

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
     * EVIDENCIAS FOTOGRÁFICAS
     *
     * Se preparan TODAS las imágenes y se dividen
     * automáticamente en grupos de 4 por página.
     */
    $evidenciasRaw = data_get(
        $answers,
        'evidencia_fotografica',
        []
    );

    if (is_string($evidenciasRaw)) {
        $evidenciasRaw = [$evidenciasRaw];
    }

    if (!is_array($evidenciasRaw)) {
        $evidenciasRaw = [];
    }

    $evidenciasSrc = [];

    foreach ($evidenciasRaw as $evidencia) {
        /*
         * Puede venir como:
         * - string con ruta
         * - arreglo con data base64
         * - arreglo con path/url/file/ruta
         */
        if (is_array($evidencia)) {
            $evidencia =
                $evidencia['data']
                ?? $evidencia['path']
                ?? $evidencia['url']
                ?? $evidencia['file']
                ?? $evidencia['ruta']
                ?? '';
        }

        if (!is_string($evidencia) || trim($evidencia) === '') {
            continue;
        }

        $evidencia = trim($evidencia);

        /*
         * Si ya viene como imagen base64, se utiliza directamente.
         */
        if (str_starts_with($evidencia, 'data:image/')) {
            $evidenciasSrc[] = [
                'src' => $evidencia,
            ];

            continue;
        }

        /*
         * Si viene como ruta guardada.
         */
        $rutaEvidencia = ltrim(
            str_replace('\\', '/', $evidencia),
            '/'
        );

        $evidenciaPath = storage_path(
            'app/public/' . $rutaEvidencia
        );

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

        $evidenciasSrc[] = [
            'src' =>
                'data:' .
                $mimeEvidencia .
                ';base64,' .
                base64_encode(
                    file_get_contents($evidenciaPath)
                ),
        ];
    }

    /*
     * Máximo 4 evidencias por página.
     */
    $paginasEvidencias = array_chunk(
        $evidenciasSrc,
        4
    );

    /*
     * Página 1 = formulario.
     * Las páginas siguientes contienen evidencias.
     */
    $totalPaginas =
        1 + count($paginasEvidencias);

    $numeroPagina = function ($numero) {
        return str_pad(
            (string) $numero,
            2,
            '0',
            STR_PAD_LEFT
        );
    };
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
                FECHA DE EMISIÓN: 01/08/2026
            </td>
        </tr>

        <!-- FILA 3 -->
        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>

            <td class="right-cell">
                NÚMERO DE REVISIÓN: 01
            </td>
        </tr>

        <!-- FILA 4 -->
        <tr>
            <td class="center-cell">
                BOLETA DE OBSERVACIONES
            </td>

            <td class="right-cell">
                PÁGINA:
                {{ $numeroPagina(1) }}
                DE
                {{ $numeroPagina($totalPaginas) }}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Bromas o Distracciones en Área de Trabajo',
                    'BROMAS O DISTRACCIONES EN ÁREA DE TRABAJO'
                ) !!}
            </td>
            
            <!-- Columna 11 -->
            <td style="border:none;"></td>
            
            <!-- Columna 12 -->
            <td style="border:none;"></td>
            
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Aplicar Procedimientos de Seguridad',
                    'NO APLICAR PROCEDIMIENTOS DE SEGURIDAD'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'No Portar EPP Específico por Actividad',
                    'NO PORTAR EPP ESPECÍFICO POR ACTIVIDAD'
                ) !!}
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Aplicar Procedimientos Operativos',
                    'NO APLICAR PROCEDIMIENTOS OPERATIVOS'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Trabajar con Equipo en Movimiento',
                    'TRABAJAR CON EQUIPO EN MOVIMIENTO'
                ) !!}
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Portar Credenciales o documentos de Acceso',
                    'NO PORTAR CREDENCIALES O DOCUMENTOS DE ACCESO'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Uso de Herramientas en Mal Estado',
                    'USO DE HERRAMIENTAS EN MAL ESTADO'
                ) !!}
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Traer Tarjeta y Candado P/Bloqueo',
                    'NO TRAER TARJETA Y CANDADO P/BLOQUEO'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Exceso de Velocidad o Movimiento Inapropiado',
                    'EXCESO DE VELOCIDAD O MOVIMIENTO INAPROPIADO'
                ) !!}
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Informar Situaciones Anormales o Riesgos Detectados',
                    'NO INFORMAR SITUACIONES ANORMALES O RIESGOS DETECTADOS'
                ) !!}
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
        
            <!-- 9 -->
            <td style="border:none;"></td>

            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Trabajar en Alturas sin Medidas de Seguridad',
                    'TRABAJAR EN ALTURAS SIN MEDIDAS DE SEGURIDAD'
                ) !!}
            </td>

            <!-- 11 -->
            <td style="border:none;"></td>

            <!-- 12 -->
            <td style="border:none;"></td>

            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'No Desbloquear Equipos de los Clientes',
                    'NO DESBLOQUEAR EQUIPOS DE LOS CLIENTES'
                ) !!}
            </td>


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
        
            <!-- 9 -->
            <td style="border:none;"></td>

            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Uso Inadecuado de EPP',
                    'USO INADECUADO DE EPP'
                ) !!}
            </td>

            <!-- 11 -->
            <td style="border:none;"></td>

            <!-- 12 -->
            <td style="border:none;"></td>

            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Desviación',
                    'Otros, especifique',
                    'OTROS, ESPECIFIQUE'
                ) !!}
            </td>

        <!-- FILA 10 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'No Realizar Bloqueos y Etiquetados',
                    'NO REALIZAR BLOQUEOS Y ETIQUETADO'
                ) !!}
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
                ACTO INSEGURO / CONDICIÓN PELIGROSA / DESVIACIÓN / INCIDENTE
            </td>
        
            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Daño a la Maquinaria',
                    'DAÑO A LA MAQUINARIA'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Daño a las Instalaciones',
                    'DAÑO A INSTALACIONES'
                ) !!}
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
                {!! $marcarFalta(
                    'Acto Inseguro',
                    'Otros, especifique',
                    'OTROS, ESPECIFIQUE'
                ) !!}
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
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Incidente / Accidente
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
            <td
                rowspan="8"
                colspan="2"
                style="
                    vertical-align:middle;
                    text-align:center;
                    font-size:8px;
                    padding:4px;
                "
            >
                {{ data_get($answers, 'descripcion_observacion', '') }}
            </td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Áreas sin Delimitacion o Señalización Adecuada',
                    'ÁREAS SIN DELIMITACIÓN O SEÑALIZACIÓN ADECUADA'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Incidente',
                    'Accidente con posible Incapacidad',
                    'ACCIDENTE CON POSIBLE INCAPACIDAD'
                ) !!}
            </td>
        </tr>
        
        <!-- FILA 16 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Equipos o Maquinaria con Matenimiento Deficiente',
                    'EQUIPOS O MAQUINARIA CON MANTENIMIENTO DEFICIENTE'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Incidente',
                    'Incidente con Daños a la Propiedad',
                    'INCIDENTE CON DAÑOS A LA PROPIEDAD'
                ) !!}
            </td>
        </tr>
        
        <!-- FILA 17 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Instalaciones Eléctricas Expuestas o en Mal Estado',
                    'INSTALACIONES ELÉCTRICAS EXPUESTAS O EN MAL ESTADO'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Incidente',
                    'Incidentes "Near Miss"',
                    'INCIDENTES "NEAR MISS"'
                ) !!}
            </td>
        </tr>
        
        <!-- FILA 18 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Piso Resbaladizo o con Obstaculos',
                    'PISO RESBALADIZO O CON OBSTÁCULOS'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Incidente',
                    'Otros Especifique',
                    'OTROS ESPECIFIQUE'
                ) !!}
            </td>
        </tr>
        
        <!-- FILA 19 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Iluminación Insuficiente',
                    'ILUMINACIÓN INSUFICIENTE'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 VACÍA -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>
        
        <!-- FILA 20 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Almacenamiento Inadecuado de Materiales',
                    'ALMACENAMIENTO INADECUADO DE MATERIALES'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 VACÍA -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>
        
        <!-- FILA 21 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Falta de Señalización de Emergencia o Rutas de Evacuación',
                    'FALTA DE SEÑALIZACIÓN DE EMERGENCIA O RUTAS DE EVACUACIÓN'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 VACÍA -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>
        
        <!-- FILA 22 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>
        
            <!-- Columna 6 -->
            <td style="border:none;"></td>
        
            <!-- Columna 9 -->
            <td style="border:none;"></td>
        
            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                {!! $marcarFalta(
                    'Condición Peligrosa',
                    'Otros, especifique',
                    'OTROS, ESPECIFIQUE'
                ) !!}
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columna 12 -->
            <td style="border:none;"></td>
        
            <!-- Columna 13 VACÍA -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
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
            <!-- Columna 1 -->
            <td rowspan="14" style="border-right:none;"></td>
        
            <!-- Columnas 2 a 13 -->
            <td colspan="12" style="
                border-bottom:none;
                border-left:none;
                border-right:none;
            "></td>
        
            <!-- Columna 14 -->
            <td rowspan="14" style="
                border-left:none;
                border-right:none;
            "></td>
        </tr>

        <!-- FILA 25 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="
                border:none;
                text-align:center;
                font-size:7px;
                padding:0;
                vertical-align:bottom;
            ">
                <div style="
                    position:relative;
                    top:1px;
                    left:-27px;
                    text-align:center;
                ">
                    ACCIÓN CORRECTIVA:
                </div>
            </td>
        
            <!-- Columnas 4 a 13 -->
            <td
                rowspan="4"
                colspan="10"
                style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-size:7px;
                    padding:4px;
                    font-weight:bold;
                "
            >
                {{ data_get($answers, 'acciones_preventivas_correctivas', '') }}
            </td>
        </tr>

        <!-- FILA 26 -->
        <tr>
            <td colspan="2" style="border:none;"></td>
        </tr>
        
        <!-- FILA 27 -->
        <tr>
            <td colspan="2" style="border:none;"></td>
        </tr>
        
        <!-- FILA 28 -->
        <tr>
            <td colspan="2" style="border:none;"></td>
        </tr>

        <!-- FILA 29 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="12" style="border:none;">
            </td>
        </tr>

        <!-- FILA 30 -->
        <tr>
        
            @php
                /*
                 * FIRMA DE QUIEN REPORTA
                 */
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
        
                /*
                 * OBSERVADOS
                 */
                $cantidadObservados = count($firmasObservados);
        
                $observadosIzquierda = [];
                $observadosCentro = [];
                $observadosDerecha = [];
                
                if ($cantidadObservados >= 4) {
                    foreach ($firmasObservados as $index => $observado) {
                
                        $posicion = $index % 3;
                
                        if ($posicion === 0) {
                            $observadosIzquierda[] = $observado;
                        } elseif ($posicion === 1) {
                            $observadosCentro[] = $observado;
                        } else {
                            $observadosDerecha[] = $observado;
                        }
                    }
                
                    /*
                     * Los nuevos observados quedan arriba.
                     *
                     * 4 arriba de 1
                     * 5 arriba de 2
                     * 6 arriba de 3
                     * 7 arriba de 4
                     * etc.
                     */
                    $observadosIzquierda = array_reverse($observadosIzquierda);
                    $observadosCentro = array_reverse($observadosCentro);
                    $observadosDerecha = array_reverse($observadosDerecha);
                }
        
                /*
                 * Ajustamos automáticamente el tamaño de las firmas
                 * para que se vean completas sin ocupar demasiado.
                 */
                if ($cantidadObservados <= 3) {
                    $maxObservadosPorColumna = 1;
                } else {
                    $maxObservadosPorColumna = max(
                        count($observadosIzquierda),
                        count($observadosCentro),
                        count($observadosDerecha)
                    );
                }
                
                if ($maxObservadosPorColumna <= 1) {
                    $altoFirmaObservado = 38;
                } elseif ($maxObservadosPorColumna === 2) {
                    $altoFirmaObservado = 23;
                } else {
                    $altoFirmaObservado = 15;
                }
            @endphp
        
        
            <!-- ====================================================== -->
            <!-- QUIEN REPORTA: COLUMNAS 2 A 6 -->
            <!-- ====================================================== -->
            <td
                rowspan="6"
                colspan="5"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                    padding:0 4px 2px 4px;
                "
            >
                <!-- FIRMA ARRIBA -->
                <div style="
                    height:44px;
                    text-align:center;
                    margin:0;
                    padding:0;
                ">
                    @if($firmaReportaSrc)
                        <img
                            src="{{ $firmaReportaSrc }}"
                            style="
                                max-height:40px;
                                max-width:115px;
                                display:block;
                                margin:0 auto;
                                object-fit:contain;
                            "
                        >
                    @endif
                </div>
        
                <!-- NOMBRE ABAJO -->
                <div style="
                    text-align:center;
                    font-size:7px;
                    font-weight:bold;
                    line-height:9px;
                    margin:0;
                    padding:0;
                ">
                    {{ data_get($answers, 'nombre_reporta_observacion', '') }}
                </div>
            </td>
        
        
            <!-- ====================================================== -->
            <!-- COLUMNAS 7 Y 8: SEPARACIÓN -->
            <!-- ====================================================== -->
            <td
                rowspan="6"
                colspan="2"
                style="border:none;"
            >
            </td>
        
        
            <!-- ====================================================== -->
            <!-- OBSERVADOS: COLUMNAS 9 A 13 -->
            <!-- ====================================================== -->
            <td
                rowspan="6"
                colspan="5"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                    text-align:center;
                    vertical-align:bottom;
                    padding:0 4px 2px 4px;
                "
            >
        
                {{-- ================================================== --}}
                {{-- UN SOLO OBSERVADO: CENTRADO --}}
                {{-- ================================================== --}}
                @if($cantidadObservados === 1)
                
                    <div style="
                        width:100%;
                        text-align:center;
                        margin:0;
                        padding:0;
                    ">
                        <!-- FIRMA -->
                        <div style="
                            height:44px;
                            text-align:center;
                            margin:0;
                            padding:0;
                        ">
                            @if(!empty($firmasObservados[0]['firma_src']))
                                <img
                                    src="{{ $firmasObservados[0]['firma_src'] }}"
                                    style="
                                        max-height:38px;
                                        max-width:115px;
                                        display:block;
                                        margin:0 auto;
                                        object-fit:contain;
                                    "
                                >
                            @endif
                        </div>
                
                        <!-- NOMBRE -->
                        <div style="
                            font-size:6px;
                            font-weight:bold;
                            line-height:9px;
                            text-align:center;
                            margin:0;
                            padding:0;
                        ">
                            {{ $firmasObservados[0]['nombre'] ?: '—' }}
                        </div>
                    </div>
                
                
                {{-- ================================================== --}}
                {{-- DOS OBSERVADOS: IZQUIERDA Y DERECHA --}}
                {{-- ================================================== --}}
                @elseif($cantidadObservados === 2)
                
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                
                            <!-- OBSERVADO 1 -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 6px 0 0;
                                text-align:center;
                                vertical-align:bottom;
                            ">
                                <div style="height:44px; text-align:center;">
                                    @if(!empty($firmasObservados[0]['firma_src']))
                                        <img
                                            src="{{ $firmasObservados[0]['firma_src'] }}"
                                            style="
                                                max-height:38px;
                                                max-width:90px;
                                                display:block;
                                                margin:0 auto;
                                                object-fit:contain;
                                            "
                                        >
                                    @endif
                                </div>
                
                                <div style="
                                    font-size:6px;
                                    font-weight:bold;
                                    line-height:9px;
                                    text-align:center;
                                ">
                                    {{ $firmasObservados[0]['nombre'] ?: '—' }}
                                </div>
                            </td>
                
                            <!-- OBSERVADO 2 -->
                            <td style="
                                width:50%;
                                border:none;
                                padding:0 0 0 6px;
                                text-align:center;
                                vertical-align:bottom;
                            ">
                                <div style="height:44px; text-align:center;">
                                    @if(!empty($firmasObservados[1]['firma_src']))
                                        <img
                                            src="{{ $firmasObservados[1]['firma_src'] }}"
                                            style="
                                                max-height:38px;
                                                max-width:90px;
                                                display:block;
                                                margin:0 auto;
                                                object-fit:contain;
                                            "
                                        >
                                    @endif
                                </div>
                
                                <div style="
                                    font-size:6px;
                                    font-weight:bold;
                                    line-height:9px;
                                    text-align:center;
                                ">
                                    {{ $firmasObservados[1]['nombre'] ?: '—' }}
                                </div>
                            </td>
                
                        </tr>
                    </table>
                
                
                {{-- ================================================== --}}
                {{-- TRES OBSERVADOS: IZQUIERDA / CENTRO / DERECHA --}}
                {{-- ================================================== --}}
                @elseif($cantidadObservados === 3)
                
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                
                            @foreach($firmasObservados as $observado)
                                <td style="
                                    width:33.33%;
                                    border:none;
                                    padding:0 3px;
                                    text-align:center;
                                    vertical-align:bottom;
                                ">
                
                                    <!-- FIRMA -->
                                    <div style="
                                        height:44px;
                                        text-align:center;
                                        margin:0;
                                        padding:0;
                                    ">
                                        @if(!empty($observado['firma_src']))
                                            <img
                                                src="{{ $observado['firma_src'] }}"
                                                style="
                                                    max-height:38px;
                                                    max-width:78px;
                                                    display:block;
                                                    margin:0 auto;
                                                    object-fit:contain;
                                                "
                                            >
                                        @endif
                                    </div>
                
                                    <!-- NOMBRE -->
                                    <div style="
                                        font-size:6px;
                                        font-weight:bold;
                                        line-height:8px;
                                        text-align:center;
                                        margin:0;
                                        padding:0;
                                    ">
                                        {{ $observado['nombre'] ?: '—' }}
                                    </div>
                
                                </td>
                            @endforeach
                
                        </tr>
                    </table>
                
                
                {{-- ================================================== --}}
                {{-- CUATRO O MÁS: TRES COLUMNAS --}}
                {{-- ================================================== --}}
                @elseif($cantidadObservados >= 4)
                
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin:0;
                        padding:0;
                    ">
                        <tr>
                
                            <!-- ================================ -->
                            <!-- IZQUIERDA: 1, 4, 7, 10... -->
                            <!-- ================================ -->
                            <td style="
                                width:33.33%;
                                border:none;
                                padding:0 3px 0 0;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                
                                @foreach($observadosIzquierda as $observado)
                
                                    <div style="
                                        margin:0;
                                        padding:0;
                                        text-align:center;
                                    ">
                
                                        <!-- FIRMA -->
                                        <div style="
                                            height:{{ $altoFirmaObservado }}px;
                                            text-align:center;
                                            margin:0;
                                            padding:0;
                                            overflow:hidden;
                                        ">
                                            @if(!empty($observado['firma_src']))
                                                <img
                                                    src="{{ $observado['firma_src'] }}"
                                                    style="
                                                        max-height:{{ $altoFirmaObservado }}px;
                                                        max-width:76px;
                                                        display:block;
                                                        margin:0 auto;
                                                        object-fit:contain;
                                                    "
                                                >
                                            @endif
                                        </div>
                
                                        <!-- NOMBRE -->
                                        <div style="
                                            min-height:8px;
                                            line-height:8px;
                                            font-size:6px;
                                            font-weight:bold;
                                            text-align:center;
                                            margin:0 0 2px 0;
                                            padding:0;
                                        ">
                                            {{ $observado['nombre'] ?: '—' }}
                                        </div>
                
                                    </div>
                
                                @endforeach
                
                            </td>
                
                
                            <!-- ================================ -->
                            <!-- CENTRO: 2, 5, 8, 11... -->
                            <!-- ================================ -->
                            <td style="
                                width:33.33%;
                                border:none;
                                padding:0 3px;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                
                                @foreach($observadosCentro as $observado)
                
                                    <div style="
                                        margin:0;
                                        padding:0;
                                        text-align:center;
                                    ">
                
                                        <!-- FIRMA -->
                                        <div style="
                                            height:{{ $altoFirmaObservado }}px;
                                            text-align:center;
                                            margin:0;
                                            padding:0;
                                            overflow:hidden;
                                        ">
                                            @if(!empty($observado['firma_src']))
                                                <img
                                                    src="{{ $observado['firma_src'] }}"
                                                    style="
                                                        max-height:{{ $altoFirmaObservado }}px;
                                                        max-width:76px;
                                                        display:block;
                                                        margin:0 auto;
                                                        object-fit:contain;
                                                    "
                                                >
                                            @endif
                                        </div>
                
                                        <!-- NOMBRE -->
                                        <div style="
                                            min-height:8px;
                                            line-height:8px;
                                            font-size:6px;
                                            font-weight:bold;
                                            text-align:center;
                                            margin:0 0 2px 0;
                                            padding:0;
                                        ">
                                            {{ $observado['nombre'] ?: '—' }}
                                        </div>
                
                                    </div>
                
                                @endforeach
                
                            </td>
                
                
                            <!-- ================================ -->
                            <!-- DERECHA: 3, 6, 9, 12... -->
                            <!-- ================================ -->
                            <td style="
                                width:33.33%;
                                border:none;
                                padding:0 0 0 3px;
                                vertical-align:bottom;
                                text-align:center;
                            ">
                
                                @foreach($observadosDerecha as $observado)
                
                                    <div style="
                                        margin:0;
                                        padding:0;
                                        text-align:center;
                                    ">
                
                                        <!-- FIRMA -->
                                        <div style="
                                            height:{{ $altoFirmaObservado }}px;
                                            text-align:center;
                                            margin:0;
                                            padding:0;
                                            overflow:hidden;
                                        ">
                                            @if(!empty($observado['firma_src']))
                                                <img
                                                    src="{{ $observado['firma_src'] }}"
                                                    style="
                                                        max-height:{{ $altoFirmaObservado }}px;
                                                        max-width:76px;
                                                        display:block;
                                                        margin:0 auto;
                                                        object-fit:contain;
                                                    "
                                                >
                                            @endif
                                        </div>
                
                                        <!-- NOMBRE -->
                                        <div style="
                                            min-height:8px;
                                            line-height:8px;
                                            font-size:6px;
                                            font-weight:bold;
                                            text-align:center;
                                            margin:0 0 2px 0;
                                            padding:0;
                                        ">
                                            {{ $observado['nombre'] ?: '—' }}
                                        </div>
                
                                    </div>
                
                                @endforeach
                
                            </td>
                
                        </tr>
                    </table>
                
                
                {{-- ================================================== --}}
                {{-- REGISTROS ANTIGUOS --}}
                {{-- ================================================== --}}
                @else
                
                    <div style="
                        text-align:center;
                        font-size:6px;
                        font-weight:bold;
                    ">
                        {{ $nombreObservadoRaw }}
                    </div>
                
                @endif
        
            </td>
        
        </tr>
        
        
        <!-- FILA 31 -->
        <tr>
        </tr>
        
        <!-- FILA 32 -->
        <tr>
        </tr>
        
        <!-- FILA 33 -->
        <tr>
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
                <div style="
                    position:relative;
                    top:0;
                    text-align:center;
                ">
                    NOMBRE Y FIRMA DE QUIEN REPORTA OBSERVACIÓN
                </div>
            </td>
        
            <!-- Columnas 7 y 8 -->
            <td
                colspan="2"
                style="border:none;"
            >
            </td>
        
            <!-- Columnas 9 a 13 -->
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
                <div style="
                    position:relative;
                    top:0;
                    text-align:center;
                ">
                    NOMBRE Y FIRMA DEL OBSERVADO
                </div>
            </td>
        
        </tr>

        <!-- FILA 37 -->
        <tr>
            <!-- Columnas 2 a 13 -->
            <td
                colspan="12"
                style="
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    border-bottom:1px solid #000;
                "
            >
            </td>
        </tr>

        </table>
    </div>

</div>

@if(count($paginasEvidencias) > 0)

    @foreach($paginasEvidencias as $indicePagina => $grupoEvidencias)

        @php
            /*
             * La página 1 corresponde al formulario,
             * por eso las evidencias comienzan en la página 2.
             */
            $paginaActual = $indicePagina + 2;

            /*
             * Se rellenan las posiciones faltantes para conservar
             * siempre la cuadrícula de 2 x 2.
             */
            $grupoEvidencias = array_pad(
                $grupoEvidencias,
                4,
                null
            );
        @endphp

        <div class="sheet evidence-page">

            <!-- MISMO ENCABEZADO DE LA BOLETA -->
            <table class="header-table">
                <tr style="height:0; line-height:0;">
                    <td style="width:25%; padding:0; border:none; height:0;"></td>
                    <td style="width:45%; padding:0; border:none; height:0;"></td>
                    <td style="width:30%; padding:0; border:none; height:0;"></td>
                </tr>

                <!-- FILA 1 -->
                <tr>
                    <td rowspan="4" class="logo-cell">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}">
                        @endif
                    </td>

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
                        FECHA DE EMISIÓN: 01/08/2026
                    </td>
                </tr>

                <!-- FILA 3 -->
                <tr>
                    <td class="center-cell">
                        SISTEMA DE GESTIÓN INTEGRAL
                    </td>

                    <td class="right-cell">
                        NÚMERO DE REVISIÓN: 01
                    </td>
                </tr>

                <!-- FILA 4 -->
                <tr>
                    <td class="center-cell">
                        BOLETA DE OBSERVACIONES
                    </td>

                    <td class="right-cell">
                        PÁGINA:
                        {{ $numeroPagina($paginaActual) }}
                        DE
                        {{ $numeroPagina($totalPaginas) }}
                    </td>
                </tr>
            </table>

            <!-- 4 EVIDENCIAS POR HOJA: 2 ARRIBA Y 2 ABAJO -->
            <table class="evidence-page-table">

                <!-- FILA SUPERIOR -->
                <tr>
                    <td>
                        @if(!empty($grupoEvidencias[0]['src']))
                            <img
                                src="{{ $grupoEvidencias[0]['src'] }}"
                                class="evidence-page-image"
                            >
                        @endif
                    </td>

                    <td>
                        @if(!empty($grupoEvidencias[1]['src']))
                            <img
                                src="{{ $grupoEvidencias[1]['src'] }}"
                                class="evidence-page-image"
                            >
                        @endif
                    </td>
                </tr>

                <!-- FILA INFERIOR -->
                <tr>
                    <td>
                        @if(!empty($grupoEvidencias[2]['src']))
                            <img
                                src="{{ $grupoEvidencias[2]['src'] }}"
                                class="evidence-page-image"
                            >
                        @endif
                    </td>

                    <td>
                        @if(!empty($grupoEvidencias[3]['src']))
                            <img
                                src="{{ $grupoEvidencias[3]['src'] }}"
                                class="evidence-page-image"
                            >
                        @endif
                    </td>
                </tr>

            </table>

        </div>

    @endforeach

@endif

</body>
</html>