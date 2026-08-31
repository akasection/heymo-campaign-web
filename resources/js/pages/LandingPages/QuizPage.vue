<template>
  <section class="quiz-shell" :style="themeStyle">
    <div class="mb-8 flex items-center justify-between gap-4">
      <a :href="landingUrl" class="inline-flex items-center gap-2 text-xs font-black text-heymo-muted transition hover:text-heymo-navy">
        <ArrowLeft :size="16" weight="bold" aria-hidden="true" />
        Back to the page
      </a>
      <span class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--brand-primary)">{{ presentation.name }}</span>
    </div>

    <div v-if="status === 'success'" class="quiz-success">
      <div
        class="flex size-14 items-center justify-center rounded-full"
        style="background-color: var(--brand-secondary); color: var(--brand-primary)"
      >
        <CheckCircle :size="30" weight="fill" aria-hidden="true" />
      </div>
      <p class="mt-6 quiz-kicker" style="color: var(--brand-primary)">You are in the right place</p>
      <h1 class="mt-3 quiz-title">Thanks, {{ form.preferred_name }}.</h1>
      <p class="mx-auto mt-4 max-w-md text-sm leading-7 text-heymo-muted">
        {{ successMessage }}
      </p>
      <a :href="landingUrl" class="landing-button mt-8" style="background-color: var(--brand-primary)"
        >Return to {{ presentation.name }} <ArrowRight :size="17" weight="bold" aria-hidden="true"
      /></a>
    </div>

    <div v-else class="grid gap-6 lg:grid-cols-[minmax(14rem,0.6fr)_minmax(0,1.4fr)] lg:gap-10">
      <aside class="quiz-aside">
        <div class="flex items-center gap-3">
          <span class="flex size-10 items-center justify-center overflow-hidden rounded-md bg-white ring-1 ring-black/10">
            <img
              v-if="presentation.logo_url"
              :src="presentation.logo_url"
              :alt="`${presentation.name} logo`"
              class="size-full object-contain p-1.5"
            />
            <span v-else class="text-sm font-black" style="color: var(--brand-primary)">{{ presentation.name.slice(0, 2).toUpperCase() }}</span>
          </span>
          <span class="text-xs font-black text-heymo-navy" :style="{ fontFamily: presentation.heading_font }">{{ presentation.name }}</span>
        </div>
        <p class="mt-10 quiz-kicker" style="color: var(--brand-primary)">A short check-in</p>
        <h1 class="mt-3 text-3xl font-black leading-tight tracking-tight text-heymo-navy" :style="{ fontFamily: presentation.heading_font }">
          {{ quiz.title }}
        </h1>
        <p class="mt-4 text-sm leading-6 text-heymo-muted">
          {{ quiz.description }}
        </p>
        <div class="mt-10 border-t border-heymo-line pt-5">
          <p class="text-[10px] font-black uppercase tracking-[0.16em] text-heymo-muted">What we will use</p>
          <ul class="mt-3 space-y-3 text-xs font-bold leading-5 text-heymo-ink">
            <li class="flex gap-2">
              <ShieldCheck :size="16" class="mt-0.5 shrink-0" style="color: var(--brand-primary)" aria-hidden="true" />Your answers, in your words
            </li>
            <li class="flex gap-2">
              <ShieldCheck :size="16" class="mt-0.5 shrink-0" style="color: var(--brand-primary)" aria-hidden="true" />A topic-specific next
              conversation
            </li>
            <li class="flex gap-2">
              <ShieldCheck :size="16" class="mt-0.5 shrink-0" style="color: var(--brand-primary)" aria-hidden="true" />Email only after your clear
              consent
            </li>
          </ul>
        </div>
      </aside>

      <section class="quiz-card" aria-labelledby="quiz-step-title">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-2" aria-label="Quiz progress">
            <span
              v-for="(step, index) in steps"
              :key="step.key"
              class="quiz-progress-dot"
              :class="index <= currentStep ? 'quiz-progress-dot-active' : ''"
              :style="index <= currentStep ? { backgroundColor: 'var(--brand-primary)' } : undefined"
            ></span>
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.14em] text-heymo-muted">Step {{ currentStep + 1 }} of {{ steps.length }}</span>
        </div>

        <form class="mt-10" @submit.prevent="handleFormSubmit">
          <div v-if="currentStep === 0">
            <p class="quiz-kicker" style="color: var(--brand-primary)">Start with you</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">How should we call you?</h2>
            <p class="mt-3 text-sm leading-6 text-heymo-muted">Use your first name or a nickname - whichever feels right for this conversation.</p>
            <label for="capture-preferred-name" class="quiz-label mt-8">What should we call you?</label>
            <input
              id="capture-preferred-name"
              v-model="form.preferred_name"
              type="text"
              autocomplete="given-name"
              maxlength="80"
              class="input input-lg mt-2 w-full"
              placeholder="e.g. Jamie or J"
            />
            <p v-if="fieldError('preferred_name')" class="quiz-error">
              {{ fieldError("preferred_name") }}
            </p>
          </div>

          <div v-else-if="currentStep === 1">
            <p class="quiz-kicker" style="color: var(--brand-primary)">A little context about you</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">How can we make this feel relevant?</h2>
            <p class="mt-3 text-sm leading-6 text-heymo-muted">
              Your age group and sex help us choose a useful presentation style. We do not store your exact age or use these details to infer what
              your results mean.
            </p>
            <div class="mt-8 grid gap-5 sm:grid-cols-2">
              <div>
                <label for="capture-age-group" class="quiz-label">Age group</label>
                <select id="capture-age-group" v-model="form.age_group" class="select select-lg mt-2 w-full">
                  <option value="">Choose an age range</option>
                  <option v-for="ageGroup in ageGroups" :key="ageGroup.value" :value="ageGroup.value">
                    {{ ageGroup.label }} - {{ ageGroup.description }}
                  </option>
                </select>
                <p v-if="fieldError('age_group')" class="quiz-error">
                  {{ fieldError("age_group") }}
                </p>
              </div>
              <div>
                <label for="capture-sex" class="quiz-label">Sex</label>
                <select id="capture-sex" v-model="form.sex" class="select select-lg mt-2 w-full">
                  <option value="">Choose one</option>
                  <option value="female">Female</option>
                  <option value="male">Male</option>
                  <option value="intersex">Intersex</option>
                  <option value="prefer_not_to_say">Prefer not to say</option>
                </select>
                <p v-if="fieldError('sex')" class="quiz-error">
                  {{ fieldError("sex") }}
                </p>
              </div>
            </div>
          </div>

          <div v-else-if="currentStep === 2">
            <p class="quiz-kicker" style="color: var(--brand-primary)">Your focus</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">
              {{ quiz.sub_interest.label }}
            </h2>
            <div class="mt-8 space-y-3">
              <button
                v-for="option in quiz.sub_interest.options"
                :key="option.value"
                type="button"
                class="quiz-choice"
                :class="form.sub_interest === option.value ? 'quiz-choice-active' : ''"
                :style="
                  form.sub_interest === option.value
                    ? {
                        borderColor: 'var(--brand-primary)',
                        backgroundColor: 'color-mix(in srgb, var(--brand-primary) 7%, white)',
                      }
                    : undefined
                "
                @click="form.sub_interest = option.value"
              >
                <span
                  class="quiz-choice-radio"
                  :style="
                    form.sub_interest === option.value
                      ? {
                          borderColor: 'var(--brand-primary)',
                          backgroundColor: 'var(--brand-primary)',
                        }
                      : undefined
                  "
                  ><Check :size="13" weight="bold" v-if="form.sub_interest === option.value" aria-hidden="true"
                /></span>
                <span>{{ option.label }}</span>
              </button>
            </div>
            <p v-if="fieldError('sub_interest')" class="quiz-error">
              {{ fieldError("sub_interest") }}
            </p>
          </div>

          <div v-else-if="currentStep === 3">
            <p class="quiz-kicker" style="color: var(--brand-primary)">The moment</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">
              {{ quiz.trigger.label }}
            </h2>
            <div class="mt-8 space-y-3">
              <button
                v-for="option in quiz.trigger.options"
                :key="option.value"
                type="button"
                class="quiz-choice"
                :class="form.trigger === option.value ? 'quiz-choice-active' : ''"
                :style="
                  form.trigger === option.value
                    ? {
                        borderColor: 'var(--brand-primary)',
                        backgroundColor: 'color-mix(in srgb, var(--brand-primary) 7%, white)',
                      }
                    : undefined
                "
                @click="form.trigger = option.value"
              >
                <span
                  class="quiz-choice-radio"
                  :style="
                    form.trigger === option.value
                      ? {
                          borderColor: 'var(--brand-primary)',
                          backgroundColor: 'var(--brand-primary)',
                        }
                      : undefined
                  "
                  ><Check :size="13" weight="bold" v-if="form.trigger === option.value" aria-hidden="true"
                /></span>
                <span>{{ option.label }}</span>
              </button>
            </div>
            <p v-if="fieldError('trigger')" class="quiz-error">
              {{ fieldError("trigger") }}
            </p>
          </div>

          <div v-else-if="currentStep === 4">
            <p class="quiz-kicker" style="color: var(--brand-primary)">Your words</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">
              {{ quiz.concern_label }}
            </h2>
            <textarea
              id="capture-concern"
              v-model="form.concern"
              class="textarea textarea-lg mt-8 min-h-40 w-full"
              maxlength="1000"
              :placeholder="quiz.concern_placeholder"
            ></textarea>
            <div class="mt-2 flex justify-between gap-3 text-[11px] text-heymo-muted">
              <span>Keep it as brief or detailed as you like.</span><span>{{ form.concern.length }}/1000</span>
            </div>
            <p v-if="fieldError('concern')" class="quiz-error">
              {{ fieldError("concern") }}
            </p>
          </div>

          <div v-else>
            <p class="quiz-kicker" style="color: var(--brand-primary)">One clear permission</p>
            <h2 id="quiz-step-title" class="mt-3 quiz-step-title">Where should we send the next step?</h2>
            <p class="mt-3 text-sm leading-6 text-heymo-muted">We will use this address for the campaign conversation connected to your answers.</p>
            <label for="capture-email" class="quiz-label mt-8">Email address</label>
            <div class="relative mt-2">
              <EnvelopeSimple
                :size="18"
                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-heymo-muted"
                aria-hidden="true"
              /><input
                id="capture-email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                class="input input-lg w-full pl-10"
                placeholder="you@example.com"
              />
            </div>
            <p v-if="fieldError('email')" class="quiz-error">
              {{ fieldError("email") }}
            </p>
            <label
              class="mt-6 flex cursor-pointer items-start gap-3 rounded-md border border-heymo-line bg-slate-50 p-4 text-xs leading-5 text-heymo-ink"
              ><input v-model="form.consent" type="checkbox" class="checkbox checkbox-sm mt-0.5" /><span
                >I agree to receive email updates about this campaign. I understand I can withdraw that permission later.</span
              ></label
            >
            <p v-if="fieldError('consent')" class="quiz-error">
              {{ fieldError("consent") }}
            </p>
          </div>

          <div v-if="formError" class="alert alert-soft alert-error mt-6 text-xs font-semibold leading-5">
            {{ formError }}
          </div>
          <div class="mt-10 flex flex-col-reverse justify-between gap-3 border-t border-heymo-line pt-5 sm:flex-row sm:items-center">
            <button
              v-if="currentStep > 0"
              type="button"
              class="btn btn-ghost gap-2 px-0 text-heymo-muted hover:bg-transparent hover:text-heymo-navy"
              @click="previous"
            >
              <ArrowLeft :size="17" weight="bold" aria-hidden="true" />Back
            </button>
            <span v-else class="hidden text-[11px] font-bold text-heymo-muted sm:block">You can go back at any time.</span>
            <button
              type="button"
              class="landing-button justify-center"
              style="background-color: var(--brand-primary)"
              :disabled="status === 'submitting'"
              @click="currentStep === steps.length - 1 ? submit() : next()"
            >
              <span v-if="status === 'submitting'">Saving...</span
              ><span v-else>{{ currentStep === steps.length - 1 ? "Save my answers" : "Continue" }}</span
              ><Check v-if="currentStep === steps.length - 1 && status !== 'submitting'" :size="17" weight="bold" aria-hidden="true" /><ArrowRight
                v-else-if="status !== 'submitting'"
                :size="17"
                weight="bold"
                aria-hidden="true"
              />
            </button>
          </div>
        </form>
      </section>
    </div>
  </section>
