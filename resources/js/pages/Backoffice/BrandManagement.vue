<template>
  <section class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-heymo-red">Brand system</p>
        <h2 class="mt-1 text-2xl font-extrabold text-heymo-navy">Brands</h2>
        <p class="mt-1 max-w-2xl text-sm text-heymo-muted">Keep the visual identity and writing profile that campaigns inherit in one place.</p>
      </div>
      <button v-if="canManage" class="btn btn-primary" @click="openCreate">
        <Plus :size="18" weight="bold" aria-hidden="true" />
        New brand
      </button>
    </div>

    <div v-if="!canManage" class="alert alert-soft alert-warning mx-auto max-w-[1600px] p-6">
      <div class="flex items-start gap-3">
        <WarningCircle :size="22" weight="fill" class="mt-0.5 shrink-0 text-heymo-warning" aria-hidden="true" />
        <div>
          <h2 class="text-sm font-bold text-heymo-navy">Brand settings are restricted</h2>
          <p class="mt-1 text-sm leading-6 text-heymo-muted">An administrator role is required to view and manage brand profiles.</p>
        </div>
      </div>
    </div>

    <div v-else class="grid gap-5 xl:grid-cols-[minmax(19rem,0.8fr)_minmax(0,1.4fr)]">
      <Panel title="Brand portfolio" description="Active profiles are ready for future campaign generation.">
        <template #action>
          <span class="text-[11px] font-bold text-heymo-muted">{{ visibleBrands.length }} shown</span>
        </template>

        <div class="tabs tabs-box mb-4 w-full" role="tablist" aria-label="Brand status">
          <button
            type="button"
            class="tab grow text-xs font-bold"
            :class="activeTab === 'active' ? 'tab-active' : ''"
            role="tab"
            :aria-selected="activeTab === 'active'"
            @click="setActiveTab('active')"
          >
            Active <span class="ml-1 tabular-nums">{{ activeBrands.length }}</span>
          </button>
          <button
            type="button"
            class="tab grow text-xs font-bold"
            :class="activeTab === 'archived' ? 'tab-active' : ''"
            role="tab"
            :aria-selected="activeTab === 'archived'"
            @click="setActiveTab('archived')"
          >
            Archived <span class="ml-1 tabular-nums">{{ archivedBrands.length }}</span>
          </button>
        </div>

        <div v-if="errorMessage" class="alert alert-soft alert-error mb-4 px-3 py-2.5 text-xs font-semibold leading-5">
          {{ errorMessage }}
        </div>
        <div v-if="isLoading" class="space-y-3" aria-label="Loading brands">
          <div v-for="placeholder in 2" :key="placeholder" class="skeleton h-20 w-full"></div>
        </div>
        <div v-else-if="!visibleBrands.length" class="rounded-md border border-dashed border-heymo-line px-4 py-10 text-center">
          <PaintBrush :size="28" class="mx-auto text-heymo-muted" aria-hidden="true" />
          <p class="mt-3 text-sm font-bold text-heymo-navy">{{ activeTab === "active" ? "No active brands yet" : "No archived brands" }}</p>
          <p class="mt-1 text-xs leading-5 text-heymo-muted">
            {{ activeTab === "active" ? "Create a profile to give future campaigns a distinct voice." : "Archived profiles will appear here." }}
          </p>
        </div>
        <ul v-else class="space-y-2" role="list">
          <li v-for="brand in visibleBrands" :key="brand.id">
            <button
              type="button"
              class="flex w-full items-center gap-3 rounded-md border px-3 py-3 text-left transition focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
              :class="
                selectedBrand?.id === brand.id
                  ? 'border-heymo-navy bg-heymo-sky'
                  : 'border-heymo-line bg-white hover:border-heymo-navy/40 hover:bg-slate-50'
              "
              @click="selectVisibleBrand(brand)"
            >
              <span
                class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-md text-xs font-extrabold text-white"
                :style="{ backgroundColor: brand.primary_color }"
              >
                <img v-if="brand.logo_url" :src="brand.logo_url" :alt="`${brand.name} logo`" class="size-full object-contain bg-white" />
                <span v-else>{{ initials(brand.name) }}</span>
              </span>
              <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-extrabold text-heymo-navy">{{ brand.name }}</span>
                <span class="mt-1 flex items-center gap-2 text-[11px] text-heymo-muted"
                  ><span class="size-2 rounded-full" :style="{ backgroundColor: brand.secondary_color }"></span>{{ brand.heading_font_label }} +
                  {{ brand.body_font_label }}</span
                >
              </span>
              <CaretRight :size="16" class="shrink-0 text-heymo-muted" aria-hidden="true" />
            </button>
          </li>
        </ul>
      </Panel>

      <div class="min-w-0">
        <Panel v-if="showBrandNotFound" title="Brand not found" description="This brand is unavailable in your workspace.">
          <div class="grid min-h-80 place-items-center rounded-md border border-dashed border-heymo-line bg-slate-50 px-6 text-center">
            <WarningCircle :size="34" class="text-heymo-muted" aria-hidden="true" />
            <p class="mt-3 text-sm font-bold text-heymo-navy">That brand is not available</p>
            <p class="mt-1 max-w-sm text-xs leading-5 text-heymo-muted">Choose an active brand from the portfolio to continue.</p>
          </div>
        </Panel>
        <Panel v-else-if="hasBrandRoute && isLoading" title="Loading brand" description="Retrieving the saved brand profile.">
          <div class="grid min-h-80 place-items-center">
            <span class="loading loading-spinner text-heymo-red" aria-label="Loading brand"></span>
          </div>
        </Panel>
        <BrandForm
          v-else-if="isEditorOpen"
          :brand="editingBrand"
          :save-brand="saveBrand"
          :upload-logo="uploadLogo"
          @cancel="closeEditor"
          @saved="handleSaved"
        />
        <Panel v-else-if="selectedBrand" :title="selectedBrand.name" description="Saved identity and derived writing profile.">
          <template #action>
            <div class="flex items-center gap-2">
              <button v-if="activeTab === 'active'" class="btn btn-outline btn-sm" @click="openEdit(selectedBrand)">
                <PencilSimple :size="16" weight="bold" aria-hidden="true" /> Edit
              </button>
              <button
                v-if="activeTab === 'active'"
                class="btn btn-square btn-ghost btn-sm border border-base-300"
                aria-label="Archive brand"
                title="Archive brand"
                @click="archiveSelected"
              >
                <Archive :size="17" weight="bold" aria-hidden="true" />
              </button>
              <button v-else class="btn btn-outline btn-sm" @click="restoreSelected">
                <ArrowCounterClockwise :size="16" weight="bold" aria-hidden="true" /> Restore
              </button>
            </div>
          </template>

          <div class="grid gap-5 lg:grid-cols-[minmax(13rem,0.65fr)_minmax(0,1.35fr)]">
            <div class="space-y-4">
              <div
                class="relative overflow-hidden rounded-md bg-primary p-5 text-primary-content"
                data-theme="brand-preview"
                :style="brandThemeStyle(selectedBrand)"
              >
                <div class="absolute -right-8 -top-10 size-32 rounded-full border-[18px] border-primary-content/15"></div>
                <div class="relative">
                  <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-primary-content/70">Identity preview</p>
                  <div class="mt-6 flex h-24 w-full items-center justify-center rounded-md bg-white px-4 py-3">
                    <img
                      v-if="selectedBrand.logo_url"
                      :src="selectedBrand.logo_url"
                      :alt="`${selectedBrand.name} logo`"
                      class="max-h-full max-w-full object-contain"
                    />
                    <span v-else class="text-xl font-extrabold text-primary">{{ initials(selectedBrand.name) }}</span>
                  </div>
                  <p class="mt-6 max-w-[12rem] text-2xl font-extrabold leading-tight" :style="{ fontFamily: selectedBrand.heading_font_stack }">
                    {{ selectedBrand.name }}
                  </p>
                  <p class="mt-3 max-w-[13rem] text-xs leading-5 text-primary-content/80" :style="{ fontFamily: selectedBrand.body_font_stack }">
                    A saved palette and type pairing ready for the campaign layer.
                  </p>
                </div>
                <div class="relative mt-8 flex items-center gap-2">
                  <span class="size-5 rounded-full border-2 border-primary-content/60 bg-secondary"></span
                  ><span class="font-mono text-[10px] text-primary-content/70">{{ selectedBrand.secondary_color }}</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div class="rounded-md border border-heymo-line p-3">
                  <span class="block size-5 rounded" :style="{ backgroundColor: selectedBrand.primary_color }"></span
                  ><span class="mt-2 block text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Primary</span
                  ><span class="mt-0.5 block font-mono text-xs font-bold text-heymo-navy">{{ selectedBrand.primary_color }}</span>
                </div>
                <div class="rounded-md border border-heymo-line p-3">
                  <span class="block size-5 rounded" :style="{ backgroundColor: selectedBrand.secondary_color }"></span
                  ><span class="mt-2 block text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Secondary</span
                  ><span class="mt-0.5 block font-mono text-xs font-bold text-heymo-navy">{{ selectedBrand.secondary_color }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-5">
              <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                  <h3 class="text-sm font-bold text-heymo-navy">Writing profile</h3>
                  <span class="font-mono text-[10px] text-heymo-muted">{{ selectedBrand.prompt_profile.version }}</span>
                </div>
                <dl class="grid gap-2 sm:grid-cols-2">
                  <div v-for="dimension in profileDimensions" :key="dimension.label" class="rounded-md bg-base-200 px-3 py-2.5">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.08em] text-heymo-muted">{{ dimension.label }}</dt>
                    <dd class="mt-1 text-xs font-extrabold text-heymo-navy">{{ dimension.value }}</dd>
                  </div>
                </dl>
              </div>
              <div class="border-t border-heymo-line pt-4">
                <h3 class="text-sm font-bold text-heymo-navy">Prompt blueprint</h3>
                <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-md bg-base-200 p-3 text-[11px] leading-5 text-base-content">{{
                  selectedBrand.prompt_profile.prompt_skeleton
                }}</pre>
              </div>
            </div>
          </div>
        </Panel>
        <Panel v-else title="Select a brand" description="Choose a saved profile to inspect its identity and writing blueprint.">
          <div class="grid min-h-80 place-items-center rounded-md border border-dashed border-heymo-line bg-slate-50 px-6 text-center">
            <PaintBrush :size="34" class="text-heymo-muted" aria-hidden="true" />
            <p class="mt-3 text-sm font-bold text-heymo-navy">Your brand workspace is ready</p>
            <p class="mt-1 max-w-sm text-xs leading-5 text-heymo-muted">
              Create a brand or select one from the portfolio to review its saved settings.
            </p>
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
  PhPaintBrush as PaintBrush,
  PhPencilSimple as PencilSimple,
  PhPlus as Plus,
  PhWarningCircle as WarningCircle,
} from "@phosphor-icons/vue";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import Panel from "../../components/Backoffice/Panel.vue";
import { useBrands } from "../../composables/useBrands";
import { brandThemeStyle } from "../../lib/brands";
import BrandForm from "./BrandForm.vue";
import type { Brand } from "../../lib/brands";

