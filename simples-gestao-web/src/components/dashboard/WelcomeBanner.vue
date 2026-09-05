<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { Sparkles, ShoppingBag } from '@lucide/vue';

const auth = useAuthStore();
const router = useRouter();

function goToNewOrder() {
  router.push('/orders');
}
</script>

<template>
  <div class="space-y-4">
    <!-- Banner Principal -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
      <div class="flex items-center gap-4">
        <!-- Avatar Feminino/Corporativo estilizado -->
        <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-amber-400 to-rose-400 p-0.5 shadow-sm flex-shrink-0">
          <div class="w-full h-full rounded-full bg-white flex items-center justify-center font-bold text-gray-700 text-lg">
            {{ auth.user?.name?.charAt(0) || 'A' }}
          </div>
        </div>

        <div class="space-y-1">
          <h2 class="text-base font-bold text-gray-900">
            Olá <span class="text-simples-orange font-extrabold">{{ auth.user?.name || 'Gestor(a)' }}</span>, seja bem-vindo(a)!
          </h2>
          <p class="text-xs text-gray-500 max-w-2xl leading-relaxed">
            Acompanhe a saúde do seu negócio em tempo real: fluxo de caixa consolidado, controle de estoque crítico e gestão de pedidos ágil.
          </p>
        </div>
      </div>

      <!-- Botão de Ação Verde Conexa -->
      <button
        type="button"
        class="w-full sm:w-auto px-5 py-2.5 bg-simples-green hover:bg-simples-green-hover text-white rounded-lg text-xs font-bold tracking-wide transition-all shadow-xs flex items-center justify-center gap-2"
        @click="goToNewOrder"
      >
        <ShoppingBag :size="14" />
        <span>Novo Pedido / Venda</span>
      </button>
    </div>

    <!-- Barra de Ferramentas / Aviso de Sistema -->
    <div class="flex items-center justify-between text-xs text-gray-500 px-1">
      <div class="flex items-center gap-2">
        <span class="font-semibold text-gray-700">Visão Geral Empresarial</span>
        <span class="text-gray-300">|</span>
        <div class="flex items-center gap-1.5 text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100 font-medium">
          <Sparkles :size="12" />
          <span>API RESTful sincronizada com PostgreSQL</span>
        </div>
      </div>

      <div class="hidden md:flex items-center gap-2 text-gray-400">
        <span>Filtro de período ativo</span>
      </div>
    </div>
  </div>
</template>
