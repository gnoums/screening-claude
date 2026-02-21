<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace no válido – PsicoScreen</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #f0f4f8; display: flex; align-items: center; justify-content: center;
               min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 40px; border-radius: 12px; max-width: 440px;
               text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .icon { font-size: 56px; margin-bottom: 16px; }
        h1 { font-size: 1.3rem; color: #c53030; margin-bottom: 12px; }
        p { color: #4a5568; font-size: .95rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🔒</div>
        <h1>Enlace no disponible</h1>
        <p>{{ $message }}</p>
        <p style="margin-top:20px;font-size:.85rem;color:#a0aec0;">
            Si crees que esto es un error, comunícate con tu psicóloga para
            que te envíe un nuevo enlace.
        </p>
    </div>
</body>
</html>
