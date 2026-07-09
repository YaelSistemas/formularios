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

        .page-break {
            page-break-after: always;
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
            padding-left: 8px;
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

    $registrosPorHoja = 10;
    $paginasEslingas = array_chunk($filasEslingas, $registrosPorHoja);

    if (count($paginasEslingas) === 0) {
        $paginasEslingas = [[]];
    }

    $imgEslingasTodas = public_path(
        'images/forms/SGI_POP_LG_01_FO_09_Checklist_Eslingas_de_Cadenas/Eslingas_Todas.png'
    );

    $imgEslingasTodasSrc = file_exists($imgEslingasTodas)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($imgEslingasTodas))
        : null;
@endphp

@foreach ($paginasEslingas as $paginaIndex => $filasPagina)

<div class="sheet {{ !$loop->last ? 'page-break' : '' }}">

    <!-- HEADER -->
    <table class="header-table">
        <tr style="height:0; line-height:0;">
            <td style="width:25%; padding:0; border:none; height:0;"></td>
            <td style="width:45%; padding:0; border:none; height:0;"></td>
            <td style="width:30%; padding:0; border:none; height:0;"></td>
        </tr>
    
        <tr>
            <td rowspan="4" class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}">
                @endif
            </td>
    
            <td class="center-cell">
                VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
            </td>
    
            <td class="right-cell">
                CÓDIGO: SGI-POP-LG-01-FO-09
            </td>
        </tr>
    
        <tr>
            <td class="center-cell">
                SISTEMA DE GESTIÓN INTEGRAL
            </td>
    
            <td class="right-cell">
                FECHA DE EMISIÓN: 27/03/25
            </td>
        </tr>
    
        <tr>
            <td rowspan="2" class="center-cell">
                CHECKLIST ESLINGAS DE CADENAS
            </td>
    
            <td class="right-cell">
                REVISIÓN: 01
            </td>
        </tr>
    
        <tr>
            <td class="right-cell">
                PÁGINA: {{ str_pad($paginaIndex + 1, 2, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

    <!-- DATOS -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:1px;
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

    <div style="width:100%; text-align:left; margin-top:10px; margin-bottom:8px; font-size:9px; line-height:1.4;">
        Considerar los siguientes criterios de acuerdo al estado de la eslinga de cadena:
        <strong>SI</strong> /
        <strong>NO</strong>
    </div>

    <!-- TABLA -->
    <table style="
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
        margin-top:5px;
        font-size:7px;
    ">
    
        <tr style="height:0; line-height:0;">
            <td style="width:5%; padding:0; border:none; height:0;"></td>  {{-- N° de Eslinga --}}
            <td style="width:6%; padding:0; border:none; height:0;"></td>  {{-- Diámetro --}}
            <td style="width:6%; padding:0; border:none; height:0;"></td>  {{-- Capacidad --}}
            <td style="width:4.5%; padding:0; border:none; height:0;"></td>  {{-- Longitud --}}
        
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:5.2%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <td style="width:5%; padding:0; border:none; height:0;"></td>
            <td style="width:5%; padding:0; border:none; height:0;"></td>
            <td style="width:4.5%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
            <td style="width:5.6%; padding:0; border:none; height:0;"></td>
            <td style="width:4%; padding:0; border:none; height:0;"></td>
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
                        font-size:9px;
                        font-weight:{{ in_array($loop->index, $boldHeaders) ? 'bold' : 'normal' }};
                    ">
                        {{ $header }}
                    </div>
                </td>
            @endforeach
        </tr>
        
        @for ($i = 0; $i < $registrosPorHoja; $i++)
        
            @php
                $fila = $filasPagina[$i] ?? [];
            @endphp
        
            <tr>
                @foreach ($columnas as $columna)
                    <td style="
                        border:1px solid #000;
                        height:18px;
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
    
    <!-- IMAGEN -->
    <table style="
        width:100%;
        margin-top:10px;
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
                            height:70px;
                            object-fit:contain;
                        "
                    >
                @endif
            </td>
        </tr>
    </table>

</div>

@endforeach

</body>
</html>
