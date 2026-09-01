<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenerationAttempt extends Model
{
    use HasFactory;

    public const STATUS_GENERATED = 'generated';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_id',
        'attempt_number',
        'provider',
        'model',
        'prompt_version',
        'prompt_payload',
        'raw_response',
        'violations',
        'status',
        'error_message',
    ];

    protected $casts = [
        'prompt_payload' => 'array',
        'raw_response' => 'array',
        'violations' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
