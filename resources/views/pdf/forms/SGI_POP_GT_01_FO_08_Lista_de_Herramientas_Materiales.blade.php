<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Lista de Herramientas Materiales' }}</title>

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
                    CODIFICACIÓN: SGI-POP-GT-01-FO-08
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
                    Lista de Herramientas Materiales
                </td>

                <td class="right-cell">
                    REVISIÓN: 01
                </td>
            </tr>
        </table>

        <!-- FECHA Y TALLER -->
        <table class="header-table" style="margin-top: 15px;">
            <tr>
                <td style="width:20%; text-align:center; font-weight:bold;">
                    UBICACIÓN
                </td>
        
                <td style="width:20%; text-align:center;">
                    {{ $taller }}
                </td>

                <td style="width:20%; border:none !important; background:#fff;"></td>
        
                <td style="width:20%; text-align:center; font-weight:bold;">
                    FECHA
                </td>
        
                <td style="width:20%; text-align:center;">
                    {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        @php
            $firmaElabora = $answers['firma_elabora'] ?? null;
            $firmaElaboraSrc = null;
        
            if ($firmaElabora) {
                $firmaPath = storage_path('app/public/' . $firmaElabora);
        
                if (file_exists($firmaPath)) {
                    $firmaElaboraSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($firmaPath));
                }
            }
        
            $firmaRevisa = $answers['firma_revisa'] ?? null;
            $firmaRevisaSrc = null;
        
            if ($firmaRevisa) {
                $firmaPath = storage_path('app/public/' . $firmaRevisa);
        
                if (file_exists($firmaPath)) {
                    $firmaRevisaSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($firmaPath));
                }
            }
        @endphp
        
        <!-- ELABORA / REVISA -->
        <table class="header-table" style="margin-top: 10px;">
            <tr>
                <td style="width:10%; text-align:center; font-weight:bold;">
                    NOMBRE Y FIRMA QUIEN ELABORA
                </td>
        
                <td style="width:30%; text-align:center; vertical-align:middle;">
                    <div>
                        {{ $answers['nombre_elabora'] ?? '' }}
                    </div>
        
                    @if($firmaElaboraSrc)
                        <img
                            src="{{ $firmaElaboraSrc }}"
                            style="
                                max-width:120px;
                                max-height:40px;
                                object-fit:contain;
                                margin-top:4px;
                            "
                        >
                    @endif
                </td>
        
                <td style="width:20%; border:none !important; background:#fff;"></td>
        
                <td style="width:10%; text-align:center; font-weight:bold;">
                    NOMBRE Y FIRMA QUIEN REVISA
                </td>
        
                <td style="width:30%; text-align:center; vertical-align:middle;">
                    <div>
                        {{ $answers['nombre_revisa'] ?? '' }}
                    </div>
        
                    @if($firmaRevisaSrc)
                        <img
                            src="{{ $firmaRevisaSrc }}"
                            style="
                                max-width:120px;
                                max-height:40px;
                                object-fit:contain;
                                margin-top:4px;
                            "
                        >
                    @endif
                </td>
            </tr>
        </table>

        @php
            $herramientasRows = $answers['tabla_herramientas_materiales'] ?? [];
        
            if (!is_array($herramientasRows)) {
                $herramientasRows = [];
            }
        
            // ANCHOS DE COLUMNAS
            $itemWidth = '7%';
            $descripcionWidth = '28.5%';
            $marcaWidth = '10%';
            $serieWidth = '10%';
            $piezasWidth = '8%';
            $cumpleWidth = '8%';
            $observacionesWidth = '28.5%';
        
            // ALTO DE FILAS A PARTIR DE LA FILA 2
            $dataRowHeight = 10;
        @endphp
        
        <!-- TABLA HERRAMIENTAS / MATERIALES -->
        <table style="
            width:100%;
            margin-top:10px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:7px;
        ">
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:{{ $itemWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $descripcionWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $marcaWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $serieWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $piezasWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $cumpleWidth }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $observacionesWidth }}; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 ENCABEZADOS -->
            <tr>
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    ITEM
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    DESCRIPCIÓN
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    MARCA
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    SERIE
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    N° DE PIEZAS
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    CUMPLE<br>(SI, NO)
                </td>
        
                <td style="border:1px solid #000; background:#d1d5db; font-weight:bold; text-align:center; vertical-align:middle; padding:3px;">
                    OBSERVACIONES
                </td>
            </tr>
        
            @for($i = 0; $i < 50; $i++)
                @php
                    $row = $herramientasRows[$i] ?? [];
        
                    $estado = $row['estado'] ?? '';
                    $cumple = '';
        
                    if ($estado === 'Cumple') {
                        $cumple = 'SI';
                    } elseif ($estado === 'No Cumple') {
                        $cumple = 'NO';
                    }
                @endphp
        
                <tr>
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['numero_item'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['descripcion_herramienta'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['marca'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['numero_serie'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['numero_piezas'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle; font-weight:bold;">
                        {{ $cumple }}
                    </td>
        
                    <td style="border:1px solid #000; height:{{ $dataRowHeight }}px; padding:2px; text-align:center; vertical-align:middle;">
                        {{ $row['observaciones'] ?? '' }}
                    </td>
                </tr>
            @endfor
        </table>

    </div>
</body>
</html>