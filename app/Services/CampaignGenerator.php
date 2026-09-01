<?php

namespace App\Services;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\GenerationAttempt;
use App\Models\IntentResponse;
use App\Models\Visitor;
use Illuminate\Support\Str;
use RuntimeException;

class CampaignGenerator
{
    public function __construct(
        private CampaignPromptBuilder $promptBuilder,
        private LlmClient $llmClient,
        private CampaignResponseParser $parser,
        private CampaignPolicyValidator $validator,
        private PresentationProfileResolver $presentationResolver,
        private BrandPromptProfileBuilder $brandProfileBuilder,
    ) {}

    public function generate(IntentResponse $intentResponse): Campaign
    {
        $visitor = $intentResponse->visitor()->first();
        $angle = $intentResponse->angle()->with('brand')->first();

        if (! $visitor instanceof Visitor || ! $angle instanceof Angle || ! $angle->brand instanceof Brand) {
            throw new RuntimeException('Campaign inputs are incomplete.');
        }

        if (! $visitor->hasActiveEmailConsent()) {
            throw new RuntimeException('Visitor has no active email consent.');
        }

        $context = $this->buildContext($visitor, $angle, $intentResponse);
        $prompt = $this->promptBuilder->build($context['prompt_input']);

        $campaignQuery = Campaign::query()->where('intent_response_id', $intentResponse->id);

        $generated = (clone $campaignQuery)->where('status', Campaign::STATUS_GENERATED)->first();

        if ($generated instanceof Campaign) {
            return $generated;
        }

        $campaign = $campaignQuery->orderBy('id')->first();

        if (! $campaign instanceof Campaign) {
            $campaign = Campaign::query()->create([
                'visitor_id' => $visitor->id,
                'angle_id' => $angle->id,
                'brand_id' => $angle->brand_id,
                'intent_response_id' => $intentResponse->id,
                'status' => Campaign::STATUS_GENERATING,
                'presentation_profile' => $context['presentation_profile'],
            ]);
        }

        try {
            $messages = $this->attempt($campaign, $prompt, $context['validator_context']);
        } catch (RuntimeException $exception) {
            // Provider/network failures fail closed: mark the campaign failed and
            // rethrow so the queue job records the failure. LlmClient logs the
            // provider error body; never log secrets here.
            $campaign->update(['status' => Campaign::STATUS_FAILED]);

            throw $exception;
        }

        if ($messages === null) {
            $campaign->update(['status' => Campaign::STATUS_FAILED]);

            return $campaign;
        }

        $this->persistMessages($campaign, $messages);
        $campaign->update([
            'status' => Campaign::STATUS_GENERATED,
            'prompt_version' => $prompt['version'],
        ]);

        return $campaign;
    }

    /**
     * @param  array<string, mixed>  $validatorContext
     * @return array<int, array<string, mixed>>|null
     */
    private function attempt(Campaign $campaign, array $prompt, array $validatorContext): ?array
    {
        $maxAttempts = max(1, (int) config('llm.retries', 1) + 1);
        $model = (string) config('llm.model', '');
        $provider = $this->providerName();
        $payload = $prompt;

        for ($attemptNumber = 1; $attemptNumber <= $maxAttempts; $attemptNumber++) {
            $result = $this->llmClient->complete($payload['messages']);
            $raw = (string) $result['content'];

            try {
                $parsed = $this->parser->parse($raw);
            } catch (RuntimeException $exception) {
                $violations = ['Malformed model output: '.$exception->getMessage()];
                $this->recordAttempt($campaign, $payload, $raw, $violations, $attemptNumber, $provider, $model, false);

                if ($attemptNumber >= $maxAttempts) {
                    return null;
                }

                $payload = $this->reprompt($prompt, $violations);

                continue;
            }

            $violations = $this->validator->validate($parsed, $validatorContext);
            $passed = $violations === [];
            $this->recordAttempt($campaign, $payload, $raw, $violations, $attemptNumber, $provider, $model, $passed);

            if ($passed) {
                return $parsed['messages'];
            }

            if ($attemptNumber >= $maxAttempts) {
                return null;
            }

            $payload = $this->reprompt($prompt, $violations);
        }

        return null;
    }

