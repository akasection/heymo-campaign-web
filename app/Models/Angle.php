<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Angle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'landing_identifier',
        'name',
        'audience',
        'trigger_moment',
        'primary_job',
        'tension',
        'desired_outcome',
        'single_promise',
        'proof',
        'objection',
        'offer',
        'tone',
        'next_step',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function scopeForBrand(Builder $query, int $brandId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('brand_id'), $brandId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull($query->getModel()->getQualifiedDeletedAtColumn());
    }
}
