<template>
  <div class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
      <div>
        <h2 class="text-2xl font-extrabold text-heymo-navy">Campaign performance</h2>
        <p class="mt-1 max-w-2xl text-sm text-heymo-muted">The funnel from landing to send, compared across angles.</p>
      </div>
      <div class="w-full max-w-xs">
        <label for="dashboard-brand" class="text-[11px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Brand</label>
        <select id="dashboard-brand" v-model="selectedBrandId" class="select select-bordered select-sm mt-1 w-full" @change="reload">
          <option :value="0">All brands</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
        </select>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-soft alert-error mb-4 text-sm">{{ errorMessage }}</div>

    <div v-if="isLoading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="index in 8" :key="index" class="skeleton h-24 rounded-lg"></div>
    </div>

    <template v-else-if="overall">
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article v-for="metric in metrics" :key="metric.label" class="card card-border min-w-0 bg-base-100 p-4 text-base-content shadow-panel">
          <p class="text-xs font-semibold uppercase tracking-[0.08em] text-heymo-muted">{{ metric.label }}</p>
          <p class="mt-2 text-2xl font-extrabold tabular-nums text-heymo-navy">{{ metric.value }}</p>
        </article>
      </div>

      <Panel title="Funnel" description="How visitors move from landing to a sent email." class="mt-6">
        <div class="space-y-4">
          <div v-for="step in funnel" :key="step.key">
            <div class="mb-1 flex items-center justify-between text-xs font-bold">
              <span class="text-heymo-ink">{{ step.label }}</span>
              <span class="tabular-nums text-heymo-muted">{{ step.count }}<span v-if="step.rate !== null"> · {{ step.rate }}%</span></span>
            </div>
            <progress class="progress progress-primary h-2 w-full" :value="step.rate ?? 0" max="100">{{ step.rate ?? 0 }}</progress>
          </div>
        </div>
      </Panel>

      <Panel title="Angle comparison" description="Side-by-side performance per angle." class="mt-6 overflow-hidden" padding-class="p-0">
        <div class="overflow-x-auto">
          <table class="table table-sm w-full min-w-225 text-left text-sm">
            <thead class="border-y border-heymo-line bg-slate-50 text-[11px] uppercase tracking-[0.08em] text-heymo-muted">
              <tr>
                <th class="px-5 py-3 font-bold">Angle</th>
                <th class="px-3 py-3 font-bold">Brand</th>
                <th class="px-3 py-3 text-right font-bold">Landed</th>
                <th class="px-3 py-3 text-right font-bold">Captures</th>
                <th class="px-3 py-3 text-right font-bold">Consented</th>
                <th class="px-3 py-3 text-right font-bold">Consent %</th>
                <th class="px-3 py-3 text-right font-bold">Generated</th>
                <th class="px-3 py-3 text-right font-bold">Sent</th>
                <th class="px-3 py-3 text-right font-bold">Delivery %</th>
                <th class="px-3 py-3 text-right font-bold">Opens</th>
                <th class="px-5 py-3 text-right font-bold">Open %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-heymo-line">
              <tr v-for="angle in angles" :key="angle.id" class="transition hover:bg-slate-50">
                <td class="px-5 py-3 font-bold text-heymo-navy">{{ angle.name }}</td>
                <td class="px-3 py-3 text-heymo-muted">{{ brandName(angle.brand_id) }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-muted">{{ angle.landed_visitors }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ angle.captures }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ angle.consented_visitors }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-muted">{{ rate(angle.consent_rate) }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ angle.campaigns_generated }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ angle.messages_sent }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-muted">{{ rate(angle.delivery_success_rate) }}</td>
                <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ angle.messages_opened }}</td>
                <td class="px-5 py-3 text-right tabular-nums text-heymo-muted">{{ rate(angle.open_rate) }}</td>
              </tr>
              <tr v-if="angles.length === 0">
                <td colspan="11" class="px-5 py-6 text-center text-sm text-heymo-muted">No angles with activity yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </Panel>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import Panel from "../../components/Backoffice/Panel.vue";
import { useDashboard } from "../../composables/useDashboard";
import type { AngleMetric, FunnelStep, OverallMetrics } from "../../lib/dashboard";

const { data, isLoading, errorMessage, load } = useDashboard();

const selectedBrandId = ref(0);

const brands = computed(() => data.value?.brands ?? []);
const overall = computed<OverallMetrics | null>(() => data.value?.overall ?? null);
const funnel = computed<FunnelStep[]>(() => data.value?.funnel ?? []);
const angles = computed<AngleMetric[]>(() => data.value?.angles ?? []);

const metrics = computed(() => {
  const o = overall.value;

  if (!o) {
    return [];
  }

  return [
    { label: "Landed visitors", value: String(o.landed_visitors) },
    { label: "Captures", value: String(o.captures) },
    { label: "Consented", value: String(o.consented_visitors) },
    { label: "Campaigns generated", value: String(o.campaigns_generated) },
    { label: "Emails sent", value: String(o.messages_sent) },
    { label: "Opens", value: String(o.messages_opened) },
    { label: "Open rate", value: rate(o.open_rate) },
    { label: "Active campaigns", value: String(o.active_campaigns) },
  ];
});

function reload(): void {
  void load(selectedBrandId.value || undefined);
}

function rate(value: number | null): string {
  return value === null ? "—" : `${value}%`;
}

function brandName(brandId: number): string {
  return brands.value.find(brand => brand.id === brandId)?.name ?? "—";
}

onMounted(() => {
  void load();
});
</script>
