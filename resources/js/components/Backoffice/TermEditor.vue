<template>
  <div>
    <label :for="`${field}-input`" class="mb-2 block text-xs font-bold text-heymo-ink">{{ label }}</label>
    <div class="flex gap-2">
      <input
        :id="`${field}-input`"
        :value="inputValue"
        type="text"
        class="input input-md min-w-0 flex-1"
        :placeholder="placeholder"
        @input="updateInput"
        @keydown="handleKeydown"
      />
      <button type="button" class="btn btn-outline btn-sm shrink-0" aria-label="Add phrase" @click="emit('add')">Add</button>
    </div>
    <p class="mt-1.5 text-[11px] text-heymo-muted">Press Enter or comma to add.</p>
    <p v-if="error" class="mt-1.5 text-xs font-semibold text-heymo-red">
      {{ error }}
    </p>
    <ul v-if="terms.length" class="mt-3 flex flex-wrap gap-2" :aria-label="label">
      <li
        v-for="(term, index) in terms"
        :key="`${term}-${index}`"
        class="badge badge-soft badge-secondary h-auto max-w-full gap-1 py-1 pl-2.5 pr-1 text-[11px] font-bold"
      >
        <span class="max-w-44 truncate">{{ term }}</span>
        <button
          type="button"
          class="btn btn-circle btn-ghost btn-xs text-heymo-muted hover:text-heymo-red"
          :aria-label="`Remove ${term}`"
          @click="emit('remove', index)"
        >
          x
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import type { BrandTermField } from "../../composables/useBrandForm";

defineProps<{
  field: BrandTermField;
  label: string;
  placeholder: string;
  terms: string[];
  inputValue: string;
  error: string;
}>();

const emit = defineEmits<{
  "update:inputValue": [value: string];
  add: [];
  remove: [index: number];
}>();

function updateInput(event: Event): void {
  if (event.target instanceof HTMLInputElement) {
    emit("update:inputValue", event.target.value);
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === "Enter" || event.key === ",") {
    event.preventDefault();
    emit("add");
  }
}
</script>
