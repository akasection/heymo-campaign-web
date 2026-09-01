<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaptureRequest;
use App\Jobs\GenerateCampaign;
use App\Models\Angle;
use App\Models\Brand;
use App\Models\Visitor;
use App\Support\LandingPageRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CaptureController extends Controller
{
    public function __construct(private LandingPageRegistry $registry) {}

    public function store(CaptureRequest $request): JsonResponse
    {
        $data = $request->validated();
        $brand = Brand::query()->active()->whereKey($data['brand_id'])->first();
        $definition = $this->registry->definition($data['landing_identifier']);
        $angle = $brand instanceof Brand && is_array($definition)
            ? $brand->angles()->active()->where('landing_identifier', $data['landing_identifier'])->first()
            : null;

        if (! $brand instanceof Brand || ! $angle instanceof Angle) {
            return response()->json([
                'message' => 'This campaign is not available right now.',
            ], 422);
        }

        $intentResponseId = null;

        DB::transaction(function () use ($data, $brand, $angle, &$intentResponseId): void {
            $visitor = Visitor::query()->updateOrCreate(
                [
                    'brand_id' => $brand->id,
                    'email' => $data['email'],
                ],
                [
                    'preferred_name' => $data['preferred_name'],
                ],
            );

            $intentResponse = $visitor->intentResponses()->create([
                'angle_id' => $angle->id,
                'landing_identifier' => $data['landing_identifier'],
                'age_group' => $data['age_group'],
                'sex' => $data['sex'],
                'sub_interest' => $data['sub_interest'],
                'trigger' => $data['trigger'],
                'concern' => $data['concern'],
                'captured_at' => now(),
                'fingerprint' => $data['fingerprint'] ?? null,
                'session_duration_seconds' => $data['session_duration_seconds'] ?? null,
                'attribution' => $data['attribution'] ?? null,
                'device' => $data['device'] ?? null,
            ]);

            $visitor->consentRecords()->create([
                'channel' => 'email',
                'email' => $data['email'],
                'source' => 'landing:'.$data['landing_identifier'],
                'policy_version' => config('capture.consent_policy_version'),
                'consented_at' => now(),
            ]);

            $intentResponseId = $intentResponse->id;
        });

        if ($intentResponseId !== null) {
            // Generation runs on the queue so provider latency never blocks the
            // capture request. The LLM is never reachable from the frontend.
            GenerateCampaign::dispatch($intentResponseId);
        }

        return response()->json([
            'status' => 'captured',
            'message' => 'Your answers are saved. We will use them to shape the next step.',
        ], 202);
    }
}
