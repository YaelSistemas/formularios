<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Tirfor' }}</title>

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

        .inspection-area {
            width: 92%;
            margin: 22px auto 0 auto;
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

        $numeroIdentificacion = data_get($answers, 'numero_identificacion', '') ?: '';
        $capacidadTirfor = data_get($answers, 'capacidad_tirfor', '') ?: '';
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
                    CÓDIGO: SST-POP-TA-01-FO-08
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    EMISIÓN: 13/06/2025
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    CHECKLIST DE TIRFOR
                </td>

                <td class="right-cell">
                    REVISIÓN: 00
                </td>
            </tr>
        </table>

        <!-- DATOS -->
        <table style="
            width: 100%;
            margin: 16px 0 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;
        ">
            <tr>
                <td style="
                    width: 12%;
                    border:1px solid #000;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:1px 4px;
                ">
                    Fecha:
                </td>
        
                <td style="
                    width: 28%;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:1px 4px;
                ">
                    {{ $fechaInspeccion }}
                </td>
        
                <td style="
                    width: 20%;
                    border:none;
                "></td>
        
                <td style="
                    width: 12%;
                    border:1px solid #000;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:1px 4px;
                ">
                    Taller / US:
                </td>
        
                <td style="
                    width: 28%;
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:1px 4px;
                ">
                    {{ $tallerValor }}
                </td>
            </tr>
        </table>

        <!-- GUÍA VISUAL -->
        <div style="
            width: 35%;
            margin-top: 30px;
            float: right;
            margin-right: 123px;
        ">
            
            <!-- TÍTULO -->
            <div style="
                text-align:center;
                font-weight:bold;
                font-size:10px;
            ">
                Guía Visual
            </div>
        
            <!-- LÍNEA -->
            <div style="
                border-bottom:1px solid #000;
                margin-top:0px;
                width:100%;
            "></div>

            <!-- IMAGEN -->
            <div style="
                text-align:center;
                margin-top:12px;
            ">
                <img
                    src="{{ public_path('images/forms/SST_POP_TA_01_FO_08_Checklist_de_Tirfor/Especs_Tirfor.png') }}"
                    style="
                        width: 320px;
                        height: 180px;
                        object-fit: contain;
                    "
                >
            </div>
        </div>

        <!-- NO. IDENTIFICACIÓN -->
        <div style="
            width: 30%;
            margin-top: 30px;
        ">
            
            <!-- TÍTULO -->
            <table style="
                width:100%;
                border-collapse:collapse;
                table-layout:fixed;
                font-size:10px;
            ">
                <tr>
                    <td style="
                        border:1px solid #000;
                        text-align:center;
                        font-weight:bold;
                        padding:2px 2px;
                        background:#f3f4f6;
                    ">
                        NO. DE IDENTIFICACIÓN:
                    </td>
                </tr>
            </table>
        
            <!-- DATO -->
            <div style="
                width:100%;
                text-align:center;
                margin-top:14px;
                font-size:10px;
            ">
                {{ $numeroIdentificacion }}
        
                <div style="
                    border-bottom:1px solid #000;
                    margin-top:2px;
                    width:100%;
                "></div>
            </div>
        </div>

        <!-- CAPACIDAD DE TIRFOR -->
        <div style="
            width: 30%;
            margin-top: 90px;
        ">
            
            <!-- TÍTULO -->
            <table style="
                width:100%;
                border-collapse:collapse;
                table-layout:fixed;
                font-size:10px;
            ">
                <tr>
                    <td style="
                        border:1px solid #000;
                        text-align:center;
                        font-weight:bold;
                        padding:2px 2px;
                        background:#f3f4f6;
                    ">
                        CAPACIDAD DE TIRFOR:
                    </td>
                </tr>
            </table>
        
            <!-- DATO -->
            <div style="
                width:100%;
                text-align:center;
                margin-top:14px;
                font-size:10px;
            ">
                {{ $capacidadTirfor }}
        
                <div style="
                    border-bottom:1px solid #000;
                    margin-top:2px;
                    width:100%;
                "></div>
            </div>
        </div>

        <!-- TEXTO INDICACIONES -->
        <div style="
            width:100%;
            text-align:center;
            font-weight:bold;
            font-size:9px;
            margin-top:15px;
        ">
            Considerar los siguientes criterios de acuerdo a las condiciones del tirfor
        </div>

        @php
            $rowsTirfor = data_get($answers, 'tabla_tirfor', []);
        
            $rowTirfor = collect($rowsTirfor)->first(function ($item) {
                return !empty(array_filter($item, fn($value) => $value !== null && $value !== ''));
            }) ?? [];
        
            $criteriosTirfor = [
                ['n' => 1,  'label' => 'GANCHO', 'estado' => 'tirfor_1_estado',  'obs' => 'tirfor_1_observaciones'],
                ['n' => 2,  'label' => 'PESTILLO DE SEGURIDAD', 'estado' => 'tirfor_2_estado',  'obs' => 'tirfor_2_observaciones'],
                ['n' => 3,  'label' => 'PALANCA TELESCÓPICA', 'estado' => 'tirfor_3_estado',  'obs' => 'tirfor_3_observaciones'],
                ['n' => 4,  'label' => 'PALANCA DE DESTRABE', 'estado' => 'tirfor_4_estado',  'obs' => 'tirfor_4_observaciones'],
                ['n' => 5,  'label' => 'CUBIERTA', 'estado' => 'tirfor_5_estado',  'obs' => 'tirfor_5_observaciones'],
                ['n' => 6,  'label' => 'MANIJA DE TRANSPORTE', 'estado' => 'tirfor_6_estado',  'obs' => 'tirfor_6_observaciones'],
                ['n' => 7,  'label' => 'PALANCA DE TRACCIÓN', 'estado' => 'tirfor_7_estado',  'obs' => 'tirfor_7_observaciones'],
                ['n' => 8,  'label' => 'GUÍA DE CABLE', 'estado' => 'tirfor_8_estado',  'obs' => 'tirfor_8_observaciones'],
                ['n' => 9,  'label' => 'CABLE DE ACERO', 'estado' => 'tirfor_9_estado',  'obs' => 'tirfor_9_observaciones'],
                ['n' => 10, 'label' => 'PIN DE SEGURIDAD', 'estado' => 'tirfor_10_estado', 'obs' => 'tirfor_10_observaciones'],
                ['n' => 11, 'label' => 'PLACA DE IDENTIFICACIÓN', 'estado' => 'tirfor_11_estado', 'obs' => 'tirfor_11_observaciones'],
            ];
        @endphp
        
        <table style="
            width:100%;
            margin:10px 0 0 0;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <colgroup>
                <col style="width:5%">
                <col style="width:10%">
                <col style="width:20%">
                <col style="width:10%">
                <col style="width:9%">
                <col style="width:10%">
                <col style="width:16%">
                <col style="width:20%">
            </colgroup>
        
            <tr>
                <td colspan="3" rowspan="2" style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    CRITERIOS DE INSPECCIÓN
                </td>
        
                <td colspan="3" style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    CONDICIÓN
                </td>
        
                <td colspan="2" rowspan="2" style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    OBSERVACIONES
                </td>
            </tr>
        
            <tr>
                <td style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    BUEN ESTADO
                </td>
        
                <td style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    DAÑADO
                </td>
        
                <td style="border:1px solid #000; background:#f3f4f6; font-weight:bold; text-align:center; vertical-align:middle;">
                    NO APLICA
                </td>
            </tr>
        
            @foreach ($criteriosTirfor as $criterio)
                <tr>
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $criterio['n'] }}
                    </td>
        
                    <td colspan="2" style="border:1px solid #000; text-align:left; vertical-align:middle; padding:2px 4px;">
                        {{ $criterio['label'] }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ data_get($rowTirfor, $criterio['estado']) === 'Buen estado' ? '✔︎' : '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ data_get($rowTirfor, $criterio['estado']) === 'Dañado' ? 'X' : '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ data_get($rowTirfor, $criterio['estado']) === 'No Aplica' ? 'NA' : '' }}
                    </td>
        
                    <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px 4px;">
                        {{ data_get($rowTirfor, $criterio['obs'], '') }}
                    </td>
                </tr>
            @endforeach
        </table>

        @php
            $nombreInspector = data_get($answers, 'nombre_trabajador_elabora_checklist', '');
        
            $firmaInspector = data_get($answers, 'firma_trabajador_elabora_checklist', '');
        
            $firmaInspectorSrc = null;
        
            if ($firmaInspector) {
                $firmaPath = storage_path('app/public/' . $firmaInspector);
        
                if (file_exists($firmaPath)) {
                    $firmaInspectorSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($firmaPath));
                }
            }
        @endphp
        
        <!-- TABLA FIRMA -->
        <table style="
            width:40%;
            margin:25px auto 0 auto;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:9px;
        ">
        
            <!-- FIRMA -->
            <tr>
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:130px;
                ">
                    @if($firmaInspectorSrc)
                        <img
                            src="{{ $firmaInspectorSrc }}"
                            style="
                                width:150px;
                                height:60px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
            </tr>
        
            <!-- NOMBRE -->
            <tr>
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    height:18px;
                    font-size:8px;
                ">
                    {{ $nombreInspector }}
                </td>
            </tr>
        
            <!-- TEXTO FIJO -->
            <tr>
                <td style="
                    border:1px solid #000;
                    background:#f3f4f6;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    height:20px;
                    font-size:8px;
                ">
                    Nombre y Firma del Inspector
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
