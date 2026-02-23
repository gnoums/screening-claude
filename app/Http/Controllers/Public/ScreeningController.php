<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\InvalidTokenException;
use App\Exceptions\ScoringException;
use App\Http\Controllers\Controller;
use App\Models\ScreeningToken;
use App\Services\ScoringService;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controlador público — accesible sin autenticación vía token de paciente.
 *
 * Rutas (sin prefijo de auth):
 *   GET  /s/{token}          → Muestra el formulario de tamizaje
 *   POST /s/{token}/submit   → Recibe y procesa las respuestas
 */
class ScreeningController extends Controller
{
    public function __construct(
        private readonly TokenService  $tokenService,
        private readonly ScoringService $scoringService,
    ) {}

    /**
     * Muestra el formulario de tamizaje al paciente.
     */
    public function show(string $token, Request $request): Response|RedirectResponse
    {
        try {
            $screeningToken = $this->tokenService->validate($token, $request);
        } catch (InvalidTokenException $e) {
            return response()
                ->view('public.screening.invalid-token', [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        }

        $screeningRequest = $screeningToken->screeningRequest->load([
            'patient',
            'user',
            'items' => fn ($q) => $q->where('status', 'pending')->orderBy('order'),
            'items.assessment.questions.options',
        ]);

        // Si no quedan ítems pendientes, redirigir a agradecimiento
        if ($screeningRequest->items->isEmpty()) {
            return redirect()->route('public.screening.completed');
        }

        // La prueba activa es el primer ítem pendiente
        $currentItem = $screeningRequest->items->first();

        // Headers no-cache: evita que servidores intermedios (LiteSpeed, CDN)
        // sirvan una versión cacheada del formulario con un token ya expirado.
        return response()
            ->view('public.screening.show', [
                'screeningRequest' => $screeningRequest,
                'currentItem'      => $currentItem,
                'assessment'       => $currentItem->assessment,
                'token'            => $token,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Procesa las respuestas del paciente para el ítem actual.
     */
    public function submit(string $token, Request $request): View|RedirectResponse
    {
        try {
            $screeningToken = $this->tokenService->validate($token, $request);
        } catch (InvalidTokenException $e) {
            // Si el token solo está expirado (no invalidado ni ya usado), permitir
            // el envío: el paciente abrió el formulario antes del vencimiento y
            // tardó en completarlo (o hubo un desfase de caché/zona horaria).
            $rawToken = ScreeningToken::findByPlainToken($token);

            if ($rawToken && $rawToken->isExpired() && ! $rawToken->is_invalidated && ! $rawToken->hasBeenUsed()) {
                $screeningToken = $rawToken;
                $rawToken->update([
                    'last_ip'         => $request->ip(),
                    'last_user_agent' => $request->userAgent(),
                ]);
            } else {
                return view('public.screening.invalid-token', [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                ]);
            }
        }

        $screeningRequest = $screeningToken->screeningRequest->load([
            'items' => fn ($q) => $q->where('status', 'pending')->orderBy('order'),
            'items.assessment.questions',
        ]);

        $currentItem = $screeningRequest->items->first();

        if (! $currentItem) {
            return redirect()->route('public.screening.completed');
        }

        // Validar que venga un array de respuestas
        $validated = $request->validate([
            'answers'   => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'exists:assessment_options,id'],
        ]);

        try {
            $response = $this->scoringService->score($currentItem, $validated['answers']);
        } catch (ScoringException $e) {
            return back()
                ->withInput()
                ->withErrors(['scoring' => $e->getMessage()]);
        }

        // Agregar IP y user agent a la respuesta
        $response->update([
            'respondent_ip'         => $request->ip(),
            'respondent_user_agent' => $request->userAgent(),
            'started_at'            => $response->started_at ?? now(),
        ]);

        // Recargar solicitud para ver si quedan ítems
        $screeningRequest->refresh()->load([
            'items' => fn ($q) => $q->where('status', 'pending'),
        ]);

        // Si todas las pruebas están completas, invalidar el token
        if ($screeningRequest->items->isEmpty()) {
            $this->tokenService->invalidateAfterCompletion($screeningToken, $request);

            return redirect()->route('public.screening.completed');
        }

        // Quedan más pruebas: redirigir al mismo token (siguiente ítem)
        return redirect()->route('public.screening.show', ['token' => $token]);
    }

    /**
     * Pantalla de agradecimiento / confirmación.
     */
    public function completed(): View
    {
        return view('public.screening.completed');
    }
}