</template>

<script setup lang="ts">
import {
  PhArrowLeft as ArrowLeft,
  PhArrowRight as ArrowRight,
  PhCheck as Check,
  PhCheckCircle as CheckCircle,
  PhEnvelopeSimple as EnvelopeSimple,
  PhShieldCheck as ShieldCheck,
} from "@phosphor-icons/vue";
import { computed, reactive, ref } from "vue";
import { ApiError, apiFetch } from "../../lib/auth";
import type { AgeGroupOption, BrandPresentation, CaptureForm, QuizDefinition } from "../../lib/landing";

type Props = {
  ageGroups: AgeGroupOption[];
  brandId: number;
  landingIdentifier: string;
  presentation: BrandPresentation;
  quiz: QuizDefinition;
};

const { ageGroups, brandId, landingIdentifier, presentation, quiz } = defineProps<Props>();
const form = reactive<CaptureForm>({
  preferred_name: "",
  age_group: "",
  sex: "",
  sub_interest: "",
  trigger: "",
  concern: "",
  email: "",
  consent: false,
});
const currentStep = ref(0);
const status = ref<"idle" | "submitting" | "success">("idle");
const errors = ref<Record<string, string>>({});
const formError = ref("");
const successMessage = ref("");
const steps = [{ key: "name" }, { key: "profile" }, { key: "focus" }, { key: "moment" }, { key: "words" }, { key: "permission" }];

