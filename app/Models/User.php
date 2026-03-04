<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'trial_starts_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'trial_starts_at'   => 'datetime',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function screeningRequests(): HasMany
    {
        return $this->hasMany(ScreeningRequest::class);
    }

    public function creditsLedger(): HasMany
    {
        return $this->hasMany(CreditsLedger::class);
    }

    public function reportExports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    public function stripePayments(): HasMany
    {
        return $this->hasMany(StripePayment::class);
    }

    // ----------------------------------------------------------------
    // Role helpers
    // ----------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPsychologist(): bool
    {
        return $this->role === 'psychologist';
    }

    // ----------------------------------------------------------------
    // Helper: saldo actual de créditos
    // ----------------------------------------------------------------

    public function creditBalance(): int
    {
        return (int) $this->creditsLedger()->sum('amount');
    }

    public function hasCredits(int $amount = 1): bool
    {
        return $this->creditBalance() >= $amount;
    }

    /**
     * Saldo neto de créditos pagados (compras - consumo).
     * Excluye créditos de trial. Útil al verificar acceso tras expiración.
     */
    public function paidCreditBalance(): int
    {
        $purchased = (int) $this->creditsLedger()
            ->whereIn('type', ['purchase', 'adjustment', 'refund'])
            ->sum('amount');

        $used = abs((int) $this->creditsLedger()
            ->where('type', 'usage')
            ->sum('amount'));

        return max(0, $purchased - $used);
    }

    // ----------------------------------------------------------------
    // Helpers: trial gratuito
    // ----------------------------------------------------------------

    public function hasStartedTrial(): bool
    {
        return $this->trial_starts_at !== null;
    }

    public function trialEndsAt(): ?\Illuminate\Support\Carbon
    {
        return $this->trial_starts_at?->addDays(\App\Services\TrialService::TRIAL_DAYS);
    }

    public function isOnActiveTrial(): bool
    {
        return $this->hasStartedTrial() && now()->lt($this->trialEndsAt());
    }

    public function trialHasExpired(): bool
    {
        return $this->hasStartedTrial() && now()->gte($this->trialEndsAt());
    }

    public function trialDaysLeft(): int
    {
        if (! $this->isOnActiveTrial()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trialEndsAt());
    }
}
