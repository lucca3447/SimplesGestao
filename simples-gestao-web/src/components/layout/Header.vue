<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { Calendar } from '@lucide/vue';

const route = useRoute();
const auth = useAuthStore();

const pageTitle = computed(() => {
  return route.meta?.title || 'Painel de Gestão';
});

const todayFormatted = computed(() => {
  return new Intl.DateTimeFormat('pt-BR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  }).format(new Date());
});
</script>

<template>
  <header class="h-20 bg-white border-b border-gray-200/80 px-8 flex items-center justify-between sticky top-0 z-10 shadow-sm/50">
    <!-- Left: Page Title -->
    <div>
      <h2 class="text-xl font-bold text-gray-900 tracking-tight">{{ pageTitle }}</h2>
      <div class="flex items-center gap-1.5 text-xs text-gray-400 capitalize mt-0.5">
        <Calendar :size="13" />
        <span>{{ todayFormatted }}</span>
      </div>
    </div>

    <!-- Right: Status / User -->
    <div class="flex items-center gap-4">
      <slot name="actions" />

      <div class="h-8 w-px bg-gray-200" v-if="$slots.actions" />

      <div class="flex items-center gap-2.5 bg-gray-50 border border-gray-200/70 py-1.5 px-3 rounded-full">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
        <span class="text-xs font-medium text-gray-600">Online</span>
      </div>
    </div>
  </header>
</template>
