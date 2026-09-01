<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignMessage extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';

    public const ROLE_PROMISE = 'promise';

    public const ROLE_MECHANISM = 'mechanism';

    public const ROLE_OBJECTION = 'objection';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_id',
        'sequence_position',
        'channel',
        'role',
        'subject',
        'headline',
        'body_paragraphs',
        'evidence_ids',
        'status',
        'scheduled_at',
        'sent_at',
        'open_token',
        'opened_at',
    ];

    protected $casts = [
        'body_paragraphs' => 'array',
        'evidence_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function deliveryEvents(): HasMany
    {
        return $this->hasMany(DeliveryEvent::class);
    }

    public function engagementEvents(): HasMany
    {
        return $this->hasMany(EngagementEvent::class);
    }
}
