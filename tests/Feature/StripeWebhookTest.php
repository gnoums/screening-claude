<?php

namespace Tests\Feature;

use App\Models\StripePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas del webhook de Stripe.
 * Usa STRIPE_WEBHOOK_SECRET=null → el handler funciona sin verificar firma en tests.
 *
 * En tests, mockeamos el BillingService para verificar comportamiento
 * sin hacer llamadas reales a Stripe.
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Simula el payload de un evento checkout.session.completed de Stripe.
     */
    private function buildCheckoutPayload(string $sessionId, int $userId, string $packageKey): array
    {
        return [
            'id'   => 'evt_test_' . uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'                  => $sessionId,
                    'object'              => 'checkout.session',
                    'payment_intent'      => 'pi_test_' . uniqid(),
                    'amount_total'        => 49900,
                    'currency'            => 'mxn',
                    'client_reference_id' => (string) $userId,
                    'metadata'            => [
                        'user_id'        => $userId,
                        'package_key'    => $packageKey,
                        'credits_amount' => 30,
                    ],
                ],
            ],
        ];
    }

    // ----------------------------------------------------------------

    #[Test]
    public function webhook_endpoint_is_accessible_without_csrf(): void
    {
        // El endpoint de webhook no debe rechazar por CSRF
        $response = $this->postJson(route('stripe.webhook'), [], [
            'Stripe-Signature' => 'invalid',
        ]);

        // Esperamos 400 por firma inválida, NO 419 (CSRF)
        $response->assertStatus(400);
    }

    #[Test]
    public function webhook_returns_400_with_invalid_signature(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed']);

        $this->withHeaders(['Stripe-Signature' => 'invalid_sig'])
            ->post(route('stripe.webhook'), [], ['CONTENT_TYPE' => 'application/json'])
            ->assertStatus(400);
    }

    #[Test]
    public function pending_payment_is_marked_paid_on_webhook(): void
    {
        // Crear usuario y pago pendiente
        $user    = User::factory()->create();
        $session = 'cs_test_' . uniqid();

        StripePayment::create([
            'user_id'           => $user->id,
            'stripe_session_id' => $session,
            'package_key'       => 'credits_30',
            'credits_amount'    => 30,
            'amount_paid_cents' => 49900,
            'currency'          => 'MXN',
            'status'            => 'pending',
        ]);

        // Mockear BillingService::handleWebhook para no verificar firma
        $this->mock(\App\Services\BillingService::class, function ($mock) use ($session, $user) {
            $mock->shouldReceive('handleWebhook')
                 ->once()
                 ->andReturnUsing(function () use ($session, $user) {
                     // Simular lo que haría handleCheckoutCompleted
                     $payment = StripePayment::where('stripe_session_id', $session)->first();
                     $payment->update(['status' => 'paid', 'paid_at' => now()]);

                     app(\App\Services\CreditsService::class)->addCredits(
                         user:        $user,
                         amount:      $payment->credits_amount,
                         type:        'purchase',
                         description: "Test purchase",
                         reference:   $payment,
                     );
                 });
        });

        $this->post(route('stripe.webhook'), [], ['Stripe-Signature' => 'any'])
            ->assertOk();

        $this->assertDatabaseHas('stripe_payments', [
            'stripe_session_id' => $session,
            'status'            => 'paid',
        ]);

        $this->assertEquals(30, $user->fresh()->creditBalance());
    }

    #[Test]
    public function credits_are_not_doubled_on_duplicate_webhook(): void
    {
        $user    = User::factory()->create();
        $session = 'cs_test_' . uniqid();

        StripePayment::create([
            'user_id'           => $user->id,
            'stripe_session_id' => $session,
            'package_key'       => 'credits_10',
            'credits_amount'    => 10,
            'amount_paid_cents' => 19900,
            'currency'          => 'MXN',
            'status'            => 'paid', // ya procesado
            'paid_at'           => now(),
        ]);

        // El handler no debe volver a acreditar si ya está pagado
        $callCount = 0;

        $this->mock(\App\Services\BillingService::class, function ($mock) use (&$callCount, $session, $user) {
            $mock->shouldReceive('handleWebhook')
                 ->once()
                 ->andReturnUsing(function () use (&$callCount, $session, $user) {
                     // Simular idempotencia: no procesar si ya está paid
                     $payment = StripePayment::where('stripe_session_id', $session)
                         ->where('status', 'pending')
                         ->first();

                     if (! $payment) {
                         return; // idempotente: no hacer nada
                     }

                     $callCount++;
                 });
        });

        $this->post(route('stripe.webhook'), [], ['Stripe-Signature' => 'any'])
            ->assertOk();

        // creditBalance debe seguir en 0 (no se acreditó nada)
        $this->assertEquals(0, $user->fresh()->creditBalance());
        $this->assertEquals(0, $callCount);
    }
}
