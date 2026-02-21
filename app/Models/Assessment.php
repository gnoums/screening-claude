<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'description',
        'version',
        'author',
        'is_active',
        'estimated_minutes',
        'credits_cost',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(AssessmentRule::class)->orderBy('min_score');
    }

    public function screeningRequestItems(): HasMany
    {
        return $this->hasMany(ScreeningRequestItem::class);
    }

    // ----------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
