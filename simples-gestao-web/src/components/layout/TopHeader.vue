<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppModal from '@/components/common/AppModal.vue';
import {
  Menu,
  Gift,
  Search,
  Moon,
  Sun,
  Smile,
  Bell,
  Settings,
  ChevronDown,
  LogOut,
  FileCode,
  Check,
  Copy,
  Star,
  Server,
  Database,
  ExternalLink,
  ShieldCheck,
} from '@lucide/vue';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const auth = useAuthStore();
const clientSearch = ref('');

// Menus e Modais
const isUserMenuOpen = ref(false);
const isNotificationsOpen = ref(false);
const isReferralModalOpen = ref(false);
const isFeedbackModalOpen = ref(false);
const isSettingsModalOpen = ref(false);

// Toast
const toastMessage = ref('');
function showToast(msg) {
  toastMessage.value = msg;
  setTimeout(() => {
    toastMessage.value = '';
  }, 3500);
}

// Notificações
const unreadCount = ref(3);
const notifications = ref([
  {
    id: 1,
    title: 'Alerta de Baixo Estoque',
    desc: '2 produtos atingiram a quantidade mínima em estoque.',
    to: '/products?filter=low_stock',
    unread: true,
  },
  {
    id: 2,
    title: 'Vendas Pendentes',
    desc: 'Há orçamentos aguardando confirmação e baixa de estoque.',
    to: '/orders?status=pending',
    unread: true,
  },
  {
    id: 3,
    title: 'Fluxo de Caixa Atualizado',
    desc: 'Receitas e despesas consolidadas com saldo positivo.',
    to: '/transactions',
    unread: true,
  },
]);

function markAllNotificationsRead() {
  unreadCount.value = 0;
  notifications.value.forEach(n => n.unread = false);
}

function handleNotificationClick(notif) {
  notif.unread = false;
  unreadCount.value = Math.max(0, unreadCount.value - 1);
  isNotificationsOpen.value = false;
  if (notif.to) {
    router.push(notif.to);
  }
}

// Indique e Ganhe
const copiedReferral = ref(false);
const referralLink = ref('https://simples.local/convite?ref=GESTOR-2026');
async function copyReferralLink() {
  try {
    await navigator.clipboard.writeText(referralLink.value);
    copiedReferral.value = true;
    setTimeout(() => {
      copiedReferral.value = false;
    }, 3000);
  } catch (err) {
    console.error('Falha ao copiar:', err);
  }
}

// Feedback
const feedbackRating = ref(5);
const feedbackText = ref('');
const feedbackSubmitted = ref(false);
function submitFeedback() {
  feedbackSubmitted.value = true;
  setTimeout(() => {
    isFeedbackModalOpen.value = false;
    feedbackSubmitted.value = false;
    feedbackText.value = '';
    showToast('Agradecemos seu feedback sobre o Simples ERP!');
  }, 1200);
}

// Modo Noturno
const isDarkMode = ref(false);
function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value;
  showToast(
    isDarkMode.value
      ? '🌙 Modo escuro ativado para este ambiente!'
      : '☀️ Modo claro restaurado!'
  );
}

// Busca de clientes
function handleClientSearch() {
  if (clientSearch.value.trim()) {
    router.push({ path: '/customers', query: { search: clientSearch.value.trim() } });
    clientSearch.value = '';
  }
}

// Logout com redirecionamento
async function handleLogout() {
  isUserMenuOpen.value = false;
  await auth.logout();
  router.push('/login');
}
</script>

