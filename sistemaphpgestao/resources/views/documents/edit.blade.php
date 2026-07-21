@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Editar Documento</h4>
        <p class="text-muted mb-0" style="font-size:13px">{{ $documento->nome }}</p>
    </div>
    <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('documentos.update', $documento) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Informações do Documento</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome do Documento *</label>
                            <input type="text" name="nome" value="{{ old('nome', $documento->nome) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($categorias as $c)
                                <option value="{{ $c }}" {{ old('categoria', $documento->categoria) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Validade</label>
                            <input type="date" name="validade" value="{{ old('validade', $documento->validade?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Projeto (opcional)</label>
                            <select name="project_id" class="form-select">
                                <option value="">Nenhum</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $documento->project_id) == $p->id ? 'selected' : '' }}>
                                    {{ Str::limit($p->nome, 50) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Instituição (opcional)</label>
                            <select name="institution_id" class="form-select">
                                <option value="">Nenhuma</option>
                                @foreach($institutions as $i)
                                <option value="{{ $i->id }}" {{ old('institution_id', $documento->institution_id) == $i->id ? 'selected' : '' }}>
                                    {{ $i->nome_fantasia ?? $i->razao_social }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Substituir Arquivo</label>
                            @if($documento->file_path)
                            <div class="mb-2 p-2 rounded" style="background:var(--bg-muted); border:1px solid var(--border); font-size:12.5px;">
                                <i class="bi bi-file-earmark me-1"></i>
                                <strong>Arquivo atual:</strong> {{ $documento->tipo }}
                                @if($documento->tamanho) — {{ $documento->tamanho }} @endif
                                @if($documento->url)
                                <a href="{{ $documento->url }}" target="_blank" class="ms-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Visualizar
                                </a>
                                @endif
                            </div>
                            @endif
                            <input type="file" name="arquivo" id="arquivo" class="form-control"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                            <div class="form-text">Deixe em branco para manter o arquivo atual. Máximo 20 MB.</div>
                            <div id="arquivo-info" class="mt-2" style="display:none; font-size:12.5px; color:var(--text-muted);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('documentos.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                    <hr>
                    <form method="POST" action="{{ route('documentos.destroy', $documento) }}"
                          onsubmit="return confirm('Excluir este documento?')">
                        @csrf @method('DELETE')
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Excluir Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($documento->created_at)
            <div class="card mt-3">
                <div class="card-body" style="font-size:12.5px; color:var(--text-muted);">
                    <div><strong>Enviado por:</strong> {{ $documento->uploader?->name ?? '—' }}</div>
                    <div class="mt-1"><strong>Enviado em:</strong> {{ $documento->created_at->format('d/m/Y H:i') }}</div>
                    <div class="mt-1"><strong>Status:</strong> {{ $documento->status_analise }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('arquivo').addEventListener('change', function() {
    const info = document.getElementById('arquivo-info');
    if (this.files[0]) {
        const size = this.files[0].size;
        const fmt = size >= 1048576 ? (size/1048576).toFixed(1)+' MB' : (size/1024).toFixed(0)+' KB';
        info.textContent = this.files[0].name + ' — ' + fmt;
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
});
</script>
@endpush

@endsection
