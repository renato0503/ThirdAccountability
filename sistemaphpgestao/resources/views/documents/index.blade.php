@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Documentos</h4>
    <a href="{{ route('documentos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Novo Documento</a>
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="d-flex gap-2">
    <select name="categoria" class="form-select" style="max-width:220px">
        <option value="">Todas as Categorias</option>
        @foreach($categorias as $c)<option value="{{ $c }}" {{ request('categoria')==$c?'selected':'' }}>{{ $c }}</option>@endforeach
    </select>
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
    @if(request('categoria'))<a href="{{ route('documentos.index') }}" class="btn btn-outline-secondary">Limpar</a>@endif
</form></div></div>
<div class="row g-3">
    @forelse($documents as $doc)
    <div class="col-md-4 col-lg-3">
        <div class="card h-100">
            <a href="{{ route('documentos.show', $doc) }}" class="text-decoration-none text-reset" style="display:block;">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <i class="bi bi-file-earmark-text fs-2 text-primary mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.875rem;word-break:break-word">{{ $doc->nome }}</div>
                            @if($doc->categoria)<span class="badge bg-light text-dark border mt-1">{{ $doc->categoria }}</span>@endif
                        </div>
                    </div>
                    <div style="font-size:.8rem;color:#64748b">
                        @if($doc->project)<div><i class="bi bi-folder2 me-1"></i>{{ Str::limit($doc->project->nome,30) }}</div>@endif
                        @if($doc->institution)<div><i class="bi bi-building me-1"></i>{{ Str::limit($doc->institution->razao_social,30) }}</div>@endif
                        <div><i class="bi bi-calendar me-1"></i>{{ $doc->created_at->format('d/m/Y') }}</div>
                        @if($doc->validade)<div><i class="bi bi-clock me-1"></i>Validade: {{ $doc->validade->format('d/m/Y') }}</div>@endif
                    </div>
                </div>
            </a>
            <div class="card-footer d-flex justify-content-between align-items-center py-2 gap-2 flex-wrap">
                <span class="badge bg-{{ $doc->status_analise==='APROVADO'?'success':($doc->status_analise==='REPROVADO'?'danger':'warning') }}" style="font-size:.75rem">{{ $doc->status_analise }}</span>
                <div class="d-flex gap-1">
                    <a href="{{ route('documentos.show', $doc) }}" class="btn btn-sm btn-outline-primary" title="Ver"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('documentos.edit', $doc) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('documentos.destroy',['documento'=>$doc]) }}" onsubmit="return confirm('Excluir documento?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="text-center text-muted py-5"><i class="bi bi-folder-x fs-2 mb-2 d-block"></i>Nenhum documento encontrado.</div></div>
    @endforelse
</div>
@if($documents->hasPages())<div class="mt-3">{{ $documents->links() }}</div>@endif
@endsection
