<?php

namespace App\Http\Controllers;

use App\Models\CampaignMessage;
use App\Models\EngagementEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OpenTrackingController extends Controller
{
    public function __invoke(Request $request, string $openToken): Response
    {
        $message = CampaignMessage::query()->where('open_token', $openToken)->first();

        if ($message instanceof CampaignMessage && $message->opened_at === null) {
            $message->forceFill(['opened_at' => now()])->save();

            $message->engagementEvents()->create([
                'type' => EngagementEvent::TYPE_OPEN,
                'occurred_at' => now(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response($this->transparentPixel(), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function transparentPixel(): string
    {
        return base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', true);
    }
}
