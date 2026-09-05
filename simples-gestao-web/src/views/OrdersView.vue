<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import { formatCurrency, formatDate, formatDateTime } from '@/utils/formatters';
import AppModal from '@/components/common/AppModal.vue';
import {
  ShoppingBag,
  Search,
  Plus,
  Trash2,
  RefreshCw,
  AlertCircle,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Loader2,
  Eye,
  Check,
  XCircle,
  CreditCard,
  User,
} from '@lucide/vue';

// Listagem de Pedidos
const route = useRoute();
const orders = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const selectedStatus = ref('');
const error = ref('');
const successMessage = ref('');

// Paginação
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(10);

// Suporte para Novo Pedido
const customersList = ref([]);
const productsList = ref([]);

// Modal Novo Pedido
const isCreateModalOpen = ref(false);
const createLoading = ref(false);
const createErrors = ref({});
const orderForm = ref({
  customer_id: '',
  payment_method: 'pix',
  status: 'confirmed',
  discount: 0,
  notes: '',
  items: [],
});

// Modal Detalhes do Pedido
const isDetailsModalOpen = ref(false);
const selectedOrder = ref(null);
const detailsLoading = ref(false);

// Ações de confirmação/cancelamento
const actionLoading = ref(false);

const paymentMethods = [
  { id: 'pix', label: 'PIX' },
  { id: 'credit_card', label: 'Cartão de Crédito' },
  { id: 'debit_card', label: 'Cartão de Débito' },
  { id: 'cash', label: 'Dinheiro' },
  { id: 'bank_transfer', label: 'Transferência Bancária' },
];

const paymentMethodLabels = {
  pix: 'PIX',
  credit_card: 'Cartão de Crédito',
  debit_card: 'Cartão de Débito',
  cash: 'Dinheiro',
  bank_transfer: 'Transferência',
};

const statusConfig = {
  confirmed: { label: 'Confirmado', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
  delivered: { label: 'Entregue', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
  pending: { label: 'Pendente', bg: 'bg-amber-50 text-amber-700 border-amber-200' },
  cancelled: { label: 'Cancelado', bg: 'bg-rose-50 text-rose-700 border-rose-200' },
};

// Cálculos dinâmicos do formulário
const orderSubtotal = computed(() => {
  return orderForm.value.items.reduce((sum, item) => {
    return sum + ((Number(item.quantity) || 0) * (Number(item.unit_price) || 0));
  }, 0);
});

const orderTotal = computed(() => {
  const disc = Number(orderForm.value.discount) || 0;
  return Math.max(0, orderSubtotal.value - disc);
});

// Buscar pedidos
async function loadOrders(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const params = {
      page,
      per_page: perPage.value,
    };
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
    if (selectedStatus.value) params.status = selectedStatus.value;

    const res = await apiClient.get('/orders', { params });
    orders.value = res.data.data;
    currentPage.value = res.data.meta.current_page;
    totalPages.value = res.data.meta.last_page;
    totalItems.value = res.data.meta.total;
  } catch (err) {
    console.error('Erro ao listar pedidos:', err);
    error.value = 'Falha ao buscar registros de pedidos.';
  } finally {
    loading.value = false;
  }
}

// Carregar clientes e produtos para o modal de criação
async function loadSelectData() {
  try {
    const [custRes, prodRes] = await Promise.all([
      apiClient.get('/customers', { params: { per_page: 100 } }),
      apiClient.get('/products', { params: { per_page: 100, is_active: 1 } }),
    ]);
    customersList.value = custRes.data.data;
    productsList.value = prodRes.data.data;
  } catch (err) {
    console.error('Erro ao carregar dados auxiliares:', err);
  }
}

// Debounce de busca
let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadOrders(1);
  }, 350);
});

function handleStatusFilter() {
  loadOrders(1);
}

// Modal Novo Pedido
function openCreateOrderModal() {
  createErrors.value = {};
  orderForm.value = {
    customer_id: '',
    payment_method: 'pix',
    status: 'confirmed',
    discount: 0,
    notes: '',
    items: [
      {
        product_id: productsList.value[0]?.id || '',
        quantity: 1,
        unit_price: productsList.value[0]?.price || 0,
      }
    ],
  };
  isCreateModalOpen.value = true;
}

