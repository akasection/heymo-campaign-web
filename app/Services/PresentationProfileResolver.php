<?php

namespace App\Services;

use RuntimeException;

class PresentationProfileResolver
{
    /**
     * Resolve the deterministic presentation profile for an age group and sex.
     * The profile is writing-style guidance only; it never becomes clinical
     * context and the exact age is never reconstructed.
     *
     * @return array<string, mixed>
     */
    public function resolve(string $ageGroup, string $sex): array
    {
        $ageProfile = config("presentation.age_groups.{$ageGroup}");

        if (! is_array($ageProfile)) {
            throw new RuntimeException("Unknown age group: {$ageGroup}");
        }

        $emphasis = config("presentation.sex_emphasis.{$sex}");

        if (! is_string($emphasis)) {
            $emphasis = (string) config('presentation.sex_emphasis.prefer_not_to_say', 'neutral and inclusive');
        }

        return array_merge(['age_group' => $ageGroup], $ageProfile, ['sex_emphasis' => $emphasis]);
    }
}
