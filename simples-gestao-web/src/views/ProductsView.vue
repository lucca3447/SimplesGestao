<script setup>
import { ref, onMounted, watch } from 'vue';
import apiClient from '@/api/client';
import { formatCurrency } from '@/utils/formatters';
import AppModal from '@/components/common/AppModal.vue';
import {
  Package,
  Search,
  Plus,
  Edit2,
  Trash2,
  RefreshCw,
  AlertCircle,
  CheckCircle2,
  AlertTriangle,
  FolderPlus,
  ChevronLeft,
  ChevronRight,
  Loader2,
  Tag,
} from '@lucide/vue';

// Dados
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');
const onlyLowStock = ref(false);
const error = ref('');
const successMessage = ref('');

// Paginação
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(10);

// Modal Produto (Create / Edit)
const isProductModalOpen = ref(false);
const productFormMode = ref('create'); // 'create' | 'edit'
const productFormLoading = ref(false);
const productErrors = ref({});
const productFormData = ref({
  id: null,
  category_id: '',
  name: '',
  description: '',
  sku: '',
  price: '',
  cost_price: '',
  stock_quantity: 0,
  min_stock: 5,
  is_active: true,
});

// Modal Categoria (Create)
const isCategoryModalOpen = ref(false);
const categoryFormLoading = ref(false);
const categoryErrors = ref({});
const categoryFormData = ref({
  name: '',
  description: '',
});

// Modal Exclusão
const isDeleteModalOpen = ref(false);
const productToDelete = ref(null);
const deleteLoading = ref(false);

// Buscar produtos
async function loadProducts(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const params = {
      page,
      per_page: perPage.value,
    };
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
    if (selectedCategory.value) params.category_id = selectedCategory.value;
    if (onlyLowStock.value) params.low_stock = 1;

    const res = await apiClient.get('/products', { params });
    products.value = res.data.data;
    currentPage.value = res.data.meta.current_page;
    totalPages.value = res.data.meta.last_page;
    totalItems.value = res.data.meta.total;
  } catch (err) {
    console.error('Erro ao carregar produtos:', err);
    error.value = 'Falha ao buscar catálogo de produtos.';
  } finally {
    loading.value = false;
  }
}

// Buscar categorias para dropdown
async function loadCategories() {
  try {
    const res = await apiClient.get('/categories', { params: { all: 1 } });
    categories.value = res.data.data;
  } catch (err) {
    console.error('Erro ao carregar categorias:', err);
  }
}

// Debounce de busca
let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadProducts(1);
  }, 350);
});

function handleCategoryFilter() {
  loadProducts(1);
}

function toggleLowStockFilter() {
  onlyLowStock.value = !onlyLowStock.value;
  loadProducts(1);
}

// Modal Produto
function openCreateProductModal() {
  productFormMode.value = 'create';
  productErrors.value = {};
  productFormData.value = {
    id: null,
    category_id: categories.value[0]?.id || '',
    name: '',
    description: '',
    sku: '',
    price: '',
    cost_price: '',
    stock_quantity: 0,
    min_stock: 5,
    is_active: true,
  };
  isProductModalOpen.value = true;
}

function openEditProductModal(product) {
  productFormMode.value = 'edit';
  productErrors.value = {};
  productFormData.value = {
    id: product.id,
    category_id: product.category_id || product.category?.id || '',
    name: product.name,
    description: product.description || '',
    sku: product.sku || '',
    price: product.price,
    cost_price: product.cost_price || '',
    stock_quantity: product.stock_quantity,
    min_stock: product.min_stock,
    is_active: Boolean(product.is_active),
  };
  isProductModalOpen.value = true;
}

async function handleProductSubmit() {
  productFormLoading.value = true;
  productErrors.value = {};
  try {
    if (productFormMode.value === 'create') {
      await apiClient.post('/products', productFormData.value);
      showNotification('Produto cadastrado com sucesso!');
    } else {
      await apiClient.put(`/products/${productFormData.value.id}`, productFormData.value);
      showNotification('Produto atualizado com sucesso!');
    }
    isProductModalOpen.value = false;
    loadProducts(currentPage.value);
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      productErrors.value = err.response.data.errors;
    } else {
      productErrors.value = { general: [err.response?.data?.message || 'Erro ao salvar produto.'] };
    }
  } finally {
    productFormLoading.value = false;
  }
}

// Modal Categoria
function openCreateCategoryModal() {
  categoryErrors.value = {};
  categoryFormData.value = { name: '', description: '' };
  isCategoryModalOpen.value = true;
}

