<?php

namespace App\Services;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Visitor;

class CampaignMailComposer
{
    public function __construct(private SignOffResolver $signOff) {}

    /**
     * Compose the final sendable email from the validated model core plus the
     * deterministic offer, compliance, sign-off, and unsubscribe blocks.
     *
     * @return array{subject: string, html: string}
     */
    public function compose(
        CampaignMessage $message,
        Campaign $campaign,
        Brand $brand,
        Angle $angle,
        Visitor $visitor,
        string $unsubscribeUrl,
    ): array {
        $profile = $campaign->presentation_profile ?? [];
        $isFinalBeat = $message->sequence_position === 3;

        $signOff = $this->signOff->resolve($brand->tone_preset, $brand->tense_preset, $brand->name);

        $html = view('mail.campaign', [
            'brandName' => $brand->name,
            'primaryColor' => $brand->primary_color ?: '#2E5BFF',
            'secondaryColor' => $brand->secondary_color ?: '#00B8A9',
            'headingFont' => $this->fontFamily($brand->heading_font),
            'bodyFont' => $this->fontFamily($brand->body_font),
            'bodyFontSize' => $this->typeSize($profile['type_size'] ?? 'standard'),
            'bodyPadding' => $this->densityPadding($profile['density'] ?? 'standard'),
            'headline' => $message->headline,
            'paragraphs' => $message->body_paragraphs ?? [],
            'evidenceTexts' => $this->resolveEvidence($message->evidence_ids ?? []),
            'offer' => $isFinalBeat ? $angle->offer : null,
            'nextStep' => $isFinalBeat ? $angle->next_step : null,
            'compliance' => (string) config('delivery.compliance_text'),
            'valediction' => $signOff['valediction'],
            'signature' => $signOff['signature'],
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render();

        return [
            'subject' => $message->subject,
            'html' => $html,
        ];
    }

    /**
     * Resolve evidence identifiers to approved text. The relational evidence
     * registry is deferred, so no identifier currently resolves; unknown IDs
     * are never rendered as model-authored facts (fail closed).
     *
     * @param  array<int, string>  $evidenceIds
     * @return array<int, string>
     */
    private function resolveEvidence(array $evidenceIds): array
    {
        return [];
    }

    private function fontFamily(?string $key): string
    {
        return match ($key) {
            'space_grotesk' => 'Space Grotesk, Arial, sans-serif',
            'source_serif' => 'Source Serif, Georgia, serif',
            'ibm_plex_sans' => 'IBM Plex Sans, Arial, sans-serif',
            default => 'Public Sans, Arial, sans-serif',
        };
    }

    private function typeSize(string $typeSize): string
    {
        return match ($typeSize) {
            'large' => '18px',
            'larger' => '20px',
            default => '16px',
        };
    }

    private function densityPadding(string $density): string
    {
        return match ($density) {
            'compact' => '24px',
            'roomy' => '40px',
            'very roomy' => '48px',
            default => '32px',
        };
    }
}
