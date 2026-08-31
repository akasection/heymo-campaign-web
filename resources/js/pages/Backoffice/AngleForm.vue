<template>
  <Panel
    :title="angle ? `Edit ${angle.name}` : 'New angle'"
    :description="
      angle
        ? 'Refine the campaign strategy without changing its stable landing slug.'
        : 'Define the strategy that a future campaign will carry forward.'
    "
  >
    <form class="space-y-7" @submit.prevent="save">
      <div class="rounded-md border border-heymo-line bg-heymo-sky px-4 py-3 text-xs leading-5 text-heymo-navy">
        <p class="font-extrabold">Angle strategy sits inside the Brand voice.</p>
        <p class="mt-1 text-heymo-muted">Use this form for the audience theory and the approved path forward. It is not a free-form prompt.</p>
      </div>

      <div>
        <label for="angle-name" class="mb-2 block text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Angle name</label>
        <input
          id="angle-name"
          v-model="draft.name"
          type="text"
          maxlength="120"
          class="input input-md w-full"
          placeholder="e.g. Train from a clearer baseline"
        />
        <p v-if="fieldError('name')" class="mt-1.5 text-xs font-semibold text-heymo-red">
          {{ fieldError("name") }}
        </p>
        <p v-else class="mt-1.5 text-[11px] text-heymo-muted">The URL-safe slug is generated once from this name and stays stable after saving.</p>
      </div>

      <fieldset class="space-y-4 border-t border-heymo-line pt-6">
        <legend class="text-sm font-bold text-heymo-navy">Landing page</legend>
        <div class="rounded-md border border-heymo-line bg-slate-50 px-4 py-3 text-xs leading-5 text-heymo-muted">
          Choose which hand-authored page should route visitors into this Angle. One active Angle can own each page for this Brand.
        </div>
        <div>
          <label for="angle-landing" class="mb-2 block text-xs font-bold text-heymo-ink">Assigned page</label>
          <select id="angle-landing" v-model="selectedLandingIdentifier" class="select select-md w-full" :disabled="isLoadingOptions">
            <option value="">No landing page assigned</option>
            <option
              v-for="page in options?.landing_pages ?? []"
              :key="page.value"
              :value="page.value"
              :disabled="page.assigned_angle_id !== null && page.assigned_angle_id !== angle?.id"
            >
              {{ page.label
              }}{{ page.assigned_angle_id !== null && page.assigned_angle_id !== angle?.id ? ` (Used by ${page.assigned_angle_name})` : "" }}
            </option>
          </select>
          <p v-if="selectedLandingPage?.description" class="mt-1.5 text-[11px] leading-4 text-heymo-muted">{{ selectedLandingPage.description }}</p>
          <p v-if="fieldError('landing_identifier')" class="mt-1.5 text-xs font-semibold text-heymo-red">{{ fieldError("landing_identifier") }}</p>
        </div>
      </fieldset>

      <fieldset class="space-y-4">
        <legend class="text-sm font-bold text-heymo-navy">The situation</legend>
        <div class="grid gap-4 lg:grid-cols-2">
          <div>
            <label for="angle-audience" class="mb-2 block text-xs font-bold text-heymo-ink">Audience</label>
            <textarea
              id="angle-audience"
              v-model="draft.audience"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="Who is this for, and what separates them from the general market?"
            ></textarea>
            <p v-if="fieldError('audience')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("audience") }}
            </p>
          </div>
          <div>
            <label for="angle-trigger" class="mb-2 block text-xs font-bold text-heymo-ink">Trigger moment</label>
            <textarea
              id="angle-trigger"
              v-model="draft.trigger_moment"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="What happened just before they became receptive?"
            ></textarea>
            <p v-if="fieldError('trigger_moment')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("trigger_moment") }}
            </p>
          </div>
          <div>
            <label for="angle-job" class="mb-2 block text-xs font-bold text-heymo-ink">Primary job</label>
            <textarea
              id="angle-job"
              v-model="draft.primary_job"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="What progress are they trying to make?"
            ></textarea>
            <p v-if="fieldError('primary_job')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("primary_job") }}
            </p>
          </div>
          <div>
            <label for="angle-tension" class="mb-2 block text-xs font-bold text-heymo-ink">Tension</label>
            <textarea
              id="angle-tension"
              v-model="draft.tension"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="What is frustrating, worrying, confusing, or unresolved?"
            ></textarea>
            <p v-if="fieldError('tension')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("tension") }}
            </p>
          </div>
          <div>
            <label for="angle-outcome" class="mb-2 block text-xs font-bold text-heymo-ink">Desired outcome</label>
            <textarea
              id="angle-outcome"
              v-model="draft.desired_outcome"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="What does better look and feel like to them?"
            ></textarea>
            <p v-if="fieldError('desired_outcome')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("desired_outcome") }}
            </p>
          </div>
          <div>
            <label for="angle-promise" class="mb-2 block text-xs font-bold text-heymo-ink">Single promise</label>
            <textarea
              id="angle-promise"
              v-model="draft.single_promise"
              class="textarea min-h-28 w-full"
              maxlength="2000"
              placeholder="What can we credibly help them achieve?"
            ></textarea>
            <p v-if="fieldError('single_promise')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("single_promise") }}
            </p>
          </div>
          <div class="lg:col-span-2">
            <label for="angle-objection" class="mb-2 block text-xs font-bold text-heymo-ink">Angle-specific objection</label>
            <textarea
              id="angle-objection"
              v-model="draft.objection"
              class="textarea min-h-24 w-full"
              maxlength="2000"
              placeholder="What would stop this person believing or acting?"
            ></textarea>
            <p v-if="fieldError('objection')" class="mt-1.5 text-xs font-semibold text-heymo-red">
              {{ fieldError("objection") }}
            </p>
          </div>
        </div>
      </fieldset>

      <fieldset class="space-y-4 border-t border-heymo-line pt-6">
        <legend class="text-sm font-bold text-heymo-navy">Approved path forward</legend>
        <div class="rounded-md border border-heymo-line bg-slate-50 px-4 py-3 text-xs leading-5 text-heymo-muted">
          Proof, offer, and next step are deterministic inputs. They are reviewed by the server and are not written by a model.
        </div>
        <div>
          <label for="angle-proof" class="mb-2 block text-xs font-bold text-heymo-ink">Proof block or evidence reference</label>
          <textarea
            id="angle-proof"
            v-model="draft.proof"
            class="textarea min-h-24 w-full"
            maxlength="2000"
            placeholder="Approved block: state the exact evidence or mechanism this angle may use."
          ></textarea>
          <p v-if="fieldError('proof')" class="mt-1.5 text-xs font-semibold text-heymo-red">
            {{ fieldError("proof") }}
          </p>
        </div>
        <div>
          <label for="angle-offer" class="mb-2 block text-xs font-bold text-heymo-ink">Offer</label>
          <textarea
            id="angle-offer"
            v-model="draft.offer"
            class="textarea min-h-24 w-full"
            maxlength="2000"
            placeholder="What makes acting now easier, stated precisely and truthfully?"
          ></textarea>
          <p v-if="fieldError('offer')" class="mt-1.5 text-xs font-semibold text-heymo-red">
            {{ fieldError("offer") }}
          </p>
        </div>
        <div>
          <label for="angle-next-step" class="mb-2 block text-xs font-bold text-heymo-ink">Next step</label>
          <textarea
            id="angle-next-step"
            v-model="draft.next_step"
            class="textarea min-h-24 w-full"
            maxlength="2000"
            placeholder="What is the smallest clear action they should take?"
          ></textarea>
          <p v-if="fieldError('next_step')" class="mt-1.5 text-xs font-semibold text-heymo-red">
            {{ fieldError("next_step") }}
          </p>
        </div>
        <div>
          <label for="angle-tone" class="mb-2 block text-xs font-bold text-heymo-ink">Angle tone</label>
          <select id="angle-tone" v-model="draft.tone" class="select select-md w-full" :disabled="isLoadingOptions">
            <option v-for="tone in options?.tones ?? []" :key="tone.value" :value="tone.value">
              {{ tone.label }}
            </option>
          </select>
          <p v-if="selectedToneDescription" class="mt-1.5 text-[11px] leading-4 text-heymo-muted">
            {{ selectedToneDescription }}
          </p>
          <p v-if="fieldError('tone')" class="mt-1.5 text-xs font-semibold text-heymo-red">
            {{ fieldError("tone") }}
          </p>
        </div>
      </fieldset>

      <div v-if="formError" class="alert alert-soft alert-error px-3 py-2.5 text-xs font-semibold leading-5">
        {{ formError }}
      </div>

      <div class="flex flex-col-reverse justify-end gap-2 border-t border-heymo-line pt-5 sm:flex-row">
        <button type="button" class="btn btn-outline" :disabled="isSaving" @click="$emit('cancel')">Cancel</button>
        <button type="submit" class="btn btn-primary" :disabled="isSaving || isLoadingOptions">
          {{ isSaving ? "Saving..." : angle ? "Save changes" : "Create angle" }}
        </button>
      </div>
    </form>
  </Panel>
