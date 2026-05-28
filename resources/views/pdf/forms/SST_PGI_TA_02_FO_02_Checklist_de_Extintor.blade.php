<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Extintor' }}</title>

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
            width: 108%;
            transform: scale(0.92);
            transform-origin: top left;
            margin-left: 5px;
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
            width: 96%;
            margin: 22px auto 0 auto;
            text-align: center;
        }

        .inspection-row {
            width: 100%;
            font-size: 0;
            text-align: center;
        }

        .inspection-item {
            display: inline-block;
            width: 31%;
            vertical-align: middle;
            font-size: 10px;
            box-sizing: border-box;
            text-align: center;
        }

        .inspection-label {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 6px;
        }

        .inspection-line-wrap {
            display: inline-block;
            width: 170px;
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
            $logoSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($path));
        }
    }

    $fechaInspeccion =
        optional($submission->created_at)->format('d/m/Y') ?: '';

    $tallerValor =
        data_get($answers, 'taller', '') ?: '';

    $nombreResponsableInspeccion =
        data_get($answers, 'nombre_responsable_inspeccion', '') ?: '';

    $firmaResponsableInspeccion =
    data_get($answers, 'firma_responsable_inspeccion');

    $firmaResponsableInspeccionSrc = null;
    
    if ($firmaResponsableInspeccion) {
        $pathFirma = storage_path('app/public/' . $firmaResponsableInspeccion);
    
        if (file_exists($pathFirma)) {
            $firmaResponsableInspeccionSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($pathFirma));
        }
    }
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
                CODIFICACIÓN: SST-PGI-TA-02-FO-02
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
                CHECKLIST DE EXTINTOR
            </td>

            <td colspan="2" class="right-cell">
                NÚMERO DE REVISIÓN: 06
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <div class="inspection-area">
        <div class="inspection-row">

            <!-- FECHA -->
            <div class="inspection-item" style="width:27%;">
                <span class="inspection-label">Fecha de Inspección:</span>

                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $fechaInspeccion }}
                    </span>

                    <span class="inspection-underline"></span>
                </span>
            </div>

            <!-- TALLER -->
            <div class="inspection-item" style="width:27%;">
                <span class="inspection-label">Taller:</span>

                <span class="inspection-line-wrap">
                    <span class="inspection-value">
                        {{ $tallerValor }}
                    </span>

                    <span class="inspection-underline"></span>
                </span>
            </div>

            <!-- NOMBRE -->
            <div class="inspection-item" style="width:40%;">
                <span class="inspection-label" style="
                    line-height:1.1;
                    text-align:right;
                    white-space:normal;
                    width:120px;
                    position:relative;
                    top:-6px;
                ">
                    Nombre y Firma del<br>
                    Resp. de Inspección:
                </span>
            
                <span class="inspection-line-wrap" style="
                    width:240px;
                    padding-left:75px;
                ">
            
                    <!-- FIRMA -->
                    @if($firmaResponsableInspeccionSrc)
                        <img
                            src="{{ $firmaResponsableInspeccionSrc }}"
                            style="
                                position:absolute;
                                left:0;
                                bottom:-2px;
                                width:95px;
                                height:38px;
                                object-fit:contain;
                                filter: contrast(180%) brightness(0.7);
                            "
                        >
                    @endif
            
                    <!-- NOMBRE -->
                    <span class="inspection-value">
                        {{ $nombreResponsableInspeccion }}
                    </span>
            
                    <span class="inspection-underline"></span>
                </span>
            </div>
        </div>
    </div> <!-- Datos -->

    <!-- INDICACIONES -->
    <div style="
        width:100%;
        text-align:center;
        margin-top:8px;
        font-size:9px;
        font-weight:bold;
        line-height:1.5;
    ">
        <div>
            Marque lo siguiente de acuerdo a la Inspección del extintor:
        </div>
    
        <div style="margin-top:4px;">
            Tipo de Extintor,
            ( ✓ ) Se Encuentra en Buenas Condiciones,
            ( X ) No Esta En Condiciones,
            ( NA ) No Aplica
        </div>
    </div> <!-- INDICACIONES -->

    <!-- TABLA EXTINTOR -->
    <table style="
        width: 99.1%;
        margin-top: 8px;
        border-collapse: separate; /* <--- CAMBIADO: De collapse a separate */
        border-spacing: 0;         /* <--- AGREGADO: Evita espacios entre celdas */
        table-layout: fixed;
        font-size: 7px;
    ">
        <tr style="height: 0; line-height: 0;">
            <td style="width: 10%; padding: 0; border: none; height: 0;"></td> 
            <td style="width: 4%; padding: 0; border: none; height: 0;"></td> 
            <td style="width: 3%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 3%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 3%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 3%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 3%; padding: 0; border: none; height: 0;"></td>
            
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5%; padding: 0; border: none; height: 0;"></td>
            
            <td style="width: 5.5%; padding: 0; border: none; height: 0;"></td>
            <td style="width: 5.5%; padding: 0; border: none; height: 0;"></td>
        </tr>
    
        <tr>
            <td rowspan="3" style="border:1px solid #000; background:#b91c1c; color:#fff; text-align:center; font-weight:bold;">
                No. de<br>Extintor
            </td>
    
            <td rowspan="3" style="border:1px solid #000; text-align:center; font-weight:bold;">
                <div style="transform:rotate(270deg); white-space:nowrap;">No. kilos</div>
            </td>
    
            <td colspan="5" style="border:1px solid #000; background:#b91c1c; color:#fff; text-align:center; font-weight:bold;">
                Tipo de Extintor
            </td>
    
            <td colspan="12" style="border:1px solid #000; background:#b91c1c; color:#fff; text-align:center; font-weight:bold;">
                Componente de Extintor
            </td>
    
            <td rowspan="3" colspan="2" style="
                border:1px solid #000;
                background:#d1d5db;
                text-align:center;
                font-weight:bold;
            ">
                Observaciones
            </td>
        </tr>
    
        <tr>
            @foreach (['PQS', 'CO2', 'Espuma Afff', 'Agente Limpio', 'Agua H2O'] as $tipo)
                <td rowspan="2" style="border:1px solid #000; background:#C7CCA7; text-align:center; font-weight:bold; height:70px;">
                    <div style="transform:rotate(270deg); white-space:nowrap;">{{ $tipo }}</div>
                </td>
            @endforeach
    
            @foreach ([
                'Anillo de<br>Verificación',
                'Etiqueta de<br>Inspección',
                'Caducidad',
                'Pasador de<br>Seguridad',
                'Cincho de<br>Seguridad',
                'Manómetro',
                'Presión',
                'Lleno',
                'Manguera y<br>Boquilla',
                'Señalética',
                'Soportes y<br>Funda',
                'Limpieza'
            ] as $componente)
                <td rowspan="2" style="border:1px solid #000; text-align:center; font-weight:bold; height:70px;">
                    <div style="
                        transform:rotate(270deg);
                        white-space:normal;
                        line-height:1.1;
                    ">
                        {!! $componente !!}
                    </div>
                </td>
            @endforeach
        </tr>
    
        <tr></tr>

        @php
            $filasExtintor = data_get($answers, 'tabla_checklist_extintor', []);
            $filasExtintor = is_array($filasExtintor) ? $filasExtintor : [];
        
            $componentesExtintor = [
                'anillo_verificacion',
                'etiqueta_inspeccion',
                'caducidad',
                'pasador_seguridad',
                'cincho_seguridad',
                'manometro',
                'presion',
                'lleno',
                'manguera_boquilla',
                'senaletica',
                'soportes_funda',
                'limpieza',
            ];
        
            $valorCorto = function ($valor) {
                if (str_contains($valor, 'Buenas Condiciones')) {
                    return '( ✓ ) Buenas Condiciones';
                }
        
                if (str_contains($valor, 'Malas Condiciones')) {
                    return '( X ) En Malas Condiciones';
                }
        
                if (str_contains($valor, 'No Aplica')) {
                    return '( NA ) No Aplica';
                }
        
                return $valor ?: '';
            };
        @endphp
        
        @php
            $totalFilas = max(10, count($filasExtintor));
        @endphp
        
        @for ($i = 0; $i < $totalFilas; $i++)
            @php
                $fila = $filasExtintor[$i] ?? [];
            @endphp
        
            <tr>
                <!-- No. Extintor -->
                <td style="border:1px solid #000; height:18px; text-align:center; vertical-align:middle;">
                    {{ data_get($fila, 'numero_extintor', '') }}
                </td>
        
                <!-- No. Kilos -->
                <td style="border:1px solid #000; height:18px; text-align:center; vertical-align:middle;">
                    {{ data_get($fila, 'numero_kilos', '') }}
                </td>
        
                <!-- Tipo de Extintor -->
                <td colspan="5" style="border:1px solid #000; height:18px; text-align:center; vertical-align:middle;">
                    {{ data_get($fila, 'tipo_extintor', '') }}
                </td>
        
                <!-- Componentes -->
                @foreach ($componentesExtintor as $componente)
                    <td style="border:1px solid #000; height:18px; text-align:center; vertical-align:middle;">
                        {{ $valorCorto(data_get($fila, $componente, '')) }}
                    </td>
                @endforeach
        
                <!-- Observaciones -->
                <td colspan="2" style="border:1px solid #000; height:18px; text-align:center; vertical-align:middle; padding:0 3px;">
                    {{ data_get($fila, 'observaciones', '') }}
                </td>
            </tr>
        @endfor
    </table>

</div>

</body>
</html>