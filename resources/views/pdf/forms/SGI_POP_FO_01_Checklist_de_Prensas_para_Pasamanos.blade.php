<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist de Prensas para Pasamanos' }}</title>

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

        $tablaPrensasPasamanos = $answers['tabla_prensas_pasamanos'] ?? [];
        $primerRegistro = $tablaPrensasPasamanos[0] ?? [];
        
        $codigoIdentificacionPrensa = $primerRegistro['codigo_identificacion_prensa'] ?? '';

        $tipoPrensa = $primerRegistro['tipo_prensa'] ?? '';
        $tipoVoltaje = $primerRegistro['tipo_voltaje'] ?? '';
        
        $prensaPasamanosSrc = null;
        $prensaPasamanosPath = public_path('images/forms/SGI_POP_FO_01_Checklist_de_Prensas_para_Pasamanos/prensa_pasamanos.jpg');
        
        if (file_exists($prensaPasamanosPath)) {
            $prensaPasamanosSrc =
                'data:image/jpeg;base64,' .
                base64_encode(file_get_contents($prensaPasamanosPath));
        }

        $componentesPasamanos = [
            1 => 'CAJA DE CONTROL',
            2 => 'PLATINA SUPERIOR',
            3 => 'PLATINA INFERIOR',
            4 => 'TERMÓMETRO DE AGUJA PLATINA SUPERIOR',
            5 => 'TERMÓMETRO DE AGUJA PLATINA INFERIOR',
            6 => 'CABLE MAESTRO PLATINA SUPERIOR',
            7 => 'CABLE MAESTRO PLATINA INFERIOR',
            8 => 'EXTENSIÓN DE ALIMENTACIÓN PRINCIPAL',
            9 => 'TERMOPAR DE CAJA DE CONTROL',
            10 => 'TORNILLOS Y/O TUERCAS',
            11 => 'RIEL INTERNO (MOLDE)',
            12 => 'TORNILLERÍA GENERAL',
        ];
        
        $notas = $primerRegistro['notas'] ?? '';
        
        $nombreInspeccionaMantenimiento = $answers['nombre_inspecciona_mantenimiento'] ?? '';
        $firmaInspeccionaMantenimiento = $answers['firma_inspecciona_mantenimiento'] ?? '';
        
        $nombreResponsableAreaPasamanos = $answers['nombre_responsable_area_pasamanos'] ?? '';
        $firmaResponsableAreaPasamanos = $answers['firma_responsable_area_pasamanos'] ?? '';
        
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
        
        $firmaInspeccionaSrc = $getFirmaSrc($firmaInspeccionaMantenimiento);
        $firmaResponsableSrc = $getFirmaSrc($firmaResponsableAreaPasamanos);
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
                    CODIFICACIÓN: SGI-POP-FO-01
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td class="right-cell">
                    FECHA EMISIÓN:
                </td>
            </tr>

            <tr>
                <td class="center-cell">
                    CHECKLIST DE PRENSAS PARA PASAMANOS
                </td>

                <td class="right-cell">
                    REVISIÓN:
                </td>
            </tr>
        </table>

        <!-- FECHA - TALLER - CÓDIGO -->
        <table style="
            width:100%;
            border-collapse:collapse;
            border:1px solid #000;
            border-top:none;
            border-bottom:none;
        ">
            <tr>
                <td style="padding:6px;">
                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        table-layout:fixed;
                    ">
                        <tr>
        
                            <!-- FECHA -->
                            <td style="
                                width:33.33%;
                                text-align:center;
                                vertical-align:bottom;
                                padding-right:10px;
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
        
                            <!-- TALLER -->
                            <td style="
                                width:33.33%;
                                text-align:center;
                                vertical-align:bottom;
                                padding-left:10px;
                                padding-right:10px;
                            ">
                                <span style="
                                    font-size:8px;
                                    font-weight:bold;
                                ">
                                    TALLER:
                                </span>
        
                                <span style="
                                    display:inline-block;
                                    min-width:120px;
                                    border-bottom:1px solid #000;
                                    text-align:center;
                                    font-size:8px;
                                ">
                                    {{ $answers['taller'] ?? '' }}
                                </span>
                            </td>
        
                            <!-- CÓDIGO -->
                            <td style="
                                width:33.33%;
                                text-align:center;
                                vertical-align:bottom;
                                padding-left:10px;
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
                                    {{ $codigoIdentificacionPrensa }}
                                </span>
                            </td>
        
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @php
            $filaAlto = '22px';
        
            $col1 = '3%';
            $col2 = '15%';
            $col3 = '10%';
            $col4 = '4%';
            $col5 = '15%';
            $col6 = '10%';
            $col7 = '3%';
            $col8 = '40%';
        
            $accionRealizar = $primerRegistro['accion_realizar'] ?? '';
        @endphp
        
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:7px;
        ">
        
            <!-- FILA FANTASMA PARA CONTROLAR ANCHOS -->
            <tr style="height:0; line-height:0;">
                <td style="width:{{ $col1 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col2 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col3 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col4 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col5 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col6 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col7 }}; padding:0; border:none; height:0;"></td>
                <td style="width:{{ $col8 }}; padding:0; border:none; height:0;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="7" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:4px;
                ">
                    ELIJA EL TIPO DE PRENSA A REVISIÓN Y TIPO DE VOLTAJE
                </td>
            
                <td style="
                    border:1px solid #000;
                    border-bottom:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                    padding:4px;
                ">
                    Guia de Inspección:
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="7" style="border:1px solid #000; border-bottom:none; padding:4px;">&nbsp;</td>
            
                <td rowspan="13" style="
                    border:1px solid #000;
                    border-top:none;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                ">
                    @if($prensaPasamanosSrc)
                        <img src="{{ $prensaPasamanosSrc }}" style="
                            width:220px;
                            height:130px;
                            object-fit:contain;
                            display:block;
                            margin:8px auto 0 auto;
                        ">
                    @endif
                </td>
            </tr>
        
            <!-- FILA 3 -->
            <tr style="height:{{ $filaAlto }};">
                <td rowspan="8" style="border:1px solid #000; border-right:none; border-top:none;">&nbsp;</td>
        
                <td style="
                    border:1px solid #000;
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO DE PRENSA:
                </td>
        
                <td colspan="2" rowspan="8" style="border:none;">
                    &nbsp;
                </td>
        
                <td style="
                    border:1px solid #000;
                    border-top:none;
                    border-right:none;
                    border-left:none;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    TIPO DE VOLTAJE:
                </td>
        
                <td colspan="2" rowspan="8" style="border:none;">
                    &nbsp;
                </td>
            </tr>
        
            <!-- FILA 4 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">T79</td>
                <td style="border:none; text-align:center;">220 VOLTS</td>
            </tr>
        
            <!-- FILA 5 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">E99</td>
                <td style="border:none; text-align:center;">110 VOLTS</td>
            </tr>
        
            <!-- FILA 6 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">1179</td>
                <td rowspan="5" style="border:none;">&nbsp;</td>
            </tr>
        
            <!-- FILA 7 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">1379</td>
            </tr>
        
            <!-- FILA 8 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">1477</td>
            </tr>
        
            <!-- FILA 9 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">1699</td>
            </tr>
        
            <!-- FILA 10 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">1879</td>
            </tr>
        
            <!-- FILA 11 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="7" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                ">
                    ESPECIFICAR LA ACCIÓN A REALIZAR
                </td>
            </tr>
        
            <!-- FILA 12 -->
            <tr style="height:{{ $filaAlto }};">
                <td rowspan="3" style="border:1px solid #000; border-right:none;">&nbsp;</td>
        
                <td colspan="5" style="border:none;">
                    &nbsp;
                </td>
        
                <td rowspan="3" style="border:1px solid #000; border-left:none;">&nbsp;</td>
            </tr>
        
            <!-- FILA 13 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center; font-weight:bold;">
                    INSPECCIÓN
                </td>
        
                <td style="border:1px solid #000; text-align:center; font-weight:bold;">
                    {{ $accionRealizar === 'INSPECCIÓN' ? 'X' : '' }}
                </td>
        
                <td style="border:none;">&nbsp;</td>
        
                <td style="border:none; text-align:center; font-weight:bold;">
                    MANTENIMIENTO
                </td>
        
                <td style="border:1px solid #000; text-align:center; font-weight:bold;">
                    {{ $accionRealizar === 'MANTENIMIENTO' ? 'X' : '' }}
                </td>
            </tr>
        
            <!-- FILA 14 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="5" style="border:none;">&nbsp;</td>
            </tr>
        
            <!-- FILA 15 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="8" style="
                    border:1px solid #000;
                    border-bottom:none;
                    text-align:center;
                    vertical-align:middle;
                    font-size:7px;
                    line-height:1.8;
                ">
                    <strong>Marque según corresponda el estado:</strong>
                    <br>
                    <strong>C</strong> = Cumple
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>NC</strong> = No Cumple
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>F</strong> = Faltante
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>MM</strong> = Mantenimiento
                </td>
            </tr>
        </table>

        <!-- TABLA COMPONENTES -->
        <table style="
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
            font-size:6px;
        ">
            <tr style="height:0; line-height:0;">
                <td style="width:10%; padding:0; border:none;"></td>
                <td style="width:40%; padding:0; border:none;"></td>
                <td style="width:15%; padding:0; border:none;"></td>
                <td style="width:35%; padding:0; border:none;"></td>
            </tr>
        
            <!-- FILA 1 -->
            <tr style="height:22px;">
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold; padding:4px;">
                    NO. DE COMPONENTE
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold; padding:4px;">
                    DESCRIPCIÓN
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold; padding:4px;">
                    ESTADO
                </td>
        
                <td style="border:1px solid #000; text-align:center; vertical-align:middle; font-weight:bold; padding:4px;">
                    COMENTARIOS
                </td>
            </tr>
        
            @foreach($componentesPasamanos as $num => $descripcion)
                <tr style="height:22px;">
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $num }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:left; vertical-align:middle; padding-left:4px; padding:2px;">
                        {{ $descripcion }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding:2px;">
                        {{ $primerRegistro['estado_item_' . $num] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding-left:4px; padding:2px;">
                        {{ $primerRegistro['observaciones_item_' . $num] ?? '' }}
                    </td>
                </tr>
            @endforeach
        
            <!-- FILA 14 -->
            <tr style="height:16px;">
                <td colspan="4" style="
                    border:1px solid #000;
                    border-bottom:none;
                    text-align:left;
                    vertical-align:middle;
                    font-weight:bold;
                    padding-left:4px;
                ">
                    NOTAS:
                </td>
            </tr>
        
            <!-- FILA 15 -->
            <tr style="height:28px;">
                <td colspan="4" style="
                    border:1px solid #000;
                    border-bottom:none;
                    border-top:none;
                    text-align:left;
                    vertical-align:top;
                    padding:8px;
                ">
                    {{ $notas }}
                </td>
            </tr>
        
            <!-- FILA 16 -->
            <tr style="height:18px;">
                <td colspan="2" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    NOMBRE Y FIRMA DE QUIEN INSPECCIONA O DA MANTENIMIENTO
                </td>
        
                <td colspan="2" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    font-weight:bold;
                ">
                    NOMBRE Y FIRMA DE RESPONSABLE DE PASAMANOS
                </td>
            </tr>
        
            <!-- FILA 17 -->
            <tr style="height:70px;">
                <td colspan="2" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                ">
                    @if($firmaInspeccionaSrc)
                        <img src="{{ $firmaInspeccionaSrc }}" style="
                            width:130px;
                            height:42px;
                            object-fit:contain;
                            display:block;
                            margin:0 auto 2px auto;
                        ">
                    @endif
        
                    <div>{{ $nombreInspeccionaMantenimiento }}</div>
                </td>
        
                <td colspan="2" style="
                    border:1px solid #000;
                    text-align:center;
                    vertical-align:middle;
                    padding:4px;
                ">
                    @if($firmaResponsableSrc)
                        <img src="{{ $firmaResponsableSrc }}" style="
                            width:130px;
                            height:42px;
                            object-fit:contain;
                            display:block;
                            margin:0 auto 2px auto;
                        ">
                    @endif
        
                    <div>{{ $nombreResponsableAreaPasamanos }}</div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
