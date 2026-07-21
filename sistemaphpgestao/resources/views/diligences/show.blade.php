@extends('layouts.app')
@section('content')

@php $dc = ['ABERTA' => 'danger', 'RESPONDIDA' => 'warning', 'ENCERRADA' => 'success']; @endphp

<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Diligência #{{ $diligence->id }}</h1>
        <div class="mt-1">
            <span class="badge bg-{{ $dc[$diligence->status] ?? 'secondary' }}">{{ $diligence->status_label }}</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('diligencias.edit', ['diligencia' => $diligence]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('diligencias.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3">

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Informações</div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size: 13.5px; row-gap: 8px;">
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Projeto</dt>
                    <dd class="col-7 mb-0">
                        @if($diligence->project)
                            <a href="{{ route('projetos.show', ['projeto' => $diligence->project]) }}" style="text-decoration: none;">
                                {{ $diligence->project->nome }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    @if($diligence->goal)
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Meta</dt>
                    <dd class="col-7 mb-0">
                        <span class="badge bg-dark me-1" style="font-size: 10px;">{{ $diligence->goal->numero ?? '' }}</span>
                        {{ $diligence->goal->titulo }}
                        <div class="mt-1">
                            <span class="badge bg-{{ $diligence->goal->status_color ?? 'secondary' }}" style="font-size: 10px;">
                                {{ $diligence->goal->status_label }}
                            </span>
                        </div>
                    </dd>
                    @endif

                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Tipo</dt>
                    <dd class="col-7 mb-0">{{ $diligence->tipo ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Responsável</dt>
                    <dd class="col-7 mb-0">{{ $diligence->responsavel ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Prazo</dt>
                    <dd class="col-7 mb-0">{{ $diligence->prazo?->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Criada em</dt>
                    <dd class="col-7 mb-0">{{ $diligence->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">

        <div class="card mb-3">
            <div class="card-header">Descrição da Diligência</div>
            <div class="card-body" style="font-size: 14px; line-height: 1.7; white-space: pre-wrap;">{{ $diligence->descricao }}</div>
        </div>

        @if($diligence->resposta)
        <div class="card mb-3" style="border-color: #86efac !important;">
            <div class="card-header" style="background: #f0fdf4; color: #15803d; border-color: #86efac;">
                <i class="bi bi-check-circle me-1"></i> Resposta Registrada
            </div>
            <div class="card-body" style="font-size: 14px; line-height: 1.7; white-space: pre-wrap;">{{ $diligence->resposta }}</div>
        </div>
        @endif

        @if($diligence->status === 'ABERTA')
        <div class="card">
            <div class="card-header">
                <i class="bi bi-reply me-1"></i> Responder esta Diligência
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('diligencias.update', ['diligencia' => $diligence]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="project_id" value="{{ $diligence->project_id }}">
                    <input type="hidden" name="goal_id" value="{{ $diligence->goal_id }}">
                    <input type="hidden" name="tipo" value="{{ $diligence->tipo }}">
                    <input type="hidden" name="descricao" value="{{ $diligence->descricao }}">
                    <input type="hidden" name="responsavel" value="{{ $diligence->responsavel }}">
                    <input type="hidden" name="prazo" value="{{ $diligence->prazo?->format('Y-m-d') }}">
                    <input type="hidden" name="status" value="RESPONDIDA">
                    <div class="mb-3">
                        <label class="form-label">Sua Resposta *</label>
                        <textarea name="resposta" class="form-control" rows="5" required placeholder="Escreva a resposta completa à diligência...">{{ old('resposta') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Enviar Resposta
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection
