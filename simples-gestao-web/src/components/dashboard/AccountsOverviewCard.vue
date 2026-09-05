<script setup>
import { Info, ExternalLink } from '@lucide/vue';
import { formatCurrency } from '@/utils/formatters';

defineProps({
  title: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
  totalAmount: {
    type: [Number, String],
    default: 0,
  },
  type: {
    type: String,
    default: 'pay', // 'pay' (pagar/red) or 'receive' (receber/green)
  }
});
</script>

<template>
  <div class="bg-white rounded-lg border border-gray-200/90 p-4 shadow-xs flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
      <div class="flex items-center gap-1 font-semibold text-gray-800">
        <span>{{ title }}</span>
        <Info :size="12" class="text-gray-400 cursor-pointer" />
      </div>
      <ExternalLink :size="12" class="text-gray-400 cursor-pointer" />
    </div>

    <!-- Items List -->
    <div class="space-y-1.5 text-xs py-1">
      <div
        v-for="item in items"
        :key="item.label"
        class="flex items-center justify-between"
      >
        <span class="text-gray-500 text-[11px]">{{ item.label }}</span>
        <span
          class="font-semibold"
          :class="[
            item.highlight
              ? (type === 'pay' ? 'text-rose-600' : 'text-emerald-600')
              : 'text-gray-800'
          ]"
        >
          {{ formatCurrency(item.amount) }}
        </span>
      </div>
    </div>

    <!-- Total Footer -->
    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
      <span class="text-gray-500 font-medium">Saldo Total:</span>
      <span class="font-extrabold text-sm text-gray-900">{{ formatCurrency(totalAmount) }}</span>
    </div>
  </div>
</template>