const props = defineProps<{ canManage: boolean }>();
const route = useRoute();
const router = useRouter();
const {
  activeBrands,
  archivedBrands,
  selectedBrand,
  isLoading,
  errorMessage,
  loadBrands,
  selectBrand,
  saveBrand,
  archiveBrand,
  restoreBrand,
  uploadLogo,
} = useBrands();
const activeTab = ref<"active" | "archived">("active");
const isEditorOpen = ref(false);
const editingBrand = ref<Brand | null>(null);

const visibleBrands = computed(() => (activeTab.value === "active" ? activeBrands.value : archivedBrands.value));
const hasBrandRoute = computed(() => route.name === "admin.brand");
const routeBrandId = computed<number | null>(() => {
  if (!hasBrandRoute.value) {
    return null;
  }

  const rawId = route.params.id;
  if (typeof rawId !== "string" || !/^[1-9]\d*$/.test(rawId)) {
    return null;
  }

  const brandId = Number(rawId);
  return Number.isSafeInteger(brandId) ? brandId : null;
});
const showBrandNotFound = computed(
  () =>
    hasBrandRoute.value &&
    !isLoading.value &&
    !errorMessage.value &&
    (routeBrandId.value === null || !activeBrands.value.some(brand => brand.id === routeBrandId.value)),
);
const profileDimensions = computed(() => {
  if (!selectedBrand.value) {
    return [];
  }

  return [
    { label: "Tone", value: selectedBrand.value.prompt_profile.voice.tone.label },
    { label: "Flow", value: selectedBrand.value.prompt_profile.voice.flow.label },
    { label: "Tense", value: selectedBrand.value.prompt_profile.voice.tense.label },
    { label: "Reading level", value: selectedBrand.value.prompt_profile.voice.reading_level.label },
  ];
});

