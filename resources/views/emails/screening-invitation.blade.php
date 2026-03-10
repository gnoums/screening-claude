<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; color: #333; font-size:14px; background:#f4f4f7; margin:0; padding:0; }
    .container { max-width: 560px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
    .header { background: #5a7123; padding: 24px 32px; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; }
    .body { padding: 28px 32px; }
    .btn { display: inline-block; padding: 14px 28px; background: #5a7123; color: #fff !important;
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
        <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="body">
        <p>{{ __('Hi,') }}</p>

        <p>
            <strong>{{ $request->user->name }}</strong>
            {{ __('has sent you a psychological screening test to complete online in a confidential manner.') }}
        </p>

        @if($request->message_to_patient)
            <p style="background:#f5f5f5;padding:12px;border-radius:4px;font-style:italic;">
                &ldquo;{{ $request->message_to_patient }}&rdquo;
            </p>
        @endif

        <p><strong>{{ __('Tests included:') }}</strong></p>
        <ul>
            @foreach($request->items as $item)
                <li>{{ $item->assessment->name }}
                    @if($item->assessment->estimated_minutes)
                        ({{ __('approx. :minutes minutes', ['minutes' => $item->assessment->estimated_minutes]) }})
                    @endif
                </li>
            @endforeach
        </ul>

        <p>{{ __('To respond, click the button:') }}</p>

        <a href="{{ $accessUrl }}" class="btn">{{ __('Start screening test') }}</a>

        <p style="font-size:12px;color:#888;">
            {{ __('Or copy and paste this link in your browser:') }}<br>
            <span style="color:#5a7123;word-break:break-all;">{{ $accessUrl }}</span>
        </p>

        <div class="warning">
            <strong>{{ __('Privacy note:') }}</strong>
            {{ __('This link is personal and non-transferable. It expires in :hours hours and can only be used once. Do not share it with anyone.', ['hours' => config('screening.token_ttl_hours')]) }}
        </div>
    </div>

    <div class="footer">
        {{ __('My Progress Matters · Psychological Screening System · If you were not expecting this email, you may ignore it.') }}
    </div>

</div>
</body>
</html>
