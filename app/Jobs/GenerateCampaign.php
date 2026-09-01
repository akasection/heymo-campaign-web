<?php

namespace App\Jobs;

use App\Models\IntentResponse;
use App\Services\CampaignDeliveryService;
use App\Services\CampaignGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 10;

    public function __construct(public int $intentResponseId) {}

    public function handle(CampaignGenerator $generator, CampaignDeliveryService $delivery): void
    {
        $intentResponse = IntentResponse::query()->find($this->intentResponseId);

        if (! $intentResponse instanceof IntentResponse) {
            // TODO(negative-flow): record a dropped-job event for observability.
            return;
        }

        $campaign = $generator->generate($intentResponse);

        // Generation succeeded; queue the sequence beats on the configured
        // cadence. Scheduling is idempotent (compare-and-set on message status),
        // so a job retry after generation never duplicates a send.
        $delivery->schedule($campaign);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GenerateCampaign job failed', [
            'intent_response_id' => $this->intentResponseId,
            'error' => $exception->getMessage(),
        ]);

        // TODO(negative-flow): persist a durable failure marker for provider or
        // network errors so the audit trail shows a failed attempt even when the
        // queue job exhausts its retries.
    }
}
