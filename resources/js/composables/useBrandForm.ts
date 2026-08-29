import { onBeforeUnmount, ref, watch } from "vue";
import type { Ref } from "vue";
import { ApiError, apiFetch } from "../lib/auth";
import { normalizeBrandTerms } from "../lib/brands";
import type { Brand, BrandDraft, BrandOptions, BrandPayload } from "../lib/brands";

type BrandTermField = "preferred_terms" | "avoided_terms";
type BrandPresetField = "tone_preset" | "flow_preset" | "tense_preset" | "reading_level_preset";
type BrandColorField = "primary_color" | "secondary_color";

const fallbackDraft: BrandDraft = {
  name: "",
  tone_preset: "balanced",
  flow_preset: "balanced",
  tense_preset: "balanced",
  reading_level_preset: "balanced",
  preferred_terms: [],
  avoided_terms: [],
  primary_color: "#2E5BFF",
  secondary_color: "#00B8A9",
  heading_font: "public_sans",
  body_font: "public_sans",
};

function draftFrom(brand: Brand | null | undefined, defaults: BrandOptions["defaults"] = {}): BrandDraft {
  if (brand) {
    return {
      name: brand.name,
      tone_preset: brand.tone_preset,
      flow_preset: brand.flow_preset,
      tense_preset: brand.tense_preset,
      reading_level_preset: brand.reading_level_preset,
      preferred_terms: [...brand.preferred_terms],
      avoided_terms: [...brand.avoided_terms],
      primary_color: brand.primary_color,
      secondary_color: brand.secondary_color,
      heading_font: brand.heading_font,
      body_font: brand.body_font,
    };
  }

  return {
    ...fallbackDraft,
    ...defaults,
    preferred_terms: [...(defaults.preferred_terms ?? fallbackDraft.preferred_terms)],
    avoided_terms: [...(defaults.avoided_terms ?? fallbackDraft.avoided_terms)],
  };
}

function validationMessage(error: unknown): string {
  return error instanceof ApiError ? error.message : "We could not load the brand options.";
}

function colorValue(color: string): string {
  return /^#[0-9A-Fa-f]{6}$/.test(color) ? color : "#000000";
}

function apiFieldErrors(error: unknown): Record<string, string[]> {
  const rawErrors = error instanceof ApiError ? error.data.errors : null;

  if (!rawErrors || typeof rawErrors !== "object" || Array.isArray(rawErrors)) {
    return {};
  }

  const fieldErrors: Record<string, string[]> = {};
  for (const [field, messages] of Object.entries(rawErrors)) {
    fieldErrors[field] = Array.isArray(messages) ? messages.filter((message): message is string => typeof message === "string") : [];
  }

  return fieldErrors;
}

