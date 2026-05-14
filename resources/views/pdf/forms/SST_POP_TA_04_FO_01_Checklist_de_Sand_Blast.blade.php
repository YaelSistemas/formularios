<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Sand Blast' }}</title>

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

        $nombreInspecciona = data_get($answers, 'nombre_inspecciona', '');
        $nombreSupervisa = data_get($answers, 'nombre_supervisa', '');

        $firmaInspecciona = data_get($answers, 'firma_inspecciona', '');
        $firmaSupervisa = data_get($answers, 'firma_supervisa', '');
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
                    CODIFICACIÓN: SST-POP-TA-04-FO-01
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
                    CHECKLIST DE SAND BLAST
                </td>

                <td colspan="2" class="right-cell">
                    NÚMERO DE REVISIÓN: 04
                </td>
            </tr>
        </table>

        <!-- DATOS -->
        <div class="inspection-area">
            <div class="inspection-row">
                <div class="inspection-item left">
                    <span class="inspection-label">Fecha de inspección:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $fechaInspeccion }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>

                <div class="inspection-item right">
                    <span class="inspection-label">Taller:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $tallerValor }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- TEXTO INDICACIONES -->
        <div style="
            text-align: center;
            margin-top: 14px;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
        ">
            Este checklist deberá llenarse cada que se use el equipo y en caso de no usarse llenarse una vez al mes.
        </div>

        <!-- TABLA SAND BLAST -->
        @php
            $rows = data_get($answers, 'tabla_sand_blast', []);
        
            $row = collect($rows)->first(function ($item) {
                return !empty(array_filter($item, fn($value) => $value !== null && $value !== ''));
            }) ?? [];
        
            $criteriosSandBlast = [
                ['n' => 1,  'label' => 'Tanque Receptor de Abrasivo', 'estado' => 'sand_blast_1_estado',  'obs' => 'sand_blast_1_observaciones'],
                ['n' => 2,  'label' => 'Trampa Tortuga', 'estado' => 'sand_blast_2_estado',  'obs' => 'sand_blast_2_observaciones'],
                ['n' => 3,  'label' => 'Maneral, Patas y Ruedas', 'estado' => 'sand_blast_3_estado',  'obs' => 'sand_blast_3_observaciones'],
                ['n' => 4,  'label' => 'Trampa Humedad', 'estado' => 'sand_blast_4_estado',  'obs' => 'sand_blast_4_observaciones'],
                ['n' => 5,  'label' => 'Manómetro', 'estado' => 'sand_blast_5_estado',  'obs' => 'sand_blast_5_observaciones'],
                ['n' => 6,  'label' => 'Purga de Humedad', 'estado' => 'sand_blast_6_estado',  'obs' => 'sand_blast_6_observaciones'],
                ['n' => 7,  'label' => 'Válvula para Desfogue con Silenciador', 'estado' => 'sand_blast_7_estado',  'obs' => 'sand_blast_7_observaciones'],
                ['n' => 8,  'label' => 'Oring', 'estado' => 'sand_blast_8_estado',  'obs' => 'sand_blast_8_observaciones'],
                ['n' => 9,  'label' => 'Válvula Cónica', 'estado' => 'sand_blast_9_estado',  'obs' => 'sand_blast_9_observaciones'],
                ['n' => 10, 'label' => 'Válvula Mezcladora', 'estado' => 'sand_blast_10_estado', 'obs' => 'sand_blast_10_observaciones'],
                ['n' => 11, 'label' => 'Empaque para Válvula Mezcladora', 'estado' => 'sand_blast_11_estado', 'obs' => 'sand_blast_11_observaciones'],
                ['n' => 12, 'label' => 'Conector Garra para Válvula Mezcladora', 'estado' => 'sand_blast_12_estado', 'obs' => 'sand_blast_12_observaciones'],
                ['n' => 13, 'label' => 'Manguera paso de Aire', 'estado' => 'sand_blast_13_estado', 'obs' => 'sand_blast_13_observaciones'],
                ['n' => 14, 'label' => 'Conectores de Aire', 'estado' => 'sand_blast_14_estado', 'obs' => 'sand_blast_14_observaciones'],
                ['n' => 15, 'label' => 'Válvula paso de Aire a Abrasivo', 'estado' => 'sand_blast_15_estado', 'obs' => 'sand_blast_15_observaciones'],
                ['n' => 16, 'label' => 'Conector Garra Manguera Abrasivo', 'estado' => 'sand_blast_16_estado', 'obs' => 'sand_blast_16_observaciones'],
                ['n' => 17, 'label' => 'Manguera de Abrasivo', 'estado' => 'sand_blast_17_estado', 'obs' => 'sand_blast_17_observaciones'],
                ['n' => 18, 'label' => 'Porta Boquilla', 'estado' => 'sand_blast_18_estado', 'obs' => 'sand_blast_18_observaciones'],
                ['n' => 19, 'label' => 'Boquilla', 'estado' => 'sand_blast_19_estado', 'obs' => 'sand_blast_19_observaciones'],
            ];
        @endphp
        
        <table style="
            width: 95%;
            margin: 18px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        ">
            <colgroup>
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 13%">
                <col style="width: 13%">
                <col style="width: 13%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 10%">
                <col style="width: 10%">
            </colgroup>
        
            <tr>
                <td rowspan="2" colspan="4" style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center;">
                    Guía de Inspección
                </td>
        
                <td rowspan="2" colspan="3" style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center;">
                    Criterios
                </td>
        
                <td colspan="2" style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center;">
                    Estado
                </td>
        
                <td rowspan="2" colspan="2" style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center;">
                    Observaciones
                </td>
            </tr>
        
            <tr>
                <td style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center; font-size:8px;">
                    Bueno
                </td>
        
                <td style="border:1px solid #000; font-weight:bold; background:#f3f4f6; text-align:center; font-size:8px;">
                    Malo
                </td>
            </tr>
        
            @foreach ($criteriosSandBlast as $index => $criterio)
                <tr>
                    @if ($index === 0)
                        <td rowspan="19" colspan="4" style="border:1px solid #000; text-align:center; vertical-align:middle;">
                            <img
                                src="{{ public_path('images/forms/SST_POP_TA_04_FO_01_Checklist_de_Sand_Blast/SandBlast.png') }}"
                                style="
                                    width: 150px;
                                    height: 300px;
                                    object-fit: contain;
                                    display: block;
                                    margin: 0 auto;
                                "
                            >
                        </td>
                    @endif
        
                    <td colspan="3" style="border:1px solid #000; padding:3px 5px; vertical-align:middle;">
                        {{ $criterio['n'] }}. {{ $criterio['label'] }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-size:11px;">
                        {{ data_get($row, $criterio['estado']) === 'Buen estado' ? '✓' : '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-size:11px;">
                        {{ data_get($row, $criterio['estado']) === 'Mal estado' ? 'x' : '' }}
                    </td>
        
                    <td colspan="2" style="border:1px solid #000; padding:3px 5px; vertical-align: middle; text-align: center;">
                        {{ data_get($row, $criterio['obs'], '') }}
                    </td>
                </tr>
            @endforeach
        </table>

        <!-- TABLA FIRMAS -->
        <table style="
            width: 95%;
            margin: 25px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;
        ">
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    padding:1px;
                ">
                    Nombre de quien Inspecciona
                </td>
        
                <td colspan="5" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    padding:1px;
                ">
                    Nombre de quien Supervisa
                </td>
            </tr>
        
            <!-- FILA 2 (NOMBRES) -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:25px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $nombreInspecciona }}
                </td>
            
                <td colspan="5" style="
                    border:1px solid #000;
                    height:25px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $nombreSupervisa }}
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    padding:1px;
                ">
                    Firma de quien Inspecciona
                </td>
        
                <td colspan="5" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                    padding:1px;
                ">
                    Firma de quien Supervisa
                </td>
            </tr>

            <!-- FILA 4 (ESPACIO PARA FIRMAS) -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:100px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    @if(!empty($firmaInspecciona))
                        <img
                            src="{{ public_path('storage/' . $firmaInspecciona) }}"
                            style="
                                max-width:180px;
                                max-height:85px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
            
                <td colspan="5" style="
                    border:1px solid #000;
                    height:100px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    @if(!empty($firmaSupervisa))
                        <img
                            src="{{ public_path('storage/' . $firmaSupervisa) }}"
                            style="
                                max-width:180px;
                                max-height:85px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
            </tr>
        
        </table>

    </div>
</body>
</html>
