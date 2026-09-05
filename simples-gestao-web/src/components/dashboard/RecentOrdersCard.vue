<script setup>
import { ShoppingBag, ExternalLink } from '@lucide/vue';
import { formatCurrency, formatDate } from '@/utils/formatters';
import { useRouter } from 'vue-router';

const router = useRouter();

defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
});

const statusConfig = {
  confirmed: { label: 'Confirmado', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
  delivered: { label: 'Entregue', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
  pending: { label: 'Pendente', bg: 'bg-amber-50 text-amber-700 border-amber-200' },
  cancelled: { label: 'Cancelado', bg: 'bg-rose-50 text-rose-700 border-rose-200' },
};
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
      <div class="flex items-center gap-1.5 font-bold text-gray-800">
        <ShoppingBag :size="15" class="text-simples-orange" />
        <span>Últimos Pedidos / Vendas</span>
      </div>
      <button
        type="button"
        class="flex items-center gap-1 text-[11px] font-semibold text-simples-orange hover:text-simples-orange-hover cursor-pointer"
        @click="router.push('/orders')"
      >
        <span>Ver todos</span>
        <ExternalLink :size="12" />
      </button>
    </div>

    <!-- Lista de Pedidos -->
    <div class="divide-y divide-gray-100 flex-1">
      <div
        v-for="order in orders"
        :key="order.id"
        class="py-2.5 flex items-center justify-between gap-3 text-xs hover:bg-gray-50/50 rounded-lg px-1.5 -mx-1.5 transition-colors"
      >
        <div class="min-w-0 space-y-0.5">
          <div class="flex items-center gap-2">
            <span class="font-bold text-gray-900 font-mono text-[11px]">{{ order.order_number }}</span>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
              :class="statusConfig[order.status]?.bg || 'bg-gray-50 text-gray-600 border-gray-200'"
            >
              {{ statusConfig[order.status]?.label || order.status }}
            </span>
          </div>
          <p class="text-gray-500 text-[11px] truncate">{{ order.customer_name }}</p>
        </div>

        <div class="text-right flex-shrink-0">
          <div class="font-bold text-gray-900">{{ formatCurrency(order.total) }}</div>
          <span class="text-[10px] text-gray-400 font-medium">{{ formatDate(order.created_at) }}</span>
        </div>
      </div>

      <div v-if="orders.length === 0" class="py-8 text-center text-xs text-gray-400">
        Nenhum pedido recente.
      </div>
    </div>
  </div>
</template>
