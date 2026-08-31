import { ref } from "vue";
import { ApiError, apiFetch } from "../lib/auth";
import type { Angle, AngleCollectionResponse, AngleOptions, AnglePayload, AngleResourceResponse } from "../lib/angles";

function messageFor(error: unknown): string {
  return error instanceof ApiError ? error.message : "We could not complete that angle action.";
}

export function useAngles() {
  const activeAngles = ref<Angle[]>([]);
  const archivedAngles = ref<Angle[]>([]);
  const selectedAngle = ref<Angle | null>(null);
  const isLoading = ref(false);
  const isMutating = ref(false);
  const errorMessage = ref("");

  function findLoadedAngle(id: number): Angle | null {
    return [...activeAngles.value, ...archivedAngles.value].find(angle => angle.id === id) ?? null;
  }

  async function loadAngles(brandId: number): Promise<boolean> {
    const selectedId = selectedAngle.value?.id;
    isLoading.value = true;
    errorMessage.value = "";

    try {
      const [activeResponse, archivedResponse] = await Promise.all([
        apiFetch<AngleCollectionResponse>(`/api/brands/${brandId}/angles`),
        apiFetch<AngleCollectionResponse>(`/api/brands/${brandId}/angles?archived=1`),
      ]);

      activeAngles.value = activeResponse.data;
      archivedAngles.value = archivedResponse.data;
      selectedAngle.value = selectedId ? findLoadedAngle(selectedId) : (activeAngles.value[0] ?? null);

      return true;
    } catch (error) {
      errorMessage.value = messageFor(error);
      activeAngles.value = [];
      archivedAngles.value = [];
      selectedAngle.value = null;
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  function selectAngle(angle: Angle | null): void {
    selectedAngle.value = angle;
  }

  async function loadOptions(brandId: number): Promise<AngleOptions> {
    return apiFetch<AngleOptions>(`/api/brands/${brandId}/angles/options`);
  }

  async function saveAngle(brandId: number, payload: AnglePayload, angleId?: number): Promise<Angle> {
    return mutate(async () => {
      const endpoint = angleId ? `/api/brands/${brandId}/angles/${angleId}` : `/api/brands/${brandId}/angles`;
      const response = await apiFetch<AngleResourceResponse>(endpoint, {
        method: angleId ? "PUT" : "POST",
        body: JSON.stringify(payload),
      });

      await loadAngles(brandId);
      const savedAngle = findLoadedAngle(response.data.id) ?? response.data;
      selectedAngle.value = savedAngle;

      return savedAngle;
    });
  }

  async function archiveAngle(brandId: number, angleId: number): Promise<void> {
    await mutate(async () => {
      await apiFetch<{ message: string }>(`/api/brands/${brandId}/angles/${angleId}`, { method: "DELETE" });
      await loadAngles(brandId);
      selectedAngle.value = activeAngles.value[0] ?? null;
    });
  }

  async function restoreAngle(brandId: number, angleId: number): Promise<void> {
    await mutate(async () => {
      const response = await apiFetch<AngleResourceResponse>(`/api/brands/${brandId}/angles/${angleId}/restore`, { method: "POST" });
      await loadAngles(brandId);
      selectedAngle.value = findLoadedAngle(response.data.id) ?? response.data;
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
    activeAngles,
    archivedAngles,
    selectedAngle,
    isLoading,
    isMutating,
    errorMessage,
    loadAngles,
    loadOptions,
    selectAngle,
    saveAngle,
    archiveAngle,
    restoreAngle,
  };
}
