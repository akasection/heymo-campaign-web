<template>
  <Panel
    :title="brand ? `Edit ${brand.name}` : 'New brand'"
    :description="brand ? 'Update the saved identity and writing profile.' : 'Set the identity that future campaign writing will inherit.'"
  >
    <form class="space-y-7" @submit.prevent="save">
      <div>
        <label for="brand-name" class="mb-2 block text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Brand name</label>
        <input id="brand-name" v-model="draft.name" type="text" maxlength="120" class="input input-md w-full" placeholder="e.g. Lexical Labs" />
        <p v-if="fieldError('name')" class="mt-1.5 text-xs font-semibold text-heymo-red">{{ fieldError("name") }}</p>
      </div>

      <fieldset class="space-y-4">
        <legend class="text-sm font-bold text-heymo-navy">Visual identity</legend>
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="brand-primary-color" class="mb-2 block text-xs font-bold text-heymo-ink">Primary color</label>
            <div class="flex items-center gap-2">
              <input
                id="brand-primary-color"
                :value="colorValue(draft.primary_color)"
                type="color"
                class="size-10 shrink-0 cursor-pointer rounded border border-heymo-line bg-white p-1"
                aria-label="Choose primary color"
                @input="setColorFromPicker('primary_color', $event)"
              />
              <input
                v-model="draft.primary_color"
                type="text"
                maxlength="7"
                class="input input-md min-w-0 flex-1 font-mono uppercase"
                placeholder="#2E5BFF"
              />
            </div>
            <p v-if="fieldError('primary_color')" class="mt-1.5 text-xs font-semibold text-heymo-red">{{ fieldError("primary_color") }}</p>
          </div>
          <div>
            <label for="brand-secondary-color" class="mb-2 block text-xs font-bold text-heymo-ink">Secondary color</label>
            <div class="flex items-center gap-2">
              <input
                id="brand-secondary-color"
                :value="colorValue(draft.secondary_color)"
                type="color"
                class="size-10 shrink-0 cursor-pointer rounded border border-heymo-line bg-white p-1"
                aria-label="Choose secondary color"
                @input="setColorFromPicker('secondary_color', $event)"
              />
              <input
                v-model="draft.secondary_color"
                type="text"
                maxlength="7"
                class="input input-md min-w-0 flex-1 font-mono uppercase"
                placeholder="#00B8A9"
              />
            </div>
            <p v-if="fieldError('secondary_color')" class="mt-1.5 text-xs font-semibold text-heymo-red">{{ fieldError("secondary_color") }}</p>
          </div>
        </div>

        <div>
          <label for="brand-logo" class="mb-2 block text-xs font-bold text-heymo-ink">Logo</label>
          <div class="flex items-center gap-3">
            <div
              class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-md border border-heymo-line bg-slate-50 text-lg font-extrabold text-heymo-navy"
            >
              <img v-if="logoPreviewUrl" :src="logoPreviewUrl" alt="Brand logo preview" class="size-full object-contain" />
              <span v-else>{{ brandInitials }}</span>
            </div>
            <div class="min-w-0">
              <input
                id="brand-logo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="file-input file-input-md w-full"
                @change="selectLogo"
              />
              <p class="mt-1.5 text-[11px] leading-4 text-heymo-muted">PNG, JPG, or WebP up to 2 MB.</p>
            </div>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="brand-heading-font" class="mb-2 block text-xs font-bold text-heymo-ink">Heading font</label>
            <select id="brand-heading-font" v-model="draft.heading_font" class="select select-md w-full" :disabled="isLoadingOptions">
              <option v-for="font in options?.fonts ?? []" :key="font.value" :value="font.value">{{ font.label }}</option>
            </select>
          </div>
          <div>
            <label for="brand-body-font" class="mb-2 block text-xs font-bold text-heymo-ink">Body font</label>
            <select id="brand-body-font" v-model="draft.body_font" class="select select-md w-full" :disabled="isLoadingOptions">
              <option v-for="font in options?.fonts ?? []" :key="font.value" :value="font.value">{{ font.label }}</option>
            </select>
          </div>
        </div>
      </fieldset>

      <fieldset class="space-y-5">
        <legend class="text-sm font-bold text-heymo-navy">Writing profile</legend>
        <div v-for="axis in voiceAxes" :key="axis.field" class="space-y-2">
          <div class="flex items-baseline justify-between gap-3">
            <div>
              <p class="text-xs font-bold text-heymo-ink">{{ axis.label }}</p>
              <p class="text-[11px] text-heymo-muted">{{ axis.start }} <span class="px-1">to</span> {{ axis.end }}</p>
            </div>
            <span class="badge badge-soft badge-info font-extrabold">{{ currentPresetLabel(axis) }}</span>
          </div>
          <div class="join w-full" role="radiogroup" :aria-label="`${axis.label} preference`">
            <button
              v-for="option in options?.presets[axis.group] ?? []"
              :key="option.value"
              type="button"
              class="btn btn-sm join-item min-w-0 flex-1 text-xs font-bold focus:z-10 focus:-outline-offset-2 focus:outline-2 focus:outline-primary"
              :class="draft[axis.field] === option.value ? 'btn-neutral' : 'btn-ghost text-base-content/60 hover:text-base-content'"
              :aria-checked="draft[axis.field] === option.value"
              role="radio"
              @click="setPreset(axis.field, option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>
      </fieldset>

      <fieldset class="space-y-4">
        <legend class="text-sm font-bold text-heymo-navy">Language cues</legend>
        <div class="grid gap-5 sm:grid-cols-2">
          <TermEditor
            field="preferred_terms"
            label="Phrases to use"
            placeholder="Add a phrase or vibe"
            :terms="draft.preferred_terms"
            :input-value="phraseInputs.preferred_terms"
            :error="fieldError('preferred_terms')"
            @update:input-value="phraseInputs.preferred_terms = $event"
            @add="addTerm('preferred_terms')"
            @remove="removeTerm('preferred_terms', $event)"
          />
          <TermEditor
            field="avoided_terms"
            label="Phrases to avoid"
            placeholder="Add a taboo or red flag"
            :terms="draft.avoided_terms"
            :input-value="phraseInputs.avoided_terms"
            :error="fieldError('avoided_terms')"
            @update:input-value="phraseInputs.avoided_terms = $event"
            @add="addTerm('avoided_terms')"
            @remove="removeTerm('avoided_terms', $event)"
          />
        </div>
      </fieldset>

      <div v-if="brand" class="card card-border bg-base-200 p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Saved prompt blueprint</p>
            <p class="mt-1 text-[11px] text-heymo-muted">Version {{ brand.prompt_profile.version }}</p>
          </div>
          <span class="badge badge-ghost badge-xs font-bold">Derived</span>
        </div>
        <pre class="mt-3 max-h-48 overflow-auto whitespace-pre-wrap text-[11px] leading-5 text-heymo-ink">{{
          brand.prompt_profile.prompt_skeleton
        }}</pre>
      </div>

      <div v-if="formError" class="alert alert-soft alert-error px-3 py-2.5 text-xs font-semibold leading-5">
        {{ formError }}
      </div>

      <div class="flex flex-col-reverse justify-end gap-2 border-t border-heymo-line pt-5 sm:flex-row">
        <button type="button" class="btn btn-outline" :disabled="isSaving" @click="$emit('cancel')">Cancel</button>
        <button type="submit" class="btn btn-primary" :disabled="isSaving || isLoadingOptions">
          {{ isSaving ? "Saving..." : brand ? "Save changes" : "Create brand" }}
        </button>
      </div>
    </form>
  </Panel>
