<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        padding: 30px 40px;
        line-height: 1.5;
    }
    .header {
        border-bottom: 3px solid #4a6fa5;
        padding-bottom: 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .app-name { font-size: 20px; font-weight: bold; color: #4a6fa5; }
    .psychologist-block { text-align: right; font-size: 10px; color: #555; }
    .section-title {
        background: #4a6fa5;
        color: white;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: bold;
        margin: 18px 0 8px;
        border-radius: 3px;
    }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th {
        background: #e8eef7;
        padding: 6px 10px;
        text-align: left;
        font-size: 10px;
        color: #333;
    }
    td { padding: 5px 10px; border-bottom: 1px solid #e4e4e4; font-size: 10px; }
    .score-box {
        border: 2px solid;
        border-radius: 6px;
        padding: 10px 16px;
        margin: 10px 0;
        display: inline-block;
    }
    .score-label { font-size: 18px; font-weight: bold; }
    .interpretation { font-size: 10px; margin-top: 4px; color: #444; }
    .disclaimer {
        border: 1px solid #f0ad4e;
        background: #fff8e7;
        padding: 10px 14px;
        margin-top: 24px;
        border-radius: 4px;
        font-size: 9px;
        color: #7a5c00;
    }
    .footer {
        border-top: 1px solid #ccc;
        margin-top: 30px;
        padding-top: 8px;
        font-size: 9px;
        color: #999;
        text-align: center;
    }
    .answer-row td:first-child { color: #555; width: 55%; }
    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
    }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<div class="header">
    <div>
        <div class="app-name">PsicoScreen</div>
        <div style="font-size:10px;color:#777;margin-top:2px;">Reporte de Tamizaje Psicológico</div>
    </div>
    <div class="psychologist-block">
        <strong>{{ $psychologist->name }}</strong><br>
        @if($psychologist->profile?->professional_license)
            Cédula: {{ $psychologist->profile->professional_license }}<br>
        @endif
        @if($psychologist->profile?->specialty)
            {{ $psychologist->profile->specialty }}<br>
        @endif
        {{ $psychologist->email }}
    </div>
</div>

{{-- DATOS DEL PACIENTE --}}
<div class="section-title">Datos del Paciente</div>
<table>
    <tr>
        <th style="width:25%">Nombre completo</th>
        <td>{{ $patient->full_name }}</td>
        <th style="width:15%">Sexo</th>
        <td>
            @php
                $sexLabels = [
                    'male'              => 'Masculino',
                    'female'            => 'Femenino',
                    'other'             => 'Otro',
                    'prefer_not_to_say' => 'Prefiero no decir',
                ];
            @endphp
            {{ $sexLabels[$patient->sex] ?? '—' }}
        </td>
    </tr>
    <tr>
        <th>Fecha de nacimiento</th>
        <td>{{ $patient->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
        <th>Edad</th>
        <td>{{ $patient->age ? $patient->age . ' años' : '—' }}</td>
    </tr>
</table>

{{-- INFO DE LA EVALUACIÓN --}}
<div class="section-title">Información de la Evaluación</div>
<table>
    <tr>
        <th style="width:30%">Fecha de aplicación</th>
        <td>{{ $request->completed_at?->setTimezone('America/Mexico_City')->format('d/m/Y H:i') ?? '—' }}</td>
        <th style="width:20%">Fecha del reporte</th>
        <td>{{ $generatedAt->setTimezone('America/Mexico_City')->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <th>Prueba(s) aplicada(s)</th>
        <td colspan="3">
            {{ $items->map(fn($i) => $i->assessment->name)->implode(', ') }}
        </td>
    </tr>
</table>

{{-- RESULTADOS POR PRUEBA --}}
@foreach($items as $item)
    @if($item->response)
        @php
            $response = $item->response;
            $colorMap = [
                'Mínima'   => ['#27ae60', '#e9f7ef'],
                'Leve'     => ['#f39c12', '#fef9e7'],
                'Moderada' => ['#e67e22', '#fdf2e9'],
                'Severa'   => ['#c0392b', '#fdedec'],
            ];
            $colors = $colorMap[$response->severity_label] ?? ['#555', '#f0f0f0'];
        @endphp

        <div class="section-title">
            {{ $item->assessment->name }}
            @if($item->assessment->short_name)
                ({{ $item->assessment->short_name }})
            @endif
        </div>

        {{-- Resultado --}}
        <div class="score-box" style="border-color:{{ $colors[0] }};background:{{ $colors[1] }};">
            <div style="font-size:11px;color:#555;">Puntaje total</div>
            <div class="score-label" style="color:{{ $colors[0] }};">
                {{ $response->total_score }}
                &nbsp;—&nbsp;
                <span class="badge" style="background:{{ $colors[0] }};color:white;">
                    {{ $response->severity_label }}
                </span>
            </div>
            <div class="interpretation">{{ $response->interpretation_text }}</div>
        </div>

        {{-- Respuestas individuales --}}
        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th style="width:35%">Respuesta seleccionada</th>
                    <th style="width:10%;text-align:center">Puntaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($response->answers->sortBy(fn($a) => $a->question->order) as $answer)
                <tr class="answer-row">
                    <td>{{ $answer->question->question_text }}</td>
                    <td>{{ $answer->option_text_snapshot }}</td>
                    <td style="text-align:center">{{ $answer->score_value_snapshot }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size:9px;color:#777;margin-bottom:8px;">
            Versión de la escala: {{ $response->assessment_version_snapshot ?? 'N/A' }}
        </p>
    @endif
@endforeach

{{-- DISCLAIMER --}}
<div class="disclaimer">
    <strong>AVISO IMPORTANTE:</strong>
    {{ config('screening.pdf_disclaimer') }}
</div>

{{-- PIE DE PÁGINA --}}
<div class="footer">
    Generado por PsicoScreen · {{ $generatedAt->format('d/m/Y H:i') }} ·
    Reporte ID: {{ $request->id }}
</div>

</body>
</html>
