<?php

namespace App\Http\Controllers;

use App\Http\Requests\AngleRequest;
use App\Http\Resources\AngleResource;
use App\Models\Angle;
use App\Models\Brand;
use App\Support\LandingPageRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AngleController extends Controller
{
    public function __construct(private LandingPageRegistry $registry) {}

    public function index(Request $request, int $brandId): AnonymousResourceCollection
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('viewAny', [Angle::class, $brand]);

        $angles = $brand->angles();

        if ($request->boolean('archived')) {
            $angles->onlyTrashed();
        }

        return AngleResource::collection(
            $angles->with('brand')->orderBy('name')->get(),
        );
    }

    public function show(Request $request, int $brandId, int $angleId): AngleResource
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $angle = $this->findScopedAngle($brand, $angleId, $request->boolean('with_trashed'));
        $this->authorize('view', $angle);

        return new AngleResource($angle->load('brand'));
    }

    public function store(AngleRequest $request, int $brandId): JsonResponse
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('create', [Angle::class, $brand]);

        $angle = new Angle($request->validated());
        $angle->brand_id = $brand->id;
        $angle->slug = $this->uniqueSlug($brand, $angle->name);
        $angle->save();

        return (new AngleResource($angle->load('brand')))
            ->additional(['message' => 'Angle created.'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(AngleRequest $request, int $brandId, int $angleId): AngleResource
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $angle = $this->findScopedAngle($brand, $angleId);
        $this->authorize('update', $angle);
        $angle->fill($request->validated());
        $angle->save();

        return (new AngleResource($angle->load('brand')))->additional([
            'message' => 'Angle updated.',
        ]);
    }

    public function destroy(Request $request, int $brandId, int $angleId): JsonResponse
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $angle = $this->findScopedAngle($brand, $angleId);
        $this->authorize('delete', $angle);
        $angle->delete();

        return response()->json(['message' => 'Angle archived.']);
    }

    public function restore(Request $request, int $brandId, int $angleId): AngleResource
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $angle = $this->findScopedAngle($brand, $angleId, true);
        $this->authorize('restore', $angle);

        if ($angle->landing_identifier && $brand->angles()
            ->active()
            ->where('landing_identifier', $angle->landing_identifier)
            ->where('id', '!=', $angle->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'landing_identifier' => 'This landing page is already assigned to another active angle.',
            ]);
        }

        $angle->restore();
        $angle->refresh();

        return (new AngleResource($angle->load('brand')))->additional([
            'message' => 'Angle restored.',
        ]);
    }

    public function options(Request $request, int $brandId): JsonResponse
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('viewAny', [Angle::class, $brand]);

        $tones = [];
        foreach (config('angles.tones', []) as $value => $tone) {
            $tones[] = array_merge(['value' => $value], $tone);
        }

        $assignedAngles = $brand->angles()
            ->active()
            ->whereNotNull('landing_identifier')
            ->get(['id', 'name', 'landing_identifier'])
            ->keyBy('landing_identifier');
        $landingPages = [];

        foreach ($this->registry->identifiers() as $identifier) {
            $definition = $this->registry->definition($identifier) ?? [];
            $assignedAngle = $assignedAngles->get($identifier);
            $landingPages[] = [
                'value' => $identifier,
                'label' => $definition['title'] ?? Str::headline($identifier),
                'description' => $definition['eyebrow'] ?? null,
                'url' => route('landing.page', [
                    'brandId' => $brand->id,
                    'landingIdentifier' => $identifier,
                ]),
                'assigned_angle_id' => $assignedAngle?->id,
                'assigned_angle_name' => $assignedAngle?->name,
            ];
        }

        return response()->json([
            'tones' => $tones,
            'landing_pages' => $landingPages,
            'defaults' => config('angles.defaults', []),
            'limits' => config('angles.limits', []),
        ]);
    }

    private function findScopedBrand(Request $request, int $brandId): Brand
    {
        return Brand::query()
            ->forOrganization((int) $request->user()->organization_id)
            ->whereKey($brandId)
            ->firstOrFail();
    }

    private function findScopedAngle(Brand $brand, int $angleId, bool $withTrashed = false): Angle
    {
        $query = $brand->angles();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->whereKey($angleId)->firstOrFail();
    }

    private function uniqueSlug(Brand $brand, string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'angle';
        $slug = $baseSlug;
        $suffix = 2;

        while (Angle::withTrashed()
            ->forBrand((int) $brand->id)
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
