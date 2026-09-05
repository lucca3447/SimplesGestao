<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import apiClient from '@/api/client';
import { formatCurrency, formatDate } from '@/utils/formatters';
import AppModal from '@/components/common/AppModal.vue';
import {
  DollarSign,
  Search,
  Plus,
  Edit2,
  Trash2,
  RefreshCw,
  AlertCircle,
  CheckCircle2,
  TrendingUp,
  TrendingDown,
  FolderPlus,
  ChevronLeft,
  ChevronRight,
  Loader2,
  Tag,
  Calendar,
  Lock,
} from '@lucide/vue';

// Listagem
const transactions = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const selectedType = ref(''); // '' | 'income' | 'expense'
const selectedCategory = ref('');
const error = ref('');
const successMessage = ref('');

// Paginação
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(15);

// Modal Transação (Create / Edit)
const isTransModalOpen = ref(false);
const transFormMode = ref('create'); // 'create' | 'edit'
const transLoading = ref(false);
const transErrors = ref({});
const transFormData = ref({
  id: null,
  financial_category_id: '',
  type: 'expense',
  amount: '',
  description: '',
  transaction_date: new Date().toISOString().split('T')[0],
});

// Modal Categoria Financeira
const isCategoryModalOpen = ref(false);
const categoryLoading = ref(false);
const categoryErrors = ref({});
const categoryFormData = ref({
  name: '',
  type: 'expense',
});

// Modal Exclusão
const isDeleteModalOpen = ref(false);
const transToDelete = ref(null);
const deleteLoading = ref(false);

// Filtrar categorias conforme o tipo selecionado no modal de transação
const filteredCategoriesForForm = computed(() => {
  return categories.value.filter(c => c.type === transFormData.value.type);
});

// Resumo rápido da página atual
const pageSummary = computed(() => {
  let income = 0;
  let expense = 0;
  for (const t of transactions.value) {
    const val = Number(t.amount) || 0;
    if (t.type === 'income') income += val;
    else expense += val;
  }
  return {
    income,
    expense,
    balance: income - expense,
  };
});

// Buscar transações
async function loadTransactions(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const params = {
      page,
      per_page: perPage.value,
    };
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
    if (selectedType.value) params.type = selectedType.value;
    if (selectedCategory.value) params.financial_category_id = selectedCategory.value;

    const res = await apiClient.get('/transactions', { params });
    transactions.value = res.data.data;
    currentPage.value = res.data.meta.current_page;
    totalPages.value = res.data.meta.last_page;
    totalItems.value = res.data.meta.total;
  } catch (err) {
    console.error('Erro ao buscar transações:', err);
    error.value = 'Falha ao carregar livro caixa e transações.';
  } finally {
    loading.value = false;
  }
}

// Buscar categorias financeiras
async function loadCategories() {
  try {
    const res = await apiClient.get('/financial-categories', { params: { all: 1 } });
    categories.value = res.data.data;
  } catch (err) {
    console.error('Erro ao carregar categorias financeiras:', err);
  }
}

// Debounce de busca
let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadTransactions(1);
  }, 350);
});

function handleFilterChange() {
  loadTransactions(1);
}

// Modal Transação
function openCreateModal(defaultType = 'expense') {
  transFormMode.value = 'create';
  transErrors.value = {};
  transFormData.value = {
    id: null,
    financial_category_id: '',
    type: defaultType,
    amount: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
  };

  // Pré-seleciona a primeira categoria compatível
  const firstCat = categories.value.find(c => c.type === defaultType);
  if (firstCat) {
    transFormData.value.financial_category_id = firstCat.id;
  }

  isTransModalOpen.value = true;
}

function openEditModal(trans) {
  transFormMode.value = 'edit';
  transErrors.value = {};
  transFormData.value = {
    id: trans.id,
    financial_category_id: trans.financial_category_id || trans.financial_category?.id || '',
    type: trans.type,
    amount: trans.amount,
    description: trans.description,
    transaction_date: trans.transaction_date,
  };
  isTransModalOpen.value = true;
}

