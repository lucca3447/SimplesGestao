<script setup>
import { ref } from 'vue';
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
} from '@lucide/vue';

const auth = useAuthStore();
const clientSearch = ref('');
</script>

<template>
  <header class="h-14 bg-white border-b border-gray-200 px-6 flex items-center justify-between sticky top-0 z-10 select-none">
    <!-- Left: Hamburger icon -->
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
      >
        <Menu :size="18" />
      </button>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3.5">
      <!-- Indique e Ganhe -->
      <button
        type="button"
        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-[#fef2eb] text-simples-orange border border-simples-orange-border rounded-lg text-xs font-semibold hover:bg-[#fde7db] transition-colors"
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
          placeholder="Localizador de Clientes"
          class="w-48 lg:w-56 pl-7 pr-3 py-1.5 bg-[#f4f4f4] hover:bg-gray-100 focus:bg-white text-xs text-gray-700 placeholder-gray-400 rounded-lg border border-transparent focus:border-gray-300 focus:outline-none transition-all"
        />
      </div>

      <!-- Quick Action Icons -->
      <div class="flex items-center gap-1 text-gray-400">
        <button type="button" title="Modo Noturno" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
          <Moon :size="16" />
        </button>
        <button type="button" title="Feedback" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
          <Smile :size="16" />
        </button>
        <div class="relative">
          <button type="button" title="Notificações" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <Bell :size="16" />
          </button>
          <span class="absolute top-1 right-1 w-3.5 h-3.5 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">7</span>
        </div>
        <button type="button" title="Configurações" class="p-1.5 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
          <Settings :size="16" />
        </button>
      </div>

      <!-- User Profile Dropdown Pill -->
      <div class="flex items-center gap-2 pl-2 border-l border-gray-200">
        <div class="w-7 h-7 rounded-full bg-gray-900 text-white text-xs font-semibold flex items-center justify-center">
          {{ auth.user?.name?.charAt(0) || 'A' }}
        </div>
        <span class="text-xs font-semibold text-gray-700 hidden lg:inline">{{ auth.user?.name || 'arthurlima' }}</span>
        <ChevronDown :size="12" class="text-gray-400" />
      </div>
    </div>
  </header>
</template>
