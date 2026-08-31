<?php

namespace Tests\Unit;

use App\Services\LlmClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmClientTest extends TestCase
{
    public function test_it_posts_to_the_openai_compatible_endpoint_and_returns_content(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'model' => 'test-model',
                'choices' => [
                    ['message' => ['content' => 'hello'], 'finish_reason' => 'stop'],
                ],
                'usage' => [],
            ], 200),
        ]);

        config([
            'llm.base_url' => 'https://api.example.com/v1',
            'llm.api_key' => 'secret',
            'llm.model' => 'test-model',
            'llm.endpoint' => 'chat/completions',
            'llm.sampling' => [
                'temperature' => 0.4,
                'max_tokens' => 1500,
            ],
        ]);

        $result = (new LlmClient)->complete([['role' => 'user', 'content' => 'hi']]);

        $this->assertSame('hello', $result['content']);
        $this->assertSame('test-model', $result['model']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/v1/chat/completions'
                && $request['model'] === 'test-model'
                && $request['temperature'] === 0.4
                && $request['max_tokens'] === 1500
                && $request['response_format'] === ['type' => 'json_object']
                && ! array_key_exists('top_p', $request->data())
                && $request->hasHeader('Authorization', 'Bearer secret');
        });
    }

    public function test_empty_response_format_omits_the_field(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'model' => 'test-model',
                'choices' => [
                    ['message' => ['content' => 'hello'], 'finish_reason' => 'stop'],
                ],
            ], 200),
        ]);

        config([
            'llm.base_url' => 'https://api.example.com/v1',
            'llm.api_key' => 'secret',
            'llm.model' => 'test-model',
            'llm.response_format' => '',
        ]);

        (new LlmClient)->complete([['role' => 'user', 'content' => 'hi']]);

        Http::assertSent(function ($request) {
            return ! array_key_exists('response_format', $request->data())
                && ! array_key_exists('safety_model', $request->data());
        });
    }

    public function test_missing_key_throws_a_non_secret_error(): void
    {
        config([
            'llm.base_url' => 'https://api.example.com/v1',
            'llm.api_key' => '',
            'llm.model' => 'test-model',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LLM_API_KEY');

        (new LlmClient)->complete([]);
    }

    public function test_failed_http_response_throws(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([], 500),
        ]);

        config([
            'llm.base_url' => 'https://api.example.com/v1',
            'llm.api_key' => 'secret',
            'llm.model' => 'test-model',
        ]);

        $this->expectException(\RuntimeException::class);

        (new LlmClient)->complete([]);
    }
}
