<?php

namespace App\Http\Controllers;

use App\Models\Angle;
use App\Models\Brand;
use App\Support\LandingPageRegistry;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function __construct(private LandingPageRegistry $registry) {}

    public function show(int $brandId, string $landingIdentifier): Response
    {
        $context = $this->context($brandId, $landingIdentifier);

        if ($context === null) {
            return $this->unavailable();
        }

        return response()->view($context['definition']['view'], $context);
    }

    public function quiz(int $brandId, string $landingIdentifier): Response
    {
        $context = $this->context($brandId, $landingIdentifier);

        if ($context === null) {
            return $this->unavailable();
        }

        return response()->view('LandingPages.quiz', $context);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function context(int $brandId, string $landingIdentifier): ?array
    {
        $definition = $this->registry->definition($landingIdentifier);
        $brand = Brand::query()->active()->whereKey($brandId)->first();

        if ($definition === null || ! $brand) {
            return null;
        }

        $angle = $brand->angles()
            ->active()
            ->where('landing_identifier', $landingIdentifier)
            ->first();

        if (! $angle instanceof Angle) {
            return null;
        }

        return [
            'brandPresentation' => $this->brandPresentation($brand),
            'definition' => $definition,
            'landingIdentifier' => $landingIdentifier,
            'angleSlug' => $angle->slug,
            'brandId' => $brand->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function brandPresentation(Brand $brand): array
    {
        $fonts = config('brands.fonts', []);
        $headingFont = $fonts[$brand->heading_font] ?? [];
        $bodyFont = $fonts[$brand->body_font] ?? [];

        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'primary_color' => $brand->primary_color,
            'secondary_color' => $brand->secondary_color,
            'heading_font' => $headingFont['stack'] ?? 'sans-serif',
            'body_font' => $bodyFont['stack'] ?? 'sans-serif',
            'logo_url' => $brand->logo_path ? Storage::disk('public')->url($brand->logo_path) : null,
        ];
    }

    private function unavailable(): Response
    {
        return response()->view('LandingPages.unavailable', [
            'message' => 'This campaign is not available right now.',
        ], 404);
    }
}
