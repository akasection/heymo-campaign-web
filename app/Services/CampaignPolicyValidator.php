<?php

namespace App\Services;

class CampaignPolicyValidator
{
    /**
     * Deterministically validate parsed campaign messages. Returns a list of
     * human-readable violations; an empty list means the output is safe.
     *
     * @param  array<string, mixed>  $parsed
     * @param  array<string, mixed>  $context
     * @return array<int, string>
     */
    public function validate(array $parsed, array $context): array
    {
        $messages = $parsed['messages'] ?? [];
        $allowedEvidence = $context['allowed_evidence_ids'] ?? [];
        $avoidedTerms = $context['avoided_terms'] ?? [];
        $demographicTerms = $context['demographic_terms'] ?? [];
        $violations = [];

        $prohibited = [];

        foreach (config('campaign-guardrails.prohibited', []) as $group) {
            foreach ($group as $rule) {
                $prohibited[] = $rule;
            }
        }

        foreach ($messages as $message) {
            $position = $message['position'];

            foreach ($message['evidence_ids'] as $id) {
                if (! in_array($id, $allowedEvidence, true)) {
                    $violations[] = "Message {$position} references unknown evidence id '{$id}'.";
                }
            }

            $copy = array_merge(
                [$message['subject'], $message['headline']],
                $message['body_paragraphs'],
            );

            foreach ($avoidedTerms as $term) {
                $term = trim((string) $term);

                if ($term === '') {
                    continue;
                }

                foreach ($copy as $text) {
                    if (stripos($text, $term) !== false) {
                        $violations[] = "Message {$position} uses the avoided term '{$term}'.";
                    }
                }
            }

            foreach ($prohibited as $rule) {
                foreach ($copy as $text) {
                    if (preg_match($rule['pattern'], $text) === 1) {
                        $violations[] = "Message {$position} contains {$rule['message']}.";
                    }
                }
            }

            foreach ($demographicTerms as $term) {
                $pattern = '/\b'.preg_quote((string) $term, '/').'\b/i';

                foreach ($copy as $text) {
                    if (preg_match($pattern, $text) === 1) {
                        $violations[] = "Message {$position} mentions demographics ('{$term}').";
                    }
                }
            }

            if (trim((string) $message['subject']) === '') {
                $violations[] = "Message {$position} has an empty subject.";
            }
        }

        return $violations;
    }
}
