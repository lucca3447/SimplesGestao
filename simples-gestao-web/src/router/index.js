import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from '@/components/layout/AppLayout.vue';
import LoginView from '@/views/LoginView.vue';
import DashboardView from '@/views/DashboardView.vue';
import CustomersView from '@/views/CustomersView.vue';
import ProductsView from '@/views/ProductsView.vue';
import OrdersView from '@/views/OrdersView.vue';
import TransactionsView from '@/views/TransactionsView.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard Geral' },
      },
      {
        path: 'customers',
        name: 'customers',
        component: CustomersView,
        meta: { title: 'Gestão de Clientes' },
      },
      {
        path: 'products',
        name: 'products',
        component: ProductsView,
        meta: { title: 'Catálogo de Produtos e Estoque' },
      },
      {
        path: 'orders',
        name: 'orders',
        component: OrdersView,
        meta: { title: 'Pedidos e Vendas' },
      },
      {
        path: 'transactions',
        name: 'transactions',
        component: TransactionsView,
        meta: { title: 'Financeiro e Livro Caixa' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guard de Autenticação
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');

  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' });
  } else if (to.meta.guestOnly && token) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});

export default router;
