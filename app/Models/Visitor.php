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

    /**
     * Affirmative email consent gate for generation. Withdrawal and conversion
     * suppression land in T-08/T-10; once a Suppression record exists this check
     * must also reject suppressed visitors.
     */
    public function hasActiveEmailConsent(): bool
    {
        return $this->consentRecords()->where('channel', 'email')->exists();
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
