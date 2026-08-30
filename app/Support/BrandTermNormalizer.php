<?php

namespace App\Support;

class BrandTermNormalizer
{
    /**
     * @param  array<int, mixed>  $terms
     * @return array<int, string>
     */
    public static function normalize(array $terms): array
    {
        $seen = [];
        $normalized = [];

        foreach ($terms as $term) {
            if (! is_string($term)) {
                continue;
            }

            $term = preg_replace('/\s+/u', ' ', trim($term)) ?? trim($term);

            if ($term === '') {
                continue;
            }

            $key = mb_strtolower($term);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $term;
        }

        return $normalized;
    }
}
