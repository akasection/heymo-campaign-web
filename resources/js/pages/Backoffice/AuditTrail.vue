<template>
  <div class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
      <div>
        <h2 class="text-2xl font-extrabold text-heymo-navy">Audit trail</h2>
        <p class="mt-1 max-w-2xl text-sm text-heymo-muted">Every quiz submission, with its source, device, and delivery outcome.</p>
      </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-4">
      <div>
        <label for="audit-brand" class="text-[11px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Brand</label>
        <select id="audit-brand" v-model="trail.brandId.value" class="select select-bordered select-sm mt-1 w-full" @change="onBrandChange">
          <option :value="null">All brands</option>
          <option v-for="brand in brandOptions" :key="brand.id" :value="brand.id">
            {{ brand.name }}
          </option>
        </select>
      </div>
      <div>
        <label for="audit-angle" class="text-[11px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Angle</label>
        <select
          id="audit-angle"
          v-model="trail.angleId.value"
          class="select select-bordered select-sm mt-1 w-full"
          :disabled="!trail.brandId.value"
          @change="trail.resetAndReload()"
        >
          <option :value="null">All angles</option>
          <option v-for="angle in angleOptions" :key="angle.id" :value="angle.id">
            {{ angle.name }}
          </option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label for="audit-search" class="text-[11px] font-bold uppercase tracking-[0.08em] text-heymo-muted">Search</label>
        <div class="mt-1 flex gap-2">
          <input
            id="audit-search"
            v-model="trail.search.value"
            type="search"
            class="input input-bordered input-sm w-full"
            placeholder="Email or name"
            @keyup.enter="trail.resetAndReload()"
          />
          <button class="btn btn-primary btn-sm" @click="trail.resetAndReload()">
            <MagnifyingGlass :size="16" weight="bold" aria-hidden="true" />
          </button>
        </div>
      </div>
    </div>

    <div v-if="trail.errorMessage.value" class="alert alert-soft alert-error mb-4 text-sm">
      {{ trail.errorMessage.value }}
    </div>

    <Panel class="overflow-hidden" padding-class="p-0">
      <div class="overflow-x-auto">
        <table class="table table-sm w-full min-w-240 text-left text-sm">
          <thead class="border-y border-heymo-line bg-slate-50 text-[11px] uppercase tracking-[0.08em] text-heymo-muted">
            <tr>
              <th class="px-5 py-3 font-bold">Captured</th>
              <th class="px-3 py-3 font-bold">Visitor</th>
              <th class="px-3 py-3 font-bold">Brand</th>
              <th class="px-3 py-3 font-bold">Angle</th>
              <th class="px-3 py-3 font-bold">Source</th>
              <th class="px-3 py-3 font-bold">Device</th>
              <th class="px-3 py-3 text-right font-bold">Session</th>
              <th class="px-3 py-3 text-right font-bold">Campaigns</th>
              <th class="px-3 py-3 text-right font-bold">Sent</th>
              <th class="px-3 py-3 text-right font-bold">Opened</th>
              <th class="px-5 py-3 text-right font-bold"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-heymo-line">
            <tr v-if="trail.isLoading.value">
              <td colspan="11" class="px-5 py-8 text-center">
                <span class="loading loading-spinner loading-sm text-heymo-red" aria-hidden="true"></span>
              </td>
            </tr>
            <tr v-else-if="trail.records.value.length === 0">
              <td colspan="11" class="px-5 py-8 text-center text-sm text-heymo-muted">No submissions match these filters.</td>
            </tr>
            <tr
              v-for="record in trail.records.value"
              v-else
              :key="record.id"
              class="cursor-pointer transition hover:bg-slate-50"
              @click="openRecord(record)"
            >
              <td class="px-5 py-3 whitespace-nowrap text-heymo-muted">
                {{ formatDate(record.captured_at) }}
              </td>
              <td class="px-3 py-3 font-bold text-heymo-navy">
                {{ record.visitor.preferred_name || "—" }}
              </td>
              <td class="px-3 py-3 text-heymo-muted">
                {{ record.brand.name }}
              </td>
              <td class="px-3 py-3 text-heymo-ink">{{ record.angle.name }}</td>
              <td class="px-3 py-3 text-heymo-muted">
                {{ sourceLabel(record.attribution) }}
              </td>
              <td class="px-3 py-3 text-heymo-muted">
                {{ deviceShort(record.device) }}
              </td>
              <td class="px-3 py-3 text-right tabular-nums text-heymo-muted">
                {{ formatDuration(record.session_duration_seconds) }}
              </td>
              <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">{{ record.campaigns_generated_count }}/{{ record.campaigns_count }}</td>
              <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">
                {{ record.messages_sent_count }}
              </td>
              <td class="px-3 py-3 text-right tabular-nums text-heymo-ink">
                {{ record.messages_opened_count }}
              </td>
              <td class="px-5 py-3 text-right">
                <CaretRight :size="16" weight="bold" class="text-heymo-muted" aria-hidden="true" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Panel>

    <div class="mt-4">
      <Pagination
        v-if="trail.meta.value"
        :current-page="trail.meta.value.current_page"
        :last-page="trail.meta.value.last_page"
        @change="trail.goToPage"
      />
    </div>

    <AuditDetailDrawer :record="selectedRecord" @close="selectedRecord = null" />
  </div>
</template>

<script setup lang="ts">
import { PhCaretRight as CaretRight, PhMagnifyingGlass as MagnifyingGlass } from "@phosphor-icons/vue";
import { onMounted, ref } from "vue";
import AuditDetailDrawer from "../../components/Backoffice/AuditDetailDrawer.vue";
import Pagination from "../../components/Backoffice/Pagination.vue";
import Panel from "../../components/Backoffice/Panel.vue";
import { useAuditTrail } from "../../composables/useAuditTrail";
import { apiFetch } from "../../lib/auth";
import type { AuditRecord } from "../../lib/audit";
import type { Brand } from "../../lib/brands";

type AngleOption = { id: number; name: string; slug: string };

const trail = useAuditTrail();
const brandOptions = ref<Brand[]>([]);
const angleOptions = ref<AngleOption[]>([]);
const selectedRecord = ref<AuditRecord | null>(null);

async function loadBrands(): Promise<void> {
  try {
    const response = await apiFetch<{ data: Brand[] }>("/api/brands");
    brandOptions.value = response.data;
  } catch {
    brandOptions.value = [];
  }
}

async function loadAngles(brandId: number | null): Promise<void> {
  if (!brandId) {
    angleOptions.value = [];

    return;
  }

  try {
    const response = await apiFetch<{ data: AngleOption[] }>(`/api/brands/${brandId}/angles`);
    angleOptions.value = response.data;
  } catch {
    angleOptions.value = [];
  }
}

function onBrandChange(): void {
  trail.angleId.value = null;
  void loadAngles(trail.brandId.value);
  trail.resetAndReload();
}

function openRecord(record: AuditRecord): void {
  selectedRecord.value = record;
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

  return `${Math.floor(seconds / 60)}m ${seconds % 60}s`;
}

function sourceLabel(attribution: Record<string, string> | null): string {
  if (!attribution || Object.keys(attribution).length === 0) {
    return "direct";
  }

  return attribution.utm_source ?? (attribution.referrer ? "referral" : "direct");
}

function deviceShort(device: Record<string, string> | null): string {
  if (!device || Object.keys(device).length === 0) {
    return "—";
  }

  return device.type ?? device.os ?? "—";
}

onMounted(() => {
  void loadBrands();
  void trail.load();
});
</script>
