<template>
  <div class="space-y-3">
    <div v-for="(message, index) in messages" :key="index" class="overflow-hidden rounded-md border border-heymo-line bg-white">
      <div class="border-b border-heymo-line bg-slate-50 px-4 py-3">
        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-heymo-muted">
          {{ messageLabel(message, index) }}
        </p>
        <p class="mt-1 text-sm font-semibold text-heymo-ink">Subject: {{ message.subject }}</p>
      </div>
      <div class="px-4 py-3">
        <p class="text-base font-bold text-heymo-navy">{{ message.headline }}</p>
        <div class="mt-2 space-y-2">
          <p v-for="(paragraph, paragraphIndex) in message.body_paragraphs" :key="paragraphIndex" class="text-sm leading-6 text-heymo-ink">
            {{ paragraph }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ParsedMessage } from "../../lib/audit";

defineProps<{ messages: ParsedMessage[] }>();

function messageLabel(message: ParsedMessage, index: number): string {
  if (message.role) {
    return `#${message.position ?? index + 1} · ${message.role}`;
  }

  return "Email preview";
}
</script>
