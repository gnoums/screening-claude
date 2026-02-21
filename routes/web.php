<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Psychologist\DashboardController;
use App\Http\Controllers\Psychologist\PatientController;
use App\Http\Controllers\Psychologist\ReportController;
use App\Http\Controllers\Psychologist\ScreeningRequestController;
use App\Http\Controllers\Public\ScreeningController;
use Illuminate\Support\Facades\Route;

// ====================================================================
// Rutas públicas (sin autenticación) — Acceso de paciente vía token
// Rate limit: 30 req/min por IP (configurado en config/screening.php)
// ====================================================================

// Esta ruta DEBE declararse ANTES de /s/{token} para que "gracias"
// no sea capturado como valor del parámetro token.
Route::get('/s/gracias', [ScreeningController::class, 'completed'])
    ->name('public.screening.completed');

Route::middleware('throttle:public-screening')->group(function () {
    Route::get('/s/{token}', [ScreeningController::class, 'show'])
        ->name('public.screening.show');

    Route::post('/s/{token}/submit', [ScreeningController::class, 'submit'])
        ->name('public.screening.submit');
});

// ====================================================================
// Landing page
// ====================================================================

Route::get('/', fn () => view('welcome'))->name('home');

// ====================================================================
// Área autenticada — Psicóloga
// ====================================================================

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Pacientes
    Route::resource('patients', PatientController::class)
        ->names('psychologist.patients');

    // Solicitudes de tamizaje
    Route::resource('screenings', ScreeningRequestController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('psychologist.screenings');

    Route::post('screenings/{screening}/resend', [ScreeningRequestController::class, 'resend'])
        ->name('psychologist.screenings.resend');

    // Exportar PDF de resultados
    Route::get('screenings/{screening}/pdf', [ReportController::class, 'download'])
        ->name('psychologist.screenings.pdf');

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
