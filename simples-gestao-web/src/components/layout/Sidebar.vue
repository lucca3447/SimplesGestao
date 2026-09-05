<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
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
  ChevronDown,
  LogOut,
  Plus,
  AlertTriangle,
  Clock,
  TrendingUp,
  TrendingDown,
} from '@lucide/vue';

const props = defineProps({
  isCollapsed: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const searchQuery = ref('');
const openMenus = ref({
  customers: false,
  products: false,
  orders: false,
  transactions: false,
});

const navigation = [
  {
    name: 'Dashboard',
    path: '/',
    icon: LayoutDashboard,
  },
  {
    name: 'Cliente',
    key: 'customers',
    path: '/customers',
    icon: Users,
    children: [
      { name: 'Todos os Clientes', path: '/customers' },
      { name: 'Novo Cliente', path: '/customers', action: 'new', icon: Plus },
    ],
  },
  {
    name: 'Produto',
    key: 'products',
    path: '/products',
    icon: Package,
    children: [
      { name: 'Catálogo de Produtos', path: '/products' },
      { name: 'Novo Produto', path: '/products', action: 'new', icon: Plus },
      { name: 'Estoque Crítico', path: '/products', filter: 'low_stock', icon: AlertTriangle },
    ],
  },
  {
    name: 'Venda',
    key: 'orders',
    path: '/orders',
    icon: ShoppingCart,
    children: [
      { name: 'Listagem de Vendas', path: '/orders' },
      { name: 'Novo Pedido', path: '/orders', action: 'new', icon: Plus },
      { name: 'Vendas Pendentes', path: '/orders', status: 'pending', icon: Clock },
    ],
  },
  {
    name: 'Financeiro',
    key: 'transactions',
    path: '/transactions',
    icon: DollarSign,
    children: [
      { name: 'Livro Caixa', path: '/transactions' },
      { name: 'Receitas (Entradas)', path: '/transactions', type: 'income', icon: TrendingUp },
      { name: 'Despesas (Saídas)', path: '/transactions', type: 'expense', icon: TrendingDown },
    ],
  },
];

// Filtro de busca na sidebar
const filteredNavigation = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return navigation;

  return navigation.filter((item) => {
    const matchParent = item.name.toLowerCase().includes(query);
    const matchChild = item.children?.some((c) => c.name.toLowerCase().includes(query));
    return matchParent || matchChild;
  });
});

function isItemActive(path) {
  if (path === '/') return route.path === '/';
  return route.path.startsWith(path);
}

function toggleSubmenu(key) {
  openMenus.value[key] = !openMenus.value[key];
}

function isSubmenuOpen(item) {
  if (!item.children) return false;
  if (searchQuery.value.trim()) return true;
  return openMenus.value[item.key] ?? isItemActive(item.path);
}

function navigateSub(child) {
  const query = {};
  if (child.action) query.action = child.action;
  if (child.filter) query.filter = child.filter;
  if (child.status) query.status = child.status;
  if (child.type) query.type = child.type;

  router.push({ path: child.path, query });
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

    <!-- Search Input (Apenas quando expandido) -->
    <div v-if="!isCollapsed" class="px-3 pt-3 pb-2">
      <div class="relative">
        <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Procurar menu ou ação..."
          class="w-full pl-8 pr-3 py-1.5 bg-[#f4f4f4] hover:bg-gray-100 focus:bg-white text-xs text-gray-700 placeholder-gray-400 rounded-lg border border-transparent focus:border-gray-300 focus:outline-none transition-all"
        />
      </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-2.5 py-2 space-y-1 overflow-y-auto">
      <div v-for="item in filteredNavigation" :key="item.name" class="space-y-0.5">
        <!-- Item Principal -->
        <div
          class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all group cursor-pointer"
          :class="[
            isItemActive(item.path)
              ? 'bg-simples-orange-light text-simples-orange font-semibold shadow-xs'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
          @click="item.children ? toggleSubmenu(item.key) : router.push(item.path)"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <component
              :is="item.icon"
              :size="16"
              :class="[
                isItemActive(item.path) ? 'text-simples-orange' : 'text-gray-400 group-hover:text-gray-600'
              ]"
              class="flex-shrink-0"
            />
            <span v-if="!isCollapsed" class="truncate">{{ item.name }}</span>
          </div>

          <!-- Chevron de Submenu -->
          <div v-if="!isCollapsed && item.children" class="flex items-center">
            <ChevronDown
              v-if="isSubmenuOpen(item)"
              :size="13"
              class="text-simples-orange transition-transform duration-150"
            />
            <ChevronRight
              v-else
              :size="13"
              class="text-gray-300 group-hover:text-gray-400 transition-transform duration-150"
            />
          </div>
        </div>

        <!-- Submenu Expandível -->
        <div
          v-if="!isCollapsed && item.children && isSubmenuOpen(item)"
          class="pl-6 pr-1 py-1 space-y-0.5 animate-fade-in border-l-2 border-orange-100 ml-4 my-0.5"
        >
          <button
            v-for="child in item.children"
            :key="child.name"
            type="button"
            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-md text-[11px] text-gray-500 hover:text-simples-orange hover:bg-orange-50/60 transition-colors text-left font-medium cursor-pointer"
            @click="navigateSub(child)"
          >
            <span class="truncate">{{ child.name }}</span>
            <component :is="child.icon" v-if="child.icon" :size="11" class="text-gray-400 flex-shrink-0 ml-1" />
          </button>
        </div>
      </div>
    </nav>

    <!-- User Footer & Logout -->
    <div class="p-3 border-t border-gray-100 bg-[#fafafa]">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-simples-orange-light text-simples-orange font-bold flex items-center justify-center text-xs uppercase flex-shrink-0 border border-simples-orange-border">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
          <div v-if="!isCollapsed" class="min-w-0">
            <p class="text-xs font-semibold truncate text-gray-900">{{ auth.user?.name || 'Administrador' }}</p>
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
