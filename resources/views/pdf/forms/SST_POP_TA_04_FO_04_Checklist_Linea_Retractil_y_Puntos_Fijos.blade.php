<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist Línea Retráctil y Puntos Fijos' }}</title>
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
            width: 110%;
            transform: scale(0.86);
            transform-origin: top left;
            margin-left: 30px;
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

        .row-2-center {
            font-size: 10px;
        }

        .row-3-center {
            font-size: 10px;
        }

        .row-1-right,
        .row-2-right,
        .row-3-right {
            font-size: 9px;
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
            font-size: 10px;
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
            font-size: 10px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 8px;
        }
        
        .inspection-line-wrap {
            display: inline-block;
            width: 160px;
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

        .criteria-table {
            width: 99.6%;
            margin: 14px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }
        
        .criteria-table td,
        .criteria-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.15;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
        }
        
        .criteria-table .top-title {
            font-weight: bold;
            font-size: 9px;
            background-color: #f3f4f6; /* gris claro */
        }
        
        .criteria-table .group-title {
            font-weight: bold;
            font-size: 7px;
            background-color: #f3f4f6; /* gris claro */
        }
        
        .criteria-table .sub-title {
            font-weight: normal;
            font-size: 7px;
        }
        
        .criteria-table .vertical-center {
            font-weight: bold;
            font-size: 7px;
            background-color: #f3f4f6; /* gris claro */
        }

        .data-table {
            width: 99.6%;
            margin: 0 auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
        }
        
        .data-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
            font-size: 7px;
        }
        
        /* FILAS CON DATOS */
        .row-filled td {
            padding: 4px 3px;
            line-height: 1.15;
        }
        
        /* FILAS VACÍAS */
        .row-empty td {
            padding: 0;
            line-height: 0.9;
            font-size: 1px;
            height: 18px;
        }
        
        /* si quieres observaciones alineada a la izquierda solo cuando tenga datos */
        .row-filled .obs-cell {
            text-align: left;
            padding-left: 6px;
        }
    </style>