const themeStyle = computed(() => ({
  "--brand-primary": presentation.primary_color,
  "--brand-secondary": presentation.secondary_color,
  "--brand-heading": presentation.heading_font,
  "--brand-body": presentation.body_font,
}));
const landingUrl = computed(() => `/angles/${brandId}/${landingIdentifier}`);

function fieldError(field: string): string {
  return errors.value[field] ?? "";
}

function validateStep(): boolean {
  errors.value = {};
  formError.value = "";

  if (currentStep.value === 0) {
    if (!form.preferred_name.trim()) {
      errors.value.preferred_name = "Tell us what you would like us to call you.";
    }
  }

  if (currentStep.value === 1) {
    if (!form.age_group) {
      errors.value.age_group = "Choose an age range to continue.";
    }
    if (!form.sex) {
      errors.value.sex = "Choose an option to continue.";
    }
  }

  if (currentStep.value === 2 && !form.sub_interest) {
    errors.value.sub_interest = "Choose the focus that fits best.";
  }

  if (currentStep.value === 3 && !form.trigger) {
    errors.value.trigger = "Choose what brought you here.";
  }

  if (currentStep.value === 4 && !form.concern.trim()) {
    errors.value.concern = "Add a few words so the next step stays personal.";
  }

  if (currentStep.value === 5) {
    if (!/^\S+@\S+\.\S+$/.test(form.email)) {
      errors.value.email = "Enter a valid email address.";
    }
    if (!form.consent) {
      errors.value.consent = "Email updates require your explicit consent.";
    }
  }

  return Object.keys(errors.value).length === 0;
}

