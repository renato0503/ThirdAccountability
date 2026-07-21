@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Nova Meta</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">{{ Str::limit($projeto->nome, 60) }}</p>
    </div>
    <a href="{{ route('projetos.show', ['projeto' => $projeto]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Projeto
    </a>
</div>

<form method="POST" action="{{ route('projetos.metas.store', ['projeto' => $projeto]) }}">
    @csrf
    <div class="row g-3">

        {{-- ── Coluna principal ── --}}
        <div class="col-md-8">

            {{-- Card: Dados da Meta --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-bullseye me-1"></i> Dados da Meta
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Número</label>
                            <input type="text" name="numero" value="{{ old('numero') }}"
                                class="form-control" placeholder="01, 02...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tipo de Meta</label>
                            <select name="tipo_meta" class="form-select">
                                <option value="QUANTITATIVA" {{ old('tipo_meta', 'QUANTITATIVA') == 'QUANTITATIVA' ? 'selected' : '' }}>Quantitativa</option>
                                <option value="QUALITATIVA"  {{ old('tipo_meta') == 'QUALITATIVA'  ? 'selected' : '' }}>Qualitativa</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Título da Meta *</label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}"
                                class="form-control @error('titulo') is-invalid @enderror" required
                                placeholder="Descreva o título da meta de forma clara">
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição da Meta</label>
                            <textarea name="descricao" class="form-control" rows="3"
                                placeholder="Ex: Atender 180 professores em oficinas de formação">{{ old('descricao') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Aferição da Meta</label>
                            <textarea name="afericao_meta" class="form-control" rows="3"
                                placeholder="Como a meta será mensurada/aferida (ex: lista de presença, relatórios, registros fotográficos)">{{ old('afericao_meta') }}</textarea>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                Forma como o cumprimento da meta será comprovado.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Indicador</label>
                            <input type="text" name="indicador" value="{{ old('indicador') }}"
                                class="form-control" placeholder="ex: Número de atendimentos realizados">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Unidade de Medida</label>
                            <input type="text" name="unidade_medida" value="{{ old('unidade_medida') }}"
                                class="form-control" placeholder="pessoas, cursos...">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Quantidade Prevista</label>
                            <input type="number" name="quantidade_prevista" value="{{ old('quantidade_prevista') }}"
                                class="form-control" min="0" placeholder="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Início</label>
                            <input type="date" name="data_inicio" value="{{ old('data_inicio') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Prazo / Término</label>
                            <input type="date" name="prazo" value="{{ old('prazo') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'PENDENTE') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>{{-- /card Dados da Meta --}}

            {{-- Card: Responsável da Meta --}}
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-1"></i> Responsável da Meta
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nome do Responsável</label>
                            <input type="text" name="responsavel" value="{{ old('responsavel') }}"
                                class="form-control" placeholder="Nome completo">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone_responsavel" value="{{ old('telefone_responsavel') }}"
                                class="form-control" placeholder="(00) 00000-0000">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email_responsavel" value="{{ old('email_responsavel') }}"
                                class="form-control" placeholder="email@exemplo.com">
                        </div>

                    </div>
                </div>
            </div>{{-- /card Responsável --}}

        </div>{{-- /col-md-8 --}}

        {{-- ── Coluna lateral ── --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Ações</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Criar Meta
                        </button>
                        <a href="{{ route('projetos.show', ['projeto' => $projeto]) }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>{{-- /col-md-4 --}}

    </div>{{-- /row --}}
</form>

@endsection
