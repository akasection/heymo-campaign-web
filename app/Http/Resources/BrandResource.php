<?php

namespace App\Http\Resources;

use App\Services\BrandPromptProfileBuilder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $fonts = config('brands.fonts', []);
        $headingFont = $fonts[$this->heading_font] ?? [];
        $bodyFont = $fonts[$this->body_font] ?? [];
        $promptProfile = app(BrandPromptProfileBuilder::class)->build([
            'name' => $this->name,
            'tone_preset' => $this->tone_preset,
            'flow_preset' => $this->flow_preset,
            'tense_preset' => $this->tense_preset,
            'reading_level_preset' => $this->reading_level_preset,
            'preferred_terms' => $this->preferred_terms ?? [],
            'avoided_terms' => $this->avoided_terms ?? [],
        ]);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tone_preset' => $this->tone_preset,
            'flow_preset' => $this->flow_preset,
            'tense_preset' => $this->tense_preset,
            'reading_level_preset' => $this->reading_level_preset,
            'preferred_terms' => $this->preferred_terms ?? [],
            'avoided_terms' => $this->avoided_terms ?? [],
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'heading_font' => $this->heading_font,
            'heading_font_label' => $headingFont['label'] ?? $this->heading_font,
            'heading_font_stack' => $headingFont['stack'] ?? 'sans-serif',
            'body_font' => $this->body_font,
            'body_font_label' => $bodyFont['label'] ?? $this->body_font,
            'body_font_stack' => $bodyFont['stack'] ?? 'sans-serif',
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'prompt_profile' => $promptProfile,
        ];
    }
}