function next(): void {
  if (validateStep()) {
    currentStep.value = Math.min(currentStep.value + 1, steps.length - 1);
  }
}

function handleFormSubmit(): void {
  if (currentStep.value === steps.length - 1) {
    void submit();
    return;
  }

  next();
}

function previous(): void {
  errors.value = {};
  formError.value = "";
  currentStep.value = Math.max(currentStep.value - 1, 0);
}

async function submit(): Promise<void> {
  if (!validateStep()) {
    return;
  }

  status.value = "submitting";

  try {
    const response = await apiFetch<{ message: string }>("/api/capture", {
      method: "POST",
      body: JSON.stringify({
        brand_id: brandId,
        landing_identifier: landingIdentifier,
        preferred_name: form.preferred_name.trim(),
        age_group: form.age_group,
        sex: form.sex,
        sub_interest: form.sub_interest,
        trigger: form.trigger,
        concern: form.concern.trim(),
        email: form.email.trim(),
        consent: form.consent,
      }),
    });

    successMessage.value = response.message;
    status.value = "success";
  } catch (error) {
    status.value = "idle";
    if (error instanceof ApiError) {
      const rawErrors = error.data.errors;
      if (rawErrors && typeof rawErrors === "object" && !Array.isArray(rawErrors)) {
        for (const [field, messages] of Object.entries(rawErrors)) {
          const firstMessage = Array.isArray(messages) ? messages.find((message): message is string => typeof message === "string") : null;
          if (firstMessage) {
            errors.value[field] = firstMessage;
          }
        }
      }
      formError.value = error.message;
      return;
    }

    formError.value = "We could not save those answers. Please try again.";
  }
}
</script>

