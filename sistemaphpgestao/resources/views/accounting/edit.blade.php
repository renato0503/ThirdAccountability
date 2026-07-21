@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Editar Prestação de Contas</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Prestação #{{ $report->id }} — {{ $report->project?->nome ?? '' }}</p>
    </div>
    <a href="{{ route('prestacao-contas.show', ['prestacao_conta' => $report]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('prestacao-contas.update', ['prestacao_conta' => $report]) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">

        <div class="col-md-8">

            {{-- Status e Datas --}}
            <div class="card mb-3">
                <div class="card-header">Status e Datas</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ old('status', $report->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data de Envio</label>
                            <input type="date" name="data_envio" value="{{ old('data_envio', $report->data_envio?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data de Aprovação</label>
                            <input type="date" name="data_aprovacao" value="{{ old('data_aprovacao', $report->data_aprovacao?->format('Y-m-d')) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Relatório Textual --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-file-text me-1"></i> Relatório de Atividades</div>
                <div class="card-body">
                    <textarea name="relatorio_texto" class="form-control" rows="10"
                        placeholder="Descreva as atividades realizadas, resultados alcançados e demais informações pertinentes à prestação de contas...">{{ old('relatorio_texto', $report->relatorio_texto) }}</textarea>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Texto livre — descreva as atividades e resultados do projeto.
                    </div>
                </div>
            </div>

            {{-- Links de Vídeos --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-play-circle me-1"></i> Links de Vídeos</div>
                <div class="card-body">
                    <textarea name="links_videos" class="form-control" rows="4"
                        placeholder="Cole os links de vídeos, um por linha (YouTube, Drive, etc.)...">{{ old('links_videos', $report->links_videos) }}</textarea>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Um link por linha.
                    </div>
                </div>
            </div>

            {{-- Fotos --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-images me-1"></i> Fotos <span class="text-muted fw-normal" style="font-size: 12px;">(até 5 fotos — JPG/PNG/WEBP, máx. 4 MB cada)</span></div>
                <div class="card-body">

                    {{-- Fotos existentes --}}
                    @if($report->fotos && count($report->fotos) > 0)
                    <div class="row g-2 mb-3">
                        @foreach($report->fotos as $idx => $foto)
                        <div class="col-sm-4">
                            <div class="position-relative">
                                <img src="{{ Storage::url($foto) }}" alt="Foto {{ $idx + 1 }}"
                                    style="width:100%;height:120px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);"
                                    onerror="this.style.display='none'">
                                <form method="POST" action="{{ route('prestacao-contas.remove-photo', ['prestacao_conta' => $report]) }}"
                                    class="position-absolute top-0 end-0 m-1"
                                    onsubmit="return confirm('Remover esta foto?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="index" value="{{ $idx }}">
                                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 2px 6px;" title="Remover">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @php $slotsLeft = 5 - count($report->fotos ?? []); @endphp
                    @if($slotsLeft > 0)
                    <div>
                        <label class="form-label" style="font-size: 13px;">Adicionar fotos ({{ $slotsLeft }} vaga(s) disponível(is))</label>
                        <input type="file" name="fotos[]" class="form-control" accept="image/jpeg,image/png,image/webp"
                            multiple {{ $slotsLeft > 1 ? '' : '' }}>
                    </div>
                    @else
                    <div class="alert alert-info mb-0" style="font-size: 13px;">
                        <i class="bi bi-info-circle me-1"></i> Limite de 5 fotos atingido. Remova uma para adicionar outra.
                    </div>
                    @endif

                </div>
            </div>

            {{-- Observações --}}
            <div class="card">
                <div class="card-header">Observações</div>
                <div class="card-body">
                    <textarea name="observacoes" class="form-control" rows="4" placeholder="Observações gerais...">{{ old('observacoes', $report->observacoes) }}</textarea>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('prestacao-contas.show', ['prestacao_conta' => $report]) }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
