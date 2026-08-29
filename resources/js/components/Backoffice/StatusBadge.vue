<template>
  <span class="badge badge-sm gap-1.5 font-bold" :class="toneClass">
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
      neutral: "badge-soft badge-neutral",
      positive: "badge-soft badge-success",
      warning: "badge-soft badge-warning",
      danger: "badge-soft badge-error",
      navy: "badge-soft badge-info",
    })[tone],
);
</script>
