<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'captured_at' => $this->captured_at?->toIso8601String(),
            'landing_identifier' => $this->landing_identifier,
            'fingerprint' => $this->fingerprint,
            'session_duration_seconds' => $this->session_duration_seconds,
            'attribution' => $this->attribution,
            'device' => $this->device,
            'age_group' => $this->age_group,
            'sex' => $this->sex,
            'sub_interest' => $this->sub_interest,
            'trigger' => $this->trigger,
            'concern' => $this->concern,
            'visitor' => [
                'id' => $this->visitor?->id,
                'preferred_name' => $this->visitor?->preferred_name,
                'email' => $this->visitor?->email,
            ],
            'angle' => [
                'id' => $this->angle?->id,
                'name' => $this->angle?->name,
                'slug' => $this->angle?->slug,
            ],
            'brand' => [
                'id' => $this->angle?->brand?->id,
                'name' => $this->angle?->brand?->name,
            ],
            'campaigns_count' => $this->campaigns_count ?? 0,
            'campaigns_generated_count' => $this->campaigns_generated_count ?? 0,
            'messages_sent_count' => $this->messages_sent_count ?? 0,
            'messages_opened_count' => $this->messages_opened_count ?? 0,
        ];
    }
}
