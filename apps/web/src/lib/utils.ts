import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatCurrency(value: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
}

export function formatDate(date: Date | string | null | undefined): string {
  if (!date) return '-';
  const d = typeof date === 'string' ? new Date(date) : date;
  return new Intl.DateTimeFormat('pt-BR').format(d);
}

export function formatDateTime(date: Date | string | null | undefined): string {
  if (!date) return '-';
  const d = typeof date === 'string' ? new Date(date) : date;
  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(d);
}

export function maskCnpj(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 14);
  return digits
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d)/, '.$1/$2')
    .replace(/(\d{4})(\d)/, '$1-$2');
}

export function maskCep(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 8);
  return digits.replace(/^(\d{5})(\d)/, '$1-$2');
}

export function maskPhone(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 11);
  if (digits.length <= 10) {
    return digits.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  }
  return digits.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
}

export function validateCnpj(cnpj: string): boolean {
  const digits = cnpj.replace(/\D/g, '');
  if (digits.length !== 14) return false;

  // Elimina sequencias iguais
  if (/^(\d)\1{13}$/.test(digits)) return false;

  // Valida primeiro digito
  let sum = 0;
  let weight = 5;
  for (let i = 0; i < 12; i++) {
    sum += parseInt(digits[i]!) * weight;
    weight = weight === 2 ? 9 : weight - 1;
  }
  let digit = 11 - (sum % 11);
  if (digit > 9) digit = 0;
  if (parseInt(digits[12]!) !== digit) return false;

  // Valida segundo digito
  sum = 0;
  weight = 6;
  for (let i = 0; i < 13; i++) {
    sum += parseInt(digits[i]!) * weight;
    weight = weight === 2 ? 9 : weight - 1;
  }
  digit = 11 - (sum % 11);
  if (digit > 9) digit = 0;
  if (parseInt(digits[13]!) !== digit) return false;

  return true;
}

export function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    RASCUNHO: 'bg-gray-500',
    EM_ANALISE: 'bg-yellow-500',
    APROVADO: 'bg-green-500',
    EM_EXECUCAO: 'bg-blue-500',
    SUSPENSO: 'bg-orange-500',
    FINALIZADO: 'bg-purple-500',
    PRESTACAO_CONTAS: 'bg-indigo-500',
    PRESTACAO_APROVADA: 'bg-emerald-500',
    PRESTACAO_REPROVADA: 'bg-red-500',
    PENDENTE: 'bg-yellow-500',
    PAGO: 'bg-green-500',
    ABERTA: 'bg-red-500',
    RESPONDIDA: 'bg-blue-500',
    FECHADA: 'bg-gray-500',
    CANCELADA: 'bg-gray-500',
    SELECIONADA: 'bg-green-500',
    COM_RESULTADOS: 'bg-blue-500',
    SEM_RESULTADOS: 'bg-yellow-500',
  };
  return colors[status] || 'bg-gray-500';
}

export function getStatusText(status: string): string {
  const texts: Record<string, string> = {
    RASCUNHO: 'Rascunho',
    EM_ANALISE: 'Em Análise',
    APROVADO: 'Aprovado',
    EM_EXECUCAO: 'Em Execução',
    SUSPENSO: 'Suspenso',
    FINALIZADO: 'Finalizado',
    PRESTACAO_CONTAS: 'Prestação de Contas',
    PRESTACAO_APROVADA: 'Prestação Aprovada',
    PRESTACAO_REPROVADA: 'Prestação Reprovada',
    PENDENTE: 'Pendente',
    PAGO: 'Pago',
    ABERTA: 'Aberta',
    RESPONDIDA: 'Respondida',
    FECHADA: 'Fechada',
    VENCIDA: 'Vencida',
    CANCELADA: 'Cancelada',
    SELECIONADA: 'Selecionada',
    COM_RESULTADOS: 'Com Resultados',
    SEM_RESULTADOS: 'Sem Resultados',
  };
  return texts[status] || status;
}
