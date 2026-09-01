import { computed, ref } from "vue";
import { ApiError, apiFetch } from "../lib/auth";
import type { AuditCollectionResponse, AuditRecord } from "../lib/audit";

export function useAuditTrail() {
  const records = ref<AuditRecord[]>([]);
  const meta = ref<AuditCollectionResponse["meta"] | null>(null);
  const page = ref(1);
  const perPage = ref(50);
  const brandId = ref<number | null>(null);
  const angleId = ref<number | null>(null);
  const search = ref("");
  const isLoading = ref(false);
  const errorMessage = ref("");

  const hasPagination = computed(() => (meta.value?.last_page ?? 1) > 1);

  async function load(): Promise<boolean> {
    isLoading.value = true;
    errorMessage.value = "";

    const params = new URLSearchParams();
    params.set("page", String(page.value));
    params.set("per_page", String(perPage.value));

    if (brandId.value) {
      params.set("brand_id", String(brandId.value));
    }

    if (angleId.value) {
      params.set("angle_id", String(angleId.value));
    }

    if (search.value.trim()) {
      params.set("q", search.value.trim());
    }

    try {
      const response = await apiFetch<AuditCollectionResponse>(`/api/audit?${params.toString()}`);
      records.value = response.data;
      meta.value = response.meta;

      return true;
    } catch (error) {
      errorMessage.value = error instanceof ApiError ? error.message : "Could not load the audit trail.";

      return false;
    } finally {
      isLoading.value = false;
    }
  }

  function goToPage(target: number): void {
    if (!meta.value) {
      return;
    }

    const next = Math.min(Math.max(target, 1), meta.value.last_page);

    if (next !== page.value) {
      page.value = next;
      void load();
    }
  }

  function resetAndReload(): void {
    page.value = 1;
    void load();
  }

  return {
    records,
    meta,
    page,
    perPage,
    brandId,
    angleId,
    search,
    isLoading,
    errorMessage,
    hasPagination,
    load,
    goToPage,
    resetAndReload,
  };
}
