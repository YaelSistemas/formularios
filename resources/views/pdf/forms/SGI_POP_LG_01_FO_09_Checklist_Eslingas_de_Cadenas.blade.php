<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist Eslingas de Cadenas' }}</title>

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
            width: 100%;
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
            width: 100%;
            margin: 22px auto 0 auto;
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
            width: 220px;
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

    $nombreColaborador =
        data_get($answers, 'nombre_colaborador_inspecciono', '') ?: '';
    
    $firmaColaborador =
        data_get($answers, 'firma_colaborador_inspecciono');
    
    $firmaColaboradorSrc = null;
    
    if ($firmaColaborador) {
        $pathFirma = storage_path('app/public/' . $firmaColaborador);
    
        if (file_exists($pathFirma)) {
            $firmaColaboradorSrc =
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
                CODIFICACIÓN: SGI-POP-LG-01-FO-09
            </td>
        </tr>

        <tr>
            <td colspan="2" class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>

            <td colspan="2" class="right-cell">
                FECHA DE EMISIÓN:
            </td>
        </tr>

        <tr>
            <td colspan="2" class="center-cell">
                CHECKLIST ESLINGAS DE CADENAS
            </td>

            <td colspan="2" class="right-cell">
                NÚMERO DE REVISIÓN:
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:15px;
    ">
        <tr>
            <td style="
                width:100%;
                vertical-align:top;
            ">
    
                <div class="inspection-area">
    
                    <!-- FILA 1 -->
                    <div style="
                        width:100%;
                        text-align:center;
                        font-size:0;
                    ">
    
                        <!-- TALLER -->
                        <div style="
                            display:inline-block;
                            width:45%;
                            text-align:center;
                            font-size:10px;
                            vertical-align:middle;
                        ">
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
    
                        <!-- FECHA -->
                        <div style="
                            display:inline-block;
                            width:45%;
                            text-align:center;
                            font-size:10px;
                            vertical-align:middle;
                        ">
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
    
                    </div>
    
                    <!-- FILA 2 -->
                    <div style="
                        width:100%;
                        text-align:center;
                        font-size:0;
                        margin-top:40px;
                    ">
    
                        <!-- NOMBRE -->
                        <div style="
                            display:inline-block;
                            width:45%;
                            text-align:center;
                            font-size:10px;
                            vertical-align:middle;
                        ">
                            <span class="inspection-label" style="
                                display:inline-block;
                                line-height:1.1;
                                text-align:center;
                                vertical-align:top;
                                margin-top:-10px;
                            ">
                                Nombre del colaborador<br>
                                que inspecciono:
                            </span>
    
                            <span class="inspection-line-wrap" style="
                                width:260px;
                            ">
                                <span class="inspection-value">
                                    {{ $nombreColaborador }}
                                </span>
    
                                <span class="inspection-underline"></span>
                            </span>
                        </div>
    
                        <!-- FIRMA -->
                        <div style="
                            display:inline-block;
                            width:45%;
                            text-align:center;
                            font-size:10px;
                            vertical-align:middle;
                        ">
                            <span class="inspection-label" style="
                                display:inline-block;
                                line-height:1.1;
                                text-align:center;
                                vertical-align:top;
                                margin-top:-10px;
                            ">
                                Firma del colaborador<br>
                                que inspecciono:
                            </span>
    
                            <span class="inspection-line-wrap" style="
                                height:20px;
                                width:260px;
                            ">
    
                                @if($firmaColaboradorSrc)
                                    <img
                                        src="{{ $firmaColaboradorSrc }}"
                                        style="
                                            position:absolute;
                                            left:50%;
                                            transform:translateX(-50%);
                                            bottom:0px;
                                            max-width:120px;
                                            max-height:35px;
                                            object-fit:contain;
                                            display:block;
                                        "
                                    >
                                @endif
    
                                <span class="inspection-underline"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="width:100%; text-align:left; margin-top:25px; margin-bottom:8px; font-size:9px; line-height:1.4;">
        Considerar los siguientes criterios de acuerdo al estado de la eslinga de cadena:
        <strong>SI</strong> /
        <strong>NO</strong>
    </div>

    @php
        $headers = [
            'N° de Eslinga',
            'Diámetro',
            'Capacidad',
            'Longitud',
            'Elongación Causada por Estiramiento',
            'Eslabones Distorsionados o Dañados',
            'Presenta Muescas o Estrías',
            'Presenta Corrosión General',
            'Eslabones Torcidos',
            'Trizaduras en Partes Soldadas',
            'Ojos o Eslabones Desgastados',
            'Se Realiza Revisión de Ganchos',
            'Cuenta con Seguro de Gancho',
            'Tiene Rotulación Carga Máxima',
            'Almacenamiento Correcto',
            'Libre de Aceite o Grasas (Libre de Químicos)',
            'Etiqueta Visible',
            'Cuenta con Certificado de Fabricante',
            'La Eslinga está en Buenas Condiciones',
        ];
    @endphp
    
    <!-- TABLA -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:5px;
        font-size:7px;
    ">
    
        <tr style="height:0; line-height:0;">
            <td style="width:4%; padding:0; border:none; height:0;"></td>  {{-- N° de Eslinga --}}
            <td style="width:4%; padding:0; border:none; height:0;"></td>  {{-- Diámetro --}}
            <td style="width:4%; padding:0; border:none; height:0;"></td>  {{-- Capacidad --}}
            <td style="width:4%; padding:0; border:none; height:0;"></td>  {{-- Longitud --}}
        
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <!-- FILA HEADERS -->
        <tr>
            @foreach ($headers as $header)
        
                @php
                    $boldHeaders = [
                        0,  // No. de Eslinga
                        1,  // Diámetro
                        2,  // Capacidad
                        3,  // Longitud
                        18, // La Eslinga está en Buenas Condiciones
                    ];
                @endphp
        
                <td style="
                    border:1px solid #000;
                    height:100px;
                    text-align:center;
                    vertical-align:middle;
                    padding:0;
                ">
                    <div style="
                        writing-mode:vertical-rl;
                        transform:rotate(270deg);
                        text-align:center;
                        white-space:normal;
                        line-height:1.1;
                        margin:auto;
                        width:100%;
                        font-size:10px;
                        font-weight:{{ in_array($loop->index, $boldHeaders) ? 'bold' : 'normal' }};
                    ">
                        {{ $header }}
                    </div>
                </td>
            @endforeach
        </tr>
    
        @php
            $filasEslingas = data_get($answers, 'tabla_checklist_eslingas_cadenas', []);
            $filasEslingas = is_array($filasEslingas) ? $filasEslingas : [];
        
            $columnas = [
                'numero_eslinga',
                'diametro',
                'capacidad',
                'longitud',
                'elongacion_causada_por_estiramiento',
                'eslabones_distorsionados_o_danados',
                'presenta_muescas_o_estrias',
                'presenta_corrosion_general',
                'eslabones_torcidos',
                'trizaduras_en_partes_soldadas',
                'ojos_o_eslabones_desgastados',
                'se_realiza_revision_de_ganchos',
                'cuenta_con_seguro_de_gancho',
                'tiene_rotulacion_carga_maxima',
                'almacenamiento_correcto',
                'libre_de_aceite_o_grasas_libre_de_quimicos',
                'etiqueta_visible',
                'cuenta_con_certificado_de_fabricante',
                'la_eslinga_esta_en_buenas_condiciones',
            ];
        
            $valorSiNo = function ($valor) {
                if (
                    $valor === true ||
                    $valor === 'true' ||
                    $valor === 'si' ||
                    $valor === 'sí' ||
                    $valor === 'SI'
                ) {
                    return 'SI';
                }
        
                if (
                    $valor === false ||
                    $valor === 'false' ||
                    $valor === 'no' ||
                    $valor === 'NO'
                ) {
                    return 'NO';
                }
        
                return $valor ?: '';
            };
        
            $totalFilas = max(10, count($filasEslingas));
        @endphp
        
        @for ($i = 0; $i < $totalFilas; $i++)
        
            @php
                $fila = $filasEslingas[$i] ?? [];
            @endphp
        
            <tr>
                @foreach ($columnas as $columna)
                    <td style="
                        border:1px solid #000;
                        height:15px;
                        text-align:center;
                        vertical-align:middle;
                        font-size:8px;
                        padding:2px;
                    ">
                        {{ $valorSiNo(data_get($fila, $columna, '')) }}
                    </td>
                @endforeach
            </tr>
        @endfor
    </table>

    <!-- TABLA NOTA -->
    <table style="
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
        margin-top:10px;
        font-size:8px;
    ">
    
        <!-- CONTROL DE ANCHOS -->
        <tr style="height:0; line-height:0;">
            <td style="width:4.3%; padding:0; border:none; height:0;"></td>
            <td style="width:95.7%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <!-- FILA -->
        <tr>
            <!-- LABEL -->
            <td style="border:none; height:40px; text-align:center; vertical-align:middle;">
                <div style="font-weight:bold; font-size:10px; margin-top:-20px;">
                    Notas
                </div>
            </td>
        
            <!-- DATO -->
            <td style="border-bottom:1px solid #000; height:40px; text-align:left; vertical-align:bottom; font-size:9px; padding:0 8px 0px 8px;">
                {{ data_get($answers, 'notas', '') }}
            </td>
        </tr>
    </table>

    @php
        $imgEslingasTodas = public_path(
            'images/forms/SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas/Eslingas_Todas.png'
        );
    
        $imgEslingasTodasSrc = file_exists($imgEslingasTodas)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($imgEslingasTodas))
            : null;
    @endphp
    
    <!-- IMAGEN -->
    <table style="
        width:100%;
        margin-top:15px;
        border-collapse:collapse;
        table-layout:fixed;
    ">
    
        <tr>
    
            <td style="
                width:100%;
                text-align:center;
                vertical-align:middle;
            ">
    
                @if($imgEslingasTodasSrc)
                    <img
                        src="{{ $imgEslingasTodasSrc }}"
                        style="
                            width:1000px;
                            height:120px;
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