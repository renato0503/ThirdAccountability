@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Editar Usuário</h4>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>
<form method="POST" action="{{ route('usuarios.update',['usuario'=>$usuario]) }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card"><div class="card-header">Dados do Usuário</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome Completo *</label><input type="text" name="name" value="{{ old('name',$usuario->name) }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">E-mail *</label><input type="email" name="email" value="{{ old('email',$usuario->email) }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Nova Senha <small class="text-muted">(deixe em branco para manter)</small></label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
                    <div class="col-md-6"><label class="form-label">Confirmar Nova Senha</label><input type="password" name="password_confirmation" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Perfil de Acesso *</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $r)<option value="{{ $r }}" {{ old('role',$usuario->role)==$r?'selected':'' }}>{{ $r }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Instituição</label>
                        <select name="institution_id" class="form-select">
                            <option value="">Nenhuma (acesso geral)</option>
                            @foreach($institutions as $i)<option value="{{ $i->id }}" {{ old('institution_id',$usuario->institution_id)==$i->id?'selected':'' }}>{{ $i->nome_fantasia??$i->razao_social }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Situação *</label>
                        <select name="active" class="form-select" required>
                            <option value="1" {{ old('active',$usuario->active)?'selected':'' }}>Ativo</option>
                            <option value="0" {{ !old('active',$usuario->active)?'selected':'' }}>Inativo</option>
                        </select></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header">Informações</div><div class="card-body" style="font-size:.85rem">
                <p class="mb-1"><strong>ID:</strong> {{ $usuario->id }}</p>
                <p class="mb-1"><strong>Criado em:</strong> {{ $usuario->created_at->format('d/m/Y') }}</p>
                @if($usuario->id === auth()->id())
                <div class="alert alert-warning py-2 mt-2 mb-0" style="font-size:.8rem"><i class="bi bi-exclamation-triangle me-1"></i>Este é o seu próprio usuário.</div>
                @endif
            </div></div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Salvar Alterações</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
@endsection
