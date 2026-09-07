<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import logoFull from '@/assets/logo-full.png';
import api from '@/api/client';
import { LogIn, AlertCircle, Loader2, RotateCcw, CheckCircle2 } from '@lucide/vue';

const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isResetting = ref(false);
const resetSuccessMessage = ref('');

async function handleSubmit() {
  errorMessage.value = '';
  const result = await auth.login(email.value, password.value);
  if (result.success) {
    router.push('/');
  } else {
    errorMessage.value = result.message || 'E-mail ou senha incorretos.';
  }
}

function fillDemoCredentials(type = 'admin') {
  if (type === 'admin') {
    email.value = 'admin@simplesgestao.com';
    password.value = 'password';
  } else {
    email.value = 'maria@simplesgestao.com';
    password.value = 'password';
  }
  handleSubmit();
}

async function handleResetDemo() {
  if (!confirm('Deseja restaurar o banco de dados para o estado inicial de demonstração? Isso irá redefinir clientes, produtos e pedidos para os dados originais de teste.')) {
    return;
  }
  isResetting.value = true;
  errorMessage.value = '';
  resetSuccessMessage.value = '';
  try {
    const response = await api.post('/demo/reset');
    resetSuccessMessage.value = response.data?.message || 'Banco de dados restaurado com sucesso!';
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Falha ao restaurar banco de dados demo. Tente novamente em 1 minuto.';
  } finally {
    isResetting.value = false;
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f6f8] flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-sm p-8 space-y-6">
      <!-- Logo Header -->
      <div class="text-center space-y-2">
        <img :src="logoFull" alt="Simples" class="h-14 mx-auto object-contain" />
        <p class="text-xs text-gray-500 font-medium">Acesse sua conta para gerenciar seu negócio</p>
      </div>

      <!-- Alerta de Erro -->
      <div
        v-if="errorMessage"
        class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs flex items-center gap-2"
      >
        <AlertCircle :size="16" class="flex-shrink-0 text-rose-500" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Formulário de Login -->
      <form class="space-y-4" @submit.prevent="handleSubmit">
        <div class="space-y-1">
          <label for="email" class="block text-xs font-semibold text-gray-700">E-mail corporativo</label>
          <input
            id="email"
            v-model="email"
            type="email"
            name="email"
            autocomplete="username"
            required
            placeholder="seu.email@empresa.com"
            class="w-full px-3.5 py-2.5 bg-gray-50 hover:bg-gray-100/70 focus:bg-white text-sm text-gray-900 rounded-xl border border-gray-200 focus:border-simples-orange focus:ring-2 focus:ring-simples-orange/10 focus:outline-none transition-all"
          />
        </div>

        <div class="space-y-1">
          <label for="password" class="block text-xs font-semibold text-gray-700">Senha de acesso</label>
          <input
            id="password"
            v-model="password"
            type="password"
            name="password"
            autocomplete="current-password"
            required
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 bg-gray-50 hover:bg-gray-100/70 focus:bg-white text-sm text-gray-900 rounded-xl border border-gray-200 focus:border-simples-orange focus:ring-2 focus:ring-simples-orange/10 focus:outline-none transition-all"
          />
        </div>

        <!-- Botão Entrar -->
        <button
          type="submit"
          :disabled="auth.loading"
          class="w-full py-2.5 px-4 bg-simples-orange hover:bg-simples-orange-hover text-white text-sm font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
        >
          <Loader2 v-if="auth.loading" :size="16" class="animate-spin" />
          <LogIn v-else :size="16" />
          <span>{{ auth.loading ? 'Entrando no sistema...' : 'Entrar' }}</span>
        </button>
      </form>

      <!-- Divisor Demo -->
      <div class="relative my-4">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-xs uppercase">
          <span class="bg-white px-2 text-gray-400 text-[10px] font-bold">Acesso Rápido para Demonstração</span>
        </div>
      </div>

      <!-- Botões de Acesso Rápido (1 clique) -->
      <div class="grid grid-cols-2 gap-2.5">
        <button
          type="button"
          class="py-2 px-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 transition-colors text-center cursor-pointer"
          @click="fillDemoCredentials('admin')"
        >
          👤 Admin
          <span class="block text-[10px] text-gray-400 font-normal">Acesso completo</span>
        </button>

        <button
          type="button"
          class="py-2 px-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 transition-colors text-center cursor-pointer"
          @click="fillDemoCredentials('operator')"
        >
          👥 Operador
          <span class="block text-[10px] text-gray-400 font-normal">Vendas e estoque</span>
        </button>
      </div>

      <!-- Feedback de Sucesso da Restauração -->
      <div v-if="resetSuccessMessage" class="flex items-center gap-2 p-3 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-xl border border-emerald-200 animate-fade-in">
        <CheckCircle2 :size="15" class="shrink-0 text-emerald-500" />
        <span>{{ resetSuccessMessage }}</span>
      </div>

      <!-- Botão para Restaurar Banco de Dados Demo -->
      <div class="pt-2 border-t border-gray-100 text-center">
        <button
          type="button"
          :disabled="isResetting"
          class="text-[11px] text-gray-500 hover:text-simples-orange inline-flex items-center gap-1.5 transition-colors disabled:opacity-50 cursor-pointer"
          title="Restaura os dados originais caso alguém tenha excluído ou alterado registros"
          @click="handleResetDemo"
        >
          <RotateCcw :size="12" :class="{ 'animate-spin': isResetting }" />
          <span>{{ isResetting ? 'Restaurando banco de dados...' : 'Restaurar dados de teste padrão' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
