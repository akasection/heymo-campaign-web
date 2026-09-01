<template>
  <section class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex justify-end">
      <button v-if="canManage && selectedBrandId" class="btn btn-primary" @click="openCreate">
        <Plus :size="18" weight="bold" aria-hidden="true" />
        New angle
      </button>
    </div>

    <div v-if="!canManage" class="alert alert-soft alert-warning mx-auto max-w-[1600px] p-6">
      <div class="flex items-start gap-3">
        <WarningCircle :size="22" weight="fill" class="mt-0.5 shrink-0 text-heymo-warning" aria-hidden="true" />
        <div>
          <h2 class="text-sm font-bold text-heymo-navy">Angle settings are restricted</h2>
          <p class="mt-1 text-sm leading-6 text-heymo-muted">An administrator role is required to view and manage campaign strategy.</p>
        </div>
      </div>
    </div>

    <div v-else class="grid gap-5 xl:grid-cols-[minmax(19rem,0.8fr)_minmax(0,1.4fr)]">
      <Panel title="Angle library" description="Active strategies are available to future campaign generation.">
        <template #action>
          <span class="text-[11px] font-bold text-heymo-muted">{{ visibleAngles.length }} shown</span>
        </template>

        <label for="angle-brand" class="mb-2 block text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Brand</label>
        <select
          id="angle-brand"
          class="select select-md mb-4 w-full"
          :value="selectedBrandId ?? ''"
          :disabled="isBrandsLoading"
          @change="changeBrand"
        >
          <option v-if="!activeBrands.length" value="">No active brands</option>
          <option v-for="brand in activeBrands" :key="brand.id" :value="brand.id">
            {{ brand.name }}
          </option>
        </select>

        <div class="tabs tabs-box mb-4 w-full" role="tablist" aria-label="Angle status">
          <button
            type="button"
            class="tab grow text-xs font-bold"
            :class="activeTab === 'active' ? 'tab-active' : ''"
            role="tab"
            :aria-selected="activeTab === 'active'"
            @click="setActiveTab('active')"
          >
            Active
            <span class="ml-1 tabular-nums">{{ activeAngles.length }}</span>
          </button>
          <button
            type="button"
            class="tab grow text-xs font-bold"
            :class="activeTab === 'archived' ? 'tab-active' : ''"
            role="tab"
            :aria-selected="activeTab === 'archived'"
            @click="setActiveTab('archived')"
          >
            Archived
            <span class="ml-1 tabular-nums">{{ archivedAngles.length }}</span>
          </button>
        </div>

        <div v-if="errorMessage" class="alert alert-soft alert-error mb-4 px-3 py-2.5 text-xs font-semibold leading-5">
          {{ errorMessage }}
        </div>
        <div v-if="isBrandsLoading || isAnglesLoading" class="space-y-3" aria-label="Loading angles">
          <div v-for="placeholder in 3" :key="placeholder" class="skeleton h-24 w-full"></div>
        </div>
        <div v-else-if="!visibleAngles.length" class="rounded-md border border-dashed border-heymo-line px-4 py-10 text-center">
          <Target :size="28" class="mx-auto text-heymo-muted" aria-hidden="true" />
          <p class="mt-3 text-sm font-bold text-heymo-navy">
            {{ activeTab === "active" ? "No active angles yet" : "No archived angles" }}
          </p>
          <p class="mt-1 text-xs leading-5 text-heymo-muted">
            {{
              activeTab === "active" ? "Create a strategy to give a future campaign a clear point of view." : "Archived strategies will appear here."
            }}
          </p>
        </div>
        <ul v-else class="space-y-2" role="list">
          <li v-for="angle in visibleAngles" :key="angle.id">
            <button
              type="button"
              class="w-full rounded-md border px-3 py-3 text-left transition focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
              :class="
                selectedAngle?.id === angle.id
                  ? 'border-heymo-navy bg-heymo-sky'
                  : 'border-heymo-line bg-white hover:border-heymo-navy/40 hover:bg-slate-50'
              "
              @click="selectVisibleAngle(angle)"
            >
              <span class="flex items-start gap-3">
                <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-md bg-heymo-navy text-white">
                  <Target :size="18" weight="bold" aria-hidden="true" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-sm font-extrabold text-heymo-navy">{{ angle.name }}</span>
                  <span class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-heymo-muted">
                    <span class="badge badge-soft badge-info badge-sm font-bold">{{ angle.tone_label }}</span>
                    <span :class="angle.proof_state === 'configured' ? 'text-heymo-positive' : 'text-heymo-warning'">
                      {{ angle.proof_state === "configured" ? "Proof configured" : "Proof missing" }}
                    </span>
                    <span class="truncate" :class="angle.landing_page ? 'text-heymo-navy' : 'text-heymo-warning'">
                      {{ angle.landing_page?.label ?? "No landing page" }}
                    </span>
                  </span>
                </span>
                <CaretRight :size="16" class="mt-1 shrink-0 text-heymo-muted" aria-hidden="true" />
              </span>
            </button>
          </li>
        </ul>
      </Panel>

      <div class="min-w-0">
        <Panel v-if="showAngleNotFound" title="Angle not found" description="This angle is unavailable in the selected brand.">
          <div class="grid min-h-80 place-items-center rounded-md border border-dashed border-heymo-line bg-slate-50 px-6 text-center">
            <WarningCircle :size="34" class="text-heymo-muted" aria-hidden="true" />
            <p class="mt-3 text-sm font-bold text-heymo-navy">That angle is not available</p>
            <p class="mt-1 max-w-sm text-xs leading-5 text-heymo-muted">Choose an active strategy from the library to continue.</p>
          </div>
        </Panel>
        <AngleForm
          v-else-if="isEditorOpen && selectedBrandId"
          :key="editingAngle?.id ?? 'new-angle'"
          :angle="editingAngle"
          :load-options="loadAngleOptions"
          :save-angle="saveSelectedAngle"
          @cancel="closeEditor"
          @saved="handleSaved"
        />
        <Panel v-else-if="selectedAngle" :title="selectedAngle.name" description="Saved strategy and deterministic campaign inputs.">
          <template #action>
            <div class="flex items-center gap-2">
              <button v-if="activeTab === 'active'" class="btn btn-outline btn-sm" @click="openEdit(selectedAngle)">
                <PencilSimple :size="16" weight="bold" aria-hidden="true" />
                Edit
              </button>
              <button
                v-if="activeTab === 'active'"
                class="btn btn-square btn-ghost btn-sm border border-base-300"
                aria-label="Archive angle"
                title="Archive angle"
                @click="archiveSelected"
              >
                <Archive :size="17" weight="bold" aria-hidden="true" />
              </button>
              <button v-else class="btn btn-outline btn-sm" @click="restoreSelected">
                <ArrowCounterClockwise :size="16" weight="bold" aria-hidden="true" />
                Restore
              </button>
            </div>
          </template>

          <div class="space-y-6">
            <div class="flex flex-wrap items-center gap-2 border-b border-heymo-line pb-4">
              <span class="badge badge-neutral font-bold">{{ selectedAngle.brand.name }}</span>
              <span class="badge badge-soft badge-info font-bold">{{ selectedAngle.tone_label }}</span>
              <span class="font-mono text-[11px] text-heymo-muted">/{{ selectedAngle.slug }}</span>
            </div>

            <div class="border-b border-heymo-line pb-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h3 class="text-sm font-bold text-heymo-navy">Landing page</h3>
                  <p class="mt-1 text-xs text-heymo-muted">The hand-authored page currently assigned to this Angle.</p>
                </div>
                <span class="badge badge-soft font-bold" :class="selectedAngle.landing_page ? 'badge-success' : 'badge-warning'">
                  {{ selectedAngle.landing_page?.label ?? "Unassigned" }}
                </span>
              </div>
              <div v-if="selectedAngle.landing_page" class="mt-3 flex min-w-0 flex-col gap-2 sm:flex-row">
                <input
                  :value="selectedAngle.landing_page.url"
                  type="url"
                  readonly
                  aria-label="Landing page URL"
                  class="input input-sm min-w-0 flex-1 font-mono text-xs"
                />
                <button type="button" class="btn btn-outline btn-sm shrink-0" @click="copyLandingUrl(selectedAngle)">
                  <Check v-if="copiedAngleId === selectedAngle.id" :size="16" weight="bold" aria-hidden="true" />
                  <Copy v-else :size="16" weight="bold" aria-hidden="true" />
                  {{ copiedAngleId === selectedAngle.id ? "Copied" : "Copy URL" }}
                </button>
              </div>
              <p v-else class="mt-3 rounded-md bg-slate-50 px-3 py-2.5 text-xs leading-5 text-heymo-muted">
                No static landing page is assigned. Edit this Angle to select one of the available page variants.
              </p>
              <p v-if="copyError" class="mt-2 text-xs font-semibold text-heymo-red" role="status">
                {{ copyError }}
              </p>
            </div>

            <div>
              <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-heymo-navy">Strategy context</h3>
                <span class="text-[11px] font-bold text-heymo-muted">{{
                  selectedAngle.updated_at ? `Updated ${formatDate(selectedAngle.updated_at)}` : "Saved strategy"
                }}</span>
              </div>
              <dl class="grid gap-3 md:grid-cols-2">
                <div v-for="field in contextFields" :key="field.label" class="rounded-md bg-base-200 px-3 py-3">
                  <dt class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">
                    {{ field.label }}
                  </dt>
                  <dd class="mt-1 text-xs leading-5 text-heymo-ink">
                    {{ field.value }}
                  </dd>
                </div>
              </dl>
            </div>

            <div class="border-t border-heymo-line pt-5">
              <h3 class="text-sm font-bold text-heymo-navy">Promise and objection</h3>
              <div class="mt-3 grid gap-3 md:grid-cols-2">
                <div class="rounded-md border border-heymo-line px-3 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Single promise</p>
                  <p class="mt-1 text-xs leading-5 text-heymo-ink">
                    {{ selectedAngle.single_promise }}
                  </p>
                </div>
                <div class="rounded-md border border-heymo-line px-3 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Objection</p>
                  <p class="mt-1 text-xs leading-5 text-heymo-ink">
                    {{ selectedAngle.objection }}
                  </p>
                </div>
              </div>
            </div>

            <div class="border-t border-heymo-line pt-5">
              <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-heymo-navy">Deterministic path</h3>
                <span class="badge badge-soft" :class="selectedAngle.proof_state === 'configured' ? 'badge-success' : 'badge-warning'">{{
                  selectedAngle.proof_state
                }}</span>
              </div>
              <div class="space-y-3">
                <div class="rounded-md bg-slate-50 px-3 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Proof block</p>
                  <p class="mt-1 text-xs leading-5 text-heymo-ink">
                    {{ selectedAngle.proof }}
                  </p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                  <div class="rounded-md bg-slate-50 px-3 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Offer</p>
                    <p class="mt-1 text-xs leading-5 text-heymo-ink">
                      {{ selectedAngle.offer }}
                    </p>
                  </div>
                  <div class="rounded-md bg-slate-50 px-3 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Next step</p>
                    <p class="mt-1 text-xs leading-5 text-heymo-ink">
                      {{ selectedAngle.next_step }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Panel>
        <Panel v-else title="Select an angle" description="Choose a saved strategy to inspect its campaign theory.">
          <div class="grid min-h-80 place-items-center rounded-md border border-dashed border-heymo-line bg-slate-50 px-6 text-center">
            <Target :size="34" class="text-heymo-muted" aria-hidden="true" />
            <p class="mt-3 text-sm font-bold text-heymo-navy">Your strategy library is ready</p>
            <p class="mt-1 max-w-sm text-xs leading-5 text-heymo-muted">Create an angle or select one from the library to review its inputs.</p>
          </div>
        </Panel>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import {
  PhArchive as Archive,
  PhArrowCounterClockwise as ArrowCounterClockwise,
  PhCaretRight as CaretRight,
  PhCheck as Check,
  PhCopy as Copy,
  PhPencilSimple as PencilSimple,
  PhPlus as Plus,
  PhTarget as Target,
  PhWarningCircle as WarningCircle,
} from "@phosphor-icons/vue";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import Panel from "../../components/Backoffice/Panel.vue";
import { useAngles } from "../../composables/useAngles";
import { useBrands } from "../../composables/useBrands";
import AngleForm from "./AngleForm.vue";
import type { Angle, AngleOptions, AnglePayload } from "../../lib/angles";

const props = defineProps<{ canManage: boolean }>();
const route = useRoute();
const router = useRouter();
const { activeBrands, isLoading: isBrandsLoading, loadBrands } = useBrands();
const {
  activeAngles,
  archivedAngles,
  selectedAngle,
  isLoading: isAnglesLoading,
  errorMessage,
  loadAngles,
  loadOptions,
  selectAngle,
  saveAngle,
  archiveAngle,
  restoreAngle,
} = useAngles();

const activeTab = ref<"active" | "archived">("active");
const selectedBrandId = ref<number | null>(routeNumber(route.params.brandId));
const isEditorOpen = ref(false);
const editingAngle = ref<Angle | null>(null);
const copiedAngleId = ref<number | null>(null);
const copyError = ref("");

const hasAngleRoute = computed(() => route.name === "admin.angle");
const routeAngleId = computed(() => routeNumber(route.params.angleId));
const routeBrandId = computed(() => routeNumber(route.params.brandId));
const visibleAngles = computed(() => (activeTab.value === "active" ? activeAngles.value : archivedAngles.value));
const showAngleNotFound = computed(() => hasAngleRoute.value && !isAnglesLoading.value && !errorMessage.value && !selectedAngle.value);
const contextFields = computed(() => {
  if (!selectedAngle.value) {
    return [];
  }

  return [
    { label: "Audience", value: selectedAngle.value.audience },
    { label: "Trigger moment", value: selectedAngle.value.trigger_moment },
    { label: "Primary job", value: selectedAngle.value.primary_job },
    { label: "Tension", value: selectedAngle.value.tension },
    { label: "Desired outcome", value: selectedAngle.value.desired_outcome },
  ];
});

function routeNumber(value: unknown): number | null {
  if (typeof value !== "string" || !/^[1-9]\d*$/.test(value)) {
    return null;
  }

  const number = Number(value);
  return Number.isSafeInteger(number) ? number : null;
}

function findLoadedAngle(id: number): Angle | null {
  return [...activeAngles.value, ...archivedAngles.value].find(angle => angle.id === id) ?? null;
}

function syncBrand(): void {
  if (!activeBrands.value.length) {
    selectedBrandId.value = null;
    return;
  }

  const requestedBrandId = routeBrandId.value;
  if (requestedBrandId && activeBrands.value.some(brand => brand.id === requestedBrandId)) {
    selectedBrandId.value = requestedBrandId;
    return;
  }

  if (!selectedBrandId.value || !activeBrands.value.some(brand => brand.id === selectedBrandId.value)) {
    selectedBrandId.value = activeBrands.value[0].id;
  }
}

function syncAngle(): void {
  if (routeAngleId.value) {
    const routedAngle = findLoadedAngle(routeAngleId.value);
    if (routedAngle) {
      selectAngle(routedAngle);
      activeTab.value = routedAngle.archived_at ? "archived" : "active";
      return;
    }
  }

  const availableAngles = activeTab.value === "active" ? activeAngles.value : archivedAngles.value;
  if (!selectedAngle.value || !availableAngles.some(angle => angle.id === selectedAngle.value?.id)) {
    selectAngle(availableAngles[0] ?? null);
  }
}

async function loadSelectedBrand(): Promise<void> {
  if (!selectedBrandId.value) {
    return;
  }

  selectAngle(null);
  await loadAngles(selectedBrandId.value);
  syncAngle();
}

function changeBrand(event: Event): void {
  if (!(event.target instanceof HTMLSelectElement)) {
    return;
  }

  const brandId = Number(event.target.value);
  if (!Number.isSafeInteger(brandId) || brandId < 1) {
    return;
  }

  selectedBrandId.value = brandId;
  activeTab.value = "active";
  isEditorOpen.value = false;
  editingAngle.value = null;
  router.push({ name: "admin.angles", params: { brandId: String(brandId) } });
}

function setActiveTab(tab: "active" | "archived"): void {
  activeTab.value = tab;
  isEditorOpen.value = false;
  editingAngle.value = null;
  syncAngle();

  if (selectedBrandId.value) {
    router.push({
      name: "admin.angles",
      params: { brandId: String(selectedBrandId.value) },
    });
  }
}

function selectVisibleAngle(angle: Angle): void {
  selectAngle(angle);
  isEditorOpen.value = false;
  editingAngle.value = null;

  if (selectedBrandId.value) {
    router.push({
      name: "admin.angle",
      params: {
        brandId: String(selectedBrandId.value),
        angleId: String(angle.id),
      },
    });
  }
}

function openCreate(): void {
  activeTab.value = "active";
  editingAngle.value = null;
  isEditorOpen.value = true;

  if (selectedBrandId.value) {
    router.push({
      name: "admin.angles",
      params: { brandId: String(selectedBrandId.value) },
    });
  }
}

function openEdit(angle: Angle): void {
  editingAngle.value = angle;
  selectAngle(angle);
  isEditorOpen.value = true;
}

function closeEditor(): void {
  isEditorOpen.value = false;
  editingAngle.value = null;
}

async function copyLandingUrl(angle: Angle): Promise<void> {
  const url = angle.landing_page?.url;

  if (!url) {
    return;
  }

  try {
    await navigator.clipboard.writeText(url);
    copiedAngleId.value = angle.id;
    copyError.value = "";
    window.setTimeout(() => {
      if (copiedAngleId.value === angle.id) {
        copiedAngleId.value = null;
      }
    }, 1800);
  } catch {
    copyError.value = "Copy failed. Select the URL and copy it manually.";
  }
}

function loadAngleOptions(): Promise<AngleOptions> {
  if (!selectedBrandId.value) {
    return Promise.reject(new Error("Select a Brand first."));
  }

  return loadOptions(selectedBrandId.value);
}

function saveSelectedAngle(payload: AnglePayload, angleId?: number): Promise<Angle> {
  if (!selectedBrandId.value) {
    return Promise.reject(new Error("Select a Brand first."));
  }

  return saveAngle(selectedBrandId.value, payload, angleId);
}

function handleSaved(angle: Angle): void {
  selectAngle(angle);
  isEditorOpen.value = false;
  editingAngle.value = null;

  if (selectedBrandId.value) {
    router.replace({
      name: "admin.angle",
      params: {
        brandId: String(selectedBrandId.value),
        angleId: String(angle.id),
      },
    });
  }
}

async function archiveSelected(): Promise<void> {
  if (!selectedAngle.value || !selectedBrandId.value || !globalThis.confirm(`Archive ${selectedAngle.value.name}?`)) {
    return;
  }

  await archiveAngle(selectedBrandId.value, selectedAngle.value.id);
  activeTab.value = "active";
  await router.replace({
    name: "admin.angles",
    params: { brandId: String(selectedBrandId.value) },
  });
}

async function restoreSelected(): Promise<void> {
  if (!selectedAngle.value || !selectedBrandId.value) {
    return;
  }

  const angleId = selectedAngle.value.id;
  await restoreAngle(selectedBrandId.value, angleId);
  activeTab.value = "active";
  await router.replace({
    name: "admin.angle",
    params: {
      brandId: String(selectedBrandId.value),
      angleId: String(angleId),
    },
  });
}

function formatDate(value: string): string {
  return new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(value));
}

watch(activeBrands, syncBrand, { immediate: true });
watch(selectedBrandId, loadSelectedBrand, { immediate: true });
watch([activeAngles, archivedAngles, routeAngleId], syncAngle);
watch(routeBrandId, syncBrand);
watch(routeAngleId, () => {
  isEditorOpen.value = false;
  editingAngle.value = null;
});

onMounted(() => {
  if (props.canManage) {
    loadBrands();
  }
});
</script>
