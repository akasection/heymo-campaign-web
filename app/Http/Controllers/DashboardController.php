<?php

namespace App\Http\Controllers;

use App\Services\CampaignMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private CampaignMetricsService $metrics) {}

    public function index(Request $request): JsonResponse
    {
        $organizationId = (int) $request->user()->organization_id;

        if ($organizationId === 0) {
            return response()->json(['message' => 'This workspace has no organization.'], 403);
        }

        $brandId = $request->integer('brand_id') ?: null;

        return response()->json([
            'data' => $this->metrics->for($organizationId, $brandId),
        ]);
    }
}
