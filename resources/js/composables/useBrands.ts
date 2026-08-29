import { ref } from "vue";
import { ApiError, apiFetch } from "../lib/auth";
import type { Brand, BrandCollectionResponse, BrandPayload, BrandResourceResponse } from "../lib/brands";

function messageFor(error: unknown): string {
  return error instanceof ApiError ? error.message : "We could not complete that brand action.";
}

export function useBrands() {
  const activeBrands = ref<Brand[]>([]);
  const archivedBrands = ref<Brand[]>([]);
  const selectedBrand = ref<Brand | null>(null);
  const isLoading = ref(false);
  const isMutating = ref(false);
  const errorMessage = ref("");

  function findLoadedBrand(id: number): Brand | null {
    return [...activeBrands.value, ...archivedBrands.value].find(brand => brand.id === id) ?? null;
  }

  async function loadBrands(): Promise<boolean> {
    const selectedId = selectedBrand.value?.id;
    isLoading.value = true;
    errorMessage.value = "";

    try {
      const [activeResponse, archivedResponse] = await Promise.all([
        apiFetch<BrandCollectionResponse>("/api/brands"),
        apiFetch<BrandCollectionResponse>("/api/brands?archived=1"),
      ]);

      activeBrands.value = activeResponse.data;
      archivedBrands.value = archivedResponse.data;
      selectedBrand.value = selectedId ? findLoadedBrand(selectedId) : (activeBrands.value[0] ?? null);

      return true;
    } catch (error) {
      errorMessage.value = messageFor(error);
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  function selectBrand(brand: Brand | null): void {
    selectedBrand.value = brand;
  }

  async function saveBrand(payload: BrandPayload, brandId?: number): Promise<Brand> {
    isMutating.value = true;
    errorMessage.value = "";

    try {
      const endpoint = brandId ? `/api/brands/${brandId}` : "/api/brands";
      const response = await apiFetch<BrandResourceResponse>(endpoint, {
        method: brandId ? "PUT" : "POST",
        body: JSON.stringify(payload),
      });
      const savedBrand = response.data;

      await loadBrands();
      const refreshedBrand = findLoadedBrand(savedBrand.id) ?? savedBrand;
      selectedBrand.value = refreshedBrand;

      return refreshedBrand;
    } catch (error) {
      errorMessage.value = messageFor(error);
      throw error;
    } finally {
      isMutating.value = false;
    }
  }

  async function archiveBrand(brandId: number): Promise<void> {
    await mutate(async () => {
      await apiFetch<{ message: string }>(`/api/brands/${brandId}`, {
        method: "DELETE",
      });
      await loadBrands();

      if (selectedBrand.value?.id === brandId) {
        selectedBrand.value = activeBrands.value[0] ?? null;
      }
    });
  }

  async function restoreBrand(brandId: number): Promise<void> {
    await mutate(async () => {
      const response = await apiFetch<BrandResourceResponse>(`/api/brands/${brandId}/restore`, { method: "POST" });
      await loadBrands();
      selectedBrand.value = findLoadedBrand(response.data.id) ?? response.data;
    });
  }

  async function uploadLogo(brandId: number, file: File): Promise<Brand> {
    return mutate(async () => {
      const body = new FormData();
      body.append("logo", file);
      const response = await apiFetch<BrandResourceResponse>(`/api/brands/${brandId}/logo`, {
        method: "POST",
        body,
      });

      await loadBrands();
      const refreshedBrand = findLoadedBrand(response.data.id) ?? response.data;
      selectedBrand.value = refreshedBrand;

      return refreshedBrand;
    });
  }

  async function mutate<T>(action: () => Promise<T>): Promise<T> {
    isMutating.value = true;
    errorMessage.value = "";

    try {
      return await action();
    } catch (error) {
      errorMessage.value = messageFor(error);
      throw error;
    } finally {
      isMutating.value = false;
    }
  }

  return {
    activeBrands,
    archivedBrands,
    selectedBrand,
    isLoading,
    isMutating,
    errorMessage,
    loadBrands,
    selectBrand,
    saveBrand,
    archiveBrand,
    restoreBrand,
    uploadLogo,
  };
}
