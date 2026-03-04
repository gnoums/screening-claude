<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Psychologist\BillingController;
use App\Http\Controllers\Psychologist\DashboardController;
use App\Http\Controllers\Psychologist\PatientController;
use App\Http\Controllers\Psychologist\ReportController;
use App\Http\Controllers\Psychologist\ScreeningRequestController;
use App\Http\Controllers\Public\ScreeningController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// ====================================================================
// Webhook de Stripe — sin CSRF (excluido en bootstrap/app.php)
// ====================================================================

Route::post('stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook');

// ====================================================================
// Language switch — stores locale in session, then redirects back
// ====================================================================

Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'es'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('language.switch');

// ====================================================================
// Rutas públicas — Paciente vía token
// Esta ruta DEBE ir ANTES de /s/{token}
// ====================================================================

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

Route::get('/', fn () => redirect()->route('login'))->name('home');

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

    Route::post('screenings/{screening}/link', [ScreeningRequestController::class, 'getLink'])
        ->name('psychologist.screenings.get-link');

    // Exportar PDF de resultados
    Route::get('screenings/{screening}/pdf', [ReportController::class, 'download'])
        ->name('psychologist.screenings.pdf');

    // ----------------------------------------------------------------
    // Facturación y créditos (Stripe)
    // ----------------------------------------------------------------
    Route::prefix('billing')->name('psychologist.billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success', [BillingController::class, 'success'])->name('success');
    });

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ====================================================================
// Panel de administración — solo role=admin
// ====================================================================

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        // Gestión de usuarios
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/credits', [AdminUserController::class, 'adjustCredits'])->name('users.credits');
        Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

        // Audit logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Pruebas de tamizaje
        Route::get('assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('assessments/create', [AdminAssessmentController::class, 'create'])->name('assessments.create');
        Route::post('assessments', [AdminAssessmentController::class, 'store'])->name('assessments.store');
        Route::get('assessments/{assessment}', [AdminAssessmentController::class, 'show'])->name('assessments.show');
        Route::get('assessments/{assessment}/edit', [AdminAssessmentController::class, 'edit'])->name('assessments.edit');
        Route::patch('assessments/{assessment}', [AdminAssessmentController::class, 'update'])->name('assessments.update');

        // Preguntas
        Route::post('assessments/{assessment}/questions', [AdminAssessmentController::class, 'storeQuestion'])
            ->name('assessments.questions.store');
        Route::patch('questions/{question}', [AdminAssessmentController::class, 'updateQuestion'])
            ->name('assessments.questions.update');
        Route::delete('questions/{question}', [AdminAssessmentController::class, 'destroyQuestion'])
            ->name('assessments.questions.destroy');

        // Opciones
        Route::post('questions/{question}/options', [AdminAssessmentController::class, 'storeOption'])
            ->name('assessments.options.store');
        Route::delete('options/{option}', [AdminAssessmentController::class, 'destroyOption'])
            ->name('assessments.options.destroy');

        // Reglas de interpretación
        Route::post('assessments/{assessment}/rules', [AdminAssessmentController::class, 'storeRule'])
            ->name('assessments.rules.store');
        Route::delete('rules/{rule}', [AdminAssessmentController::class, 'destroyRule'])
            ->name('assessments.rules.destroy');
    });

// ====================================================================
// Debug temporal — solo admin — ELIMINAR después de diagnosticar
// ====================================================================
Route::middleware(['auth', 'admin'])->get('/admin/debug-log', function () {
    $logFile = storage_path('logs/laravel.log');
    if (! file_exists($logFile)) {
        return response('Log vacío o no existe.', 200)->header('Content-Type', 'text/plain');
    }
    $lines  = array_slice(file($logFile), -150);
    return response(implode('', $lines), 200)->header('Content-Type', 'text/plain');
})->name('admin.debug-log');

require __DIR__ . '/auth.php';
