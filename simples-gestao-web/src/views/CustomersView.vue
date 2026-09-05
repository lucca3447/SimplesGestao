<script setup>
import { ref, onMounted, watch } from 'vue';
import apiClient from '@/api/client';
import { formatCpfCnpj, formatDate } from '@/utils/formatters';
import AppModal from '@/components/common/AppModal.vue';
import {
  Users,
  Search,
  UserPlus,
  Edit2,
  Trash2,
  RefreshCw,
  AlertCircle,
  CheckCircle2,
  Phone,
  Mail,
  MapPin,
  FileText,
  ChevronLeft,
  ChevronRight,
  Loader2,
} from '@lucide/vue';

// Estado de dados
const customers = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const error = ref('');
const successMessage = ref('');

// Paginação
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const perPage = ref(10);

// Modal de Formulário (Criação / Edição)
const isFormModalOpen = ref(false);
const formMode = ref('create'); // 'create' | 'edit'
const formLoading = ref(false);
const formErrors = ref({});
const formData = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  cpf_cnpj: '',
  address: '',
  notes: '',
});

// Modal de Confirmação de Exclusão
const isDeleteModalOpen = ref(false);
const customerToDelete = ref(null);
const deleteLoading = ref(false);

// Buscar clientes na API
async function loadCustomers(page = 1) {
  loading.value = true;
  error.value = '';
  try {
    const params = {
      page,
      per_page: perPage.value,
    };
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }

    const response = await apiClient.get('/customers', { params });
    customers.value = response.data.data;
    currentPage.value = response.data.meta.current_page;
    totalPages.value = response.data.meta.last_page;
    totalItems.value = response.data.meta.total;
  } catch (err) {
    console.error('Erro ao listar clientes:', err);
    error.value = 'Não foi possível carregar a lista de clientes.';
  } finally {
    loading.value = false;
  }
}

// Debounce de busca
let searchTimeout = null;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadCustomers(1);
  }, 350);
});

// Abrir modal de criação
function openCreateModal() {
  formMode.value = 'create';
  formErrors.value = {};
  formData.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    cpf_cnpj: '',
    address: '',
    notes: '',
  };
  isFormModalOpen.value = true;
}

// Abrir modal de edição
function openEditModal(customer) {
  formMode.value = 'edit';
  formErrors.value = {};
  formData.value = {
    id: customer.id,
    name: customer.name,
    email: customer.email || '',
    phone: customer.phone || '',
    cpf_cnpj: customer.cpf_cnpj || '',
    address: customer.address || '',
    notes: customer.notes || '',
  };
  isFormModalOpen.value = true;
}

// Salvar formulário (Create ou Edit)
async function handleSubmitForm() {
  formLoading.value = true;
  formErrors.value = {};
  try {
    if (formMode.value === 'create') {
      await apiClient.post('/customers', formData.value);
      showNotification('Cliente cadastrado com sucesso!');
    } else {
      await apiClient.put(`/customers/${formData.value.id}`, formData.value);
      showNotification('Dados do cliente atualizados com sucesso!');
    }
    isFormModalOpen.value = false;
    loadCustomers(currentPage.value);
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      formErrors.value = err.response.data.errors;
    } else {
      formErrors.value = { general: [err.response?.data?.message || 'Erro ao salvar cliente.'] };
    }
  } finally {
    formLoading.value = false;
  }
}

// Exclusão
function openDeleteModal(customer) {
  customerToDelete.value = customer;
  isDeleteModalOpen.value = true;
}

