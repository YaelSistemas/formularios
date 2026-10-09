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



        .page-header {

            width: 100%;

            margin: 0;

        }



        .sheet {

            width: 100%;

            margin: 0;

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



    </style>



</head>



<body>

    @php



        /*

         * Obtiene los campos definidos en el Definition.

         */



        $fieldsCollection = collect($fields ?? []);

        $getField = fn($id) => $fieldsCollection->firstWhere('id', $id);



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

    @php

            /*
             * ==========================================================
             * ANCHO DE LAS 3 COLUMNAS DE LA QUINTA TABLA
             * ==========================================================
             *
             * Modifica SOLAMENTE estos valores.
             * Son porcentajes y deben sumar 100.
             */
            $tabla5Col1 = 30;
            $tabla5Col2 = 30;
            $tabla5Col3 = 40;

            /*
             * ==========================================================
             * ALTO DE LA FILA DE ENCABEZADO Y DE LAS FILAS DE DETALLE
             * ==========================================================
             *
             * $tabla5AltoFila1:
             * alto del encabezado.
             *
             * $tabla5AltoFilaDetalle:
             * alto base de cada fila dinámica de acciones/evidencias.
             * Si una imagen necesita más espacio, Dompdf puede ampliar
             * visualmente la fila para mostrarla completa.
             */
            $tabla5AltoFila1 = 15;
            $tabla5AltoFilaDetalle = 15;

            /*
             * ==========================================================
             * DATOS DE LA TABLA DE MANTENIMIENTO
             * ==========================================================
             *
             * Cada registro de tabla_mantenimiento_prensas representa
             * un "Problema actual".
             *
             * Cada problema utiliza como mínimo 2 filas:
             *
             * 0 acciones = 2 filas vacías
             * 1 acción   = 2 filas (1 con datos + 1 vacía)
             * 2 acciones = 2 filas
             * 3 acciones = 3 filas
             * 4 acciones = 4 filas
             * etc.
             *
             * A partir de 2 acciones se generan exactamente las filas
             * necesarias, sin agregar filas vacías adicionales.
             *
             * Además, la tabla 5 muestra como mínimo 3 Problemas actuales.
             * Los bloques faltantes se completan vacíos, cada uno con sus
             * 2 filas mínimas de Acciones realizadas / Evidencia.
             */
            $tabla5Mantenimientos = $answers['tabla_mantenimiento_prensas'] ?? [];

            if (is_string($tabla5Mantenimientos)) {
                $tabla5MantenimientosDecodificados = json_decode(
                    $tabla5Mantenimientos,
                    true
                );

                $tabla5Mantenimientos =
                    is_array($tabla5MantenimientosDecodificados)
                        ? $tabla5MantenimientosDecodificados
                        : [];
            }

            if (!is_array($tabla5Mantenimientos)) {
                $tabla5Mantenimientos = [];
            }

            /*
             * Si por compatibilidad llega una sola fila asociativa,
             * convertirla en una lista de filas.
             */
            if (
                isset($tabla5Mantenimientos['problema_actual'])
                || isset($tabla5Mantenimientos['acciones_realizadas'])
            ) {
                $tabla5Mantenimientos = [$tabla5Mantenimientos];
            }

            /*
             * Normaliza las acciones realizadas.
             * Soporta tanto el formato nuevo como registros anteriores.
             */
            $tabla5NormalizarAcciones = function ($mantenimiento) {
                $acciones = is_array($mantenimiento)
                    ? ($mantenimiento['acciones_realizadas'] ?? [])
                    : [];

                /*
                 * Formato anterior:
                 * acciones_realizadas = "texto"
                 */
                if (is_string($acciones)) {
                    $texto = trim($acciones);

                    return $texto !== ''
                        ? [[
                            'accion' => $texto,
                            'evidencia_fotografica' => [],
                        ]]
                        : [];
                }

                if (!is_array($acciones)) {
                    return [];
                }

                /*
                 * Una sola acción enviada como objeto asociativo.
                 */
                if (
                    !empty($acciones)
                    && (
                        array_key_exists('accion', $acciones)
                        || array_key_exists('evidencia_fotografica', $acciones)
                    )
                ) {
                    $acciones = [$acciones];
                }

                $normalizadas = [];

                foreach ($acciones as $accion) {
                    if (is_string($accion)) {
                        $texto = trim($accion);

                        if ($texto !== '') {
                            $normalizadas[] = [
                                'accion' => $texto,
                                'evidencia_fotografica' => [],
                            ];
                        }

                        continue;
                    }

                    if (!is_array($accion)) {
                        continue;
                    }

                    $texto = trim((string) (
                        $accion['accion']
                        ?? $accion['texto']
                        ?? $accion['accion_realizada']
                        ?? ''
                    ));

                    $evidencias = $accion['evidencia_fotografica'] ?? [];

                    if (!is_array($evidencias)) {
                        $evidencias = $evidencias ? [$evidencias] : [];
                    }

                    /*
                     * Una sola evidencia como objeto asociativo.
                     */
                    if (
                        !empty($evidencias)
                        && (
                            array_key_exists('path', $evidencias)
                            || array_key_exists('data', $evidencias)
                        )
                    ) {
                        $evidencias = [$evidencias];
                    }

                    $normalizadas[] = [
                        'accion' => $texto,
                        'evidencia_fotografica' => array_values($evidencias),
                    ];
                }

                return $normalizadas;
            };

            /*
             * Convierte una evidencia guardada en storage/public a un
             * data URI para que Dompdf pueda mostrarla correctamente.
             *
             * También soporta data:image/... de registros antiguos.
             */
            $tabla5ObtenerImagenSrc = function ($evidencia) {
                $data = '';
                $path = '';
                $mime = '';

                if (is_string($evidencia)) {
                    if (str_starts_with($evidencia, 'data:image/')) {
                        return $evidencia;
                    }

                    $path = $evidencia;
                } elseif (is_array($evidencia)) {
                    $data = is_string($evidencia['data'] ?? null)
                        ? $evidencia['data']
                        : '';

                    if (
                        $data !== ''
                        && str_starts_with($data, 'data:image/')
                    ) {
                        return $data;
                    }

                    $path = (string) ($evidencia['path'] ?? '');
                    $mime = (string) ($evidencia['type'] ?? '');
                }

                if ($path === '') {
                    return null;
                }

                $relativePath = ltrim(
                    str_replace('\\', '/', $path),
                    '/'
                );

                if (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr(
                        $relativePath,
                        strlen('storage/')
                    );
                }

                $absolutePath = storage_path(
                    'app/public/' . $relativePath
                );

                if (!file_exists($absolutePath)) {
                    /*
                     * Compatibilidad adicional por si la ruta ya apunta
                     * directamente a public/storage.
                     */
                    $publicPath = public_path(
                        'storage/' . $relativePath
                    );

                    if (!file_exists($publicPath)) {
                        return null;
                    }

                    $absolutePath = $publicPath;
                }

                if ($mime === '') {
                    $mimeDetectado = @mime_content_type($absolutePath);
                    $mime = $mimeDetectado ?: 'image/jpeg';
                }

                return
                    'data:' .
                    $mime .
                    ';base64,' .
                    base64_encode(
                        file_get_contents($absolutePath)
                    );
            };

            /*
             * Preparar cada problema con el número dinámico de filas que
             * necesita según la cantidad de acciones realizadas.
             */
            $tabla5MantenimientosPreparados = [];

            foreach ($tabla5Mantenimientos as $mantenimiento) {
                if (!is_array($mantenimiento)) {
                    continue;
                }

                $problemaActual = trim((string) (
                    $mantenimiento['problema_actual'] ?? ''
                ));

                $acciones = $tabla5NormalizarAcciones(
                    $mantenimiento
                );

                $cantidadAcciones = count($acciones);

                /*
                 * ======================================================
                 * MÍNIMO VISUAL DE 2 FILAS POR PROBLEMA ACTUAL
                 * ======================================================
                 *
                 * 0 acciones = 2 filas vacías
                 * 1 acción   = 2 filas (la segunda queda vacía)
                 * 2 acciones = 2 filas
                 * 3 acciones = 3 filas
                 * 4 acciones = 4 filas
                 * etc.
                 *
                 * Se agregan elementos null ÚNICAMENTE para completar
                 * las 2 filas mínimas. Desde 2 acciones en adelante no
                 * se agrega ninguna fila extra.
                 */
                $accionesParaMostrar = array_values($acciones);

                while (count($accionesParaMostrar) < 2) {
                    $accionesParaMostrar[] = null;
                }

                $filasNecesarias = count($accionesParaMostrar);

                $tabla5MantenimientosPreparados[] = [
                    'problema_actual' => $problemaActual,
                    'acciones' => $accionesParaMostrar,
                    'filas' => $filasNecesarias,
                ];
            }

            /*
             * ==========================================================
             * MÍNIMO VISUAL DE 3 PROBLEMAS ACTUALES
             * ==========================================================
             *
             * Sin importar cuántos Problemas actuales tenga el registro,
             * la tabla 5 mostrará como mínimo 3 bloques.
             *
             * 1 problema real = 1 con datos + 2 vacíos
             * 2 problemas reales = 2 con datos + 1 vacío
             * 3 problemas reales = se muestran los 3
             * 4 o más problemas = se muestran todos
             *
             * Cada problema vacío conserva sus 2 filas mínimas para
             * Acciones realizadas / Evidencia fotográfica.
             */
            while (count($tabla5MantenimientosPreparados) < 3) {
                $tabla5MantenimientosPreparados[] = [
                    'problema_actual' => '',
                    'acciones' => [null, null],
                    'filas' => 2,
                ];
            }


            /*
             * ==========================================================
             * PAGINACIÓN MANUAL DESDE ESTE MISMO BLADE
             * ==========================================================
             *
             * El número total de páginas se obtiene antes de imprimir
             * el documento, igual que en otros formatos del proyecto.
             *
             * Primera hoja:
             * encabezado + tablas 1 a 4 + parte de tabla 5.
             *
             * Hojas siguientes:
             * encabezado + continuación de tabla 5.
             */
            $tabla5CapacidadPrimeraPagina = 400;

            /*
             * Separación vertical entre el encabezado general y la tabla 5
             * únicamente a partir de la página 2.
             *
             * Puedes modificar este valor si después quieres más o menos
             * espacio sin afectar la primera página.
             */
            $tabla5SeparacionPaginasSiguientes = 12;

            /*
             * Se descuenta la separación de la capacidad disponible para
             * que la paginación manual siga evitando desbordamientos.
             */
            $tabla5CapacidadPaginasSiguientes =
                820 - $tabla5SeparacionPaginasSiguientes;

            $tabla5EstimarAltoTexto = function ($texto, $caracteresPorLinea = 42) {
                $texto = trim((string) $texto);

                if ($texto === '') {
                    return 22;
                }

                $longitud = mb_strlen($texto, 'UTF-8');

                $lineas = max(
                    1,
                    (int) ceil(
                        $longitud / max(1, $caracteresPorLinea)
                    )
                );

                return max(
                    22,
                    ($lineas * 11) + 8
                );
            };

            $tabla5EstimarAltoAccion = function ($accion) use ($tabla5EstimarAltoTexto) {
                if (!is_array($accion)) {
                    return 22;
                }

                $texto = trim((string) (
                    $accion['accion']
                    ?? $accion['texto']
                    ?? $accion['accion_realizada']
                    ?? ''
                ));

                $altoTexto = $tabla5EstimarAltoTexto(
                    $texto,
                    42
                );

                $evidencias = $accion['evidencia_fotografica'] ?? [];

                if (!is_array($evidencias)) {
                    $evidencias = $evidencias ? [$evidencias] : [];
                }

                if (
                    !empty($evidencias)
                    && (
                        array_key_exists('path', $evidencias)
                        || array_key_exists('data', $evidencias)
                    )
                ) {
                    $evidencias = [$evidencias];
                }

                $cantidadEvidencias = count($evidencias);

                $filasImagenes = $cantidadEvidencias > 0
                    ? (int) ceil($cantidadEvidencias / 2)
                    : 0;

                $altoImagenes = $filasImagenes > 0
                    ? ($filasImagenes * 82)
                    : 22;

                return max(
                    22,
                    $altoTexto,
                    $altoImagenes
                );
            };

            $tabla5Paginas = [
                [],
            ];

            $paginaTabla5Actual = 0;
            $altoTabla5Usado = 0;

            foreach ($tabla5MantenimientosPreparados as $mantenimientoPreparado) {
                $problemaActual =
                    $mantenimientoPreparado['problema_actual'] ?? '';

                $accionesProblema =
                    $mantenimientoPreparado['acciones'] ?? [];

                if (!is_array($accionesProblema)) {
                    $accionesProblema = [];
                }

                $accionesProblema = array_values(
                    $accionesProblema
                );

                /*
                 * Seguridad adicional para mantener siempre las 2 filas
                 * mínimas, incluso con registros antiguos o estructuras
                 * que lleguen sin pasar por la preparación anterior.
                 */
                while (count($accionesProblema) < 2) {
                    $accionesProblema[] = null;
                }

                $altoProblema = $tabla5EstimarAltoTexto(
                    $problemaActual,
                    42
                );

                $indiceAccionProblema = 0;
                $cantidadAccionesProblema =
                    count($accionesProblema);

                while (
                    $indiceAccionProblema
                    < $cantidadAccionesProblema
                ) {
                    $capacidadPaginaActual =
                        $paginaTabla5Actual === 0
                            ? $tabla5CapacidadPrimeraPagina
                            : $tabla5CapacidadPaginasSiguientes;

                    $espacioDisponible =
                        $capacidadPaginaActual
                        - $altoTabla5Usado;

                    if (
                        $espacioDisponible < 22
                        && count(
                            $tabla5Paginas[
                                $paginaTabla5Actual
                            ]
                        ) > 0
                    ) {
                        $paginaTabla5Actual++;

                        $tabla5Paginas[
                            $paginaTabla5Actual
                        ] = [];

                        $altoTabla5Usado = 0;

                        continue;
                    }

                    $accionesChunk = [];
                    $altoAccionesChunk = 0;

                    while (
                        $indiceAccionProblema
                        < $cantidadAccionesProblema
                    ) {
                        $accionActual =
                            $accionesProblema[
                                $indiceAccionProblema
                            ];

                        $altoAccionActual =
                            $tabla5EstimarAltoAccion(
                                $accionActual
                            );

                        $nuevoAltoAcciones =
                            $altoAccionesChunk
                            + $altoAccionActual;

                        $altoBloqueCandidato = max(
                            $altoProblema,
                            $nuevoAltoAcciones
                        );

                        $capacidadPaginaActual =
                            $paginaTabla5Actual === 0
                                ? $tabla5CapacidadPrimeraPagina
                                : $tabla5CapacidadPaginasSiguientes;

                        $espacioDisponible =
                            $capacidadPaginaActual
                            - $altoTabla5Usado;

                        if (
                            count($accionesChunk) > 0
                            && $altoBloqueCandidato
                                > $espacioDisponible
                        ) {
                            break;
                        }

                        if (
                            count($accionesChunk) === 0
                            && $altoBloqueCandidato
                                > $espacioDisponible
                            && count(
                                $tabla5Paginas[
                                    $paginaTabla5Actual
                                ]
                            ) > 0
                        ) {
                            break;
                        }

                        $accionesChunk[] = $accionActual;

                        $altoAccionesChunk =
                            $nuevoAltoAcciones;

                        $indiceAccionProblema++;
                    }

                    if (count($accionesChunk) === 0) {
                        $paginaTabla5Actual++;

                        $tabla5Paginas[
                            $paginaTabla5Actual
                        ] = [];

                        $altoTabla5Usado = 0;

                        continue;
                    }

                    $altoBloqueFinal = max(
                        $altoProblema,
                        $altoAccionesChunk
                    );

                    $tabla5Paginas[
                        $paginaTabla5Actual
                    ][] = [
                        'problema_actual' =>
                            $problemaActual,
                        'acciones' =>
                            $accionesChunk,
                        'filas' =>
                            count($accionesChunk),
                    ];

                    $altoTabla5Usado +=
                        $altoBloqueFinal;

                    /*
                     * Si el mismo problema todavía tiene más acciones,
                     * la continuación empieza en una hoja nueva.
                     *
                     * El problema se repite en la columna 1 para conservar
                     * siempre la alineación Problema / Acción / Evidencia.
                     */
                    if (
                        $indiceAccionProblema
                        < $cantidadAccionesProblema
                    ) {
                        $paginaTabla5Actual++;

                        $tabla5Paginas[
                            $paginaTabla5Actual
                        ] = [];

                        $altoTabla5Usado = 0;
                    }
                }
            }

            while (
                count($tabla5Paginas) > 1
                && count(
                    $tabla5Paginas[
                        count($tabla5Paginas) - 1
                    ]
                ) === 0
            ) {
                array_pop($tabla5Paginas);
            }

            if (count($tabla5Paginas) === 0) {
                $tabla5Paginas = [[]];
            }

            $totalPaginas = max(
                1,
                count($tabla5Paginas)
            );

            /*
             * ==========================================================
             * SEXTA TABLA - 2 COLUMNAS X 6 FILAS
             * ==========================================================
             *
             * ANCHO DE COLUMNAS:
             * Modifica únicamente estos dos valores.
             * Son porcentajes y deben sumar 100.
             */
            $tabla6Col1 = 50;
            $tabla6Col2 = 50;

            /*
             * ALTO DE LAS 6 FILAS:
             * Modifica cada valor de forma independiente.
             * Están expresados en píxeles.
             */
            $tabla6AltoFila1 = 15;
            $tabla6AltoFila2 = 30;
            $tabla6AltoFila3 = 15;
            $tabla6AltoFila4 = 30;
            $tabla6AltoFila5 = 15;
            $tabla6AltoFila6 = 75;

            $tabla6AltosFilas = [
                1 => $tabla6AltoFila1,
                2 => $tabla6AltoFila2,
                3 => $tabla6AltoFila3,
                4 => $tabla6AltoFila4,
                5 => $tabla6AltoFila5,
                6 => $tabla6AltoFila6,
            ];

            /*
             * ==========================================================
             * DATOS DE LA SEXTA TABLA
             * ==========================================================
             */
            $observacionesTabla6 =
                (string) ($answers['observaciones'] ?? '');

            $recomendacionesTabla6 =
                (string) ($answers['recomendaciones'] ?? '');

            $nombreInspeccionaTabla6 =
                (string) (
                    $answers['nombre_inspecciona_mantenimiento']
                    ?? ''
                );

            $nombreAutorizaTabla6 =
                (string) (
                    $answers['nombre_autoriza']
                    ?? ''
                );

            $firmaInspeccionaTabla6 =
                $answers['firma_inspecciona_mantenimiento']
                ?? null;

            $firmaAutorizaTabla6 =
                $answers['firma_autoriza']
                ?? null;

            /*
             * Se reutiliza el resolvedor de imágenes ya existente en este
             * mismo PDF. Soporta tanto rutas guardadas en storage/public
             * como firmas antiguas en formato data:image/...
             */
            $firmaInspeccionaTabla6Src =
                $firmaInspeccionaTabla6
                    ? $tabla5ObtenerImagenSrc(
                        $firmaInspeccionaTabla6
                    )
                    : null;

            $firmaAutorizaTabla6Src =
                $firmaAutorizaTabla6
                    ? $tabla5ObtenerImagenSrc(
                        $firmaAutorizaTabla6
                    )
                    : null;

            /*
             * La imagen de firma se adapta automáticamente al alto
             * configurado para la fila 6.
             */
            $tabla6AltoMaxFirma = max(
                20,
                $tabla6AltoFila6 - 22
            );

    @endphp


    @foreach(
        $tabla5Paginas
        as $paginaIndexTabla5 => $paginaTabla5ActualDatos
    )

        @if($paginaIndexTabla5 > 0)
            <div class="page-break"></div>
        @endif

        <div class="sheet">


            <div class="page-header">

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

                    PÁGINA: {{ $paginaIndexTabla5 + 1 }} DE {{ $totalPaginas }}

                </td>



            </tr>





            <!-- FILA 2 -->

            <tr>

                <!-- FECHA DE EMISIÓN -->

                <td class="right-cell">

                    FECHA EMISIÓN: 28/09/2026

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

                    REVISIÓN: 02

                </td>



            </tr>



        </table>

            </div>

            <!-- ======================================= -->
            <!-- SEPARACIÓN SOLO DESDE LA PÁGINA 2       -->
            <!-- ======================================= -->
            @if($paginaIndexTabla5 > 0)
                <div style="
                    height:{{ $tabla5SeparacionPaginasSiguientes }}px;
                    line-height:0;
                    font-size:0;
                    padding:0;
                    margin:0;
                ">&nbsp;</div>
            @endif


            @if($paginaIndexTabla5 === 0)

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

            $codigo = $answers['codigo'] ?? '';



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





                <!-- COLUMNA 11 - CÓDIGO -->

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

                        Código:

                    </div>

                </td>



                <!-- COLUMNA 12 - DATO CÓDIGO -->

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

                        {{ $codigo }}

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

        <!-- SEGUNDA TABLA - 11 COLUMNAS X 7 FILAS  -->

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

            $tabla2Col2  = 9.5;

            $tabla2Col3  = 3;

            $tabla2Col4  = 7.5;

            $tabla2Col5  = 25;

            $tabla2Col6  = 2.5;

            $tabla2Col7  = 7;

            $tabla2Col8  = 3;

            $tabla2Col9  = 7.5;

            $tabla2Col10 = 25;

            $tabla2Col11 = 5;



            /*

             * ==========================================================

             * ALTO DE LAS 7 FILAS DE LA SEGUNDA TABLA

             * ==========================================================

             * Modifica SOLAMENTE estos valores.

             * Están expresados en píxeles.

             */



            $tabla2AltoFila1 = 18;

            $tabla2AltoFila2 = 15;

            $tabla2AltoFila3 = 15;

            $tabla2AltoFila4 = 15;

            $tabla2AltoFila5 = 15;

            $tabla2AltoFila6 = 15;

            $tabla2AltoFila7 = 15;



            $tabla2AltosFilas = [

                            $tabla2AltoFila1,

                            $tabla2AltoFila2,

                            $tabla2AltoFila3,

                            $tabla2AltoFila4,

                            $tabla2AltoFila5,

                            $tabla2AltoFila6,

                            $tabla2AltoFila7,

            ];



            /*
             * ==========================================================
             * TIPO DE PRENSA Y VOLTAJE SELECCIONADOS
             * ==========================================================
             *
             * Se usan para colocar la X en las casillas de Caja o Rieles.
             * El voltaje se normaliza a su parte numérica para mantener
             * compatibilidad con valores como "440 V" o "440 VOLTS".
             */
            $tipoPrensaSeleccionadoTabla2 = mb_strtolower(
                trim((string) ($answers['tipo_prensa'] ?? '')),
                'UTF-8'
            );

            $tipoVoltajeSeleccionadoTabla2 = preg_replace(
                '/\D+/',
                '',
                (string) ($answers['tipo_voltaje'] ?? '')
            );

            $tabla2EsCaja =
                $tipoPrensaSeleccionadoTabla2 === 'caja';

            $tabla2EsRieles =
                $tipoPrensaSeleccionadoTabla2 === 'rieles';



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

            <!-- FILAS 2 A 7                         -->

            <!-- =================================== -->

            @for($filaTabla2 = 2; $filaTabla2 <= 7; $filaTabla2++)



                @php

                    $altoFilaTabla2 = ${'tabla2AltoFila' . $filaTabla2};

                    $esUltimaFilaTabla2 = $filaTabla2 === 7;

                @endphp



                <tr>



                    <!-- =================================== -->

                    <!-- COLUMNA 1                           -->

                    <!-- VISUALMENTE UNIDA DE FILA 2 A 7    -->

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

                                @if($filaTabla2 === 7)

                                    border-bottom:1px solid #000;

                                @endif

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



                        <!-- COLUMNA 3 - X CAJA 440 V -->

                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            @if(
                                $tabla2EsCaja
                                && $tipoVoltajeSeleccionadoTabla2 === '440'
                            )
                                X
                            @endif
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



                        <!-- COLUMNA 7 - BLANCO SIN BORDES -->

                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 8 - X RIELES 440 V -->

                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            @if(
                                $tabla2EsRieles
                                && $tipoVoltajeSeleccionadoTabla2 === '440'
                            )
                                X
                            @endif
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



                        <!-- COLUMNA 3 - X CAJA 220 V -->

                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            @if(
                                $tabla2EsCaja
                                && $tipoVoltajeSeleccionadoTabla2 === '220'
                            )
                                X
                            @endif
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



                        <!-- COLUMNA 7 - BLANCO SIN BORDES -->

                        <td style="border:none; padding:0; text-align:center; vertical-align:middle;">
                            <div style="height:{{ $altoFilaTabla2 }}px; line-height:0; padding:0; margin:0; font-size:0;">&nbsp;</div>
                        </td>

                        <!-- COLUMNA 8 - X RIELES 220 V -->

                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            @if(
                                $tabla2EsRieles
                                && $tipoVoltajeSeleccionadoTabla2 === '220'
                            )
                                X
                            @endif
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



                        <!-- COLUMNA 3 - X CAJA 110 V -->

                        <td style="
                            border:1px solid #000;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            height:{{ $altoFilaTabla2 }}px;
                        ">
                            @if(
                                $tabla2EsCaja
                                && $tipoVoltajeSeleccionadoTabla2 === '110'
                            )
                                X
                            @endif
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

                    @endif



                    <!-- =================================== -->

                    <!-- COLUMNA 11                          -->

                    <!-- VISUALMENTE UNIDA DE FILA 2 A 7    -->

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



        <!-- ======================================= -->

        <!-- TERCERA TABLA - 7 COLUMNAS X 11 FILAS  -->

        <!-- ======================================= -->



        @php



            /*

             * ==========================================================

             * ANCHO DE LAS 7 COLUMNAS DE LA TERCERA TABLA

             * ==========================================================

             *

             * Modifica SOLAMENTE estos valores.

             * Son porcentajes y deben sumar 100.

             */

            $tabla3Col1 = 5;

            $tabla3Col2 = 3;

            $tabla3Col3 = 37;

            $tabla3Col4 = 10;

            $tabla3Col5 = 3;

            $tabla3Col6 = 37;

            $tabla3Col7 = 5;



            /*

             * ==========================================================

             * ALTO DE LAS 11 FILAS DE LA TERCERA TABLA

             * ==========================================================

             *

             * Modifica SOLAMENTE estos valores.

             * Están expresados en píxeles.

             */

            $tabla3AltoFila1  = 18;

            $tabla3AltoFila2  = 15;

            $tabla3AltoFila3  = 15;

            $tabla3AltoFila4  = 15;

            $tabla3AltoFila5  = 15;

            $tabla3AltoFila6  = 15;

            $tabla3AltoFila7  = 15;

            $tabla3AltoFila8  = 15;

            $tabla3AltoFila9  = 15;

            $tabla3AltoFila10 = 18;

            $tabla3AltoFila11 = 30;

            $tabla3AltosFilas = [

                $tabla3AltoFila1,

                $tabla3AltoFila2,

                $tabla3AltoFila3,

                $tabla3AltoFila4,

                $tabla3AltoFila5,

                $tabla3AltoFila6,

                $tabla3AltoFila7,

                $tabla3AltoFila8,

                $tabla3AltoFila9,

                $tabla3AltoFila10,

                $tabla3AltoFila11,

            ];



            /*

             * ==========================================================

             * ACCIONES MOSTRADAS EN LA TERCERA TABLA

             * ==========================================================

             *

             * Columna 3: filas 3 a 8

             * Columna 6: filas 3 a 8

             * "Otros" se muestra aparte en las filas 10 y 11.

             */

            $tabla3AccionesCol3 = [

                3 => 'Limpieza de cajas de control',

                4 => 'Revisión de conexiones eléctricas',

                5 => 'Revisión de contactores y termostatos',

                6 => 'Medición de resistencia',

                7 => 'Revisión de extensión y cables maestros',

                8 => 'Ajuste de tornillería',

            ];



            $tabla3AccionesCol6 = [

                3 => 'Inspección de cámara de presión',

                4 => 'Revisión de conexiones hidráulicas y neumáticas',

                5 => 'Revisión de mangueras de llenado y enfriamiento',

                6 => 'Limpieza y lubricación de tornillos de ajuste',

                7 => 'Inspección de rieles o cajas que no estén dobladas',

                8 => 'Revisión de calentamiento de platinas 150° C a 165° C',

            ];



            /*

             * ==========================================================

             * ACCIONES SELECCIONADAS

             * ==========================================================

             *

             * Se normaliza el valor para poder marcar con "X" cada

             * acción seleccionada en las columnas 2 y 5.

             */

            $accionesSeleccionadas = $answers['acciones'] ?? [];

            if (is_string($accionesSeleccionadas)) {

                $accionesDecodificadas = json_decode($accionesSeleccionadas, true);

                if (is_array($accionesDecodificadas)) {

                    $accionesSeleccionadas = $accionesDecodificadas;

                } elseif ($accionesSeleccionadas !== '') {

                    $accionesSeleccionadas = [$accionesSeleccionadas];

                } else {

                    $accionesSeleccionadas = [];

                }

            }



            if (!is_array($accionesSeleccionadas)) {

                $accionesSeleccionadas = [];

            }



            $accionEstaSeleccionada = function ($accion) use ($accionesSeleccionadas) {

                return in_array($accion, $accionesSeleccionadas, true)

                    || !empty($accionesSeleccionadas[$accion]);

            };



            $especifiqueOtra = $answers['especifique_otra'] ?? '';

        @endphp



        <table style="

            width:100%;

            border-collapse:collapse;

            table-layout:fixed;

            font-size:8px;

            margin-top:0;

        ">



            <!-- DEFINICIÓN DE LAS 7 COLUMNAS -->

            <tr style="height:0; line-height:0;">

                <td style="width:{{ $tabla3Col1 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col2 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col3 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col4 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col5 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col6 }}%; padding:0; border:none; height:0;"></td>

                <td style="width:{{ $tabla3Col7 }}%; padding:0; border:none; height:0;"></td>

            </tr>



            <!-- =================================== -->

            <!-- FILA 1                              -->

            <!-- COLUMNAS 1 A 7 UNIDAS              -->

            <!-- =================================== -->

            <tr>

                <td

                    colspan="7"

                    style="

                        border:1px solid #000;

                        border-top:none;

                        background:#e5e7eb;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $tabla3AltoFila1 }}px;

                    "

                >

                    ACCIONES

                </td>

            </tr>



            <!-- =================================== -->

            <!-- FILA 2                              -->

            <!-- =================================== -->

            <tr>



                <!-- COLUMNA 1 - UNIDA DE FILA 2 A 9 -->

                <!-- EN BLANCO, SIN BORDE SUPERIOR NI DERECHO -->

                <td

                    rowspan="8"

                    style="

                        padding:0;

                        border:none;

                        border-left:1px solid #000;

                        border-bottom:1px solid #000;

                        text-align:center;

                        vertical-align:middle;

                    "

                >&nbsp;</td>



                <!-- COLUMNAS 2 A 6 UNIDAS -->

                <!-- EN BLANCO Y SIN BORDES -->

                <td

                    colspan="5"

                    style="

                        border:none;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                    "

                >

                    <div style="

                        height:{{ $tabla3AltoFila2 }}px;

                        line-height:0;

                        padding:0;

                        margin:0;

                        font-size:0;

                    ">&nbsp;</div>

                </td>



                <!-- COLUMNA 7 - UNIDA DE FILA 2 A 9 -->

                <!-- EN BLANCO, SIN BORDE SUPERIOR NI IZQUIERDO -->

                <td

                    rowspan="8"

                    style="

                        padding:0;

                        border:none;

                        border-right:1px solid #000;

                        border-bottom:1px solid #000;

                        text-align:center;

                        vertical-align:middle;

                    "

                >&nbsp;</td>

            </tr>



            <!-- =================================== -->

            <!-- FILA 3                              -->

            <!-- =================================== -->

            <tr>



                <!-- COLUMNA 2 - MARCA DE ACCIÓN COLUMNA 3 -->

                <td style="

                    border:1px solid #000;

                    padding:0;

                    text-align:center;

                    vertical-align:middle;

                    font-weight:bold;

                    height:{{ $tabla3AltoFila3 }}px;

                ">

                    @if($accionEstaSeleccionada($tabla3AccionesCol3[3]))

                        X

                    @endif

                </td>



                <!-- COLUMNA 3 - ACCIÓN -->

                <!-- SIN BORDES, TEXTO A LA IZQUIERDA Y CENTRADO VERTICALMENTE -->

                <td style="

                    border:none;

                    padding:0 3px;

                    text-align:left;

                    vertical-align:middle;

                    font-weight:bold;

                    height:{{ $tabla3AltoFila3 }}px;

                    line-height:1.1;

                ">

                    {{ mb_strtoupper($tabla3AccionesCol3[3], 'UTF-8') }}

                </td>



                <!-- COLUMNA 4 - UNIDA DE FILA 3 A 8 -->

                <!-- EN BLANCO Y SIN BORDES -->

                <td

                    rowspan="6"

                    style="

                        border:none;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                    "

                >&nbsp;</td>



                <!-- COLUMNA 5 - MARCA DE ACCIÓN COLUMNA 6 -->

                <td style="

                    border:1px solid #000;

                    padding:0;

                    text-align:center;

                    vertical-align:middle;

                    font-weight:bold;

                    height:{{ $tabla3AltoFila3 }}px;

                ">

                    @if($accionEstaSeleccionada($tabla3AccionesCol6[3]))

                        X

                    @endif

                </td>



                <!-- COLUMNA 6 - ACCIÓN -->

                <!-- SIN BORDES, TEXTO A LA IZQUIERDA Y CENTRADO VERTICALMENTE -->

                <td style="

                    border:none;

                    padding:0 3px;

                    text-align:left;

                    vertical-align:middle;

                    font-weight:bold;

                    height:{{ $tabla3AltoFila3 }}px;

                    line-height:1.1;

                ">

                    {{ mb_strtoupper($tabla3AccionesCol6[3], 'UTF-8') }}

                </td>

            </tr>



            <!-- =================================== -->

            <!-- FILAS 4 A 8                         -->

            <!-- =================================== -->

            @for($filaTabla3 = 4; $filaTabla3 <= 8; $filaTabla3++)



                @php

                    $altoFilaTabla3 = ${'tabla3AltoFila' . $filaTabla3};

                @endphp



                <tr>



                    <!-- COLUMNA 2 - MARCA DE ACCIÓN COLUMNA 3 -->

                    <td style="

                        border:1px solid #000;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $altoFilaTabla3 }}px;

                    ">

                        @if($accionEstaSeleccionada($tabla3AccionesCol3[$filaTabla3]))

                            X

                        @endif

                    </td>



                    <!-- COLUMNA 3 - ACCIÓN -->

                    <!-- SIN BORDES, TEXTO A LA IZQUIERDA Y CENTRADO VERTICALMENTE -->

                    <td style="

                        border:none;

                        padding:0 3px;

                        text-align:left;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $altoFilaTabla3 }}px;

                        line-height:1.1;

                    ">

                        {{ mb_strtoupper($tabla3AccionesCol3[$filaTabla3], 'UTF-8') }}

                    </td>



                    <!-- COLUMNA 4 ESTÁ UNIDA DESDE LA FILA 3 HASTA LA 8 -->

                    <!-- COLUMNA 5 - MARCA DE ACCIÓN COLUMNA 6 -->

                    <td style="

                        border:1px solid #000;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $altoFilaTabla3 }}px;

                    ">

                        @if($accionEstaSeleccionada($tabla3AccionesCol6[$filaTabla3]))

                            X

                        @endif

                    </td>



                    <!-- COLUMNA 6 - ACCIÓN -->

                    <!-- SIN BORDES, TEXTO A LA IZQUIERDA Y CENTRADO VERTICALMENTE -->

                    <td style="

                        border:none;

                        padding:0 3px;

                        text-align:left;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $altoFilaTabla3 }}px;

                        line-height:1.1;

                    ">

                        {{ mb_strtoupper($tabla3AccionesCol6[$filaTabla3], 'UTF-8') }}

                    </td>

                </tr>

            @endfor



            <!-- =================================== -->

            <!-- FILA 9                              -->

            <!-- COLUMNAS 2 A 6 UNIDAS              -->

            <!-- EN BLANCO Y SIN BORDES              -->

            <!-- =================================== -->

            <tr>

                <td

                    colspan="5"

                    style="

                        border:none;

                        padding:0;

                        text-align:center;

                        vertical-align:middle;

                    "

                >

                    <div style="

                        height:{{ $tabla3AltoFila9 }}px;

                        line-height:0;

                        padding:0;

                        margin:0;

                        font-size:0;

                    ">&nbsp;</div>

                </td>

            </tr>



            <!-- =================================== -->

            <!-- FILA 10                             -->

            <!-- COLUMNAS 1 A 7 UNIDAS              -->

            <!-- =================================== -->

            <tr>

                <td

                    colspan="7"

                    style="

                        border:1px solid #000;

                        background:#e5e7eb;

                        padding:0 6px;

                        text-align:left;

                        vertical-align:middle;

                        font-weight:bold;

                        height:{{ $tabla3AltoFila10 }}px;

                    "

                >

                    OTRO, ESPECIFIQUE:

                </td>

            </tr>



            <!-- =================================== -->

            <!-- FILA 11                             -->

            <!-- COLUMNAS 1 A 7 UNIDAS              -->

            <!-- =================================== -->

            <tr>

                <td

                    colspan="7"

                    style="

                        border:1px solid #000;

                        padding:0 6px;

                        text-align:left;

                        vertical-align:middle;

                        height:{{ $tabla3AltoFila11 }}px;

                    "

                >

                    {{ $especifiqueOtra }}

                </td>

            </tr>

        </table>

        <!-- ======================================= -->
        <!-- CUARTA TABLA - 12 COLUMNAS X 5 FILAS   -->
        <!-- ======================================= -->

        @php
            /*
             * ==========================================================
             * ANCHO DE LAS 12 COLUMNAS DE LA CUARTA TABLA
             * ==========================================================
             *
             * Modifica SOLAMENTE estos valores.
             * Son porcentajes y deben sumar 100.
             */

            $tabla4Col1  = 5;
            $tabla4Col2  = 20;
            $tabla4Col3  = 5;
            $tabla4Col4  = 3;
            $tabla4Col5  = 13;
            $tabla4Col6  = 5;
            $tabla4Col7  = 3;
            $tabla4Col8  = 13;
            $tabla4Col9  = 5;
            $tabla4Col10 = 3;
            $tabla4Col11 = 20;
            $tabla4Col12 = 5;

            /*
             * ==========================================================
             * ALTO DE LAS 5 FILAS DE LA CUARTA TABLA
             * ==========================================================
             *
             * Modifica SOLAMENTE estos valores.
             * Están expresados en píxeles.
             */

            $tabla4AltoFila1 = 18;
            $tabla4AltoFila2 = 10;
            $tabla4AltoFila3 = 15;
            $tabla4AltoFila4 = 10;
            $tabla4AltoFila5 = 1;

            $tabla4AltosFilas = [
                $tabla4AltoFila1,
                $tabla4AltoFila2,
                $tabla4AltoFila3,
                $tabla4AltoFila4,
                $tabla4AltoFila5,
            ];
        

            /*
             * ==========================================================
             * TIPOS DE MANTENIMIENTO SELECCIONADOS
             * ==========================================================
             *
             * Se obtienen desde tabla_mantenimiento_prensas para marcar
             * con "X" Preventivo, Correctivo o Correctivo Preventivo.
             */

            $actividadesMantenimiento = $answers['tabla_mantenimiento_prensas'] ?? [];

            if (is_string($actividadesMantenimiento)) {
                $actividadesDecodificadas = json_decode($actividadesMantenimiento, true);
                $actividadesMantenimiento = is_array($actividadesDecodificadas)
                    ? $actividadesDecodificadas
                    : [];
            }

            if (!is_array($actividadesMantenimiento)) {
                $actividadesMantenimiento = [];
            }

            if (isset($actividadesMantenimiento['tipo_mantenimiento'])) {
                $actividadesMantenimiento = [$actividadesMantenimiento];
            }

            $tiposMantenimientoSeleccionados = [];

            foreach ($actividadesMantenimiento as $actividadMantenimiento) {
                if (!is_array($actividadMantenimiento)) {
                    continue;
                }

                $tipoMantenimientoActividad = $actividadMantenimiento['tipo_mantenimiento'] ?? '';

                if ($tipoMantenimientoActividad !== '') {
                    $tiposMantenimientoSeleccionados[] = $tipoMantenimientoActividad;
                }
            }

            $tipoMantenimientoEstaSeleccionado = function ($tipo) use ($tiposMantenimientoSeleccionados) {
                return in_array($tipo, $tiposMantenimientoSeleccionados, true);
            };

        @endphp

        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
            margin-top:0;
        ">

            <!-- DEFINICIÓN DE LAS 12 COLUMNAS -->
            <tr style="height:0; line-height:0;">
                <td style="width:{{ $tabla4Col1 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col2 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col3 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col4 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col5 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col6 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col7 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col8 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col9 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col10 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col11 }}%; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $tabla4Col12 }}%; padding:0; border:none; height:0;"></td>
            </tr>

            <!-- =================================== -->
            <!-- FILA 1 - COLUMNAS 1 A 12 UNIDAS   -->
            <!-- =================================== -->
            <tr>
                <td
                    colspan="12"
                    style="
                        border:1px solid #000;
                        border-top:none;
                        background:#e5e7eb;
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        font-weight:bold;
                        height:{{ $tabla4AltoFila1 }}px;
                    "
                >
                    ACTIVIDAD
                </td>
            </tr>

            <!-- =================================== -->
            <!-- FILA 2 - COLUMNAS 1 A 12 UNIDAS   -->
            <!-- SOMBREADA Y EN BLANCO              -->
            <!-- =================================== -->
            <tr>
                <td
                    colspan="12"
                    style="
                        border:none;
                        border-left:1px solid #000;
                        border-right:1px solid #000;
                        background:#e5e7eb;
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        height:{{ $tabla4AltoFila2 }}px;
                    "
                >&nbsp;</td>
            </tr>

            <!-- =================================== -->
            <!-- FILA 3 - TIPO DE MANTENIMIENTO     -->
            <!-- =================================== -->
            <tr>

                <!-- COLUMNA 1 - BLANCO, SIN BORDE DERECHO -->
                <td style="
                    border:none;
                    border-left:1px solid #000;
                    border-right:none;
                    background:#e5e7eb;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                    height:{{ $tabla4AltoFila3 }}px;
                ">&nbsp;</td>

                <!-- COLUMNA 2 - TÍTULO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0 3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                    line-height:1.1;
                ">
                    TIPO DE MANTENIMIENTO:
                </td>

                <!-- COLUMNA 3 - BLANCO SIN BORDES -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0;
                    height:{{ $tabla4AltoFila3 }}px;
                ">&nbsp;</td>

                <!-- COLUMNA 4 - X PREVENTIVO -->
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                ">
                    @if($tipoMantenimientoEstaSeleccionado('Preventivo'))
                        X
                    @endif
                </td>

                <!-- COLUMNA 5 - PREVENTIVO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0 3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                ">
                    Preventivo
                </td>

                <!-- COLUMNA 6 - BLANCO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0;
                    height:{{ $tabla4AltoFila3 }}px;
                ">&nbsp;</td>

                <!-- COLUMNA 7 - X CORRECTIVO -->
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                ">
                    @if($tipoMantenimientoEstaSeleccionado('Correctivo'))
                        X
                    @endif
                </td>

                <!-- COLUMNA 8 - CORRECTIVO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0 3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                ">
                    Correctivo
                </td>

                <!-- COLUMNA 9 - BLANCO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0;
                    height:{{ $tabla4AltoFila3 }}px;
                ">&nbsp;</td>

                <!-- COLUMNA 10 - X CORRECTIVO PREVENTIVO -->
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                ">
                    @if($tipoMantenimientoEstaSeleccionado('Correctivo Preventivo'))
                        X
                    @endif
                </td>

                <!-- COLUMNA 11 - CORRECTIVO PREVENTIVO -->
                <td style="
                    border:none;
                    background:#e5e7eb;
                    padding:0 3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    height:{{ $tabla4AltoFila3 }}px;
                    line-height:1.1;
                ">
                    Correctivo preventivo
                </td>

                <!-- COLUMNA 12 - BLANCO -->
                <td style="
                    border:none;
                    border-right:1px solid #000;
                    background:#e5e7eb;
                    padding:0;
                    height:{{ $tabla4AltoFila3 }}px;
                ">&nbsp;</td>

            </tr>

            <!-- =================================== -->
            <!-- FILA 4 - COLUMNAS 1 A 12 UNIDAS   -->
            <!-- SOMBREADA Y EN BLANCO              -->
            <!-- =================================== -->
            <tr>
                <td
                    colspan="12"
                    style="
                        border:none;
                        border-left:1px solid #000;
                        border-right:1px solid #000;
                        background:#e5e7eb;
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        height:{{ $tabla4AltoFila4 }}px;
                    "
                >&nbsp;</td>
            </tr>

            <!-- =================================== -->
            <!-- FILA 5 - COLUMNAS 1 A 12 UNIDAS   -->
            <!-- =================================== -->
            <tr>
                <td
                    colspan="12"
                    style="
                        border:1px solid #000;
                        border-bottom:none;
                        padding:0;
                        text-align:center;
                        vertical-align:middle;
                        height:{{ $tabla4AltoFila5 }}px;
                    "
                >&nbsp;</td>
            </tr>

        </table>

            @endif


            <!-- ======================================= -->
            <!-- QUINTA TABLA - ENCABEZADO               -->
            <!-- ======================================= -->
            <table style="
                width:100%;
                border-collapse:collapse;
                table-layout:fixed;
                font-size:8px;
                margin-top:0;
            ">

                <tr style="height:0; line-height:0;">
                    <td style="
                        width:{{ $tabla5Col1 }}%;
                        padding:0;
                        border:none;
                        height:0;
                    "></td>

                    <td style="
                        width:{{ $tabla5Col2 }}%;
                        padding:0;
                        border:none;
                        height:0;
                    "></td>

                    <td style="
                        width:{{ $tabla5Col3 }}%;
                        padding:0;
                        border:none;
                        height:0;
                    "></td>
                </tr>

                <tr>
                    <td style="
                        border:1px solid #000;
                        border-top:1px solid #000;
                        background:#e5e7eb;
                        padding:0 4px;
                        text-align:center;
                        vertical-align:middle;
                        font-weight:bold;
                        height:{{ $tabla5AltoFila1 }}px;
                    ">
                        Problema actual
                    </td>

                    <td style="
                        border:1px solid #000;
                        border-top:1px solid #000;
                        background:#e5e7eb;
                        padding:0 4px;
                        text-align:center;
                        vertical-align:middle;
                        font-weight:bold;
                        height:{{ $tabla5AltoFila1 }}px;
                    ">
                        Acciones realizadas
                    </td>

                    <td style="
                        border:1px solid #000;
                        border-top:1px solid #000;
                        background:#e5e7eb;
                        padding:0 4px;
                        text-align:center;
                        vertical-align:middle;
                        font-weight:bold;
                        height:{{ $tabla5AltoFila1 }}px;
                    ">
                        Evidencia fotográfica
                    </td>
                </tr>

            </table>

            <!-- ======================================= -->
            <!-- CONTENIDO DE TABLA 5 DE ESTA HOJA       -->
            <!-- ======================================= -->
            @foreach(
                $paginaTabla5ActualDatos
                as $mantenimientoTabla5
            )

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                    margin:0;
                    padding:0;
                    page-break-inside:avoid;
                ">

                    <tr style="height:0; line-height:0;">
                        <td style="
                            width:{{ $tabla5Col1 }}%;
                            padding:0;
                            border:none;
                            height:0;
                        "></td>

                        <td style="
                            width:{{ $tabla5Col2 }}%;
                            padding:0;
                            border:none;
                            height:0;
                        "></td>

                        <td style="
                            width:{{ $tabla5Col3 }}%;
                            padding:0;
                            border:none;
                            height:0;
                        "></td>
                    </tr>

                    @for(
                        $filaProblemaTabla5 = 0;
                        $filaProblemaTabla5
                            < $mantenimientoTabla5['filas'];
                        $filaProblemaTabla5++
                    )
                        @php
                            $accionTabla5 =
                                $mantenimientoTabla5[
                                    'acciones'
                                ][$filaProblemaTabla5]
                                ?? null;

                            $textoAccionTabla5 =
                                is_array($accionTabla5)
                                    ? trim((string) (
                                        $accionTabla5['accion']
                                        ?? ''
                                    ))
                                    : '';

                            $evidenciasAccionTabla5 =
                                is_array($accionTabla5)
                                    ? (
                                        $accionTabla5[
                                            'evidencia_fotografica'
                                        ]
                                        ?? []
                                    )
                                    : [];

                            if (
                                !is_array(
                                    $evidenciasAccionTabla5
                                )
                            ) {
                                $evidenciasAccionTabla5 =
                                    $evidenciasAccionTabla5
                                        ? [
                                            $evidenciasAccionTabla5
                                        ]
                                        : [];
                            }

                            $gruposEvidenciasTabla5 =
                                array_chunk(
                                    array_values(
                                        $evidenciasAccionTabla5
                                    ),
                                    2
                                );
                        @endphp

                        <tr style="page-break-inside:avoid;">

                            @if($filaProblemaTabla5 === 0)
                                <td
                                    rowspan="{{
                                        $mantenimientoTabla5[
                                            'filas'
                                        ]
                                    }}"
                                    style="
                                        border:none;
                                        border-left:1px solid #000;
                                        border-right:1px solid #000;
                                        border-bottom:1px solid #000;
                                        padding:4px 5px;
                                        text-align:left;
                                        vertical-align:middle;
                                        line-height:1.2;
                                        white-space:normal;
                                        word-break:break-word;
                                        overflow-wrap:anywhere;
                                        page-break-inside:avoid;
                                    "
                                >
                                    {{
                                        $mantenimientoTabla5[
                                            'problema_actual'
                                        ]
                                    }}
                                </td>
                            @endif

                            <td style="
                                border:none;
                                border-right:1px solid #000;
                                border-bottom:1px solid #000;
                                padding:4px 5px;
                                text-align:left;
                                vertical-align:middle;
                                line-height:1.2;
                                white-space:normal;
                                word-break:break-word;
                                overflow-wrap:anywhere;
                                height:{{
                                    $tabla5AltoFilaDetalle
                                }}px;
                                page-break-inside:avoid;
                            ">
                                @if($textoAccionTabla5 !== '')
                                    {{ $textoAccionTabla5 }}
                                @else
                                    &nbsp;
                                @endif
                            </td>

                            <td style="
                                border:none;
                                border-right:1px solid #000;
                                border-bottom:1px solid #000;
                                padding:3px;
                                text-align:center;
                                vertical-align:middle;
                                height:{{
                                    $tabla5AltoFilaDetalle
                                }}px;
                                page-break-inside:avoid;
                            ">
                                @if(
                                    $accionTabla5
                                    && count(
                                        $gruposEvidenciasTabla5
                                    ) > 0
                                )
                                    <table style="
                                        width:100%;
                                        border-collapse:collapse;
                                        table-layout:fixed;
                                        margin:0;
                                        padding:0;
                                    ">
                                        @foreach(
                                            $gruposEvidenciasTabla5
                                            as $grupoEvidenciasTabla5
                                        )
                                            <tr>
                                                @for(
                                                    $indiceImagenTabla5 = 0;
                                                    $indiceImagenTabla5 < 2;
                                                    $indiceImagenTabla5++
                                                )
                                                    @php
                                                        $evidenciaTabla5 =
                                                            $grupoEvidenciasTabla5[
                                                                $indiceImagenTabla5
                                                            ]
                                                            ?? null;

                                                        $imagenTabla5Src =
                                                            $evidenciaTabla5
                                                                ? $tabla5ObtenerImagenSrc(
                                                                    $evidenciaTabla5
                                                                )
                                                                : null;
                                                    @endphp

                                                    <td style="
                                                        width:50%;
                                                        border:none;
                                                        padding:2px;
                                                        text-align:center;
                                                        vertical-align:middle;
                                                    ">
                                                        @if(
                                                            $imagenTabla5Src
                                                        )
                                                            <img
                                                                src="{{
                                                                    $imagenTabla5Src
                                                                }}"
                                                                style="
                                                                    max-width:96%;
                                                                    max-height:75px;
                                                                    object-fit:contain;
                                                                    display:block;
                                                                    margin:0 auto;
                                                                "
                                                            >
                                                        @else
                                                            &nbsp;
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    &nbsp;
                                @endif
                            </td>
                        </tr>
                    @endfor

                </table>
            @endforeach

            <!-- ======================================= -->
            <!-- SEXTA TABLA - 2 COLUMNAS X 6 FILAS     -->
            <!-- ======================================= -->
            @if($paginaIndexTabla5 === ($totalPaginas - 1))

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                    margin:0;
                    padding:0;
                    page-break-inside:avoid;
                ">

                    <!-- =================================== -->
                    <!-- DEFINICIÓN DE ANCHOS                -->
                    <!-- =================================== -->
                    <tr style="height:0; line-height:0;">
                        <td style="
                            width:{{ $tabla6Col1 }}%;
                            padding:0;
                            border:none;
                            height:0;
                        "></td>

                        <td style="
                            width:{{ $tabla6Col2 }}%;
                            padding:0;
                            border:none;
                            height:0;
                        "></td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 1 - OBSERVACIONES             -->
                    <!-- COLUMNAS 1 Y 2 UNIDAS              -->
                    <!-- =================================== -->
                    <tr>
                        <td
                            colspan="2"
                            style="
                                border:1px solid #000;
                                border-top:none;
                                background:#e5e7eb;
                                padding:0 6px;
                                text-align:left;
                                vertical-align:middle;
                                font-weight:bold;
                                height:{{ $tabla6AltoFila1 }}px;
                            "
                        >
                            OBSERVACIONES:
                        </td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 2 - DATO OBSERVACIONES        -->
                    <!-- COLUMNAS 1 Y 2 UNIDAS              -->
                    <!-- =================================== -->
                    <tr>
                        <td
                            colspan="2"
                            style="
                                border:1px solid #000;
                                padding:4px 6px;
                                text-align:left;
                                vertical-align:middle;
                                line-height:1.2;
                                white-space:normal;
                                word-break:break-word;
                                overflow-wrap:anywhere;
                                height:{{ $tabla6AltoFila2 }}px;
                            "
                        >
                            @if(trim($observacionesTabla6) !== '')
                                {{ $observacionesTabla6 }}
                            @else
                                &nbsp;
                            @endif
                        </td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 3 - RECOMENDACIONES           -->
                    <!-- COLUMNAS 1 Y 2 UNIDAS              -->
                    <!-- =================================== -->
                    <tr>
                        <td
                            colspan="2"
                            style="
                                border:1px solid #000;
                                background:#e5e7eb;
                                padding:0 6px;
                                text-align:left;
                                vertical-align:middle;
                                font-weight:bold;
                                height:{{ $tabla6AltoFila3 }}px;
                            "
                        >
                            RECOMENDACIONES:
                        </td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 4 - DATO RECOMENDACIONES      -->
                    <!-- COLUMNAS 1 Y 2 UNIDAS              -->
                    <!-- =================================== -->
                    <tr>
                        <td
                            colspan="2"
                            style="
                                border:1px solid #000;
                                padding:4px 6px;
                                text-align:left;
                                vertical-align:middle;
                                line-height:1.2;
                                white-space:normal;
                                word-break:break-word;
                                overflow-wrap:anywhere;
                                height:{{ $tabla6AltoFila4 }}px;
                            "
                        >
                            @if(trim($recomendacionesTabla6) !== '')
                                {{ $recomendacionesTabla6 }}
                            @else
                                &nbsp;
                            @endif
                        </td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 5 - ENCABEZADOS DE FIRMAS     -->
                    <!-- =================================== -->
                    <tr>
                        <!-- COLUMNA 1 -->
                        <td style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:3px 6px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            line-height:1.15;
                            height:{{ $tabla6AltoFila5 }}px;
                        ">
                            NOMBRE Y FIRMA DE QUIEN INSPECCIONA O DA MANTENIMIENTO
                        </td>

                        <!-- COLUMNA 2 -->
                        <td style="
                            border:1px solid #000;
                            background:#e5e7eb;
                            padding:3px 6px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            line-height:1.15;
                            height:{{ $tabla6AltoFila5 }}px;
                        ">
                            NOMBRE Y FIRMA DE QUIEN AUTORIZA
                        </td>
                    </tr>

                    <!-- =================================== -->
                    <!-- FILA 6 - FIRMAS Y NOMBRES          -->
                    <!-- =================================== -->
                    <tr>
                        <!-- COLUMNA 1 - INSPECCIONA / MANTENIMIENTO -->
                        <td style="
                            border:1px solid #000;
                            padding:4px 6px;
                            text-align:center;
                            vertical-align:middle;
                            height:{{ $tabla6AltoFila6 }}px;
                        ">
                            @if($firmaInspeccionaTabla6Src)
                                <img
                                    src="{{ $firmaInspeccionaTabla6Src }}"
                                    style="
                                        max-width:80%;
                                        max-height:{{ $tabla6AltoMaxFirma }}px;
                                        object-fit:contain;
                                        display:block;
                                        margin:0 auto 3px auto;
                                    "
                                >
                            @endif

                            <div style="
                                text-align:center;
                                line-height:1.15;
                                margin:0;
                                padding:0;
                            ">
                                @if(trim($nombreInspeccionaTabla6) !== '')
                                    {{ $nombreInspeccionaTabla6 }}
                                @else
                                    &nbsp;
                                @endif
                            </div>
                        </td>

                        <!-- COLUMNA 2 - AUTORIZA -->
                        <td style="
                            border:1px solid #000;
                            padding:4px 6px;
                            text-align:center;
                            vertical-align:middle;
                            height:{{ $tabla6AltoFila6 }}px;
                        ">
                            @if($firmaAutorizaTabla6Src)
                                <img
                                    src="{{ $firmaAutorizaTabla6Src }}"
                                    style="
                                        max-width:80%;
                                        max-height:{{ $tabla6AltoMaxFirma }}px;
                                        object-fit:contain;
                                        display:block;
                                        margin:0 auto 3px auto;
                                    "
                                >
                            @endif

                            <div style="
                                text-align:center;
                                line-height:1.15;
                                margin:0;
                                padding:0;
                            ">
                                @if(trim($nombreAutorizaTabla6) !== '')
                                    {{ $nombreAutorizaTabla6 }}
                                @else
                                    &nbsp;
                                @endif
                            </div>
                        </td>
                    </tr>

                </table>

            @endif


        </div>

    @endforeach

</body>



</html>
