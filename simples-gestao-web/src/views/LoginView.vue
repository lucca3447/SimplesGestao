<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppModal from '@/components/common/AppModal.vue';
import logoFull from '@/assets/logo-full.png';
import { LogIn, AlertCircle, Loader2, KeyRound, Check } from '@lucide/vue';

const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isForgotModalOpen = ref(false);

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

function selectDemoAndClose(type = 'admin') {
  isForgotModalOpen.value = false;
  fillDemoCredentials(type);
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
          <div class="flex items-center justify-between">
            <label for="password" class="block text-xs font-semibold text-gray-700">Senha de acesso</label>
            <button
              type="button"
              class="text-[11px] font-semibold text-simples-orange hover:underline cursor-pointer"
              @click="isForgotModalOpen = true"
            >
              Esqueceu a senha?
            </button>
          </div>
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
    </div>

    <!-- Modal Recuperação de Senha Demo -->
    <AppModal
      v-model="isForgotModalOpen"
      title="Recuperação de Acesso"
      max-width="max-w-md"
    >
      <div class="space-y-4 text-xs text-gray-600">
        <div class="w-10 h-10 rounded-full bg-orange-50 text-simples-orange flex items-center justify-center mx-auto">
          <KeyRound :size="20" />
        </div>
        <div class="text-center space-y-1">
          <h4 class="text-sm font-bold text-gray-900">Credenciais de Demonstração</h4>
          <p>Para facilitar a avaliação do sistema, você pode entrar instantaneamente utilizando as contas abaixo:</p>
        </div>

        <div class="space-y-2">
          <div
            class="p-3 bg-gray-50 hover:bg-orange-50/50 border border-gray-200 rounded-xl flex items-center justify-between cursor-pointer transition-colors"
            @click="selectDemoAndClose('admin')"
          >
            <div>
              <p class="font-bold text-gray-900">Administrador</p>
              <p class="text-[11px] text-gray-500 font-mono">admin@simplesgestao.com · Senha: password</p>
            </div>
            <span class="text-xs font-bold text-simples-orange flex items-center gap-1">
              <span>Usar</span>
              <Check :size="14" />
            </span>
          </div>

          <div
            class="p-3 bg-gray-50 hover:bg-orange-50/50 border border-gray-200 rounded-xl flex items-center justify-between cursor-pointer transition-colors"
            @click="selectDemoAndClose('operator')"
          >
            <div>
              <p class="font-bold text-gray-900">Operador</p>
              <p class="text-[11px] text-gray-500 font-mono">maria@simplesgestao.com · Senha: password</p>
            </div>
            <span class="text-xs font-bold text-simples-orange flex items-center gap-1">
              <span>Usar</span>
              <Check :size="14" />
            </span>
          </div>
        </div>
      </div>

      <template #footer>
        <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold cursor-pointer"
          @click="isForgotModalOpen = false"
        >
          Fechar
        </button>
      </template>
    </AppModal>
  </div>
</template>
