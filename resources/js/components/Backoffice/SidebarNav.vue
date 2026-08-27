<template>
  <aside class="flex h-full w-72 flex-col bg-heymo-navy text-white lg:w-64">
    <div class="flex h-20 items-center justify-between border-b border-white/10 px-6">
      <div class="flex items-center gap-2.5">
        <span class="flex size-8 items-center justify-center rounded-lg bg-heymo-red text-white">
          <Drop :size="20" weight="fill" aria-hidden="true" />
        </span>
        <span class="text-xl font-extrabold tracking-[0.02em]">heymo<span class="text-heymo-red">!</span></span>
      </div>
      <button class="heymo-icon-button border-white/20 bg-transparent text-white lg:hidden" aria-label="Close navigation" @click="$emit('close')">
        <X :size="20" weight="bold" aria-hidden="true" />
      </button>
    </div>

    <nav class="flex-1 px-3 py-5" aria-label="Backoffice navigation">
      <p class="px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-blue-200/70">Workspace</p>
      <ul class="mt-3 space-y-1">
        <li v-for="item in items" :key="item.id">
          <button
            class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm font-semibold transition focus:outline-2 focus:outline-offset-2 focus:outline-white"
            :class="item.id === activeId ? 'bg-white text-heymo-navy shadow-sm' : 'text-blue-100 hover:bg-white/10'"
            :aria-current="item.id === activeId ? 'page' : undefined"
            @click="$emit('select', item.id)"
          >
            <component :is="item.icon" :size="19" weight="bold" aria-hidden="true" />
            <span class="flex-1">{{ item.label }}</span>
            <span
              v-if="item.count"
              class="rounded-full px-2 py-0.5 text-[10px] font-bold"
              :class="item.id === activeId ? 'bg-heymo-sky text-heymo-navy' : 'bg-white/10 text-white'"
            >
              {{ item.count }}
            </span>
          </button>
        </li>
      </ul>
    </nav>

    <div class="border-t border-white/10 p-4">
      <button
        class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-left text-sm font-semibold text-blue-100 transition hover:bg-white/10 focus:outline-2 focus:outline-offset-2 focus:outline-white"
      >
        <Gear :size="19" weight="bold" aria-hidden="true" />
        Settings
      </button>
      <div class="mt-4 flex items-center gap-3 px-2">
        <span class="flex size-9 items-center justify-center rounded-full bg-white/15 text-sm font-bold">LM</span>
        <div class="min-w-0">
          <p class="truncate text-sm font-bold">Lena Morgan</p>
          <p class="truncate text-xs text-blue-200">Campaign operations</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { PhDrop as Drop, PhGear as Gear, PhX as X } from "@phosphor-icons/vue";
import type { Component } from "vue";

defineProps<{
  items: { id: string; label: string; icon: Component; count?: string }[];
  activeId: string;
}>();

defineEmits<{
  select: [id: string];
  close: [];
}>();
</script>
