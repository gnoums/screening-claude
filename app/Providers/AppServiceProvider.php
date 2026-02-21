<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    // ----------------------------------------------------------------

    protected function configureRateLimiting(): void
    {
        // Rate limiter para endpoints públicos del paciente
        RateLimiter::for('public-screening', function (Request $request) {
            $max = (int) config('screening.public_rate_limit', 30);

            return Limit::perMinute($max)
                ->by($request->ip())
                ->response(function () {
                    abort(429, 'Demasiadas solicitudes. Intenta de nuevo en un momento.');
                });
        });

        // Rate limiter de autenticación (por defecto en Breeze)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
