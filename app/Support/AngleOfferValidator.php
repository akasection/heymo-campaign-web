<?php

namespace App\Support;

class AngleOfferValidator
{
    /**
     * @return array<string, array<int, string>>
     */
    public function validate(string $offer, string $nextStep): array
    {
        $errors = [];
        $patterns = [
            '/\\b(?:limited time|only today|act now|last chance|ends tonight|while supplies last)\\b/i' => 'contains false urgency language',
            '/\\b(?:limited|exclusive)\\s+(?:spots?|availability|access|supply)\\b/i' => 'contains scarcity language',
            '/\\b(?:hidden|secret)\\s+(?:terms?|conditions?|fees?)\\b/i' => 'contains hidden-condition language',
            '/\\b(?:better than|compare(?:d)? to|number one|#1)\\b/i' => 'contains misleading comparison language',
            '/\\b(?:cure|diagnos(?:e|is)|guarante(?:e|ed)|prevent)\\b/i' => 'contains an unsupported clinical promise',
        ];

        foreach (['offer' => $offer, 'next_step' => $nextStep] as $field => $value) {
            foreach ($patterns as $pattern => $message) {
                if (preg_match($pattern, $value) === 1) {
                    $errors[$field][] = ucfirst($message).'.';
                }
            }
        }

        return $errors;
    }
}
