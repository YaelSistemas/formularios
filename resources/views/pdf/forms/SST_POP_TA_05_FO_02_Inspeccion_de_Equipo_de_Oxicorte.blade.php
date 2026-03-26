<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Inspección de Equipo de Oxicorte' }}</title>
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

        .row-3-center {
            font-size: 10px;
            text-transform: uppercase;
        }

        .info-block {
            width: 544px;
            margin: 24px auto 0 auto;
        }

        .info-row {
            display: block;
            width: 100%;
            margin-bottom: 12px;
        }

        .info-row-inline {
            display: flex;
            align-items: flex-end;
            gap: 16px;
            white-space: nowrap;
        }

        .info-group {
            display: inline-block;
            vertical-align: bottom;
        }

        .info-group-inline {
            display: inline-flex;
            align-items: flex-end;
        }

        .info-label {
            display: inline-block;
            font-weight: bold;
            vertical-align: bottom;
            text-align: right;
            margin-right: 8px;
            width: 110px;
            white-space: normal;
            line-height: 1.1;
        }

        .info-label-short {
            display: inline-block;
            font-weight: bold;
            vertical-align: bottom;
            text-align: right;
            margin-right: 8px;
            width: 82px;
            white-space: nowrap;
            line-height: 1.1;
        }

        .info-line {
            display: inline-block;
            width: 160px;
            border-bottom: 1px solid #000;
            vertical-align: bottom;
            text-align: center;
            height: 14px;
        }

        .info-line-short {
            display: inline-block;
            width: 160px;
            border-bottom: 1px solid #000;
            vertical-align: bottom;
            text-align: center;
            height: 14px;
        }

        .info-value {
            font-size: 10px;
            line-height: 1;
            display: inline-block;
        }

        .info-equipo {
            margin-left: 12px;
        }

        .inspection-table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .inspection-table th,
        .inspection-table td {
            border: 1px solid #000;
            font-size: 8px;
            line-height: 1.1;
            padding: 3px 4px;
            box-sizing: border-box;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .inspection-table th {
            font-weight: bold;
            text-align: center;
            background-color: #d9d9d9;
        }

        .cond-header {
            text-align: center;
            font-weight: bold;
        }

        .item-merged {
            text-align: left;
            font-weight: bold;
            padding-left: 6px;
        }

        .estado-col {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        .croquis-header {
            text-align: center;
            font-weight: bold;
        }

        .croquis-cell {
            text-align: center;
            vertical-align: middle;
            padding: 6px;
        }

        .croquis-cell img {
            max-width: 100%;
            max-height: 280px;
            object-fit: contain;
        }

        .obs-title {
            text-align: center;
            font-weight: bold;
            background-color: #d9d9d9;
        }

        .obs-note {
            text-align: center;
            font-weight: bold;
            line-height: 1.2;
        }

        .obs-body {
            vertical-align: middle;
            text-align: center;
            padding: 8px;
            line-height: 1.3;
        }

        .nota-final {
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        .signatures-table {
            width: 100%;
            margin-top: 70px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 26px;
            box-sizing: border-box;
        }

        .signature-box {
            width: 100%;
            text-align: center;
        }

        .signature-image-wrap {
            height: 72px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: -20px;
        }

        .signature-image {
            max-width: 180px;
            max-height: 70px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.2;
            min-height: 12px;
            margin-bottom: 0;
        }

        .signature-line {
            width: 210px;
            margin: 0 auto 6px auto;
            border-top: 1px solid #000;
            height: 0;
        }

        .signature-label {
            margin-top: 0;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
        }

        .x-mark {
            font-size: 11px;
            font-weight: bold;
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

        $logo = $getField('encabezado_logo');
        $logoSrc = $toImageSrc(data_get($logo, 'url'));
        $guideImageSrc = $toImageSrc('images/forms/SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte/Imagen_Oxicorte.png');

        $fechaValor = optional($submission->created_at)->format('d/m/Y') ?: '';
        $empresaValor = data_get($answers, 'taller', '') ?: '';
        $numeroEquipoValor = data_get($answers, 'numero_identificacion_equipo_oxicorte', '') ?: '';
        $observacionesValor = data_get($answers, 'observaciones', '') ?: '';

        $nombreInspectorValor = data_get($answers, 'nombre_inspector', '') ?: '';
        $firmaInspectorSrc = $toImageSrc(data_get($answers, 'firma_inspector', ''));

        $nombreSupervisorValor = data_get($answers, 'nombre_supervisor', '') ?: '';
        $firmaSupervisorSrc = $toImageSrc(data_get($answers, 'firma_supervisor', ''));

        $items = [
            ['n' => 1, 'label' => 'Carro Porta Cilindros con Cadena', 'estado' => 'carro_porta_cilindros_cadena_estado'],
            ['n' => 2, 'label' => 'Estado Físico de los Cilindros', 'estado' => 'estado_fisico_cilindros_estado'],
            ['n' => 3, 'label' => 'Regulador de Oxígeno', 'estado' => 'regulador_oxigeno_estado'],
            ['n' => 4, 'label' => 'Manómetro de Alta Presión, Contenido', 'estado' => 'manometro_alta_presion_oxigeno_estado'],
            ['n' => 5, 'label' => 'Manómetro de Baja Presión, Trabajo', 'estado' => 'manometro_baja_presion_oxigeno_estado'],
            ['n' => 6, 'label' => 'Válvula Check Regulador de Oxígeno', 'estado' => 'valvula_check_regulador_oxigeno_estado'],
            ['n' => 7, 'label' => 'Regulador de Acetileno', 'estado' => 'regulador_acetileno_estado'],
            ['n' => 8, 'label' => 'Manómetro de Alta Presión, Contenido', 'estado' => 'manometro_alta_presion_acetileno_estado'],
            ['n' => 9, 'label' => 'Manómetro de Baja Presión, Trabajo', 'estado' => 'manometro_baja_presion_acetileno_estado'],
            ['n' => 10, 'label' => 'Válvula Check Regulador de Acetileno', 'estado' => 'valvula_check_regulador_acetileno_estado'],
            ['n' => 11, 'label' => 'Manguera de Oxígeno', 'estado' => 'manguera_oxigeno_estado'],
            ['n' => 12, 'label' => 'Válvula Check Maneral de Oxígeno', 'estado' => 'valvula_check_maneral_oxigeno_estado'],
            ['n' => 13, 'label' => 'Manguera de Acetileno', 'estado' => 'manguera_acetileno_estado'],
            ['n' => 14, 'label' => 'Válvula Check Maneral de Acetileno', 'estado' => 'valvula_check_maneral_acetileno_estado'],
            ['n' => 15, 'label' => 'Abrazaderas', 'estado' => 'abrazaderas_estado'],
            ['n' => 16, 'label' => 'Maneral Mezclador de Gases', 'estado' => 'maneral_mezclador_gases_estado'],
            ['n' => 17, 'label' => 'Llave Dosificadora de Oxígeno', 'estado' => 'llave_dosificadora_oxigeno_estado'],
            ['n' => 18, 'label' => 'Llave Dosificadora de Acetileno', 'estado' => 'llave_dosificadora_acetileno_estado'],
            ['n' => 19, 'label' => 'Boquilla de Corte o Soldadura', 'estado' => 'boquilla_corte_soldadura_estado'],
            ['n' => 20, 'label' => 'Tuercas Roscadas de Unión y Empaques', 'estado' => 'tuercas_roscadas_union_empaques_estado'],
            ['n' => 21, 'label' => 'Limpia Boquillas', 'estado' => 'limpia_boquillas_estado'],
            ['n' => 22, 'label' => 'Chispero', 'estado' => 'chispero_estado'],
            ['n' => 23, 'label' => 'Llave de Cuadro de Acetileno', 'estado' => 'llave_cuadro_acetileno_estado'],
            ['n' => 24, 'label' => 'Extintor Cercano al Área de Trabajo', 'estado' => 'extintor_cercano_area_trabajo_estado'],
        ];
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
                <td class="center-cell">VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.</td>
                <td class="right-cell">CODIFICACIÓN: SST-POP-TA-05-FO-02</td>
            </tr>
            <tr>
                <td class="center-cell">SISTEMA DE GESTIÓN INTEGRAL</td>
                <td class="right-cell">FECHA DE EMISIÓN: 27/03/2025</td>
            </tr>
            <tr>
                <td class="center-cell row-3-center">INSPECCIÓN DE EQUIPO DE OXICORTE</td>
                <td class="right-cell">NÚMERO DE REVISIÓN: 03</td>
            </tr>
        </table>

        <div class="info-block">
            <div class="info-row">
                <span class="info-group">
                    <span class="info-label">Fecha:</span><span class="info-line"><span class="info-value">{{ $fechaValor }}</span></span>
                </span>
            </div>

            <div class="info-row info-row-inline">
                <span class="info-group-inline">
                    <span class="info-label">Empresa / Unidad de Servicio:</span><span class="info-line"><span class="info-value">{{ $empresaValor }}</span></span>
                </span>

                <span class="info-group-inline info-equipo">
                    <span class="info-label-short">No. de Equipo:</span><span class="info-line-short"><span class="info-value">{{ $numeroEquipoValor }}</span></span>
                </span>
            </div>
        </div>

        <table class="inspection-table">
            <colgroup>
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 16%;">
                <col style="width: 4%;">
                <col style="width: 4%;">
                <col style="width: 11.2%;">
                <col style="width: 11.2%;">
                <col style="width: 11.2%;">
                <col style="width: 11.2%;">
                <col style="width: 11.2%;">
            </colgroup>
            <tr>
                <th colspan="6" class="cond-header">CONDICIONES DE ACCESORIOS</th>
                <th>BIEN</th>
                <th>MAL</th>
                <th colspan="5" class="croquis-header">CROQUIS GUIA PUNTOS A INSPECCIONAR</th>
            </tr>

            @foreach($items as $index => $item)
                @php
                    $estado = strtoupper(trim((string) data_get($answers, $item['estado'], '')));
                @endphp
                <tr>
                    <td colspan="6" class="item-merged">{{ $item['n'] }}. {{ $item['label'] }}</td>
                    <td class="estado-col">
                        @if($estado === 'BIEN')
                            <span class="x-mark">X</span>
                        @endif
                    </td>
                    <td class="estado-col">
                        @if($estado === 'MAL')
                            <span class="x-mark">X</span>
                        @endif
                    </td>

                    @if($index === 0)
                        <td colspan="5" rowspan="15" class="croquis-cell">
                            @if($guideImageSrc)
                                <img src="{{ $guideImageSrc }}" alt="Croquis guía">
                            @endif
                        </td>
                    @elseif($index === 15)
                        <td colspan="5" class="obs-title">OBSERVACIONES</td>
                    @elseif($index === 16)
                        <td colspan="5" class="obs-note">Verificar con jabonadura todas las conexiones del equipo.</td>
                    @elseif($index === 17)
                        <td colspan="5" rowspan="7" class="obs-body">{{ $observacionesValor }}</td>
                    @endif
                </tr>
            @endforeach
        </table>

        <div class="nota-final">
            NOTA: SI EL EQUIPO TIENE DEFICIENCIAS, SUSPENDER SU USO DE INMEDIATO.
        </div>

        <table class="signatures-table">
            <tr>
                <td>
                    <div class="signature-box">
                        <div class="signature-image-wrap">
                            @if($firmaInspectorSrc)
                                <img src="{{ $firmaInspectorSrc }}" alt="Firma del Inspector" class="signature-image">
                            @endif
                        </div>
                        <div class="signature-name">{{ $nombreInspectorValor }}</div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Nombre y Firma del Inspector</div>
                    </div>
                </td>
                <td>
                    <div class="signature-box">
                        <div class="signature-image-wrap">
                            @if($firmaSupervisorSrc)
                                <img src="{{ $firmaSupervisorSrc }}" alt="Firma del Supervisor" class="signature-image">
                            @endif
                        </div>
                        <div class="signature-name">{{ $nombreSupervisorValor }}</div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Nombre y Firma del Supervisor</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>