async function handleCategorySubmit() {
  categoryFormLoading.value = true;
  categoryErrors.value = {};
  try {
    const res = await apiClient.post('/categories', categoryFormData.value);
    showNotification('Categoria criada com sucesso!');
    isCategoryModalOpen.value = false;
    await loadCategories();
    // Se o modal de produto estiver aberto, auto-seleciona a nova categoria
    if (isProductModalOpen.value && res.data?.data?.id) {
      productFormData.value.category_id = res.data.data.id;
    }
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      categoryErrors.value = err.response.data.errors;
    } else {
      categoryErrors.value = { general: [err.response?.data?.message || 'Erro ao criar categoria.'] };
    }
  } finally {
    categoryFormLoading.value = false;
  }
}

// Exclusão
function openDeleteModal(product) {
  productToDelete.value = product;
  isDeleteModalOpen.value = true;
}

async function confirmDelete() {
  if (!productToDelete.value) return;
  deleteLoading.value = true;
  try {
    await apiClient.delete(`/products/${productToDelete.value.id}`);
    showNotification('Produto excluído com sucesso!');
    isDeleteModalOpen.value = false;
    productToDelete.value = null;
    loadProducts(currentPage.value);
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao excluir produto.');
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
  loadProducts();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header com Título e Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
          <Package :size="22" class="text-simples-orange" />
          <span>Catálogo de Produtos & Estoque</span>
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">
          Controle de itens, precificação, margem operacional e alertas de reposição.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-all shadow-xs cursor-pointer"
          @click="openCreateCategoryModal"
        >
          <FolderPlus :size="15" />
          <span>Nova Categoria</span>
        </button>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
          @click="openCreateProductModal"
        >
          <Plus :size="16" />
          <span>Novo Produto</span>
        </button>
      </div>
    </div>

    <!-- Feedback Toast -->
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
        @click="loadProducts(currentPage)"
      >
        Recarregar
      </button>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
        <!-- Campo de Busca -->
        <div class="relative flex-1 max-w-sm">
          <Search :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Buscar por nome ou código SKU..."
            class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          />
        </div>

        <!-- Filtro por Categoria -->
        <select
          v-model="selectedCategory"
          class="px-3 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:outline-none text-gray-700 transition-all cursor-pointer"
          @change="handleCategoryFilter"
        >
          <option value="">Todas as categorias</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>

        <!-- Toggle Baixo Estoque -->
        <button
          type="button"
          class="px-3 py-2 text-xs font-semibold rounded-lg border transition-all flex items-center gap-1.5 cursor-pointer"
          :class="[
            onlyLowStock
              ? 'bg-rose-500 text-white border-rose-500 shadow-xs'
              : 'bg-white hover:bg-rose-50 text-rose-600 border-rose-200'
          ]"
          @click="toggleLowStockFilter"
        >
          <AlertTriangle :size="14" />
          <span>Estoque Baixo</span>
        </button>
      </div>

      <!-- Contadores e Refresh -->
      <div class="flex items-center gap-3 justify-end text-xs text-gray-500">
        <span class="font-medium text-gray-700">Total: <strong class="text-gray-900">{{ totalItems }}</strong> itens</span>
        <button
          type="button"
          class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          title="Atualizar lista"
          :disabled="loading"
          @click="loadProducts(currentPage)"
        >
          <RefreshCw :size="15" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Tabela de Produtos -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
          <thead class="bg-gray-50/80 text-gray-500 font-semibold border-b border-gray-200 uppercase tracking-wider text-[10px]">
            <tr>
              <th scope="col" class="px-5 py-3.5">Produto</th>
              <th scope="col" class="px-5 py-3.5">Categoria</th>
              <th scope="col" class="px-5 py-3.5">Preço Venda</th>
              <th scope="col" class="px-5 py-3.5">Custo Unit.</th>
              <th scope="col" class="px-5 py-3.5">Estoque</th>
              <th scope="col" class="px-5 py-3.5">Status</th>
              <th scope="col" class="px-5 py-3.5 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading Row -->
            <tr v-if="loading && products.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Loader2 :size="24" class="animate-spin text-simples-orange" />
                  <span>Carregando catálogo...</span>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="products.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Package :size="28" class="text-gray-300" />
                  <p class="font-medium text-gray-600">Nenhum produto encontrado.</p>
                  <p class="text-[11px]">Experimente limpar os filtros ou cadastre um novo produto.</p>
                </div>
              </td>
            </tr>

            <!-- Produtos Listados -->
            <tr
              v-for="product in products"
              v-else
              :key="product.id"
              class="hover:bg-gray-50/60 transition-colors"
            >
              <!-- Nome e SKU -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div>
                  <div class="font-semibold text-gray-900">{{ product.name }}</div>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span v-if="product.sku" class="text-[10px] font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-500 font-semibold">
                      {{ product.sku }}
                    </span>
                    <span v-if="product.description" class="text-[11px] text-gray-400 truncate max-w-xs">
                      {{ product.description }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Categoria -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                  <Tag :size="10" class="text-gray-400" />
                  <span>{{ product.category?.name || 'Sem categoria' }}</span>
                </span>
              </td>

              <!-- Preço de Venda -->
              <td class="px-5 py-3.5 whitespace-nowrap font-bold text-gray-900">
                {{ formatCurrency(product.price) }}
              </td>

              <!-- Custo Unitário -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500">
                {{ product.cost_price ? formatCurrency(product.cost_price) : '—' }}
              </td>

              <!-- Estoque e Alerta -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <span
                    class="px-2 py-0.5 rounded-full text-[11px] font-bold border"
                    :class="[
                      product.stock_quantity <= product.min_stock
                        ? 'bg-rose-50 text-rose-700 border-rose-200'
                        : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    ]"
                  >
                    {{ product.stock_quantity }} un
                  </span>
                  <span
                    v-if="product.stock_quantity <= product.min_stock"
                    class="text-[10px] text-rose-500 font-medium flex items-center gap-0.5"
                    title="Abaixo do estoque mínimo recomendado"
                  >
                    <AlertTriangle :size="12" />
                    <span>Mín: {{ product.min_stock }}</span>
                  </span>
                </div>
              </td>

              <!-- Status -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="product.is_active ? 'bg-emerald-100/60 text-emerald-800' : 'bg-gray-100 text-gray-500'"
                >
                  {{ product.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>

              <!-- Ações -->
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    title="Editar Produto"
                    class="p-1.5 text-gray-400 hover:text-simples-orange hover:bg-orange-50 rounded-md transition-colors cursor-pointer"
                    @click="openEditProductModal(product)"
                  >
                    <Edit2 :size="14" />
                  </button>
                  <button
                    type="button"
                    title="Excluir Produto"
                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                    @click="openDeleteModal(product)"
                  >
                    <Trash2 :size="14" />
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
            @click="loadProducts(currentPage - 1)"
          >
            <ChevronLeft :size="14" />
            <span>Anterior</span>
          </button>
          <button
            type="button"
            :disabled="currentPage >= totalPages || loading"
            class="px-2.5 py-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-medium transition-colors cursor-pointer"
            @click="loadProducts(currentPage + 1)"
          >
            <span>Próxima</span>
            <ChevronRight :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criação / Edição de Produto -->
    <AppModal
      v-model="isProductModalOpen"
      :title="productFormMode === 'create' ? 'Cadastrar Novo Produto' : 'Editar Dados do Produto'"
      max-width="max-w-xl"
    >
      <form id="product-form" class="space-y-4" @submit.prevent="handleProductSubmit">
        <div
          v-if="productErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ productErrors.general[0] }}
        </div>

        <!-- Nome do Produto -->
        <div class="space-y-1">
          <label for="prod-name" class="block text-xs font-semibold text-gray-700">
            Nome do Produto <span class="text-rose-500">*</span>
          </label>
          <input
            id="prod-name"
            v-model="productFormData.name"
            type="text"
            required
            placeholder="Ex: Teclado Mecânico RGB"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': productErrors.name }"
          />
          <p v-if="productErrors.name" class="text-[11px] text-rose-600 font-medium">
            {{ productErrors.name[0] }}
          </p>
        </div>

        <!-- Categoria e SKU -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="prod-cat" class="block text-xs font-semibold text-gray-700">
              Categoria <span class="text-rose-500">*</span>
            </label>
            <select
              id="prod-cat"
              v-model="productFormData.category_id"
              required
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all cursor-pointer"
              :class="{ 'border-rose-400 bg-rose-50/20': productErrors.category_id }"
            >
              <option disabled value="">Selecione uma categoria</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="productErrors.category_id" class="text-[11px] text-rose-600 font-medium">
              {{ productErrors.category_id[0] }}
            </p>
          </div>

          <div class="space-y-1">
            <label for="prod-sku" class="block text-xs font-semibold text-gray-700">Código SKU</label>
            <input
              id="prod-sku"
              v-model="productFormData.sku"
              type="text"
              placeholder="Ex: ELE-0001"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all uppercase font-mono"
              :class="{ 'border-rose-400 bg-rose-50/20': productErrors.sku }"
            />
            <p v-if="productErrors.sku" class="text-[11px] text-rose-600 font-medium">
              {{ productErrors.sku[0] }}
            </p>
          </div>
        </div>

        <!-- Preços: Venda e Custo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="prod-price" class="block text-xs font-semibold text-gray-700">
              Preço de Venda (R$) <span class="text-rose-500">*</span>
            </label>
            <input
              id="prod-price"
              v-model="productFormData.price"
              type="number"
              step="0.01"
              min="0"
              required
              placeholder="0,00"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
              :class="{ 'border-rose-400 bg-rose-50/20': productErrors.price }"
            />
            <p v-if="productErrors.price" class="text-[11px] text-rose-600 font-medium">
              {{ productErrors.price[0] }}
            </p>
          </div>

          <div class="space-y-1">
            <label for="prod-cost" class="block text-xs font-semibold text-gray-700">Custo de Aquisição (R$)</label>
            <input
              id="prod-cost"
              v-model="productFormData.cost_price"
              type="number"
              step="0.01"
              min="0"
              placeholder="0,00"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- Estoque e Estoque Mínimo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="prod-stock" class="block text-xs font-semibold text-gray-700">Estoque Atual</label>
            <input
              id="prod-stock"
              v-model="productFormData.stock_quantity"
              type="number"
              min="0"
              required
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            />
          </div>

          <div class="space-y-1">
            <label for="prod-min-stock" class="block text-xs font-semibold text-gray-700">Estoque Mínimo (Alerta)</label>
            <input
              id="prod-min-stock"
              v-model="productFormData.min_stock"
              type="number"
              min="0"
              required
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- Descrição -->
        <div class="space-y-1">
          <label for="prod-desc" class="block text-xs font-semibold text-gray-700">Descrição / Ficha Técnica</label>
          <textarea
            id="prod-desc"
            v-model="productFormData.description"
            rows="2"
            placeholder="Detalhes, especificações e observações..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          ></textarea>
        </div>

        <!-- Ativo / Inativo Checkbox -->
        <div class="flex items-center gap-2 pt-1">
          <input
            id="prod-active"
            v-model="productFormData.is_active"
            type="checkbox"
            class="rounded border-gray-300 text-simples-orange focus:ring-simples-orange"
          />
          <label for="prod-active" class="text-xs font-semibold text-gray-700 select-none cursor-pointer">
            Produto ativo para vendas no catálogo
          </label>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isProductModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="submit"
          form="product-form"
          :disabled="productFormLoading"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="productFormLoading" :size="14" class="animate-spin" />
          <span>{{ productFormLoading ? 'Salvando...' : (productFormMode === 'create' ? 'Cadastrar Produto' : 'Salvar Alterações') }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal Nova Categoria -->
    <AppModal
      v-model="isCategoryModalOpen"
      title="Nova Categoria de Produtos"
      max-width="max-w-md"
    >
      <form id="category-form" class="space-y-4" @submit.prevent="handleCategorySubmit">
        <div
          v-if="categoryErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ categoryErrors.general[0] }}
        </div>

        <div class="space-y-1">
          <label for="cat-name" class="block text-xs font-semibold text-gray-700">
            Nome da Categoria <span class="text-rose-500">*</span>
          </label>
          <input
            id="cat-name"
            v-model="categoryFormData.name"
            type="text"
            required
            placeholder="Ex: Utilidades, Bebidas, Acessórios"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': categoryErrors.name }"
          />
          <p v-if="categoryErrors.name" class="text-[11px] text-rose-600 font-medium">
            {{ categoryErrors.name[0] }}
          </p>
        </div>

        <div class="space-y-1">
          <label for="cat-desc" class="block text-xs font-semibold text-gray-700">Descrição</label>
          <textarea
            id="cat-desc"
            v-model="categoryFormData.description"
            rows="2"
            placeholder="Resumo dos itens que compõem este segmento..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          ></textarea>
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
          form="category-form"
          :disabled="categoryFormLoading"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="categoryFormLoading" :size="14" class="animate-spin" />
          <span>{{ categoryFormLoading ? 'Criando...' : 'Criar Categoria' }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal Excluir Produto -->
    <AppModal
      v-model="isDeleteModalOpen"
      title="Confirmar Exclusão de Produto"
      max-width="max-w-md"
    >
      <div class="space-y-3">
        <p class="text-xs text-gray-600">
          Tem certeza de que deseja remover o produto
          <strong class="text-gray-900">{{ productToDelete?.name }}</strong>?
        </p>
        <p class="text-[11px] text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-100">
          Produtos com histórico de vendas não podem ser excluídos por integridade fiscal; caso deseje retirá-lo de circulação, desmarque a opção "Ativo".
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
