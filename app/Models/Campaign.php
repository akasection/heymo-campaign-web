<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    public const STATUS_GENERATING = 'generating';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'visitor_id',
        'angle_id',
        'brand_id',
        'intent_response_id',
        'status',
        'presentation_profile',
        'prompt_version',
    ];

    protected $casts = [
        'presentation_profile' => 'array',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function angle(): BelongsTo
    {
        return $this->belongsTo(Angle::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function intentResponse(): BelongsTo
    {
        return $this->belongsTo(IntentResponse::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function generationAttempts(): HasMany
    {
        return $this->hasMany(GenerationAttempt::class);
    }
}
