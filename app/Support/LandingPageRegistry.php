<?php

namespace App\Support;

class LandingPageRegistry
{
    /**
     * @return array<string, mixed>|null
     */
    public function definition(string $identifier): ?array
    {
        $definition = config("landing-pages.{$identifier}");

        return is_array($definition) ? $definition : null;
    }

    /**
     * @return array<int, string>
     */
    public function identifiers(): array
    {
        return array_keys(config('landing-pages', []));
    }

    /**
     * @return array<int, string>
     */
    public function choiceValues(string $identifier, string $field): array
    {
        $options = $this->definition($identifier)['quiz'][$field]['options'] ?? [];

        return array_values(array_filter(array_map(
            static fn (mixed $option): ?string => is_array($option) && is_string($option['value'] ?? null) ? $option['value'] : null,
            $options,
        )));
    }
}
