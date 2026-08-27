<template>
  <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold" :class="toneClass">
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { StatusTone } from "../../pages/Backoffice/dashboard";

const { tone } = defineProps<{
  label: string;
  tone: StatusTone;
}>();

const toneClass = computed(
  () =>
    ({
      neutral: "bg-slate-100 text-slate-600",
      positive: "bg-emerald-50 text-heymo-positive",
      warning: "bg-amber-50 text-heymo-warning",
      danger: "bg-rose-50 text-heymo-red",
      navy: "bg-blue-50 text-heymo-navy",
    })[tone],
);
</script>
