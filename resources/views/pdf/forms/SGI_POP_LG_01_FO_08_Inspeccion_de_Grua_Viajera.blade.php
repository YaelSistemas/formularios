<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Grúa Viajera' }}</title>

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

        .right-cell {
            font-weight: bold;
            text-align: center;
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
            font-size: 8px;
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
            font-size: 8px;
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
            font-size: 8px;
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

        .responsable-table {
            width: 92%;
            margin: 15px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }
        
        .responsable-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .responsable-label {
            width: 18%;
            text-align: right;
            padding-right: 8px !important;
            font-weight: bold;
            line-height: 1.2;
            vertical-align: middle;
        }
        
        .responsable-label-text {
            display: block;
            position: relative;
            top: 5px;
        }
        
        .responsable-line-cell {
            width: 32%;
            padding-right: 18px !important;
        }
        
        .responsable-line {
            width: 100%;
            height: 30px;
            position: relative;
            border-bottom: 1px solid #000;
        }
        
        .responsable-value {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 2px;
            text-align: center;
            font-size: 8px;
            font-weight: normal;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .responsable-signature {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 2px;
            margin: auto;
            max-width: 120px;
            max-height: 28px;
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

        $tallerValor = data_get($answers, 'taller', '') ?: '';

        $nombreResponsableInspeccion =
            data_get($answers, 'nombre_responsable_inspeccion', '') ?: '';

        $firmaResponsableInspeccion =
            data_get($answers, 'firma_responsable_inspeccion', '') ?: '';

        $tablaGrua = data_get($answers, 'tabla_grua_viajera', []);
        $tablaGrua = collect(data_get($answers, 'tabla_grua_viajera', []))
            ->filter(fn($item) => !empty(array_filter($item, fn($value) => $value !== null && $value !== '')))
            ->values();
    @endphp

    @foreach($tablaGrua as $pageIndex => $rowGrua)
        @php

            $fechaUsoRaw = data_get($rowGrua, 'fecha_uso', '');
    
            $fechaUso = '';
            
            if (!empty($fechaUsoRaw)) {
                try {
                    $fechaUso = \Carbon\Carbon::parse($fechaUsoRaw)->format('d/m/Y');
                } catch (\Throwable $e) {
                    $fechaUso = $fechaUsoRaw;
                }
            }

            $rows = [
                [
                    'descripcion' => 'Número de Revisión',
                    'estado' => data_get($rowGrua, 'numero_revision', ''),
                    'observaciones' => '',
                ],
                [
                    'descripcion' => 'Capacidad',
                    'estado' => data_get($rowGrua, 'capacidad', ''),
                    'observaciones' => data_get($rowGrua, 'observaciones_capacidad', ''),
                ],
                [
                    'descripcion' => 'Revisión total del gancho (pestillo, grietas, fatiga, sin desgaste)',
                    'estado' => data_get($rowGrua, 'grua_1_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_1_observaciones', ''),
                ],
                [
                    'descripcion' => 'Revisión de cable (sin roturas, aplastamientos, cocas, desgaste)',
                    'estado' => data_get($rowGrua, 'grua_2_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_2_observaciones', ''),
                ],
                [
                    'descripcion' => 'Inspección que cable no se enrede en el tambor',
                    'estado' => data_get($rowGrua, 'grua_3_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_3_observaciones', ''),
                ],
                [
                    'descripcion' => 'Estado físico y operación de botonera (Limpieza, estado físico y sin falso contacto, carcasa de botonera sin fisuras)',
                    'estado' => data_get($rowGrua, 'grua_4_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_4_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar botones de elevación y descenso (Limpieza, estado físico y sin falso contacto)',
                    'estado' => data_get($rowGrua, 'grua_5_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_5_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar botones de movimientos a través del puente izq.- der. (Limpieza, estado físico y sin falso contacto)',
                    'estado' => data_get($rowGrua, 'grua_6_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_6_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar botones de movimiento longitudinal o transversal (Limpieza, estado físico y sin falso contacto)',
                    'estado' => data_get($rowGrua, 'grua_7_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_7_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar botón de paro de emergencia (Limpieza, estado físico y sin falso contacto)',
                    'estado' => data_get($rowGrua, 'grua_8_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_8_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar botones adicionales si aplican (Limpieza, estado físico y sin falso contacto)',
                    'estado' => data_get($rowGrua, 'grua_9_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_9_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar alarma acústica',
                    'estado' => data_get($rowGrua, 'grua_10_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_10_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar luz de torreta o estroboótica',
                    'estado' => data_get($rowGrua, 'grua_11_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_11_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar final de carrera de puente',
                    'estado' => data_get($rowGrua, 'grua_12_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_12_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar final de carrera de carro',
                    'estado' => data_get($rowGrua, 'grua_13_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_13_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar final de cerrera de gancho',
                    'estado' => data_get($rowGrua, 'grua_14_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_14_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar funcionamiento de medidor de carga',
                    'estado' => data_get($rowGrua, 'grua_15_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_15_observaciones', ''),
                ],
                [
                    'descripcion' => 'Comprobar los accesos (que no haya obstáculos)',
                    'estado' => data_get($rowGrua, 'grua_16_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_16_observaciones', ''),
                ],
                [
                    'descripcion' => 'Revisar que los topes de grúa se encuentren bien',
                    'estado' => data_get($rowGrua, 'grua_17_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_17_observaciones', ''),
                ],
                [
                    'descripcion' => 'Revisar buen estado de todas las advertencias de riegos',
                    'estado' => data_get($rowGrua, 'grua_18_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_18_observaciones', ''),
                ],
                [
                    'descripcion' => 'Ruidos extraños',
                    'estado' => data_get($rowGrua, 'grua_19_estado', ''),
                    'observaciones' => data_get($rowGrua, 'grua_19_observaciones', ''),
                ],
            ];
        @endphp
    
        <div class="sheet" style="{{ $pageIndex > 0 ? 'page-break-before: always;' : '' }}">

        <!-- HEADER -->
        <table class="header-table">
        
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
        
                <!-- LOGO -->
                <td style="width:25%; padding:0; border:none; height:0;"></td>
        
                <!-- CENTRO -->
                <td style="width:45%; padding:0; border:none; height:0;"></td>
        
                <!-- DERECHA -->
                <td style="width:30%; padding:0; border:none; height:0;"></td>
        
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td rowspan="4" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>
        
                <td class="center-cell row-1-center">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>
        
                <td class="right-cell">
                    CÓDIGO: SGI-POP-LG-01-FO-08
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>
        
                <td class="right-cell">
                    FECHA DE EMISIÓN: 27/03/2025
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td rowspan="2" class="center-cell">
                    INSPECCIÓN DE GRÚA VIAJERA
                </td>
            
                <td class="right-cell">
                    REVISIÓN: 02
                </td>
            </tr>
            
            <!-- FILA 4 -->
            <tr>
                <td class="right-cell">
                    PÁGINA: {{ str_pad($pageIndex + 1, 2, '0', STR_PAD_LEFT) }}
                </td>
            </tr>
        
        </table>

        <!-- DATOS -->
        <div class="inspection-area">
            <div class="inspection-row">
                <div class="inspection-item left">
                    <span class="inspection-label">U.S./Empresa:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $tallerValor }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>

                <div class="inspection-item right"></div>
            </div>
        </div>

        <!-- RESPONSABLE DE INSPECCIÓN -->
        <table class="responsable-table">
            <tr>
                <!-- ETIQUETA NOMBRE -->
                <td class="responsable-label">
                    <div class="responsable-label-text">
                        Nombre del colaborador<br>
                        que inspeccionó:
                    </div>
                </td>
        
                <!-- LÍNEA NOMBRE -->
                <td class="responsable-line-cell">
                    <div class="responsable-line">
                        <span class="responsable-value">
                            {{ $nombreResponsableInspeccion }}
                        </span>
                    </div>
                </td>
        
                <!-- ETIQUETA FIRMA -->
                <td class="responsable-label">
                    <div class="responsable-label-text">
                        Firma del colaborador<br>
                        que inspeccionó:
                    </div>
                </td>
        
                <!-- LÍNEA FIRMA -->
                <td class="responsable-line-cell">
                    <div class="responsable-line">
                        @if(!empty($firmaResponsableInspeccion))
                            <img
                                src="{{ public_path('storage/' . $firmaResponsableInspeccion) }}"
                                class="responsable-signature">
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- TEXTO -->
        <div class="inspection-area">
            <div class="inspection-row">
                <div class="inspection-item left">
                    <span class="inspection-label" style="font-size: 8px;"> Considerar los siguientes criterios de acuerdo al estado de la grúa viajera:</span>
                </div>

                <div class="inspection-item right">
                    <span class="inspection-label" style="font-size: 8px; position: relative; top: -5px;">
                        ( S ) Satisfactorio 
                        &nbsp;&nbsp;
                        ( X ) No Satisfactorio
                        &nbsp;&nbsp;
                        ( N ) No Aplica</span>
                </div>
            </div>
        </div>

        <!-- TABLA --> 
        <table
            style="width: 92%; margin: 10px auto 0 auto; border-collapse: collapse;">
            <!-- CONTROL DE ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:42%; padding:0; border:none; height:0;"></td>
                <td style="width:20%; padding:0; border:none; height:0;"></td>
                <td style="width:38%; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr>
                <td rowspan="3" style="border:1px solid #000; height:20px; text-align:center; vertical-align:middle; font-weight:bold; font-size:9px; background:#d1d5db;">
                    Descripcion
                </td>
        
                <td style="border:1px solid #000; height:20px; text-align:center; vertical-align:middle; font-weight:bold; font-size:9px; background:#d1d5db;">
                    Fecha de uso
                </td>
        
                <td rowspan="3" style="border:1px solid #000; height:20px; text-align:center; vertical-align:middle; font-weight:bold; font-size:9px; background:#d1d5db;">
                    Observaciones
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <td style="border:1px solid #000; height:20px; text-align:center; vertical-align:middle; font-size:8px;">
                    {{ $fechaUso }}
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr>
                <td style="border:1px solid #000; height:20px; text-align:center; vertical-align:middle; font-weight:bold; font-size:9px; background:#d1d5db;">
                    Estado
                </td>
            </tr>
        
            @foreach($rows as $row)
                <tr>
                    @if($row['descripcion'] === 'Número de Revisión')
                
                        <!-- DESCRIPCION -->
                        <td style="border:1px solid #000; height:20px; font-size:8px; padding:4px; vertical-align:middle; font-weight:bold;">
                            {{ $row['descripcion'] }}
                        </td>
                
                        <!-- ESTADO + OBSERVACIONES JUNTAS -->
                        <td colspan="2" style="border:1px solid #000; height:20px; font-size:8px; padding:4px; text-align:center; vertical-align:middle;">
                            {{ $row['estado'] }}
                        </td>

                    @else
                
                        <!-- DESCRIPCION -->
                        <td style="border:1px solid #000; height:20px; font-size:8px; padding:4px; vertical-align:middle;">
                            {{ $row['descripcion'] }}
                        </td>
                
                        <!-- ESTADO -->
                        <td style="border:1px solid #000; height:20px; font-size:8px; padding:4px; text-align:center; vertical-align:middle;">
                            {{ $row['estado'] }}
                        </td>
                
                        <!-- OBSERVACIONES -->
                        <td style="border:1px solid #000; height:20px; font-size:8px; padding:4px; vertical-align:middle;">
                            {{ $row['observaciones'] }}
                        </td>
                
                    @endif
                </tr>
            @endforeach
        </table>

    </div>
    @endforeach
</body>
</html>
