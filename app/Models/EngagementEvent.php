<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementEvent extends Model
{
    use HasFactory;

    public const TYPE_OPEN = 'open';

    public const TYPE_CLICK = 'click';

    protected $fillable = [
        'campaign_message_id',
        'type',
        'occurred_at',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function campaignMessage(): BelongsTo
    {
        return $this->belongsTo(CampaignMessage::class);
    }
}