</template>

<script setup lang="ts">
import { computed, onMounted, toRef } from "vue";
import Panel from "../../components/Backoffice/Panel.vue";
import TermEditor from "../../components/Backoffice/TermEditor.vue";
import { useBrandForm } from "../../composables/useBrandForm";
import type { BrandPresetField } from "../../composables/useBrandForm";
import type { Brand, BrandPayload } from "../../lib/brands";

const props = defineProps<{
  brand: Brand | null;
  saveBrand: (payload: BrandPayload, brandId?: number) => Promise<Brand>;
  uploadLogo: (brandId: number, file: File) => Promise<Brand>;
}>();

const emit = defineEmits<{
  cancel: [];
  saved: [brand: Brand];
}>();

const sourceBrand = toRef(props, "brand");
const {
  draft,
  options,
  phraseInputs,
  errors,
  formError,
  isLoadingOptions,
  isSaving,
  logoPreviewUrl,
  loadOptions,
  addTerm,
  removeTerm,
  setLogo,
  setColorFromPicker,
  colorValue,
  submit,
} = useBrandForm(sourceBrand);

const voiceAxes = [
  { field: "tone_preset", group: "tone", label: "Tone", start: "Formal", end: "Informal" },
  { field: "flow_preset", group: "flow", label: "Flow", start: "Descriptive", end: "Narrative" },
  { field: "tense_preset", group: "tense", label: "Tense", start: "Serious", end: "Relaxed" },
  { field: "reading_level_preset", group: "reading_level", label: "Reading level", start: "Simpler", end: "Complex" },
] as const;

const brandInitials = computed(() => {
  const name = draft.value.name || props.brand?.name || "Brand";
  return name
    .split(" ")
    .map(part => part[0])
    .filter(Boolean)
    .slice(0, 2)
    .join("")
    .toUpperCase();
});

function fieldError(field: string): string {
  return errors.value[field]?.[0] ?? "";
}

function currentPresetLabel(axis: (typeof voiceAxes)[number]): string {
  return options.value?.presets[axis.group].find(option => option.value === draft.value[axis.field])?.label ?? "Balanced";
}

function setPreset(field: BrandPresetField, value: string): void {
  draft.value[field] = value;
}

function selectLogo(event: Event): void {
  const input = event.target as HTMLInputElement;
  setLogo(input.files?.[0] ?? null);
}

async function save(): Promise<void> {
  const savedBrand = await submit(props.saveBrand, props.uploadLogo);
  if (savedBrand) {
    emit("saved", savedBrand);
  }
}

onMounted(loadOptions);
</script>
