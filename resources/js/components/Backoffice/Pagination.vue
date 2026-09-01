<template>
  <nav v-if="lastPage > 1" class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Pagination">
    <p class="text-xs font-semibold text-heymo-muted">Page {{ currentPage }} of {{ lastPage }}</p>
    <div class="join">
      <button class="btn btn-outline btn-sm join-item" :disabled="currentPage <= 1" @click="$emit('change', currentPage - 1)">Previous</button>
      <button
        v-for="pageNumber in pages"
        :key="pageNumber"
        class="btn btn-sm join-item"
        :class="pageNumber === currentPage ? 'btn-primary' : 'btn-outline'"
        :aria-current="pageNumber === currentPage ? 'page' : undefined"
        @click="$emit('change', pageNumber)"
      >
        {{ pageNumber }}
      </button>
      <button class="btn btn-outline btn-sm join-item" :disabled="currentPage >= lastPage" @click="$emit('change', currentPage + 1)">Next</button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  currentPage: number;
  lastPage: number;
}>();

defineEmits<{
  change: [page: number];
}>();

const pages = computed(() => {
  const delta = 2;
  const start = Math.max(1, props.currentPage - delta);
  const end = Math.min(props.lastPage, props.currentPage + delta);
  const range: number[] = [];

  for (let page = start; page <= end; page++) {
    range.push(page);
  }

  return range;
});
</script>
