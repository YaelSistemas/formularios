<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Inspección de Estrobos' }}</title>

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
                $logoSrc =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($path));
            }
        }
        /*
         * Divide la tabla en páginas de 10 registros.
         * Cada página se completa con filas vacías hasta llegar a 10.
         */
        $tablaEstrobosOriginal = collect($answers['tabla_estrobos'] ?? [])
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                return collect($row)->contains(function ($value) {
                    return $value !== null && $value !== '';
                });
            })
            ->values()
            ->all();

        // Aunque no existan registros, genera una hoja con 10 filas vacías.
        if (empty($tablaEstrobosOriginal)) {
            $tablaEstrobosOriginal = [[]];
        }

        $paginasEstrobos = array_chunk($tablaEstrobosOriginal, 10);

        $paginasEstrobos = array_map(function ($pagina) {
            while (count($pagina) < 10) {
                $pagina[] = [];
            }

            return $pagina;
        }, $paginasEstrobos);

        $totalPaginas = count($paginasEstrobos);
    @endphp

    @foreach($paginasEstrobos as $pageIndex => $tablaEstrobos)
        <div
            class="sheet"
            style="{{ $pageIndex > 0 ? 'page-break-before: always;' : '' }}"
        >

        <!-- HEADER -->
        <table class="header-table">
        
            <!-- ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:25%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <!-- LOGO -->
                <td rowspan="4" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>
        
                <!-- EMPRESA -->
                <td class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>
        
                <!-- CODIFICACIÓN -->
                <td class="right-cell">
                    CÓDIGO: SGI-POP-GT-01-FO-11
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <!-- SISTEMA -->
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>
        
                <!-- FECHA -->
                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <!-- NOMBRE DEL FORMULARIO USA FILAS 3 Y 4 -->
                <td rowspan="2" class="center-cell">
                    CHECKLIST DE INSPECCIÓN DE ESTROBOS
                </td>
        
                <!-- REVISIÓN -->
                <td class="right-cell">
                    REVISIÓN: 03
                </td>
            </tr>
        
            <!-- FILA 4 -->
            <tr>
                <!-- PÁGINA -->
                <td class="right-cell">
                    PÁGINA: {{ $pageIndex + 1 }} DE {{ $totalPaginas }}
                </td>
            </tr>
        
        </table>

        <!-- FECHA Y TALLER -->
        <table style="
            width:100%;
            margin-top:12px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:9px;
        ">
        
            <!-- ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
                <td style="width:15%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <tr>
                <!-- ESPACIO -->
                <td style="border:none;"></td>

                <!-- FECHA -->
                <td style="
                    border:none;
                    text-align:left;
                    vertical-align:bottom;
                    padding:0;
                ">
                    <span style="font-weight:bold;">
                        FECHA:
                    </span>
        
                    <span style="
                        display:inline-block;
                        width:180px;
                        text-align:center;
                        border-bottom:1px solid #000;
                        padding-bottom:0px;
                        margin-left:6px;
                    ">
                        {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                    </span>
                </td>
        
                <!-- ESPACIO -->
                <td style="border:none;"></td>
        
                <!-- TALLER -->
                <td style="
                    border:none;
                    text-align:right;
                    vertical-align:bottom;
                    padding:0;
                ">
                    <span style="font-weight:bold;">
                        EMPRESA / UNIDAD DE SERVICIO:
                    </span>
        
                    <span style="
                        display:inline-block;
                        width:180px;
                        text-align:center;
                        border-bottom:1px solid #000;
                        padding-bottom:0px;
                        margin-left:6px;
                    ">
                        {{ $answers['taller'] ?? '' }}
                    </span>
                </td>
        
                <!-- ESPACIO -->
                <td style="border:none;"></td>
            </tr>
        </table>
    
        <!-- INDICACIÓN -->
        <div style="
            width:100%;
            margin-top:12px;
            text-align:center;
            font-size:8px;
            line-height:1.3;
        ">
            MARQUE SEGÚN LAS CONDICIONES DEL ESTROBO:
            &nbsp;&nbsp;&nbsp;&nbsp;
            BIEN;
            <span style="font-weight:bold;">✔︎</span>
            &nbsp;&nbsp;
            MAL;
            <span style="font-weight:bold;">X</span>
            &nbsp;&nbsp;
            NO APLICA.
            <span style="font-weight:bold;">N/A</span>
        </div>
     

          
        <!-- CONTENEDOR TABLA + IMAGEN -->
        <table style="
            width:100%;
            margin-top:5px;
            border-collapse:collapse;
            table-layout:fixed;
        ">
        
            <!-- ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:85%; padding:0; border:none;"></td>
                <td style="width:2%; padding:0; border:none;"></td>
                <td style="width:13%; padding:0; border:none;"></td>
            </tr>
        
            <tr>
        
                <!-- TABLA -->
                <td style="
                    border:none;
                    vertical-align:top;
                    padding:0;
                ">
        
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        font-size:7px;
                    ">
        
                        <!-- ANCHOS COLUMNAS -->
                        <tr style="height:0; line-height:0;">
        
                            <td style="width:7%; padding:0;"></td>
                            <td style="width:7%; padding:0;"></td>
                            <td style="width:7%; padding:0;"></td>
        
                            <td style="width:9%; padding:0;"></td>
                            <td style="width:9%; padding:0;"></td>
                            <td style="width:9%; padding:0;"></td>
                            <td style="width:9%; padding:0;"></td>
                            <td style="width:9%; padding:0;"></td>
        
                            <td style="width:9%; padding:0;"></td>
                            <td style="width:9%; padding:0;"></td>

                            <td style="width:16%; padding:0;"></td>
        
                        </tr>
        
                        <!-- HEADERS -->
                        <tr>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                N° de identificación
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Capacidad
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Diámetro de cable
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Cocas (distorsionadas, dobladas, oxidadas, corroídas)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Cables (distorsionados, oxidados, corroídos)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Torones (desgastados o cortados)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Alambres (desgastados o cortados)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Casquillos (deformaciones, abiertos, oxidados, corroídos)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Condición de alma o soporte central
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Lubricación (sequedad)
                            </td>
        
                            <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:4px 2px; height:25px;">
                                Observaciones
                            </td>
        
                        </tr>
        
                        <!-- FILAS -->
                        @foreach($tablaEstrobos as $row)
        
                            <tr>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['numero_identificacion'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['capacidad'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['diametro_cable'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['cocas_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['cables_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['torones_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['alambres_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['casquillos_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['condicion_alma_soporte_central_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['lubricacion_estado'] ?? '' }}
                                </td>
        
                                <td style="border:1px solid #000; padding:3px; text-align:center; height:25px;">
                                    {{ $row['observaciones'] ?? '' }}
                                </td>
        
                            </tr>
        
                        @endforeach
        
                    </table>
        
                </td>
        
                <!-- ESPACIO -->
                <td style="border:none;"></td>
        
                <!-- IMAGEN -->
                <td style="
                    border:none;
                    vertical-align:top;
                    text-align:center;
                    padding:0;
                ">
        
                    <img
                        src="{{ public_path('images/forms/SGI_POP_GT_01_FO_11_Checklist_de_Inspeccion_de_Estrobos/estrobos.png') }}"
                        style="
                            width:150px; 
                            height:340px; 
                            object-fit:contain; 
                        "
                    >
                </td>
            </tr>
        </table>

        <!-- FIRMAS -->
        <table style="
            width:100%;
            margin-top:15px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:9px;
        ">
        
            <!-- ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:30%; padding:0; border:none;"></td>
                <td style="width:7%; padding:0; border:none;"></td>
                <td style="width:30%; padding:0; border:none;"></td>
                <td style="width:25%; padding:0; border:none;"></td>
            </tr>
        
            <tr>
        
                <!-- ESPACIO -->
                <td style="border:none;"></td>
        
                <!-- REALIZÓ -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                    height:75px;
                ">
        
                    <div style="
                        font-weight:bold;
                        margin-bottom:2px;
                        font-size:9px;
                    ">
                        REALIZÓ
                    </div>
        
                    <div style="
                        height:32px;
                        text-align:center;
                    ">
                        @if(!empty($answers['firma_inspector']))
                            <img
                                src="{{ public_path('storage/' . $answers['firma_inspector']) }}"
                                style="
                                    width:120px;
                                    height:30px;
                                    object-fit:contain;
                                "
                            >
                        @endif
                    </div>
        
                    <div style="
                        width:100%;
                        margin-top:2px;
                        text-align:center;
                        font-size:7px;
                    ">
                        {{ $answers['nombre_inspector'] ?? '' }}
                    </div>
        
                    <div style="
                        margin-top:1px;
                        font-size:7px;
                        font-weight:bold;
                    ">
                        NOMBRE Y FIRMA
                    </div>
        
                </td>
        
                <!-- ESPACIO -->
                <td style="border:none;"></td>
        
                <!-- SUPERVISOR -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                    height:75px;
                ">
        
                    <div style="
                        font-weight:bold;
                        margin-bottom:2px;
                        font-size:9px;
                    ">
                        SUPERVISÓ
                    </div>
        
                    <div style="
                        height:32px;
                        text-align:center;
                    ">
                        @if(!empty($answers['firma_supervisor']))
                            <img
                                src="{{ public_path('storage/' . $answers['firma_supervisor']) }}"
                                style="
                                    width:120px;
                                    height:30px;
                                    object-fit:contain;
                                "
                            >
                        @endif
                    </div>
        
                    <div style="
                        width:100%;
                        margin-top:2px;
                        text-align:center;
                        font-size:7px;
                    ">
                        {{ $answers['nombre_supervisor'] ?? '' }}
                    </div>
        
                    <div style="
                        margin-top:1px;
                        font-size:7px;
                        font-weight:bold;
                    ">
                        NOMBRE Y FIRMA
                    </div>
        
                </td>
        
                <!-- ESPACIO FINAL -->
                <td style="border:none;"></td>
        
            </tr>
        
        </table>


        </div>
    @endforeach

</body>
</html>
