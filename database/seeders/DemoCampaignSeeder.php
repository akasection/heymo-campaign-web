<?php

namespace Database\Seeders;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\ConsentRecord;
use App\Models\DeliveryEvent;
use App\Models\EngagementEvent;
use App\Models\GenerationAttempt;
use App\Models\IntentResponse;
use App\Models\LandingEvent;
use App\Models\Suppression;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoCampaignSeeder extends Seeder
{
    /**
     * Deterministic, dev/demo-only campaign history. Never runs in production.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $brands = Brand::query()->whereIn('slug', ['lexical-labs', 'xo-health-group'])->get()->keyBy('slug');

        $angles = Angle::query()
            ->with('brand')
            ->whereIn('brand_id', $brands->pluck('id'))
            ->get()
            ->groupBy(fn (Angle $angle) => $angle->brand->slug)
            ->mapWithKeys(fn (Collection $list, string $slug) => [$slug => $list->keyBy('slug')]);

        $base = Carbon::create(2026, 7, 21, 9, 0, 0);
        $offset = 0;

        foreach ($this->captures() as $definition) {
            $offset++;
            $this->seedCapture($definition, $brands, $angles, $base->copy()->addMinutes($offset * 20));
        }

        foreach ($this->landingOnly() as $definition) {
            $offset++;
            $this->seedLandingOnly($definition, $brands, $angles, $base->copy()->addMinutes($offset * 20));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function captures(): array
    {
        return [
            // Same-angle pair (lexical fatigue): demographics + answers visibly change the copy.
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'fatigue',
                'fingerprint' => '10000000-0000-4000-8000-000000000001',
                'email' => 'jamie.fatigue@example.com',
                'preferred_name' => 'Jamie',
                'age_group' => '18_29',
                'sex' => 'female',
                'sub_interest' => 'daily_energy',
                'trigger' => 'recent_change',
                'concern' => 'I can get through the day but I do not feel like myself.',
                'attribution' => ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'fatigue_spring'],
                'device' => ['type' => 'mobile', 'os' => 'iOS', 'browser' => 'Safari'],
                'session_duration_seconds' => 95,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'compact'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'queued', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'fatigue',
                'fingerprint' => '10000000-0000-4000-8000-000000000002',
                'email' => 'marcus.fatigue@example.com',
                'preferred_name' => 'Marcus',
                'age_group' => '45_59',
                'sex' => 'male',
                'sub_interest' => 'stamina',
                'trigger' => 'busy_stretch',
                'concern' => 'Energy crashes mid-afternoon and thyroid runs in my family.',
                'attribution' => null,
                'device' => ['type' => 'desktop', 'os' => 'Windows', 'browser' => 'Chrome'],
                'session_duration_seconds' => 180,
                'presentation_profile' => ['type_size' => 'large', 'density' => 'roomy'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'obesity',
                'fingerprint' => '10000000-0000-4000-8000-000000000003',
                'email' => 'priya.wellness@example.com',
                'preferred_name' => 'Priya',
                'age_group' => '30_44',
                'sex' => 'female',
                'sub_interest' => 'steady_habits',
                'trigger' => 'health_goal',
                'concern' => 'I want information I can understand and use at my own pace.',
                'attribution' => ['utm_source' => 'newsletter', 'utm_medium' => 'email'],
                'device' => ['type' => 'mobile', 'os' => 'Android', 'browser' => 'Chrome'],
                'session_duration_seconds' => 72,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'standard'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'premarital-check',
                'fingerprint' => '10000000-0000-4000-8000-000000000004',
                'email' => 'dev.couple@example.com',
                'preferred_name' => 'Dev',
                'age_group' => '30_44',
                'sex' => 'male',
                'sub_interest' => 'shared_baseline',
                'trigger' => 'engagement',
                'concern' => 'I want it to feel like a shared step, not a scary checklist.',
                'attribution' => ['utm_source' => 'instagram', 'utm_medium' => 'social'],
                'device' => ['type' => 'mobile', 'os' => 'iOS', 'browser' => 'Safari'],
                'session_duration_seconds' => 110,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'standard'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'queued', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'athletic-performance',
                'fingerprint' => '10000000-0000-4000-8000-000000000005',
                'email' => 'leo.training@example.com',
                'preferred_name' => 'Leo',
                'age_group' => '18_29',
                'sex' => 'male',
                'sub_interest' => 'training_recovery',
                'trigger' => 'new_program',
                'concern' => 'I want to know what to watch before changing my whole plan.',
                'attribution' => ['utm_source' => 'google', 'utm_medium' => 'cpc'],
                'device' => ['type' => 'mobile', 'os' => 'Android', 'browser' => 'Chrome'],
                'session_duration_seconds' => 130,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'compact'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'fatigue',
                'fingerprint' => '10000000-0000-4000-8000-000000000006',
                'email' => 'suppressed.fatigue@example.com',
                'preferred_name' => 'Rae',
                'age_group' => '45_59',
                'sex' => 'female',
                'sub_interest' => 'sleep_recovery',
                'trigger' => 'sleep_not_restoring',
                'concern' => 'Rest no longer feels like a reliable reset.',
                'attribution' => null,
                'device' => ['type' => 'desktop', 'os' => 'macOS', 'browser' => 'Safari'],
                'session_duration_seconds' => 60,
                'presentation_profile' => ['type_size' => 'large', 'density' => 'roomy'],
                'suppressed' => true,
                'delivery' => [
                    ['status' => 'skipped', 'opened' => false],
                    ['status' => 'skipped', 'opened' => false],
                    ['status' => 'skipped', 'opened' => false],
                ],
            ],
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'athletic-performance',
                'fingerprint' => '10000000-0000-4000-8000-000000000007',
                'email' => 'failed.generation@example.com',
                'preferred_name' => 'Sam',
                'age_group' => '60_74',
                'sex' => 'male',
                'sub_interest' => 'baseline',
                'trigger' => 'returning',
                'concern' => 'I do not want more data unless the scope is clear.',
                'attribution' => ['utm_source' => 'bing', 'utm_medium' => 'cpc'],
                'device' => ['type' => 'desktop', 'os' => 'Windows', 'browser' => 'Edge'],
                'session_duration_seconds' => 45,
                'campaign' => 'failed',
            ],
            [
                'brand' => 'xo-health-group',
                'landing_identifier' => 'fatigue',
                'fingerprint' => '20000000-0000-4000-8000-000000000008',
                'email' => 'henry.fatigue@example.com',
                'preferred_name' => 'Henry',
                'age_group' => '45_59',
                'sex' => 'male',
                'sub_interest' => 'sleep_recovery',
                'trigger' => 'sleep_not_restoring',
                'concern' => 'Sleep is not giving me my usual reset.',
                'attribution' => ['utm_source' => 'google', 'utm_medium' => 'cpc'],
                'device' => ['type' => 'desktop', 'os' => 'Windows', 'browser' => 'Chrome'],
                'session_duration_seconds' => 150,
                'presentation_profile' => ['type_size' => 'large', 'density' => 'roomy'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
            [
                'brand' => 'xo-health-group',
                'landing_identifier' => 'obesity',
                'fingerprint' => '20000000-0000-4000-8000-000000000009',
                'email' => 'ana.wellness@example.com',
                'preferred_name' => 'Ana',
                'age_group' => '30_44',
                'sex' => 'female',
                'sub_interest' => 'understand_trends',
                'trigger' => 'frustration',
                'concern' => 'I do not want a test to imply one set of measurements defines my health.',
                'attribution' => null,
                'device' => ['type' => 'mobile', 'os' => 'iOS', 'browser' => 'Safari'],
                'session_duration_seconds' => 88,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'standard'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'queued', 'opened' => false],
                ],
            ],
            [
                'brand' => 'xo-health-group',
                'landing_identifier' => 'premarital-check',
                'fingerprint' => '20000000-0000-4000-8000-000000000010',
                'email' => 'tony.couple@example.com',
                'preferred_name' => 'Tony',
                'age_group' => '30_44',
                'sex' => 'male',
                'sub_interest' => 'peace_of_mind',
                'trigger' => 'upcoming_wedding',
                'concern' => 'We want a serious but supportive step before the wedding.',
                'attribution' => ['utm_source' => 'facebook', 'utm_medium' => 'social'],
                'device' => ['type' => 'mobile', 'os' => 'Android', 'browser' => 'Chrome'],
                'session_duration_seconds' => 120,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'standard'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => true],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
            [
                'brand' => 'xo-health-group',
                'landing_identifier' => 'athletic-performance',
                'fingerprint' => '20000000-0000-4000-8000-000000000011',
                'email' => 'mira.training@example.com',
                'preferred_name' => 'Mira',
                'age_group' => '18_29',
                'sex' => 'female',
                'sub_interest' => 'baseline',
                'trigger' => 'returning',
                'concern' => 'I want clear scope before another set of numbers.',
                'attribution' => ['utm_source' => 'google', 'utm_medium' => 'organic'],
                'device' => ['type' => 'mobile', 'os' => 'iOS', 'browser' => 'Safari'],
                'session_duration_seconds' => 100,
                'presentation_profile' => ['type_size' => 'standard', 'density' => 'compact'],
                'delivery' => [
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                    ['status' => 'sent', 'opened' => false],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function landingOnly(): array
    {
        return [
            [
                'brand' => 'lexical-labs',
                'landing_identifier' => 'fatigue',
                'fingerprint' => '30000000-0000-4000-8000-000000000012',
                'attribution' => ['utm_source' => 'facebook', 'utm_medium' => 'social'],
                'device' => ['type' => 'mobile', 'os' => 'Android', 'browser' => 'Chrome'],
            ],
            [
                'brand' => 'xo-health-group',
                'landing_identifier' => 'athletic-performance',
                'fingerprint' => '30000000-0000-4000-8000-000000000013',
                'attribution' => null,
                'device' => ['type' => 'desktop', 'os' => 'macOS', 'browser' => 'Safari'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @param  Collection<int, Brand>  $brands
     * @param  Collection<int, Collection<string, Angle>>  $angles
     */
    private function seedCapture(array $d, Collection $brands, Collection $angles, Carbon $at): void
    {
        $brand = $brands[$d['brand']];
        $angle = $angles[$d['brand']][$d['landing_identifier']];

        $visitor = $this->insert(new Visitor, [
            'brand_id' => $brand->id,
            'preferred_name' => $d['preferred_name'],
            'email' => $d['email'],
        ], $at);

        $intent = $this->insert(new IntentResponse, [
            'visitor_id' => $visitor->id,
            'angle_id' => $angle->id,
            'landing_identifier' => $d['landing_identifier'],
            'age_group' => $d['age_group'],
            'sex' => $d['sex'],
            'sub_interest' => $d['sub_interest'],
            'trigger' => $d['trigger'],
            'concern' => $d['concern'],
            'captured_at' => $at,
            'fingerprint' => $d['fingerprint'],
            'session_duration_seconds' => $d['session_duration_seconds'] ?? null,
            'attribution' => $d['attribution'] ?? null,
            'device' => $d['device'] ?? null,
        ], $at);

        $landedAt = $at->copy()->subMinutes(2);
        $this->insert(new LandingEvent, [
            'fingerprint' => $d['fingerprint'],
            'brand_id' => $brand->id,
            'landing_identifier' => $d['landing_identifier'],
            'angle_id' => $angle->id,
            'attribution' => $d['attribution'] ?? null,
            'device' => $d['device'] ?? null,
            'landed_at' => $landedAt,
        ], $landedAt);

        $consentedAt = $at->copy()->addSeconds(10);
        $this->insert(new ConsentRecord, [
            'visitor_id' => $visitor->id,
            'channel' => Suppression::CHANNEL_EMAIL,
            'email' => $d['email'],
            'source' => 'landing:'.$d['landing_identifier'],
            'policy_version' => config('capture.consent_policy_version'),
            'consented_at' => $consentedAt,
        ], $consentedAt);

        if (($d['campaign'] ?? 'generated') === 'failed') {
            $this->seedFailedCampaign($visitor, $angle, $brand, $intent, $d, $at);

            return;
        }

        $this->seedCampaign($visitor, $angle, $brand, $intent, $d, $at);

        if ($d['suppressed'] ?? false) {
            $suppressedAt = $at->copy()->addMinutes(6);
            $this->insert(new Suppression, [
                'visitor_id' => $visitor->id,
                'channel' => Suppression::CHANNEL_EMAIL,
                'reason' => Suppression::REASON_UNSUBSCRIBE,
                'source' => 'unsubscribe',
                'suppressed_at' => $suppressedAt,
            ], $suppressedAt);
        }
    }

    /**
     * @param  array<string, mixed>  $d
     */
    private function seedCampaign(Visitor $visitor, Angle $angle, Brand $brand, IntentResponse $intent, array $d, Carbon $at): Campaign
    {
        $campaign = $this->insert(new Campaign, [
            'visitor_id' => $visitor->id,
            'angle_id' => $angle->id,
            'brand_id' => $brand->id,
            'intent_response_id' => $intent->id,
            'status' => Campaign::STATUS_GENERATED,
            'presentation_profile' => $d['presentation_profile'] ?? ['type_size' => 'standard', 'density' => 'standard'],
            'prompt_version' => 'seed-1.0',
        ], $at);

        $this->insert(new GenerationAttempt, [
            'campaign_id' => $campaign->id,
            'attempt_number' => 1,
            'provider' => 'together',
            'model' => $d['model'] ?? 'kimi-k2',
            'prompt_version' => 'seed-1.0',
            'prompt_payload' => ['seed' => true],
            'raw_response' => ['seed' => true],
            'violations' => [],
            'status' => GenerationAttempt::STATUS_GENERATED,
            'error_message' => null,
        ], $at->copy()->addSeconds(5));

        $beats = $this->messageBeats($angle, $d);

        foreach ($beats as $index => $beat) {
            $delivery = $d['delivery'][$index] ?? ['status' => 'sent', 'opened' => false];
            $deliveryStatus = $delivery['status'];
            $scheduledAt = $at->copy()->addMinutes(1 + $index * 30);
            $sentAt = $deliveryStatus === 'sent' ? $scheduledAt->copy()->addSeconds(5) : null;
            $opened = $deliveryStatus === 'sent' && ($delivery['opened'] ?? false);

            $messageStatus = match ($deliveryStatus) {
                'skipped' => CampaignMessage::STATUS_SKIPPED,
                'queued' => CampaignMessage::STATUS_QUEUED,
                default => CampaignMessage::STATUS_SENT,
            };

            $message = $this->insert(new CampaignMessage, [
                'campaign_id' => $campaign->id,
                'sequence_position' => $index + 1,
                'channel' => CampaignMessage::CHANNEL_EMAIL,
                'role' => $beat['role'],
                'subject' => $beat['subject'],
                'headline' => $beat['headline'],
                'body_paragraphs' => $beat['body_paragraphs'],
                'evidence_ids' => [],
                'status' => $messageStatus,
                'scheduled_at' => $scheduledAt,
                'sent_at' => $sentAt,
                'open_token' => (string) Str::uuid(),
                'opened_at' => $opened ? $sentAt->copy()->addMinutes(3) : null,
            ], $scheduledAt);

            if ($deliveryStatus === 'skipped') {
                $this->insert(new DeliveryEvent, [
                    'campaign_message_id' => $message->id,
                    'attempt_number' => 1,
                    'status' => DeliveryEvent::STATUS_SKIPPED,
                    'to_address' => $d['email'],
                    'error_message' => null,
                    'metadata' => ['reason' => 'suppressed'],
                ], $scheduledAt->copy()->addSeconds(5));
            } else {
                $this->insert(new DeliveryEvent, [
                    'campaign_message_id' => $message->id,
                    'attempt_number' => 1,
                    'status' => DeliveryEvent::STATUS_ATTEMPTED,
                    'to_address' => $d['email'],
                    'error_message' => null,
                    'metadata' => null,
                ], $scheduledAt);

                if ($deliveryStatus === 'sent') {
                    $this->insert(new DeliveryEvent, [
                        'campaign_message_id' => $message->id,
                        'attempt_number' => 2,
                        'status' => DeliveryEvent::STATUS_SENT,
                        'to_address' => $d['email'],
                        'error_message' => null,
                        'metadata' => null,
                    ], $sentAt);
                }
            }

            if ($opened) {
                $this->insert(new EngagementEvent, [
                    'campaign_message_id' => $message->id,
                    'type' => EngagementEvent::TYPE_OPEN,
                    'occurred_at' => $sentAt->copy()->addMinutes(3),
                    'user_agent' => $d['device']['browser'] ?? null,
                    'metadata' => null,
                ], $sentAt->copy()->addMinutes(3));
            }
        }

        return $campaign;
    }

    /**
     * @param  array<string, mixed>  $d
     */
    private function seedFailedCampaign(Visitor $visitor, Angle $angle, Brand $brand, IntentResponse $intent, array $d, Carbon $at): void
    {
        $campaign = $this->insert(new Campaign, [
            'visitor_id' => $visitor->id,
            'angle_id' => $angle->id,
            'brand_id' => $brand->id,
            'intent_response_id' => $intent->id,
            'status' => Campaign::STATUS_FAILED,
            'presentation_profile' => [],
            'prompt_version' => null,
        ], $at);

        $this->insert(new GenerationAttempt, [
            'campaign_id' => $campaign->id,
            'attempt_number' => 1,
            'provider' => 'together',
            'model' => $d['model'] ?? 'kimi-k2',
            'prompt_version' => 'seed-1.0',
            'prompt_payload' => ['seed' => true],
            'raw_response' => ['seed' => true],
            'violations' => ['Unsupported evidence reference'],
            'status' => GenerationAttempt::STATUS_FAILED,
            'error_message' => 'Validator rejected output after retry.',
        ], $at->copy()->addSeconds(5));
    }

    /**
     * @param  array<string, mixed>  $d
     * @param  Collection<int, Brand>  $brands
     * @param  Collection<int, Collection<string, Angle>>  $angles
     */
    private function seedLandingOnly(array $d, Collection $brands, Collection $angles, Carbon $at): void
    {
        $brand = $brands[$d['brand']];
        $angle = $angles[$d['brand']][$d['landing_identifier']] ?? null;

        $this->insert(new LandingEvent, [
            'fingerprint' => $d['fingerprint'],
            'brand_id' => $brand->id,
            'landing_identifier' => $d['landing_identifier'],
            'angle_id' => $angle?->id,
            'attribution' => $d['attribution'] ?? null,
            'device' => $d['device'] ?? null,
            'landed_at' => $at,
        ], $at);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function messageBeats(Angle $angle, array $d): array
    {
        $name = $d['preferred_name'];
        $concern = $d['concern'];
        $ageLabel = config("capture.age_groups.{$d['age_group']}.label") ?? $d['age_group'];

        return [
            [
                'role' => CampaignMessage::ROLE_PROMISE,
                'subject' => "Your starting point for {$angle->slug}",
                'headline' => "{$name}, you are in the right place",
                'body_paragraphs' => [
                    "You told us you {$d['trigger']}, and that {$concern} We will keep the next steps grounded in exactly that, without turning one symptom into a diagnosis.",
                    'This first message restates the promise from the page: a clearer starting point, in your words.',
                ],
            ],
            [
                'role' => CampaignMessage::ROLE_MECHANISM,
                'subject' => 'How the panel gets you useful numbers',
                'headline' => 'Plain terms, no guesswork',
                'body_paragraphs' => [
                    "A blood panel reports the measurements included in your selected panel. For a {$ageLabel} adult, we keep the explanation direct and the numbers in one place.",
                    "Your focus on {$d['sub_interest']} shapes how we explain this, not what the test claims it can do.",
                ],
            ],
            [
                'role' => CampaignMessage::ROLE_OBJECTION,
                'subject' => 'The reason you hesitated, answered',
                'headline' => 'A fair hesitation',
                'body_paragraphs' => [
                    "You mentioned: {$concern} That is a fair hesitation, and it is exactly the kind of thing we address with the recorded objection rather than a generic FAQ.",
                    'Next step: review the panel details and decide whether ordering feels right for you.',
                ],
            ],
        ];
    }

    private function insert(Model $model, array $attributes, Carbon $at): Model
    {
        $model->fill($attributes);
        $model->created_at = $at;
        $model->updated_at = $at;
        $model->save();

        return $model;
    }
}
