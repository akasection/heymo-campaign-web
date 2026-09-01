<?php

$nullableNumber = static function (string $key, string $cast = 'float') {
    $value = env($key);

    if ($value === null || $value === '') {
        return null;
    }

    return $cast === 'int' ? (int) $value : (float) $value;
};

$nullableList = static function (string $key) {
    $value = env($key);

    if ($value === null || trim((string) $value) === '') {
        return null;
    }

    return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
};

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI-compatible LLM provider (Together AI / Fireworks AI)
    |--------------------------------------------------------------------------
    |
    | The provider and model are configuration values, never source-code
    | constants. Keys stay server-side and must never be serialized to the
    | browser, logged, or surfaced in an error message.
    |
    */

    'base_url' => env('LLM_API_URL'),
    'api_key' => env('LLM_API_KEY'),
    'model' => env('LLM_MODEL'),
    'endpoint' => env('LLM_ENDPOINT', 'chat/completions'),
    'timeout' => (int) env('LLM_TIMEOUT', 30),
    'retries' => (int) env('LLM_RETRIES', 1),

    /*
    |--------------------------------------------------------------------------
    | Structured output
    |--------------------------------------------------------------------------
    |
    | Together AI supports "json_object", "json_schema", and "regex" through the
    | response_format field. "json_object" is the expected value here because the
    | campaign pipeline parses strict JSON. Set it to an empty value to disable
    | the field (for models that do not support JSON mode).
    |
    */

    'response_format' => env('LLM_RESPONSE_FORMAT', 'json_object'),

    /*
    |--------------------------------------------------------------------------
    | Together-specific moderation
    |--------------------------------------------------------------------------
    |
    | Optional moderation model (e.g. "meta-llama/Meta-Llama-Guard-3-8B"). Leave
    | empty to disable. Applied server-side by Together AI when set.
    |
    */

    'safety_model' => env('LLM_SAFETY_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Sampling controls
    |--------------------------------------------------------------------------
    |
    | Null values are omitted from the request so Together AI applies its
    | per-model defaults. Tune temperature or top_p/top_k, not both; pick one of
    | the three repetition penalties rather than stacking them.
    |
    */

    'sampling' => [
        'temperature' => (float) env('LLM_TEMPERATURE', 0.4),
        'max_tokens' => (int) env('LLM_MAX_TOKENS', 1500),
        'top_p' => $nullableNumber('LLM_TOP_P'),
        'top_k' => $nullableNumber('LLM_TOP_K', 'int'),
        'repetition_penalty' => $nullableNumber('LLM_REPETITION_PENALTY'),
        'frequency_penalty' => $nullableNumber('LLM_FREQUENCY_PENALTY'),
        'presence_penalty' => $nullableNumber('LLM_PRESENCE_PENALTY'),
        'seed' => $nullableNumber('LLM_SEED', 'int'),
        'stop' => $nullableList('LLM_STOP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt version
    |--------------------------------------------------------------------------
    |
    | Identifies the assembled prompt recipe so every generation attempt can
    | reconstruct which static contracts and routing rules were in effect.
    |
    */

    'prompt_version' => 'campaign-generation-v1',

];