</template>

<script setup lang="ts">
import { computed, onMounted, toRef } from "vue";
import Panel from "../../components/Backoffice/Panel.vue";
import { useAngleForm } from "../../composables/useAngleForm";
import type { Angle, AngleOptions, AnglePayload } from "../../lib/angles";

const props = defineProps<{
  angle: Angle | null;
  loadOptions: () => Promise<AngleOptions>;
  saveAngle: (payload: AnglePayload, angleId?: number) => Promise<Angle>;
}>();

const emit = defineEmits<{
  cancel: [];
  saved: [angle: Angle];
}>();

const sourceAngle = toRef(props, "angle");
const { draft, options, errors, formError, isLoadingOptions, isSaving, load, submit } = useAngleForm(sourceAngle, props.loadOptions);

const selectedLandingIdentifier = computed({
  get: () => draft.value.landing_identifier ?? "",
  set: value => {
    draft.value.landing_identifier = value || null;
  },
});
const selectedLandingPage = computed(() => options.value?.landing_pages.find(page => page.value === draft.value.landing_identifier) ?? null);
const selectedToneDescription = computed(() => options.value?.tones.find(tone => tone.value === draft.value.tone)?.description ?? "");

function fieldError(field: string): string {
  return errors.value[field]?.[0] ?? "";
}

async function save(): Promise<void> {
  const savedAngle = await submit(props.saveAngle);
  if (savedAngle) {
    emit("saved", savedAngle);
  }
}

onMounted(load);
</script>
