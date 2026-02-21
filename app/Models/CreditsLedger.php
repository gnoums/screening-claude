<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditsLedger extends Model
{
    protected $table = 'credits_ledger';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'reference_id',
        'reference_type',
        'balance_after',
    ];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Referencia polimórfica (p.ej. ScreeningRequest) */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