function onTransTypeChange() {
  const firstCat = categories.value.find(c => c.type === transFormData.value.type);
  transFormData.value.financial_category_id = firstCat ? firstCat.id : '';
}

async function handleTransSubmit() {
  transLoading.value = true;
  transErrors.value = {};
  try {
    const payload = {
      financial_category_id: Number(transFormData.value.financial_category_id),
      type: transFormData.value.type,
      amount: Number(transFormData.value.amount),
      description: transFormData.value.description,
      transaction_date: transFormData.value.transaction_date,
    };

    if (transFormMode.value === 'create') {
      await apiClient.post('/transactions', payload);
      showNotification('Lançamento registrado com sucesso!');
    } else {
      await apiClient.put(`/transactions/${transFormData.value.id}`, payload);
      showNotification('Lançamento atualizado com sucesso!');
    }
    isTransModalOpen.value = false;
    loadTransactions(currentPage.value);
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      transErrors.value = err.response.data.errors;
    } else {
      transErrors.value = { general: [err.response?.data?.message || 'Erro ao registrar lançamento.'] };
    }
  } finally {
    transLoading.value = false;
  }
}

// Modal Categoria Financeira
function openCategoryModal() {
  categoryErrors.value = {};
  categoryFormData.value = { name: '', type: 'expense' };
  isCategoryModalOpen.value = true;
}

async function handleCategorySubmit() {
  categoryLoading.value = true;
  categoryErrors.value = {};
  try {
    const res = await apiClient.post('/financial-categories', categoryFormData.value);
    showNotification('Categoria financeira cadastrada com sucesso!');
    isCategoryModalOpen.value = false;
    await loadCategories();
    if (isTransModalOpen.value && res.data?.data?.id) {
      transFormData.value.financial_category_id = res.data.data.id;
    }
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      categoryErrors.value = err.response.data.errors;
    } else {
      categoryErrors.value = { general: [err.response?.data?.message || 'Erro ao cadastrar categoria.'] };
    }
  } finally {
    categoryLoading.value = false;
  }
}

// Exclusão
function openDeleteModal(trans) {
  transToDelete.value = trans;
  isDeleteModalOpen.value = true;
}

async function confirmDelete() {
  if (!transToDelete.value) return;
  deleteLoading.value = true;
  try {
    await apiClient.delete(`/transactions/${transToDelete.value.id}`);
    showNotification('Lançamento excluído com sucesso!');
    isDeleteModalOpen.value = false;
    transToDelete.value = null;
    loadTransactions(currentPage.value);
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao excluir lançamento.');
  } finally {
    deleteLoading.value = false;
  }
}

function showNotification(msg) {
  successMessage.value = msg;
  setTimeout(() => {
    successMessage.value = '';
  }, 4000);
}

