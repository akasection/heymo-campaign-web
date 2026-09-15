import { ref, watch } from "vue";
import type { Ref } from "vue";
import { ApiError } from "../lib/auth";
import type { Angle, AngleDraft, AngleOptions, AnglePayload } from "../lib/angles";

type AngleTextField = Exclude<keyof AngleDraft, "landing_identifier">;

const fallbackDraft: AngleDraft = {
  landing_identifier: null,
  name: "",
  audience: "",
  trigger_moment: "",
  primary_job: "",
  tension: "",
  desired_outcome: "",
  single_promise: "",
  proof: "",
  objection: "",
  offer: "",
  tone: "matter_of_fact",
  next_step: "",
  next_step_url: "",
};

function draftFrom(angle: Angle | null | undefined, defaults: AngleOptions["defaults"] = {}): AngleDraft {
  if (angle) {
    return {
      landing_identifier: angle.landing_identifier,
      name: angle.name,
      audience: angle.audience,
      trigger_moment: angle.trigger_moment,
      primary_job: angle.primary_job,
      tension: angle.tension,
      desired_outcome: angle.desired_outcome,
      single_promise: angle.single_promise,
      proof: angle.proof,
      objection: angle.objection,
      offer: angle.offer,
      tone: angle.tone,
      next_step: angle.next_step,
      next_step_url: angle.next_step_url ?? "",
    };
  }

  return { ...fallbackDraft, ...defaults };
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

function messageFor(error: unknown): string {
  return error instanceof ApiError ? error.message : "We could not save this angle.";
}

function useAngleForm(sourceAngle: Ref<Angle | null | undefined>, loadOptions: () => Promise<AngleOptions>) {
  const draft = ref<AngleDraft>(draftFrom(sourceAngle.value));
  const options = ref<AngleOptions | null>(null);
  const errors = ref<Record<string, string[]>>({});
  const formError = ref("");
  const isLoadingOptions = ref(false);
  const isSaving = ref(false);

  function reset(angle: Angle | null | undefined = sourceAngle.value): void {
    draft.value = draftFrom(angle, options.value?.defaults);
    errors.value = {};
    formError.value = "";
  }

  watch(sourceAngle, angle => reset(angle), { immediate: true });

  async function load(): Promise<void> {
    isLoadingOptions.value = true;
    formError.value = "";

    try {
      options.value = await loadOptions();
      if (!sourceAngle.value) {
        draft.value = draftFrom(null, options.value.defaults);
      }
    } catch (error) {
      formError.value = messageFor(error);
    } finally {
      isLoadingOptions.value = false;
    }
  }

  function validate(): Record<string, string[]> {
    const nextErrors: Record<string, string[]> = {};
    const requiredFields: AngleTextField[] = [
      "name",
      "audience",
      "trigger_moment",
      "primary_job",
      "tension",
      "desired_outcome",
      "single_promise",
      "proof",
      "objection",
      "offer",
      "next_step",
    ];

    for (const field of requiredFields) {
      if (!draft.value[field].trim()) {
        nextErrors[field] = ["This field is required."];
      }
    }

    const nameLimit = options.value?.limits.name ?? 120;
    const textLimit = options.value?.limits.text ?? 2000;
    if (draft.value.name.length > nameLimit) {
      nextErrors.name = [`Keep the name to ${nameLimit} characters or fewer.`];
    }

    for (const field of requiredFields.slice(1)) {
      if (draft.value[field].length > textLimit) {
        nextErrors[field] = [`Keep this field to ${textLimit} characters or fewer.`];
      }
    }

    return nextErrors;
  }

  function payload(): AnglePayload {
    return Object.fromEntries(
      Object.entries(draft.value).map(([field, value]) => [field, typeof value === "string" ? value.trim() : value]),
    ) as AnglePayload;
  }

  async function submit(saveAngle: (payload: AnglePayload, angleId?: number) => Promise<Angle>): Promise<Angle | null> {
    errors.value = validate();
    formError.value = "";

    if (Object.values(errors.value).some(messages => messages.length > 0)) {
      return null;
    }

    isSaving.value = true;

    try {
      const savedAngle = await saveAngle(payload(), sourceAngle.value?.id);
      reset(savedAngle);
      return savedAngle;
    } catch (error) {
      errors.value = apiFieldErrors(error);
      formError.value = messageFor(error);
      return null;
    } finally {
      isSaving.value = false;
    }
  }

  return {
    draft,
    options,
    errors,
    formError,
    isLoadingOptions,
    isSaving,
    load,
    submit,
  };
}

export { draftFrom, useAngleForm };
