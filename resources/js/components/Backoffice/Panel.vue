<template>
  <section class="card card-border bg-base-100 text-base-content shadow-panel" :class="paddingClass">
    <header v-if="title || $slots.action" class="mb-4 flex items-start justify-between gap-3">
      <div>
        <h2 v-if="title" class="text-sm font-bold text-heymo-navy">
          {{ title }}
        </h2>
        <p v-if="description" class="mt-1 text-xs leading-5 text-heymo-muted">
          {{ description }}
        </p>
      </div>
      <slot name="action" />
    </header>
    <slot />
  </section>
</template>

<script setup lang="ts">
const {
  title = "",
  description = "",
  paddingClass = "p-4 sm:p-5",
} = defineProps<{
  title?: string;
  description?: string;
  paddingClass?: string;
}>();
</script>
