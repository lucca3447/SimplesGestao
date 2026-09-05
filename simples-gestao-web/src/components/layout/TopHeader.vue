<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
  Menu,
  Gift,
  Search,
  Moon,
  Smile,
  Bell,
  Settings,
  ChevronDown,
  LogOut,
  FileCode,
  User,
} from '@lucide/vue';

const router = useRouter();
const auth = useAuthStore();
const clientSearch = ref('');
const isUserMenuOpen = ref(false);

function handleClientSearch() {
  if (clientSearch.value.trim()) {
    router.push({ path: '/customers', query: { search: clientSearch.value.trim() } });
    clientSearch.value = '';
  }
}

function handleLogout() {
  auth.logout();
  router.push('/login');
}
</script>

<template>
  <header class="h-14 bg-white border-b border-gray-200 px-6 flex items-center justify-between sticky top-0 z-10 select-none">
    <!-- Left: Hamburger icon -->
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
        aria-label="Abrir menu"
      >
        <Menu :size="18" />
      </button>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3.5">
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
        <button type="button" title="Modo Noturno" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
          <Moon :size="16" />
        </button>
        <button type="button" title="Feedback" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
          <Smile :size="16" />
        </button>
        <div class="relative">
          <button type="button" title="Notificações" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
            <Bell :size="16" />
          </button>
          <span class="absolute top-1 right-1 w-3.5 h-3.5 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">3</span>
        </div>
        <button type="button" title="Configurações" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
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

        <!-- Dropdown Menu -->
        <div
          v-if="isUserMenuOpen"
          class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-30 text-xs animate-fade-in"
        >
          <div class="px-3 py-2 border-b border-gray-100">
            <p class="font-bold text-gray-900 truncate">{{ auth.user?.name }}</p>
            <p class="text-[11px] text-gray-400 truncate">{{ auth.user?.email }}</p>
            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
              {{ auth.user?.role }}
            </span>
          </div>

          <a
            href="http://localhost:8000/docs/api"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 text-gray-700 font-medium"
            @click="isUserMenuOpen = false"
          >
            <FileCode :size="14" class="text-simples-orange" />
            <span>Documentação API</span>
          </a>

          <button
            type="button"
            class="w-full flex items-center gap-2 px-3 py-2 hover:bg-rose-50 text-rose-600 font-semibold text-left transition-colors cursor-pointer border-t border-gray-100"
            @click="handleLogout"
          >
            <LogOut :size="14" />
            <span>Encerrar Sessão</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
