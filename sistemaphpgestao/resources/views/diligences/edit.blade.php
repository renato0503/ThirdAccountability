@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Editar Diligência</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Diligência #{{ $diligence->id }} — {{ $diligence->project?->nome ?? '' }}</p>
    </div>
    <a href="{{ route('diligencias.show', ['diligencia' => $diligence]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('diligencias.update', ['diligencia' => $diligence]) }}">
    @csrf
    @method('PUT')
    <div class="row g-3">

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dados da Diligência</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Projeto *</label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $diligence->project_id) == $p->id ? 'selected' : '' }}>
                                    {{ Str::limit($p->nome, 50) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Vinculada <span class="text-muted fw-normal" style="font-size:12px;">(opcional)</span></label>
                            <select name="goal_id" class="form-select">
                                <option value="">— Sem meta específica —</option>
                                @foreach($metas as $m)
                                <option value="{{ $m->id }}" {{ old('goal_id', $diligence->goal_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->numero ? $m->numero . ' — ' : '' }}{{ $m->titulo }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Diligência</label>
                            <select name="tipo" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($tipos as $t)
                                <option value="{{ $t }}" {{ old('tipo', $diligence->tipo) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prazo</label>
                            <input type="date" name="prazo" value="{{ old('prazo', $diligence->prazo?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ old('status', $diligence->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição *</label>
                            <textarea name="descricao" class="form-control" rows="5" required>{{ old('descricao', $diligence->descricao) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsável</label>
                            <input type="text" name="responsavel" value="{{ old('responsavel', $diligence->responsavel) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Resposta</label>
                            <textarea name="resposta" class="form-control" rows="4" placeholder="Insira a resposta à diligência (se houver)...">{{ old('resposta', $diligence->resposta) }}</textarea>
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
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('diligencias.show', ['diligencia' => $diligence]) }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
