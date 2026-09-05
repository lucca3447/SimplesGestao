<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import logoIcon from '@/assets/logo-icon.png';
import {
  LayoutDashboard,
  Users,
  Package,
  ShoppingCart,
  DollarSign,
  Search,
  ChevronRight,
  LogOut
} from '@lucide/vue';

const route = useRoute();
const auth = useAuthStore();
const searchQuery = ref('');

const navigation = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Cliente', path: '/customers', icon: Users, hasSubmenu: true },
  { name: 'Produto', path: '/products', icon: Package, hasSubmenu: true },
  { name: 'Venda', path: '/orders', icon: ShoppingCart, hasSubmenu: true },
  { name: 'Financeiro', path: '/transactions', icon: DollarSign, hasSubmenu: true },
];

function isActive(path) {
  if (path === '/') return route.path === '/';
  return route.path.startsWith(path);
}
</script>

<template>
  <aside class="w-60 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 h-screen sticky top-0 select-none z-20">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-5 gap-2.5 border-b border-gray-100">
      <img :src="logoIcon" alt="simples" class="w-7 h-7 object-contain" />
      <div class="flex items-baseline gap-1">
        <span class="text-xl font-extrabold tracking-tight text-gray-900 lowercase font-sans">simples</span>
        <span class="w-1.5 h-1.5 rounded-full bg-simples-orange inline-block"></span>
      </div>
    </div>

    <!-- Search Input -->
    <div class="px-3 pt-3 pb-2">
      <div class="relative">
        <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Procurar item"
          class="w-full pl-8 pr-3 py-1.5 bg-[#f4f4f4] hover:bg-gray-100 focus:bg-white text-xs text-gray-700 placeholder-gray-400 rounded-lg border border-transparent focus:border-gray-300 focus:outline-none transition-all"
        />
      </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in navigation"
        :key="item.name"
        :to="item.path"
        class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all group"
        :class="[
          isActive(item.path)
            ? 'bg-simples-orange-light text-simples-orange font-semibold shadow-xs'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
        ]"
      >
        <div class="flex items-center gap-2.5">
          <component
            :is="item.icon"
            :size="16"
            :class="[
              isActive(item.path) ? 'text-simples-orange' : 'text-gray-400 group-hover:text-gray-600'
            ]"
          />
          <span>{{ item.name }}</span>
        </div>

        <ChevronRight
          v-if="item.hasSubmenu"
          :size="13"
          :class="[
            isActive(item.path) ? 'text-simples-orange rotate-90' : 'text-gray-300 group-hover:text-gray-400'
          ]"
          class="transition-transform duration-150"
        />
      </router-link>
    </nav>

    <!-- User Footer & Logout -->
    <div class="p-3 border-t border-gray-100 bg-[#fafafa]">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-simples-orange-light text-simples-orange font-bold flex items-center justify-center text-xs uppercase flex-shrink-0 border border-simples-orange-border">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
          <div class="min-w-0">
            <p class="text-xs font-semibold truncate text-gray-900">{{ auth.user?.name || 'Arthur Lima' }}</p>
            <span class="text-[10px] text-gray-400 uppercase font-medium">{{ auth.user?.role || 'admin' }}</span>
          </div>
        </div>

        <button
          type="button"
          title="Sair do sistema"
          class="p-1.5 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors flex-shrink-0"
          @click="auth.logout"
        >
          <LogOut :size="16" />
        </button>
      </div>
    </div>
  </aside>
</template>
