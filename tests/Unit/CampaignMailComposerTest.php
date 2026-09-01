<?php

namespace Tests\Unit;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Visitor;
use App\Services\CampaignMailComposer;
use App\Services\SignOffResolver;
use Tests\TestCase;

class CampaignMailComposerTest extends TestCase
{
    public function test_it_composes_the_model_core_and_deterministic_blocks(): void
    {
        $composer = new CampaignMailComposer(new SignOffResolver);

        $result = $composer->compose(
            new CampaignMessage([
                'sequence_position' => 3,
                'subject' => 'A follow-up about your panel',
                'headline' => 'Your next step, explained',
                'body_paragraphs' => ['First paragraph.', 'Second paragraph.'],
                'evidence_ids' => [],
            ]),
            new Campaign(['presentation_profile' => ['type_size' => 'standard', 'density' => 'standard']]),
            new Brand([
                'name' => 'Lexical Labs',
                'tone_preset' => 'informal',
                'tense_preset' => 'relaxed',
                'primary_color' => '#2E5BFF',
                'secondary_color' => '#00B8A9',
                'heading_font' => 'space_grotesk',
                'body_font' => 'public_sans',
            ]),
            new Angle(['offer' => 'The panel price is shown before checkout.', 'next_step' => 'Review the panel details and decide.']),
            new Visitor(['email' => 'remy@example.test']),
            'https://example.test/unsubscribe/1?sig=abc',
        );

        $this->assertSame('A follow-up about your panel', $result['subject']);
        $this->assertStringContainsString('Your next step, explained', $result['html']);
        $this->assertStringContainsString('First paragraph.', $result['html']);
        $this->assertStringContainsString('The panel price is shown before checkout.', $result['html']);
        $this->assertStringContainsString('Next step:', $result['html']);
        $this->assertStringContainsString('Review the panel details and decide.', $result['html']);
        $this->assertStringContainsString('Take care', $result['html']);
        $this->assertStringContainsString('The Lexical Labs team', $result['html']);
        $this->assertStringContainsString((string) config('delivery.compliance_text'), $result['html']);
        $this->assertStringContainsString('https://example.test/unsubscribe/1?sig=abc', $result['html']);
    }

    public function test_offer_and_next_step_only_appear_on_the_final_beat(): void
    {
        $composer = new CampaignMailComposer(new SignOffResolver);

        $result = $composer->compose(
            new CampaignMessage([
                'sequence_position' => 1,
                'subject' => 'Welcome',
                'headline' => 'Thanks for your interest',
                'body_paragraphs' => ['First paragraph.'],
                'evidence_ids' => [],
            ]),
            new Campaign(['presentation_profile' => []]),
            new Brand(['name' => 'Lexical Labs', 'tone_preset' => 'informal', 'tense_preset' => 'relaxed']),
            new Angle(['offer' => 'Offer text.', 'next_step' => 'CTA text.']),
            new Visitor(['email' => 'remy@example.test']),
            'https://example.test/unsubscribe/1?sig=abc',
        );

        $this->assertStringNotContainsString('Offer text.', $result['html']);
        $this->assertStringNotContainsString('Next step:', $result['html']);
        $this->assertStringContainsString('https://example.test/unsubscribe/1?sig=abc', $result['html']);
    }
}
