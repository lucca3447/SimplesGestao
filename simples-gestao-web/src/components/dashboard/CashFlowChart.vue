<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';
import { Info, ExternalLink } from '@lucide/vue';
import { formatCurrency } from '@/utils/formatters';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});

const chartData = computed(() => {
  const labels = props.data.map(item => item.label);
  const incomes = props.data.map(item => item.income);
  const expenses = props.data.map(item => item.expense);

  return {
    labels,
    datasets: [
      {
        label: 'Entradas (Receitas)',
        backgroundColor: '#208b5d',
        borderRadius: 4,
        data: incomes,
      },
      {
        label: 'Saídas (Despesas)',
        backgroundColor: '#f43f5e',
        borderRadius: 4,
        data: expenses,
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        boxWidth: 12,
        font: { family: 'Plus Jakarta Sans', size: 11 },
        color: '#6b7280',
      },
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${formatCurrency(context.raw)}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { size: 11 }, color: '#9ca3af' },
    },
    y: {
      grid: { color: '#f3f4f6' },
      ticks: {
        font: { size: 10 },
        color: '#9ca3af',
        callback: (val) => 'R$ ' + val,
      },
    },
  },
};
</script>

<template>
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
      <div class="flex items-center gap-1.5 font-bold text-gray-800">
        <span>Fluxo de Caixa Consolidado</span>
        <Info :size="13" class="text-gray-400 cursor-pointer hover:text-gray-600" />
      </div>
      <ExternalLink :size="13" class="text-gray-400 cursor-pointer hover:text-gray-600" />
    </div>

    <!-- Chart Container -->
    <div class="h-64 w-full">
      <Bar v-if="data.length > 0" :data="chartData" :options="chartOptions" />
      <div v-else class="h-full flex items-center justify-center text-xs text-gray-400">
        Carregando dados do gráfico...
      </div>
    </div>
  </div>
</template>
