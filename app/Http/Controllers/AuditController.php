<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditRecordResource;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\IntentResponse;
use App\Models\LandingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class AuditController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $brandIds = $this->organizationBrandIds($request);

        $query = IntentResponse::query()
            ->whereHas('angle', fn ($q) => $q->whereIn('brand_id', $brandIds))
            ->with(['visitor', 'angle.brand']);

        if ($brandId = $request->integer('brand_id')) {
            $query->whereHas('angle', fn ($q) => $q->where('brand_id', $brandId));
        }

        if ($angleId = $request->integer('angle_id')) {
            $query->where('angle_id', $angleId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->whereHas('visitor', function ($visitor) use ($search) {
                $visitor->where('email', 'ilike', "%{$search}%")
                    ->orWhere('preferred_name', 'ilike', "%{$search}%");
            });
        }

        $query->orderBy('captured_at', $request->string('sort')->toString() === 'oldest' ? 'asc' : 'desc');

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        $query->withCount([
            'campaigns as campaigns_count',
            'campaigns as campaigns_generated_count' => fn ($q) => $q->where('status', Campaign::STATUS_GENERATED),
            'messages as messages_sent_count' => fn ($q) => $q->whereNotNull('sent_at'),
            'messages as messages_opened_count' => fn ($q) => $q->whereNotNull('opened_at'),
        ]);

        return AuditRecordResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function show(Request $request, int $intentResponse): JsonResponse
    {
        $organizationId = (int) $request->user()->organization_id;

        $intent = IntentResponse::query()
            ->whereHas('angle.brand', fn ($q) => $q->where('organization_id', $organizationId))
            ->with([
                'visitor.brand',
                'visitor.consentRecords',
                'visitor.suppressions',
                'angle.brand',
                'campaigns' => fn ($q) => $q->with([
                    'generationAttempts',
                    'messages' => fn ($messages) => $messages->with(['deliveryEvents', 'engagementEvents'])->orderBy('sequence_position'),
                ])->orderBy('id'),
            ])
            ->findOrFail($intentResponse);

        $landingEvents = $intent->fingerprint
            ? LandingEvent::query()->where('fingerprint', $intent->fingerprint)->orderBy('landed_at')->get()
            : collect();

        return response()->json([
            'data' => $this->detailPayload($intent, $landingEvents),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function organizationBrandIds(Request $request): array
    {
        $organizationId = (int) $request->user()->organization_id;

        if ($organizationId === 0) {
            return [-1];
        }

        return Brand::query()
            ->forOrganization($organizationId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  Collection<int, LandingEvent>  $landingEvents
     * @return array<string, mixed>
     */
    private function detailPayload(IntentResponse $intent, Collection $landingEvents): array
    {
        return [
            'id' => $intent->id,
            'captured_at' => $intent->captured_at?->toIso8601String(),
            'landing_identifier' => $intent->landing_identifier,
            'fingerprint' => $intent->fingerprint,
            'session_duration_seconds' => $intent->session_duration_seconds,
            'attribution' => $intent->attribution,
            'device' => $intent->device,
            'age_group' => $intent->age_group,
            'sex' => $intent->sex,
            'sub_interest' => $intent->sub_interest,
            'trigger' => $intent->trigger,
            'concern' => $intent->concern,
            'visitor' => [
                'id' => $intent->visitor?->id,
                'preferred_name' => $intent->visitor?->preferred_name,
                'email' => $intent->visitor?->email,
                'created_at' => $intent->visitor?->created_at?->toIso8601String(),
            ],
            'angle' => [
                'id' => $intent->angle?->id,
                'name' => $intent->angle?->name,
                'slug' => $intent->angle?->slug,
                'landing_identifier' => $intent->angle?->landing_identifier,
                'brand' => [
                    'id' => $intent->angle?->brand?->id,
                    'name' => $intent->angle?->brand?->name,
                ],
            ],
            'landing_events' => $landingEvents->map(fn (LandingEvent $event) => [
                'id' => $event->id,
                'landed_at' => $event->landed_at?->toIso8601String(),
                'landing_identifier' => $event->landing_identifier,
                'attribution' => $event->attribution,
                'device' => $event->device,
            ])->values()->all(),
            'consent_records' => $intent->visitor?->consentRecords?->map(fn ($record) => [
                'channel' => $record->channel,
                'email' => $record->email,
                'source' => $record->source,
                'policy_version' => $record->policy_version,
                'consented_at' => $record->consented_at?->toIso8601String(),
            ])->values()->all() ?? [],
            'suppressions' => $intent->visitor?->suppressions?->map(fn ($record) => [
                'channel' => $record->channel,
                'reason' => $record->reason,
                'source' => $record->source,
                'suppressed_at' => $record->suppressed_at?->toIso8601String(),
            ])->values()->all() ?? [],
            'campaigns' => $intent->campaigns?->map(fn ($campaign) => [
                'id' => $campaign->id,
                'status' => $campaign->status,
                'presentation_profile' => $campaign->presentation_profile,
                'prompt_version' => $campaign->prompt_version,
                'created_at' => $campaign->created_at?->toIso8601String(),
                'generation_attempts' => $campaign->generationAttempts?->map(fn ($attempt) => [
                    'attempt_number' => $attempt->attempt_number,
                    'provider' => $attempt->provider,
                    'model' => $attempt->model,
                    'prompt_version' => $attempt->prompt_version,
                    'status' => $attempt->status,
                    'violations' => $attempt->violations,
                    'error_message' => $attempt->error_message,
                    'created_at' => $attempt->created_at?->toIso8601String(),
                ])->values()->all() ?? [],
                'messages' => $campaign->messages?->map(fn ($message) => [
                    'id' => $message->id,
                    'sequence_position' => $message->sequence_position,
                    'role' => $message->role,
                    'subject' => $message->subject,
                    'status' => $message->status,
                    'scheduled_at' => $message->scheduled_at?->toIso8601String(),
                    'sent_at' => $message->sent_at?->toIso8601String(),
                    'opened_at' => $message->opened_at?->toIso8601String(),
                    'delivery_events' => $message->deliveryEvents?->map(fn ($event) => [
                        'attempt_number' => $event->attempt_number,
                        'status' => $event->status,
                        'to_address' => $event->to_address,
                        'error_message' => $event->error_message,
                        'created_at' => $event->created_at?->toIso8601String(),
                    ])->values()->all() ?? [],
                    'engagement_events' => $message->engagementEvents?->map(fn ($event) => [
                        'type' => $event->type,
                        'occurred_at' => $event->occurred_at?->toIso8601String(),
                    ])->values()->all() ?? [],
                ])->values()->all() ?? [],
            ])->values()->all() ?? [],
        ];
    }
}
