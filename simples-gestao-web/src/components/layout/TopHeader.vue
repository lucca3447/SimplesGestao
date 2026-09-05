<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
  Menu,
  Search,
  ChevronDown,
  LogOut,
  FileCode,
  User,
} from '@lucide/vue';

defineEmits(['toggle-sidebar']);

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

async function handleLogout() {
  isUserMenuOpen.value = false;
  await auth.logout();
  router.push('/login');
}
</script>

<template>
  <header class="h-14 bg-white border-b border-gray-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-10 select-none">
    <!-- Left: Hamburger toggle -->
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

    <!-- Right Controls: apenas o que é real e funcional -->
    <div class="flex items-center gap-3">
      <!-- Localizador de Clientes (conecta à API via busca em /customers) -->
      <div class="relative">
        <Search :size="13" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="clientSearch"
          type="text"
          placeholder="Localizador de Clientes (Enter)"
          class="w-44 sm:w-56 pl-7 pr-3 py-1.5 bg-[#f4f4f4] hover:bg-gray-100 focus:bg-white text-xs text-gray-700 placeholder-gray-400 rounded-lg border border-transparent focus:border-gray-300 focus:outline-none transition-all"
          @keyup.enter="handleClientSearch"
        />
      </div>

      <!-- Link Documentação Swagger / OpenAPI -->
      <a
        href="http://localhost:8000/docs/api"
        target="_blank"
        rel="noopener noreferrer"
        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-100 transition-colors"
        title="Documentação Interativa OpenAPI / Swagger"
      >
        <FileCode :size="14" class="text-simples-orange" />
        <span>Docs API</span>
      </a>

      <!-- User Profile Dropdown Pill -->
      <div class="relative">
        <button
          type="button"
          class="flex items-center gap-2 pl-2 sm:pl-3 border-l border-gray-200 hover:opacity-80 transition-opacity cursor-pointer"
          @click="isUserMenuOpen = !isUserMenuOpen"
        >
          <div class="w-7 h-7 rounded-full bg-gray-900 text-white text-xs font-semibold flex items-center justify-center">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
          <span class="text-xs font-semibold text-gray-700 hidden md:inline">{{ auth.user?.name || 'Gestor' }}</span>
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
  </header>
</template>
