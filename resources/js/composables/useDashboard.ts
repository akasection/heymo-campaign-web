import { ref } from "vue";
import { ApiError, apiFetch } from "../lib/auth";
import type { DashboardResponse } from "../lib/dashboard";

export function useDashboard() {
  const data = ref<DashboardResponse["data"] | null>(null);
  const isLoading = ref(false);
  const errorMessage = ref("");

  async function load(brandId?: number): Promise<boolean> {
    isLoading.value = true;
    errorMessage.value = "";

    try {
      const query = brandId ? `?brand_id=${brandId}` : "";
      const response = await apiFetch<DashboardResponse>(`/api/dashboard${query}`);
      data.value = response.data;

      return true;
    } catch (error) {
      errorMessage.value = error instanceof ApiError ? error.message : "Could not load dashboard metrics.";

      return false;
    } finally {
      isLoading.value = false;
    }
  }

  return { data, isLoading, errorMessage, load };
}
