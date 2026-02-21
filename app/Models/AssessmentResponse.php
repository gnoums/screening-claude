<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentResponse extends Model
{
    protected $fillable = [
        'screening_request_item_id',
        'patient_id',
        'total_score',
        'severity_label',
        'interpretation_text',
        'assessment_name_snapshot',
        'assessment_version_snapshot',
        'respondent_ip',
        'respondent_user_agent',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function screeningRequestItem(): BelongsTo
    {
        return $this->belongsTo(ScreeningRequestItem::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentResponseAnswer::class);
    }
}
