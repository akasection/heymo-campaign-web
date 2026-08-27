<template>
  <button
    class="hex-tile relative flex aspect-square w-full min-w-0 flex-col items-center justify-center px-3 text-center transition focus:outline-2 focus:outline-offset-2 focus:outline-heymo-red"
    :class="stateClass"
    :aria-pressed="selected"
    @click="$emit('select', id)"
  >
    <CheckCircle v-if="state === 'complete'" :size="21" weight="fill" aria-hidden="true" />
    <WarningCircle v-else-if="state === 'active'" :size="21" weight="fill" aria-hidden="true" />
    <Clock v-else :size="20" weight="bold" aria-hidden="true" />
    <span class="mt-1 text-[11px] font-bold leading-3">{{ label }}</span>
  </button>
</template>

<script setup lang="ts">
import { PhCheckCircle as CheckCircle, PhClock as Clock, PhWarningCircle as WarningCircle } from "@phosphor-icons/vue";
import { computed } from "vue";

const { state, selected } = defineProps<{
  id: number;
  label: string;
  state: "complete" | "active" | "waiting";
  selected: boolean;
}>();

defineEmits<{
  select: [id: number];
}>();

const stateClass = computed(() => {
  if (state === "complete") {
    return selected ? "bg-heymo-navy text-white ring-2 ring-heymo-red" : "bg-heymo-navy text-white";
  }

  if (state === "active") {
    return selected ? "bg-heymo-red text-white ring-2 ring-heymo-navy" : "bg-heymo-red text-white";
  }

  return selected ? "bg-heymo-sky text-heymo-navy ring-2 ring-heymo-red" : "bg-slate-100 text-heymo-muted";
});
</script>
