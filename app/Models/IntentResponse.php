<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class IntentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'angle_id',
        'landing_identifier',
        'age_group',
        'sex',
        'sub_interest',
        'trigger',
        'concern',
        'captured_at',
        'fingerprint',
        'session_duration_seconds',
        'attribution',
        'device',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'attribution' => 'array',
        'device' => 'array',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function angle(): BelongsTo
    {
        return $this->belongsTo(Angle::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(CampaignMessage::class, Campaign::class, 'intent_response_id', 'campaign_id');
    }
}
