<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMessage extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';

    public const ROLE_PROMISE = 'promise';

    public const ROLE_MECHANISM = 'mechanism';

    public const ROLE_OBJECTION = 'objection';

    public const STATUS_GENERATED = 'generated';

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
    ];

    protected $casts = [
        'body_paragraphs' => 'array',
        'evidence_ids' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
