<?php

namespace App\Http\Resources;

use App\Support\LandingPageRegistry;
use Illuminate\Http\Resources\Json\JsonResource;

class AngleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $tone = config("angles.tones.{$this->tone}", []);
        $landingIdentifier = $this->landing_identifier;
        $landingDefinition = is_string($landingIdentifier)
            ? app(LandingPageRegistry::class)->definition($landingIdentifier)
            : null;
        $landingPage = is_string($landingIdentifier) && is_array($landingDefinition) && $this->brand
            ? [
                'identifier' => $landingIdentifier,
                'label' => $landingDefinition['title'] ?? $landingIdentifier,
                'eyebrow' => $landingDefinition['eyebrow'] ?? null,
                'url' => route('landing.page', [
                    'brandId' => $this->brand->id,
                    'landingIdentifier' => $landingIdentifier,
                ]),
            ]
            : null;

        return [
            'id' => $this->id,
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ],
            'name' => $this->name,
            'slug' => $this->slug,
            'landing_identifier' => $landingIdentifier,
            'landing_page' => $landingPage,
            'audience' => $this->audience,
            'trigger_moment' => $this->trigger_moment,
            'primary_job' => $this->primary_job,
            'tension' => $this->tension,
            'desired_outcome' => $this->desired_outcome,
            'single_promise' => $this->single_promise,
            'proof' => $this->proof,
            'objection' => $this->objection,
            'offer' => $this->offer,
            'tone' => $this->tone,
            'tone_label' => $tone['label'] ?? $this->tone,
            'tone_description' => $tone['description'] ?? null,
            'next_step' => $this->next_step,
            'proof_state' => filled($this->proof) ? 'configured' : 'missing',
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
