<?php

namespace Tests\Unit;

use App\Services\CampaignResponseParser;
use Tests\TestCase;

class CampaignResponseParserTest extends TestCase
{
    public function test_it_parses_valid_json_with_three_messages(): void
    {
        $raw = json_encode(['messages' => [
            $this->beat(1, 'promise'),
            $this->beat(2, 'mechanism'),
            $this->beat(3, 'objection'),
        ]]);

        $parsed = (new CampaignResponseParser)->parse($raw);

        $this->assertCount(3, $parsed['messages']);
        $this->assertSame('promise', $parsed['messages'][0]['role']);
        $this->assertSame('mechanism', $parsed['messages'][1]['role']);
        $this->assertSame('objection', $parsed['messages'][2]['role']);
    }

    public function test_it_strips_markdown_code_fences(): void
    {
        $json = json_encode(['messages' => [
            $this->beat(1, 'promise'),
            $this->beat(2, 'mechanism'),
            $this->beat(3, 'objection'),
        ]]);

        $parsed = (new CampaignResponseParser)->parse("```json\n{$json}\n```");

        $this->assertCount(3, $parsed['messages']);
    }

    public function test_it_rejects_a_wrong_message_count(): void
    {
        $this->expectException(\RuntimeException::class);

        (new CampaignResponseParser)->parse(json_encode(['messages' => []]));
    }

    public function test_it_rejects_a_message_without_body_paragraphs(): void
    {
        $this->expectException(\RuntimeException::class);

        (new CampaignResponseParser)->parse(json_encode(['messages' => [
            $this->beat(1, 'promise', ['body_paragraphs' => []]),
            $this->beat(2, 'mechanism'),
            $this->beat(3, 'objection'),
        ]]));
    }

    public function test_it_rejects_non_json_output(): void
    {
        $this->expectException(\RuntimeException::class);

        (new CampaignResponseParser)->parse('I cannot produce JSON right now.');
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
