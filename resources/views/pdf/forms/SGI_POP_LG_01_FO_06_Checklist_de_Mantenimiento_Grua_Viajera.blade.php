<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Mantenimiento Grúa Viajera' }}</title>

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

            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:25%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
            </tr>

            <!-- FILA 1 -->
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
                    CODIFICACIÓN: SGI-POP-LG-01-FO-06
                </td>
            </tr>

            <!-- FILA 2 -->
            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>

            <!-- FILA 3 -->
            <tr>
                <td class="center-cell">
                    Checklist Mantenimiento Grúa Viajera
                </td>

                <td class="right-cell">
                    REVISIÓN: 01
                </td>
            </tr>
        </table>

        <!-- FECHA Y TALLER -->
        <table class="header-table" style="margin-top: 15px;">
            <tr>
                <!-- FECHA -->
                <td style="width:35%; text-align:center; font-weight:bold;">
                    FECHA: {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                </td>
        
                <!-- ESPACIO CENTRAL -->
                <td style="width:20%; border:none !important; background:#fff;"></td>
        
                <!-- TALLER -->
                <td style="width:45%; text-align:center; font-weight:bold;">
                    UBICACIÓN: {{ $taller }}
                </td>
            </tr>
        </table>

        <!-- INDICACIÓN -->
        <div style="margin-top: 10px; text-align: center; font-weight: bold; font-style: italic; font-size: 8px;">
            Considerar los siguientes criterios de acuerdo al estado de la grúa viajera.
        </div>

        @php
            $rowHeight = 10; // ALTO DE FILAS
        @endphp
        
        <!-- TABLA PRINCIPAL 5 COLUMNAS X 22 FILAS -->
        <table style="
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
        
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 HEADER -->
            <tr>
                <!-- DESCRIPCIÓN: columnas 1 y 2 + filas 1 y 2 -->
                <td colspan="2" rowspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    DESCRIPCIÓN
                </td>
            
                <!-- CONDICIONES: columnas 3 y 4 -->
                <td colspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    CONDICIONES
                </td>
            
                <!-- OBSERVACIONES: columna 5 + filas 1 y 2 -->
                <td rowspan="2" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    OBSERVACIONES
                </td>
            </tr>
            
            <!-- FILA 2 HEADER -->
            <tr>
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    BUENAS
                </td>
            
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#d1d5db;
                ">
                    MALAS
                </td>
            </tr>
            
            <!-- FILA 3 - CONDICIONES MECÁNICAS -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#e5e7eb;
                ">
                    CONDICIONES MECÁNICAS:
                </td>
            </tr>
            
            @php
                $filasMecanicas = [
                    ['num' => '1.',   'desc' => 'Transmisión Longitudinal',        'bold' => true],
                    ['num' => '1.1.', 'desc' => 'Rodajas',                         'bold' => false, 'base' => 'mecanicas_1_1_rodajas'],
                    ['num' => '1.2.', 'desc' => 'Baleros',                         'bold' => false, 'base' => 'mecanicas_1_2_baleros'],
                    ['num' => '1.3.', 'desc' => 'Tren de engrane',                 'bold' => false, 'base' => 'mecanicas_1_3_tren_engrane'],
                    ['num' => '1.4.', 'desc' => 'Vías de desplazamiento',          'bold' => false, 'base' => 'mecanicas_1_4_vias_desplazamiento'],
                    ['num' => '1.5.', 'desc' => 'Tornillería',                     'bold' => false, 'base' => 'mecanicas_1_5_tornilleria'],
            
                    ['num' => '2.',   'desc' => 'Transmisión Transversal',          'bold' => true],
                    ['num' => '2.1.', 'desc' => 'Rodajas',                         'bold' => false, 'base' => 'mecanicas_2_1_rodajas'],
                    ['num' => '2.2.', 'desc' => 'Baleros',                         'bold' => false, 'base' => 'mecanicas_2_2_baleros'],
                    ['num' => '2.3.', 'desc' => 'Tren de engrane',                 'bold' => false, 'base' => 'mecanicas_2_3_tren_engrane'],
                    ['num' => '2.4.', 'desc' => 'Vías de desplazamiento',          'bold' => false, 'base' => 'mecanicas_2_4_vias_desplazamiento'],
                    ['num' => '2.5.', 'desc' => 'Tornillería',                     'bold' => false, 'base' => 'mecanicas_2_5_tornilleria'],
            
                    ['num' => '3.',   'desc' => 'Niveles aceite de motorreductores','bold' => true, 'base' => 'mecanicas_3_niveles_aceite_motorreductores'],
            
                    ['num' => '4.',   'desc' => 'Embrague y Freno',                'bold' => true],
                    ['num' => '4.1.', 'desc' => 'Discos',                          'bold' => false, 'base' => 'mecanicas_4_1_discos'],
                    ['num' => '4.2.', 'desc' => 'Pastas',                          'bold' => false, 'base' => 'mecanicas_4_2_pastas'],
                    ['num' => '4.3.', 'desc' => 'Tornillería',                     'bold' => false, 'base' => 'mecanicas_4_3_tornilleria'],
            
                    ['num' => '5.',   'desc' => 'Tambor de Transmisión',            'bold' => true],
                    ['num' => '5.1.', 'desc' => 'Cable de acero',                  'bold' => false, 'base' => 'mecanicas_5_1_cable_acero'],
                    ['num' => '5.2.', 'desc' => 'Ganchos 22.5 y 8.5 Ton.',          'bold' => false, 'base' => 'mecanicas_5_2_ganchos'],
                ];
            @endphp
            
            @foreach($filasMecanicas as $fila) 

            @php
                $base = $fila['base'] ?? null;
                $estado = $base ? data_get($answers, $base . '_estado', '') : '';
                $observaciones = $base ? data_get($answers, $base . '_observaciones', '') : '';
            
                $esBuenas = $estado === 'Buenas condiciones';
                $esMalas = $estado === 'Malas condiciones';
            @endphp
                <tr>
            
                    <!-- COLUMNA 1 - NUMERO -->
                    <td style="
                        border:1px solid #000;
                        height:{{ $rowHeight }}px;
                        padding:3px;
                        text-align:right;
                        vertical-align:middle;
                        font-weight:{{ $fila['bold'] ? 'bold' : 'normal' }};
                    ">
                        {{ $fila['num'] }}
                    </td>
            
                    @if($fila['bold'] && $fila['num'] !== '3.')
            
                        <!-- DESCRIPCION -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:left;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $fila['desc'] }}
                        </td>
            
                        <!-- COLUMNAS 3,4,5 JUNTAS -->
                        <td colspan="3" style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            vertical-align:middle;
                        ">
                        </td>
            
                    @elseif($fila['bold'])

                        <!-- DESCRIPCION -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:left;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $fila['desc'] }}
                        </td>
                    
                        <!-- BUENAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esBuenas ? '✔' : '' }}
                        </td>
                    
                        <!-- MALAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esMalas ? 'x' : '' }}
                        </td>
                    
                        <!-- OBSERVACIONES -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ $observaciones }}
                        </td>
            
                    @else
            
                        <!-- DESCRIPCION -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:left;
                            vertical-align:middle;
                        ">
                            {{ $fila['desc'] }}
                        </td>
            
                        <!-- BUENAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esBuenas ? '✔' : '' }}
                        </td>
                        
                        <!-- MALAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esMalas ? 'x' : '' }}
                        </td>
                        
                        <!-- OBSERVACIONES -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ $observaciones }}
                        </td>
            
                    @endif
            
                </tr>
            @endforeach
        
        </table>

        <!-- SEGUNDA TABLA 5 COLUMNAS X 13 FILAS -->
        <table style="
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        ">
        
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:10%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    background:#e5e7eb;
                ">
                    CONDICIONES ELÉCTRICAS
                </td>
            </tr>
            
            @php
                $filasElectricas = [
                    ['num' => '1.',   'desc' => 'Conexiones y aislantes de motores', 'bold' => true,  'base' => 'electricas_1_conexiones_aislantes_motores'],
            
                    ['num' => '2.',   'desc' => 'Sensores de paro',                  'bold' => true],
            
                    ['num' => '2.1.', 'desc' => 'Longitudinal',                      'bold' => false, 'base' => 'electricas_2_1_longitudinal'],
                    ['num' => '2.2.', 'desc' => 'Transversal',                       'bold' => false, 'base' => 'electricas_2_2_transversal'],
                    ['num' => '2.3.', 'desc' => 'Ganchos de subida',                 'bold' => false, 'base' => 'electricas_2_3_ganchos_subida'],
            
                    ['num' => '3.',   'desc' => 'Variadores de Velocidad',           'bold' => true,  'base' => 'electricas_3_variadores_velocidad'],
                    ['num' => '4.',   'desc' => 'Tableros Eléctricos',               'bold' => true,  'base' => 'electricas_4_tableros_electricos'],
                    ['num' => '5.',   'desc' => 'Botonera',                          'bold' => true,  'base' => 'electricas_5_botonera'],
                    ['num' => '6.',   'desc' => 'Toma Corriente',                    'bold' => true,  'base' => 'electricas_6_toma_corriente'],
                ];
            @endphp
            
            @foreach($filasElectricas as $fila)

            @php
                $base = $fila['base'] ?? null;
            
                $estado = $base
                    ? data_get($answers, $base . '_estado', '')
                    : '';
            
                $observaciones = $base
                    ? data_get($answers, $base . '_observaciones', '')
                    : '';
            
                $esBuenas = $estado === 'Buenas condiciones';
                $esMalas = $estado === 'Malas condiciones';
            @endphp
                <tr>
            
                    <!-- COLUMNA 1 -->
                    <td style="
                        border:1px solid #000;
                        height:{{ $rowHeight }}px;
                        padding:3px;
                        text-align:right;
                        vertical-align:middle;
                        font-weight:{{ $fila['bold'] ? 'bold' : 'normal' }};
                    ">
                        {{ $fila['num'] }}
                    </td>
            
                    @if(
                        $fila['bold'] &&
                        !in_array($fila['num'], ['1.', '3.', '4.', '5.', '6.'])
                    )
            
                        <!-- DESCRIPCION -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:left;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $fila['desc'] }}
                        </td>
            
                        <!-- COLUMNAS 3,4,5 JUNTAS -->
                        <td colspan="3" style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            vertical-align:middle;
                        ">
                        </td>
            
                    @else
            
                        <!-- DESCRIPCION -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:left;
                            vertical-align:middle;
                            font-weight:{{ $fila['bold'] ? 'bold' : 'normal' }};
                        ">
                            {{ $fila['desc'] }}
                        </td>
            
                        <!-- BUENAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esBuenas ? '✔' : '' }}
                        </td>
                        
                        <!-- MALAS -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        ">
                            {{ $esMalas ? 'x' : '' }}
                        </td>
                        
                        <!-- OBSERVACIONES -->
                        <td style="
                            border:1px solid #000;
                            height:{{ $rowHeight }}px;
                            padding:3px;
                            text-align:center;
                            vertical-align:middle;
                        ">
                            {{ $observaciones }}
                        </td>
                    @endif
                </tr>
            @endforeach

            <!-- FILA 11 - OBSERVACIONES -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    OBSERVACIONES:
                </td>
            </tr>
            
            <!-- FILAS 12 Y 13 - OBSERVACIONES GENERALES -->
            <tr>
                <td colspan="5" style="
                    border:1px solid #000;
                    height:40px;
                    padding:3px; 
                    text-align:left; 
                    vertical-align:middle;
                ">
                    {{ data_get($answers, 'observaciones_generales', '') }}
                </td>
            </tr>
        </table>

        @php
            $nombreResponsable = data_get($answers, 'nombre_responsable_mantenimiento', '');
            $firmaResponsable = data_get($answers, 'firma_responsable_mantenimiento', '');
        
            $firmaSrc = null;
        
            if (!empty($firmaResponsable)) {
                $firmaPath = storage_path('app/public/' . ltrim($firmaResponsable, '/'));
        
                if (file_exists($firmaPath)) {
                    $firmaSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($firmaPath));
                }
            }
        @endphp
        
        <!-- TABLA RESPONSABLE Y FIRMA -->
        <table style="
            width:100%;
            margin-top:10px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <tr>
                <td style="
                    width:50%;
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    RESPONSABLE MANTENIMIENTO:
                </td>
        
                <td style="
                    width:50%;
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    FIRMA:
                </td>
            </tr>
        
            <tr>
                <td style="
                    border:1px solid #000;
                    height:60px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $nombreResponsable }}
                </td>
        
                <td style="
                    border:1px solid #000;
                    height:60px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    @if($firmaSrc)
                        <img src="{{ $firmaSrc }}" style="
                            max-width: 180px;
                                max-height: 50px;
                                object-fit: contain;
                        ">
                    @endif
                </td>
            </tr>
        </table>

    </div>
</body>
</html>