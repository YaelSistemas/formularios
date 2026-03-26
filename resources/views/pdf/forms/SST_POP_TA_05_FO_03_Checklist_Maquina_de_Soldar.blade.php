<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Checklist Máquina de Soldar' }}</title>
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
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            border: 1px solid #000;
            vertical-align: middle;
            box-sizing: border-box;
            line-height: 1.05;
        }

        .logo-cell {
            width: 18%;
            text-align: center;
            padding: 4px;
        }

        .logo-cell img {
            max-width: 100%;
            max-height: 60px;
            object-fit: contain;
        }

        .center-cell {
            width: 45%;
            text-align: center;
            font-weight: bold;
            padding: 4px 6px;
        }

        .right-cell {
            width: 37%;
            text-align: left;
            font-weight: bold;
            padding: 4px 8px;
        }

        .row-1-center {
            font-size: 9px;
            white-space: nowrap;
        }

        .row-2-center {
            font-size: 10px;
        }

        .row-3-center {
            font-size: 10px;
            text-transform: uppercase;
        }

        .row-1-right,
        .row-2-right,
        .row-3-right {
            font-size: 9px;
        }

        .info-block {
            margin-top: 24px;
            text-align: center;
        }

        .info-group {
            display: inline-block;
            text-align: left;
            margin-left: 0;
        }

        .inline-row {
            white-space: nowrap;
            font-size: 10px;
            line-height: 1;
        }

        .inline-row + .inline-row {
            margin-top: 18px;
        }

        .fecha-label,
        .serie-label {
            display: inline-block;
            width: 78px;
            font-weight: bold;
            margin-right: 1px;
            vertical-align: bottom;
            text-align: right;
        }

        .fecha-line,
        .serie-line {
            display: inline-block;
            width: 150px;
            border-bottom: 1px solid #000;
            text-align: center;
            vertical-align: bottom;
            height: 14px;
            line-height: 14px;
            padding: 0 4px 1px 4px;
            box-sizing: border-box;
        }

        .empresa-label,
        .tipo-label {
            display: inline-block;
            width: 170px;
            font-weight: bold;
            margin-left: 8px;
            margin-right: 1px;
            vertical-align: bottom;
            text-align: right;
        }

        .empresa-line,
        .tipo-line {
            display: inline-block;
            width: 250px;
            border-bottom: 1px solid #000;
            text-align: center;
            vertical-align: bottom;
            height: 14px;
            line-height: 14px;
            padding: 0 4px 1px 4px;
            box-sizing: border-box;
        }

        .checklist-note-table {
            width: 560pt;
            margin: 24px auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .checklist-note-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            background: #f3f4f6;
            line-height: 1.15;
            box-sizing: border-box;
        }

        .checklist-grid {
            width: 560pt;
            margin: 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .checklist-grid th,
        .checklist-grid td {
            border: 1px solid #000;
            font-size: 9px;
            line-height: 1.15;
            vertical-align: middle;
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .checklist-grid th {
            background: #e5e7eb;
            font-weight: bold;
            text-align: center;
            padding: 4px 5px;
        }

        .num-head,
        .num-cell {
            width: 16pt;
            min-width: 16pt;
            max-width: 16pt;
            text-align: center;
            padding: 1px 0;
            font-size: 7px;
        }

        .partes-head {
            text-align: center;
            padding: 4px 5px;
        }

        .partes-cell {
            text-align: left;
            padding: 4px 6px;
        }

        .cond-head,
        .cond-cell {
            width: 90pt;
            min-width: 90pt;
            max-width: 90pt;
            text-align: center;
            padding: 4px 4px;
        }

        .obs-head {
            text-align: center;
            padding: 4px 5px;
        }

        .obs-cell {
            text-align: left;
            padding: 4px 6px;
        }

        .cond-value {
            font-weight: bold;
            font-size: 10px;
        }

        .machine-image-block {
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }

        .machine-image-block img {
            width: 300px;
            max-width: 300px;
            height: auto;
            object-fit: contain;
        }

        .signatures-table {
            width: 100%;
            margin-top: 100px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 30px;
            box-sizing: border-box;
        }

        .signature-box {
            min-height: 90px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature-box img {
            max-width: 180px;
            max-height: 70px;
            object-fit: contain;
            display: block;
            margin: 0 auto 4px auto;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: -18px;
        }

        .signature-line {
            width: 210px;
            margin: 0 auto 2px auto;
            border-top: 1px solid #000;
            height: 1px;
        }

        .signature-label {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }

        /* ========= COMPACTACIÓN SOLO DE LA PARTE INFERIOR ========= */

        .lower-compact .checklist-note-table {
            margin-top: 14px;
        }

        .lower-compact .checklist-note-table td {
            font-size: 8px;
            padding: 4px 5px;
            line-height: 1.05;
        }

        .lower-compact .checklist-grid th,
        .lower-compact .checklist-grid td {
            font-size: 7px;
            line-height: 1.0;
            padding: 2px 3px;
        }

        .lower-compact .num-head,
        .lower-compact .num-cell {
            font-size: 6px;
            padding: 1px 0;
        }

        .lower-compact .cond-value {
            font-size: 8px;
        }

        .lower-compact .machine-image-block {
            margin-top: 10px;
        }

        .lower-compact .machine-image-block img {
            width: 180px;
            max-width: 180px;
        }

        .lower-compact .signatures-table {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    @php
        $fieldsCollection = collect($fields ?? []);

        $getField = function ($id) use ($fieldsCollection) {
            return $fieldsCollection->firstWhere('id', $id);
        };

        $toImageSrc = function ($pathOrUrl) {
            if (!is_string($pathOrUrl) || trim($pathOrUrl) === '') {
                return null;
            }

            if (
                str_starts_with($pathOrUrl, 'data:image') ||
                str_starts_with($pathOrUrl, 'http://') ||
                str_starts_with($pathOrUrl, 'https://')
            ) {
                return $pathOrUrl;
            }

            $normalized = ltrim($pathOrUrl, '/');

            if (str_starts_with($normalized, 'storage/')) {
                $fullPath = public_path($normalized);
            } else {
                $publicDirect = public_path($normalized);
                $storagePath = public_path('storage/' . $normalized);
                $fullPath = file_exists($publicDirect) ? $publicDirect : $storagePath;
            }

            if (!file_exists($fullPath)) {
                return null;
            }

            $mime = mime_content_type($fullPath) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
        };

        $formatCondicion = function ($value) {
            $value = strtoupper(trim((string) $value));

            return match ($value) {
                'B' => '(B) BUENO',
                'M' => '(M) MALO',
                'NA' => '(NA) NO APLICA',
                default => $value,
            };
        };

        $logo = $getField('encabezado_logo');
        $logoSrc = $toImageSrc(data_get($logo, 'url'));
        $machineImageSrc = $toImageSrc('images/forms/SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar/Imagen_Maquina_Soldar.png');

        $firmaInspectorSrc = $toImageSrc(data_get($answers, 'firma_inspector'));
        $firmaSupervisorSrc = $toImageSrc(data_get($answers, 'firma_supervisor'));
        $nombreInspectorValor = data_get($answers, 'nombre_inspector', '') ?: '';
        $nombreSupervisorValor = data_get($answers, 'nombre_supervisor', '') ?: '';

        $fechaValor = optional($submission->created_at)->format('d/m/Y') ?: '';
        $empresaUnidadValor = data_get($answers, 'taller', '') ?: '';
        $numeroSerieValor = data_get($answers, 'numero_serie_maquina', '') ?: '';
        $tipoModeloValor = data_get($answers, 'tipo_modelo_maquina', '') ?: '';

        $items = [
            ['n' => 1, 'label' => 'Voltímetro', 'estado' => 'voltimetro_estado', 'obs' => 'voltimetro_observaciones'],
            ['n' => 2, 'label' => 'Interruptor de Encendido y Apagado', 'estado' => 'interruptor_encendido_apagado_estado', 'obs' => 'interruptor_encendido_apagado_observaciones'],
            ['n' => 3, 'label' => 'Control de Inductancia', 'estado' => 'control_inductancia_estado', 'obs' => 'control_inductancia_observaciones'],
            ['n' => 4, 'label' => 'Selector Rotativo de Proceso', 'estado' => 'selector_rotativo_proceso_estado', 'obs' => 'selector_rotativo_proceso_observaciones'],
            ['n' => 5, 'label' => 'Amperímetro', 'estado' => 'amperimetro_estado', 'obs' => 'amperimetro_observaciones'],
            ['n' => 6, 'label' => 'Control de Ajuste de Amperaje/Voltaje', 'estado' => 'control_ajuste_amperaje_voltaje_estado', 'obs' => 'control_ajuste_amperaje_voltaje_observaciones'],
            ['n' => 7, 'label' => 'Sector de Control de Amperaje/Voltaje', 'estado' => 'sector_control_amperaje_voltaje_estado', 'obs' => 'sector_control_amperaje_voltaje_observaciones'],
            ['n' => 8, 'label' => 'Carcasa Metálica de Protección', 'estado' => 'carcasa_metalica_proteccion_estado', 'obs' => 'carcasa_metalica_proteccion_observaciones'],
            ['n' => 9, 'label' => 'Pantalla', 'estado' => 'pantalla_estado', 'obs' => 'pantalla_observaciones'],
            ['n' => 10, 'label' => 'Dispositivo de Bloqueo', 'estado' => 'dispositivo_bloqueo_estado', 'obs' => 'dispositivo_bloqueo_observaciones'],
            ['n' => 11, 'label' => 'Cable a Tierra', 'estado' => 'cable_tierra_estado', 'obs' => 'cable_tierra_observaciones'],
            ['n' => 12, 'label' => 'Pinza de Cable a Tierra', 'estado' => 'pinza_cable_tierra_estado', 'obs' => 'pinza_cable_tierra_observaciones'],
            ['n' => 13, 'label' => 'Cable Porta Electrodos', 'estado' => 'cable_porta_electrodos_estado', 'obs' => 'cable_porta_electrodos_observaciones'],
            ['n' => 14, 'label' => 'Pinza Porta Electrodos', 'estado' => 'pinza_porta_electrodos_estado', 'obs' => 'pinza_porta_electrodos_observaciones'],
            ['n' => 15, 'label' => 'Cables de Alimentación Aislados', 'estado' => 'cables_alimentacion_aislados_estado', 'obs' => 'cables_alimentacion_aislados_observaciones'],
            ['n' => 16, 'label' => 'Aislamiento de Humedad', 'estado' => 'aislamiento_humedad_estado', 'obs' => 'aislamiento_humedad_observaciones'],
            ['n' => 17, 'label' => 'Limpieza', 'estado' => 'limpieza_estado', 'obs' => 'limpieza_observaciones'],
        ];

        $totalObsChars = collect($items)->sum(
            fn ($item) => mb_strlen((string) data_get($answers, $item['obs'], ''))
        );

        $maxObsChars = collect($items)->max(
            fn ($item) => mb_strlen((string) data_get($answers, $item['obs'], ''))
        );

        $compactLower = $totalObsChars > 1200 || $maxObsChars > 140;
    @endphp

    <div class="sheet">
        <table class="header-table">
            <colgroup>
                <col style="width: 18%">
                <col style="width: 45%">
                <col style="width: 37%">
            </colgroup>

            <tr>
                <td rowspan="3" class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo">
                    @endif
                </td>

                <td class="center-cell row-1-center">
                    VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.
                </td>

                <td class="right-cell row-1-right">
                    Código:SST-POP-TA-05-FO-03
                </td>
            </tr>

            <tr>
                <td class="center-cell row-2-center">
                    Sistema de Gestión Integral
                </td>

                <td class="right-cell row-2-right">
                    Fecha de Emision: 27/03/2025
                </td>
            </tr>

            <tr>
                <td class="center-cell row-3-center">
                    Checklist Maquina de Soldar
                </td>

                <td class="right-cell row-3-right">
                    Revisión: 02
                </td>
            </tr>
        </table>

        <div class="info-block">
            <div class="info-group">
                <div class="inline-row">
                    <span class="fecha-label">Fecha:</span>
                    <span class="fecha-line">{{ $fechaValor }}</span>
                    <span class="empresa-label">Empresa / Unidad de Servicio:</span>
                    <span class="empresa-line">{{ $empresaUnidadValor }}</span>
                </div>

                <div class="inline-row">
                    <span class="serie-label">No. de Serie:</span>
                    <span class="serie-line">{{ $numeroSerieValor }}</span>
                    <span class="tipo-label">Tipo y modelo de maquina:</span>
                    <span class="tipo-line">{{ $tipoModeloValor }}</span>
                </div>
            </div>
        </div>

        <div class="{{ $compactLower ? 'lower-compact' : '' }}">
            <table class="checklist-note-table">
                <tr>
                    <td>
                        Marque según las condiciones de la maquina de soldar Bueno: B, Malo: M o No Aplica: Na
                    </td>
                </tr>
            </table>

            <table class="checklist-grid">
                <colgroup>
                    <col style="width: 16pt;">
                    <col style="width: 85pt;">
                    <col style="width: 85pt;">
                    <col style="width: 90pt;">
                    <col style="width: 94pt;">
                    <col style="width: 95pt;">
                    <col style="width: 95pt;">
                </colgroup>

                <tr>
                    <th class="num-head">N°</th>
                    <th class="partes-head" colspan="2">PARTES DEL EQUIPO</th>
                    <th class="cond-head">CONDICION</th>
                    <th class="obs-head" colspan="3">OBSERVACIONES</th>
                </tr>

                @foreach($items as $item)
                    @php
                        $condicion = $formatCondicion(data_get($answers, $item['estado'], ''));
                        $observacion = data_get($answers, $item['obs'], '');
                    @endphp
                    <tr>
                        <td class="num-cell">{{ $item['n'] }}</td>
                        <td class="partes-cell" colspan="2">{{ $item['label'] }}</td>
                        <td class="cond-cell cond-value">{{ $condicion }}</td>
                        <td class="obs-cell" colspan="3">{{ $observacion }}</td>
                    </tr>
                @endforeach
            </table>

            @if($machineImageSrc)
                <div class="machine-image-block">
                    <img src="{{ $machineImageSrc }}" alt="Imagen máquina de soldar">
                </div>
            @endif

            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="signature-box">
                            @if($firmaInspectorSrc)
                                <img src="{{ $firmaInspectorSrc }}" alt="Firma inspector">
                            @endif
                            <div class="signature-name">{{ $nombreInspectorValor }}</div>
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Nombre y firma del inspector</div>
                    </td>

                    <td>
                        <div class="signature-box">
                            @if($firmaSupervisorSrc)
                                <img src="{{ $firmaSupervisorSrc }}" alt="Firma supervisor">
                            @endif
                            <div class="signature-name">{{ $nombreSupervisorValor }}</div>
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Nombre y firma del supervisor</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>