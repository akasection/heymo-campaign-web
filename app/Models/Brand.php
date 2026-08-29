<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tone_preset',
        'flow_preset',
        'tense_preset',
        'reading_level_preset',
        'preferred_terms',
        'avoided_terms',
        'primary_color',
        'secondary_color',
        'heading_font',
        'body_font',
        'logo_path',
    ];

    protected $casts = [
        'preferred_terms' => 'array',
        'avoided_terms' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('organization_id'), $organizationId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull($query->getModel()->getQualifiedDeletedAtColumn());
    }
}
