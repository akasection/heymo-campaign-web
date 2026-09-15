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
                'sequence_position' => 2,
                'subject' => 'How the process works, step by step',
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
            new Angle([
                'offer' => 'The panel price is shown before checkout.',
                'next_step' => 'Review the panel details and decide.',
                'next_step_url' => 'https://example.test/order',
            ]),
            new Visitor(['email' => 'remy@example.test']),
            'https://example.test/unsubscribe/1?sig=abc',
        );

        $this->assertSame('How the process works, step by step', $result['subject']);
        $this->assertStringContainsString('Your next step, explained', $result['html']);
        $this->assertStringContainsString('First paragraph.', $result['html']);
        $this->assertStringContainsString('Review the panel details and decide.', $result['html']);
        $this->assertStringContainsString('https://example.test/order', $result['html']);
        $this->assertStringNotContainsString('The panel price is shown before checkout.', $result['html']);
        $this->assertStringContainsString('Take care', $result['html']);
        $this->assertStringContainsString('The Lexical Labs team', $result['html']);
        $this->assertStringContainsString((string) config('delivery.compliance_text'), $result['html']);
        $this->assertStringContainsString('https://example.test/unsubscribe/1?sig=abc', $result['html']);
    }

    public function test_offer_and_cta_land_on_their_designated_beats(): void
    {
        $composer = new CampaignMailComposer(new SignOffResolver);

        $promise = $composer->compose(
            new CampaignMessage([
                'sequence_position' => 1,
                'subject' => 'Welcome',
                'headline' => 'Thanks for your interest',
                'body_paragraphs' => ['First paragraph.'],
                'evidence_ids' => [],
            ]),
            new Campaign(['presentation_profile' => []]),
            new Brand(['name' => 'Lexical Labs', 'tone_preset' => 'informal', 'tense_preset' => 'relaxed']),
            new Angle([
                'offer' => 'Offer text.',
                'next_step' => 'CTA text.',
                'next_step_url' => 'https://example.test/order',
            ]),
            new Visitor(['email' => 'remy@example.test']),
            'https://example.test/unsubscribe/1?sig=abc',
        );

        $this->assertStringNotContainsString('Offer text.', $promise['html']);
        $this->assertStringNotContainsString('CTA text.', $promise['html']);
        $this->assertStringNotContainsString('https://example.test/order', $promise['html']);
        $this->assertStringContainsString('https://example.test/unsubscribe/1?sig=abc', $promise['html']);

        $final = $composer->compose(
            new CampaignMessage([
                'sequence_position' => 3,
                'subject' => 'One more thing',
                'headline' => 'Keeping this simple',
                'body_paragraphs' => ['Final paragraph.'],
                'evidence_ids' => [],
            ]),
            new Campaign(['presentation_profile' => []]),
            new Brand(['name' => 'Lexical Labs', 'tone_preset' => 'informal', 'tense_preset' => 'relaxed']),
            new Angle([
                'offer' => 'Offer text.',
                'next_step' => 'CTA text.',
                'next_step_url' => 'https://example.test/order',
            ]),
            new Visitor(['email' => 'remy@example.test']),
            'https://example.test/unsubscribe/1?sig=abc',
        );

        $this->assertStringContainsString('Offer text.', $final['html']);
        $this->assertStringNotContainsString('CTA text.', $final['html']);
        $this->assertStringNotContainsString('https://example.test/order', $final['html']);
    }
}