<style scoped>
.quiz-shell {
  font-family: var(--brand-body);
}

.quiz-aside {
  align-self: start;
  border-top: 4px solid var(--brand-primary);
  padding: 1.25rem 0 0;
}

.quiz-card {
  min-width: 0;
  border: 1px solid var(--color-heymo-line);
  border-radius: 0.5rem;
  background: white;
  padding: 1.5rem;
  box-shadow: 0 18px 45px rgb(17 41 87 / 8%);
}

.quiz-success {
  border-top: 4px solid var(--brand-primary);
  background: white;
  padding: 3.5rem 1.5rem;
  text-align: center;
  box-shadow: 0 18px 45px rgb(17 41 87 / 8%);
}

.quiz-kicker {
  font-size: 0.65rem;
  font-weight: 900;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.quiz-title {
  color: var(--color-heymo-navy);
  font-family: var(--brand-heading);
  font-size: clamp(2.25rem, 6vw, 4rem);
  font-weight: 900;
  letter-spacing: -0.03em;
  line-height: 0.98;
}

.quiz-step-title {
  color: var(--color-heymo-navy);
  font-family: var(--brand-heading);
  font-size: clamp(1.65rem, 3vw, 2.35rem);
  font-weight: 900;
  letter-spacing: -0.02em;
  line-height: 1.05;
}

.quiz-progress-dot {
  display: block;
  height: 0.35rem;
  width: 2.5rem;
  border-radius: 999px;
  background: var(--color-heymo-line);
}

.quiz-choice {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 0.85rem;
  border: 1px solid var(--color-heymo-line);
  border-radius: 0.375rem;
  padding: 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 700;
  line-height: 1.4;
  color: var(--color-heymo-ink);
  transition:
    border-color 160ms ease,
    background-color 160ms ease,
    transform 160ms ease;
}

.quiz-choice:hover {
  border-color: var(--brand-primary);
  transform: translateX(2px);
}

.quiz-choice-radio {
  display: grid;
  height: 1.25rem;
  width: 1.25rem;
  flex: 0 0 auto;
  place-items: center;
  border: 1px solid var(--color-heymo-line);
  border-radius: 999px;
  color: white;
}

.quiz-label {
  display: block;
  color: var(--color-heymo-ink);
  font-size: 0.75rem;
  font-weight: 800;
}

.quiz-error {
  margin-top: 0.4rem;
  color: var(--color-heymo-red);
  font-size: 0.75rem;
  font-weight: 700;
  line-height: 1.4;
}

@media (min-width: 1024px) {
  .quiz-card {
    padding: 2.5rem;
  }

  .quiz-aside {
    position: sticky;
    top: 2rem;
  }
}
</style>
