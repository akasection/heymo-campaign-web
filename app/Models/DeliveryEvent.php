<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryEvent extends Model
{
    use HasFactory;

    public const STATUS_ATTEMPTED = 'attempted';

    public const STATUS_SENT = 'sent';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_message_id',
        'attempt_number',
        'status',
        'to_address',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function campaignMessage(): BelongsTo
    {
        return $this->belongsTo(CampaignMessage::class);
    }
}
