import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/api/client';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null);
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'));
  const loading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  async function login(email, password) {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.post('/auth/login', { email, password });
      const { user: userData, token: tokenData } = response.data;

      token.value = tokenData;
      user.value = userData;

      localStorage.setItem('auth_token', tokenData);
      localStorage.setItem('auth_user', JSON.stringify(userData));

      return { success: true };
    } catch (err) {
      const message = err.response?.data?.errors?.email?.[0] || 
                      err.response?.data?.message || 
                      'Falha ao autenticar. Verifique suas credenciais.';
      error.value = message;
      return { success: false, message };
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/auth/logout');
      }
    } catch (err) {
      console.error('Erro ao efetuar logout na API:', err);
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
    }
  }

  async function fetchProfile() {
    if (!token.value) return;
    try {
      const response = await api.get('/auth/me');
      user.value = response.data.data;
      localStorage.setItem('auth_user', JSON.stringify(response.data.data));
    } catch (err) {
      console.error('Erro ao carregar perfil do usuário:', err);
    }
  }

  return {
    token,
    user,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    fetchProfile,
  };
});
