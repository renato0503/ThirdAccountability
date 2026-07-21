@extends('layouts.app')
@section('content')

@php
$statusColors = ['PENDENTE'=>'warning','APROVADO'=>'primary','PAGO'=>'success','REJEITADO'=>'danger'];
$statusColor  = $statusColors[$expense->status] ?? 'secondary';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">{{ Str::limit($expense->descricao, 60) }}</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">
            <span class="badge bg-{{ $statusColor }}">{{ $expense->status }}</span>
            &nbsp;{{ $expense->data_despesa?->format('d/m/Y') ?? '—' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('despesas.edit', ['despesa' => $expense]) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Detalhes da Despesa</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Projeto</div>
                        <div>
                            <a href="{{ route('projetos.show', ['projeto' => $expense->project]) }}" class="text-decoration-none fw-semibold">
                                {{ $expense->project?->nome ?? '—' }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Meta</div>
                        <div>{{ $expense->goal?->titulo ?? 'Nenhuma' }}</div>
                    </div>
                    <div class="col-12">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Descrição</div>
                        <div>{{ $expense->descricao }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Valor</div>
                        <div class="fw-bold fs-5">R$ {{ number_format($expense->valor, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Categoria</div>
                        <div>{{ $expense->categoria ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Forma de Pagamento</div>
                        <div>{{ $expense->forma_pagamento ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Fornecedor</div>
                        <div>{{ $expense->fornecedor ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">CNPJ Fornecedor</div>
                        <div>{{ $expense->cnpj_fornecedor ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Nº NF / Recibo</div>
                        <div>{{ $expense->numero_nf ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Data da Despesa</div>
                        <div>{{ $expense->data_despesa?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">Data de Pagamento</div>
                        <div>{{ $expense->data_pagamento?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Status</div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    <span class="badge bg-{{ $statusColor }}" style="font-size:1rem;padding:.5rem 1.2rem">{{ $expense->status }}</span>
                </div>
                <form method="POST" action="{{ route('despesas.status', ['expense' => $expense]) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach(['PENDENTE','APROVADO','PAGO','REJEITADO'] as $s)
                        <option value="{{ $s }}" {{ $expense->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Alterar Status</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('despesas.edit', ['despesa' => $expense]) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar Despesa
                    </a>
                    <form method="POST" action="{{ route('despesas.destroy', ['despesa' => $expense]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Excluir esta despesa?')">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
