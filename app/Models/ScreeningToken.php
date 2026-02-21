<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningToken extends Model
{
    protected $fillable = [
        'screening_request_id',
        'token_hash',
        'expires_at',
        'used_at',
        'is_invalidated',
        'last_ip',
        'last_user_agent',
    ];

    protected $hidden = [
        'token_hash', // nunca exponemos el hash
    ];

    protected function casts(): array
    {
        return [
            'expires_at'     => 'datetime',
            'used_at'        => 'datetime',
            'is_invalidated' => 'boolean',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function screeningRequest(): BelongsTo
    {
        return $this->belongsTo(ScreeningRequest::class);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    public function isValid(): bool
    {
        return ! $this->is_invalidated
            && $this->expires_at->isFuture()
            && is_null($this->used_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasBeenUsed(): bool
    {
        return ! is_null($this->used_at);
    }

    /** Busca un token por su valor plano (hash SHA-256) */
    public static function findByPlainToken(string $plainToken): ?self
    {
        $hash = hash('sha256', $plainToken);

        return static::where('token_hash', $hash)->first();
    }
}