function initials(name: string): string {
  return name
    .split(" ")
    .map(part => part[0])
    .filter(Boolean)
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

function openCreate(): void {
  activeTab.value = "active";
  editingBrand.value = null;
  isEditorOpen.value = true;

  if (hasBrandRoute.value) {
    router.push({ name: "admin.brands" });
  }
}

function openEdit(brand: Brand): void {
  editingBrand.value = brand;
  selectBrand(brand);
  isEditorOpen.value = true;
  router.push({ name: "admin.brand", params: { id: String(brand.id) } });
}

function closeEditor(): void {
  isEditorOpen.value = false;
  editingBrand.value = null;
}

function handleSaved(brand: Brand): void {
  selectBrand(brand);
  isEditorOpen.value = false;
  editingBrand.value = null;
  router.replace({ name: "admin.brand", params: { id: String(brand.id) } });
}

function setActiveTab(tab: "active" | "archived"): void {
  activeTab.value = tab;
  selectBrand((tab === "active" ? activeBrands.value : archivedBrands.value)[0] ?? null);

  if (hasBrandRoute.value) {
    router.push({ name: "admin.brands" });
  }
}

function selectVisibleBrand(brand: Brand): void {
  selectBrand(brand);

  if (activeTab.value === "active") {
    router.push({ name: "admin.brand", params: { id: String(brand.id) } });
  }
}

async function archiveSelected(): Promise<void> {
  if (!selectedBrand.value || !globalThis.confirm(`Archive ${selectedBrand.value.name}?`)) {
    return;
  }

  await archiveBrand(selectedBrand.value.id);
  activeTab.value = "active";
  await router.replace({ name: "admin.brands" });
}

async function restoreSelected(): Promise<void> {
  if (!selectedBrand.value) {
    return;
  }

  const brandId = selectedBrand.value.id;
  await restoreBrand(brandId);
  activeTab.value = "active";
  await router.replace({ name: "admin.brand", params: { id: String(brandId) } });
}

watch(
  [activeBrands, archivedBrands, hasBrandRoute, routeBrandId],
  () => {
    if (hasBrandRoute.value) {
      const brand = routeBrandId.value === null ? null : (activeBrands.value.find(item => item.id === routeBrandId.value) ?? null);
      selectBrand(brand);
      activeTab.value = "active";
      return;
    }

    const availableBrands = activeTab.value === "active" ? activeBrands.value : archivedBrands.value;
    if (!selectedBrand.value || !availableBrands.some(brand => brand.id === selectedBrand.value?.id)) {
      selectBrand(availableBrands[0] ?? null);
    }
  },
  { immediate: true },
);

onMounted(() => {
  if (props.canManage) {
    loadBrands();
  }
});
</script>