function handleProductChange(itemIndex) {
  const item = orderForm.value.items[itemIndex];
  const prod = productsList.value.find(p => p.id === Number(item.product_id));
  if (prod) {
    item.unit_price = prod.price;
  }
}

function addItemLine() {
  const defaultProd = productsList.value[0];
  orderForm.value.items.push({
    product_id: defaultProd ? defaultProd.id : '',
    quantity: 1,
    unit_price: defaultProd ? defaultProd.price : 0,
  });
}

function removeItemLine(index) {
  if (orderForm.value.items.length > 1) {
    orderForm.value.items.splice(index, 1);
  }
}

async function handleCreateOrderSubmit() {
  createLoading.value = true;
  createErrors.value = {};

  try {
    const payload = {
      customer_id: orderForm.value.customer_id || null,
      payment_method: orderForm.value.payment_method,
      status: orderForm.value.status,
      discount: Number(orderForm.value.discount) || 0,
      notes: orderForm.value.notes || null,
      items: orderForm.value.items.map(i => ({
        product_id: Number(i.product_id),
        quantity: Number(i.quantity),
        unit_price: Number(i.unit_price),
      })),
    };

    await apiClient.post('/orders', payload);
    showNotification('Pedido registrado com sucesso!');
    isCreateModalOpen.value = false;
    loadOrders(1);
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      createErrors.value = err.response.data.errors;
    } else {
      createErrors.value = { general: [err.response?.data?.message || 'Erro ao registrar pedido.'] };
    }
  } finally {
    createLoading.value = false;
  }
}

// Visualizar detalhes do pedido
async function viewOrderDetails(order) {
  detailsLoading.value = true;
  isDetailsModalOpen.value = true;
  try {
    const res = await apiClient.get(`/orders/${order.id}`);
    selectedOrder.value = res.data.data;
  } catch (err) {
    console.error('Erro ao carregar detalhes do pedido:', err);
    selectedOrder.value = order;
  } finally {
    detailsLoading.value = false;
  }
}

// Confirmar pedido (baixa estoque e gera receita)
async function confirmOrder(order) {
  actionLoading.value = true;
  try {
    await apiClient.patch(`/orders/${order.id}/confirm`);
    showNotification(`Pedido #${order.order_number} confirmado com sucesso! Estoque atualizado.`);
    if (isDetailsModalOpen.value && selectedOrder.value?.id === order.id) {
      const res = await apiClient.get(`/orders/${order.id}`);
      selectedOrder.value = res.data.data;
    }
    loadOrders(currentPage.value);
  } catch (err) {
    alert(err.response?.data?.message || 'Falha ao confirmar pedido.');
  } finally {
    actionLoading.value = false;
  }
}

// Cancelar pedido (estorna estoque e anula receita)
async function cancelOrder(order) {
  if (!confirm(`Deseja realmente cancelar o pedido #${order.order_number}? O estoque será estornado.`)) {
    return;
  }
  actionLoading.value = true;
  try {
    await apiClient.patch(`/orders/${order.id}/cancel`);
    showNotification(`Pedido #${order.order_number} cancelado com sucesso! Estoque recomposto.`);
    if (isDetailsModalOpen.value && selectedOrder.value?.id === order.id) {
      const res = await apiClient.get(`/orders/${order.id}`);
      selectedOrder.value = res.data.data;
    }
    loadOrders(currentPage.value);
  } catch (err) {
    alert(err.response?.data?.message || 'Falha ao cancelar pedido.');
  } finally {
    actionLoading.value = false;
  }
}

function showNotification(msg) {
  successMessage.value = msg;
  setTimeout(() => {
    successMessage.value = '';
  }, 4000);
}

onMounted(async () => {
  if (route.query.status) {
    selectedStatus.value = String(route.query.status);
  }
  loadOrders();
  await loadSelectData();

  if (route.query.action === 'new') {
    openCreateOrderModal();
  }
});

watch(() => route.query.status, (newStatus) => {
  if (newStatus !== undefined) {
    selectedStatus.value = String(newStatus);
    loadOrders(1);
  }
});

