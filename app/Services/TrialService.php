<?php

namespace App\Services;

use App\Models\User;

/**
 * Gestiona el período de prueba gratuito.
 * 7 días · 10 créditos de evaluación.
 */
class TrialService
{
    public const TRIAL_DAYS    = 7;
    public const TRIAL_CREDITS = 10;

    public function __construct(
        private readonly CreditsService $creditsService,
    ) {}

    /**
     * Inicia el trial para un usuario recién registrado.
     * No-op si el trial ya fue iniciado.
     */
    public function startTrial(User $user): void
    {
        if ($user->hasStartedTrial()) {
            return;
        }

        $user->update(['trial_starts_at' => now()]);

        $this->creditsService->addCredits(
            $user,
            self::TRIAL_CREDITS,
            'trial',
            self::TRIAL_DAYS . '-day free trial credits',
        );
    }
}