function useBrandForm(sourceBrand: Ref<Brand | null | undefined>) {
  const draft = ref<BrandDraft>(draftFrom(sourceBrand.value));
  const options = ref<BrandOptions | null>(null);
  const phraseInputs = ref<Record<BrandTermField, string>>({
    preferred_terms: "",
    avoided_terms: "",
  });
  const errors = ref<Record<string, string[]>>({});
  const formError = ref("");
  const isLoadingOptions = ref(false);
  const isSaving = ref(false);
  const logoFile = ref<File | null>(null);
  const logoPreviewUrl = ref<string | null>(sourceBrand.value?.logo_url ?? null);

  function revokeBlobPreview(): void {
    if (logoPreviewUrl.value?.startsWith("blob:")) {
      URL.revokeObjectURL(logoPreviewUrl.value);
    }
  }

  function reset(brand: Brand | null | undefined = sourceBrand.value): void {
    revokeBlobPreview();
    draft.value = draftFrom(brand, options.value?.defaults);
    phraseInputs.value = { preferred_terms: "", avoided_terms: "" };
    errors.value = {};
    formError.value = "";
    logoFile.value = null;
    logoPreviewUrl.value = brand?.logo_url ?? null;
  }

  watch(sourceBrand, brand => reset(brand), { immediate: true });

  async function loadOptions(): Promise<void> {
    isLoadingOptions.value = true;
    formError.value = "";

    try {
      const response = await apiFetch<BrandOptions>("/api/brands/options");
      options.value = response;

      if (!sourceBrand.value) {
        draft.value = draftFrom(null, response.defaults);
      }
    } catch (error) {
      formError.value = validationMessage(error);
    } finally {
      isLoadingOptions.value = false;
    }
  }

  function addTerm(field: BrandTermField): void {
    const rawTerm = phraseInputs.value[field].trim();
    const maxTerms = options.value?.limits.max_terms ?? 20;
    const maxLength = options.value?.limits.max_term_length ?? 60;

    if (!rawTerm) {
      return;
    }

    if (rawTerm.length > maxLength) {
      errors.value[field] = [`Keep terms to ${maxLength} characters or fewer.`];
      return;
    }

    const terms = draft.value[field];
    const normalized = normalizeBrandTerms([...terms, rawTerm]);

    if (normalized.length > maxTerms) {
      errors.value[field] = [`Use ${maxTerms} terms or fewer.`];
      return;
    }

    draft.value[field] = normalized;
    phraseInputs.value[field] = "";
    errors.value[field] = [];
  }

  function removeTerm(field: BrandTermField, index: number): void {
    draft.value[field] = draft.value[field].filter((_, termIndex) => termIndex !== index);
  }

  function setLogo(file: File | null): void {
    revokeBlobPreview();
    logoFile.value = file;
    logoPreviewUrl.value = file ? URL.createObjectURL(file) : (sourceBrand.value?.logo_url ?? null);
  }

  function setColorFromPicker(field: BrandColorField, event: Event): void {
    if (!(event.target instanceof HTMLInputElement)) {
      return;
    }

    const input = event.target;
    draft.value[field] = input.value.toUpperCase();
  }

  function validate(): Record<string, string[]> {
    const nextErrors: Record<string, string[]> = {};
    const maxTerms = options.value?.limits.max_terms ?? 20;
    const maxLength = options.value?.limits.max_term_length ?? 60;

    if (!draft.value.name.trim()) {
      nextErrors.name = ["Enter a brand name."];
    }

    for (const field of ["primary_color", "secondary_color"] as BrandColorField[]) {
      if (!/^#[0-9A-Fa-f]{6}$/.test(draft.value[field])) {
        nextErrors[field] = ["Use a six-digit hex color, such as #2E5BFF."];
      }
    }

    for (const field of ["preferred_terms", "avoided_terms"] as BrandTermField[]) {
      if (draft.value[field].length > maxTerms) {
        nextErrors[field] = [`Use ${maxTerms} terms or fewer.`];
      } else if (draft.value[field].some(term => term.length > maxLength)) {
        nextErrors[field] = [`Keep terms to ${maxLength} characters or fewer.`];
      }
    }

    return nextErrors;
  }

  function payload(): BrandPayload {
    return {
      ...draft.value,
      name: draft.value.name.trim(),
      preferred_terms: normalizeBrandTerms(draft.value.preferred_terms),
      avoided_terms: normalizeBrandTerms(draft.value.avoided_terms),
      primary_color: draft.value.primary_color.toUpperCase(),
      secondary_color: draft.value.secondary_color.toUpperCase(),
    };
  }

  async function submit(
    saveBrand: (payload: BrandPayload, brandId?: number) => Promise<Brand>,
    uploadLogo: (brandId: number, file: File) => Promise<Brand>,
  ): Promise<Brand | null> {
    errors.value = validate();
    formError.value = "";

    if (Object.values(errors.value).some(messages => messages.length > 0)) {
      return null;
    }

    isSaving.value = true;

    try {
      let savedBrand = await saveBrand(payload(), sourceBrand.value?.id);
      if (logoFile.value) {
        savedBrand = await uploadLogo(savedBrand.id, logoFile.value);
      }

      reset(savedBrand);
      return savedBrand;
    } catch (error) {
      errors.value = apiFieldErrors(error);
      formError.value = validationMessage(error);
      return null;
    } finally {
      isSaving.value = false;
    }
  }

  onBeforeUnmount(revokeBlobPreview);

  return {
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
    reset,
  };
}

export { colorValue, useBrandForm };
export type { BrandColorField, BrandPresetField, BrandTermField };
