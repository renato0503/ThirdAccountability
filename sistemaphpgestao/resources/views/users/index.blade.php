@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Usuários do Sistema</h4>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Novo Usuário</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Instituição</th><th>Situação</th><th>Criado em</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="fw-semibold" style="font-size:.9rem">{{ $u->name }}</td>
                    <td style="font-size:.875rem">{{ $u->email }}</td>
                    <td><span class="badge bg-primary" style="font-size:.75rem">{{ $u->role }}</span></td>
                    <td style="font-size:.875rem">{{ $u->institution?->nome_fantasia??$u->institution?->razao_social??'—' }}</td>
                    <td><span class="badge bg-{{ $u->active?'success':'secondary' }}">{{ $u->active?'Ativo':'Inativo' }}</span></td>
                    <td style="font-size:.875rem">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('usuarios.edit',['usuario'=>$u]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy',['usuario'=>$u]) }}" onsubmit="return confirm('Desativar usuário?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-slash-circle"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Nenhum usuário encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
</div>
@endsection
