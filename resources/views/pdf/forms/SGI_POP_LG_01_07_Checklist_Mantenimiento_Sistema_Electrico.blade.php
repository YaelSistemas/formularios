<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Mantenimiento Sistema Eléctrico' }}</title>

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

        .main-table {
            font-size: 7px;
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
    @endphp

    <div class="sheet">

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
                <td rowspan="3" class="logo-cell">

                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif

                </td>

                <!-- EMPRESA -->
                <td class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <!-- CODIFICACION -->
                <td class="right-cell">
                    CODIFICACIÓN: SGI-POP-LG-01-FO-07
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

                <!-- NOMBRE FORMULARIO -->
                <td class="center-cell">
                    CHECKLIST DE MANTENIMIENTO SISTEMA ELÉCTRICO
                </td>

                <!-- REVISION -->
                <td class="right-cell">
                    REVISIÓN: 02
                </td>
            </tr>
        </table>

        <!-- FECHA Y TALLER -->
        <table class="header-table" style="margin-top: 15px;">
        
            <tr>
        
                <!-- FECHA -->
                <td style="
                    width:35%;
                    text-align:center;
                    font-weight:bold;
                ">
                    FECHA:
                    {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                </td>
        
                <!-- ESPACIO -->
                <td style="
                    width:20%;
                    border:none !important;
                    background:#fff;
                "></td>
        
                <!-- TALLER -->
                <td style="
                    width:45%;
                    text-align:center;
                    font-weight:bold;
                ">
                    UNIDAD DE SERVICIO:
                    {{ $answers['taller'] ?? '' }}
                </td>
            </tr>
        </table>

        <!-- INDICACIÓN -->
        <div style="margin-top: 10px; text-align: center; font-weight: bold; font-style: italic; font-size: 8px;">
            Considerar las siguientes condiciones de acuerdo al estado del sistema eléctrico
        </div>

        @php
            $rowHeight = 10;
            $observacionesHeight = 35;
        
            $tablaSistemaElectrico = $answers['tabla_sistema_electrico'][0] ?? [];
        
            $estado = fn($id) => $tablaSistemaElectrico[$id] ?? '';
            $obs = fn($id) => $tablaSistemaElectrico[$id] ?? '';
        
            $rowsSistemaElectrico = [
                ['tipo' => 'titulo', 'num' => '1', 'desc' => 'Subestación'],
                ['tipo' => 'item', 'num' => '1.1', 'desc' => 'Interruptores termomagnéticos', 'estado' => 'electrico_1_1_estado', 'obs' => 'electrico_1_1_observaciones'],
                ['tipo' => 'item', 'num' => '1.2', 'desc' => 'Cableado y conexiones', 'estado' => 'electrico_1_2_estado', 'obs' => 'electrico_1_2_observaciones'],
                ['tipo' => 'item', 'num' => '1.3', 'desc' => 'Tablero eléctrico / pastillas', 'estado' => 'electrico_1_3_estado', 'obs' => 'electrico_1_3_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '2', 'desc' => 'Banco de Capacitores'],
                ['tipo' => 'item', 'num' => '2.1', 'desc' => 'Capacitor', 'estado' => 'electrico_2_1_estado', 'obs' => 'electrico_2_1_observaciones'],
                ['tipo' => 'item', 'num' => '2.2', 'desc' => 'Conexiones', 'estado' => 'electrico_2_2_estado', 'obs' => 'electrico_2_2_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '3', 'desc' => 'Transformador de 400 a 200 y 110 volt'],
                ['tipo' => 'item', 'num' => '3.1', 'desc' => 'Barras de conexión', 'estado' => 'electrico_3_1_estado', 'obs' => 'electrico_3_1_observaciones'],
                ['tipo' => 'item', 'num' => '3.2', 'desc' => 'Cableado y conexiones', 'estado' => 'electrico_3_2_estado', 'obs' => 'electrico_3_2_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '4', 'desc' => 'Tableros de contactos'],
                ['tipo' => 'item', 'num' => '4.1', 'desc' => 'Conectores de ploga a 440 y 220 V', 'estado' => 'electrico_4_1_estado', 'obs' => 'electrico_4_1_observaciones'],
                ['tipo' => 'item', 'num' => '4.2', 'desc' => 'Contactos polarizados a 110 volts', 'estado' => 'electrico_4_2_estado', 'obs' => 'electrico_4_2_observaciones'],
                ['tipo' => 'item', 'num' => '4.3', 'desc' => 'Cableado y conexiones', 'estado' => 'electrico_4_3_estado', 'obs' => 'electrico_4_3_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '5', 'desc' => 'Alumbrado'],
                ['tipo' => 'item', 'num' => '5.1', 'desc' => 'Lámparas leds almacén', 'estado' => 'electrico_5_1_estado', 'obs' => 'electrico_5_1_observaciones'],
                ['tipo' => 'item', 'num' => '5.2', 'desc' => 'Reflectores almacén', 'estado' => 'electrico_5_2_estado', 'obs' => 'electrico_5_2_observaciones'],
                ['tipo' => 'item', 'num' => '5.3', 'desc' => 'Alumbrado vestidores y WC', 'estado' => 'electrico_5_3_estado', 'obs' => 'electrico_5_3_observaciones'],
                ['tipo' => 'item', 'num' => '5.4', 'desc' => 'Hidroneumático y calentador', 'estado' => 'electrico_5_4_estado', 'obs' => 'electrico_5_4_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '6', 'desc' => 'Almacén de fríos'],
                ['tipo' => 'item', 'num' => '6.1', 'desc' => 'Alumbrado', 'estado' => 'electrico_6_1_estado', 'obs' => 'electrico_6_1_observaciones'],
                ['tipo' => 'item', 'num' => '6.2', 'desc' => 'Aterrizaje de racks', 'estado' => 'electrico_6_2_estado', 'obs' => 'electrico_6_2_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '7', 'desc' => 'Oficinas administrativas'],
                ['tipo' => 'item', 'num' => '7.1', 'desc' => 'Alumbrado y contactos', 'estado' => 'electrico_7_1_estado', 'obs' => 'electrico_7_1_observaciones'],
                ['tipo' => 'item', 'num' => '7.2', 'desc' => 'Red de datos', 'estado' => 'electrico_7_2_estado', 'obs' => 'electrico_7_2_observaciones'],
        
                ['tipo' => 'item', 'num' => '8', 'desc' => 'Alumbrado de patios', 'estado' => 'electrico_8_estado', 'obs' => 'electrico_8_observaciones'],
        
                ['tipo' => 'titulo', 'num' => '9', 'desc' => 'Pararrayos'],
                ['tipo' => 'item', 'num' => '9.1', 'desc' => 'Sistema de tierras y conexiones', 'estado' => 'electrico_9_1_estado', 'obs' => 'electrico_9_1_observaciones'],
                ['tipo' => 'item', 'num' => '9.2', 'desc' => 'Acoplador y disparador electromagnético', 'estado' => 'electrico_9_2_estado', 'obs' => 'electrico_9_2_observaciones'],
        
                ['tipo' => 'item', 'num' => '10', 'desc' => 'Sistema de bombeo cisterna', 'estado' => 'electrico_10_estado', 'obs' => 'electrico_10_observaciones'],
                ['tipo' => 'item', 'num' => '11', 'desc' => 'Sistema de video vigilancia', 'estado' => 'electrico_11_estado', 'obs' => 'electrico_11_observaciones'],
                ['tipo' => 'item', 'num' => '12', 'desc' => 'Extractores ambientales', 'estado' => 'electrico_12_estado', 'obs' => 'electrico_12_observaciones'],
            ];
        @endphp
        
        <table style="
            width:100%;
            margin-top:10px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:5%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
                <td style="width:7%; padding:0; border:none; height:0;"></td>
                <td style="width:7%; padding:0; border:none; height:0;"></td>
                <td style="width:7%; padding:0; border:none; height:0;"></td>
                <td style="width:44%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <tr>
                <td colspan="2" rowspan="2" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">
                    DESCRIPCIÓN
                </td>
                <td colspan="3" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">
                    CONDICIONES
                </td>
                <td rowspan="2" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">
                    OBSERVACIONES
                </td>
            </tr>
        
            <tr>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">BUENAS</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">MALAS</td>
                <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold; background:#d1d5db;">N/A</td>
            </tr>
        
            <tr>
                <td colspan="6" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">
                    CONDICIONES ELÉCTRICAS:
                </td>
            </tr>
        
            @foreach($rowsSistemaElectrico as $row)
                @if($row['tipo'] === 'titulo')
                    <tr>
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle; font-weight:bold;">
                            {{ $row['num'] }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle; font-weight:bold;">
                            {{ $row['desc'] }}
                        </td>
        
                        <td colspan="4" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px;"></td>
                    </tr>
                @else
                    @php
                        $estadoActual = $estado($row['estado']);
                    @endphp
        
                    <tr>
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:right; vertical-align:middle;">
                            {{ $row['num'] }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                            {{ $row['desc'] }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold;">
                            {{ $estadoActual === 'Buenas' ? '✔' : '' }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold;">
                            {{ $estadoActual === 'Malas' ? 'X' : '' }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold;">
                            {{ $estadoActual === 'N/A' ? 'N/A' : '' }}
                        </td>
        
                        <td style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:left; vertical-align:middle;">
                            {{ $obs($row['obs']) }}
                        </td>
                    </tr>
                @endif
            @endforeach
        
            <tr>
                <td colspan="6" style="border:1px solid #000; height:{{ $rowHeight }}px; padding:3px; text-align:center; vertical-align:middle; font-weight:bold;">
                    OBSERVACIONES:
                </td>
            </tr>
        
            <tr>
                <td colspan="6" style="border:1px solid #000; height:{{ $observacionesHeight }}px; padding:3px; text-align:center; vertical-align:top;">
                    {{ $answers['observaciones_generales'] ?? '' }}
                </td>
            </tr>
        </table>

        <!-- TABLA RESPONSABLE Y FIRMA -->
        <table style="
            width:100%;
            margin-top:10px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:8px;
        ">
        
            <!-- ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:50%; padding:0; border:none; height:0;"></td>
                <td style="width:50%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
        
                <!-- RESPONSABLE -->
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    RESPONSABLE MANTENIMIENTO:
                </td>
        
                <!-- FIRMA -->
                <td style="
                    border:1px solid #000;
                    height:{{ $rowHeight }}px;
                    padding:3px 3px 3px 8px;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    FIRMA:
                </td>
        
            </tr>
        
            <!-- FILA 2 -->
            <tr>
        
                <!-- NOMBRE -->
                <td style="
                    border:1px solid #000;
                    height:55px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
                    {{ $answers['nombre_responsable_mantenimiento'] ?? '' }}
                </td>
        
                <!-- FIRMA -->
                <td style="
                    border:1px solid #000;
                    height:55px;
                    padding:3px;
                    text-align:center;
                    vertical-align:middle;
                ">
        
                    @php
                        $firmaResponsable = $answers['firma_responsable_mantenimiento'] ?? null;
        
                        $firmaResponsableSrc = null;
        
                        if ($firmaResponsable) {
        
                            $firmaPath = storage_path('app/public/' . $firmaResponsable);
        
                            if (file_exists($firmaPath)) {
        
                                $firmaResponsableSrc =
                                    'data:image/png;base64,' .
                                    base64_encode(file_get_contents($firmaPath));
                            }
                        }
                    @endphp
        
                    @if($firmaResponsableSrc)
        
                        <img
                            src="{{ $firmaResponsableSrc }}"
                            style="
                                max-width:180px;
                                max-height:45px;
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