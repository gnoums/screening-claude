<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; color: #333; font-size:14px; background:#f4f4f7; margin:0; padding:0; }
    .container { max-width: 560px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
    .header { background: #4a6fa5; padding: 24px 32px; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; }
    .body { padding: 28px 32px; }
    .btn { display: inline-block; padding: 14px 28px; background: #4a6fa5; color: #fff !important;
           text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold; margin: 20px 0; }
    .meta { font-size: 12px; color: #888; border-top: 1px solid #eee; margin-top: 24px; padding-top: 12px; }
    .warning { background: #fff8e1; border-left: 4px solid #ffc107; padding: 10px 14px;
               font-size: 12px; color: #7a5c00; margin-top: 16px; border-radius: 3px; }
    .footer { padding: 14px 32px; background: #f0f0f0; font-size: 11px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>PsicoScreen</h1>
    </div>

    <div class="body">
        <p>Hola,</p>

        <p>
            <strong>{{ $request->user->name }}</strong> te ha enviado una evaluación psicológica
            para que la completes en línea de manera confidencial.
        </p>

        @if($request->message_to_patient)
            <p style="background:#f5f5f5;padding:12px;border-radius:4px;font-style:italic;">
                &ldquo;{{ $request->message_to_patient }}&rdquo;
            </p>
        @endif

        <p><strong>Prueba(s) incluidas:</strong></p>
        <ul>
            @foreach($request->items as $item)
                <li>{{ $item->assessment->name }}
                    @if($item->assessment->estimated_minutes)
                        (aprox. {{ $item->assessment->estimated_minutes }} minutos)
                    @endif
                </li>
            @endforeach
        </ul>

        <p>Para responder, haz clic en el botón:</p>

        <a href="{{ $accessUrl }}" class="btn">Iniciar evaluación</a>

        <p style="font-size:12px;color:#888;">
            O copia y pega este enlace en tu navegador:<br>
            <span style="color:#4a6fa5;word-break:break-all;">{{ $accessUrl }}</span>
        </p>

        <div class="warning">
            <strong>Nota de privacidad:</strong>
            Este enlace es personal e intransferible.
            Expira en {{ config('screening.token_ttl_hours') }} horas y
            solo puede usarse una vez. No lo compartas con nadie.
        </div>
    </div>

    <div class="footer">
        PsicoScreen · Sistema de Tamizaje Psicológico ·
        Si no esperabas este correo, puedes ignorarlo.
    </div>

</div>
</body>
</html>
