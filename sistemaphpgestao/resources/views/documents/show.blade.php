@extends('layouts.app')
@section('content')

@php
    $mime = strtolower($documento->mime_type ?? '');
    $ext  = strtolower($documento->tipo ?? '');
    $isImage = str_starts_with($mime, 'image/') || in_array($ext, ['JPG','JPEG','PNG','GIF','WEBP','SVG']);
    $isPdf   = $mime === 'application/pdf' || $ext === 'PDF';
    $isText  = str_starts_with($mime, 'text/') || in_array($ext, ['TXT','CSV','LOG']);
    $statusColor = $documento->status_analise === 'APROVADO' ? 'success'
                 : ($documento->status_analise === 'REPROVADO' ? 'danger' : 'warning');
@endphp

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $documento->nome }}</h4>
        <div class="d-flex gap-2 flex-wrap" style="font-size:13px">
            @if($documento->categoria)<span class="badge bg-light text-dark border">{{ $documento->categoria }}</span>@endif
            <span class="badge bg-{{ $statusColor }}">{{ $documento->status_analise }}</span>
            @if($documento->tipo)<span class="badge bg-info">{{ $documento->tipo }}</span>@endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($documento->url)
        <a href="{{ $documento->url }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir em nova aba
        </a>
        <a href="{{ $documento->url }}" download class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Baixar
        </a>
        @endif
        <a href="{{ route('documentos.edit', $documento) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('documentos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-eye me-1"></i> Pré-visualização</span>
                @if($documento->tamanho)<small class="text-muted fw-normal">{{ $documento->tamanho }}</small>@endif
            </div>
            <div class="card-body p-0">
                @if(!$documento->url)
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                        Nenhum arquivo anexado a este documento.
                    </div>
                @elseif($isImage)
                    <div class="text-center p-3" style="background:#f1f5f9;">
                        <img src="{{ $documento->url }}" alt="{{ $documento->nome }}"
                             style="max-width:100%; max-height:75vh; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,.08);">
                    </div>
                @elseif($isPdf)
                    <iframe src="{{ $documento->url }}" style="width:100%; height:80vh; border:0; display:block;"></iframe>
                @elseif($isText)
                    <div class="p-3" style="background:#f8fafc; max-height:75vh; overflow:auto;">
                        <iframe src="{{ $documento->url }}" style="width:100%; height:75vh; border:0; background:#fff;"></iframe>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <i class="bi bi-file-earmark-arrow-down fs-1 d-block mb-2 text-primary"></i>
                        <p class="mb-3">Este tipo de arquivo não pode ser exibido diretamente no navegador.</p>
                        <a href="{{ $documento->url }}" download class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Baixar arquivo
                        </a>
                        <a href="{{ $documento->url }}" target="_blank" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Tentar abrir
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> Informações</div>
            <div class="card-body" style="font-size:13px;">
                <dl class="mb-0">
                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Nome</dt>
                    <dd class="mb-2 fw-semibold">{{ $documento->nome }}</dd>

                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Categoria</dt>
                    <dd class="mb-2">{{ $documento->categoria ?: '—' }}</dd>

                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Tipo / Tamanho</dt>
                    <dd class="mb-2">{{ $documento->tipo ?: '—' }} @if($documento->tamanho) · {{ $documento->tamanho }} @endif</dd>

                    @if($documento->validade)
                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Validade</dt>
                    <dd class="mb-2">
                        {{ $documento->validade->format('d/m/Y') }}
                        @php $dias = now()->startOfDay()->diffInDays($documento->validade, false); @endphp
                        @if($dias < 0)
                            <span class="badge bg-danger ms-1">Vencido</span>
                        @elseif($dias <= 30)
                            <span class="badge bg-warning ms-1">{{ (int) $dias }} dias</span>
                        @else
                            <span class="badge bg-success ms-1">{{ (int) $dias }} dias</span>
                        @endif
                    </dd>
                    @endif

                    @if($documento->institution)
                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Instituição</dt>
                    <dd class="mb-2"><i class="bi bi-building me-1"></i>{{ $documento->institution->nome_fantasia ?? $documento->institution->razao_social }}</dd>
                    @endif

                    @if($documento->project)
                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Projeto</dt>
                    <dd class="mb-2">
                        <i class="bi bi-folder2 me-1"></i>
                        <a href="{{ route('projetos.show', $documento->project) }}">{{ $documento->project->nome }}</a>
                    </dd>
                    @endif

                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Enviado por</dt>
                    <dd class="mb-2">{{ $documento->uploader?->name ?? '—' }}</dd>

                    <dt class="text-muted fw-normal" style="font-size:11.5px; text-transform:uppercase; letter-spacing:.04em;">Enviado em</dt>
                    <dd class="mb-0">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('documentos.edit', $documento) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Editar Documento
                    </a>
                    <form method="POST" action="{{ route('documentos.destroy', $documento) }}"
                          onsubmit="return confirm('Excluir este documento?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
