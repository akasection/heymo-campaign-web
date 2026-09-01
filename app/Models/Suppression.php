<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suppression extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';

    public const REASON_UNSUBSCRIBE = 'unsubscribe';

    protected $fillable = [
        'visitor_id',
        'channel',
        'reason',
        'source',
        'suppressed_at',
    ];

    protected $casts = [
        'suppressed_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
