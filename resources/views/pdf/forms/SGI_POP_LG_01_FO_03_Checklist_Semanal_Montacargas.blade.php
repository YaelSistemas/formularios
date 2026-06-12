<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist Semanal Montacargas' }}</title>

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

        $taller = $answers['taller'] ?? '';

        $equipo = $answers['equipo'] ?? '';
        $horometro = $answers['horometro'] ?? '';
        $fechaUltimoMantenimiento = $answers['fecha_ultimo_mantenimiento'] ?? '';

        $montacargasSrc = null;
        $montacargasPath = public_path('images/forms/SGI_POP_LG_01_FO_03_Checklist_Semanal_Montacargas/Montacargas.jpg');
        
        if (file_exists($montacargasPath)) {
            $montacargasSrc =
                'data:image/jpeg;base64,' .
                base64_encode(file_get_contents($montacargasPath));
        }
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
                    CODIFICACIÓN: SGI-POP-LG-01-FO-03
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    Checklist Semanal Montacargas
                </td>

                <td class="right-cell">
                    REVISIÓN: 01
                </td>
            </tr>
        </table>

        <!-- DATOS GENERALES + IMAGEN -->
        <table style="width: 100%; margin-top: 10px; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <!-- DATOS IZQUIERDA -->
                <td style="
                    width: 35%;
                    vertical-align: top;
                    padding: 0;
                    border: none;
                ">
                    <!-- FECHA -->
                    <table class="header-table" style="width: 100%;">
                        <tr>
                            <td style="text-align: center; font-weight: bold; padding: 2px 4px;">
                                Fecha:
                                {{ \Carbon\Carbon::parse($submission->created_at)->format('d/m/Y') }}
                            </td>
                        </tr>
                    </table>
        
                    <!-- TALLER -->
                    <table class="header-table" style="width: 100%; margin-top: 8px;">
                        <tr>
                            <td style="text-align: center; font-weight: bold; padding: 2px 4px;">
                                Taller:
                                {{ $taller }}
                            </td>
                        </tr>
                    </table>
        
                    <!-- EQUIPO -->
                    <table class="header-table" style="width: 100%; margin-top: 8px;">
                        <tr>
                            <td style="text-align: center; font-weight: bold; padding: 2px 4px;">
                                Equipo:
                                {{ $equipo }}
                            </td>
                        </tr>
                    </table>
        
                    <!-- HORÓMETRO -->
                    <table class="header-table" style="width: 100%; margin-top: 8px;">
                        <tr>
                            <td style="text-align: center; font-weight: bold; padding: 2px 4px;">
                                Horómetro:
                                {{ $horometro }}
                            </td>
                        </tr>
                    </table>
        
                    <!-- FECHA ÚLTIMO MANTENIMIENTO -->
                    <table class="header-table" style="width: 100%; margin-top: 8px;">
                        <tr>
                            <td style="text-align: center; font-weight: bold; padding: 2px 4px;">
                                Fecha del último mantenimiento:
                                {{ $fechaUltimoMantenimiento }}
                            </td>
                        </tr>
                    </table>
                </td>
        
                <!-- ESPACIO MEDIO -->
                <td style="
                    width: 5%;
                    border: none;
                    padding: 0;
                "></td>
        
                <!-- IMAGEN DERECHA -->
                <td style="
                    width: 60%;
                    vertical-align: top;
                    text-align: center;
                    border: none;
                    padding: 0;
                ">
                    @if($montacargasSrc)
                        <img
                            src="{{ $montacargasSrc }}"
                            style="
                                width: 240px;
                                height: 120px;
                                object-fit: contain;
                            "
                        >
                    @endif
                </td>
            </tr>
        </table>

        <!-- TABLA PRINCIPAL -->
        <table class="header-table main-table" style="
            margin-top: 12px;
            width: 100%;
        ">

        <tr style="height:0; line-height:0;">
            <td style="width:22%; padding:0; border:none;"></td>
            <td style="width:35%; padding:0; border:none;"></td>
            <td style="width:9%; padding:0; border:none;"></td>
            <td style="width:9%; padding:0; border:none;"></td>
            <td style="width:25%; padding:0; border:none;"></td>
        </tr>
            <!-- FILA 1 -->
            <tr>
                <td colspan="5" style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    CONDICIÓN DEL EQUIPO
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <!-- DESCRIPCIÓN -->
                <td rowspan="2" colspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    DESCRIPCIÓN
                </td>
        
                <!-- OPCIÓN -->
                <td colspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    OPCIÓN
                </td>
        
                <!-- OBSERVACIONES -->
                <td rowspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    OBSERVACIONES
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    BUEN ESTADO
                </td>
        
                <td style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    MAL ESTADO
                </td>
            </tr>
        
            @php
                $condicionEquipoRows = [
                    [
                        'titulo' => 'LLANTAS (REVESTIMIENTO Y PRESION)',
                        'criterio' => 'No presenta fisuras, grietas, cortaduras, chipotes, ni desgaste excesivo.',
                        'estado' => $answers['condicion_equipo_llantas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_llantas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'CARROCERIA',
                        'criterio' => 'Libre de golpes, fisuras o corrosión. No tiene partes sueltas ni deformaciones que afecten la estructura.',
                        'estado' => $answers['condicion_equipo_carroceria_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_carroceria_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'VOLANTE',
                        'criterio' => 'Gira correctamente sin dificultad. No presenta daños visibles.',
                        'estado' => $answers['condicion_equipo_volante_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_volante_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ASIENTO',
                        'criterio' => 'En buen estado, sin roturas ni desgaste excesivo. Ajuste funcional y cinturón de seguridad operativo.',
                        'estado' => $answers['condicion_equipo_asiento_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_asiento_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'RELOJES INDICADORES',
                        'criterio' => 'Todos los indicadores funcionan correctamente (combustible, temperatura, presión de aceite, horómetro, etc.).',
                        'estado' => $answers['condicion_equipo_relojes_indicadores_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_relojes_indicadores_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'FRENO',
                        'criterio' => 'Responde de manera efectiva al aplicar presión. Sin ruidos extraños ni recorrido excesivo del pedal.',
                        'estado' => $answers['condicion_equipo_freno_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_freno_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'FRENO DE EMERGENCIA',
                        'criterio' => 'Se activa correctamente y mantiene el montacargas fijo.',
                        'estado' => $answers['condicion_equipo_freno_emergencia_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_freno_emergencia_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'CARRO PORTAHORQUILLAS',
                        'criterio' => 'Sin deformaciones ni daños estructurales. Movimiento fluido y seguro.',
                        'estado' => $answers['condicion_equipo_carro_portahorquillas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_carro_portahorquillas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'INCLINACION DE HORQUILLAS',
                        'criterio' => 'Se inclina sin obstrucciones ni movimientos bruscos.',
                        'estado' => $answers['condicion_equipo_inclinacion_horquillas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_inclinacion_horquillas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'SUBIR Y BAJAR HORQUILLAS',
                        'criterio' => 'Movimiento fluido y sin bloques.',
                        'estado' => $answers['condicion_equipo_subir_bajar_horquillas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_subir_bajar_horquillas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ESTADO Y SEGURO DE HORQUILLAS',
                        'criterio' => 'No presenta fisuras, grietas, cortaduras, chipotes, ni desgaste excesivo.',
                        'estado' => $answers['condicion_equipo_estado_seguro_horquillas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_estado_seguro_horquillas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ETIQUETA DE CARGA MÁXIMA',
                        'criterio' => 'Visible y en buenas condiciones. No esta borrosa ni deteriorada.',
                        'estado' => $answers['condicion_equipo_etiqueta_carga_maxima_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_etiqueta_carga_maxima_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'SEÑALAMIENTO EN PALANCAS',
                        'criterio' => 'No estas desgastadas.',
                        'estado' => $answers['condicion_equipo_senalamiento_palancas_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_senalamiento_palancas_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ETIQUETA DE TIPO COMBUSTIBLE',
                        'criterio' => 'Visible y en buen estado.',
                        'estado' => $answers['condicion_equipo_etiqueta_tipo_combustible_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_etiqueta_tipo_combustible_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'MANUAL DE PROCEDIMIENTO',
                        'criterio' => 'Disponible en la unidad.',
                        'estado' => $answers['condicion_equipo_manual_procedimiento_estado'] ?? '',
                        'observaciones' => $answers['condicion_equipo_manual_procedimiento_observaciones'] ?? '',
                    ],
                ];
            @endphp
            
            @foreach($condicionEquipoRows as $row)
                <tr>
                    <!-- COLUMNA 1 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        font-weight: bold;
                        text-align: left;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['titulo'] }}
                    </td>
            
                    <!-- COLUMNA 2 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        text-align: left;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['criterio'] }}
                    </td>
            
                    <!-- COLUMNA 3 -->
                    <td style="
                        text-align: center;
                        font-weight: bold;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['estado'] === 'Buen Estado' ? '✔' : '' }}
                    </td>
            
                    <!-- COLUMNA 4 -->
                    <td style="
                        text-align: center;
                        font-weight: bold;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['estado'] === 'Mal Estado' ? 'X' : '' }}
                    </td>
            
                    <!-- COLUMNA 5 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['observaciones'] }}
                    </td>
                </tr>
            @endforeach
        </table>

        <!-- TABLA SISTEMA DE SEGURIDAD -->
        <table class="header-table main-table" style="
            margin-top: 8px;
            width: 100%;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:22%; padding:0; border:none;"></td>
                <td style="width:35%; padding:0; border:none;"></td>
                <td style="width:9%; padding:0; border:none;"></td>
                <td style="width:9%; padding:0; border:none;"></td>
                <td style="width:25%; padding:0; border:none;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td colspan="5" style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    SISTEMA DE SEGURIDAD
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td rowspan="2" colspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    DESCRIPCIÓN
                </td>
        
                <td colspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    OPCIÓN
                </td>
        
                <td rowspan="2" style="
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    OBSERVACIONES
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    BUEN ESTADO
                </td>
        
                <td style="
                    font-weight: bold;
                    text-align: center;
                    height: 5px;
                    padding: 0px 2px;
                ">
                    MAL ESTADO
                </td>
            </tr>
        
            @php
                $sistemaSeguridadRows = [
                    [
                        'titulo' => 'TORRETA',
                        'criterio' => 'Funciona correctamente.',
                        'estado' => $answers['sistema_seguridad_torreta_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_torreta_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ESPEJOS',
                        'criterio' => 'No están rotos ni sucios. Proporcionan una visión clara del entorno.',
                        'estado' => $answers['sistema_seguridad_espejos_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_espejos_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'CLAXÓN',
                        'criterio' => 'Funciona adecuadamente y titne una buena potencia sonora.',
                        'estado' => $answers['sistema_seguridad_claxon_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_claxon_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'CINTURON DE SEGURIDAD',
                        'criterio' => 'Buen estado de las correas y seguros, se ajusta correctamente.',
                        'estado' => $answers['sistema_seguridad_cinturon_seguridad_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_cinturon_seguridad_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'EXTINTOR',
                        'criterio' => 'Presión adecuada, sin caducar y en sitio correcto.',
                        'estado' => $answers['sistema_seguridad_extintor_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_extintor_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ALARAMA DE REVERSA',
                        'criterio' => 'Emite un sonido fuerte y claro al retroceder.',
                        'estado' => $answers['sistema_seguridad_alarma_reversa_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_alarma_reversa_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'FAROS DELANTEROS',
                        'criterio' => 'Funciona correctamente y brindan iluminación adecuada.',
                        'estado' => $answers['sistema_seguridad_faros_delanteros_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_faros_delanteros_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'FAROS Y CUARTOS TRASEROS',
                        'criterio' => 'Funcionan correctamente y están libres de daños.',
                        'estado' => $answers['sistema_seguridad_faros_cuartos_traseros_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_faros_cuartos_traseros_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'CADENA',
                        'criterio' => 'en buen estado, sin desgaste excesivo ni oxidación.',
                        'estado' => $answers['sistema_seguridad_cadena_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_cadena_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ETIQUETA DE PUNTO DE IZAJE, INDICADOR DE VELOCIDAD MÁXIMA',
                        'criterio' => 'Las etiquetas estan en buen estado y son legibles.',
                        'estado' => $answers['sistema_seguridad_etiquetas_izaje_velocidad_estado'] ?? '',
                        'observaciones' => $answers['sistema_seguridad_etiquetas_izaje_velocidad_observaciones'] ?? '',
                    ],
                ];
            @endphp
        
            @foreach($sistemaSeguridadRows as $row)
                <tr>
                    <!-- COLUMNA 1 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        font-weight: bold;
                        text-align: left;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['titulo'] }}
                    </td>
        
                    <!-- COLUMNA 2 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        text-align: left;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['criterio'] }}
                    </td>
        
                    <!-- COLUMNA 3 -->
                    <td style="
                        text-align: center;
                        font-weight: bold;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['estado'] === 'Buen Estado' ? '✔' : '' }}
                    </td>
        
                    <!-- COLUMNA 4 -->
                    <td style="
                        text-align: center;
                        font-weight: bold;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['estado'] === 'Mal Estado' ? 'X' : '' }}
                    </td>
        
                    <!-- COLUMNA 5 -->
                    <td style="
                        height: 10px;
                        padding: 0px 2px;
                        vertical-align: middle;
                        font-size: 6px;
                    ">
                        {{ $row['observaciones'] }}
                    </td>
                </tr>
            @endforeach
        </table>

        <!-- TABLA SISTEMA DE REFRIGERACIÓN -->
        <table class="header-table main-table" style="margin-top: 8px; width: 100%;">

            <tr style="height:0; line-height:0;">
                <td style="width:22%; padding:0; border:none;"></td>
                <td style="width:35%; padding:0; border:none;"></td>
                <td style="width:9%; padding:0; border:none;"></td>
                <td style="width:4.5%; padding:0; border:none;"></td>
                <td style="width:4.5%; padding:0; border:none;"></td>
                <td style="width:25%; padding:0; border:none;"></td>
            </tr>
        
            <tr>
                <td colspan="6" style="font-weight:bold; text-align:center; height:5px; padding:0px 2px;">
                    SISTEMA DE REFRIGERACIÓN
                </td>
            </tr>
        
            <tr>
                <td rowspan="2" colspan="2" style="font-weight:bold; text-align:center; vertical-align:middle; height:5px; padding:0px 2px;">
                    DESCRIPCIÓN
                </td>
                <td colspan="3" style="font-weight:bold; text-align:center; height:5px; padding:0px 2px;">
                    OPCIÓN
                </td>
                <td rowspan="2" style="font-weight:bold; text-align:center; vertical-align:middle; height:5px; padding:0px 2px;">
                    OBSERVACIONES
                </td>
            </tr>
        
            <tr>
                <td style="font-weight:bold; text-align:center; height:5px; padding:0px 2px;">A NIVEL</td>
                <td colspan="2" style="font-weight:bold; text-align:center; height:5px; padding:0px 2px;">LE FALTA</td>
            </tr>
        
            @php
                $sistemaRefrigeracionNivelRows = [
                    [
                        'titulo' => 'ANTICONGELANTE',
                        'criterio' => 'Nivel adecuado y sin fugas.',
                        'estado' => $answers['sistema_refrigeracion_anticongelante_estado'] ?? '',
                        'observaciones' => $answers['sistema_refrigeracion_anticongelante_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'SISTEMA HIDRAULICO (PISTÓN/MANGUERAS)',
                        'criterio' => 'Sin fugas ni daños visibles en pistón o mangueras. Revisión de conexiones y limpieza.',
                        'estado' => $answers['sistema_refrigeracion_sistema_hidraulico_estado'] ?? '',
                        'observaciones' => $answers['sistema_refrigeracion_sistema_hidraulico_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ACEITE DE TRANSMISIÓN AUTOMATICA',
                        'criterio' => 'Nivel adecuado y sin fugas. No presenta olores extraños.',
                        'estado' => $answers['sistema_refrigeracion_aceite_transmision_automatica_estado'] ?? '',
                        'observaciones' => $answers['sistema_refrigeracion_aceite_transmision_automatica_observaciones'] ?? '',
                    ],
                    [
                        'titulo' => 'ACEITE DE MOTOR',
                        'criterio' => 'Nivel adecuado y sin fugas. Sin presencia de residuos o contaminación.',
                        'estado' => $answers['sistema_refrigeracion_aceite_motor_estado'] ?? '',
                        'observaciones' => $answers['sistema_refrigeracion_aceite_motor_observaciones'] ?? '',
                    ],
                ];
        
                $fugasEstado = $answers['sistema_refrigeracion_fugas_estado'] ?? '';
                $fugasObs = $answers['sistema_refrigeracion_fugas_observaciones'] ?? '';
        
                $manguerasEstado = $answers['sistema_refrigeracion_mangueras_estado'] ?? '';
                $manguerasObs = $answers['sistema_refrigeracion_mangueras_observaciones'] ?? '';
        
                $taponEstado = $answers['sistema_refrigeracion_tapon_combustibles_estado'] ?? '';
                $taponObs = $answers['sistema_refrigeracion_tapon_combustibles_observaciones'] ?? '';
            @endphp
        
            @foreach($sistemaRefrigeracionNivelRows as $row)
                <tr>
                    <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:left; vertical-align:middle; font-size:6px;">
                        {{ $row['titulo'] }}
                    </td>
        
                    <td style="height:10px; padding:0px 2px; text-align:left; vertical-align:middle; font-size:6px;">
                        {{ $row['criterio'] }}
                    </td>
        
                    <td style="text-align:center; font-weight:bold; vertical-align:middle; font-size:6px;">
                        {{ $row['estado'] === 'A Nivel' ? '✔' : '' }}
                    </td>

                    <td colspan="2" style="text-align:center; font-weight:bold; vertical-align:middle; font-size:6px;">
                        {{ $row['estado'] === 'Le Falta' ? 'X' : '' }}
                    </td>
        
                    <td style="height:10px; padding:0px 2px; vertical-align:middle; font-size:6px;">
                        {{ $row['observaciones'] }}
                    </td>
                </tr>
            @endforeach
        
            <!-- FUGAS FILA 8 -->
            <tr>
                <td rowspan="2" style="height:10px; padding:0px 2px; font-weight:bold; text-align:left; vertical-align:middle; font-size:6px;">
                    FUGAS DE ACEITE/FLUIDO/COMBUSTIBLE/AGUA
                </td>
        
                <td rowspan="2" style="height:10px; padding:0px 2px; text-align:left; vertical-align:middle; font-size:6px;">
                    No se detectan fugas en el sistema.
                </td>
        
                <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:center; vertical-align:middle; font-size:6px;">
                    SIN FUGAS
                </td>
        
                <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:center; vertical-align:middle; font-size:6px;">
                    FUGA MENOR
                </td>
        
                <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:center; vertical-align:middle; font-size:6px;">
                    FUGA MAYOR
                </td>
        
                <td rowspan="2" style="height:10px; padding:0px 2px; vertical-align:middle; font-size:6px;">
                    {{ $fugasObs }}
                </td>
            </tr>
        
            <!-- FUGAS FILA 9 -->
            <tr>
                <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $fugasEstado === 'Sin Fuga' ? '✔' : '' }}
                </td>
        
                <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $fugasEstado === 'Fuga Menor' ? 'X' : '' }}
                </td>

                <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $fugasEstado === 'Fuga Mayor' ? 'X' : '' }}
                </td>
            </tr>
        
            <!-- MANGUERAS FILA 10 -->
            <tr>
                <td rowspan="2" style="height:10px; padding:0px 2px; font-weight:bold; text-align:left; vertical-align:middle; font-size:6px;">
                    MANGUERAS
                </td>
        
                <td rowspan="2" style="height:10px; padding:0px 2px; text-align:left; vertical-align:middle; font-size:6px;">
                    Sin rayones, golpes, grietas ni fugas. Conexiones firmes y en buen estado.
                </td>
        
                <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:center; vertical-align:middle; font-size:6px;">
                    BUEN ESTADO
                </td>
        
                <td colspan="2" style="height:10px; padding:0px 2px; font-weight:bold; text-align:center; vertical-align:middle; font-size:6px;">
                    MAL ESTADO
                </td>
        
                <td rowspan="2" style="height:10px; padding:0px 2px; vertical-align:middle; font-size:6px;">
                    {{ $manguerasObs }}
                </td>
            </tr>
        
            <!-- MANGUERAS FILA 11 -->
            <tr>
                <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $manguerasEstado === 'Buen Estado' ? '✔' : '' }}
                </td>
        
                <td colspan="2" style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $manguerasEstado === 'Mal Estado' ? 'X' : '' }}
                </td>
            </tr>
        
            <!-- TAPÓN FILA 12 -->
            <tr>
                <td style="height:10px; padding:0px 2px; font-weight:bold; text-align:left; vertical-align:middle; font-size:6px;">
                    TAPÓN DE COMBUSTIBLE
                </td>
        
                <td style="height:10px; padding:0px 2px; text-align:left; vertical-align:middle; font-size:6px;">
                    Correctamente colocado y en bunas condiciones (Sin trapos u objetos improvisados).
                </td>
        
                <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $taponEstado === 'Buen Estado' ? '✔' : '' }}
                </td>
        
                <td colspan="2" style="text-align: center; font-weight: bold; vertical-align: middle; font-size: 6px;">
                    {{ $taponEstado === 'Mal Estado' ? 'X' : '' }}
                </td>
        
                <td style="height:10px; padding:0px 2px; vertical-align:middle; font-size:6px;">
                    {{ $taponObs }}
                </td>
            </tr>
        </table>

        @php
            $notas = $answers['notas'] ?? '';
        
            $nombreInspector = $answers['nombre_inspector'] ?? '';
        
            $firmaInspector = $answers['firma_inspector'] ?? '';
        
            $firmaInspectorSrc = null;
        
            if (!empty($firmaInspector)) {
                $firmaPath = storage_path('app/public/' . $firmaInspector);
        
                if (file_exists($firmaPath)) {
                    $firmaInspectorSrc =
                        'data:image/png;base64,' .
                        base64_encode(file_get_contents($firmaPath));
                }
            }
        @endphp
        
        <!-- TABLA FINAL -->
        <table class="header-table main-table" style="
            margin-top: 8px;
            width: 100%;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:15%; padding:0; border:none;"></td>
                <td style="width:35%; padding:0; border:none;"></td>
                <td style="width:25%; padding:0; border:none;"></td>
                <td style="width:25%; padding:0; border:none;"></td>
            </tr>
        
            <tr>
                <!-- NOTAS -->
                <td style="
                    font-size:6px;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px 4px;
                ">
                    NOTAS:
                </td>
        
                <!-- DATO NOTAS -->
                <td style="
                    font-size:6px;
                    vertical-align:middle;
                    padding:2px 4px;
                ">
                    {{ $notas }}
                </td>
        
                <!-- TITULO FIRMA -->
                <td style="
                    font-size:6px;
                    font-weight:bold;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px 4px;
                ">
                    REALIZÓ INSPECCIÓN<br>
                    (Nombre y Firma):
                </td>
        
                <!-- NOMBRE + FIRMA -->
                <td style="
                    font-size:6px;
                    text-align:center;
                    vertical-align:middle;
                    padding:2px 4px;
                ">
                    @if($firmaInspectorSrc)
                        <img
                            src="{{ $firmaInspectorSrc }}"
                            style="
                                width:120px;
                                height:45px;
                                object-fit:contain;
                                display:block;
                                margin:0 auto 2px auto;
                            "
                        >
                    @endif
        
                    <div>
                        {{ $nombreInspector }}
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>