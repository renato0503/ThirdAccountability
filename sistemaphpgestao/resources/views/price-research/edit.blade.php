@extends('layouts.app')
@section('content')

<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Editar pesquisa de preços</h1>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Pesquisa #{{ $pesquisa->id }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pesquisa-precos.show', $pesquisa) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <form method="POST" action="{{ route('pesquisa-precos.destroy', $pesquisa) }}" onsubmit="return confirm('Excluir esta pesquisa? Os resultados associados também serão removidos.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Excluir</button>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('pesquisa-precos.update', $pesquisa) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dados da pesquisa</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Termo pesquisado *</label>
                            <input type="text" name="search_term" value="{{ old('search_term', $pesquisa->search_term) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="category" value="{{ old('category', $pesquisa->category) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity', $pesquisa->quantity) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unidade</label>
                            <select name="unit" class="form-select">
                                <option value="">—</option>
                                @foreach($units as $u)
                                    <option value="{{ $u }}" @selected(old('unit', $pesquisa->unit) === $u)>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Fonte de busca</label>
                            @php $sel = old('sources', $pesquisa->sources ?? []); @endphp
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="PNCP" id="src-pncp" {{ in_array('PNCP', $sel) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="src-pncp">PNCP</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sources[]" value="RADAR_TCE_MT" id="src-radar" {{ in_array('RADAR_TCE_MT', $sel) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="src-radar">Radar TCE-MT</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <input type="text" maxlength="2" style="text-transform: uppercase" name="state" value="{{ old('state', $pesquisa->state) }}" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Município</label>
                            <input type="text" name="city" value="{{ old('city', $pesquisa->city) }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Período inicial</label>
                            <input type="date" name="date_start" value="{{ old('date_start', $pesquisa->date_start?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Período final</label>
                            <input type="date" name="date_end" value="{{ old('date_end', $pesquisa->date_end?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $pesquisa->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Vínculos e status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Projeto vinculado</label>
                        <select name="project_id" class="form-select">
                            <option value="">Sem vínculo</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" @selected(old('project_id', $pesquisa->project_id) == $proj->id)>
                                    {{ $proj->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" @selected(old('status', $pesquisa->status) === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Salvar alterações</button>
                    <a href="{{ route('pesquisa-precos.show', $pesquisa) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
