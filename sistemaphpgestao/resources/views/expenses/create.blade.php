@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Registrar Despesa</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Registre um novo lançamento financeiro</p>
    </div>
    <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('despesas.store') }}">
    @csrf
    <div class="row g-3">

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Informações da Despesa</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Projeto *</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">Selecione o projeto...</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', request('project_id')) == $p->id ? 'selected' : '' }}>
                                    {{ Str::limit($p->nome, 50) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta <span style="color: var(--text-muted); font-weight: 400;">(opcional)</span></label>
                            <select name="goal_id" class="form-select">
                                <option value="">Selecione a meta...</option>
                                @foreach($goals as $g)
                                <option value="{{ $g->id }}" {{ old('goal_id') == $g->id ? 'selected' : '' }}>{{ $g->titulo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição da Despesa *</label>
                            <input type="text" name="descricao" value="{{ old('descricao') }}" class="form-control" required placeholder="Descreva a despesa de forma clara">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($categorias as $c)
                                <option value="{{ $c }}" {{ old('categoria') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fornecedor</label>
                            <input type="text" name="fornecedor" value="{{ old('fornecedor') }}" class="form-control" placeholder="Nome do fornecedor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CNPJ do Fornecedor</label>
                            <input type="text" name="cnpj_fornecedor" value="{{ old('cnpj_fornecedor') }}" class="form-control" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$) *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="valor" value="{{ old('valor') }}" class="form-control" step="0.01" min="0" required placeholder="0,00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="forma_pagamento" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($formasPagamento as $f)
                                <option value="{{ $f }}" {{ old('forma_pagamento') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nº NF / Recibo</label>
                            <input type="text" name="numero_nf" value="{{ old('numero_nf') }}" class="form-control" placeholder="Número do documento">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data da Despesa</label>
                            <input type="date" name="data_despesa" value="{{ old('data_despesa') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Pagamento</label>
                            <input type="date" name="data_pagamento" value="{{ old('data_pagamento') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Registrar Despesa
                        </button>
                        <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
