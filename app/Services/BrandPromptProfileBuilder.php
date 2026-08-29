<?php

namespace App\Services;

use App\Support\BrandTermNormalizer;
use RuntimeException;

class BrandPromptProfileBuilder
{
    public const VERSION = 'brand-profile-v1';

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function build(array $attributes): array
    {
        $presets = config('brands.presets');
        $version = config('brands.prompt_version', self::VERSION);
        $voice = [];

        foreach ([
            'tone_preset' => 'tone',
            'flow_preset' => 'flow',
            'tense_preset' => 'tense',
            'reading_level_preset' => 'reading_level',
        ] as $attribute => $group) {
            $value = $attributes[$attribute] ?? config("brands.defaults.{$attribute}", 'balanced');
            $definition = $presets[$group][$value] ?? $presets[$group]['balanced'];

            $voice[str_replace('_preset', '', $attribute)] = [
                'value' => $value,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'prompt_file' => $definition['prompt_file'],
                'instruction' => $this->loadInstruction($definition['prompt_file']),
            ];
        }

        $preferredTerms = BrandTermNormalizer::normalize($attributes['preferred_terms'] ?? []);
        $avoidedTerms = BrandTermNormalizer::normalize($attributes['avoided_terms'] ?? []);
        $brandName = trim((string) ($attributes['name'] ?? 'this brand')) ?: 'this brand';
        $preferredLanguage = $preferredTerms === [] ? 'None specified.' : implode(', ', $preferredTerms);
        $avoidedLanguage = $avoidedTerms === [] ? 'None specified.' : implode(', ', $avoidedTerms);

        return [
            'version' => $version,
            'brand_name' => $brandName,
            'voice' => $voice,
            'preferred_terms' => $preferredTerms,
            'avoided_terms' => $avoidedTerms,
            'prompt_skeleton' => implode("\n", [
                "Write for the {$brandName} brand profile ({$version}).",
                "## Tone\n{$voice['tone']['instruction']}",
                "## Flow\n{$voice['flow']['instruction']}",
                "## Tense\n{$voice['tense']['instruction']}",
                "## Reading level\n{$voice['reading_level']['instruction']}",
                "Use preferred language naturally where it fits: {$preferredLanguage}",
                "Avoid these terms and phrases: {$avoidedLanguage}",
                'This profile controls presentation and expression only. Do not invent health claims, infer clinical information, or alter approved facts, offers, compliance language, or calls to action supplied by the system.',
            ]),
        ];
    }

    private function loadInstruction(string $filename): string
    {
        $path = resource_path("llm/config/brands/{$filename}");

        if (! is_readable($path)) {
            throw new RuntimeException("Brand prompt instruction file is missing or unreadable: {$filename}");
        }

        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            throw new RuntimeException("Brand prompt instruction file is empty: {$filename}");
        }

        return trim($contents);
    }
}
