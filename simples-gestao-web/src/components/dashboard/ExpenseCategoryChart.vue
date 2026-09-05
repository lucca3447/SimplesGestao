<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js';
import { Info, ExternalLink } from '@lucide/vue';
import { formatCurrency } from '@/utils/formatters';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const palette = [
  '#208b5d', '#e06236', '#38bdf8', '#fbbf24', '#a855f7',
  '#f43f5e', '#64748b', '#06b6d4', '#84cc16'
];

const chartData = computed(() => {
  const labels = props.categories.map(c => c.name);
  const amounts = props.categories.map(c => c.amount);

  return {
    labels,
    datasets: [
      {
        data: amounts,
        backgroundColor: palette.slice(0, labels.length),
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        boxWidth: 10,
        font: { family: 'Plus Jakarta Sans', size: 10 },
        color: '#4b5563',
      },
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const item = props.categories[context.dataIndex];
          return ` ${item.name}: ${formatCurrency(item.amount)} (${item.percentage}%)`;
        }
      }
    }
  },
  cutout: '68%',
};

const router = useRouter();
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
      <div class="flex items-center gap-1.5 font-bold text-gray-800">
        <span>Despesas por Categoria</span>
        <Info :size="13" class="text-gray-400 cursor-help" title="Distribuição percentual das despesas dos últimos 90 dias" />
      </div>
      <button
        type="button"
        title="Ver despesas detalhadas"
        class="text-gray-400 hover:text-simples-orange transition-colors cursor-pointer"
        @click="router.push('/transactions?type=expense')"
      >
        <ExternalLink :size="13" />
      </button>
    </div>

    <!-- Chart Container -->
    <div class="h-64 w-full flex items-center justify-center">
      <Doughnut v-if="categories.length > 0" :data="chartData" :options="chartOptions" />
      <div v-else class="text-xs text-gray-400 text-center">
        Nenhuma despesa registrada no período.
      </div>
    </div>
  </div>
</template>
