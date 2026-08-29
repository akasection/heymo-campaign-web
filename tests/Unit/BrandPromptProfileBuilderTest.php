<?php

namespace Tests\Unit;

use App\Services\BrandPromptProfileBuilder;
use Tests\TestCase;

class BrandPromptProfileBuilderTest extends TestCase
{
    public function test_it_builds_the_named_brand_voice_profile(): void
    {
        $profile = (new BrandPromptProfileBuilder)->build([
            'name' => 'Lexical Labs',
            'tone_preset' => 'informal',
            'flow_preset' => 'narrative',
            'tense_preset' => 'relaxed',
            'reading_level_preset' => 'simpler',
            'preferred_terms' => ['clear numbers', '  clear   numbers ', 'small steps'],
            'avoided_terms' => ['miracle', ' MIRACLE '],
        ]);

        $this->assertSame(BrandPromptProfileBuilder::VERSION, $profile['version']);
        $this->assertSame('Informal', $profile['voice']['tone']['label']);
        $this->assertSame('Narrative', $profile['voice']['flow']['label']);
        $this->assertSame('Relaxed', $profile['voice']['tense']['label']);
        $this->assertSame('Simpler', $profile['voice']['reading_level']['label']);
        $this->assertSame('tone-informal.md', $profile['voice']['tone']['prompt_file']);
        $this->assertSame(['clear numbers', 'small steps'], $profile['preferred_terms']);
        $this->assertSame(['miracle'], $profile['avoided_terms']);
        $this->assertStringContainsString('Lexical Labs', $profile['prompt_skeleton']);
        $this->assertStringContainsString('# Informal tone', $profile['voice']['tone']['instruction']);
        $this->assertStringContainsString('# Narrative flow', $profile['voice']['flow']['instruction']);
        $this->assertStringContainsString('Do not invent health claims', $profile['prompt_skeleton']);
    }

    public function test_it_uses_balanced_defaults_for_missing_voice_settings(): void
    {
        $profile = (new BrandPromptProfileBuilder)->build([]);

        $this->assertSame('balanced', $profile['voice']['tone']['value']);
        $this->assertSame('balanced', $profile['voice']['flow']['value']);
        $this->assertSame('balanced', $profile['voice']['tense']['value']);
        $this->assertSame('balanced', $profile['voice']['reading_level']['value']);
        $this->assertSame([], $profile['preferred_terms']);
        $this->assertSame([], $profile['avoided_terms']);
    }

    public function test_every_voice_preset_loads_its_markdown_instruction_file(): void
    {
        $builder = new BrandPromptProfileBuilder;

        foreach ([
            'tone_preset' => 'tone',
            'flow_preset' => 'flow',
            'tense_preset' => 'tense',
            'reading_level_preset' => 'reading_level',
        ] as $attribute => $group) {
            $voiceKey = str_replace('_preset', '', $attribute);
            $filePrefix = str_replace('_', '-', $group);

            foreach (array_keys(config("brands.presets.{$group}", [])) as $value) {
                $profile = $builder->build([$attribute => $value]);
                $voice = $profile['voice'][$voiceKey];

                $this->assertSame("{$filePrefix}-{$value}.md", $voice['prompt_file']);
                $this->assertStringStartsWith('# ', $voice['instruction']);
            }
        }
    }
}
