<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title ?? 'Registro' }}</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.45;
        }

        .page {
            width: 100%;
        }

        .header {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta {
            font-size: 11px;
            color: #4b5563;
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 18px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }

        .field-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .field-label {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .field-value {
            font-size: 12px;
            color: #111827;
            word-break: break-word;
        }

        .muted {
            color: #6b7280;
        }

        .table-wrap {
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .signature-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #fff;
        }

        .signature-box img {
            max-width: 260px;
            max-height: 120px;
        }

        .static-text {
            white-space: pre-line;
        }
    </style>
</head>
<body>
    @php
        $nonInputTypes = ['separator', 'fixed_file'];
        $fieldsById = collect($fields ?? [])->keyBy('id');
    @endphp

    <div class="page">
        <div class="header">
            <div class="title">{{ $form->title ?? 'Registro' }}</div>
            <div class="meta">Registro #{{ $submission->id ?? '—' }}</div>
            <div class="meta">Usuario: {{ $userName ?: '—' }}</div>
            <div class="meta">Fecha del registro: {{ optional($submission->created_at)->format('d/m/Y H:i') ?: '—' }}</div>
            <div class="meta">PDF generado: {{ optional($generatedAt)->format('d/m/Y H:i') ?: now()->format('d/m/Y H:i') }}</div>
        </div>

        <div class="section-title">Información capturada</div>

        @forelse(($fields ?? []) as $field)
            @php
                $id = $field['id'] ?? null;
                $type = $field['type'] ?? 'text';
                $label = $field['label'] ?? $id ?? 'Campo';
                $value = $id ? ($answers[$id] ?? null) : null;
            @endphp

            @if(!$id || in_array($type, $nonInputTypes, true))
                @continue
            @endif

            @if($type === 'separator')
                @continue
            @endif

            @if($type === 'static_text')
                <div class="field-card">
                    <div class="field-label">{{ $label }}</div>
                    <div class="field-value static-text">{{ $field['text'] ?? '' }}</div>
                </div>
                @continue
            @endif

            @if($type === 'fixed_image')
                <div class="field-card">
                    <div class="field-label">{{ $label }}</div>
                    <div class="field-value">
                        @if(!empty($field['url']))
                            <img src="{{ $field['url'] }}" alt="{{ $label }}" style="max-width: 300px; max-height: 160px;">
                        @else
                            <span class="muted">Sin imagen.</span>
                        @endif
                    </div>
                </div>
                @continue
            @endif

            @if($type === 'table')
                @php
                    $rows = is_array($value) ? $value : [];
                    $rowSchema = is_array($field['row_schema'] ?? null) ? $field['row_schema'] : [];
                @endphp

                <div class="field-card">
                    <div class="field-label">{{ $label }}</div>

                    @if(count($rows))
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach($rowSchema as $col)
                                            <th>{{ $col['label'] ?? ($col['id'] ?? 'Columna') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            @foreach($rowSchema as $col)
                                                @php
                                                    $colId = $col['id'] ?? null;
                                                    $cell = $colId ? ($row[$colId] ?? null) : null;
                                                @endphp
                                                <td>
                                                    @if(is_bool($cell))
                                                        {{ $cell ? 'Sí' : 'No' }}
                                                    @elseif(is_array($cell))
                                                        {{ json_encode($cell, JSON_UNESCAPED_UNICODE) }}
                                                    @elseif($cell !== null && $cell !== '')
                                                        {{ $cell }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="field-value muted">Sin registros.</div>
                    @endif
                </div>
                @continue
            @endif

            @if($type === 'signature')
                @php
                    $signatureSrc = null;

                    if (is_string($value) && str_starts_with($value, 'data:image')) {
                        $signatureSrc = $value;
                    } elseif (is_string($value) && $value !== '') {
                        $fullPath = public_path('storage/' . ltrim($value, '/'));
                        if (file_exists($fullPath)) {
                            $mime = mime_content_type($fullPath) ?: 'image/png';
                            $signatureSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                        }
                    }
                @endphp

                <div class="field-card">
                    <div class="field-label">{{ $label }}</div>
                    <div class="field-value">
                        @if($signatureSrc)
                            <div class="signature-box">
                                <img src="{{ $signatureSrc }}" alt="Firma">
                            </div>
                        @else
                            <span class="muted">Sin firma.</span>
                        @endif
                    </div>
                </div>
                @continue
            @endif

            <div class="field-card">
                <div class="field-label">{{ $label }}</div>
                <div class="field-value">
                    @if(is_bool($value))
                        {{ $value ? 'Sí' : 'No' }}
                    @elseif(is_array($value))
                        {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                    @elseif($value !== null && $value !== '')
                        {{ $value }}
                    @else
                        <span class="muted">—</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="field-card">
                <div class="field-value muted">No hay campos configurados para este formulario.</div>
            </div>
        @endforelse
    </div>
</body>
</html>