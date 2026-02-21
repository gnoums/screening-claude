<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResponseAnswer extends Model
{
    protected $fillable = [
        'assessment_response_id',
        'assessment_question_id',
        'assessment_option_id',
        'score_value_snapshot',
        'option_text_snapshot',
    ];

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function response(): BelongsTo
    {
        return $this->belongsTo(AssessmentResponse::class, 'assessment_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AssessmentOption::class, 'assessment_option_id');
    }
}
