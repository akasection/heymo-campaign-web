<?php

namespace App\Services;

use RuntimeException;

class CampaignResponseParser
{
    private const EXPECTED_ROLES = [
        1 => 'promise',
        2 => 'mechanism',
        3 => 'objection',
    ];

    /**
     * Parse and structurally validate a raw model completion into the fixed
     * three-beat message contract.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function parse(string $raw): array
    {
        $decoded = $this->decode($raw);

        if (! is_array($decoded) || ! is_array($decoded['messages'] ?? null)) {
            throw new RuntimeException('Model response is missing the messages array.');
        }

        $rawMessages = $decoded['messages'];

        if (count($rawMessages) !== 3) {
            throw new RuntimeException('Model response must contain exactly three messages.');
        }

        $normalized = [];

        foreach (self::EXPECTED_ROLES as $position => $role) {
            $message = $rawMessages[$position - 1] ?? null;

            if (! is_array($message)) {
                throw new RuntimeException("Message at position {$position} is missing.");
            }

            if (($message['role'] ?? null) !== $role || (int) ($message['position'] ?? 0) !== $position) {
                throw new RuntimeException("Message at position {$position} has an unexpected role or position.");
            }

            $subject = trim((string) ($message['subject'] ?? ''));
            $headline = trim((string) ($message['headline'] ?? ''));

            if ($subject === '' || $headline === '') {
                throw new RuntimeException("Message at position {$position} is missing a subject or headline.");
            }

            $paragraphs = $message['body_paragraphs'] ?? null;

            if (! is_array($paragraphs) || $paragraphs === []) {
                throw new RuntimeException("Message at position {$position} is missing body paragraphs.");
            }

            $paragraphs = array_values(array_filter(array_map(
                static fn (mixed $paragraph): ?string => is_string($paragraph) ? trim($paragraph) : null,
                $paragraphs,
            )));

            if ($paragraphs === []) {
                throw new RuntimeException("Message at position {$position} has no non-empty body paragraphs.");
            }

            $evidenceIds = $message['evidence_ids'] ?? [];

            if (! is_array($evidenceIds)) {
                throw new RuntimeException("Message at position {$position} has an invalid evidence_ids field.");
            }

            $evidenceIds = array_values(array_filter(array_map(
                static fn (mixed $id): ?string => is_string($id) ? trim($id) : null,
                $evidenceIds,
            )));

            $normalized[] = [
                'position' => $position,
                'role' => $role,
                'subject' => $subject,
                'headline' => $headline,
                'body_paragraphs' => $paragraphs,
                'evidence_ids' => $evidenceIds,
            ];
        }

        return ['messages' => $normalized];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $raw): array
    {
        $raw = trim($raw);

        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $raw, $matches) === 1) {
            $raw = $matches[1];
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start === false || $end === false || $end <= $start) {
            throw new RuntimeException('Model response does not contain a JSON object.');
        }

        $candidate = substr($raw, $start, $end - $start + 1);

        try {
            $decoded = json_decode($candidate, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('Model response is not valid JSON: '.$exception->getMessage());
        }

        return is_array($decoded) ? $decoded : [];
    }
}
