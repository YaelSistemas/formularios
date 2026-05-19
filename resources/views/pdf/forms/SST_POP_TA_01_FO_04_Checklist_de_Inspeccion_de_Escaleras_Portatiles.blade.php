<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Inspección de Escaleras Portátiles' }}</title>

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
            margin: 22px 0 0 -75px;
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
            margin-left: -10%;
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
        $nombreInspector = data_get($answers, 'nombre_inspector', '') ?: '';

        $firmaInspector = data_get($answers, 'firma_inspector');

        $firmaInspectorSrc = null;
        
        if ($firmaInspector) {
            $pathFirma = storage_path('app/public/' . $firmaInspector);
        
            if (file_exists($pathFirma)) {
                $firmaInspectorSrc =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($pathFirma));
            }
        }

        $imagenEscalera = public_path(
            'images/forms/SST_POP_TA_01_FO_04_Checklist_de_Inspeccion_de_Escaleras_Portatiles/Inspeccion_Escalera_Portatil.png'
        );
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
                    CODIFICACIÓN: SST-POP-TA-01-FO-04
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
                    CHECKLIST DE INSPECCIÓN DE ESCALERAS PORTÁTILES
                </td>

                <td colspan="2" class="right-cell">
                    NÚMERO DE REVISIÓN: 03
                </td>
            </tr>
        </table>

        <!-- DATOS + IMAGEN -->
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            margin-top:15px;
        ">
            <tr>
        
                <!-- DATOS -->
                <td style="
                    width:65%;
                    vertical-align:top;
                ">
        
                    <div class="inspection-area">
        
                        <!-- FILA 1 -->
                        <div class="inspection-row">
        
                            <!-- FECHA -->
                            <div class="inspection-item left" style="text-align:left;">
                                <span class="inspection-label">Fecha:</span>
        
                                <span class="inspection-line-wrap">
                                    <span class="inspection-value">
                                        {{ $fechaInspeccion }}
                                    </span>
        
                                    <span class="inspection-underline"></span>
                                </span>
                            </div>
        
                            <!-- INSPECTOR -->
                            <div class="inspection-item right" style="text-align:left;">
                                <span class="inspection-label">Inspector:</span>
        
                                <span class="inspection-line-wrap" style="width:200px;">
                                    <span class="inspection-value">
                                        {{ $nombreInspector }}
                                    </span>
        
                                    <span class="inspection-underline"></span>
                                </span>
                            </div>
                        </div>
        
                        <!-- FILA 2 -->
                        <div class="inspection-row" style="margin-top:100px;">
        
                            <!-- TALLER -->
                            <div class="inspection-item left" style="text-align:left;">
                                <span class="inspection-label">Taller:</span>
        
                                <span class="inspection-line-wrap">
                                    <span class="inspection-value">
                                        {{ $tallerValor }}
                                    </span>
        
                                    <span class="inspection-underline"></span>
                                </span>
                            </div>
        
                            <!-- FIRMA -->
                            <div class="inspection-item right" style="text-align:left;">
                                <span class="inspection-label">Firma:</span>
        
                                <span class="inspection-line-wrap" style="height:20px; width:220px;">
        
                                    @if($firmaInspectorSrc)
                                        <img
                                            src="{{ $firmaInspectorSrc }}"
                                            style="
                                                position:absolute;
                                                left:0;
                                                right:0;
                                                bottom:0px;
                                                margin:auto;
                                                max-width:120px;
                                                max-height:35px;
                                                object-fit:contain;
                                            "
                                        >
                                    @endif
                                    <span class="inspection-underline"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
        
                <!-- IMAGEN -->
                <td style="
                    width:35%;
                    text-align:center;
                    vertical-align:top;
                ">
                    <table style="
                        width:180px;
                        margin:0 0 0 -20px;;
                        border-collapse:collapse;
                        table-layout:fixed;
                    ">

                        <!-- IMAGEN -->
                        <tr>
                            <td style="
                                border:none;
                                text-align:center;
                                padding:8px 4px;
                            ">
                                @if(file_exists($imagenEscalera))
                                    <img
                                        src="{{ $imagenEscalera }}"
                                        style="
                                            width:500px;
                                            height:150px;
                                            object-fit:contain;
                                            display:block;
                                            margin:0 auto;
                                        "
                                    >
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- TABLA CRITERIOS ESCALERAS -->
        <table style="
            width: 99.6%;
            margin: 10px 0 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 5.5px;
        ">
            <colgroup>
                @for ($i = 1; $i <= 32; $i++)
                    <col style="width: 3.125%;">
                @endfor
            </colgroup>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="4" style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                ">
                    Tipo de Escalera:
                </td>
        
                <td colspan="28" style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                    font-size:7px;
                ">
                    Descripción
                </td>
            </tr>
        
            <!-- FILA 2 (Criterios principales) -->
            <tr>
                <td rowspan="2" colspan="2" style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                    line-height:1.25;
                    font-weight:bold;
                    font-size:6px;
                ">
                    Fija, Tijera,<br>
                    Extensión, <br>
                    Doble,Otro.
                </td>
                
                <td rowspan="2" colspan="2" style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px 2px;
                    line-height:1.25;
                    font-weight:bold;
                    font-size:6px;
                ">
                    Número de Identificación de la Escalera
                </td>
        
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Zapatas/Patas:<br>Gastado, Suelto, Rajado o Faltante</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Rieles/Planos Verticales:<br>Bordes Afilados, Rajados o Doblados</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Escalones/Peldaños:<br>Sueltos, Roto, Gastado o Faltante</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Tope Superior:<br>Rajado, Suelto o Faltante</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Ferretería:<br>Difícil de Operar</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Limpieza:<br>Materiales Grasos, Aceitosos o Resbaladizos</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">General:<br>Partes Oxidadas, Corroídas, Rajadas, Sueltas o Faltantes</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Etiquetas:<br>Faltante o No Legible</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Seguros de Peldaños:<br>Suelto, Roto o Faltante</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">La Escalera está Libre de Grietas</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Cuerda/Polea:<br>Gastado, Raído o Faltante</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Brazos de Unión en Buenas Condiciones</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Seguros en Buenas Condiciones</td>
                <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:4px 2px; font-weight:bold; font-size:6px;">Polea en Buenas Condiciones</td>
            </tr>
        
            <!-- FILA 3 (Subtítulos con alto expandido emulando 3 filas) -->
            <tr>
                @for ($i = 0; $i < 14; $i++)
                    <td style="
                        border: 1px solid #000; 
                        background:#f3f4f6;
                        text-align: center; 
                        vertical-align: middle; 
                        padding: 2px; 
                        font-weight: bold;
                        height: 45px;
                        line-height: 1.1;
                        font-size:5px;
                    ">
                        Necesita<br>Reparación
                    </td>
                    <td style="
                        border: 1px solid #000; 
                        text-align: center; 
                        vertical-align: middle; 
                        padding: 2px; 
                        font-weight: bold;
                        height: 45px;
                        line-height: 1.1;
                        font-size:5px;
                    ">
                        Buen<br>Estado
                    </td>
                @endfor
            </tr>
        
            <!-- FILAS DE DATOS DENTRO DEL LOOP -->
            @php
                $rows = data_get($answers, 'tabla_escaleras_portatiles', []);
            
                $filasConDatos = collect($rows)->filter(function ($row) {
                    return !empty(array_filter(
                        $row,
                        fn($value) => $value !== null && $value !== ''
                    ));
                })->values();
            
                $minFilas = 8;
                $totalFilas = $filasConDatos->count() > 8 ? $filasConDatos->count() : 8;
            @endphp
            
            @for ($i = 0; $i < $totalFilas; $i++)
                @php
                    $row = $filasConDatos[$i] ?? [];
                @endphp
            
                <tr>
                    <!-- TIPO DE ESCALERA -->
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'tipo_escalera') }}
                    </td>
            
                    <!-- NÚMERO IDENTIFICACIÓN -->
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'numero_identificacion_escalera') }}
                    </td>
            
                    <!-- RESPUESTAS POR CRITERIO (28 columnas restantes) -->
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'zapatas_patas_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'rieles_planos_verticales_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'escalones_peldanos_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'tope_superior_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'ferreteria_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'limpieza_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'general_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'etiquetas_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'seguros_peldanos_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'escalera_libre_grietas_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'cuerda_polea_estado') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'brazos_union_buenas_condiciones') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'seguros_buenas_condiciones') }}
                    </td>
            
                    <td colspan="2" style="border:1px solid #000; height:22px; text-align:center; vertical-align:middle; padding:3px 2px;">
                        {{ data_get($row, 'polea_buenas_condiciones') }}
                    </td>
                </tr>
            @endfor
        </table>

        <!-- TABLA OBSERVACIONES --> 
        <table style="
            width:99.6%;
            margin-top:15px;
            border-collapse:collapse;
            table-layout:fixed;
        ">
            <colgroup>
                @for ($i = 1; $i <= 32; $i++)
                    <col style="width:3.125%;">
                @endfor
            </colgroup>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="32" style="
                    border-top:1px solid #000;
                    border-left:1px solid #000;
                    border-right:1px solid #000;
                    border-bottom:none;
                    height:20px;
                    vertical-align:middle;
                    font-weight:bold;
                    font-size:9px;
                ">
                    <div style="
                        position:relative;
                        left: 5px;
                    ">
                        Observaciones
                    </div>
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td colspan="32" style="
                    border-left:1px solid #000;
                    border-right:1px solid #000;
                    border-bottom:1px solid #000;
                    border-top:none;
                    height:25px;
                    text-align:left;
                    vertical-align:middle;
                    padding:6px 8px;
                    font-size:8px;
                    white-space:pre-line;
                ">
                    {{ data_get($answers, 'observaciones') }}
                </td>
            </tr>
        
        </table>

    </div>
</body>
</html>