async function confirmDelete() {
  if (!customerToDelete.value) return;
  deleteLoading.value = true;
  try {
    await apiClient.delete(`/customers/${customerToDelete.value.id}`);
    showNotification('Cliente excluído com sucesso!');
    isDeleteModalOpen.value = false;
    customerToDelete.value = null;
    loadCustomers(currentPage.value);
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao excluir cliente.');
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
  loadCustomers();
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header com Título e Ação Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
          <Users :size="22" class="text-simples-orange" />
          <span>Gestão de Clientes</span>
        </h1>
        <p class="text-xs text-gray-500 mt-0.5">
          Cadastre, pesquise e mantenha atualizada a base de clientes do seu negócio.
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer"
        @click="openCreateModal"
      >
        <UserPlus :size="16" />
        <span>Novo Cliente</span>
      </button>
    </div>

    <!-- Feedback Toast / Sucesso -->
    <div
      v-if="successMessage"
      class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs flex items-center gap-2 animate-fade-in"
    >
      <CheckCircle2 :size="16" class="text-emerald-600 flex-shrink-0" />
      <span>{{ successMessage }}</span>
    </div>

    <!-- Feedback de Erro Geral -->
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
        @click="loadCustomers(currentPage)"
      >
        Recarregar
      </button>
    </div>

    <!-- Barra de Filtros e Busca -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="relative w-full sm:w-80">
        <Search :size="15" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Buscar por nome, e-mail ou CPF/CNPJ..."
          class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
        />
      </div>

      <div class="flex items-center gap-3 w-full sm:w-auto justify-end text-xs text-gray-500">
        <span class="font-medium text-gray-700">Total: <strong class="text-gray-900">{{ totalItems }}</strong> clientes</span>
        <button
          type="button"
          class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          title="Atualizar lista"
          :disabled="loading"
          @click="loadCustomers(currentPage)"
        >
          <RefreshCw :size="15" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Tabela de Clientes -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
          <thead class="bg-gray-50/80 text-gray-500 font-semibold border-b border-gray-200 uppercase tracking-wider text-[10px]">
            <tr>
              <th scope="col" class="px-5 py-3.5">Cliente</th>
              <th scope="col" class="px-5 py-3.5">Documento</th>
              <th scope="col" class="px-5 py-3.5">Contato</th>
              <th scope="col" class="px-5 py-3.5">Cadastro</th>
              <th scope="col" class="px-5 py-3.5 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading Row -->
            <tr v-if="loading && customers.length === 0">
              <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Loader2 :size="24" class="animate-spin text-simples-orange" />
                  <span>Carregando clientes...</span>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-else-if="customers.length === 0">
              <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                  <Users :size="28" class="text-gray-300" />
                  <p class="font-medium text-gray-600">Nenhum cliente encontrado.</p>
                  <p class="text-[11px]">Tente ajustar a busca ou cadastre um novo cliente.</p>
                </div>
              </td>
            </tr>

            <!-- Clientes Listados -->
            <tr
              v-for="customer in customers"
              v-else
              :key="customer.id"
              class="hover:bg-gray-50/60 transition-colors"
            >
              <!-- Nome e Avatar -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-orange-50 border border-orange-100 text-simples-orange font-bold flex items-center justify-center flex-shrink-0 text-xs">
                    {{ customer.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900">{{ customer.name }}</div>
                    <div v-if="customer.address" class="text-[11px] text-gray-400 truncate max-w-xs flex items-center gap-1">
                      <MapPin :size="10" />
                      <span>{{ customer.address }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Documento (CPF/CNPJ) -->
              <td class="px-5 py-3.5 whitespace-nowrap font-mono text-gray-700">
                {{ formatCpfCnpj(customer.cpf_cnpj) }}
              </td>

              <!-- Contato (Email / Telefone) -->
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="space-y-0.5">
                  <div v-if="customer.email" class="flex items-center gap-1.5 text-gray-700">
                    <Mail :size="11" class="text-gray-400" />
                    <span>{{ customer.email }}</span>
                  </div>
                  <div v-if="customer.phone" class="flex items-center gap-1.5 text-gray-500 text-[11px]">
                    <Phone :size="11" class="text-gray-400" />
                    <span>{{ customer.phone }}</span>
                  </div>
                  <span v-if="!customer.email && !customer.phone" class="text-gray-400 italic text-[11px]">—</span>
                </div>
              </td>

              <!-- Data de Cadastro -->
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500">
                {{ formatDate(customer.created_at) }}
              </td>

              <!-- Ações -->
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    title="Editar Cliente"
                    class="p-1.5 text-gray-400 hover:text-simples-orange hover:bg-orange-50 rounded-md transition-colors cursor-pointer"
                    @click="openEditModal(customer)"
                  >
                    <Edit2 :size="14" />
                  </button>
                  <button
                    type="button"
                    title="Excluir Cliente"
                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                    @click="openDeleteModal(customer)"
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
            @click="loadCustomers(currentPage - 1)"
          >
            <ChevronLeft :size="14" />
            <span>Anterior</span>
          </button>
          <button
            type="button"
            :disabled="currentPage >= totalPages || loading"
            class="px-2.5 py-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 rounded-md disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 font-medium transition-colors cursor-pointer"
            @click="loadCustomers(currentPage + 1)"
          >
            <span>Próxima</span>
            <ChevronRight :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criação / Edição de Cliente -->
    <AppModal
      v-model="isFormModalOpen"
      :title="formMode === 'create' ? 'Cadastrar Novo Cliente' : 'Editar Dados do Cliente'"
      max-width="max-w-xl"
    >
      <form id="customer-form" class="space-y-4" @submit.prevent="handleSubmitForm">
        <!-- Erro Geral -->
        <div
          v-if="formErrors.general"
          class="bg-rose-50 border border-rose-200 text-rose-700 px-3 py-2 rounded-lg text-xs"
        >
          {{ formErrors.general[0] }}
        </div>

        <!-- Nome Completo -->
        <div class="space-y-1">
          <label for="cust-name" class="block text-xs font-semibold text-gray-700">
            Nome Completo ou Razão Social <span class="text-rose-500">*</span>
          </label>
          <input
            id="cust-name"
            v-model="formData.name"
            type="text"
            required
            placeholder="Ex: João da Silva ou Silva & Filhos LTDA"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': formErrors.name }"
          />
          <p v-if="formErrors.name" class="text-[11px] text-rose-600 font-medium">
            {{ formErrors.name[0] }}
          </p>
        </div>

        <!-- Linha: CPF/CNPJ e Telefone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label for="cust-doc" class="block text-xs font-semibold text-gray-700">CPF ou CNPJ</label>
            <input
              id="cust-doc"
              v-model="formData.cpf_cnpj"
              type="text"
              placeholder="000.000.000-00 ou 00.000.000/0000-00"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
              :class="{ 'border-rose-400 bg-rose-50/20': formErrors.cpf_cnpj }"
            />
            <p v-if="formErrors.cpf_cnpj" class="text-[11px] text-rose-600 font-medium">
              {{ formErrors.cpf_cnpj[0] }}
            </p>
          </div>

          <div class="space-y-1">
            <label for="cust-phone" class="block text-xs font-semibold text-gray-700">Telefone / Celular</label>
            <input
              id="cust-phone"
              v-model="formData.phone"
              type="tel"
              placeholder="(00) 90000-0000"
              class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- E-mail -->
        <div class="space-y-1">
          <label for="cust-email" class="block text-xs font-semibold text-gray-700">E-mail corporativo</label>
          <input
            id="cust-email"
            v-model="formData.email"
            type="email"
            placeholder="cliente@empresa.com"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
            :class="{ 'border-rose-400 bg-rose-50/20': formErrors.email }"
          />
          <p v-if="formErrors.email" class="text-[11px] text-rose-600 font-medium">
            {{ formErrors.email[0] }}
          </p>
        </div>

        <!-- Endereço Completo -->
        <div class="space-y-1">
          <label for="cust-address" class="block text-xs font-semibold text-gray-700">Endereço Comercial / Residencial</label>
          <input
            id="cust-address"
            v-model="formData.address"
            type="text"
            placeholder="Rua, Número, Bairro, Cidade - UF"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          />
        </div>

        <!-- Observações -->
        <div class="space-y-1">
          <label for="cust-notes" class="block text-xs font-semibold text-gray-700">Observações Internas</label>
          <textarea
            id="cust-notes"
            v-model="formData.notes"
            rows="2"
            placeholder="Condições comerciais especiais, contato do setor financeiro..."
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:ring-1 focus:ring-simples-orange focus:outline-none transition-all"
          ></textarea>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
          @click="isFormModalOpen = false"
        >
          Cancelar
        </button>
        <button
          type="submit"
          form="customer-form"
          :disabled="formLoading"
          class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="formLoading" :size="14" class="animate-spin" />
          <span>{{ formLoading ? 'Salvando...' : (formMode === 'create' ? 'Cadastrar Cliente' : 'Salvar Alterações') }}</span>
        </button>
      </template>
    </AppModal>

    <!-- Modal de Confirmação de Exclusão -->
    <AppModal
      v-model="isDeleteModalOpen"
      title="Confirmar Exclusão"
      max-width="max-w-md"
    >
      <div class="space-y-3">
        <p class="text-xs text-gray-600">
          Tem certeza de que deseja excluir o cliente
          <strong class="text-gray-900">{{ customerToDelete?.name }}</strong>?
        </p>
        <p class="text-[11px] text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-100">
          Esta ação não poderá ser desfeita caso o cliente não tenha vínculos históricos.
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
