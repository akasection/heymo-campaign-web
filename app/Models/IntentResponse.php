<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'angle_id',
        'landing_identifier',
        'age',
        'sex',
        'sub_interest',
        'trigger',
        'concern',
        'captured_at',
    ];

    protected $casts = [
        'age' => 'integer',
        'captured_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function angle(): BelongsTo
    {
        return $this->belongsTo(Angle::class);
    }
}
