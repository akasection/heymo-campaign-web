<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BrandController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Brand::class);

        $brands = $this->scopedBrands($request);

        if ($request->boolean('archived')) {
            $brands->onlyTrashed();
        }

        return BrandResource::collection(
            $brands->orderBy('name')->get(),
        );
    }

    public function show(Request $request, int $brandId): BrandResource
    {
        $brand = $this->findScopedBrand($request, $brandId, $request->boolean('with_trashed'));
        $this->authorize('view', $brand);

        return new BrandResource($brand);
    }

    public function store(BrandRequest $request): JsonResponse
    {
        $this->authorize('create', Brand::class);

        $brand = new Brand($request->validated());
        $brand->organization_id = $request->user()->organization_id;
        $brand->slug = $this->uniqueSlug((int) $brand->organization_id, $brand->name);
        $brand->save();

        return (new BrandResource($brand))
            ->additional(['message' => 'Brand created.'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(BrandRequest $request, int $brandId): BrandResource
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('update', $brand);
        $brand->fill($request->validated());
        $brand->save();

        return (new BrandResource($brand))->additional([
            'message' => 'Brand updated.',
        ]);
    }

    public function destroy(Request $request, int $brandId): JsonResponse
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('delete', $brand);
        $brand->delete();

        return response()->json(['message' => 'Brand archived.']);
    }

    public function restore(Request $request, int $brandId): BrandResource
    {
        $brand = $this->findScopedBrand($request, $brandId, true);
        $this->authorize('restore', $brand);
        $brand->restore();
        $brand->refresh();

        return (new BrandResource($brand))->additional([
            'message' => 'Brand restored.',
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Brand::class);

        return response()->json([
            'presets' => [
                'tone' => $this->optionList(config('brands.presets.tone', [])),
                'flow' => $this->optionList(config('brands.presets.flow', [])),
                'tense' => $this->optionList(config('brands.presets.tense', [])),
                'reading_level' => $this->optionList(config('brands.presets.reading_level', [])),
            ],
            'fonts' => $this->optionList(config('brands.fonts', [])),
            'defaults' => config('brands.defaults', []),
            'limits' => config('brands.limits', []),
        ]);
    }

    public function uploadLogo(Request $request, int $brandId): BrandResource
    {
        $brand = $this->findScopedBrand($request, $brandId);
        $this->authorize('update', $brand);
        $request->validate([
            'logo' => [
                'required',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048',
            ],
        ]);

        $disk = Storage::disk('public');
        $newPath = $request->file('logo')->store("brands/{$brand->id}", 'public');
        $oldPath = $brand->logo_path;

        try {
            $brand->forceFill(['logo_path' => $newPath])->save();
        } catch (Throwable $exception) {
            $disk->delete($newPath);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $newPath) {
            $disk->delete($oldPath);
        }

        return (new BrandResource($brand->refresh()))->additional([
            'message' => 'Brand logo updated.',
        ]);
    }

    private function scopedBrands(Request $request): Builder
    {
        return Brand::query()->forOrganization((int) $request->user()->organization_id);
    }

    private function findScopedBrand(Request $request, int $brandId, bool $withTrashed = false): Brand
    {
        $query = $this->scopedBrands($request);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->whereKey($brandId)->firstOrFail();
    }

    private function uniqueSlug(int $organizationId, string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'brand';
        $slug = $baseSlug;
        $suffix = 2;

        while (Brand::withTrashed()
            ->forOrganization($organizationId)
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, array<string, string>>  $options
     * @return array<int, array<string, string>>
     */
    private function optionList(array $options): array
    {
        $result = [];

        foreach ($options as $value => $option) {
            $result[] = array_merge([
                'value' => $value,
            ], $option);
        }

        return $result;
    }
}
