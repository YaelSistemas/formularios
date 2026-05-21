<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Unidades Móviles' }}</title>

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

        .right-cell {
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }

        .row-1-center {
            font-size: 10px;
        }

        .inspection-area {
            width: 92%;
            margin: 12px auto 0 auto;
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
            width: 150px;
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
    
        $placaValor = data_get($answers, 'placas', '') ?: '';
    
        $kilometrajeValor = data_get($answers, 'kilometraje', '') ?: '';
    
        $inspectorValor = data_get($answers, 'nombre_responsable_inspeccion', '') ?: '';
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
                    CODIFICACIÓN: SST-PGI-TA-02-FO-04
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
                    CHECKLIST DE UNIDADES MÓVILES
                </td>

                <td colspan="2" class="right-cell">
                    NÚMERO DE REVISIÓN: 06
                </td>
            </tr>
        </table>

        <!-- DATOS -->
        <div class="inspection-area">
        
            <!-- FILA 1 -->
            <div class="inspection-row">
        
                <!-- FECHA -->
                <div class="inspection-item left">
                    <span class="inspection-label">
                        Fecha:
                    </span>
        
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">
                            {{ $fechaInspeccion }}
                        </span>
        
                        <span class="inspection-underline"></span>
                    </span>
                </div>
        
                <!-- TALLER -->
                <div class="inspection-item right">
                    <span class="inspection-label">
                        Taller:
                    </span>
        
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">
                            {{ $tallerValor }}
                        </span>
        
                        <span class="inspection-underline"></span>
                    </span>
                </div>
        
            </div>
        
            <!-- FILA 2 -->
            <div class="inspection-row" style="margin-top: 5px;">
            
                <!-- PLACA -->
                <div style="
                    display:inline-block;
                    width:25%;
                    text-align:center;
                    vertical-align:middle;
                    font-size:10px;
                ">
                    <span class="inspection-label">
                        Placa:
                    </span>
            
                    <span class="inspection-line-wrap" style="width:80px;">
                        <span class="inspection-value">
                            {{ $placaValor }}
                        </span>
            
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            
                <!-- KILOMETRAJE -->
                <div style="
                    display:inline-block;
                    width:30%;
                    text-align:center;
                    vertical-align:middle;
                    font-size:10px;
                ">
                    <span class="inspection-label">
                        Kilometraje:
                    </span>
            
                    <span class="inspection-line-wrap" style="width:80px;">
                        <span class="inspection-value">
                            {{ $kilometrajeValor }}
                        </span>
            
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            
                <!-- INSPECTOR -->
                <div style="
                    display:inline-block;
                    width:35%;
                    text-align:center;
                    vertical-align:middle;
                    font-size:10px;
                ">
                    <span class="inspection-label">
                        Inspector:
                    </span>
            
                    <span class="inspection-line-wrap" style="width:150px;">
                        <span class="inspection-value">
                            {{ $inspectorValor }}
                        </span>
            
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- TABLA -->
        <table style="
            width:100%;
            margin-top:5px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <tr>
                <td style="
                    border:1px solid #000;
                    height:5px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#f3f4f6;
                ">
                    Marque una ( ✔ ) de acuerdo con la condición; no aplica (N/A)
                </td>
            </tr>
        </table>

        <!-- CONTENEDOR TABLAS -->
        <div style="
            width:100%;
            margin-top:5px;
            font-size:0;
        ">
        
            <!-- TABLA IZQUIERDA -->
            <div style="
                display:inline-block;
                width:60%;
                vertical-align:top;
            ">
            
                @php
                    $motorRows = [
                        ['label' => 'Cables de Bujías', 'value' => data_get($answers, 'cables_bujias', '')],
                        ['label' => 'Nivel de Anticongelante', 'value' => data_get($answers, 'nivel_anticongelante', '')],
                        ['label' => 'Nivel de Líquido para Frenos', 'value' => data_get($answers, 'nivel_liquido_frenos', '')],
                        ['label' => 'Nivel de Aceite para Motor', 'value' => data_get($answers, 'nivel_aceite_motor', '')],
                        ['label' => 'Nivel de Aceite para Transmisión', 'value' => data_get($answers, 'nivel_aceite_transmision', '')],
                        ['label' => 'Líquido Limpia Parabrisas', 'value' => data_get($answers, 'liquido_limpia_parabrisas', '')],
                    ];
                @endphp
            
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:7px;
                ">
                    <tr>
                        <td rowspan="2" colspan="2" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Motor
                        </td>
            
                        <td colspan="4" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            CONDICIÓN
                        </td>
                    </tr>
            
                    <tr>
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            BUENA
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            MALA
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            REPOSICIÓN
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            REPARACIÓN
                        </td>
                    </tr>
            
                    @foreach ($motorRows as $row)
                        <tr>
                            <td colspan="2" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:left;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $row['label'] }}
                            </td>
            
                            <td colspan="4" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:center;
                                vertical-align:middle;
                                font-size:8px;
                            ">
                                {{ $row['value'] }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        
            <!-- TABLA DERECHA -->
            <div style="
                display:inline-block;
                width:38%;
                vertical-align:top;
                margin-left:2%;
            ">
            
                @php
                    $observacionesMotorRows = [
                        data_get($answers, 'cables_bujias_observaciones', ''),
                        data_get($answers, 'nivel_anticongelante_observaciones', ''),
                        data_get($answers, 'nivel_liquido_frenos_observaciones', ''),
                        data_get($answers, 'nivel_aceite_motor_observaciones', ''),
                        data_get($answers, 'nivel_aceite_transmision_observaciones', ''),
                        data_get($answers, 'liquido_limpia_parabrisas_observaciones', ''),
                    ];
                @endphp
            
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                ">
            
                    <!-- FILA 1 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
            
                    <!-- FILA 2 SIN BORDES LATERALES -->
                    <tr>
                        <td style="
                            border-top:1px solid #000;
                            border-bottom:1px solid #000;
                            border-left:none;
                            border-right:none;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                        </td>
                    </tr>
            
                    <!-- FILAS 3 A 8 -->
                    @foreach ($observacionesMotorRows as $observacion)
                        <tr>
                            <td style="
                                border:1px solid #000;
                                height:16px;
                                text-align:center;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $observacion }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <!-- CONTENEDOR TABLAS CHASIS -->
        <div style="
            width:100%;
            margin-top:5px;
            font-size:0;
        ">
        
            <!-- TABLA IZQUIERDA -->
            <div style="
                display:inline-block;
                width:60%;
                vertical-align:top;
            ">
        
                @php
                    $chasisRows = [
                        ['label' => 'Golpes en Carrocería', 'value' => data_get($answers, 'golpes_carroceria', '')],
                        ['label' => 'Vidrios Estrellados', 'value' => data_get($answers, 'vidrios_estrellados', '')],
                        ['label' => 'Espejo Retrovisor', 'value' => data_get($answers, 'espejo_retrovisor', '')],
                        ['label' => 'Espejos Laterales', 'value' => data_get($answers, 'espejos_laterales', '')],
                        ['label' => 'Llantas del Vehículo', 'value' => data_get($answers, 'llantas_vehiculo', '')],
                        ['label' => 'Luces de la Unidad (Altas y Bajas)', 'value' => data_get($answers, 'luces_unidad_altas_bajas', '')],
                        ['label' => 'Intermitentes', 'value' => data_get($answers, 'intermitentes', '')],
                        ['label' => 'Direccionales', 'value' => data_get($answers, 'direccionales', '')],
                        ['label' => 'Foco de Reversa', 'value' => data_get($answers, 'foco_reversa', '')],
                        ['label' => 'Llanta Auxiliar', 'value' => data_get($answers, 'llanta_auxiliar', '')],
                        ['label' => 'Alarma de Reversa', 'value' => data_get($answers, 'alarma_reversa', '')],
                        ['label' => 'Torreta', 'value' => data_get($answers, 'torreta', '')],
                        ['label' => 'Calzas de Seguridad', 'value' => data_get($answers, 'calzas_seguridad', '')],
                        ['label' => 'Banderola', 'value' => data_get($answers, 'banderola', '')],
                    ];
                @endphp
        
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:7px;
                ">
        
                    <!-- FILA 1 -->
                    <tr>
                        <td rowspan="2" colspan="2" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Chasis
                        </td>
        
                        <td colspan="4" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            CONDICIÓN
                        </td>
                    </tr>
        
                    <!-- FILA 2 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            BUENA
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            MALA
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            REPOSICIÓN
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            REPARACIÓN
                        </td>
                    </tr>
        
                    <!-- FILAS -->
                    @foreach ($chasisRows as $row)
                        <tr>
                            <td colspan="2" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:left;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $row['label'] }}
                            </td>
        
                            <td colspan="4" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:center;
                                vertical-align:middle;
                                font-size:8px;
                            ">
                                {{ $row['value'] }}
                            </td>
                        </tr>
                    @endforeach
        
                </table>
        
            </div>
        
            <!-- TABLA DERECHA -->
            <div style="
                display:inline-block;
                width:38%;
                vertical-align:top;
                margin-left:2%;
            ">
        
                @php
                    $observacionesChasisRows = [
                        data_get($answers, 'golpes_carroceria_observaciones', ''),
                        data_get($answers, 'vidrios_estrellados_observaciones', ''),
                        data_get($answers, 'espejo_retrovisor_observaciones', ''),
                        data_get($answers, 'espejos_laterales_observaciones', ''),
                        data_get($answers, 'llantas_vehiculo_observaciones', ''),
                        data_get($answers, 'luces_unidad_altas_bajas_observaciones', ''),
                        data_get($answers, 'intermitentes_observaciones', ''),
                        data_get($answers, 'direccionales_observaciones', ''),
                        data_get($answers, 'foco_reversa_observaciones', ''),
                        data_get($answers, 'llanta_auxiliar_observaciones', ''),
                        data_get($answers, 'alarma_reversa_observaciones', ''),
                        data_get($answers, 'torreta_observaciones', ''),
                        data_get($answers, 'calzas_seguridad_observaciones', ''),
                        data_get($answers, 'banderola_observaciones', ''),
                    ];
                @endphp
        
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                ">
        
                    <!-- FILA 1 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
        
                    <!-- FILA 2 -->
                    <tr>
                        <td style="
                            border-top:1px solid #000;
                            border-bottom:1px solid #000;
                            border-left:none;
                            border-right:none;
                            height:14px;
                        ">
                        </td>
                    </tr>
        
                    <!-- FILAS -->
                    @foreach ($observacionesChasisRows as $observacion)
                        <tr>
                            <td style="
                                border:1px solid #000;
                                height:16px;
                                text-align:center;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $observacion }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <!-- CONTENEDOR TABLAS INTERIOR -->
        <div style="
            width:100%;
            margin-top:5px;
            font-size:0;
        ">
        
            <!-- TABLA IZQUIERDA -->
            <div style="
                display:inline-block;
                width:60%;
                vertical-align:top;
            ">
        
                @php
                    $interiorRows = [
                        ['label' => 'Vestiduras de Asientos', 'value' => data_get($answers, 'vestiduras_asientos', '')],
                        ['label' => 'Cinturones de Seguridad', 'value' => data_get($answers, 'cinturones_seguridad', '')],
                        ['label' => 'Tablero', 'value' => data_get($answers, 'tablero', '')],
                        ['label' => 'Herramienta para Retirar Refacción', 'value' => data_get($answers, 'herramienta_retirar_refaccion', '')],
                        ['label' => 'Gato Hidráulico', 'value' => data_get($answers, 'gato_hidraulico', '')],
                        ['label' => 'Triángulos de Precaución', 'value' => data_get($answers, 'triangulos_precaucion', '')],
                        ['label' => 'Cable para Pasar Corriente', 'value' => data_get($answers, 'cable_pasar_corriente', '')],
                        ['label' => 'Botiquín Móvil', 'value' => data_get($answers, 'botiquin_movil', '')],
                        ['label' => 'Cámara de Reversa', 'value' => data_get($answers, 'camara_reversa', '')],
                    ];
                @endphp
        
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:7px;
                ">
        
                    <!-- FILA 1 -->
                    <tr>
                        <td rowspan="2" colspan="2" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Interior
                        </td>
        
                        <td colspan="4" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            CONDICIÓN
                        </td>
                    </tr>
        
                    <!-- FILA 2 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            BUENA
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            MALA
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            REPOSICIÓN
                        </td>
        
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            REPARACIÓN
                        </td>
                    </tr>
        
                    <!-- FILAS -->
                    @foreach ($interiorRows as $row)
                        <tr>
                            <td colspan="2" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:left;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $row['label'] }}
                            </td>
        
                            <td colspan="4" style="
                                border:1px solid #000;
                                height:14px;
                                text-align:center;
                                vertical-align:middle;
                                font-size:8px;
                            ">
                                {{ $row['value'] }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- EXTINTOR -->
                    <tr>
                        <td rowspan="3" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Extintor
                        </td>
                    
                        <td colspan="5" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            CONDICIÓN
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Vigencia
                        </td>
                    
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Buena
                        </td>
                    
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Mala
                        </td>
                    
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Reposición
                        </td>
                    
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Reparación
                        </td>
                    </tr>
                    
                    <tr>
                        <!-- VIGENCIA -->
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ data_get($answers, 'vigencia_extintor', '') }}
                        </td>
                    
                        <!-- CONDICIÓN -->
                        <td colspan="4" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ data_get($answers, 'condicion_extintor', '') }}
                        </td>
                    </tr>
                </table>
            </div>
        
            <!-- TABLA DERECHA -->
            <div style="
                display:inline-block;
                width:38%;
                vertical-align:top;
                margin-left:2%;
            ">
        
                @php
                    $observacionesInteriorRows = [
                        data_get($answers, 'vestiduras_asientos_observaciones', ''),
                        data_get($answers, 'cinturones_seguridad_observaciones', ''),
                        data_get($answers, 'tablero_observaciones', ''),
                        data_get($answers, 'herramienta_retirar_refaccion_observaciones', ''),
                        data_get($answers, 'gato_hidraulico_observaciones', ''),
                        data_get($answers, 'triangulos_precaucion_observaciones', ''),
                        data_get($answers, 'cable_pasar_corriente_observaciones', ''),
                        data_get($answers, 'botiquin_movil_observaciones', ''),
                        data_get($answers, 'camara_reversa_observaciones', ''),
                    ];
                @endphp
        
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                ">
        
                    <!-- FILA 1 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
        
                    <!-- FILA 2 -->
                    <tr>
                        <td style="
                            border-top:1px solid #000;
                            border-bottom:1px solid #000;
                            border-left:none;
                            border-right:none;
                            height:14px;
                        ">
                        </td>
                    </tr>
        
                    <!-- FILAS -->
                    @foreach ($observacionesInteriorRows as $observacion)
                        <tr>
                            <td style="
                                border:1px solid #000;
                                height:16px;
                                text-align:center;
                                vertical-align:middle;
                                padding:0 4px;
                            ">
                                {{ $observacion }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- OBSERVACIONES EXTINTOR -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="
                            border-top:1px solid #000;
                            border-bottom:1px solid #000;
                            border-left:none;
                            border-right:none;
                            height:14px;
                        ">
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'condicion_extintor_observaciones', '') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- CONTENEDOR TABLAS NUEVO -->
        <div style="
            width:100%;
            margin-top:5px;
            font-size:0;
        ">
        
            <!-- TABLA IZQUIERDA -->
            <div style="
                display:inline-block;
                width:60%;
                vertical-align:top;
            ">
            
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:7px;
                ">
            
                    <!-- FILA 1 -->
                    <tr>
                        <td colspan="3" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Documentación
                        </td>
            
                        <td colspan="3" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Vigencia
                        </td>
                    </tr>
            
                    <!-- FILA 2 -->
                    <tr>
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:left; vertical-align:middle; padding:0 4px;">
                            Tarjeta de Circulación
                        </td>
            
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'vigencia_tarjeta_circulacion', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 3 -->
                    <tr>
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:left; vertical-align:middle; padding:0 4px;">
                            Licencia de Conducir
                        </td>
            
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'vigencia_licencia_conducir', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 4 -->
                    <tr>
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:left; vertical-align:middle; padding:0 4px;">
                            Tipo de Licencia
                        </td>
            
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'vigencia_tipo_licencia', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 5 VACÍA -->
                    <tr>
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:left; vertical-align:middle; padding:0 4px;">
                            Póliza de Seguro
                        </td>
            
                        <td colspan="3" style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'poliza_seguro', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 6 -->
                    <tr>
                        <td rowspan="3" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Tarjeta Efecticar
                        </td>
            
                        <td colspan="5" style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            CONDICIÓN
                        </td>
                    </tr>
            
                    <!-- FILA 7 -->
                    <tr>
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Vigencia
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Buena
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Mala
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Reposición
                        </td>
            
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle; font-weight:bold; background:#f3f4f6;">
                            Reparación
                        </td>
                    </tr>
            
                    <!-- FILA 8 -->
                    <tr>
                        <td style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'vigencia_tarjeta_efecticar', '') }}
                        </td>
            
                        <td colspan="4" style="border:1px solid #000; height:14px; text-align:center; vertical-align:middle;">
                            {{ data_get($answers, 'condicion_tarjeta_efecticar', '') }}
                        </td>
                    </tr>
                </table>
            </div>
        
            <!-- TABLA DERECHA -->
            <div style="
                display:inline-block;
                width:38%;
                vertical-align:top;
                margin-left:2%;
            ">
            
                <table style="
                    width:100%;
                    border-collapse:collapse;
                    table-layout:fixed;
                    font-size:8px;
                ">
            
                    <!-- FILA 1 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
            
                    <!-- FILA 2 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'vigencia_tarjeta_circulacion_observaciones', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 3 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'vigencia_licencia_conducir_observaciones', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 4 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'vigencia_tipo_licencia_observaciones', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 5 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'poliza_seguro_observaciones', '') }}
                        </td>
                    </tr>
            
                    <!-- FILA 6 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:14px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                            background:#f3f4f6;
                        ">
                            Observaciones
                        </td>
                    </tr>
            
                    <!-- FILA 7 -->
                    <tr>
                        <td style="
                            border-top:1px solid #000;
                            border-bottom:1px solid #000;
                            border-left:none;
                            border-right:none;
                            height:14px;
                        ">
                        </td>
                    </tr>
            
                    <!-- FILA 8 -->
                    <tr>
                        <td style="
                            border:1px solid #000;
                            height:16px;
                            text-align:center;
                            vertical-align:middle;
                            padding:0 4px;
                        ">
                            {{ data_get($answers, 'condicion_tarjeta_efecticar_observaciones', '') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- AVISO -->
        <table style="
            width:100%;
            margin-top:5px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <tr>
                <td style="
                    height:18px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#f3f4f6;
                    border:none;
                ">
                    "PROHIBIDO CONDUCIR CUALQUIER VEHÍCULO A EXCESO DE VELOCIDAD O BAJO LOS EFECTOS DEL ALCOHOL O ALGUNA DROGA"
                </td>
            </tr>
        </table>

        <!-- TABLA FINAL -->
        <table style="
            width:100%;
            margin-top:5px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <tr>
        
                <!-- NOTAS -->
                <td style="
                    border:1px solid #000;
                    height:40px;
                    width:10%;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    Notas:
                </td>
        
                <!-- DATO -->
                <td style="
                    border:1px solid #000;
                    height:40px;
                    width:90%;
                    text-align:center;
                    vertical-align:middle;
                    padding:0 6px;
                ">
                    {{ data_get($answers, 'notas', '') }}
                </td>
        
            </tr>
        </table>

    </div> <!-- Principal -->
</body>
</html>