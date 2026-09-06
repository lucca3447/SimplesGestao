import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import AppModal from '@/components/common/AppModal.vue';

describe('AppModal', () => {
  it('renderiza o título corretamente', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Novo Cliente',
        modelValue: false,
      },
    });

    expect(wrapper.find('#app-modal-title').text()).toBe('Novo Cliente');
  });

  it('renderiza conteúdo do slot padrão', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Teste',
        modelValue: false,
      },
      slots: {
        default: '<p data-testid="body">Conteúdo do modal</p>',
      },
    });

    expect(wrapper.find('[data-testid="body"]').text()).toBe('Conteúdo do modal');
  });

  it('renderiza slot de footer quando fornecido', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Teste',
        modelValue: false,
      },
      slots: {
        default: '<p>Corpo</p>',
        footer: '<button data-testid="save">Salvar</button>',
      },
    });

    expect(wrapper.find('[data-testid="save"]').exists()).toBe(true);
  });

  it('não renderiza footer quando slot não é fornecido', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Teste',
        modelValue: false,
      },
      slots: {
        default: '<p>Corpo</p>',
      },
    });

    const footerDiv = wrapper.find('.bg-gray-50.border-t');
    expect(footerDiv.exists()).toBe(false);
  });

  it('emite eventos ao clicar no botão fechar', async () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Teste',
        modelValue: true,
      },
    });

    const closeBtn = wrapper.find('button[aria-label="Fechar janela"]');
    expect(closeBtn.exists()).toBe(true);

    await closeBtn.trigger('click');

    expect(wrapper.emitted('update:modelValue')).toBeTruthy();
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([false]);
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('aplica maxWidth customizado', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Wide',
        modelValue: false,
        maxWidth: 'max-w-2xl',
      },
    });

    const dialog = wrapper.find('dialog');
    expect(dialog.classes()).toContain('max-w-2xl');
  });

  it('usa maxWidth padrão max-w-lg quando não especificado', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Default',
        modelValue: false,
      },
    });

    const dialog = wrapper.find('dialog');
    expect(dialog.classes()).toContain('max-w-lg');
  });

  it('tem atributo closedby="any" no dialog', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Teste',
        modelValue: false,
      },
    });

    const dialog = wrapper.find('dialog');
    expect(dialog.attributes('closedby')).toBe('any');
  });

  it('tem aria-labelledby apontando para o título', () => {
    const wrapper = mount(AppModal, {
      props: {
        title: 'Acessível',
        modelValue: false,
      },
    });

    const dialog = wrapper.find('dialog');
    expect(dialog.attributes('aria-labelledby')).toBe('app-modal-title');
  });
});
