<?php

namespace App\Services;

use App\Models\StripePayment;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * Gestiona la integración con Stripe Checkout para compra de créditos.
 */
class BillingService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Crea una Stripe Checkout Session para un paquete de créditos.
     *
     * @throws \InvalidArgumentException Si el paquete no existe
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCheckoutSession(User $user, string $packageKey): Session
    {
        $package = config("stripe.credit_packages.{$packageKey}");

        if (! $package) {
            throw new \InvalidArgumentException("Paquete de créditos '{$packageKey}' no encontrado.");
        }

        $session = $this->stripe->checkout->sessions->create([
            'mode'                => 'payment',
            'customer_email'      => $user->email,
            'client_reference_id' => (string) $user->id,
            'line_items'          => [
                [
                    'price'    => $package['price_id'],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'user_id'        => $user->id,
                'package_key'    => $packageKey,
                'credits_amount' => $package['credits'],
            ],
            'success_url' => route('psychologist.billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('psychologist.billing.index'),
            'locale'      => 'es',
        ]);

        // Registrar el intento de pago antes de que el usuario pague
        StripePayment::create([
            'user_id'          => $user->id,
            'stripe_session_id' => $session->id,
            'package_key'      => $packageKey,
            'credits_amount'   => $package['credits'],
            'amount_paid_cents' => $session->amount_total ?? ($package['amount_mxn'] * 100),
            'currency'         => strtoupper($session->currency ?? 'MXN'),
            'status'           => 'pending',
        ]);

        return $session;
    }

    /**
     * Procesa el webhook de Stripe para `checkout.session.completed`.
     * Acredita créditos y actualiza el registro de pago.
     *
     * @throws \UnexpectedValueException Si el payload es inválido
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('stripe.webhook_secret'),
        );

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event->data->object),
            default                      => null, // ignorar otros eventos
        };
    }

    // ----------------------------------------------------------------
    // Private handlers
    // ----------------------------------------------------------------

    private function handleCheckoutCompleted(\Stripe\Checkout\Session $session): void
    {
        $payment = StripePayment::where('stripe_session_id', $session->id)
            ->where('status', 'pending')
            ->first();

        if (! $payment) {
            // Puede llegar dos veces (idempotencia): ignorar si ya procesado
            return;
        }

        $payment->update([
            'stripe_payment_intent' => $session->payment_intent,
            'status'                => 'paid',
            'paid_at'               => now(),
            'stripe_payload'        => $session->toArray(),
        ]);

        // Acreditar créditos en el ledger
        $creditsService = app(CreditsService::class);
        $creditsService->addCredits(
            user:        $payment->user,
            amount:      $payment->credits_amount,
            type:        'purchase',
            description: "Compra de {$payment->credits_amount} créditos — Stripe {$session->id}",
            reference:   $payment,
        );
    }

    private function handleCheckoutExpired(\Stripe\Checkout\Session $session): void
    {
        StripePayment::where('stripe_session_id', $session->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }
}