    /**
     * @param  array<int, string>  $violations
     * @return array<string, mixed>
     */
    private function reprompt(array $prompt, array $violations): array
    {
        $payload = $prompt;
        $list = implode("\n", array_map(static fn (string $violation): string => "- {$violation}", $violations));

        $payload['messages'][] = [
            'role' => 'user',
            'content' => "Your previous response was rejected by the deterministic safety validator. Fix these violations and return valid JSON only:\n{$list}",
        ];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $violations
     */
    private function recordAttempt(
        Campaign $campaign,
        array $payload,
        string $raw,
        array $violations,
        int $attemptNumber,
        string $provider,
        string $model,
        bool $passed,
    ): void {
        $campaign->generationAttempts()->create([
            'attempt_number' => $attemptNumber,
            'provider' => $provider,
            'model' => $model,
            'prompt_version' => $payload['version'],
            'prompt_payload' => $payload['messages'],
            'raw_response' => ['content' => $raw],
            'violations' => $violations === [] ? null : $violations,
            'status' => $passed ? GenerationAttempt::STATUS_GENERATED : GenerationAttempt::STATUS_FAILED,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    private function persistMessages(Campaign $campaign, array $messages): void
    {
        foreach ($messages as $message) {
            $campaign->messages()->create([
                'sequence_position' => $message['position'],
                'channel' => CampaignMessage::CHANNEL_EMAIL,
                'role' => $message['role'],
                'subject' => $message['subject'],
                'headline' => $message['headline'],
                'body_paragraphs' => $message['body_paragraphs'],
                'evidence_ids' => $message['evidence_ids'],
                'open_token' => (string) Str::uuid(),
                'status' => CampaignMessage::STATUS_GENERATED,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(Visitor $visitor, Angle $angle, IntentResponse $intentResponse): array
    {
        $brand = $angle->brand;

        $brandProfile = $this->brandProfileBuilder->build([
            'name' => $brand->name,
            'tone_preset' => $brand->tone_preset,
            'flow_preset' => $brand->flow_preset,
            'tense_preset' => $brand->tense_preset,
            'reading_level_preset' => $brand->reading_level_preset,
            'preferred_terms' => $brand->preferred_terms ?? [],
            'avoided_terms' => $brand->avoided_terms ?? [],
        ]);

        $presentationProfile = $this->presentationResolver->resolve($intentResponse->age_group, $intentResponse->sex);

        return [
            'prompt_input' => [
                'brand_profile' => $brandProfile,
                'angle' => $angle->only([
                    'audience',
                    'trigger_moment',
                    'primary_job',
                    'tension',
                    'desired_outcome',
                    'single_promise',
                    'proof',
                    'objection',
                    'tone',
                ]),
                'visitor' => [
                    'preferred_name' => $visitor->preferred_name,
                ],
                'intent' => [
                    'sub_interest' => $intentResponse->sub_interest,
                    'trigger' => $intentResponse->trigger,
                    'concern' => $intentResponse->concern,
                ],
                'presentation_profile' => $presentationProfile,
            ],
            'validator_context' => [
                'allowed_evidence_ids' => [], // TODO: wire deterministic brand-scoped evidence source.
                'avoided_terms' => $brand->avoided_terms ?? [],
                'demographic_terms' => $this->demographicTerms($intentResponse),
            ],
            'presentation_profile' => $presentationProfile,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function demographicTerms(IntentResponse $intentResponse): array
    {
        $terms = [];
        $label = config("capture.age_groups.{$intentResponse->age_group}.label");

        if (is_string($label) && $label !== '') {
            $terms[] = $label;
        }

        if (in_array($intentResponse->sex, ['female', 'male', 'intersex', 'prefer_not_to_say'], true)) {
            $terms[] = $intentResponse->sex;
        }

        return $terms;
    }

    private function providerName(): string
    {
        $host = parse_url((string) config('llm.base_url', ''), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : 'unknown';
    }
}
