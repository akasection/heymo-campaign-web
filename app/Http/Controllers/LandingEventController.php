<?php

namespace App\Http\Controllers;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\LandingEvent;
use App\Support\CaptureMetadata;
use App\Support\LandingPageRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandingEventController extends Controller
{
    public function __construct(private LandingPageRegistry $registry) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fingerprint' => ['required', 'uuid'],
            'brand_id' => ['required', 'integer', 'min:1'],
            'landing_identifier' => ['required', 'string', 'max:80', Rule::in($this->registry->identifiers())],
            'attribution' => ['nullable', 'array'],
            'device' => ['nullable', 'array'],
        ]);

        $brand = Brand::query()->active()->whereKey($data['brand_id'])->first();
        $definition = $this->registry->definition($data['landing_identifier']);
        $angle = $brand instanceof Brand && is_array($definition)
            ? $brand->angles()->active()->where('landing_identifier', $data['landing_identifier'])->first()
            : null;

        LandingEvent::query()->create([
            'fingerprint' => $data['fingerprint'],
            'brand_id' => $brand?->id,
            'landing_identifier' => $data['landing_identifier'],
            'angle_id' => $angle instanceof Angle ? $angle->id : null,
            'attribution' => CaptureMetadata::sanitizeAttribution($data['attribution'] ?? null),
            'device' => CaptureMetadata::sanitizeDevice($data['device'] ?? null),
            'landed_at' => now(),
        ]);

        return response()->json(['status' => 'recorded'], 202);
    }
}
