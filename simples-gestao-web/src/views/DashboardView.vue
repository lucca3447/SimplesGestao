<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/client';
import { formatCurrency } from '@/utils/formatters';
import WelcomeBanner from '@/components/dashboard/WelcomeBanner.vue';
import MetricCard from '@/components/common/MetricCard.vue';
import CashFlowChart from '@/components/dashboard/CashFlowChart.vue';
import ExpenseCategoryChart from '@/components/dashboard/ExpenseCategoryChart.vue';
import TopProductsList from '@/components/dashboard/TopProductsList.vue';
import RecentOrdersCard from '@/components/dashboard/RecentOrdersCard.vue';
import {
  TrendingDown,
  TrendingUp,
  Wallet,
  ShoppingCart,
  AlertTriangle,
  Users,
  Calendar,
  RefreshCw,
  AlertCircle,
} from '@lucide/vue';

const loading = ref(true);
const error = ref('');
const activePeriod = ref('this_month');

const summary = ref({
  period: { start_date: '', end_date: '' },
  metrics: {
    total_income: 0,
    total_expense: 0,
    net_balance: 0,
    confirmed_orders_count: 0,
    pending_orders_count: 0,
    average_ticket: 0,
    low_stock_count: 0,
    total_customers: 0,
  },
});

const charts = ref({
  cash_flow: [],
  expenses_by_category: [],
  top_selling_products: [],
  recent_orders: [],
});

const periodOptions = [
  { id: 'today', label: 'Hoje' },
  { id: 'this_month', label: 'Este Mês' },
  { id: 'last_month', label: 'Mês Anterior' },
  { id: 'last_30_days', label: 'Últimos 30 dias' },
  { id: 'this_year', label: 'Este Ano' },
];

async function loadDashboardData() {
  loading.value = true;
  error.value = '';

  try {
    const [summaryRes, chartsRes] = await Promise.all([
      apiClient.get('/dashboard/summary', { params: { period: activePeriod.value } }),
      apiClient.get('/dashboard/charts'),
    ]);

    summary.value = summaryRes.data;
    charts.value = chartsRes.data;
  } catch (err) {
    console.error('Erro ao carregar dashboard:', err);
    error.value = 'Falha ao sincronizar métricas com o servidor.';
  } finally {
    loading.value = false;
  }
}

function handlePeriodChange(newPeriod) {
  activePeriod.value = newPeriod;
  loadDashboardData();
}

onMounted(() => {
  loadDashboardData();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Welcome Banner Hero -->
    <WelcomeBanner />

    <!-- Barra de Filtro de Período e Atualização -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs">
      <div class="flex items-center gap-2 text-xs font-semibold text-gray-700">
        <Calendar :size="15" class="text-simples-orange" />
        <span>Filtrar período:</span>
        <div class="flex flex-wrap gap-1">
          <button
            v-for="period in periodOptions"
            :key="period.id"
            type="button"
            class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all cursor-pointer"
            :class="[
              activePeriod === period.id
                ? 'bg-simples-orange text-white shadow-xs'
                : 'bg-gray-100 hover:bg-gray-200 text-gray-600'
            ]"
            @click="handlePeriodChange(period.id)"
          >
            {{ period.label }}
          </button>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-800 font-semibold px-2.5 py-1 rounded-md hover:bg-gray-100 transition-colors cursor-pointer"
        :disabled="loading"
        @click="loadDashboardData"
      >
        <RefreshCw :size="13" :class="{ 'animate-spin': loading }" />
        <span>Atualizar dados</span>
      </button>
    </div>

    <!-- Mensagem de Erro se houver -->
    <div
      v-if="error"
      class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs flex items-center justify-between gap-2"
    >
      <div class="flex items-center gap-2">
        <AlertCircle :size="16" class="flex-shrink-0 text-rose-500" />
        <span>{{ error }}</span>
      </div>
      <button
        type="button"
        class="underline font-bold hover:text-rose-900 cursor-pointer"
        @click="loadDashboardData"
      >
        Tentar novamente
      </button>
    </div>

    <!-- Grid de Métricas (6 Cards Estilo Conexa) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
      <!-- 1. Contas a Pagar -->
      <MetricCard
        title="Contas a Pagar"
        :value="formatCurrency(summary.metrics.total_expense)"
        subtitle="Saídas no período"
        color="red"
      >
        <template #icon>
          <TrendingDown :size="15" />
        </template>
      </MetricCard>

      <!-- 2. Contas a Receber -->
      <MetricCard
        title="Contas a Receber"
        :value="formatCurrency(summary.metrics.total_income)"
        subtitle="Entradas no período"
        color="green"
      >
        <template #icon>
          <TrendingUp :size="15" />
        </template>
      </MetricCard>

      <!-- 3. Saldo Líquido -->
      <MetricCard
        title="Saldo Líquido"
        :value="formatCurrency(summary.metrics.net_balance)"
        subtitle="Resultado operacional"
        color="blue"
      >
        <template #icon>
          <Wallet :size="15" />
        </template>
      </MetricCard>

      <!-- 4. Vendas Faturadas -->
      <MetricCard
        title="Vendas Faturadas"
        :value="`${summary.metrics.confirmed_orders_count} pedidos`"
        :subtitle="`Ticket médio ${formatCurrency(summary.metrics.average_ticket)}`"
        color="orange"
      >
        <template #icon>
          <ShoppingCart :size="15" />
        </template>
      </MetricCard>

      <!-- 5. Baixo Estoque -->
      <MetricCard
        title="Baixo Estoque"
        :value="`${summary.metrics.low_stock_count} itens`"
        subtitle="Necessita reposição"
        color="red"
      >
        <template #icon>
          <AlertTriangle :size="15" />
        </template>
      </MetricCard>

      <!-- 6. Total de Clientes -->
      <MetricCard
        title="Total de Clientes"
        :value="`${summary.metrics.total_customers} ativos`"
        subtitle="Base cadastrada"
        color="purple"
      >
        <template #icon>
          <Users :size="15" />
        </template>
      </MetricCard>
    </div>

    <!-- Grid de Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <CashFlowChart :data="charts.cash_flow" />
      </div>
      <div>
        <ExpenseCategoryChart :categories="charts.expenses_by_category" />
      </div>
    </div>

    <!-- Grid de Listas Inferiores -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <TopProductsList :products="charts.top_selling_products" />
      <RecentOrdersCard :orders="charts.recent_orders" />
    </div>
  </div>
</template>
