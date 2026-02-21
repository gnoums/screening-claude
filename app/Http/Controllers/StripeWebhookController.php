<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Recibe y procesa webhooks de Stripe.
 *
 * Esta ruta debe ser excluida del middleware VerifyCsrfToken.
 * Ver: App\Http\Middleware\VerifyCsrfToken (o bootstrap/app.php en L11)
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly BillingService $billing,
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $this->billing->handleWebhook($payload, $sigHeader);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature inválida', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook payload inválido', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        } catch (\Throwable $e) {
            Log::error('Error procesando webhook de Stripe', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retornar 200 para que Stripe no reintente por errores internos
            return response('Webhook received with errors', 200);
        }

        return response('OK', 200);
    }
}
