@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Nova Diligência</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Registre uma solicitação de diligência</p>
    </div>
    <a href="{{ route('diligencias.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('diligencias.store') }}">
    @csrf
    <div class="row g-3">

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dados da Diligência</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Projeto *</label>
                            <select name="project_id" id="projectSelect" class="form-select" required>
                                <option value="">Selecione o projeto...</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', request('project_id')) == $p->id ? 'selected' : '' }}>
                                    {{ Str::limit($p->nome, 50) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Vinculada <span class="text-muted fw-normal" style="font-size:12px;">(opcional)</span></label>
                            <select name="goal_id" id="goalSelect" class="form-select">
                                <option value="">— Sem meta específica —</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Diligência</label>
                            <select name="tipo" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($tipos as $t)
                                <option value="{{ $t }}" {{ old('tipo') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prazo para Resposta</label>
                            <input type="date" name="prazo" value="{{ old('prazo') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição *</label>
                            <textarea name="descricao" class="form-control" rows="5" required placeholder="Descreva detalhadamente a diligência...">{{ old('descricao') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsável</label>
                            <input type="text" name="responsavel" value="{{ old('responsavel') }}" class="form-control" placeholder="Nome do responsável">
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
                            <i class="bi bi-check-lg"></i> Criar Diligência
                        </button>
                        <a href="{{ route('diligencias.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
// Ao selecionar um projeto, carrega as metas desse projeto via AJAX
document.getElementById('projectSelect').addEventListener('change', function() {
    var projectId = this.value;
    var goalSelect = document.getElementById('goalSelect');
    goalSelect.innerHTML = '<option value="">— Sem meta específica —</option>';
    if (!projectId) return;

    fetch('/api/projetos/' + projectId + '/metas')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            data.forEach(function(meta) {
                var opt = document.createElement('option');
                opt.value = meta.id;
                opt.textContent = (meta.numero ? meta.numero + ' — ' : '') + meta.titulo;
                goalSelect.appendChild(opt);
            });
        })
        .catch(function() {});
});
</script>
@endpush

@endsection
