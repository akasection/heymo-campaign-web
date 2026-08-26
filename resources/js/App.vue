<script setup>
import { ref } from "vue";

const appName = "Heymo! Campaign Webapp";
const stack = ["Laravel 9", "Vue 3", "PostgreSQL 18", "Valkey 8"];
const count = ref(0);
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-950 p-6 text-slate-100">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/60 p-8 shadow-2xl">
      <p class="text-xs uppercase tracking-widest text-emerald-400">Campaign webapp</p>
      <h1 class="mt-2 text-2xl font-semibold">{{ appName }}</h1>
      <p class="mt-3 text-sm text-slate-400">Vue 3 is live. The backend is connected to PostgreSQL 18 and Valkey 8 via Docker.</p>

      <ul class="mt-4 flex flex-wrap gap-2">
        <li v-for="s in stack" :key="s" class="rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-300">
          {{ s }}
        </li>
      </ul>

      <div class="mt-6 flex items-center gap-3">
        <button @click="count++" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-emerald-400">
          Count: {{ count }}
        </button>
        <p class="text-xs text-slate-500">Reactivity is working</p>
      </div>
    </div>
  </div>
</template>
