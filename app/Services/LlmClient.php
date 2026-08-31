<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LlmClient
{
    /**
     * Call the OpenAI-compatible chat completions endpoint and return the
     * normalized first choice.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function complete(array $messages, array $options = []): array
    {
        $baseUrl = rtrim((string) config('llm.base_url', ''), '/');
        $endpoint = ltrim((string) config('llm.endpoint', 'chat/completions'), '/');
        $apiKey = (string) config('llm.api_key', '');
        $model = (string) config('llm.model', '');

        if ($baseUrl === '' || $model === '') {
            throw new RuntimeException('LLM provider is not configured: set LLM_API_URL and LLM_MODEL.');
        }

        if ($apiKey === '') {
            throw new RuntimeException('LLM provider is not configured: set LLM_API_KEY.');
        }

        $url = "{$baseUrl}/{$endpoint}";

        Log::debug('LLM request', [
            'url' => $url,
            'model' => $model,
            'messages' => count($messages),
            'timeout' => (int) config('llm.timeout', 30),
        ]);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('llm.timeout', 30))
            ->post($url, array_merge([
                'model' => $model,
                'messages' => $messages,
            ], $this->samplingPayload(), $this->responseFormatPayload(), $this->safetyModelPayload(), $options));

        // TODO(negative-flow): handle timeouts, 429 rate limits, and 5xx with a
        // bounded safe retry and non-secret, actionable error messages. Provider
        // errors that should retry must bubble up to the queued job's retry
        // policy; do not retry a malformed completion here.

        if ($response->failed()) {
            Log::error('LLM request failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $this->summarize((string) $response->body()),
            ]);

            throw new RuntimeException("LLM provider returned HTTP {$response->status()}.");
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('LLM provider returned a non-JSON response.');
        }

        $content = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('LLM provider returned an empty completion.');
        }

        return [
            'content' => $content,
            'model' => $data['model'] ?? $model,
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
            'usage' => $data['usage'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function samplingPayload(): array
    {
        $payload = [];
        $sampling = config('llm.sampling', []);

        foreach (array_keys($sampling) as $key) {
            if ($sampling[$key] !== null) {
                $payload[$key] = $sampling[$key];
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseFormatPayload(): array
    {
        $format = config('llm.response_format');

        if (! is_string($format) || $format === '') {
            return [];
        }

        return ['response_format' => ['type' => $format]];
    }

    /**
     * @return array<string, mixed>
     */
    private function safetyModelPayload(): array
    {
        $safetyModel = config('llm.safety_model');

        if (! is_string($safetyModel) || $safetyModel === '') {
            return [];
        }

        return ['safety_model' => $safetyModel];
    }

    /**
     * Truncate a provider response body for safe, non-secret log output.
     */
    private function summarize(string $body, int $length = 500): string
    {
        $body = trim($body);

        return $body === '' ? '' : mb_substr($body, 0, $length);
    }
}
