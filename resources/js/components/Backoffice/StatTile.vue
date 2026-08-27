<template>
  <article class="heymo-panel min-w-0 p-4 sm:p-5">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-heymo-muted">
          {{ label }}
        </p>
        <p class="mt-2 text-2xl font-extrabold tabular-nums text-heymo-navy">
          {{ value }}
        </p>
      </div>
      <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-heymo-sky text-heymo-navy">
        <component :is="icon" :size="20" weight="bold" aria-hidden="true" />
      </span>
    </div>
    <p class="mt-4 flex items-center gap-1 text-xs font-semibold" :class="trendClass">
      <TrendUp v-if="trend === 'up'" :size="15" weight="bold" aria-hidden="true" />
      <TrendDown v-else :size="15" weight="bold" aria-hidden="true" />
      {{ change }}
    </p>
  </article>
</template>

<script setup lang="ts">
import { PhTrendDown as TrendDown, PhTrendUp as TrendUp } from "@phosphor-icons/vue";
import { computed } from "vue";
import type { Component } from "vue";

const { trend } = defineProps<{
  label: string;
  value: string;
  change: string;
  trend: "up" | "down";
  icon: Component;
}>();

const trendClass = computed(() => (trend === "up" ? "text-heymo-positive" : "text-heymo-red"));
</script>