</head>
<body>
    @php
        $fieldsCollection = collect($fields ?? []);
        $getField = function ($id) use ($fieldsCollection) {
            return $fieldsCollection->firstWhere('id', $id);
        };

        $logo = $getField('encabezado_logo');

        $logoSrc = null;
        $logoUrl = data_get($logo, 'url');

        if (is_string($logoUrl) && $logoUrl !== '') {
            if (
                str_starts_with($logoUrl, 'http://') ||
                str_starts_with($logoUrl, 'https://') ||
                str_starts_with($logoUrl, 'data:image')
            ) {
                $logoSrc = $logoUrl;
            } else {
                $normalizedLogo = ltrim(str_replace('\\', '/', $logoUrl), '/');
                $possiblePath = public_path($normalizedLogo);

                if (file_exists($possiblePath)) {
                    $mime = mime_content_type($possiblePath) ?: 'image/png';
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($possiblePath));
                }
            }
        }

        $fechaInspeccion = optional($submission->created_at)->format('d/m/Y') ?: '';
        $tallerValor = data_get($answers, 'taller', '') ?: '';

        $rutaImagen = public_path('images/forms/SST_POP_TA_04_FO_04_Checklist_Linea_Retractil_y_Puntos_Fijos/LineaRetractil.png');

        $imagenSrc = null;
        
        if (file_exists($rutaImagen)) {
            $mime = mime_content_type($rutaImagen) ?: 'image/png';
            $imagenSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($rutaImagen));
        }

        $nombreInspector = data_get($answers, 'nombre_inspector', '');
        $firmaInspector = data_get($answers, 'firma_inspector', '');
        
        $firmaSrc = null;
        
        if (!empty($firmaInspector)) {
            if (str_starts_with($firmaInspector, 'data:image')) {
                $firmaSrc = $firmaInspector;
            } else {
                $relativePath = ltrim(str_replace('\\', '/', $firmaInspector), '/');
        
                if (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr($relativePath, 8);
                }
        
                $rutaFirma = storage_path('app/public/' . $relativePath);
        
                if (file_exists($rutaFirma)) {
                    $mime = mime_content_type($rutaFirma) ?: 'image/png';
                    $firmaSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($rutaFirma));
                }
            }
        }
    @endphp

    <div class="sheet">
        <table class="header-table">
            <colgroup>
                <col style="width: 20%">
                <col style="width: 25%">
                <col style="width: 25%">
                <col style="width: 15%">
                <col style="width: 15%">
            </colgroup>
            <tr>
                <td rowspan="3" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo">
                    @endif
                </td>

                <td colspan="2" class="center-cell row-1-center">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <td colspan="2" class="right-cell row-1-right">
                    CODIFICACIÓN: SST-POP-TA-04-FO-04
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell row-2-center">
                    SISTEMA DE GESTIÓN INTEGRAL
                </td>

                <td colspan="2" class="right-cell row-2-right">
                    FECHA DE EMISIÓN: 27/03/2025
                </td>
            </tr>

            <tr>
                <td colspan="2" class="center-cell row-3-center">
                    Checklist Línea Retráctil y Puntos Fijos
                </td>

                <td colspan="2" class="right-cell row-3-right">
                    NÚMERO DE REVISIÓN: 01
                </td>
            </tr>
        </table>

        <div class="inspection-area">
            <div class="inspection-row">
                <div class="inspection-item left">
                    <span class="inspection-label">Fecha de inspección:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $fechaInspeccion }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>
        
                <div class="inspection-item right">
                    <span class="inspection-label">Taller:</span>
                    <span class="inspection-line-wrap">
                        <span class="inspection-value">{{ $tallerValor }}</span>
                        <span class="inspection-underline"></span>
                    </span>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 12px; font-size: 10px; font-weight: bold;">
            Este checklist deberá llenarse cada que se use línea de vida y en caso de no usarse llenarse una vez al mes.
        </div>
        
        <div style="text-align: center; margin-top: 4px; font-size: 10px; font-weight: bold;">
            Considerar los siguientes criterios de acuerdo a las condiciones de la línea de vida.
        </div>

        <table class="criteria-table">
            <tr>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td colspan="22" class="top-title">
                    Criterios: (✓) Buen Estado &nbsp;&nbsp; (X) Mal Estado &nbsp;&nbsp; (NA) No Aplica
                </td>
            </tr>
        
            <tr>
                <td rowspan="2" class="vertical-center">N° de Identificación</td>
                <td rowspan="2" class="vertical-center">Marca / Modelo del Arnés</td>

                <td colspan="4" class="group-title">CONDICIONES GENERALES</td>
                <td colspan="3" class="group-title">1. MOSQUETÓN</td>
                <td colspan="5" class="group-title">2. GANCHO DE SEGURIDAD DE CIERRE AUTOMATICO</td>       
                <td colspan="5" class="group-title">3. CONECTOR DE PUNTO FIJO/PUNTO DE ANCLAJE FIJO</td>
                <td colspan="2" class="group-title">Acciones</td>
                <td rowspan="2" colspan="3" class="vertical-center">Observaciones</td>
            </tr>
        
            <tr>
                <!-- CONDICIONES GENERALES -->
                <td class="sub-title">Manija de Anclaje</td>
                <td class="sub-title">Carcaza Termoplástica</td>
                <td class="sub-title">Línea de Vida Acero Galvanizado o Textil</td>
                <td class="sub-title">Activación de Sistema de Bloqueo</td>
        
                <!-- MOSQUETÓN -->
                <td class="sub-title">Desgaste, Deformaciones</td>
                <td class="sub-title">Picaduras, Grietas</td>
                <td class="sub-title">Corrosión</td>
        
                <!-- GANCHO -->
                <td class="sub-title">Desgaste, Deformaciones</td>
                <td class="sub-title">Picadura, Grietas</td>
                <td colspan="2" class="sub-title">
                    Ajuste Inadecuado o Incorrecto de los Cierres de Seguridad (Enganches)
                </td>
                <td class="sub-title">Corrosión</td>
        
                <!-- CONECTOR -->
                <td class="sub-title">Forro del Cable se Encuentra Desgastado</td>
                <td class="sub-title">Cuerpo de línea Presencia de Daño</td>
                <td class="sub-title">Costuras Rotas o Dañadas</td>
                <td class="sub-title">Argollas o Deformaciones</td>
                <td class="sub-title">Presencia de Aceites, Grasas o Químicos</td>
        
                <!-- ACCIONES -->
                <td class="sub-title">El Equipo se Marca como Dañado y es Sacado de Uso</td>
                <td class="sub-title">El Equipo está en Buenas Condiciones</td>
            </tr>
        </table>

        @php
            $rows = data_get($answers, 'tabla_linea_retractil', []);

            $filasConDatos = collect($rows)->filter(function ($row) {
                return !empty(array_filter($row, fn($value) => $value !== null && $value !== ''));
            })->values();
            
            $totalFilas = $filasConDatos->count();
            
            $pages = [];
            $remaining = $totalFilas;
            $offset = 0;
            
            while ($remaining > 0) {
                if (empty($pages)) {
                    // PRIMERA HOJA
                    if ($remaining <= 8) {
                        // última hoja con pie
                        $capacity = 8;
                    } elseif ($remaining <= 10) {
                        // deja 1 fila para la siguiente
                        $capacity = $remaining - 1;
                    } else {
                        // ya hay más hojas, primera hoja máxima 10
                        $capacity = 10;
                    }
                } else {
                    // HOJAS 2 EN ADELANTE
                    if ($remaining <= 14) {
                        // última hoja con pie
                        $capacity = 14;
                    } elseif ($remaining <= 18) {
                        // deja 1 fila para la siguiente
                        $capacity = $remaining - 1;
                    } else {
                        // hoja intermedia máxima 18
                        $capacity = 18;
                    }
                }
            
                $pages[] = $filasConDatos->slice($offset, $capacity)->values();
            
                $offset += $capacity;
                $remaining = $totalFilas - $offset;
            }
            
            if (empty($pages)) {
                $pages[] = collect();
            }
            
            $ultimaPaginaFilas = collect(end($pages))->count();
            
            $alturaFirma = match (true) {
                $ultimaPaginaFilas >= 8 => 0,
                $ultimaPaginaFilas === 7 => 10,
                $ultimaPaginaFilas === 6 => 30,
                $ultimaPaginaFilas === 5 => 50,
                $ultimaPaginaFilas === 4 => 80,
                $ultimaPaginaFilas === 3 => 120,
                default => 140,
            };
        @endphp
        
        @foreach($pages as $pageIndex => $chunk)
            @php
                $filasPorPagina = $loop->first
                    ? max(8, $chunk->count()) // primera hoja mínimo 8
                    : $chunk->count();
        
                $esUltimaPagina = $loop->last;
            @endphp
        
            <table class="data-table" style="margin-top: 6px;">
                @for ($i = 0; $i < $filasPorPagina; $i++)
                    @php
                        $row = $chunk[$i] ?? [];
                        $isEmpty = empty($row);
                    @endphp
        
                    <tr class="{{ $isEmpty ? 'row-empty' : 'row-filled' }}">
                        <td>{{ data_get($row, 'numero_identificacion') }}</td>
                        <td>{{ data_get($row, 'marca_modelo') }}</td>
        
                        <td>{{ data_get($row, 'manija_anclaje') }}</td>
                        <td>{{ data_get($row, 'carcaza_termoplastica') }}</td>
                        <td>{{ data_get($row, 'linea_vida_acero_textil') }}</td>
                        <td>{{ data_get($row, 'activacion_sistema_bloqueo') }}</td>
        
                        <td>{{ data_get($row, 'mosqueton_1_1') }}</td>
                        <td>{{ data_get($row, 'mosqueton_1_2') }}</td>
                        <td>{{ data_get($row, 'mosqueton_1_3') }}</td>
        
                        <td>{{ data_get($row, 'gancho_2_1') }}</td>
                        <td>{{ data_get($row, 'gancho_2_2') }}</td>
                        <td colspan="2">{{ data_get($row, 'gancho_2_3') }}</td>
                        <td>{{ data_get($row, 'gancho_2_4') }}</td>
        
                        <td>{{ data_get($row, 'conector_3_1') }}</td>
                        <td>{{ data_get($row, 'conector_3_2') }}</td>
                        <td>{{ data_get($row, 'conector_3_3') }}</td>
                        <td>{{ data_get($row, 'conector_3_4') }}</td>
                        <td>{{ data_get($row, 'conector_3_5') }}</td>
        
                        @php
                            $accion = data_get($row, 'acciones', '');
                        @endphp
        
                        <td>{{ $accion === 'El Equipo se Marca como Dañado y es Sacado de Uso' ? '●' : '' }}</td>
                        <td>{{ $accion === 'El Equipo está en Buenas Condiciones' ? '●' : '' }}</td>
        
                        <td colspan="3">{{ data_get($row, 'observaciones') }}</td>
                    </tr>
                @endfor
            </table>
        
            @if(!$loop->last)
                <div style="page-break-after: always;"></div>
        
                <table class="criteria-table" style="margin-top: 0;">
                    <tr>
                        <td style="border: none;"></td>
                        <td style="border: none;"></td>
                        <td colspan="22" class="top-title">
                            Criterios: (✓) Buen Estado &nbsp;&nbsp; (X) Mal Estado &nbsp;&nbsp; (NA) No Aplica
                        </td>
                    </tr>
        
                    <tr>
                        <td rowspan="2" class="vertical-center">N° de Identificación</td>
                        <td rowspan="2" class="vertical-center">Marca / Modelo del Arnés</td>
        
                        <td colspan="4" class="group-title">CONDICIONES GENERALES</td>
                        <td colspan="3" class="group-title">1. MOSQUETÓN</td>
                        <td colspan="5" class="group-title">2. GANCHO DE SEGURIDAD DE CIERRE AUTOMATICO</td>
                        <td colspan="5" class="group-title">3. CONECTOR DE PUNTO FIJO/PUNTO DE ANCLAJE FIJO</td>
                        <td colspan="2" class="group-title">Acciones</td>
                        <td rowspan="2" colspan="3" class="vertical-center">Observaciones</td>
                    </tr>
        
                    <tr>
                        <td class="sub-title">Manija de Anclaje</td>
                        <td class="sub-title">Carcaza Termoplástica</td>
                        <td class="sub-title">Línea de Vida Acero Galvanizado o Textil</td>
                        <td class="sub-title">Activación de Sistema de Bloqueo</td>
        
                        <td class="sub-title">Desgaste, Deformaciones</td>
                        <td class="sub-title">Picaduras, Grietas</td>
                        <td class="sub-title">Corrosión</td>
        
                        <td class="sub-title">Desgaste, Deformaciones</td>
                        <td class="sub-title">Picadura, Grietas</td>
                        <td colspan="2" class="sub-title">
                            Ajuste Inadecuado o Incorrecto de los Cierres de Seguridad (Enganches)
                        </td>
                        <td class="sub-title">Corrosión</td>
        
                        <td class="sub-title">Forro del Cable se Encuentra Desgastado</td>
                        <td class="sub-title">Cuerpo de línea Presencia de Daño</td>
                        <td class="sub-title">Costuras Rotas o Dañadas</td>
                        <td class="sub-title">Argollas o Deformaciones</td>
                        <td class="sub-title">Presencia de Aceites, Grasas o Químicos</td>
        
                        <td class="sub-title">El Equipo se Marca como Dañado y es Sacado de Uso</td>
                        <td class="sub-title">El Equipo está en Buenas Condiciones</td>
                    </tr>
                </table>
            @endif
        @endforeach

        <table class="data-table" style="margin-top: 6px;">
            <!-- FILA 1 -->
            <tr>
                <td colspan="24" style="
                    color: red;
                    font-weight: bold;
                    font-size: 9px;
                    text-align: center;
                    padding: 6px;
                ">
                    Si su equipo de sistema anticaída, no cumple con algún criterio critico, NO DEBE SER UTILIZADO y debe informar a supervisor para cambio.
                </td>
            </tr>
        
            <!-- FILA 2 -->
            <tr>
                <!-- columnas vacías sin borde -->
                <td style="border: none;"></td>
                <td style="border: none;"></td>
        
                <!-- columna 3 a 11 -->
                <td colspan="9" style="
                    font-weight: bold;
                    font-size: 9px;
                    text-align: center;
                    padding: 6px;
                ">
                    Guía para la inspección
                </td>
        
                <!-- resto sin bordes -->
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
            </tr>
        </table>

        @if($imagenSrc)
            <div style="margin-top: 6px; width: 100%; position: relative; height: {{ $alturaFirma }}px;">
        
                <!-- IMAGEN -->
                <div style="position: absolute; left: 250px; top: 0;">
                    <img src="{{ $imagenSrc }}" style="width: 150px; height: 150px;">
                </div>
        
                <!-- FIRMA -->
                <div style="position: absolute; right: 250px; top: 70px; text-align: center; width: 180px;">

                    <!-- FIRMA -->
                    @if(!empty($firmaSrc))
                        <div style="height: 35px; margin-bottom: 2px;">
                            <img src="{{ $firmaSrc }}" style="max-width: 120px; max-height: 48px; object-fit: contain;">
                        </div>
                    @endif
                
                    <!-- NOMBRE (arriba de la línea) -->
                    <div style="font-size: 9px; font-weight: bold; margin-bottom: 2px;">
                        {{ $nombreInspector }}
                    </div>
                
                    <!-- LÍNEA -->
                    <div style="width: 180px; border-top: 1px solid #000;"></div>
                
                    <!-- TEXTO FIJO -->
                    <div style="margin-top: 2px; font-size: 9px; font-weight: bold;">
                        Nombre y Firma del Inspector
                    </div>
                
                </div>
        
            </div>
        @endif
    </div>
</body>
</html>