onMounted(() => {
  loadCategories();
  loadTransactions();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
          <DollarSign :size="22" class="text-simples-orange" />
          <span>Financeiro & Livro Caixa</span>
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">
          Extrato consolidado de receitas de vendas, despesas operacionais e plano de contas.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-all shadow-xs cursor-pointer"
          @click="openCategoryModal"
        >
          <FolderPlus :size="15" />
          <span>Nova Categoria</span>
        </button>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          @click="openCreateModal('expense')"
        >
          <Plus :size="16" />
          <span>Novo Lançamento</span>
        </button>
      </div>
    </div>

    <!-- Cards Resumo Rápido -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-xs text-gray-400 font-medium block">Entradas (Receitas)</span>
          <span class="text-lg font-extrabold text-emerald-600">{{ formatCurrency(pageSummary.income) }}</span>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <TrendingUp :size="18" />
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-xs text-gray-400 font-medium block">Saídas (Despesas)</span>
          <span class="text-lg font-extrabold text-rose-600">{{ formatCurrency(pageSummary.expense) }}</span>
        </div>
        <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center">
          <TrendingDown :size="18" />
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
        <div>
          <span class="text-xs text-gray-400 font-medium block">Saldo do Período</span>
          <span
            class="text-lg font-extrabold"
            :class="pageSummary.balance >= 0 ? 'text-sky-600' : 'text-rose-600'"
          >
            {{ formatCurrency(pageSummary.balance) }}
          </span>
        </div>
        <div class="w-10 h-10 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center">
          <DollarSign :size="18" />
        </div>
      </div>
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
        @click="loadTransactions(currentPage)"
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
            placeholder="Buscar por descrição do lançamento..."
            class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          />
        </div>

        <!-- Filtro Tipo -->
        <select
          v-model="selectedType"
          class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:outline-none text-gray-700 transition-all cursor-pointer"
          @change="handleFilterChange"
        >
          <option value="">Todas as movimentações</option>
          <option value="income">Apenas Entradas (Receitas)</option>
          <option value="expense">Apenas Saídas (Despesas)</option>
        </select>

        <!-- Filtro Categoria -->
        <select
          v-model="selectedCategory"
          class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:outline-none text-gray-700 transition-all cursor-pointer"
          @change="handleFilterChange"
        >
          <option value="">Todas as categorias</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">
            {{ c.name }} ({{ c.type === 'income' ? 'Receita' : 'Despesa' }})
          </option>
        </select>
      </div>

      <!-- Contagem & Refresh -->
      <div class="flex items-center gap-3 justify-end text-xs text-gray-500">
        <span class="font-medium text-gray-700">Total: <strong class="text-gray-900">{{ totalItems }}</strong> registros</span>
        <button
          type="button"
          class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          title="Atualizar lista"
          :disabled="loading"
          @click="loadTransactions(currentPage)"
        >
          <RefreshCw :size="15" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Tabela de Movimentações -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
          <thead class="bg-gray-50/80 text-gray-500 font-semibold border-b border-gray-200 uppercase tracking-wider text-[10px]">
            <tr>
              <th scope="col" class="px-5 py-3.5">Tipo</th>
              <th scope="col" class="px-5 py-3.5">Descrição</th>
              <th scope="col" class="px-5 py-3.5">Categoria</th>
              <th scope="col" class="px-5 py-3.5">Data Movimento</th>
              <th scope="col" class="px-5 py-3.5 text-right">Valor (R$)</th>
              <th scope="col" class="px-5 py-3.5 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading Row -->
            <tr v-if="loading && transactions.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Loader2 :size="24" class="animate-spin text-simples-orange" />
                  <span>Carregando livro caixa...</span>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="transactions.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <DollarSign :size="28" class="text-gray-300" />
                  <p class="font-medium text-gray-600">Nenhum lançamento encontrado.</p>
                  <p class="text-[11px]">Experimente outros filtros ou adicione uma movimentação manual.</p>
                </div>
              </td>
            </tr>

            <!-- Linhas de Transações -->
            <tr
              v-for="trans in transactions"
              v-else
              :key="trans.id"
              class="hover:bg-gray-50/60 transition-colors"
            >
              <!-- Ícone Tipo -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span
                  class="w-7 h-7 rounded-full flex items-center justify-center"
                  :class="trans.type === 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500'"
                  :title="trans.type === 'income' ? 'Entrada / Receita' : 'Saída / Despesa'"
                >
                  <TrendingUp v-if="trans.type === 'income'" :size="15" />
                  <TrendingDown v-else :size="15" />
                </span>
              </td>

              <!-- Descrição -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-gray-900">{{ trans.description }}</span>
                  <span
                    v-if="trans.order_id"
                    class="bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded text-[10px] font-bold"
                    title="Originada automaticamente de um pedido faturado"
                  >
                    Venda Automática
                  </span>
                </div>
              </td>

              <!-- Categoria Financeira -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                  <Tag :size="10" class="text-gray-400" />
                  <span>{{ trans.financial_category?.name || 'Geral' }}</span>
                </span>
              </td>

              <!-- Data -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500">
                {{ formatDate(trans.transaction_date) }}
              </td>

              <!-- Valor -->
              <td
                class="px-5 py-3.5 whitespace-nowrap text-right font-extrabold"
                :class="trans.type === 'income' ? 'text-emerald-600' : 'text-rose-600'"
              >
                {{ trans.type === 'income' ? '+' : '-' }} {{ formatCurrency(trans.amount) }}
              </td>

              <!-- Ações -->
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div v-if="!trans.order_id" class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    title="Editar Lançamento"
                    class="p-1.5 text-gray-400 hover:text-simples-orange hover:bg-orange-50 rounded-md transition-colors cursor-pointer"
                    @click="openEditModal(trans)"
                  >
                    <Edit2 :size="14" />
                  </button>
                  <button
                    type="button"
                    title="Excluir Lançamento"
                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                    @click="openDeleteModal(trans)"
                  >
                    <Trash2 :size="14" />
                  </button>
                </div>
                <div v-else class="text-gray-300 flex items-center justify-end" title="Vinculada a pedido: gerencie pelo módulo de Vendas">
                  <Lock :size="13" />
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
            @click="loadTransactions(currentPage - 1)"
          >
            <ChevronLeft :size="14" />
            <span>Anterior</span>
          </button>
          <button
            type="button"
            :disabled="currentPage >= totalPages || loading"
            class="px-2.5 py-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-medium transition-colors cursor-pointer"
            @click="loadTransactions(currentPage + 1)"
          >
            <span>Próxima</span>
            <ChevronRight :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Novo / Editar Lançamento -->
    <AppModal
      v-model="isTransModalOpen"
      :title="transFormMode === 'create' ? 'Novo Lançamento Financeiro' : 'Editar Lançamento'"
      max-width="max-w-lg"
    >
      <form id="trans-form" class="space-y-4" @submit.prevent="handleTransSubmit">
        <div
          v-if="transErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ transErrors.general[0] }}
        </div>

        <!-- Tipo de Transação -->
        <div class="space-y-1">
          <label class="block text-xs font-semibold text-gray-700">Tipo da Movimentação</label>
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              class="py-2 px-3 rounded-lg text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer"
              :class="[
                transFormData.type === 'expense'
                  ? 'bg-rose-50 border-rose-300 text-rose-700 shadow-xs'
                  : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              @click="transFormData.type = 'expense'; onTransTypeChange()"
            >
              <TrendingDown :size="15" />
              <span>Saída (Despesa)</span>
            </button>

            <button
              type="button"
              class="py-2 px-3 rounded-lg text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer"
              :class="[
                transFormData.type === 'income'
                  ? 'bg-emerald-50 border-emerald-300 text-emerald-700 shadow-xs'
                  : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              @click="transFormData.type = 'income'; onTransTypeChange()"
            >
              <TrendingUp :size="15" />
              <span>Entrada (Receita)</span>
            </button>
          </div>
        </div>

        <!-- Categoria Financeira -->
        <div class="space-y-1">
          <label for="trans-cat" class="block text-xs font-semibold text-gray-700">
            Categoria Financeira <span class="text-rose-500">*</span>
          </label>
          <select
            id="trans-cat"
            v-model="transFormData.financial_category_id"
            required
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all cursor-pointer"
            :class="{ 'border-rose-400 bg-rose-50/20': transErrors.financial_category_id }"
          >
            <option disabled value="">Selecione uma categoria</option>
            <option v-for="c in filteredCategoriesForForm" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
          <p v-if="transErrors.financial_category_id" class="text-[11px] text-rose-600 font-medium">
            {{ transErrors.financial_category_id[0] }}
          </p>
        </div>

        <!-- Descrição -->
        <div class="space-y-1">
          <label for="trans-desc" class="block text-xs font-semibold text-gray-700">
            Descrição do Lançamento <span class="text-rose-500">*</span>
          </label>
          <input
            id="trans-desc"
            v-model="transFormData.description"
            type="text"
            required
            placeholder="Ex: Pagamento Fornecedor XYZ, Aluguel Março..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': transErrors.description }"
          />
          <p v-if="transErrors.description" class="text-[11px] text-rose-600 font-medium">
            {{ transErrors.description[0] }}
          </p>
        </div>

        <!-- Valor e Data -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="trans-amount" class="block text-xs font-semibold text-gray-700">
              Valor (R$) <span class="text-rose-500">*</span>
            </label>
            <input
              id="trans-amount"
              v-model="transFormData.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              placeholder="0,00"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
              :class="{ 'border-rose-400 bg-rose-50/20': transErrors.amount }"
            />
            <p v-if="transErrors.amount" class="text-[11px] text-rose-600 font-medium">
              {{ transErrors.amount[0] }}
            </p>
          </div>

          <div class="space-y-1">
            <label for="trans-date" class="block text-xs font-semibold text-gray-700">
              Data do Movimento <span class="text-rose-500">*</span>
            </label>
            <input
              id="trans-date"
              v-model="transFormData.transaction_date"
              type="date"
              required
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
              :class="{ 'border-rose-400 bg-rose-50/20': transErrors.transaction_date }"
            />
            <p v-if="transErrors.transaction_date" class="text-[11px] text-rose-600 font-medium">
              {{ transErrors.transaction_date[0] }}
            </p>
          </div>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isTransModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="submit"
          form="trans-form"
          :disabled="transLoading"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="transLoading" :size="14" class="animate-spin" />
          <span>{{ transLoading ? 'Salvando...' : 'Salvar Lançamento' }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal Nova Categoria Financeira -->
    <AppModal
      v-model="isCategoryModalOpen"
      title="Nova Categoria Financeira"
      max-width="max-w-md"
    >
      <form id="fin-cat-form" class="space-y-4" @submit.prevent="handleCategorySubmit">
        <div
          v-if="categoryErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ categoryErrors.general[0] }}
        </div>

        <div class="space-y-1">
          <label for="fin-cat-name" class="block text-xs font-semibold text-gray-700">
            Nome da Categoria <span class="text-rose-500">*</span>
          </label>
          <input
            id="fin-cat-name"
            v-model="categoryFormData.name"
            type="text"
            required
            placeholder="Ex: Aluguel, Combustível, Marketing..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': categoryErrors.name }"
          />
          <p v-if="categoryErrors.name" class="text-[11px] text-rose-600 font-medium">
            {{ categoryErrors.name[0] }}
          </p>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-semibold text-gray-700">Tipo de Fluxo</label>
          <div class="flex items-center gap-4 text-xs">
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input
                v-model="categoryFormData.type"
                type="radio"
                value="expense"
                class="text-simples-orange focus:ring-simples-orange"
              />
              <span class="font-semibold text-rose-600">Despesa (Saída)</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input
                v-model="categoryFormData.type"
                type="radio"
                value="income"
                class="text-simples-orange focus:ring-simples-orange"
              />
              <span class="font-semibold text-emerald-600">Receita (Entrada)</span>
            </label>
          </div>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isCategoryModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="submit"
          form="fin-cat-form"
          :disabled="categoryLoading"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="categoryLoading" :size="14" class="animate-spin" />
          <span>{{ categoryLoading ? 'Cadastrando...' : 'Cadastrar Categoria' }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal Excluir Lançamento -->
    <AppModal
      v-model="isDeleteModalOpen"
      title="Confirmar Exclusão de Lançamento"
      max-width="max-w-md"
    >
      <div class="space-y-3">
        <p class="text-xs text-gray-600">
          Tem certeza de que deseja excluir o lançamento
          <strong class="text-gray-900">{{ transToDelete?.description }}</strong> no valor de
          <strong class="text-gray-900">{{ formatCurrency(transToDelete?.amount) }}</strong>?
        </p>
        <p class="text-[11px] text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-100">
          Esta ação alterará o saldo contábil e o fluxo de caixa histórico da empresa.
        </p>
      </div>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isDeleteModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="button"
          :disabled="deleteLoading"
          class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 cursor-pointer"
          @click="confirmDelete"
        >
          <Loader2 v-if="deleteLoading" :size="14" class="animate-spin" />
          <span>{{ deleteLoading ? 'Excluindo...' : 'Sim, Excluir' }}</span>
        </button>
      </template>
    </AppModal>
  </div>
</template>
