<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invalid link – My Progress Matters') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #f4f7ee; display: flex; align-items: center; justify-content: center;
               min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 40px; border-radius: 12px; max-width: 440px;
               text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .icon { font-size: 56px; margin-bottom: 16px; }
        h1 { font-size: 1.3rem; color: #c53030; margin-bottom: 12px; }
        p { color: #4a5568; font-size: .95rem; line-height: 1.6; }
        .lang-switch { font-size: .75rem; margin-top: 20px; }
        .lang-switch a { color: #a0aec0; text-decoration: none; padding: 2px 5px; border-radius: 4px; }
        .lang-switch a.active { font-weight: bold; color: #5a7123; }
        .lang-switch a:hover { color: #5a7123; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🔒</div>
        <h1>{{ __('Link not available') }}</h1>
        <p>{{ $message }}</p>
        <p style="margin-top:20px;font-size:.85rem;color:#a0aec0;">
            {{ __('If you believe this is an error, contact your psychologist to send you a new link.') }}
        </p>
        <div class="lang-switch">
            <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            &nbsp;|&nbsp;
            <a href="{{ route('language.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
        </div>
    </div>
</body>
</html>
