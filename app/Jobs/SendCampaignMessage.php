<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CampaignMessage;
use App\Models\DeliveryEvent;
use App\Services\CampaignMailComposer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class SendCampaignMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 10;

    public function __construct(public int $messageId) {}

    public function handle(CampaignMailComposer $composer): void
    {
        $message = CampaignMessage::query()
            ->with(['campaign.visitor', 'campaign.angle', 'campaign.brand'])
            ->find($this->messageId);

        if (! $message instanceof CampaignMessage) {
            return;
        }

        // Idempotency: a terminal decision is never re-made by a duplicate job.
        if (in_array($message->status, [
            CampaignMessage::STATUS_SENT,
            CampaignMessage::STATUS_SKIPPED,
            CampaignMessage::STATUS_FAILED,
        ], true)) {
            return;
        }

        $campaign = $message->campaign;
        $visitor = $campaign?->visitor;
        $angle = $campaign?->angle;
        $brand = $campaign?->brand;
        $attemptNumber = $this->attempts();

        if ($visitor === null || $angle === null || $brand === null) {
            $this->skip($message, $attemptNumber, 'Campaign delivery inputs are incomplete.');

            return;
        }

        if ($visitor->isSuppressed()) {
            $this->skip($message, $attemptNumber, 'Visitor is suppressed for email.', $visitor->email);

            return;
        }

        if (! $visitor->hasActiveEmailConsent()) {
            $this->skip($message, $attemptNumber, 'Visitor has no active email consent.', $visitor->email);

            return;
        }

        $message->deliveryEvents()->create([
            'attempt_number' => $attemptNumber,
            'status' => DeliveryEvent::STATUS_ATTEMPTED,
            'to_address' => $visitor->email,
        ]);

        try {
            $unsubscribeUrl = URL::temporarySignedRoute(
                'unsubscribe',
                now()->addDays((int) config('delivery.unsubscribe.expires_days', 30)),
                ['visitor' => $visitor->id],
            );

            $composed = $composer->compose($message, $campaign, $brand, $angle, $visitor, $unsubscribeUrl);

            Mail::to($visitor->email)->send(new CampaignMail($composed['subject'], $composed['html']));

            $message->deliveryEvents()->create([
                'attempt_number' => $attemptNumber,
                'status' => DeliveryEvent::STATUS_SENT,
                'to_address' => $visitor->email,
            ]);

            $message->update([
                'status' => CampaignMessage::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $message->deliveryEvents()->create([
                'attempt_number' => $attemptNumber,
                'status' => DeliveryEvent::STATUS_FAILED,
                'to_address' => $visitor->email,
                'error_message' => Str::limit($exception->getMessage(), 500),
                'metadata' => ['exception' => $exception::class],
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $message = CampaignMessage::query()->find($this->messageId);

        if ($message instanceof CampaignMessage) {
            $message->update(['status' => CampaignMessage::STATUS_FAILED]);
        }

        Log::error('SendCampaignMessage job failed', [
            'campaign_message_id' => $this->messageId,
            'error' => $exception->getMessage(),
        ]);
    }

    private function skip(CampaignMessage $message, int $attemptNumber, string $reason, ?string $toAddress = null): void
    {
        $message->deliveryEvents()->create([
            'attempt_number' => $attemptNumber,
            'status' => DeliveryEvent::STATUS_SKIPPED,
            'to_address' => $toAddress,
            'error_message' => $reason,
        ]);

        $message->update(['status' => CampaignMessage::STATUS_SKIPPED]);
    }
}
