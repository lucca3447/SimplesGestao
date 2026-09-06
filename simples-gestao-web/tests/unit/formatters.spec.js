import { describe, it, expect } from 'vitest';
import {
  formatCurrency,
  formatDate,
  formatDateTime,
  formatCpfCnpj,
} from '@/utils/formatters';

describe('formatCurrency', () => {
  it('formata valor numérico como moeda BRL', () => {
    const result = formatCurrency(1234.56);
    // Intl pode usar espaço normal ou espaço não-quebrável
    expect(result).toMatch(/R\$\s*1\.234,56/);
  });

  it('formata zero corretamente', () => {
    expect(formatCurrency(0)).toMatch(/R\$\s*0,00/);
  });

  it('formata string numérica', () => {
    expect(formatCurrency('99.9')).toMatch(/R\$\s*99,90/);
  });

  it('retorna R$ 0,00 para valor null ou undefined', () => {
    expect(formatCurrency(null)).toMatch(/R\$\s*0,00/);
    expect(formatCurrency(undefined)).toMatch(/R\$\s*0,00/);
  });

  it('retorna R$ 0,00 para string não-numérica', () => {
    expect(formatCurrency('abc')).toMatch(/R\$\s*0,00/);
  });

  it('formata valores negativos', () => {
    const result = formatCurrency(-500);
    expect(result).toContain('500,00');
  });
});

describe('formatDate', () => {
  it('formata data ISO em formato pt-BR (dd/mm/aaaa)', () => {
    // Usa T12:00:00 para evitar problema de fuso horário (UTC meia-noite → dia anterior em BRT)
    const result = formatDate('2026-03-15T12:00:00');
    expect(result).toBe('15/03/2026');
  });

  it('retorna "-" para string vazia', () => {
    expect(formatDate('')).toBe('-');
  });

  it('retorna "-" para null', () => {
    expect(formatDate(null)).toBe('-');
  });

  it('retorna "-" para undefined', () => {
    expect(formatDate(undefined)).toBe('-');
  });
});

describe('formatDateTime', () => {
  it('formata data e hora em formato curto pt-BR', () => {
    const result = formatDateTime('2026-03-15T14:30:00');
    // Formato curto pt-BR: dd/mm/aaaa hh:mm ou dd/mm/aa hh:mm
    expect(result).toMatch(/15\/03/);
    expect(result).toMatch(/14:30/);
  });

  it('retorna "-" para string vazia', () => {
    expect(formatDateTime('')).toBe('-');
  });

  it('retorna "-" para null', () => {
    expect(formatDateTime(null)).toBe('-');
  });
});

describe('formatCpfCnpj', () => {
  it('formata CPF (11 dígitos) corretamente', () => {
    expect(formatCpfCnpj('12345678901')).toBe('123.456.789-01');
  });

  it('formata CNPJ (14 dígitos) corretamente', () => {
    expect(formatCpfCnpj('12345678000190')).toBe('12.345.678/0001-90');
  });

  it('formata CPF com máscara pré-existente (remove não-dígitos)', () => {
    expect(formatCpfCnpj('123.456.789-01')).toBe('123.456.789-01');
  });

  it('retorna valor original para quantidade de dígitos diferente de 11 ou 14', () => {
    expect(formatCpfCnpj('12345')).toBe('12345');
  });

  it('retorna "-" para null ou undefined', () => {
    expect(formatCpfCnpj(null)).toBe('-');
    expect(formatCpfCnpj(undefined)).toBe('-');
  });

  it('retorna "-" para string vazia', () => {
    expect(formatCpfCnpj('')).toBe('-');
  });
});
