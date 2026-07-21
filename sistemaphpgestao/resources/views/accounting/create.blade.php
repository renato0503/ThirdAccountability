@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Nova Prestação de Contas</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Inicie o processo de prestação de contas</p>
    </div>
    <a href="{{ route('prestacao-contas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('prestacao-contas.store') }}">
    @csrf
    <div class="row g-3">

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Dados da Prestação</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Projeto *</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">Selecione o projeto...</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                    {{ Str::limit($p->nome, 65) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Relatório de Atividades</label>
                            <textarea name="relatorio_texto" class="form-control" rows="6"
                                placeholder="Descreva as atividades realizadas e resultados alcançados...">{{ old('relatorio_texto') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações gerais sobre esta prestação de contas...">{{ old('observacoes') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Links de Vídeos <span class="text-muted fw-normal" style="font-size:12px;">(um por linha)</span></label>
                            <textarea name="links_videos" class="form-control" rows="3"
                                placeholder="https://youtube.com/watch?v=...&#10;https://drive.google.com/...">{{ old('links_videos') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Criar Prestação
                        </button>
                        <a href="{{ route('prestacao-contas.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
