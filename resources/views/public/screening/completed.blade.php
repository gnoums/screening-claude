<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Assessment completed – PsicoScreen') }}</title>
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
        .lang-switch { font-size: .75rem; margin-top: 20px; }
        .lang-switch a { color: #a0aec0; text-decoration: none; padding: 2px 5px; border-radius: 4px; }
        .lang-switch a.active { font-weight: bold; color: #4a6fa5; }
        .lang-switch a:hover { color: #4a6fa5; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">✅</div>
        <h1>{{ __('Assessment completed!') }}</h1>
        <p>
            {{ __('Your answers have been submitted successfully. Your psychologist will review the results and contact you soon.') }}
        </p>
        <div class="disclaimer">
            {{ config('screening.pdf_disclaimer') }}
        </div>
        <div class="lang-switch">
            <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            &nbsp;|&nbsp;
            <a href="{{ route('language.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
        </div>
    </div>
</body>
</html>
