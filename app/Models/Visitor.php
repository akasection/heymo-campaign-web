<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'preferred_name',
        'email',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function intentResponses(): HasMany
    {
        return $this->hasMany(IntentResponse::class);
    }

    public function consentRecords(): HasMany
    {
        return $this->hasMany(ConsentRecord::class);
    }

    public function suppressions(): HasMany
    {
        return $this->hasMany(Suppression::class);
    }

    /**
     * A durable opt-out (or conversion) block for a channel. Once suppressed, a
     * visitor is excluded from queued and future delivery; this cannot be
     * bypassed by a queued job.
     */
    public function isSuppressed(string $channel = Suppression::CHANNEL_EMAIL): bool
    {
        return $this->suppressions()->where('channel', $channel)->exists();
    }

    /**
     * Affirmative email consent gate for generation and send. Rejects visitors
     * who never consented or who are suppressed for the email channel.
     */
    public function hasActiveEmailConsent(): bool
    {
        if ($this->isSuppressed(Suppression::CHANNEL_EMAIL)) {
            return false;
        }

        return $this->consentRecords()->where('channel', Suppression::CHANNEL_EMAIL)->exists();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
