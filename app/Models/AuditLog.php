<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    // Tabla append-only: no usamos updated_at
    public const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'auditable_id',
        'auditable_type',
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'    => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ----------------------------------------------------------------
    // Factory method
    // ----------------------------------------------------------------

    public static function record(
        string $event,
        ?Model $auditable = null,
        array  $metadata = [],
        ?int   $userId = null,
        ?string $ip = null,
        ?string $userAgent = null,
    ): self {
        return static::create([
            'auditable_id'   => $auditable?->getKey(),
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'user_id'        => $userId,
            'event'          => $event,
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'metadata'       => $metadata,
            'occurred_at'    => now(),
        ]);
    }
}
