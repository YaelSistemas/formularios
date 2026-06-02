<!doctype html> 
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Boleta de Observaciones' }}</title>

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

        .main-table {
            width: 99.6%;
            margin-top: 8px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table td {
            border: 1px solid #000;
            height: 12px;
            padding: 2px 4px;
            vertical-align: middle;
            font-size: 8px;
            line-height: 1.1;
        }

        .bold-center {
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
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

    $fechaRegistro =
        optional($submission->created_at)->format('d/m/Y') ?: '';

    $tallerValor =
        data_get($answers, 'taller', '') ?: '';
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
                CODIFICACIÓN: SST-PGI-TA-01-FO-01
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
                BOLETA DE OBSERVACIONES
            </td>

            <td colspan="2" class="right-cell">
                NÚMERO DE REVISIÓN: 06
            </td>
        </tr>
    </table>

    <!-- TABLA BASE 14 x 25 -->
    <table class="main-table">
    
        <!-- ANCHOS DE COLUMNAS -->
        <tr style="height:0; line-height:0;">
        
            <!-- Columna 1 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 2 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 3 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 4 -->
            <td style="width:10.2%; padding:0; border:none; height:0;"></td>
            <!-- Columna 5 -->
            <td style="width:10.2%; padding:0; border:none; height:0;"></td>
            <!-- Columna 6 -->
            <td style="width:9.7%; padding:0; border:none; height:0;"></td>
            <!-- Columna 7 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 8 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
            <!-- Columna 9 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 10 -->
            <td style="width:18.25%; padding:0; border:none; height:0;"></td>
            <!-- Columna 11 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 12 -->
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <!-- Columna 13 -->
            <td style="width:18.25%; padding:0; border:none; height:0;"></td>
            <!-- Columna 14 -->
            <td style="width:1%; padding:0; border:none; height:0;"></td>
        
        </tr>
    
        <!-- FILA 1 -->
        <tr>
            <!-- Columna 1 unida -->
            <td rowspan="23" style="border-right:none;"></td>
    
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 7 unida -->
            <td rowspan="23" style="border-left:none;"></td>
    
            <!-- Columna 8 unida -->
            <td rowspan="23" style="border-right:none;"></td>
    
            <!-- Columnas 9 a 13 -->
            <td colspan="5" class="bold-center" style="border-bottom:none; border-left:none; border-right:none;">
                Falta Cometida
            </td>
    
            <!-- Columna 14 unida -->
            <td rowspan="23" style="border-left:none;"></td>
        </tr>
    
        <!-- FILA 2 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-bottom:none; border-left:none;"></td>
        
            <!-- Columnas 9 y 10 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Acto Inseguro
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 12 y 13 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Desviaciones
            </td>
        </tr>
    
        <!-- FILA 3 -->
        <tr>
            <!-- Columna 2 -->
            <td class="bold-center" style="border-top:none; border-right:none; border-bottom:none; border-left:none;">
                Fecha:
            </td>
    
            <!-- Columna 3 -->
            <td class="center" style="font-weight:bold;">
                {{ $fechaRegistro }}
            </td>
    
            <!-- Columna 4 -->
            <td style="border:none;"></td>
    
            <!-- Columna 5 -->
            <td class="bold-center" style="border-top:none; border-right:none; border-bottom:none; border-left:none;">
                Taller
            </td>
    
            <!-- Columna 6 -->
            <td class="center" style="font-weight:bold;">
                {{ $tallerValor }}
            </td>
    
            <!-- Columna 9 -->
            <td style="border:none;"></td>

            <!-- Columna 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                BROMAS O DISTRACCIONES EN ÁREA DE TRABAJO
            </td>
            
            <!-- Columna 11 -->
            <td style="border:none;"></td>
            
            <!-- Columna 12 -->
            <td style="border:none;"></td>
            
            <!-- Columna 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO APLICAR PROCEDIMIENTOS DE SEGURIDAD
            </td>
        </tr>

        <!-- FILA 4 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO PORTAR EPP ESPECÍFICO POR ACTIVIDAD
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO APLICAR PROCEDIMIENTOS OPERATIVOS
            </td>
        </tr>

        <!-- FILA 5 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                TRABAJAR CON EQUIPO EN MOVIMIENTO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO PORTAR CREDENCIALES O DOCUMENTOS DE ACCESO
            </td>
        </tr>

        <!-- FILA 6 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-20px; text-align:center;">
                    PLANTA O ÁREA DE TRABAJO:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:0px; text-align:center; font-weight:bold;">
                    {{ data_get($answers, 'planta_area_trabajo', '') }}
                </div>
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                USO DE HERRAMIENTAS EN MAL ESTADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO TRAER TARJETA Y CANDADO P/BLOQUEO
            </td>
        </tr>

        <!-- FILA 7 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                EXCESO DE VELOCIDAD O MOVIMIENTO INAPROPIADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO INFORMAR SITUACIONES ANORMALES O RIESGOS DETECTADOS
            </td>
        </tr>

        <!-- FILA 8 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                TRABAJAR EN ALTURAS SIN MEDIDAS DE SEGURIDAD
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO DESBLOQUEAR EQUIPOS DE LOS CLIENTES
            </td>
        </tr>

        <!-- FILA 9 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-3px; text-align:center;">
                    NOMBRE DEL PERSONAL OBSERVADO:
                </div>
            </td>
            
            <!-- Columnas 4 y 5 -->
            <td colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:0px; text-align:center; font-weight:bold;">
                    {{ data_get($answers, 'nombre_personal_observado', '') }}
                </div>
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>

            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                USO INADECUADO DE EPP
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
                OTRO, ESPECIFIQUE
            </td>
        </tr>

        <!-- FILA 10 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                NO REALIZAR BLOQUEOS Y ETIQUETADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 11 -->
        <tr>
            <!-- Columnas 2 a 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- Columnas 4 y 5-->
            <td colspan="2" class="bold-center" style="border:none; font-size:6px; text-align:center;">
                ACTO INSEGURO / CONDICIÓN PELIGROSA / DESVIACIÓN
            </td>
        
            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                DAÑO A LA MAQUINARIA
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 12 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-29px; text-align:center;">
                    TIPO DE OBSERVACION:
                </div>
            </td>
            
            <!-- Columnas 4 y 5 -->
            <td colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:0px; text-align:center; font-weight:bold;">
                    {{ data_get($answers, 'tipo_observacion', '') }}
                </div>
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                DAÑO A INSTALACIONES
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 13 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                OTROS, ESPECIFIQUE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- 12 -->
            <td style="border:none;"></td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;">
            </td>
        </tr>

        <!-- FILA 14 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-bottom:none; border-left:none;"></td>
        
            <!-- Columnas 9 y 10 -->
            <td colspan="2" class="bold-center" style="border:none; text-align:left;">
                Condición Peligrosa
            </td>
        
            <!-- Columna 11 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 12 y 13 -->
            <td colspan="2" class="bold-center">
                {{ data_get($answers, 'tipo_observacion', '') }}
            </td>
        </tr>

        <!-- FILA 15 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-5px; text-align:center;">
                    DESCRIPCIÓN DE LA OBSERVACION:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td rowspan="8" colspan="2" style="vertical-align:middle; text-align:center; font-size:8px; padding:4px;">
                {{ data_get($answers, 'descripcion_observacion', '') }}
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
            
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ÁREAS SIN DELIMITACIÓN O SEÑALIZACIÓN ADECUADA
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
            
            <!-- Columnas 12 y 13 -->
            <td rowspan="8" colspan="2" style="vertical-align:middle; text-align:center; font-size:8px; padding:4px;">
                {{ data_get($answers, 'acciones_preventivas_correctivas', '') }}
            </td>
        </tr>

        <!-- FILA 16 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>

            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                EQUIPOS O MAQUINARIA CON MANTENIMIENTO DEFICIENTE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 17 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                INSTALACIONES ELÉCTRICAS EXPUESTAS O EN MAL ESTADO
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 18 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                PISO RESBALADIZO O CON OBSTÁCULOS
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 19 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ILUMINACIÓN INSUFICIENTE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 20 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                ALMACENAMIENTO INADECUADO DE MATERIALES
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 21 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                FALTA DE SEÑALIZACIÓN DE EMERGENCIA O RUTAS DE EVACUACIÓN
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 22 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- 9 -->
            <td style="border:none;"></td>
            
            <!-- 10 -->
            <td style="border:none; font-size:7px; text-align:left;">
                OTROS, ESPECIFIQUE
            </td>
            
            <!-- 11 -->
            <td style="border:none;"></td>
        </tr>

        <!-- FILA 23 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        </tr>

        <!-- FILA 24 -->
        <tr>
            <!-- Columna 1 unida -->
            <td rowspan="14" style="border-right:none;"></td>
    
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 7 unida -->
            <td rowspan="14" style="border-left:none;"></td>
    
            <!-- Columna 8 unida -->
            <td rowspan="14" style="border-right:none;"></td>
    
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-bottom:none; border-left:none; border-right:none;"></td>
    
            <!-- Columna 14 unida -->
            <td rowspan="14" style="border-left:none;"></td>
        </tr>

        <!-- FILA 25 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:bottom;">
                <div style="position:relative; top:1px; left:-27px; text-align:center;">
                    ACCIÓN CORRECTIVA:
                </div>
            </td>
        
            <!-- Columnas 4 y 5 -->
            <td rowspan="4" colspan="2" style="border:1px solid #000; text-align:center; vertical-align:middle; font-size:7px; padding:4px; font-weight:bold;">
                {{ data_get($answers, 'acciones_preventivas_correctivas', '') }}
            </td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 9 a 12 -->
            <td style="border:none;">
            </td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;"></td>
        </tr>

        <!-- FILA 26 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 9 a 12 -->
            <td style="border:none;">
            </td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;"></td>
        </tr>
        
        <!-- FILA 27 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 9 a 12 -->
            <td style="border:none;">
            </td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;"></td>
        </tr>

        <!-- FILA 28 -->
        <tr>
            <!-- Columnas 2 y 3 -->
            <td colspan="2" style="border:none;"></td>

            <!-- 6 -->
            <td style="border:none;"></td>
        
            <!-- Columnas 9 a 12 -->
            <td style="border:none;">
            </td>
            
            <!-- 13 -->
            <td style="border:none; font-size:7px; text-align:left;"></td>
        </tr>

        <!-- FILA 29 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 30 -->
        <tr>
        
            @php
                $firmaReporta = data_get($answers, 'firma_reporta_observacion');
                $firmaReportaSrc = null;
        
                if ($firmaReporta) {
                    $firmaPath = storage_path(
                        'app/public/' . ltrim($firmaReporta, '/')
                    );
        
                    if (file_exists($firmaPath)) {
                        $firmaReportaSrc =
                            'data:image/png;base64,' .
                            base64_encode(file_get_contents($firmaPath));
                    }
                }
            @endphp
        
            <!-- Columnas 2, 3 y 4 -->
            <td rowspan="2" colspan="3" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:bottom; font-size:8px; font-weight:bold; padding-bottom:2px;">
                {{ data_get($answers, 'nombre_reporta_observacion', '') }}
            </td>
        
            <!-- Columnas 5 y 6 -->
            <td rowspan="2" colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:middle; padding:0;">
                @if($firmaReportaSrc)
                    <img src="{{ $firmaReportaSrc }}" style="
                        max-height:40px;
                        max-width:100px;
                        display:block;
                        margin:0 auto;
                        object-fit:contain;
                    ">
                @endif
        
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 31 -->
        <tr>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        
        </tr>

        <!-- FILA 32 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:top;">
                <div style="position:relative; top:0px; text-align:center;">
                    NOMBRE Y FIRMA DE QUIEN REPORTA OBSERVACIÓN
                </div>
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 33 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 34 -->
        <tr>
        
            @php
                $firmaObservado = data_get($answers, 'firma_observado');
                $firmaObservadoSrc = null;
        
                if ($firmaObservado) {
                    $firmaPath = storage_path(
                        'app/public/' . ltrim($firmaObservado, '/')
                    );
        
                    if (file_exists($firmaPath)) {
                        $firmaObservadoSrc =
                            'data:image/png;base64,' .
                            base64_encode(file_get_contents($firmaPath));
                    }
                }
            @endphp
        
            <!-- Columnas 2, 3 y 4 -->
            <td rowspan="2" colspan="3" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:bottom; font-size:8px; font-weight:bold; padding-bottom:2px;">
                {{ data_get($answers, 'nombre_observado', '') }}
            </td>
        
            <!-- Columnas 5 y 6 -->
            <td rowspan="2" colspan="2" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000; 
            text-align:center; vertical-align:middle; padding:0;">
                @if($firmaObservadoSrc)
                    <img src="{{ $firmaObservadoSrc }}" style="
                        max-height:40px;
                        max-width:100px;
                        display:block;
                        margin:0 auto;
                        object-fit:contain;
                    ">
                @endif
        
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 35 -->
        <tr>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        
        </tr>

        <!-- FILA 36 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border:none; text-align:center; font-size:7px; padding:0; vertical-align:top;">
                <div style="position:relative; top:0px; text-align:center;">
                    NOMBRE Y FIRMA DEL OBSERVADO
                </div>
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border:none;">
            </td>
        </tr>

        <!-- FILA 37 -->
        <tr>
            <!-- Columnas 2 a 6 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        
            <!-- Columnas 9 a 13 -->
            <td colspan="5" style="border-top:none; border-right:none; border-left:none; border-bottom:1px solid #000;">
            </td>
        </tr>

    </table>

</div>

</body>
</html>