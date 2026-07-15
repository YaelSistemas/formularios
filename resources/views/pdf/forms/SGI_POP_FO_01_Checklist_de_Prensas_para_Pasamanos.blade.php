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

        if (!is_array($tablaPrensasPasamanos)) {
            $tablaPrensasPasamanos = [];
        }

        $tablaPrensasPasamanos = array_values($tablaPrensasPasamanos);

        // Por seguridad, si no existen filas se genera una hoja vacía.
        if (count($tablaPrensasPasamanos) === 0) {
            $tablaPrensasPasamanos = [[]];
        }

        $totalPaginas = count($tablaPrensasPasamanos);

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

    @foreach($tablaPrensasPasamanos as $paginaIndex => $registroPrensa)
        @php
            $codigoIdentificacionPrensa =
                $registroPrensa['codigo_identificacion_prensa'] ?? '';

            $tipoPrensa =
                $registroPrensa['tipo_prensa'] ?? '';

            $tipoVoltaje =
                $registroPrensa['tipo_voltaje'] ?? '';

            $accionRealizar =
                $registroPrensa['accion_realizar'] ?? '';

            $notas =
                $registroPrensa['notas'] ?? '';
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
        
            <!-- FILA 1: PÁGINA -->
            <tr>
                <!-- LOGO ABARCA LAS 4 FILAS -->
                <td rowspan="4" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}">
                    @endif
                </td>
        
                <!-- EMPRESA ABARCA PÁGINA Y FECHA -->
                <td rowspan="2" class="center-cell">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>
        
                <td class="right-cell">
                    PÁGINA: {{ $paginaIndex + 1 }} DE {{ $totalPaginas }}
                </td>
            </tr>
        
            <!-- FILA 2: FECHA DE EMISIÓN -->
            <tr>
                <td class="right-cell">
                    FECHA EMISIÓN: 27/03/2025
                </td>
            </tr>
        
            <!-- FILA 3: CÓDIGO -->
            <tr>
                <td class="center-cell">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>
        
                <td class="right-cell">
                    CÓDIGO: SGI-POP-FO-01
                </td>
            </tr>
        
            <!-- FILA 4: REVISIÓN -->
            <tr>
                <td class="center-cell">
                    CHECKLIST DE PRENSAS PARA PASAMANOS
                </td>
        
                <td class="right-cell">
                    REVISIÓN: 01
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
                                    UNIDAD DE SERVICIO:
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
        
            $estiloOpcionSeleccionada = function ($valorSeleccionado, $opcion) {
                if ($valorSeleccionado === $opcion) {
                    return '
                        display:inline-block;
                        border:1px solid #ff0000;
                        color:#ff0000;
                        font-weight:bold;
                        padding:1px 6px;
                        line-height:1.2;
                    ';
                }
            
                return '
                    display:inline-block;
                    border:1px solid transparent;
                    color:#000;
                    font-weight:normal;
                    padding:1px 6px;
                    line-height:1.2;
                ';
            };
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
            
                <td rowspan="14" style="
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
                <td rowspan="9" style="border:1px solid #000; border-right:none; border-top:none;">&nbsp;</td>
        
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
        
                <td colspan="2" rowspan="9" style="border:none;">
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
        
                <td colspan="2" rowspan="9" style="border:none;">
                    &nbsp;
                </td>
            </tr>
        
            <!-- FILA 4 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, 'H49') }}">
                        H49
                    </span>
                </td>
            
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoVoltaje, '220 VOLTS') }}">
                        220 VOLTS
                    </span>
                </td>
            </tr>
            
            <!-- FILA 5 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, 'T79') }}">
                        T79
                    </span>
                </td>
            
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoVoltaje, '110 VOLTS') }}">
                        110 VOLTS
                    </span>
                </td>
            </tr>
            
            <!-- FILA 6 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, 'E99') }}">
                        E99
                    </span>
                </td>
            
                <td rowspan="6" style="border:none;">
                    &nbsp;
                </td>
            </tr>
            
            <!-- FILA 7 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, '1179') }}">
                        1179
                    </span>
                </td>
            </tr>
            
            <!-- FILA 8 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, '1379') }}">
                        1379
                    </span>
                </td>
            </tr>
            
            <!-- FILA 9 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, '1477') }}">
                        1477
                    </span>
                </td>
            </tr>
            
            <!-- FILA 10 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, '1699') }}">
                        1699
                    </span>
                </td>
            </tr>
            
            <!-- FILA 11 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center;">
                    <span style="{{ $estiloOpcionSeleccionada($tipoPrensa, '1879') }}">
                        1879
                    </span>
                </td>
            </tr>
        
            <!-- FILA 12 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="7" style="
                    border:1px solid #000;
                    text-align:center;
                    font-weight:bold;
                ">
                    ESPECIFICAR LA ACCIÓN A REALIZAR
                </td>
            </tr>
        
            <!-- FILA 13 -->
            <tr style="height:{{ $filaAlto }};">
                <td rowspan="3" style="border:1px solid #000; border-right:none;">&nbsp;</td>
        
                <td colspan="5" style="border:none;">
                    &nbsp;
                </td>
        
                <td rowspan="3" style="border:1px solid #000; border-left:none;">&nbsp;</td>
            </tr>
        
            <!-- FILA 14 -->
            <tr style="height:{{ $filaAlto }};">
                <td style="border:none; text-align:center; font-weight:bold;">
                    INSPECCIÓN
                </td>
        
                <td style="border:1px solid #000; text-align:center; font-weight:bold;">
                    {{ $accionRealizar === 'Inspección' ? 'X' : '' }}
                </td>
        
                <td style="border:none;">&nbsp;</td>
        
                <td style="border:none; text-align:center; font-weight:bold;">
                    MANTENIMIENTO
                </td>
        
                <td style="border:1px solid #000; text-align:center; font-weight:bold;">
                    {{ $accionRealizar === 'Mantenimiento' ? 'X' : '' }}
                </td>
            </tr>
        
            <!-- FILA 15 -->
            <tr style="height:{{ $filaAlto }};">
                <td colspan="5" style="border:none;">&nbsp;</td>
            </tr>
        
            <!-- FILA 16 -->
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
                        {{ $registroPrensa['estado_item_' . $num] ?? '' }}
                    </td>
        
                    <td style="border:1px solid #000; text-align:center; vertical-align:middle; padding-left:4px; padding:2px;">
                        {{ $registroPrensa['observaciones_item_' . $num] ?? '' }}
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

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
