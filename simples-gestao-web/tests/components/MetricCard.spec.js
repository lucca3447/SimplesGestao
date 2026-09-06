import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import MetricCard from '@/components/common/MetricCard.vue';

// Mock do vue-router
const pushMock = vi.fn();
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: pushMock,
  }),
}));

describe('MetricCard', () => {
  it('renderiza título e valor corretamente', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Receita Total',
        value: 'R$ 15.000,00',
      },
    });

    expect(wrapper.text()).toContain('Receita Total');
    expect(wrapper.text()).toContain('R$ 15.000,00');
  });

  it('renderiza subtítulo quando fornecido', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Clientes',
        value: '42',
        subtitle: 'Mês atual',
      },
    });

    expect(wrapper.text()).toContain('Mês atual');
  });

  it('não renderiza subtítulo quando não fornecido', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Teste',
        value: '0',
      },
    });

    // Não deve ter o span de subtítulo
    const subtitleSpan = wrapper.find('span.text-\\[11px\\]');
    expect(subtitleSpan.exists()).toBe(false);
  });

  it('renderiza slot de ícone quando fornecido', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Teste',
        value: '10',
        color: 'blue',
      },
      slots: {
        icon: '<span data-testid="icon">📊</span>',
      },
    });

    expect(wrapper.find('[data-testid="icon"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('📊');
  });

  it('navega ao clicar quando prop "to" está definida', async () => {
    pushMock.mockClear();

    const wrapper = mount(MetricCard, {
      props: {
        title: 'Pedidos',
        value: '8',
        to: '/orders',
      },
    });

    await wrapper.trigger('click');
    expect(pushMock).toHaveBeenCalledWith('/orders');
  });

  it('não navega ao clicar quando prop "to" está vazia', async () => {
    pushMock.mockClear();

    const wrapper = mount(MetricCard, {
      props: {
        title: 'Pedidos',
        value: '8',
      },
    });

    await wrapper.trigger('click');
    expect(pushMock).not.toHaveBeenCalled();
  });

  it('aplica cursor-pointer quando tem prop "to"', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Teste',
        value: '1',
        to: '/test',
      },
    });

    expect(wrapper.classes()).toContain('cursor-pointer');
  });

  it('não aplica cursor-pointer quando não tem prop "to"', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Teste',
        value: '1',
      },
    });

    expect(wrapper.classes()).not.toContain('cursor-pointer');
  });

  it('mostra botão ↗ clicável quando tem prop "to"', () => {
    const wrapper = mount(MetricCard, {
      props: {
        title: 'Teste',
        value: '1',
        to: '/somewhere',
      },
    });

    const button = wrapper.find('button[title="Acessar listagem completa"]');
    expect(button.exists()).toBe(true);
  });
});
