<template>
  <Transition name="drawer">
    <div v-if="id !== null" class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Visitor journey">
      <button class="absolute inset-0 bg-heymo-navy/45" aria-label="Close details" @click="$emit('close')"></button>

      <aside class="relative flex h-full w-full max-w-2xl flex-col overflow-y-auto bg-white shadow-xl">
        <header class="sticky top-0 z-10 flex items-center justify-between border-b border-heymo-line bg-white px-5 py-4">
          <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-heymo-red">Audit trail</p>
            <h2 class="truncate text-lg font-extrabold text-heymo-navy">
              {{ drawerTitle }}
            </h2>
          </div>
          <button class="btn btn-square btn-ghost btn-sm" aria-label="Close" @click="$emit('close')">
            <X :size="18" weight="bold" aria-hidden="true" />
          </button>
        </header>

        <div v-if="isLoading" class="flex flex-1 items-center justify-center gap-2 p-10 text-sm font-semibold text-heymo-muted">
          <span class="loading loading-spinner loading-sm text-heymo-red" aria-hidden="true"></span>
          Loading journey...
        </div>

        <div v-else-if="errorMessage" class="p-6">
          <div class="alert alert-soft alert-error text-sm">
            {{ errorMessage }}
          </div>
        </div>

        <div v-else-if="detail" class="flex-1 space-y-6 p-5">
          <section class="rounded-lg border border-heymo-line p-4">
            <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Capture</h3>
            <dl class="mt-3 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-2">
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Captured at</dt>
                <dd class="font-semibold text-heymo-navy">
                  {{ formatDate(detail.captured_at) }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Landing page</dt>
                <dd class="font-semibold text-heymo-navy">
                  {{ detail.landing_identifier }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Email</dt>
                <dd class="font-semibold text-heymo-navy">
                  {{ detail.visitor.email }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Age group / sex</dt>
                <dd class="font-semibold text-heymo-navy">{{ detail.age_group }} · {{ detail.sex }}</dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Session duration</dt>
                <dd class="font-semibold text-heymo-navy">
                  {{ formatDuration(detail.session_duration_seconds) }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Fingerprint</dt>
                <dd class="break-all font-mono text-xs text-heymo-muted">
                  {{ detail.fingerprint || "—" }}
                </dd>
              </div>
            </dl>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Attribution</dt>
                <dd class="mt-1 text-xs leading-5 text-heymo-ink">
                  {{ attributionLabel(detail.attribution) }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-semibold text-heymo-muted">Device</dt>
                <dd class="mt-1 text-xs leading-5 text-heymo-ink">
                  {{ deviceLabel(detail.device) }}
                </dd>
              </div>
            </div>
            <p class="mt-4 rounded-md bg-slate-50 p-3 text-xs leading-5 text-heymo-ink"><b>Concern:</b> {{ detail.concern }}</p>
          </section>

          <section class="rounded-lg border border-heymo-line p-4">
            <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Landing events</h3>
            <ul v-if="detail.landing_events.length" class="mt-3 space-y-2">
              <li v-for="event in detail.landing_events" :key="event.id" class="flex flex-wrap items-center justify-between gap-2 text-xs">
                <span class="font-semibold text-heymo-navy">{{ event.landing_identifier }}</span>
                <span class="text-heymo-muted">{{ formatDate(event.landed_at) }}</span>
              </li>
            </ul>
            <p v-else class="mt-3 text-xs text-heymo-muted">No landing events recorded for this fingerprint.</p>
          </section>

          <section class="rounded-lg border border-heymo-line p-4">
            <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Consent &amp; suppression</h3>
            <div class="mt-3 space-y-2">
              <div
                v-for="consent in detail.consent_records"
                :key="`consent-${consent.consented_at}`"
                class="flex flex-wrap items-center justify-between gap-2 text-xs"
              >
                <span class="font-semibold text-heymo-navy">{{ consent.channel }} consent · {{ consent.policy_version }}</span>
                <span class="text-heymo-muted">{{ formatDate(consent.consented_at) }} · {{ consent.source }}</span>
              </div>
              <div
                v-for="suppression in detail.suppressions"
                :key="`suppression-${suppression.suppressed_at}`"
                class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-rose-50 px-3 py-2 text-xs"
              >
                <span class="font-semibold text-heymo-red">Suppressed ({{ suppression.reason }})</span>
                <span class="text-heymo-muted">{{ formatDate(suppression.suppressed_at) }}</span>
              </div>
            </div>
          </section>

          <section v-for="campaign in detail.campaigns" :key="campaign.id" class="rounded-lg border border-heymo-line p-4">
            <div class="flex items-center justify-between gap-3">
              <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Campaign #{{ campaign.id }}</h3>
              <span class="badge badge-soft" :class="campaignStatusClass(campaign.status)">{{ campaign.status }}</span>
            </div>
            <p class="mt-2 text-xs text-heymo-muted">Prompt version: {{ campaign.prompt_version || "—" }}</p>

            <div class="mt-3 space-y-4">
              <div v-for="attempt in campaign.generation_attempts" :key="attempt.attempt_number">
                <p class="text-xs text-heymo-muted">
                  Attempt {{ attempt.attempt_number }} · {{ attempt.provider }}/{{ attempt.model }} · {{ attempt.status }}
                  <span v-if="attempt.error_message"> · {{ attempt.error_message }}</span>
                </p>
                <div v-if="attempt.parsed_messages?.length" class="mt-2">
                  <EmailMessagePreview :messages="attempt.parsed_messages" />
                </div>
                <GuardrailStatusBox class="mt-2" :violations="attempt.violations" />
              </div>
            </div>

            <div class="mt-3 space-y-3">
              <div v-for="message in campaign.messages" :key="message.id" class="rounded-md border border-heymo-line p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <span class="text-xs font-bold text-heymo-navy">#{{ message.sequence_position }} · {{ message.role }}</span>
                  <span class="badge badge-soft" :class="messageStatusClass(message.status)">{{ message.status }}</span>
                </div>
                <p class="mt-2 text-[11px] text-heymo-muted">
                  Scheduled {{ formatDate(message.scheduled_at) }} · Sent {{ formatDate(message.sent_at) }} · Opened
                  {{ formatDate(message.opened_at) }}
                </p>
                <div class="mt-2">
                  <EmailMessagePreview :messages="[message]" />
                </div>
                <GuardrailStatusBox class="mt-2" :violations="null" />
                <div class="mt-2 flex flex-wrap gap-1">
                  <span v-for="event in message.delivery_events" :key="`delivery-${event.created_at}`" class="badge badge-outline badge-xs">{{
                    event.status
                  }}</span>
                  <span
                    v-for="event in message.engagement_events"
                    :key="`engagement-${event.occurred_at}`"
                    class="badge badge-soft badge-info badge-xs"
                    >{{ event.type }}</span
                  >
                </div>
              </div>
            </div>
          </section>
        </div>
      </aside>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { PhX as X } from "@phosphor-icons/vue";
import { computed, ref, watch } from "vue";
import { ApiError, apiFetch } from "../../lib/auth";
import EmailMessagePreview from "./EmailMessagePreview.vue";
import GuardrailStatusBox from "./GuardrailStatusBox.vue";
import type { AuditDetail, AuditDetailResponse } from "../../lib/audit";

const props = defineProps<{ id: number | null }>();

defineEmits<{ close: [] }>();

const detail = ref<AuditDetail | null>(null);
const isLoading = ref(false);
const errorMessage = ref("");

const drawerTitle = computed(() => detail.value?.visitor?.preferred_name || detail.value?.visitor?.email || "Audit trail");

watch(
  () => props.id,
  id => {
    detail.value = null;
    errorMessage.value = "";

    if (id === null) {
      return;
    }

    void loadDetail(id);
  },
  { immediate: true },
);

async function loadDetail(id: number): Promise<void> {
  isLoading.value = true;
  errorMessage.value = "";

  try {
    const response = await apiFetch<AuditDetailResponse>(`/api/audit/${id}`);
    detail.value = response.data;
  } catch (error) {
    errorMessage.value = error instanceof ApiError ? error.message : "Could not load this journey.";
  } finally {
    isLoading.value = false;
  }
}

function formatDate(value: string | null): string {
  if (!value) {
    return "—";
  }

  const date = new Date(value);

  return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
}

function formatDuration(seconds: number | null): string {
  if (seconds === null || seconds === undefined) {
    return "—";
  }

  if (seconds < 60) {
    return `${seconds}s`;
  }

  const minutes = Math.floor(seconds / 60);

  return `${minutes}m ${seconds % 60}s`;
}

function attributionLabel(attribution: Record<string, string> | null): string {
  if (!attribution || Object.keys(attribution).length === 0) {
    return "direct";
  }

  const parts: string[] = [];

  if (attribution.utm_source) {
    parts.push(`source: ${attribution.utm_source}`);
  }

  if (attribution.utm_medium) {
    parts.push(`medium: ${attribution.utm_medium}`);
  }

  if (attribution.utm_campaign) {
    parts.push(`campaign: ${attribution.utm_campaign}`);
  }

  if (attribution.referrer) {
    parts.push(`referrer: ${attribution.referrer}`);
  }

  return parts.join(" · ") || "direct";
}

function deviceLabel(device: Record<string, string> | null): string {
  if (!device || Object.keys(device).length === 0) {
    return "—";
  }

  return [device.type, device.os, device.browser].filter(Boolean).join(" · ");
}

function campaignStatusClass(status: string): string {
  return status === "generated" ? "badge-success" : status === "failed" ? "badge-error" : "badge-warning";
}

function messageStatusClass(status: string): string {
  return status === "sent" ? "badge-success" : status === "failed" || status === "skipped" ? "badge-error" : "badge-warning";
}
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 180ms ease;
}

.drawer-enter-active aside,
.drawer-leave-active aside {
  transition: transform 200ms ease;
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from aside,
.drawer-leave-to aside {
  transform: translateX(100%);
}
</style>
