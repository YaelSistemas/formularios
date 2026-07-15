<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist Inspección de Eslingas' }}</title>

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
         * Divide la tabla de eslingas en páginas de 10 registros.
         * Cada página se completa con filas vacías hasta llegar a 10.
         */
        $tablaEslingasOriginal = collect($answers['tabla_eslingas'] ?? [])
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

        // Aunque no existan registros, genera una página con 10 filas vacías.
        if (empty($tablaEslingasOriginal)) {
            $tablaEslingasOriginal = [[]];
        }

        $paginasEslingas = array_chunk($tablaEslingasOriginal, 10);

        $paginasEslingas = array_map(function ($pagina) {
            while (count($pagina) < 10) {
                $pagina[] = [];
            }

            return $pagina;
        }, $paginasEslingas);

        $totalPaginas = count($paginasEslingas);
    @endphp

    @foreach($paginasEslingas as $pageIndex => $tablaEslingas)
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
                <!-- LOGO: USA LAS 4 FILAS -->
                <td rowspan="4" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>
        
                <!-- EMPRESA -->
                <td class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>
        
                <!-- CÓDIGO -->
                <td class="right-cell">
                    CÓDIGO: SGI-POP-GT-01-FO-10
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
                <!-- TÍTULO: USA LAS FILAS DE REVISIÓN Y PÁGINA -->
                <td rowspan="2" class="center-cell">
                    CHECKLIST DE INSPECCIÓN DE ESLINGAS
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
                <td style="width:15%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:20%; padding:0; border:none; height:0;"></td>
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
                        TALLER:
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

        @php
            $eslingaIzqSrc = null;
            $eslingaDerSrc = null;
        
            $eslingaIzqPath = public_path(
                'images/forms/SGI_POP_GT_01_FO_10_Checklist_Inspeccion_de_Eslingas/eslinga_izq.png'
            );
        
            $eslingaDerPath = public_path(
                'images/forms/SGI_POP_GT_01_FO_10_Checklist_Inspeccion_de_Eslingas/eslinga_der.png'
            );
        
            if (file_exists($eslingaIzqPath)) {
                $eslingaIzqSrc =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($eslingaIzqPath));
            }
        
            if (file_exists($eslingaDerPath)) {
                $eslingaDerSrc =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($eslingaDerPath));
            }
        @endphp
        
        <!-- IMÁGENES ESLINGAS -->
        <table style="
            width:100%;
            margin-top:15px;
            border-collapse:collapse;
        ">
            <tr>
        
                <!-- IZQUIERDA -->
                <td style="
                    width:50%;
                    text-align:center;
                    vertical-align:top;
                    border:none;
                ">
                    @if($eslingaIzqSrc)
                        <img
                            src="{{ $eslingaIzqSrc }}"
                            style="
                                width:100px;
                                height:50px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
        
                <!-- DERECHA -->
                <td style="
                    width:50%;
                    text-align:center;
                    vertical-align:top;
                    border:none;
                ">
                    @if($eslingaDerSrc)
                        <img
                            src="{{ $eslingaDerSrc }}"
                            style="
                                width:100px;
                                height:50px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
            </tr>
        </table>

        <!-- INDICACIÓN -->
        <div style="
            width:100%;
            margin-top:0px;
            text-align:center;
            font-size:6px;
            line-height:1.3;
            font-weight:bold;
        ">
            CONTESTA SEGÚN APLIQUE AL ESTADO FÍSICO DEL EQUIPO:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="font-weight:bold;">( ✔︎ )</span>
            BUENAS CONDICIONES
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span style="font-weight:bold;">( X )</span>
            EN MALAS CONDICIONES
        </div>


        
        <!-- TABLA ESLINGAS -->
        <table style="
            width:100%;
            margin-top:8px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:6px;
        ">
            <!-- ANCHOS COLUMNAS -->
            <tr style="height:0; line-height:0;">
                <td style="width:8%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:11%; padding:0;"></td>
                <td style="width:15%; padding:0;"></td>
            </tr>
        
            <!-- FILA 1: HEADERS -->
            <tr>
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    N° de serie de eslinga
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Se almacenan eslingas en ambiente seco, ventilado y protegido contra lluvia o medios agresivos que pueda dañarla
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Se observan roturas de monofilamentos, hilos cortados o gastados sobre todo en los cantos, (ojo - cuerpo)
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Etiquetas son legibles y contienen la información de carga máxima, código de trazabilidad, largo y ancho
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Presentan desgaste y/o abrasión
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Se han realizado nudos en las eslingas
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Los accesorios que usan las eslingas en sus ojos (grilletes, ganchos, cáncamos, etc.)
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Presentan quemaduras por soldadura, exposición al sol u otro factor similar
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; text-align:center; vertical-align:middle; padding:3px 2px; height:25px;">
                    Observaciones
                </td>
            </tr>
        
            <!-- FILAS 2 A 14 -->
            @foreach($tablaEslingas as $row)
                <tr>
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['numero_serie_eslinga'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['almacenamiento_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['roturas_monofilamentos_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['etiquetas_legibles_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['desgaste_abrasion_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['nudos_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['accesorios_ojos_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['quemaduras_estado'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; padding:3px 2px; text-align:center; vertical-align:middle; height:15px;">
                        {{ $row['observaciones'] ?? '' }}
                    </td>
                </tr>
            @endforeach
        </table>

        @php
            $firmaInspecciona = $answers['firma_colaborador_inspecciona'] ?? null;
            $firmaSupervisor = $answers['firma_supervisor'] ?? null;
        
            $firmaInspeccionaSrc = null;
            $firmaSupervisorSrc = null;
        
            if ($firmaInspecciona) {
                $firmaPath = storage_path('app/public/' . $firmaInspecciona);
        
                if (file_exists($firmaPath)) {
                    $firmaInspeccionaSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($firmaPath));
                }
            }
        
            if ($firmaSupervisor) {
                $firmaPath = storage_path('app/public/' . $firmaSupervisor);
        
                if (file_exists($firmaPath)) {
                    $firmaSupervisorSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($firmaPath));
                }
            }
        @endphp

        <!-- FIRMAS -->
        <table style="
            width:100%;
            margin-top:12px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:7px;
        ">
            <tr>
                <!-- REALIZÓ -->
                <td style="
                    width:45%;
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    height:10px;
                ">
                    REALIZÓ
                </td>
        
                <td style="width:10%; border:none;"></td>
        
                <!-- SUPERVISOR -->
                <td style="
                    width:45%;
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    height:10px;
                ">
                    REVISÓ
                </td>
            </tr>
        
            <tr>
                <!-- NOMBRE INSPECCIONA -->
                <td style="
                    border:1px solid #000;
                    padding:4px;
                    height:15px;
                ">
                    <strong>NOMBRE:</strong>
                    {{ $answers['nombre_colaborador_inspecciona'] ?? '' }}
                </td>
        
                <td style="border:none;"></td>
        
                <!-- NOMBRE SUPERVISOR -->
                <td style="
                    border:1px solid #000;
                    padding:4px;
                    height:15px;
                ">
                    <strong>SUPERVISOR:</strong>
                    {{ $answers['nombre_supervisor'] ?? '' }}
                </td>
            </tr>
        
            <tr>
                <!-- FIRMA INSPECCIONA -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:70px;
                ">
                    @if($firmaInspeccionaSrc)
                        <img
                            src="{{ $firmaInspeccionaSrc }}"
                            style="
                                max-width:180px;
                                max-height:55px;
                            "
                        >
                    @endif
                </td>
        
                <td style="border:none;"></td>
        
                <!-- FIRMA SUPERVISOR -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:70px;
                ">
                    @if($firmaSupervisorSrc)
                        <img
                            src="{{ $firmaSupervisorSrc }}"
                            style="
                                max-width:180px;
                                max-height:55px;
                            "
                        >
                    @endif
                </td>
            </tr>
        </table>

        </div>
    @endforeach
</body>
</html>
