<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between gap-2">
      <h3 class="text-xs font-bold uppercase tracking-[0.08em] text-heymo-muted">Preview</h3>
      <span v-if="preview" class="truncate text-xs font-semibold text-heymo-muted">{{ preview.label }}</span>
    </div>

    <div
      v-if="loading"
      class="flex items-center justify-center gap-2 rounded-md border border-heymo-line p-10 text-sm font-semibold text-heymo-muted"
    >
      <span class="loading loading-spinner loading-sm text-heymo-red" aria-hidden="true"></span>
      Loading preview...
    </div>

    <div v-else-if="error" class="alert alert-soft alert-error text-sm">
      {{ error }}
    </div>

    <div v-else-if="preview" class="space-y-3">
      <p class="text-sm font-semibold text-heymo-ink">Subject: {{ preview.subject }}</p>
      <GuardrailStatusBox :violations="preview.violations" />
      <div class="overflow-hidden rounded-md border border-heymo-line bg-white">
        <iframe :srcdoc="preview.html" class="h-[72vh] w-full" title="Email preview"></iframe>
      </div>
    </div>

    <div v-else class="rounded-md border border-dashed border-heymo-line p-10 text-center text-sm text-heymo-muted">Select an email to preview.</div>
  </div>
</template>

<script setup lang="ts">
import GuardrailStatusBox from "./GuardrailStatusBox.vue";
import type { EmailPreviewPayload } from "../../lib/audit";

defineProps<{
  preview: EmailPreviewPayload | null;
  loading: boolean;
  error: string;
}>();
</script>
