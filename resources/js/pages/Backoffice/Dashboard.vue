<template>
  <div class="min-h-screen bg-heymo-canvas">
    <Transition name="fade">
      <button
        v-if="isMobileNavOpen"
        class="fixed inset-0 z-30 bg-heymo-navy/45 lg:hidden"
        aria-label="Close navigation"
        @click="isMobileNavOpen = false"
      ></button>
    </Transition>
    <Transition name="drawer">
      <div v-if="isMobileNavOpen" class="fixed inset-y-0 left-0 z-40 lg:hidden">
        <SidebarNav
          :items="navigationItems"
          :active-id="activeNavigation"
          :user-name="currentUser?.name ?? 'Workspace user'"
          :organization-name="currentUser?.organization?.name ?? 'Campaign operations'"
          @select="selectNavigation"
          @close="isMobileNavOpen = false"
        />
      </div>
    </Transition>

    <div class="flex min-h-screen">
      <div class="hidden shrink-0 lg:block">
        <SidebarNav
          :items="navigationItems"
          :active-id="activeNavigation"
          :user-name="currentUser?.name ?? 'Workspace user'"
          :organization-name="currentUser?.organization?.name ?? 'Campaign operations'"
          @select="selectNavigation"
          @close="isMobileNavOpen = false"
        />
      </div>

      <main class="min-w-0 flex-1">
        <header
          class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-heymo-line bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8"
        >
          <div class="flex min-w-0 items-center gap-3">
            <button
              class="btn btn-square btn-ghost h-10 w-10 border border-base-300 bg-base-100 lg:hidden"
              aria-label="Open navigation"
              @click="isMobileNavOpen = true"
            >
              <List :size="21" weight="bold" aria-hidden="true" />
            </button>
            <div class="min-w-0">
              <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-heymo-red">Campaign operations</p>
              <h1 class="truncate text-lg font-extrabold text-heymo-navy sm:text-xl">
                {{ activeNavigationLabel }}
              </h1>
            </div>
          </div>
          <div class="flex items-center gap-2 sm:gap-3">
            <button class="btn btn-square btn-ghost relative h-10 w-10 border border-base-300 bg-base-100" aria-label="View notifications">
              <Bell :size="20" weight="bold" aria-hidden="true" />
              <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-heymo-red ring-2 ring-white" aria-hidden="true"></span>
            </button>
            <button
              class="btn btn-outline btn-sm h-auto min-h-10 px-2.5 py-2 text-left disabled:cursor-wait disabled:opacity-60"
              :aria-label="isSigningOut ? 'Signing out' : 'Sign out'"
              :disabled="isSigningOut"
              title="Sign out"
              @click="logout"
            >
              <span class="flex size-7 items-center justify-center rounded-full bg-heymo-navy text-[10px] font-bold text-white">{{
                currentUserInitials
              }}</span>
              <span class="hidden max-w-32 truncate text-xs font-bold text-heymo-navy sm:block">{{ currentUser?.name ?? "Workspace user" }}</span>
              <SignOut :size="16" weight="bold" class="text-heymo-muted" aria-hidden="true" />
            </button>
          </div>
        </header>

        <BrandManagement v-if="isAuthReady && activeNavigation === 'brands'" :can-manage="canManageBackoffice" />

        <div v-else-if="isAuthReady" class="mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
          <div class="mb-6 flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
            <div>
              <h2 class="text-2xl font-extrabold text-heymo-navy">Campaign pulse</h2>
              <p class="mt-1 max-w-2xl text-sm text-heymo-muted">A live view of the sample journey across today&apos;s active cohorts.</p>
            </div>
            <div class="flex items-center gap-2">
              <button class="btn btn-outline btn-sm">
                <CalendarBlank :size="18" weight="bold" aria-hidden="true" />
                This week
                <CaretDown :size="14" weight="bold" aria-hidden="true" />
              </button>
              <button v-if="canManageBackoffice" class="btn btn-primary btn-sm">
                <Plus :size="18" weight="bold" aria-hidden="true" />
                <span class="hidden sm:inline">New campaign</span>
                <span class="sm:hidden">New</span>
              </button>
            </div>
          </div>

          <div class="grid gap-4 xl:grid-cols-12 xl:gap-5">
            <Panel title="Active sample pickup locations" description="10 live pickup points across 4 campaigns." class="xl:col-span-7">
              <template #action>
                <button class="btn btn-link btn-xs h-auto min-h-0 gap-1 px-0 text-heymo-red">
                  View map
                  <ArrowUpRight :size="15" weight="bold" aria-hidden="true" />
                </button>
              </template>
              <div
                class="relative h-64 overflow-hidden rounded-md border border-blue-100 bg-[#eef4fb] sm:h-72"
                aria-label="Map of active sample pickup locations"
              >
                <div
                  class="absolute inset-0 opacity-60"
                  style="
                    background-image:
                      linear-gradient(28deg, transparent 48%, #c9d9ed 49%, #c9d9ed 51%, transparent 52%),
                      linear-gradient(112deg, transparent 47%, #d4e1f1 48%, #d4e1f1 50%, transparent 51%),
                      linear-gradient(0deg, transparent 49%, #dbe6f3 50%, transparent 51%);
                    background-size:
                      190px 130px,
                      220px 170px,
                      100% 70px;
                  "
                ></div>
                <div
                  class="absolute left-[8%] top-[20%] h-[65%] w-[78%] rounded-[45%_55%_48%_52%/48%_35%_65%_52%] border border-blue-200/80 bg-white/25"
                ></div>
                <span
                  v-for="location in sampleLocations"
                  :key="location.id"
                  class="absolute z-10 flex size-5 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full ring-4 ring-white/75"
                  :class="location.status === 'collected' ? 'bg-heymo-navy' : 'bg-heymo-red'"
                  :style="{ top: location.top, left: location.left }"
                  :title="`${location.id}: ${location.status}`"
                >
                  <span class="size-1.5 rounded-full bg-white" aria-hidden="true"></span>
                </span>
                <div
                  class="absolute bottom-3 left-3 flex items-center gap-3 rounded bg-white/95 px-3 py-2 text-[11px] font-bold text-heymo-muted shadow-sm"
                >
                  <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-heymo-navy"></span> Collected</span>
                  <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-heymo-red"></span> Pending</span>
                </div>
              </div>
            </Panel>

            <div class="grid gap-4 sm:grid-cols-3 xl:col-span-5 xl:grid-cols-1">
              <StatTile v-for="(metric, index) in dashboardMetrics" :key="metric.label" v-bind="metric" :icon="metricIcons[index]" />
            </div>

            <Panel
              title="Samples processed"
              description="Collection and laboratory progress by campaign."
              class="overflow-hidden xl:col-span-7"
              padding-class="p-0"
            >
              <div class="overflow-x-auto">
                <table class="table table-sm w-full min-w-155 text-left text-sm">
                  <thead class="border-y border-heymo-line bg-slate-50 text-[11px] uppercase tracking-[0.08em] text-heymo-muted">
                    <tr>
                      <th class="px-5 py-3 font-bold">Campaign</th>
                      <th class="px-3 py-3 font-bold">Planned</th>
                      <th class="px-3 py-3 font-bold">Collected</th>
                      <th class="px-3 py-3 font-bold">Completion</th>
                      <th class="px-5 py-3 text-right font-bold">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-heymo-line">
                    <tr v-for="sample in sampleRows" :key="sample.campaign" class="transition hover:bg-slate-50">
                      <td class="px-5 py-3.5 font-bold text-heymo-navy">
                        {{ sample.campaign }}
                      </td>
                      <td class="px-3 py-3.5 tabular-nums text-heymo-muted">
                        {{ sample.planned }}
                      </td>
                      <td class="px-3 py-3.5 tabular-nums text-heymo-ink">
                        {{ sample.collected }}
                      </td>
                      <td class="px-3 py-3.5">
                        <div class="flex min-w-28 items-center gap-2">
                          <progress class="progress progress-primary h-1.5 flex-1" :value="sample.completionValue" max="100">
                            {{ sample.completion }}
                          </progress>
                          <span class="w-8 text-xs font-bold tabular-nums text-heymo-muted">{{ sample.completion }}</span>
                        </div>
                      </td>
                      <td class="px-5 py-3.5 text-right">
                        <StatusBadge :label="sample.status" :tone="sample.tone" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </Panel>

            <Panel title="Sampling rhythm" description="Samples entering the lab each day." class="xl:col-span-5">
              <div class="flex h-44 items-end gap-2 border-b border-l border-heymo-line px-3 pb-4 pt-5">
                <div v-for="(value, index) in trendValues" :key="index" class="group flex flex-1 flex-col items-center justify-end gap-2">
                  <span class="invisible rounded bg-heymo-navy px-1.5 py-0.5 text-[10px] font-bold text-white group-hover:visible">{{ value }}</span>
                  <span class="w-full max-w-5 rounded-t bg-heymo-sky transition group-hover:bg-heymo-red" :style="{ height: `${value}%` }"></span>
                  <span class="text-[9px] font-bold text-heymo-muted">{{ ["M", "T", "W", "T", "F", "S", "S", "M", "T", "W"][index] }}</span>
                </div>
              </div>
              <div class="mt-4 flex items-center justify-between text-xs">
                <span class="font-bold text-heymo-navy">Weekly target: 680</span>
                <span class="font-bold text-heymo-positive">On track</span>
              </div>
            </Panel>

            <Panel title="Campaign progress" description="Delivery status across the sample journey." class="xl:col-span-4">
              <div class="space-y-5">
                <div v-for="item in campaignProgress" :key="item.label">
                  <div class="mb-2 flex justify-between gap-3 text-xs font-bold">
                    <span class="text-heymo-ink">{{ item.label }}</span
                    ><span class="tabular-nums text-heymo-muted">{{ item.value }}%</span>
                  </div>
                  <progress class="progress progress-primary h-2 w-full" :value="item.value" max="100">{{ item.value }}%</progress>
                </div>
              </div>
              <div class="mt-6 flex items-center gap-3 rounded-md bg-heymo-sky px-3 py-3 text-xs leading-5 text-heymo-navy">
                <Pulse :size="19" weight="bold" class="shrink-0 text-heymo-red" aria-hidden="true" />
                Participation is 9% ahead of this time last cycle.
              </div>
            </Panel>

            <Panel title="Campaign health" description="A balanced cohort remains the goal." class="xl:col-span-3">
              <div class="mx-auto grid aspect-square max-w-48 place-items-center rounded-full border border-dashed border-heymo-line bg-slate-50 p-5">
                <div class="relative grid size-full place-items-center rounded-full border border-heymo-line">
                  <div class="absolute size-[66%] rotate-30 border border-heymo-red/50 bg-heymo-red/10 hex-tile"></div>
                  <span class="relative text-center text-xs font-bold leading-4 text-heymo-navy"
                    >87<br /><span class="font-medium text-heymo-muted">health score</span></span
                  >
                </div>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 text-[11px] font-semibold text-heymo-muted">
                <span>Reach <b class="float-right text-heymo-navy">92</b></span>
                <span>Consent <b class="float-right text-heymo-navy">88</b></span>
                <span>Collection <b class="float-right text-heymo-navy">79</b></span>
                <span>Retention <b class="float-right text-heymo-navy">86</b></span>
              </div>
            </Panel>

            <Panel title="Recent alerts" class="xl:col-span-2" padding-class="p-4">
              <div class="space-y-3">
                <div v-for="alert in activeAlerts" :key="alert.id" class="flex items-start gap-2.5">
                  <span
                    class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full"
                    :class="
                      alert.tone === 'danger'
                        ? 'bg-rose-50 text-heymo-red'
                        : alert.tone === 'warning'
                          ? 'bg-amber-50 text-heymo-warning'
                          : 'bg-emerald-50 text-heymo-positive'
                    "
                  >
                    <WarningCircle v-if="alert.tone !== 'positive'" :size="15" weight="fill" aria-hidden="true" />
                    <CheckCircle v-else :size="15" weight="fill" aria-hidden="true" />
                  </span>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-heymo-ink">
                      {{ alert.title }}
                    </p>
                    <p class="mt-0.5 text-[11px] leading-4 text-heymo-muted">
                      {{ alert.detail }}
                    </p>
                  </div>
                  <button
                    class="text-heymo-muted hover:text-heymo-red focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
                    :aria-label="`Dismiss ${alert.title}`"
                    @click="dismissAlert(alert.id)"
                  >
                    <X :size="15" weight="bold" aria-hidden="true" />
                  </button>
                </div>
              </div>
            </Panel>

            <Panel title="Priority" class="xl:col-span-3" padding-class="p-4">
              <ul class="space-y-3">
                <li v-for="priority in priorityItems" :key="priority.label" class="flex items-center justify-between text-xs">
                  <span class="flex items-center gap-2 font-bold text-heymo-ink"
                    ><span
                      class="size-2 rounded-full"
                      :class="priority.tone === 'warning' ? 'bg-heymo-warning' : priority.tone === 'positive' ? 'bg-heymo-positive' : 'bg-heymo-navy'"
                    ></span
                    >{{ priority.label }}</span
                  >
                  <span class="font-extrabold tabular-nums text-heymo-navy">{{ priority.value }}</span>
                </li>
              </ul>
            </Panel>

            <Panel title="Validation flow" description="Select any station to focus today's review." class="xl:col-span-8">
              <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-4">
                <HexValidationTile
                  v-for="step in validationSteps"
                  :key="step.id"
                  v-bind="step"
                  :selected="selectedValidationStep === step.id"
                  @select="selectValidationStep"
                />
              </div>
              <p class="mt-4 text-xs text-heymo-muted">
                <b class="text-heymo-navy">{{ selectedValidation.label }}</b> is
                {{ selectedValidation.state === "active" ? "the current attention point" : selectedValidation.state }}.
              </p>
            </Panel>
          </div>
        </div>
        <div v-else class="mx-auto grid min-h-96 max-w-[1600px] place-items-center p-4 sm:p-6 lg:p-8">
          <div class="flex items-center gap-3 text-sm font-semibold text-heymo-muted" aria-label="Loading workspace">
            <span class="loading loading-spinner loading-sm text-heymo-red" aria-hidden="true"></span>
            Loading workspace...
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  PhArrowUpRight as ArrowUpRight,
  PhBell as Bell,
  PhCalendarBlank as CalendarBlank,
  PhCaretDown as CaretDown,
  PhChartLineUp as ChartLineUp,
  PhCheckCircle as CheckCircle,
  PhClipboardText as ClipboardText,
  PhDrop as Drop,
  PhFlask as Flask,
  PhList as List,
  PhMapPin as MapPin,
  PhPlus as Plus,
  PhPulse as Pulse,
  PhSignOut as SignOut,
  PhSquaresFour as SquaresFour,
  PhUsersThree as UsersThree,
  PhWarningCircle as WarningCircle,
  PhX as X,
} from "@phosphor-icons/vue";
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import type { Component } from "vue";
import HexValidationTile from "../../components/Backoffice/HexValidationTile.vue";
import Panel from "../../components/Backoffice/Panel.vue";
import SidebarNav from "../../components/Backoffice/SidebarNav.vue";
import StatTile from "../../components/Backoffice/StatTile.vue";
import StatusBadge from "../../components/Backoffice/StatusBadge.vue";
import { ApiError, apiFetch, clearAccessToken, saveAccessToken } from "../../lib/auth";
import BrandManagement from "./BrandManagement.vue";
import {
  campaignProgress,
  dashboardMetrics,
  initialValidationSteps,
  priorityItems,
  recentAlerts,
  sampleLocations,
  sampleRows,
  trendValues,
} from "./dashboard";
import type { AuthUser, AuthenticationResponse } from "../../lib/auth";

const navigationItems: {
  id: string;
  label: string;
  icon: Component;
  count?: string;
}[] = [
  { id: "dashboard", label: "Dashboard", icon: SquaresFour },
  { id: "brands", label: "Brands", icon: Drop },
  { id: "campaigns", label: "Campaigns", icon: ChartLineUp, count: "18" },
  { id: "samples", label: "Samples", icon: Flask, count: "433" },
  { id: "participants", label: "Participants", icon: UsersThree },
  { id: "locations", label: "Locations", icon: MapPin },
  { id: "reports", label: "Reports", icon: ClipboardText },
];

const metricIcons = [Flask, ChartLineUp, Drop];
const activeAlerts = ref([...recentAlerts]);
const isMobileNavOpen = ref(false);
const selectedValidationStep = ref(3);
const validationSteps = ref([...initialValidationSteps]);
const currentUser = ref<AuthUser | null>(null);
const isSigningOut = ref(false);
const isAuthReady = ref(false);
const route = useRoute();
const router = useRouter();

const navigationRoutes: Record<string, string> = {
  dashboard: "admin.dashboard",
  brands: "admin.brands",
  campaigns: "admin.campaigns",
  samples: "admin.samples",
  participants: "admin.participants",
  locations: "admin.locations",
  reports: "admin.reports",
};

const activeNavigation = computed(() => (typeof route.meta.navigationId === "string" ? route.meta.navigationId : "dashboard"));
const activeNavigationLabel = computed(() => navigationItems.find(item => item.id === activeNavigation.value)?.label ?? "Dashboard");
const selectedValidation = computed(() => validationSteps.value.find(step => step.id === selectedValidationStep.value) ?? validationSteps.value[0]);
const canManageBackoffice = computed(() => currentUser.value?.capabilities.includes("backoffice.manage") ?? false);
const currentUserInitials = computed(() => {
  const name = currentUser.value?.name ?? "Workspace user";
  return name
    .split(" ")
    .map(part => part[0])
    .filter(Boolean)
    .slice(0, 2)
    .join("")
    .toUpperCase();
});

async function loadAuthenticatedUser() {
  try {
    const response = await apiFetch<AuthenticationResponse>("/api/auth/token", {
      method: "POST",
    });
    saveAccessToken(response.access_token);
    currentUser.value = response.user;
  } catch (error) {
    if (error instanceof ApiError && error.status === 401) {
      globalThis.location.assign("/login");
    }
  } finally {
    isAuthReady.value = true;
  }
}

async function logout() {
  isSigningOut.value = true;

  try {
    await apiFetch<{ message: string }>("/api/auth/logout", { method: "POST" });
  } finally {
    clearAccessToken();
    globalThis.location.assign("/login");
  }
}

function selectNavigation(id: string) {
  isMobileNavOpen.value = false;
  router.push({ name: navigationRoutes[id] ?? navigationRoutes.dashboard });
}

function dismissAlert(id: number) {
  activeAlerts.value = activeAlerts.value.filter(alert => alert.id !== id);
}

function selectValidationStep(id: number) {
  selectedValidationStep.value = id;
}

onMounted(loadAuthenticatedUser);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 180ms ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: transform 200ms ease;
}

.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(-100%);
}
</style>
