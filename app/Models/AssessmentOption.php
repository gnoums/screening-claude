<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentOption extends Model
{
    protected $fillable = [
        'assessment_question_id',
        'order',
        'option_text',
        'score_value',
    ];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function responseAnswers(): HasMany
    {
        return $this->hasMany(AssessmentResponseAnswer::class);
    }
}
