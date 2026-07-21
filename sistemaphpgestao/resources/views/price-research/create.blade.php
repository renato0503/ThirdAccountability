@extends('layouts.app')
@section('content')

<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Nova pesquisa de preços</h1>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Informe o termo e os filtros. A busca é executada nas fontes selecionadas após salvar.
        </div>
    </div>
    <a href="{{ route('pesquisa-precos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('pesquisa-precos.store') }}">
    @csrf

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dados da pesquisa</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Termo pesquisado *</label>
                            <input type="text" name="search_term" value="{{ old('search_term') }}" class="form-control" required
                                   placeholder="Ex.: bola, bola max 200, uniforme esportivo, notebook, cadeira">
                            <div class="text-muted mt-1" style="font-size: 12px;">
                                Use termos próximos da especificação real do item — quanto mais específico, melhor a qualidade da cotação.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="Ex.: Material esportivo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantidade desejada</label>
                            <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unidade</label>
                            <select name="unit" class="form-select">
                                <option value="">—</option>
                                @foreach($units as $u)
                                    <option value="{{ $u }}" @selected(old('unit') === $u)>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Fonte de busca</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="PNCP" id="src-pncp"
                                           {{ in_array('PNCP', old('sources', ['PNCP'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="src-pncp">PNCP — Portal Nacional de Contratações Públicas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="RADAR_TCE_MT" id="src-radar"
                                           {{ in_array('RADAR_TCE_MT', old('sources', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="src-radar">Radar TCE-MT (consulta assistida)</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <input type="text" maxlength="2" style="text-transform: uppercase" name="state" value="{{ old('state') }}" class="form-control" placeholder="MT">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Município</label>
                            <input type="text" name="city" value="{{ old('city') }}" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Período inicial</label>
                            <input type="date" name="date_start" value="{{ old('date_start') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Período final</label>
                            <input type="date" name="date_end" value="{{ old('date_end') }}" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações internas</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Anotações úteis para auditoria/prestação de contas">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Vínculos</div>
                <div class="card-body">
                    @if(auth()->user()->isAdmin())
                        <div class="mb-3">
                            <label class="form-label">Instituição *</label>
                            <select name="institution_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}" @selected(old('institution_id') == $inst->id)>{{ $inst->razao_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="institution_id" value="{{ auth()->user()->institution_id }}">
                        <div class="mb-3">
                            <label class="form-label">Instituição</label>
                            <input type="text" class="form-control" disabled value="{{ auth()->user()->institution?->razao_social ?? '—' }}">
                        </div>
                    @endif

                    <div class="mb-0">
                        <label class="form-label">Projeto vinculado (opcional)</label>
                        <select name="project_id" class="form-select">
                            <option value="">Sem vínculo</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" @selected(old('project_id', $projectId) == $proj->id)>
                                    {{ $proj->nome }} @if($proj->codigo) ({{ $proj->codigo }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <button type="submit" name="buscar_agora" value="1" class="btn btn-primary">
                        <i class="bi bi-search"></i> Salvar e buscar preços
                    </button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-save"></i> Salvar pesquisa
                    </button>
                    <a href="{{ route('pesquisa-precos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
