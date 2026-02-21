<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación completada – PsicoScreen</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #f0f4f8; display: flex; align-items: center; justify-content: center;
               min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 40px; border-radius: 12px; max-width: 480px;
               text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .icon { font-size: 64px; margin-bottom: 16px; }
        h1 { font-size: 1.5rem; color: #27ae60; margin-bottom: 12px; }
        p { color: #4a5568; font-size: .95rem; line-height: 1.6; }
        .disclaimer { background: #fffbea; border: 1px solid #f6e05e; border-radius: 6px;
                      padding: 12px; margin-top: 24px; font-size: .8rem; color: #744210; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">✅</div>
        <h1>¡Evaluación completada!</h1>
        <p>
            Tus respuestas han sido enviadas exitosamente.
            Tu psicóloga revisará los resultados y se pondrá en contacto contigo pronto.
        </p>
        <div class="disclaimer">
            {{ config('screening.pdf_disclaimer') }}
        </div>
    </div>
</body>
</html>
