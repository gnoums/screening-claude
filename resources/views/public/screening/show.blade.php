<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación Psicológica – {{ $assessment->name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8; color: #1a202c; margin: 0; padding: 20px;
        }
        .card {
            max-width: 680px; margin: 0 auto; background: #fff;
            border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08);
            padding: 32px;
        }
        .progress-bar-bg { background: #e2e8f0; border-radius: 99px; height: 8px; margin: 12px 0 24px; }
        .progress-bar-fill { background: #4a6fa5; height: 8px; border-radius: 99px; transition: width .3s; }
        h1 { font-size: 1.4rem; color: #2d3748; margin-bottom: 4px; }
        .subtitle { font-size: .9rem; color: #718096; margin-bottom: 24px; }
        .question-block { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .question-text { font-weight: 600; font-size: .95rem; margin-bottom: 12px; color: #2d3748; }
        .options-grid { display: grid; gap: 8px; }
        .option-label {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
            border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .option-label:hover { border-color: #4a6fa5; background: #ebf4ff; }
        input[type="radio"]:checked + .option-label { border-color: #4a6fa5; background: #ebf4ff; }
        input[type="radio"] { display: none; }
        .btn-submit {
            width: 100%; padding: 14px; background: #4a6fa5; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; font-weight: bold;
            cursor: pointer; margin-top: 16px;
        }
        .btn-submit:hover { background: #3a5a8a; }
        .error-msg { background: #fed7d7; color: #c53030; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
        .psychologist-note { font-size: .8rem; color: #a0aec0; text-align: center; margin-top: 24px; }
        @media (max-width: 480px) { .card { padding: 20px; } }
    </style>
</head>
<body>
<div class="card">

    {{-- Encabezado --}}
    <h1>{{ $assessment->name }}</h1>
    <p class="subtitle">
        Evaluación enviada por <strong>{{ $screeningRequest->user->name }}</strong>.
        Responde con honestidad según cómo te has sentido recientemente.
    </p>

    {{-- Progreso --}}
    @php
        $total     = $screeningRequest->items->count() + $screeningRequest->items->where('status','completed')->count();
        $completed = $screeningRequest->items->where('status','completed')->count();
        $pct       = $total > 0 ? round(($completed / $total) * 100) : 0;
    @endphp
    <div style="font-size:.8rem;color:#718096;">
        Prueba {{ $completed + 1 }} de {{ $total }}
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width:{{ $pct }}%"></div>
    </div>

    {{-- Errores --}}
    @if($errors->any())
        <div class="error-msg">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Formulario --}}
    <form method="POST" action="{{ route('public.screening.submit', ['token' => $token]) }}">
        @csrf

        @foreach($assessment->questions as $question)
            <div class="question-block">
                <div class="question-text">
                    {{ $loop->iteration }}. {{ $question->question_text }}
                    @if($question->is_required)
                        <span style="color:#e53e3e;font-size:.8rem;">(requerida)</span>
                    @endif
                </div>
                <div class="options-grid">
                    @foreach($question->options as $option)
                        <div>
                            <input
                                type="radio"
                                id="q{{ $question->id }}_o{{ $option->id }}"
                                name="answers[{{ $question->id }}]"
                                value="{{ $option->id }}"
                                {{ old("answers.{$question->id}") == $option->id ? 'checked' : '' }}
                            >
                            <label class="option-label" for="q{{ $question->id }}_o{{ $option->id }}">
                                {{ $option->option_text }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn-submit">
            Enviar respuestas →
        </button>
    </form>

    <p class="psychologist-note">
        Tus respuestas son confidenciales y solo serán vistas por
        {{ $screeningRequest->user->name }}.
    </p>
</div>
</body>
</html>
