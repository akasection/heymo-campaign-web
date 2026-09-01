<?php

namespace Tests\Unit;

use App\Services\CampaignPolicyValidator;
use Tests\TestCase;

class CampaignPolicyValidatorTest extends TestCase
{
    public function test_valid_messages_pass(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertSame([], $violations);
    }

    public function test_unknown_evidence_id_is_rejected(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['evidence_ids' => ['ghost']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertNotEmpty($violations);
        $this->assertStringContainsString("unknown evidence id 'ghost'", $violations[0]);
    }

    public function test_avoided_brand_term_is_rejected(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['We promise a miracle.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => ['miracle'], 'demographic_terms' => []],
        );

        $this->assertNotEmpty($violations);
        $this->assertStringContainsString("avoided term 'miracle'", $violations[0]);
    }

    public function test_clinical_claim_is_rejected(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['This panel will cure your tiredness.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertNotEmpty($violations);
    }

    public function test_it_does_not_flag_the_word_health(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['A thoughtful start to your health conversation.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertSame([], $violations);
    }

    public function test_it_flags_healing_and_treatment_language(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['We can treat your low energy and start healing.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertNotEmpty($violations);
    }

    public function test_false_urgency_is_rejected(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['Limited time offer ends tonight.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => []],
        );

        $this->assertNotEmpty($violations);
    }

    public function test_demographic_mention_is_rejected(): void
    {
        $violations = (new CampaignPolicyValidator)->validate(
            $this->parsed(['body_paragraphs' => ['Designed for a female aged 30-44.']]),
            ['allowed_evidence_ids' => [], 'avoided_terms' => [], 'demographic_terms' => ['30-44', 'female']],
        );

        $this->assertNotEmpty($violations);
        $this->assertStringContainsString('mentions demographics', $violations[0]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function parsed(array $overrides = []): array
    {
        return ['messages' => [
            $this->beat(1, 'promise', $overrides),
            $this->beat(2, 'mechanism'),
            $this->beat(3, 'objection'),
        ]];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function beat(int $position, string $role, array $overrides = []): array
    {
        return array_merge([
            'position' => $position,
            'role' => $role,
            'subject' => "Subject {$position}",
            'headline' => "Headline {$position}",
            'body_paragraphs' => ["Paragraph {$position}"],
            'evidence_ids' => [],
        ], $overrides);
    }
}
