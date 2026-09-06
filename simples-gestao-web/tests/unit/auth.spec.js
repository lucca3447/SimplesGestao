import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';

// Mock do módulo @/api/client antes de importar o store
vi.mock('@/api/client', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
    interceptors: {
      request: { use: vi.fn() },
      response: { use: vi.fn() },
    },
  },
}));

import { useAuthStore } from '@/stores/auth';
import api from '@/api/client';

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    localStorage.clear();
    vi.clearAllMocks();
  });

  describe('estado inicial', () => {
    it('inicia sem autenticação quando não há token no localStorage', () => {
      const auth = useAuthStore();
      expect(auth.token).toBeNull();
      expect(auth.user).toBeNull();
      expect(auth.isAuthenticated).toBe(false);
      expect(auth.loading).toBe(false);
      expect(auth.error).toBeNull();
    });

    it('inicia autenticado quando há token no localStorage', () => {
      localStorage.setItem('auth_token', 'test-token-123');
      localStorage.setItem('auth_user', JSON.stringify({ name: 'Test', role: 'admin' }));

      // Recriar pinia para reler o localStorage
      setActivePinia(createPinia());
      const auth = useAuthStore();

      expect(auth.token).toBe('test-token-123');
      expect(auth.user).toEqual({ name: 'Test', role: 'admin' });
      expect(auth.isAuthenticated).toBe(true);
    });
  });

  describe('isAdmin computed', () => {
    it('retorna true quando user.role === "admin"', () => {
      localStorage.setItem('auth_token', 'tok');
      localStorage.setItem('auth_user', JSON.stringify({ role: 'admin' }));
      setActivePinia(createPinia());
      const auth = useAuthStore();
      expect(auth.isAdmin).toBe(true);
    });

    it('retorna false quando user.role !== "admin"', () => {
      localStorage.setItem('auth_token', 'tok');
      localStorage.setItem('auth_user', JSON.stringify({ role: 'user' }));
      setActivePinia(createPinia());
      const auth = useAuthStore();
      expect(auth.isAdmin).toBe(false);
    });

    it('retorna false quando user é null', () => {
      const auth = useAuthStore();
      expect(auth.isAdmin).toBe(false);
    });
  });

  describe('login()', () => {
    it('autentica com sucesso e persiste dados', async () => {
      api.post.mockResolvedValueOnce({
        data: {
          user: { id: 1, name: 'Admin', role: 'admin' },
          token: 'new-token-abc',
        },
      });

      const auth = useAuthStore();
      const result = await auth.login('admin@test.com', 'password');

      expect(result).toEqual({ success: true });
      expect(auth.token).toBe('new-token-abc');
      expect(auth.user).toEqual({ id: 1, name: 'Admin', role: 'admin' });
      expect(auth.isAuthenticated).toBe(true);
      expect(auth.loading).toBe(false);
      expect(auth.error).toBeNull();

      // Verifica persistência no localStorage
      expect(localStorage.getItem('auth_token')).toBe('new-token-abc');
      expect(JSON.parse(localStorage.getItem('auth_user'))).toEqual({
        id: 1, name: 'Admin', role: 'admin',
      });
    });

    it('retorna erro com mensagem da API em caso de falha', async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            errors: { email: ['Credenciais inválidas.'] },
          },
        },
      });

      const auth = useAuthStore();
      const result = await auth.login('wrong@test.com', 'wrong');

      expect(result.success).toBe(false);
      expect(result.message).toBe('Credenciais inválidas.');
      expect(auth.error).toBe('Credenciais inválidas.');
      expect(auth.isAuthenticated).toBe(false);
      expect(auth.loading).toBe(false);
    });

    it('usa mensagem genérica quando a API não retorna erro estruturado', async () => {
      api.post.mockRejectedValueOnce({
        response: { data: {} },
      });

      const auth = useAuthStore();
      const result = await auth.login('a@b.com', 'x');

      expect(result.message).toBe('Falha ao autenticar. Verifique suas credenciais.');
    });
  });

  describe('logout()', () => {
    it('limpa estado e localStorage ao fazer logout', async () => {
      // Preparar estado autenticado
      localStorage.setItem('auth_token', 'my-token');
      localStorage.setItem('auth_user', JSON.stringify({ name: 'User' }));
      setActivePinia(createPinia());
      const auth = useAuthStore();

      api.post.mockResolvedValueOnce({});
      await auth.logout();

      expect(auth.token).toBeNull();
      expect(auth.user).toBeNull();
      expect(auth.isAuthenticated).toBe(false);
      expect(localStorage.getItem('auth_token')).toBeNull();
      expect(localStorage.getItem('auth_user')).toBeNull();
      expect(api.post).toHaveBeenCalledWith('/auth/logout');
    });

    it('limpa estado mesmo quando a API de logout falha', async () => {
      localStorage.setItem('auth_token', 'tok');
      localStorage.setItem('auth_user', JSON.stringify({ name: 'U' }));
      setActivePinia(createPinia());
      const auth = useAuthStore();

      api.post.mockRejectedValueOnce(new Error('Network error'));
      await auth.logout();

      expect(auth.token).toBeNull();
      expect(auth.user).toBeNull();
      expect(localStorage.getItem('auth_token')).toBeNull();
    });
  });

  describe('fetchProfile()', () => {
    it('atualiza user com dados da API', async () => {
      localStorage.setItem('auth_token', 'tok');
      setActivePinia(createPinia());
      const auth = useAuthStore();

      api.get.mockResolvedValueOnce({
        data: { data: { id: 1, name: 'Updated Name', role: 'admin' } },
      });

      await auth.fetchProfile();

      expect(auth.user).toEqual({ id: 1, name: 'Updated Name', role: 'admin' });
      expect(JSON.parse(localStorage.getItem('auth_user'))).toEqual({
        id: 1, name: 'Updated Name', role: 'admin',
      });
    });

    it('não faz requisição quando não há token', async () => {
      const auth = useAuthStore();
      await auth.fetchProfile();
      expect(api.get).not.toHaveBeenCalled();
    });
  });
});
