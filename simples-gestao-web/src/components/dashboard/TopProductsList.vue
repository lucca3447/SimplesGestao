<script setup>
import { useRouter } from 'vue-router';
import { Info, ExternalLink, Package } from '@lucide/vue';
import { formatCurrency } from '@/utils/formatters';

const router = useRouter();

defineProps({
  products: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
      <div class="flex items-center gap-1.5 font-bold text-gray-800">
        <span>Produtos Mais Vendidos</span>
        <Info :size="13" class="text-gray-400 cursor-help" title="Itens com maior volume de faturamento em vendas confirmadas" />
      </div>
      <button
        type="button"
        title="Acessar catálogo de produtos"
        class="text-gray-400 hover:text-simples-orange transition-colors cursor-pointer"
        @click="router.push('/products')"
      >
        <ExternalLink :size="13" />
      </button>
    </div>

    <!-- Lista de Produtos -->
    <div class="divide-y divide-gray-100 flex-1">
      <div
        v-for="(product, index) in products"
        :key="product.id"
        class="py-2.5 flex items-center justify-between gap-3 text-xs"
      >
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="w-5 h-5 rounded-md bg-gray-100 text-gray-500 font-bold flex items-center justify-center text-[10px] flex-shrink-0">
            {{ index + 1 }}
          </span>
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ product.name }}</p>
            <span class="text-[10px] text-gray-400 font-mono">{{ product.sku }}</span>
          </div>
        </div>

        <div class="text-right flex-shrink-0">
          <div class="font-bold text-gray-900">{{ formatCurrency(product.total_revenue) }}</div>
          <span class="text-[10px] text-emerald-600 font-medium">{{ product.total_quantity }} un vendidas</span>
        </div>
      </div>

      <div v-if="products.length === 0" class="py-8 text-center text-xs text-gray-400">
        Nenhuma venda registrada ainda.
      </div>
    </div>
  </div>
</template>
