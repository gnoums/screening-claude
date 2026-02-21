<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentRule extends Model
{
    protected $fillable = [
        'assessment_id',
        'min_score',
        'max_score',
        'severity_label',
        'interpretation_text',
        'color_code',
    ];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    /** Obtiene la regla que aplica para un puntaje dado */
    public function scopeForScore($query, int $score)
    {
        return $query->where('min_score', '<=', $score)
                     ->where('max_score', '>=', $score);
    }
}
