<?php

namespace App\Services;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\IntentResponse;
use App\Models\LandingEvent;
use App\Models\Suppression;
use App\Models\Visitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function for(int $organizationId, ?int $brandId = null): array
    {
        $brands = Brand::query()
            ->forOrganization($organizationId)
            ->when($brandId !== null, fn ($q) => $q->whereKey($brandId))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $brandIds = $brands->pluck('id')->map(fn ($id) => (int) $id)->all();

        $angleRows = Angle::query()
            ->whereIn('brand_id', $brandIds)
            ->orderBy('brand_id')
            ->orderBy('name')
            ->get(['id', 'brand_id', 'name', 'slug', 'landing_identifier']);

        $angleIds = $angleRows->pluck('id')->map(fn ($id) => (int) $id)->all();

        $overall = $this->overall($brandIds);

        return [
            'brands' => $brands->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ])->values()->all(),
            'overall' => $overall,
            'funnel' => $this->funnel($overall),
            'angles' => $this->angles($angleRows, $angleIds),
        ];
    }

    /**
     * @param  array<int, int>  $brandIds
     * @return array<string, int|float|null>
     */
    private function overall(array $brandIds): array
    {
        $landings = LandingEvent::query()->whereIn('brand_id', $brandIds)->count();
        $landed = LandingEvent::query()->whereIn('brand_id', $brandIds)->distinct()->count('fingerprint');
        $captures = IntentResponse::query()->whereHas('angle', fn ($q) => $q->whereIn('brand_id', $brandIds))->count();
        $consented = $this->consentedVisitors($brandIds, null);
        $campaignsGenerated = Campaign::query()->whereIn('brand_id', $brandIds)->where('status', Campaign::STATUS_GENERATED)->count();
        $activeCampaigns = Campaign::query()->whereIn('brand_id', $brandIds)->where('status', '!=', Campaign::STATUS_FAILED)->count();
        $campaignsSent = Campaign::query()->whereIn('brand_id', $brandIds)->whereHas('messages', fn ($q) => $q->whereNotNull('sent_at'))->count();
        $messagesGenerated = CampaignMessage::query()->whereHas('campaign', fn ($q) => $q->whereIn('brand_id', $brandIds))->count();
        $messagesSent = CampaignMessage::query()->whereHas('campaign', fn ($q) => $q->whereIn('brand_id', $brandIds))->whereNotNull('sent_at')->count();
        $messagesOpened = CampaignMessage::query()->whereHas('campaign', fn ($q) => $q->whereIn('brand_id', $brandIds))->whereNotNull('opened_at')->count();

        return [
            'landings' => $landings,
            'landed_visitors' => $landed,
            'captures' => $captures,
            'consented_visitors' => $consented,
            'campaigns_generated' => $campaignsGenerated,
            'active_campaigns' => $activeCampaigns,
            'campaigns_sent' => $campaignsSent,
            'messages_generated' => $messagesGenerated,
            'messages_sent' => $messagesSent,
            'messages_opened' => $messagesOpened,
            'open_rate' => $this->rate($messagesOpened, $messagesSent),
        ];
    }

    /**
     * @param  array<string, int|float|null>  $overall
     * @return array<int, array<string, mixed>>
     */
    private function funnel(array $overall): array
    {
        $landed = (int) $overall['landed_visitors'];
        $consented = (int) $overall['consented_visitors'];
        $generated = (int) $overall['campaigns_generated'];
        $sent = (int) $overall['campaigns_sent'];

        return [
            ['key' => 'landed', 'label' => 'Landed', 'count' => $landed, 'rate' => null],
            ['key' => 'consented', 'label' => 'Consented', 'count' => $consented, 'rate' => $this->rate($consented, $landed)],
            ['key' => 'generated', 'label' => 'Generated', 'count' => $generated, 'rate' => $this->rate($generated, $consented)],
            ['key' => 'sent', 'label' => 'Sent', 'count' => $sent, 'rate' => $this->rate($sent, $generated)],
        ];
    }

    /**
     * @param  Collection<int, Angle>  $angleRows
     * @param  array<int, int>  $angleIds
     * @return array<int, array<string, mixed>>
     */
    private function angles(Collection $angleRows, array $angleIds): array
    {
        $landingsByAngle = LandingEvent::query()
            ->whereIn('angle_id', $angleIds)
            ->selectRaw('angle_id, count(*) as landings, count(distinct fingerprint) as landed')
            ->groupBy('angle_id')
            ->get()
            ->keyBy('angle_id');

        $capturesByAngle = IntentResponse::query()
            ->whereIn('angle_id', $angleIds)
            ->selectRaw('angle_id, count(*) as captures')
            ->groupBy('angle_id')
            ->get()
            ->keyBy('angle_id');

        $consentedByAngle = DB::table('visitors')
            ->join('intent_responses', 'intent_responses.visitor_id', '=', 'visitors.id')
            ->join('consent_records', 'consent_records.visitor_id', '=', 'visitors.id')
            ->whereIn('intent_responses.angle_id', $angleIds)
            ->where('consent_records.channel', Suppression::CHANNEL_EMAIL)
            ->selectRaw('intent_responses.angle_id, count(distinct visitors.id) as consented')
            ->groupBy('intent_responses.angle_id')
            ->get()
            ->keyBy('angle_id');

        $campaignsByAngle = Campaign::query()
            ->whereIn('angle_id', $angleIds)
            ->selectRaw(
                'angle_id,
                sum(case when status = ? then 1 else 0 end) as generated,
                sum(case when status <> ? then 1 else 0 end) as active',
                [Campaign::STATUS_GENERATED, Campaign::STATUS_FAILED],
            )
            ->groupBy('angle_id')
            ->get()
            ->keyBy('angle_id');

        $campaignsSentByAngle = Campaign::query()
            ->whereIn('angle_id', $angleIds)
            ->whereHas('messages', fn ($q) => $q->whereNotNull('sent_at'))
            ->selectRaw('angle_id, count(*) as sent_campaigns')
            ->groupBy('angle_id')
            ->get()
            ->keyBy('angle_id');

        $messagesByAngle = CampaignMessage::query()
            ->join('campaigns', 'campaigns.id', '=', 'campaign_messages.campaign_id')
            ->whereIn('campaigns.angle_id', $angleIds)
            ->selectRaw(
                'campaigns.angle_id,
                sum(case when campaign_messages.sent_at is not null then 1 else 0 end) as sent,
                sum(case when campaign_messages.opened_at is not null then 1 else 0 end) as opened',
            )
            ->groupBy('campaigns.angle_id')
            ->get()
            ->keyBy('angle_id');

        return $angleRows->map(function (Angle $angle) use (
            $landingsByAngle,
            $capturesByAngle,
            $consentedByAngle,
            $campaignsByAngle,
            $campaignsSentByAngle,
            $messagesByAngle,
        ): array {
            $landings = (int) ($landingsByAngle[$angle->id]->landings ?? 0);
            $landed = (int) ($landingsByAngle[$angle->id]->landed ?? 0);
            $captures = (int) ($capturesByAngle[$angle->id]->captures ?? 0);
            $consented = (int) ($consentedByAngle[$angle->id]->consented ?? 0);
            $generated = (int) ($campaignsByAngle[$angle->id]->generated ?? 0);
            $active = (int) ($campaignsByAngle[$angle->id]->active ?? 0);
            $campaignsSent = (int) ($campaignsSentByAngle[$angle->id]->sent_campaigns ?? 0);
            $sent = (int) ($messagesByAngle[$angle->id]->sent ?? 0);
            $opened = (int) ($messagesByAngle[$angle->id]->opened ?? 0);

            return [
                'id' => $angle->id,
                'brand_id' => $angle->brand_id,
                'name' => $angle->name,
                'slug' => $angle->slug,
                'landing_identifier' => $angle->landing_identifier,
                'landings' => $landings,
                'landed_visitors' => $landed,
                'captures' => $captures,
                'consented_visitors' => $consented,
                'campaigns_generated' => $generated,
                'active_campaigns' => $active,
                'campaigns_sent' => $campaignsSent,
                'messages_sent' => $sent,
                'messages_opened' => $opened,
                'consent_rate' => $this->rate($consented, $landed),
                'delivery_success_rate' => $this->rate($campaignsSent, $generated),
                'open_rate' => $this->rate($opened, $sent),
            ];
        })->values()->all();
    }

    /**
     * @param  array<int, int>  $brandIds
     */
    private function consentedVisitors(array $brandIds, ?int $angleId): int
    {
        $query = Visitor::query()
            ->whereIn('brand_id', $brandIds)
            ->whereHas('consentRecords', fn ($q) => $q->where('channel', Suppression::CHANNEL_EMAIL));

        if ($angleId !== null) {
            $query->whereHas('intentResponses', fn ($q) => $q->where('angle_id', $angleId));
        }

        return (int) $query->distinct()->count('id');
    }

    private function rate(int $numerator, int $denominator): ?float
    {
        if ($denominator === 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
