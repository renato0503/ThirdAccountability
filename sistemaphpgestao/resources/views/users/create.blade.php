@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Novo Usuário</h4>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>
<form method="POST" action="{{ route('usuarios.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card"><div class="card-header">Dados do Usuário</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome Completo *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">E-mail *</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Senha *</label><input type="password" name="password" class="form-control" required autocomplete="new-password"></div>
                    <div class="col-md-6"><label class="form-label">Confirmar Senha *</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Perfil de Acesso *</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $r)<option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>{{ $r }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Instituição</label>
                        <select name="institution_id" class="form-select">
                            <option value="">Nenhuma (acesso geral)</option>
                            @foreach($institutions as $i)<option value="{{ $i->id }}" {{ old('institution_id')==$i->id?'selected':'' }}>{{ $i->nome_fantasia??$i->razao_social }}</option>@endforeach
                        </select></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header">Perfis Disponíveis</div><div class="card-body" style="font-size:.85rem">
                <ul class="list-unstyled mb-0">
                    <li><strong>ADMIN_GERAL</strong> — Acesso total ao sistema</li>
                    <li class="mt-1"><strong>ADMIN_INSTITUICAO</strong> — Gerencia uma instituição</li>
                    <li class="mt-1"><strong>GESTOR_PROJETO</strong> — Gerencia projetos</li>
                    <li class="mt-1"><strong>FISCAL_PROJETO</strong> — Diligências (somente visualização e aprovação)</li>
                    <li class="mt-1"><strong>CONSELHO_FISCAL_1, 2 e 3</strong> — Diligências, Prestação de Contas e Relatório (visualização e aprovação das diligências)</li>
                    <li class="mt-1"><strong>FISCAL_EXTERNO</strong> — Apenas visualização</li>
                </ul>
            </div></div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Criar Usuário</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>
@endsection
