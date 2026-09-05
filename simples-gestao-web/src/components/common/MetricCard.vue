<script setup>
import { Info, ExternalLink } from '@lucide/vue';

defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  color: {
    type: String,
    default: 'green', // green, red, blue, orange, purple
  },
});

const colorClasses = {
  green: 'bg-emerald-50 text-emerald-600',
  red: 'bg-rose-50 text-rose-500',
  blue: 'bg-sky-50 text-sky-600',
  orange: 'bg-amber-50 text-amber-600',
  purple: 'bg-purple-50 text-purple-600',
};
</script>

<template>
  <div class="bg-white rounded-lg border border-gray-200/90 p-4 shadow-xs flex flex-col justify-between hover:border-gray-300 transition-all">
    <!-- Header: Título + ⓘ + ↗ -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
      <div class="flex items-center gap-1 font-medium text-gray-700 truncate">
        <span>{{ title }}</span>
        <Info :size="12" class="text-gray-400 cursor-pointer hover:text-gray-600 flex-shrink-0" />
      </div>
      <ExternalLink :size="12" class="text-gray-400 cursor-pointer hover:text-gray-600 flex-shrink-0" />
    </div>

    <!-- Body: Ícone em círculo pastel + Valor grande + Rótulo de período -->
    <div class="flex flex-col items-center justify-center py-2 text-center">
      <!-- Ícone em círculo pastel -->
      <div
        v-if="$slots.icon"
        :class="`w-8 h-8 rounded-full flex items-center justify-center mb-2.5 ${colorClasses[color] || colorClasses.green}`"
      >
        <slot name="icon" />
      </div>

      <!-- Valor grande -->
      <div class="text-xl lg:text-2xl font-extrabold text-gray-900 tracking-tight">
        {{ value }}
      </div>

      <!-- Rótulo do período (ex: Fevereiro, Mês atual) -->
      <span v-if="subtitle" class="text-[11px] text-gray-400 mt-1 font-medium">
        {{ subtitle }}
      </span>
    </div>
  </div>
</template>
