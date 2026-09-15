<template>
  <div
    class="rounded-md border px-3 py-2 text-xs"
    :class="violated ? 'border-red-200 bg-red-50 text-heymo-red' : 'border-emerald-200 bg-emerald-50 text-heymo-positive'"
  >
    <div v-if="violated" class="space-y-1">
      <p class="font-bold uppercase tracking-[0.08em]">Guardrail violations</p>
      <ul class="list-inside list-disc space-y-0.5">
        <li v-for="(violation, index) in violations" :key="index">{{ violation }}</li>
      </ul>
    </div>
    <p v-else class="flex items-center gap-1.5 font-semibold">
      <CheckCircle :size="14" weight="bold" aria-hidden="true" />
      No violations
    </p>
  </div>
</template>

<script setup lang="ts">
import { PhCheckCircle as CheckCircle } from "@phosphor-icons/vue";
import { computed } from "vue";

const props = defineProps<{ violations: string[] | null }>();

const violated = computed(() => Boolean(props.violations?.length));
</script>
