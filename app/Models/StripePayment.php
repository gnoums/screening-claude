<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePayment extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_session_id',
        'stripe_payment_intent',
        'package_key',
        'credits_amount',
        'amount_paid_cents',
        'currency',
        'status',
        'paid_at',
        'stripe_payload',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'        => 'datetime',
            'stripe_payload' => 'array',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Monto en pesos (de centavos a unidad) */
    public function amountFormatted(): string
    {
        return '$' . number_format($this->amount_paid_cents / 100, 2) . ' ' . $this->currency;
    }
}