<template>
  <header class="h-14 bg-white border-b border-gray-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-10 select-none">
    <!-- Left: Hamburger icon -->
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
        aria-label="Recolher ou expandir menu lateral"
        title="Recolher/Expandir menu"
        @click="$emit('toggle-sidebar')"
      >
        <Menu :size="18" />
      </button>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-2 sm:gap-3.5">
      <!-- Link Documentação Swagger API -->
      <a
        href="http://localhost:8000/docs/api"
        target="_blank"
        rel="noopener noreferrer"
        class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-100 transition-colors"
        title="Documentação Interativa OpenAPI / Swagger"
      >
        <FileCode :size="14" class="text-simples-orange" />
        <span>Docs API</span>
      </a>

      <!-- Indique e Ganhe -->
      <button
        type="button"
        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-[#fef2eb] text-simples-orange border border-simples-orange-border rounded-lg text-xs font-semibold hover:bg-[#fde7db] transition-colors cursor-pointer"
        @click="isReferralModalOpen = true"
      >
        <Gift :size="14" />
        <span>Indique e Ganhe</span>
      </button>

      <!-- Localizador de Clientes -->
      <div class="relative hidden sm:block">
        <Search :size="13" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="clientSearch"
          type="text"
          placeholder="Localizador de Clientes (Enter)"
          class="w-48 lg:w-56 pl-7 pr-3 py-1.5 bg-[#f4f4f4] hover:bg-gray-100 focus:bg-white text-xs text-gray-700 placeholder-gray-400 rounded-lg border border-transparent focus:border-gray-300 focus:outline-none transition-all"
          @keyup.enter="handleClientSearch"
        />
      </div>

      <!-- Quick Action Icons -->
      <div class="flex items-center gap-1 text-gray-400">
        <!-- Modo Noturno -->
        <button
          type="button"
          :title="isDarkMode ? 'Mudar para tema claro' : 'Mudar para tema escuro'"
          class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          @click="toggleDarkMode"
        >
          <Sun v-if="isDarkMode" :size="16" class="text-amber-500" />
          <Moon v-else :size="16" />
        </button>

        <!-- Feedback -->
        <button
          type="button"
          title="Enviar Feedback do Sistema"
          class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          @click="isFeedbackModalOpen = true"
        >
          <Smile :size="16" />
        </button>

        <!-- Notificações com Dropdown -->
        <div class="relative">
          <button
            type="button"
            title="Notificações do Sistema"
            class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
            @click="isNotificationsOpen = !isNotificationsOpen"
          >
            <Bell :size="16" />
          </button>
          <span
            v-if="unreadCount > 0"
            class="absolute top-1 right-1 w-3.5 h-3.5 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center pointer-events-none"
          >
            {{ unreadCount }}
          </span>

          <!-- Dropdown Notificações -->
          <div
            v-if="isNotificationsOpen"
            class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-xl py-2 z-30 text-xs animate-fade-in"
          >
            <div class="px-3.5 py-2 border-b border-gray-100 flex items-center justify-between">
              <span class="font-bold text-gray-900">Notificações</span>
              <button
                v-if="unreadCount > 0"
                type="button"
                class="text-[10px] text-simples-orange hover:underline font-semibold cursor-pointer"
                @click="markAllNotificationsRead"
              >
                Marcar lidas
              </button>
            </div>

            <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
              <div
                v-for="notif in notifications"
                :key="notif.id"
                class="p-3 hover:bg-gray-50 cursor-pointer transition-colors"
                :class="{ 'bg-orange-50/20': notif.unread }"
                @click="handleNotificationClick(notif)"
              >
                <div class="flex items-start justify-between gap-2">
                  <p class="font-semibold text-gray-900 leading-tight">{{ notif.title }}</p>
                  <span v-if="notif.unread" class="w-1.5 h-1.5 rounded-full bg-simples-orange flex-shrink-0 mt-1"></span>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">{{ notif.desc }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Configurações -->
        <button
          type="button"
          title="Configurações do Sistema"
          class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
          @click="isSettingsModalOpen = true"
        >
          <Settings :size="16" />
        </button>
      </div>

      <!-- User Profile Dropdown Pill -->
      <div class="relative">
        <button
          type="button"
          class="flex items-center gap-2 pl-2 border-l border-gray-200 hover:opacity-80 transition-opacity cursor-pointer"
          @click="isUserMenuOpen = !isUserMenuOpen"
        >
          <div class="w-7 h-7 rounded-full bg-gray-900 text-white text-xs font-semibold flex items-center justify-center">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
          <span class="text-xs font-semibold text-gray-700 hidden lg:inline">{{ auth.user?.name || 'Gestor' }}</span>
          <ChevronDown :size="12" class="text-gray-400" />
        </button>

        <!-- Dropdown Menu do Usuário -->
        <div
          v-if="isUserMenuOpen"
          class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-xl py-1 z-30 text-xs animate-fade-in"
        >
          <div class="px-3.5 py-2.5 border-b border-gray-100">
            <p class="font-bold text-gray-900 truncate">{{ auth.user?.name }}</p>
            <p class="text-[11px] text-gray-400 truncate">{{ auth.user?.email }}</p>
            <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
              Perfil: {{ auth.user?.role }}
            </span>
          </div>

          <a
            href="http://localhost:8000/docs/api"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 px-3.5 py-2 hover:bg-gray-50 text-gray-700 font-medium"
            @click="isUserMenuOpen = false"
          >
            <FileCode :size="14" class="text-simples-orange" />
            <span>Documentação OpenAPI</span>
          </a>

          <button
            type="button"
            class="w-full flex items-center gap-2 px-3.5 py-2 hover:bg-rose-50 text-rose-600 font-semibold text-left transition-colors cursor-pointer border-t border-gray-100"
            @click="handleLogout"
          >
            <LogOut :size="14" />
            <span>Encerrar Sessão (Sair)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Flutuante -->
    <div
      v-if="toastMessage"
      class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white text-xs px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2 animate-fade-in"
    >
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Modal Indique e Ganhe -->
    <AppModal
      v-model="isReferralModalOpen"
      title="Programa Indique e Ganhe — Simples"
      max-width="max-w-md"
    >
      <div class="space-y-4 text-xs text-gray-600">
        <div class="w-12 h-12 rounded-full bg-orange-50 text-simples-orange flex items-center justify-center mx-auto">
          <Gift :size="24" />
        </div>
        <div class="text-center space-y-1">
          <h4 class="text-sm font-bold text-gray-900">Compartilhe e ganhe benefícios</h4>
          <p>Indique o Simples ERP para outro empresário. Quando ele ativar a assinatura, ambos ganham 30 dias de isenção total!</p>
        </div>

        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-2">
          <label class="block text-[11px] font-semibold text-gray-700">Seu link exclusivo de indicação:</label>
          <div class="flex items-center gap-2">
            <input
              type="text"
              readonly
              :value="referralLink"
              class="flex-1 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 select-all font-mono"
            />
            <button
              type="button"
              class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
              :class="copiedReferral ? 'bg-emerald-600 text-white' : 'bg-simples-orange text-white hover:bg-simples-orange-hover'"
              @click="copyReferralLink"
            >
              <Check v-if="copiedReferral" :size="14" />
              <Copy v-else :size="14" />
              <span>{{ copiedReferral ? 'Copiado!' : 'Copiar' }}</span>
            </button>
          </div>
        </div>
      </div>
      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold cursor-pointer"
          @click="isReferralModalOpen = false"
        >
          Fechar
        </button>
      </template>
    </AppModal>

    <!-- Modal Feedback -->
    <AppModal
      v-model="isFeedbackModalOpen"
      title="Enviar Feedback"
      max-width="max-w-md"
    >
      <form class="space-y-4 text-xs" @submit.prevent="submitFeedback">
        <p class="text-gray-600">Como está sendo sua experiência com o sistema Simples?</p>

        <!-- Rating Estrelas -->
        <div class="flex items-center justify-center gap-2 py-2">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            class="p-1 cursor-pointer transition-transform hover:scale-110"
            @click="feedbackRating = star"
          >
            <Star
              :size="24"
              :class="star <= feedbackRating ? 'text-amber-400 fill-amber-400' : 'text-gray-300'"
            />
          </button>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-semibold text-gray-700">Deixe suas sugestões ou comentários:</label>
          <textarea
            v-model="feedbackText"
            rows="3"
            placeholder="O que você mais gostou ou gostaria de ver nas próximas atualizações?"
            class="w-full px-3 py-2 bg-gray-50 focus:bg-white text-xs text-gray-900 rounded-lg border border-gray-200 focus:border-simples-orange focus:outline-none"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold cursor-pointer"
            @click="isFeedbackModalOpen = false"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="px-5 py-2 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold shadow-xs cursor-pointer"
          >
            {{ feedbackSubmitted ? 'Enviando...' : 'Enviar Avaliação' }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Modal Configurações do Sistema -->
    <AppModal
      v-model="isSettingsModalOpen"
      title="Configurações & Diagnóstico do Sistema"
      max-width="max-w-md"
    >
      <div class="space-y-4 text-xs text-gray-600">
        <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2.5">
          <div class="flex items-center justify-between">
            <span class="flex items-center gap-1.5 font-medium text-gray-700">
              <Server :size="14" class="text-simples-orange" />
              <span>Backend API</span>
            </span>
            <span class="font-mono text-gray-900 font-bold">Laravel 11.x (PHP 8.4)</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="flex items-center gap-1.5 font-medium text-gray-700">
              <Database :size="14" class="text-simples-orange" />
              <span>Banco de Dados</span>
            </span>
            <span class="font-mono text-gray-900 font-bold">PostgreSQL 16 (Porta 5433)</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="flex items-center gap-1.5 font-medium text-gray-700">
              <ShieldCheck :size="14" class="text-emerald-600" />
              <span>Autenticação</span>
            </span>
            <span class="font-mono text-emerald-700 font-bold">Sanctum Stateless Token</span>
          </div>

          <div class="flex items-center justify-between border-t border-gray-200/60 pt-2">
            <span class="font-medium text-gray-700">Versão da Aplicação</span>
            <span class="font-bold text-simples-orange">v1.0.0 (Release)</span>
          </div>
        </div>

        <p class="text-[11px] text-gray-400">
          Todas as transações operacionais de pedidos executam com locks pessimistas e garantem integridade atômica com o fluxo de caixa.
        </p>
      </div>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold cursor-pointer"
          @click="isSettingsModalOpen = false"
        >
          Fechar
        </button>
      </template>
    </AppModal>
  </header>
</template>
