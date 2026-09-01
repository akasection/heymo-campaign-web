<?php

namespace App\Services;

class SignOffResolver
{
    /**
     * Resolve the deterministic valediction and signature identity for a brand.
     *
     * @return array{valediction: string, signature: string}
     */
    public function resolve(string $tone, string $tense, string $brandName): array
    {
        $matrix = config('delivery.sign_off', []);
        $valediction = $matrix[$tone][$tense] ?? $matrix['balanced']['balanced'] ?? 'Regards';

        $name = trim($brandName);
        $signature = $name !== '' ? "The {$name} team" : 'The team';

        return [
            'valediction' => (string) $valediction,
            'signature' => $signature,
        ];
    }
}
