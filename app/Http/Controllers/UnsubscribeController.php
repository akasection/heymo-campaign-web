<?php

namespace App\Http\Controllers;

use App\Models\Suppression;
use App\Models\Visitor;
use Illuminate\Contracts\View\View;

class UnsubscribeController extends Controller
{
    public function __invoke(Visitor $visitor): View
    {
        // Idempotent: re-submitting the same signed link does not create
        // duplicate suppression rows. A suppression is durable and blocks
        // queued and future delivery.
        $visitor->suppressions()->firstOrCreate(
            ['channel' => Suppression::CHANNEL_EMAIL],
            [
                'reason' => Suppression::REASON_UNSUBSCRIBE,
                'source' => 'unsubscribe:link',
                'suppressed_at' => now(),
            ],
        );

        return view('unsubscribe', [
            'email' => $visitor->email,
        ]);
    }
}