watch(() => route.query.action, (newAction) => {
  if (newAction === 'new') {
    openCreateOrderModal();
  }
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header com Título e Botão de Ação -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
          <ShoppingBag :size="22" class="text-simples-orange" />
          <span>Vendas & Pedidos</span>
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">
          Emissão de pedidos comerciais, faturamento, baixa instantânea de estoque e integração financeira.
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
        @click="openCreateOrderModal"
      >
        <Plus :size="16" />
        <span>Novo Pedido / Venda</span>
      </button>
    </div>

    <!-- Feedback Sucesso -->
    <div
      v-if="successMessage"
      class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 animate-fade-in"
    >
      <CheckCircle2 :size="16" class="text-emerald-600 flex-shrink-0" />
      <span>{{ successMessage }}</span>
    </div>

    <!-- Feedback Erro -->
    <div
      v-if="error"
      class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs flex items-center justify-between gap-2"
    >
      <div class="flex items-center gap-2">
        <AlertCircle :size="16" class="text-rose-500 flex-shrink-0" />
        <span>{{ error }}</span>
      </div>
      <button
        type="button"
        class="underline font-semibold cursor-pointer"
        @click="loadOrders(currentPage)"
      >
        Recarregar
      </button>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
        <!-- Busca -->
        <div class="relative flex-1 max-w-sm">
          <Search :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Buscar por número do pedido ou cliente..."
            class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          />
        </div>

        <!-- Filtro Status -->
        <select
          v-model="selectedStatus"
          class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:outline-none text-gray-700 transition-all cursor-pointer"
          @change="handleStatusFilter"
        >
          <option value="">Todos os status</option>
          <option value="pending">Pendentes</option>
          <option value="confirmed">Confirmados</option>
          <option value="delivered">Entregues</option>
          <option value="cancelled">Cancelados</option>
        </select>
      </div>

      <!-- Total & Refresh -->
      <div class="flex items-center gap-3 justify-end text-xs text-gray-500">
        <span class="font-medium text-gray-700">Total: <strong class="text-gray-900">{{ totalItems }}</strong> vendas</span>
        <button
          type="button"
          class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          title="Atualizar lista"
          :disabled="loading"
          @click="loadOrders(currentPage)"
        >
          <RefreshCw :size="15" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Tabela de Pedidos -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
          <thead class="bg-gray-50/80 text-gray-500 font-semibold border-b border-gray-200 uppercase tracking-wider text-[10px]">
            <tr>
              <th scope="col" class="px-5 py-3.5">Nº Pedido</th>
              <th scope="col" class="px-5 py-3.5">Cliente</th>
              <th scope="col" class="px-5 py-3.5">Pagamento</th>
              <th scope="col" class="px-5 py-3.5">Itens</th>
              <th scope="col" class="px-5 py-3.5">Valor Total</th>
              <th scope="col" class="px-5 py-3.5">Status</th>
              <th scope="col" class="px-5 py-3.5">Data</th>
              <th scope="col" class="px-5 py-3.5 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading Row -->
            <tr v-if="loading && orders.length === 0">
              <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Loader2 :size="24" class="animate-spin text-simples-orange" />
                  <span>Carregando vendas...</span>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="orders.length === 0">
              <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <ShoppingBag :size="28" class="text-gray-300" />
                  <p class="font-medium text-gray-600">Nenhum pedido encontrado.</p>
                  <p class="text-[11px]">Experimente outros filtros ou lance uma nova venda.</p>
                </div>
              </td>
            </tr>

            <!-- Pedidos Listados -->
            <tr
              v-for="order in orders"
              v-else
              :key="order.id"
              class="hover:bg-gray-50/60 transition-colors"
            >
              <!-- Nº Pedido -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded text-[11px]">
                  {{ order.order_number }}
                </span>
              </td>

              <!-- Cliente -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                  <User :size="12" class="text-gray-400" />
                  <span>{{ order.customer?.name || 'Consumidor Final' }}</span>
                </div>
              </td>

              <!-- Pagamento -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-600">
                <div class="flex items-center gap-1.5">
                  <CreditCard :size="12" class="text-gray-400" />
                  <span>{{ paymentMethodLabels[order.payment_method] || order.payment_method }}</span>
                </div>
              </td>

              <!-- Itens -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-600">
                <span class="font-medium">{{ order.items_count || order.items?.length || 1 }} un</span>
              </td>

              <!-- Valor Total -->
              <td class="px-5 py-3.5 whitespace-nowrap font-bold text-gray-900">
                {{ formatCurrency(order.total) }}
              </td>

              <!-- Status -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-block"
                  :class="statusConfig[order.status]?.bg || 'bg-gray-50 text-gray-600 border-gray-200'"
                >
                  {{ statusConfig[order.status]?.label || order.status }}
                </span>
              </td>

              <!-- Data -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500">
                {{ formatDate(order.created_at) }}
              </td>

              <!-- Ações -->
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <!-- Visualizar Detalhes -->
                  <button
                    type="button"
                    title="Visualizar Detalhes"
                    class="p-1.5 text-gray-400 hover:text-simples-orange hover:bg-orange-50 rounded-md transition-colors cursor-pointer"
                    @click="viewOrderDetails(order)"
                  >
                    <Eye :size="15" />
                  </button>

                  <!-- Confirmar Pedido Pendente -->
                  <button
                    v-if="order.status === 'pending'"
                    type="button"
                    title="Confirmar Venda (Baixar Estoque)"
                    :disabled="actionLoading"
                    class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-md transition-colors cursor-pointer"
                    @click="confirmOrder(order)"
                  >
                    <Check :size="15" />
                  </button>

                  <!-- Cancelar Pedido -->
                  <button
                    v-if="order.status !== 'cancelled'"
                    type="button"
                    title="Cancelar Venda (Estornar Estoque)"
                    :disabled="actionLoading"
                    class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                    @click="cancelOrder(order)"
                  >
                    <XCircle :size="15" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div
        v-if="totalPages > 1"
        class="px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs"
      >
        <span class="text-gray-500 text-[11px]">
          Página <strong class="text-gray-800">{{ currentPage }}</strong> de <strong class="text-gray-800">{{ totalPages }}</strong>
        </span>

        <div class="flex items-center gap-1">
          <button
            type="button"
            :disabled="currentPage <= 1 || loading"
            class="px-2.5 py-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-medium transition-colors cursor-pointer"
            @click="loadOrders(currentPage - 1)"
          >
            <ChevronLeft :size="14" />
            <span>Anterior</span>
          </button>
          <button
            type="button"
            :disabled="currentPage >= totalPages || loading"
            class="px-2.5 py-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-medium transition-colors cursor-pointer"
            @click="loadOrders(currentPage + 1)"
          >
            <span>Próxima</span>
            <ChevronRight :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Novo Pedido de Venda -->
    <AppModal
      v-model="isCreateModalOpen"
      title="Emitir Novo Pedido / Venda"
      max-width="max-w-2xl"
    >
      <form id="order-form" class="space-y-4" @submit.prevent="handleCreateOrderSubmit">
        <div
          v-if="createErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ createErrors.general[0] }}
        </div>
        <div
          v-if="createErrors.items"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ createErrors.items[0] }}
        </div>

        <!-- Cliente e Forma de Pagamento -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="order-cust" class="block text-xs font-semibold text-gray-700">Cliente</label>
            <select
              id="order-cust"
              v-model="orderForm.customer_id"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all cursor-pointer"
            >
              <option value="">Consumidor Final (Sem cadastro)</option>
              <option v-for="c in customersList" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="order-pay" class="block text-xs font-semibold text-gray-700">
              Forma de Pagamento <span class="text-rose-500">*</span>
            </label>
            <select
              id="order-pay"
              v-model="orderForm.payment_method"
              required
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all cursor-pointer"
            >
              <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">
                {{ pm.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Status Inicial -->
        <div class="space-y-1">
          <label class="block text-xs font-semibold text-gray-700">Status Inicial do Pedido</label>
          <div class="flex items-center gap-4 text-xs">
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input
                v-model="orderForm.status"
                type="radio"
                value="confirmed"
                class="text-simples-orange focus:ring-simples-orange"
              />
              <span class="font-semibold text-emerald-700">Confirmado</span>
              <span class="text-gray-400 text-[11px]">(baixa imediata do estoque e receita no caixa)</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input
                v-model="orderForm.status"
                type="radio"
                value="pending"
                class="text-simples-orange focus:ring-simples-orange"
              />
              <span class="font-semibold text-amber-700">Pendente</span>
              <span class="text-gray-400 text-[11px]">(orçamento sem baixa)</span>
            </label>
          </div>
        </div>

        <!-- Lista Dinâmica de Itens do Pedido -->
        <div class="space-y-2 pt-2 border-t border-gray-100">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800">Itens do Pedido</span>
            <button
              type="button"
              class="text-xs font-bold text-simples-orange hover:text-simples-orange-hover flex items-center gap-1 cursor-pointer"
              @click="addItemLine"
            >
              <Plus :size="14" />
              <span>Adicionar Item</span>
            </button>
          </div>

          <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
            <div
              v-for="(item, idx) in orderForm.items"
              :key="idx"
              class="flex items-center gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-200/80"
            >
              <!-- Seleção do Produto -->
              <div class="flex-1">
                <select
                  v-model="item.product_id"
                  required
                  class="w-full px-2.5 py-1.5 bg-white text-xs text-gray-900 rounded-md border border-gray-200 focus:border-simples-orange focus:outline-none cursor-pointer"
                  @change="handleProductChange(idx)"
                >
                  <option disabled value="">Selecione o produto</option>
                  <option
                    v-for="p in productsList"
                    :key="p.id"
                    :value="p.id"
                    :disabled="orderForm.status === 'confirmed' && p.stock_quantity <= 0"
                  >
                    {{ p.name }} (Estoque: {{ p.stock_quantity }}) - {{ formatCurrency(p.price) }}
                  </option>
                </select>
              </div>

              <!-- Quantidade -->
              <div class="w-20">
                <input
                  v-model="item.quantity"
                  type="number"
                  min="1"
                  required
                  placeholder="Qtd"
                  class="w-full px-2 py-1.5 bg-white text-xs text-gray-900 rounded-md border border-gray-200 focus:border-simples-orange focus:outline-none text-center font-bold"
                />
              </div>

              <!-- Preço Unitário -->
              <div class="w-24">
                <input
                  v-model="item.unit_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  placeholder="Preço"
                  class="w-full px-2 py-1.5 bg-white text-xs text-gray-900 rounded-md border border-gray-200 focus:border-simples-orange focus:outline-none text-right font-medium"
                />
              </div>

              <!-- Subtotal do Item -->
              <div class="w-24 text-right text-xs font-bold text-gray-800 pr-1">
                {{ formatCurrency((item.quantity || 0) * (item.unit_price || 0)) }}
              </div>

              <!-- Botão Remover -->
              <button
                type="button"
                :disabled="orderForm.items.length === 1"
                class="text-gray-400 hover:text-rose-600 disabled:opacity-30 disabled:hover:text-gray-400 p-1 rounded transition-colors cursor-pointer"
                @click="removeItemLine(idx)"
              >
                <Trash2 :size="15" />
              </button>
            </div>
          </div>
        </div>

        <!-- Desconto e Resumo de Totais -->
        <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="w-full sm:w-48 space-y-1">
            <label for="order-disc" class="block text-xs font-semibold text-gray-700">Desconto (R$)</label>
            <input
              id="order-disc"
              v-model="orderForm.discount"
              type="number"
              step="0.01"
              min="0"
              placeholder="0,00"
              class="w-full px-3 py-1.5 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:outline-none transition-all"
            />
          </div>

          <div class="text-right space-y-0.5 self-end">
            <div class="text-xs text-gray-500">
              Subtotal: <span class="font-medium text-gray-700">{{ formatCurrency(orderSubtotal) }}</span>
            </div>
            <div class="text-xs text-gray-500">
              Desconto: <span class="font-medium text-rose-600">- {{ formatCurrency(orderForm.discount || 0) }}</span>
            </div>
            <div class="text-sm font-bold text-gray-900 pt-1">
              Total Líquido: <span class="text-simples-green font-extrabold text-base">{{ formatCurrency(orderTotal) }}</span>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="space-y-1">
          <label for="order-notes" class="block text-xs font-semibold text-gray-700">Observações da Venda</label>
          <textarea
            id="order-notes"
            v-model="orderForm.notes"
            rows="2"
            placeholder="Instruções de entrega, condições ou referência..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:outline-none transition-all"
          ></textarea>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isCreateModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="submit"
          form="order-form"
          :disabled="createLoading || orderForm.items.length === 0"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="createLoading" :size="14" class="animate-spin" />
          <span>{{ createLoading ? 'Processando venda...' : 'Finalizar Pedido' }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal Detalhes do Pedido -->
    <AppModal
      v-model="isDetailsModalOpen"
      :title="`Detalhes do Pedido #${selectedOrder?.order_number || ''}`"
      max-width="max-w-xl"
    >
      <div v-if="detailsLoading" class="py-12 text-center text-gray-400">
        <Loader2 :size="24" class="animate-spin text-simples-orange mx-auto mb-2" />
        <span class="text-xs">Carregando detalhes do pedido...</span>
      </div>

      <div v-else-if="selectedOrder" class="space-y-4">
        <!-- Header Info -->
        <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 grid grid-cols-2 gap-3 text-xs">
          <div>
            <span class="text-gray-400 block text-[10px]">Cliente</span>
            <span class="font-bold text-gray-900">{{ selectedOrder.customer?.name || 'Consumidor Final' }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[10px]">Status</span>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold border inline-block mt-0.5"
              :class="statusConfig[selectedOrder.status]?.bg"
            >
              {{ statusConfig[selectedOrder.status]?.label || selectedOrder.status }}
            </span>
          </div>
          <div>
            <span class="text-gray-400 block text-[10px]">Pagamento</span>
            <span class="font-medium text-gray-800">{{ paymentMethodLabels[selectedOrder.payment_method] || selectedOrder.payment_method }}</span>
          </div>
          <div>
            <span class="text-gray-400 block text-[10px]">Data & Hora</span>
            <span class="font-medium text-gray-800">{{ formatDateTime(selectedOrder.created_at) }}</span>
          </div>
        </div>

        <!-- Tabela de Itens -->
        <div>
          <h4 class="text-xs font-bold text-gray-800 mb-2">Itens Inclusos</h4>
          <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-200 text-[10px] uppercase">
                <tr>
                  <th class="px-3 py-2">Item</th>
                  <th class="px-3 py-2 text-center">Qtd</th>
                  <th class="px-3 py-2 text-right">Unitário</th>
                  <th class="px-3 py-2 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in selectedOrder.items" :key="item.id">
                  <td class="px-3 py-2 font-medium text-gray-900">{{ item.product?.name || 'Produto #' + item.product_id }}</td>
                  <td class="px-3 py-2 text-center text-gray-600">{{ item.quantity }}</td>
                  <td class="px-3 py-2 text-right text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-3 py-2 text-right font-bold text-gray-900">{{ formatCurrency(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Totais -->
        <div class="bg-gray-50 p-3 rounded-lg flex justify-between items-center text-xs">
          <span class="text-gray-500">Subtotal: {{ formatCurrency(selectedOrder.subtotal) }} | Desconto: {{ formatCurrency(selectedOrder.discount) }}</span>
          <div class="text-right">
            <span class="text-[10px] text-gray-400 block">Total do Pedido</span>
            <span class="text-base font-extrabold text-simples-green">{{ formatCurrency(selectedOrder.total) }}</span>
          </div>
        </div>

        <!-- Observações -->
        <div v-if="selectedOrder.notes" class="text-xs bg-amber-50/60 p-2.5 rounded-lg border border-amber-100 text-amber-900">
          <strong class="block text-[10px] text-amber-700 uppercase">Observações:</strong>
          <span>{{ selectedOrder.notes }}</span>
        </div>
      </div>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isDetailsModalOpen = false"
        >
          Fechar
        </button>

        <button
          v-if="selectedOrder?.status === 'pending'"
          type="button"
          :disabled="actionLoading"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
          @click="confirmOrder(selectedOrder)"
        >
          <Check :size="14" />
          <span>Confirmar Venda</span>
        </button>

        <button
          v-if="selectedOrder && selectedOrder.status !== 'cancelled'"
          type="button"
          :disabled="actionLoading"
          class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
          @click="cancelOrder(selectedOrder)"
        >
          <XCircle :size="14" />
          <span>Cancelar Venda</span>
        </button>
      </template>
    </AppModal>
  </div>
</template>
