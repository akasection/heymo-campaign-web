<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'fingerprint',
        'brand_id',
        'landing_identifier',
        'angle_id',
        'attribution',
        'device',
        'landed_at',
    ];

    protected $casts = [
        'attribution' => 'array',
        'device' => 'array',
        'landed_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function angle(): BelongsTo
    {
        return $this->belongsTo(Angle::class);
    }
}
