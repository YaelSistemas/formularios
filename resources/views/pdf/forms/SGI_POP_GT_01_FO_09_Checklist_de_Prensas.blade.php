<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Prensas' }}</title>

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
                $logoSrc =
                    'data:image/png;base64,' .
                    base64_encode(file_get_contents($path));
            }
        }

        $tipo = $answers['tipo'] ?? '';
        $corriente = $answers['corriente'] ?? '';
        $noSerie = $answers['no_serie'] ?? '';
        
        $prensasSrc = null;
        $prensasPath = public_path('images/forms/SGI_POP_GT_01_FO_09_Checklist_de_Prensas/prensas.png');
        
        if (file_exists($prensasPath)) {
            $prensasSrc =
                'data:image/png;base64,' .
                base64_encode(file_get_contents($prensasPath));
        }

        $accionRealizar = $answers['accion_realizar'] ?? '';

        $componentesPrensaRows = [
            ['CAJA DE CONTROL Y/O CABEZAS DE CONTROL', 'caja_control_cabezas_control'],
            ['PLATO SUPERIOR', 'plato_superior'],
            ['PLATO INFERIOR', 'plato_inferior'],
            ['TERMOSTATO SUPERIOR', 'termostato_superior'],
            ['TERMOSTATO INFERIOR', 'termostato_inferior'],
            ['TERMOMETRO PLATO SUPERIOR', 'termometro_plato_superior'],
            ['TERMOMETRO PLATO INFERIOR', 'termometro_plato_inferior'],
            ['CABLE MAESTRO PLATO SUPERIOR', 'cable_maestro_plato_superior'],
            ['CABLE MAESTRO PLATO INFERIOR', 'cable_maestro_plato_inferior'],
            ['PLOGA DE ALIMENTACIÓN PRINCIPAL', 'ploga_alimentacion_principal'],
            ['EXTENSIÓN DE ALIMENTACIÓN PRINCIPAL', 'extension_alimentacion_principal'],
            ['PUENTE INTERCONECTOR', 'puente_interconector'],
            ['CAMARA DE PRESIÓN', 'camara_presion'],
            ['ACOPLE RAPIDO', 'acople_rapido'],
            ['VERIFICADOR DE PRESIÓN', 'verificador_presion'],
            ['MANGUERA DE LLENADO', 'manguera_llenado'],
            ['TORNILLOS Y/O PERNOS', 'tornillos_pernos'],
            ['PLATOS COMPENSADORES DE CALOR', 'platos_compensadores_calor'],
            ['RIELES', 'rieles'],
            ['MANGUERAS PARA ENFRIAMIENTO', 'mangueras_enfriamiento'],
            ['SEGUROS DE RIELES', 'seguros_rieles'],
            ['SISTEMA DE PRESIÓN: BOMBA / COMPRESOR', 'sistema_presion_bomba_compresor'],
        ];

        $nombreEntregaPrensa = $answers['nombre_entrega_prensa'] ?? '';
        $firmaEntregaPrensa = $answers['firma_entrega_prensa'] ?? '';
        
        $nombreRecibePrensa = $answers['nombre_recibe_prensa'] ?? '';
        $firmaRecibePrensa = $answers['firma_recibe_prensa'] ?? '';
        
        $nombreInspeccionaMantenimiento = $answers['nombre_inspecciona_mantenimiento'] ?? '';
        $firmaInspeccionaMantenimiento = $answers['firma_inspecciona_mantenimiento'] ?? '';
        
        $getFirmaSrc = function ($relativePath) {
            if (empty($relativePath)) {
                return null;
            }
        
            $path = storage_path('app/public/' . $relativePath);
        
            if (!file_exists($path)) {
                return null;
            }
        
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        };
        
        $firmaEntregaSrc = $getFirmaSrc($firmaEntregaPrensa);
        $firmaRecibeSrc = $getFirmaSrc($firmaRecibePrensa);
        $firmaInspeccionaSrc = $getFirmaSrc($firmaInspeccionaMantenimiento);
    @endphp

    <div class="sheet">

        <!-- HEADER -->
        <table class="header-table">
            <!-- DEFINICIÓN DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:25%; padding:0; border:none; height:0;"></td>
                <td style="width:45%; padding:0; border:none; height:0;"></td>
                <td style="width:30%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1: PÁGINA -->
            <tr>
                <!-- LOGO UTILIZA LAS 4 FILAS -->
                <td rowspan="4" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>
        
                <!-- EMPRESA UTILIZA LAS FILAS DE PÁGINA Y CODIFICACIÓN -->
                <td rowspan="2" class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>
        
                <td class="right-cell">
                    PÁGINA: 1 DE 1
                </td>
            </tr>
        
            <!-- FILA 2: CODIFICACIÓN -->
            <tr>
                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>
        
            <!-- FILA 3: FECHA EMISIÓN -->
            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>
        
                <td class="right-cell">
                    CÓDIGO: SGI-POP-GT-01-FO-09
                </td>
            </tr>
        
            <!-- FILA 4: REVISIÓN -->
            <tr>
                <td class="center-cell">
                    CHECKLIST DE PRENSAS
                </td>
        
                <td class="right-cell">
                    REVISIÓN: 05
                </td>
            </tr>
        </table>

        <table style="
            width:100%;
            border-collapse:collapse;
            border:1px solid #000;
            border-bottom:none;
            border-top:none;
        ">
            <tr>
                <td style="padding:6;">
        
                    <!-- FECHA Y CÓDIGO -->
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                    ">
                        <tr>
                            <!-- FECHA -->
                            <td style="
                                width:50%;
                                text-align:center;
                                vertical-align:bottom;
                                padding-right:20px;
                            ">
                                <span style="
                                    font-size:8px;
                                    font-weight:bold;
                                ">
                                    FECHA:
                                </span>
        
                                <span style="
                                    display:inline-block;
                                    min-width:120px;
                                    border-bottom:1px solid #000;
                                    text-align:center;
                                    font-size:8px;
                                ">
                                    {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                                </span>
                            </td>
        
                            <!-- CÓDIGO -->
                            <td style="
                                width:50%;
                                text-align:center;
                                vertical-align:bottom;
                                padding-left:20px;
                            ">
                                <span style="
                                    font-size:8px;
                                    font-weight:bold;
                                ">
                                    CÓDIGO:
                                </span>
        
                                <span style="
                                    display:inline-block;
                                    min-width:120px;
                                    border-bottom:1px solid #000;
                                    text-align:center;
                                    font-size:8px;
                                ">
                                    {{ $answers['codigo'] ?? '' }}
                                </span>
                            </td>
                        </tr>
                    </table>
        
                </td>
            </tr>
        </table>

        <!-- TABLA TIPO DE PRENSA -->
        <table style="
            width:100%;
            margin-top:0px;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:7px;
        ">
        
            <tr style="height:0; line-height:0;">
                <td style="width:4%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:4%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:4%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:14%; padding:0; border:none;"></td>
                <td style="width:4%; padding:0; border:none;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr style="height:30px;">
                <td colspan="10" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    ELIJE EL TIPO DE PRENSA A REVISIÓN Y TIPO DE CORRIENTE
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr style="height:30px;">
        
                <td rowspan="6" style="
                    border:1px solid #000;
                    border-right:none;
                "></td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO O MODELO
                </td>
        
                <td colspan="6" rowspan="4" style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px;
                ">
                    @if($prensasSrc)
                        <img
                            src="{{ $prensasSrc }}"
                            style="
                                width:440px;
                                height:70px;
                                object-fit:contain;
                            "
                        >
                    @endif
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO O MODELO
                </td>
        
                <td rowspan="6" style="
                    border:1px solid #000;
                    border-left:none;
                "></td>
        
            </tr>
        
            <!-- FILA 3 -->
            <tr style="height:30px;">
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    220 VOLTS
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    220 VOLTS
                </td>
        
            </tr>
        
            <!-- FILA 4 -->
            <tr style="height:30px;">
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    440 VOLTS
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    440 VOLTS
                </td>
        
            </tr>
        
            <!-- FILA 5 -->
            <tr style="height:30px;">
        
                <td style="
                    border:1px solid #000;
                    border:none;
                "></td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                "></td>
        
            </tr>
        
            <!-- FILA 6 -->
            <tr style="height:30px;">
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO O MODELO:
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <span style="
                        display:block;
                        border-bottom:1px solid #000;
                        font-weight:bold;
                    ">
                        {{ $tipo }}
                    </span>
                </td>
        
                <td style="border:1px solid #000; border:none;"></td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO DE CORRIENTE:
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <span style="
                        display:block;
                        border-bottom:1px solid #000;
                        font-weight:bold;
                    ">
                        {{ $corriente }}
                    </span>
                </td>
        
                <td style="border:1px solid #000; border:none;"></td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    NUMERO DE SERIE:
                </td>
        
                <td style="
                    border:1px solid #000;
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <span style="
                        display:block;
                        border-bottom:1px solid #000;
                        font-weight:bold;
                    ">
                        {{ $noSerie }}
                    </span>
                </td>
            </tr>

            <!-- FILA 7 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:1px solid #000;
                    border-top:none;
                    border-left:none;
                    border-right:none;
                ">
                </td>
            </tr>

            <!-- FILA 8 -->
            <tr style="height:30px;">
                <td colspan="10" style="
                    border:1px solid #000;
                    border-bottom:none;
                    padding:3px 0;
                "></td>
            </tr>
            
            <!-- FILA 9 -->
            <tr style="height:30px;">
                <td rowspan="11" style="
                    border:1px solid #000;
                    border-top:none;
                    border-right:none;
                "></td>
            
                <td colspan="8" style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    ESPECIFICAR UNIDAD DE SERVICIO (en caso de que no aplique colocar NA)
                </td>
            
                <td rowspan="11" style="
                    border:1px solid #000;
                    border-top:none;
                    border-left:none;
                "></td>
            </tr>
            
            <!-- FILA 10 -->
            <tr style="height:30px;">
                <td colspan="3" style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    Taller de origen
                </td>
            
                <td colspan="2" rowspan="2" style="
                    border:none;
                "></td>
            
                <td colspan="3" style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    Taller que solicita
                </td>
            </tr>
            
            <!-- FILA 11 -->
            <tr style="height:30px;">
                <td colspan="3" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    {{ $answers['taller_origen'] ?? '' }}
                </td>
            
                <td colspan="3" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    {{ $answers['taller_solicita'] ?? '' }}
                </td>
            </tr>
            
            <!-- FILA 12 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    padding:3px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
            </tr>
        
            <!-- FILA 13 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    ESPECIFICAR CON UNA (X) LA ACCIÓN A REALIZAR (en caso de que no aplque colocar NA)
                </td>
            </tr>

            <!-- FILA 14 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    padding:3px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
            </tr>

            <!-- FILA 15 -->
            <tr style="height:30px;">
            
                <td colspan="8" style="
                    border:none;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <table style="
                        width:95%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin-left:0;
                        margin-right:auto;
                    ">
                        <tr>
                            <td style="border:none; width:15%; text-align:right; font-weight:bold;">
                                INSPECCIÓN
                            </td>
                            <td style="border:none; width:10%; text-align:center;">
                                <span style="display:inline-block; width:50px; height:14px; border:1px solid #000; text-align:center; line-height:10px; font-size:9px; font-weight:bold;">
                                    {{ $accionRealizar === 'Inspección' ? 'X' : '' }}
                                </span>
                            </td>
            
                            <td style="border:none; width:15%; text-align:right; font-weight:bold;">
                                MANTENIMIENTO
                            </td>
                            <td style="border:none; width:10%; text-align:center;">
                                <span style="display:inline-block; width:50px; height:14px; border:1px solid #000; text-align:center; line-height:10px; font-size:9px; font-weight:bold;">
                                    {{ $accionRealizar === 'Mantenimiento' ? 'X' : '' }}
                                </span>
                            </td>
            
                            <td style="border:none; width:15%; text-align:right; font-weight:bold;">
                                DEVOLUCIÓN
                            </td>
                            <td style="border:none; width:10%; text-align:center;">
                                <span style="display:inline-block; width:50px; height:14px; border:1px solid #000; text-align:center; line-height:10px; font-size:9px; font-weight:bold;">
                                    {{ $accionRealizar === 'Devolución' ? 'X' : '' }}
                                </span>
                            </td>
            
                            <td style="border:none; width:15%; text-align:right; font-weight:bold;">
                                PRESTAMO
                            </td>
                            <td style="border:none; width:10%; text-align:center;">
                                <span style="display:inline-block; width:50px; height:14px; border:1px solid #000; text-align:center; line-height:10px; font-size:9px; font-weight:bold;">
                                    {{ $accionRealizar === 'Préstamo' ? 'X' : '' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- FILA 16 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    padding:10px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
            </tr>

            <!-- FILA 17 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    text-align:center;
                    vertical-align:middle;
                ">
                    Marque según corresponda el estado:
                </td>
            </tr>

            <!-- FILA 18 -->
            <tr style="height:30px;">
            
                <td colspan="8" style="
                    border:none;
                    padding:0;
                    text-align:center;
                    vertical-align:middle;
                ">
                    <table style="
                        width:89%;
                        border-collapse:collapse;
                        table-layout:fixed;
                        margin-left:0;
                        margin-right:auto;
                    ">
                        <tr>
                            <td style="border:none; width:20%; text-align:right;">
                                <span style="font-weight:bold;">C</span> = Cumple
                            </td>
                            
                            <td style="border:none; width:20%; text-align:right;">
                                <span style="font-weight:bold;">NC</span> = No Cumple
                            </td>
                            
                            <td style="border:none; width:20%; text-align:right;">
                                <span style="font-weight:bold;">F</span> = Faltante
                            </td>
                            
                            <td style="border:none; width:20%; text-align:right;">
                                <span style="font-weight:bold;">MM</span> = Mantenimiento
                            </td>
                            
                            <td style="border:none; width:20%; text-align:right;">
                                <span style="font-weight:bold;">NA</span> = No Aplica
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- FILA 19 -->
            <tr style="height:30px;">
                <td colspan="8" style="
                    border:none;
                    border-bottom:1px solid #000;
                    padding:3px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
            </tr>
        </table>

        <!-- TABLA COMPONENTES DE PRENSA -->
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:6px;
            margin-top:0;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:10%; padding:0; border:none;"></td>
                <td style="width:10%; padding:0; border:none;"></td>
                <td style="width:10%; padding:0; border:none;"></td>
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:15%; padding:0; border:none;"></td>
                <td style="width:15%; padding:0; border:none;"></td>
            </tr>
        
            <!-- FILA 20 -->
            <tr style="height:18px;">
                <td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; background:#e5e7eb; font-weight:bold; text-align:center; vertical-align:middle; padding:6px;">
                    CANTIDAD
                </td>
        
                <td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; background:#e5e7eb; font-weight:bold; text-align:center; vertical-align:middle;">
                    UNIDAD
                </td>
        
                <td colspan="3" style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; background:#e5e7eb; font-weight:bold; text-align:center; vertical-align:middle;">
                    DESCRIPCIÓN
                </td>
        
                <td colspan="3" style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; background:#e5e7eb; font-weight:bold; text-align:center; vertical-align:middle;">
                    ESTADO
                </td>
        
                <td colspan="2" style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; background:#e5e7eb; font-weight:bold; text-align:center; vertical-align:middle;">
                    OBSERVACIONES
                </td>
            </tr>
        
            @foreach($componentesPrensaRows as [$descripcion, $key])
                <tr style="height:16px;">
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $answers[$key . '_cantidad'] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        PZA
                    </td>
        
                    <td colspan="3" style="border:1px solid #000; text-align:left; vertical-align:middle; padding:2px;">
                        {{ $descripcion }}
                    </td>
        
                    <td colspan="3" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $answers[$key . '_estado'] ?? '' }}
                    </td>
        
                    <td colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $answers[$key . '_comentarios'] ?? '' }}
                    </td>
                </tr>
            @endforeach

            <!-- FILA 43 -->
            <tr style="height:18px;">
                <td colspan="10" style="
                    border:1px solid #000;
                    border-bottom:none;
                    font-weight:bold;
                    text-align:left;
                    vertical-align:middle;
                    padding-left:5px;
                ">
                    NOTAS:
                </td>
            </tr>
            
            <!-- FILA 44 -->
            <tr style="height:30px;">
                <td colspan="10" style="
                    border:1px solid #000;
                    border-top:none;
                    text-align:center;
                    vertical-align:middle;
                    padding:6px;
                ">
                    {{ $answers['notas'] ?? '' }}
                </td>
            </tr>

            <!-- FILA 45 -->
            <tr style="height:30px;">
                <td colspan="10" style="
                    border:1px solid #000;
                    border-bottom:none;
                    padding:2px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
            </tr>
        </table>

        @php
            $equiposMedicion = collect(
                $answers['tabla_equipos_medicion'] ?? []
            )
                ->filter(function ($equipo) {
                    if (!is_array($equipo)) {
                        return false;
                    }
        
                    return collect($equipo)->contains(function ($value) {
                        return $value !== null &&
                               $value !== '' &&
                               trim((string) $value) !== '';
                    });
                })
                ->values()
                ->all();
        
            /*
             * Guarda si originalmente no existía ningún registro.
             * Debe hacerse antes de completar las tres filas vacías.
             */
            $sinEquiposMedicion = count($equiposMedicion) === 0;

            /*
             * Diagonal gruesa para las celdas de equipos de medición.
             */
            $diagonalTablaSvg = '
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="1000"
                     height="100"
                     viewBox="0 0 1000 100"
                     preserveAspectRatio="none">
                    <line
                        x1="0"
                        y1="0"
                        x2="1000"
                        y2="100"
                        stroke="#ff0000"
                        stroke-width="6"
                    />
                </svg>
            ';
            
            $diagonalTablaSrc =
                'data:image/svg+xml;base64,' .
                base64_encode($diagonalTablaSvg);
            
            
            /*
             * Diagonal delgada para las firmas opcionales.
             */
            $diagonalFirmaSvg = '
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="1000"
                     height="100"
                     viewBox="0 0 1000 100"
                     preserveAspectRatio="none">
                    <line
                        x1="0"
                        y1="0"
                        x2="1000"
                        y2="100"
                        stroke="#ff0000"
                        stroke-width="6"
                    />
                </svg>
            ';
            
            $diagonalFirmaSrc =
                'data:image/svg+xml;base64,' .
                base64_encode($diagonalFirmaSvg);
                    
            /*
             * Siempre muestra como mínimo tres filas.
             */
            while (count($equiposMedicion) < 3) {
                $equiposMedicion[] = [];
            }
        @endphp

        <!-- TABLA EQUIPO DE MEDICIÓN -->
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:6px;
            margin-top:0;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:8%; padding:0; border:none;"></td>
                <td style="width:27%; padding:0; border:none;"></td>
                <td style="width:30%; padding:0; border:none;"></td>
                <td style="width:35%; padding:0; border:none;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr style="height:28px;">
                <td colspan="4" style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    text-align:center;
                    vertical-align:middle;
                    padding:6px;
                ">
                    <span style="font-weight:bold;">
                        EN CASO DE PRESTAMO O DEVOLUCIÓN:
                    </span>
                    INTEGRAR LOS DATOS DEL EQUIPO DE MEDICIÓN COMO COMPLEMENTO DE LA PRENSA
                    <br>
                    (SI NO APLICA CANCELAR CAMPOS CON UNA DIAGONAL)
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr style="height:18px;">
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:6px;
                ">
                    Cantidad
                </td>
        
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:6px;
                ">
                    Nombre del equipo
                </td>
        
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:6px;
                ">
                    Número de serie
                </td>
        
                <td style="
                    border:1px solid #000;
                    background:#e5e7eb;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:6px;
                ">
                    Observaciones
                </td>
            </tr>
        
            <!-- FILAS DE EQUIPOS: MÍNIMO 3 -->
            @if($sinEquiposMedicion)
            
                <!-- SIN REGISTROS: CADA CELDA TIENE SU PROPIA DIAGONAL -->
                @for($filaEquipo = 0; $filaEquipo < 3; $filaEquipo++)
                    <tr style="height:10px;">
            
                        <!-- CANTIDAD -->
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            line-height:0;
                        ">
                            <img
                                src="{{ $diagonalTablaSrc }}"
                                style="
                                    display:block;
                                    width:100%;
                                    height:10px;
                                    margin:0;
                                    padding:0;
                                "
                            >
                        </td>
            
                        <!-- NOMBRE DEL EQUIPO -->
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            line-height:0;
                        ">
                            <img
                                src="{{ $diagonalTablaSrc }}"
                                style="
                                    display:block;
                                    width:100%;
                                    height:10px;
                                    margin:0;
                                    padding:0;
                                "
                            >
                        </td>
            
                        <!-- NÚMERO DE SERIE -->
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            line-height:0;
                        ">
                            <img
                                src="{{ $diagonalTablaSrc }}"
                                style="
                                    display:block;
                                    width:100%;
                                    height:10px;
                                    margin:0;
                                    padding:0;
                                "
                            >
                        </td>
            
                        <!-- OBSERVACIONES -->
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0;
                            text-align:center;
                            vertical-align:middle;
                            line-height:0;
                        ">
                            <img
                                src="{{ $diagonalTablaSrc }}"
                                style="
                                    display:block;
                                    width:100%;
                                    height:10px;
                                    margin:0;
                                    padding:0;
                                "
                            >
                        </td>
            
                    </tr>
                @endfor
            
            @else
            
                <!-- CON REGISTROS -->
                @foreach($equiposMedicion as $equipoIndex => $equipo)
                    <tr style="height:10px;">
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0 1px;
                            text-align:center;
                            vertical-align:middle;
                            line-height:8px;
                            font-size:6px;
                        ">
                            {{ $equipo['cantidad'] ?? '' }}&nbsp;
                        </td>
            
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0 1px;
                            text-align:center;
                            vertical-align:middle;
                            line-height:8px;
                            font-size:6px;
                        ">
                            {{ $equipo['nombre_equipo'] ?? '' }}&nbsp;
                        </td>
            
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0 1px;
                            text-align:center;
                            vertical-align:middle;
                            line-height:8px;
                            font-size:6px;
                        ">
                            {{ $equipo['numero_serie'] ?? '' }}&nbsp;
                        </td>
            
                        <td style="
                            border:1px solid #000;
                            height:10px;
                            padding:0 1px;
                            text-align:center;
                            vertical-align:middle;
                            line-height:8px;
                            font-size:6px;
                        ">
                            {{ $equipo['observaciones'] ?? '' }}&nbsp;
                        </td>
                    </tr>
                @endforeach
            
            @endif
        
            <!-- FILA VACÍA DESPUÉS DE LOS REGISTROS -->
            <tr style="height:30px;">
                <td colspan="4" style="
                    border:1px solid #000;
                    border-bottom:none;
                    padding:2px 0;
                    text-align:center;
                    vertical-align:middle;
                ">
                </td>
            </tr>
        </table>

        <!-- TABLA FIRMAS -->
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:6px;
            margin-top:0;
        ">
            <tr style="height:18px;">
                <td colspan="3" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                ">
                    (Colocar firmas que correspondan en caso de no aplicar campos cancelar campos con una diagonal)
                </td>
            </tr>
        
            <tr style="height:24px;">
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:4px;
                ">
                    NOMBRE Y FIRMA QUIEN ENTREGA PRENSA:
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:4px;
                ">
                    NOMBRE Y FIRMA QUIEN RECIBE PRENSA:
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:4px;
                ">
                    NOMBRE Y FIRMA QUIEN INSPECCIONA O DA MANTENIMIENTO:
                </td>
            </tr>
        
            <tr style="height:70px;">
                <!-- FIRMA Y NOMBRE DE QUIEN ENTREGA -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                    height:70px;
                ">
                    @if($firmaEntregaSrc)
                        <img
                            src="{{ $firmaEntregaSrc }}"
                            style="
                                width:130px;
                                height:45px;
                                object-fit:contain;
                                display:block;
                                margin:0 auto 2px auto;
                            "
                        >
                    @else
                        <img
                            src="{{ $diagonalFirmaSrc }}"
                            style="
                                width:100%;
                                height:45px;
                                display:block;
                                margin:0 0 2px 0;
                                padding:0;
                            "
                        >
                    @endif
                
                    <div style="
                        height:12px;
                        line-height:12px;
                        text-align:center;
                    ">
                        {{ $nombreEntregaPrensa }}
                    </div>
                </td>
        
                <!-- FIRMA Y NOMBRE DE QUIEN RECIBE -->
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                    height:70px;
                ">
                    @if($firmaRecibeSrc)
                        <img
                            src="{{ $firmaRecibeSrc }}"
                            style="
                                width:130px;
                                height:45px;
                                object-fit:contain;
                                display:block;
                                margin:0 auto 2px auto;
                            "
                        >
                    @else
                        <img
                            src="{{ $diagonalFirmaSrc }}"
                            style="
                                width:100%;
                                height:45px;
                                display:block;
                                margin:0 0 2px 0;
                                padding:0;
                            "
                        >
                    @endif
                
                    <div style="
                        height:12px;
                        line-height:12px;
                        text-align:center;
                    ">
                        {{ $nombreRecibePrensa }}
                    </div>
                </td>
        
                <td style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                ">
                    @if($firmaInspeccionaSrc)
                        <img src="{{ $firmaInspeccionaSrc }}" style="
                            width:130px;
                            height:45px;
                            object-fit:contain;
                            display:block;
                            margin:0 auto 2px auto;
                        ">
                    @endif
        
                    <div>{{ $nombreInspeccionaMantenimiento }}</div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
