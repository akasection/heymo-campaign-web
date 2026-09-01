<?php

namespace App\Support;

class CaptureMetadata
{
    private const ATTRIBUTION_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer'];

    private const DEVICE_KEYS = ['type', 'os', 'browser'];

    /**
     * Keep only the allowlisted attribution keys and trim their values.
     * Returns null when nothing meaningful remains.
     *
     * @return array<string, string>|null
     */
    public static function sanitizeAttribution(mixed $value): ?array
    {
        return self::sanitizeMap($value, self::ATTRIBUTION_KEYS);
    }

    /**
     * Keep only the allowlisted device keys and trim their values.
     * Returns null when nothing meaningful remains.
     *
     * @return array<string, string>|null
     */
    public static function sanitizeDevice(mixed $value): ?array
    {
        return self::sanitizeMap($value, self::DEVICE_KEYS);
    }

    /**
     * @param  array<int, string>  $allowedKeys
     * @return array<string, string>|null
     */
    private static function sanitizeMap(mixed $value, array $allowedKeys): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $filtered = [];

        foreach ($allowedKeys as $key) {
            $entry = $value[$key] ?? null;

            if (! is_string($entry)) {
                continue;
            }

            $trimmed = preg_replace('/\s+/u', ' ', trim($entry));

            if (is_string($trimmed) && $trimmed !== '') {
                $filtered[$key] = $trimmed;
            }
        }

        return $filtered === [] ? null : $filtered;
    }
}
