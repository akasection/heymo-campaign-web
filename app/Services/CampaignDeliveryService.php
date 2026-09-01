<?php

namespace App\Services;

use App\Jobs\SendCampaignMessage;
use App\Models\Campaign;
use App\Models\CampaignMessage;

class CampaignDeliveryService
{
    /**
     * Queue the generated sequence beats on the configured cadence. The
     * status transition is an atomic compare-and-set, so a repeated schedule
     * call (e.g. a GenerateCampaign retry) never dispatches a duplicate send.
     */
    public function schedule(Campaign $campaign): void
    {
        if ($campaign->status !== Campaign::STATUS_GENERATED) {
            return;
        }

        $schedule = config('delivery.schedule', []);

        foreach ($campaign->messages()->orderBy('sequence_position')->get() as $message) {
            $queued = CampaignMessage::query()
                ->whereKey($message->id)
                ->where('status', CampaignMessage::STATUS_GENERATED)
                ->update([
                    'status' => CampaignMessage::STATUS_QUEUED,
                    'scheduled_at' => now(),
                ]);

            if ($queued === 0) {
                continue;
            }

            $delay = (int) ($schedule[$message->sequence_position] ?? 0);

            SendCampaignMessage::dispatch($message->id)->delay(now()->addSeconds($delay));
        }
    }
}
