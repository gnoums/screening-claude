<?php

namespace App\Services;

use App\Models\CreditsLedger;
use App\Models\ScreeningRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gestiona el libro mayor de créditos.
 * Cada operación crea un movimiento atómico con el saldo resultante.
 */
class CreditsService
{
    /**
     * Añade créditos (recarga / ajuste / reembolso).
     */
    public function addCredits(
        User   $user,
        int    $amount,
        string $type = 'purchase',
        string $description = '',
        mixed  $reference = null,
    ): CreditsLedger {
        if ($amount === 0) {
            throw new \InvalidArgumentException('El monto no puede ser cero.');
        }

        return $this->record($user, $amount, $type, $description, $reference);
    }

    /**
     * Descuenta créditos por el envío de una solicitud.
     *
     * @throws \DomainException Si no hay saldo suficiente
     */
    public function chargeForRequest(ScreeningRequest $request): CreditsLedger
    {
        $user   = $request->user;
        $amount = $request->credits_charged;

        if ($user->creditBalance() < $amount) {
            throw new \DomainException(__('Insufficient credit balance.'));
        }

        return $this->record(
            $user,
            -$amount,
            'usage',
            "Solicitud #{$request->id} enviada",
            $request,
        );
    }

    /**
     * Reembolso de créditos (p.ej. al cancelar solicitud).
     */
    public function refund(ScreeningRequest $request, string $reason = ''): CreditsLedger
    {
        return $this->record(
            $request->user,
            $request->credits_charged,
            'refund',
            "Reembolso solicitud #{$request->id}. {$reason}",
            $request,
        );
    }

    // ----------------------------------------------------------------
    // Private
    // ----------------------------------------------------------------

    private function record(
        User   $user,
        int    $amount,
        string $type,
        string $description,
        mixed  $reference,
    ): CreditsLedger {
        return DB::transaction(function () use ($user, $amount, $type, $description, $reference) {
            // Bloqueo pesimista para evitar condición de carrera
            $currentBalance = $user->creditsLedger()->lockForUpdate()->sum('amount');
            $newBalance     = (int) $currentBalance + $amount;

            return CreditsLedger::create([
                'user_id'        => $user->id,
                'amount'         => $amount,
                'type'           => $type,
                'description'    => $description,
                'reference_id'   => $reference?->getKey(),
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'balance_after'  => max(0, $newBalance),
            ]);
        });
    }
}
