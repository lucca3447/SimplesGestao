<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import logoIcon from '@/assets/logo-icon.png';
import {
  LayoutDashboard,
  Users,
  Package,
  ShoppingCart,
  DollarSign,
  LogOut,
} from '@lucide/vue';

defineProps({
  isCollapsed: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const navigation = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Clientes', path: '/customers', icon: Users },
  { name: 'Produtos & Estoque', path: '/products', icon: Package },
  { name: 'Vendas & Pedidos', path: '/orders', icon: ShoppingCart },
  { name: 'Financeiro', path: '/transactions', icon: DollarSign },
];

function isItemActive(path) {
  if (path === '/') return route.path === '/';
  return route.path.startsWith(path);
}

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}
</script>

<template>
  <aside
    class="bg-white border-r border-gray-200 flex flex-col flex-shrink-0 h-screen sticky top-0 select-none z-20 transition-all duration-200"
    :class="[isCollapsed ? 'w-16' : 'w-60']"
  >
    <!-- Brand Header -->
    <div class="h-14 flex items-center px-4 gap-2.5 border-b border-gray-100 overflow-hidden">
      <img :src="logoIcon" alt="simples" class="w-7 h-7 object-contain flex-shrink-0" />
      <div v-if="!isCollapsed" class="flex items-baseline gap-1 transition-opacity">
        <span class="text-xl font-extrabold tracking-tight text-gray-900 lowercase font-sans">simples</span>
        <span class="w-1.5 h-1.5 rounded-full bg-simples-orange inline-block"></span>
      </div>
    </div>

    <!-- Navigation Links: 1 clique direto para a rota correspondente -->
    <nav class="flex-1 px-2.5 py-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in navigation"
        :key="item.name"
        :to="item.path"
        :title="isCollapsed ? item.name : ''"
        class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-medium transition-all group cursor-pointer"
        :class="[
          isItemActive(item.path)
            ? 'bg-simples-orange-light text-simples-orange font-semibold shadow-xs'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
        ]"
      >
        <component
          :is="item.icon"
          :size="18"
          :class="[
            isItemActive(item.path) ? 'text-simples-orange' : 'text-gray-400 group-hover:text-gray-600'
          ]"
          class="flex-shrink-0"
        />
        <span v-if="!isCollapsed" class="truncate font-medium">{{ item.name }}</span>
      </router-link>
    </nav>

    <!-- User Footer & Logout -->
    <div class="p-3 border-t border-gray-100 bg-[#fafafa]">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-simples-orange-light text-simples-orange font-bold flex items-center justify-center text-xs uppercase flex-shrink-0 border border-simples-orange-border">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
          <div v-if="!isCollapsed" class="min-w-0">
            <p class="text-xs font-semibold truncate text-gray-900">{{ auth.user?.name || 'Gestor' }}</p>
            <span class="text-[10px] text-gray-400 uppercase font-medium">{{ auth.user?.role || 'admin' }}</span>
          </div>
        </div>

        <button
          type="button"
          title="Sair do sistema (Logout)"
          class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors flex-shrink-0 cursor-pointer"
          @click="handleLogout"
        >
          <LogOut :size="16" />
        </button>
      </div>
    </div>
  </aside>
</template>
