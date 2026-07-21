@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Instituições</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Gerencie as organizações parceiras</p>
    </div>
    <a href="{{ route('instituicoes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Nova Instituição
    </a>
</div>

<div class="card mb-3">
    <div class="card-body" style="padding: 12px 18px;">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nome ou CNPJ..." style="max-width: 360px;">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Buscar</button>
            @if(request('search'))
                <a href="{{ route('instituicoes.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Razão Social / Nome Fantasia</th>
                    <th>CNPJ</th>
                    <th>Cidade/UF</th>
                    <th>Área de Atuação</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($institutions as $inst)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text);">{{ $inst->razao_social }}</div>
                        @if($inst->nome_fantasia)
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $inst->nome_fantasia }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 13px;">{{ strlen($inst->cnpj) === 14 ? substr($inst->cnpj,0,2).'.'.substr($inst->cnpj,2,3).'.'.substr($inst->cnpj,5,3).'/'.substr($inst->cnpj,8,4).'-'.substr($inst->cnpj,12,2) : $inst->cnpj }}</span>
                    </td>
                    <td>{{ $inst->municipio }}{{ $inst->estado ? '/'.$inst->estado : '' }}</td>
                    <td>{{ $inst->area_atuacao ?? '—' }}</td>
                    <td>
                        @if($inst->active)
                            <span class="badge bg-success">Ativa</span>
                        @else
                            <span class="badge bg-secondary">Inativa</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 align-items-center">

                            <a href="{{ route('instituicoes.show', ['instituico' => $inst]) }}"
                               class="btn btn-sm btn-outline-secondary" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('instituicoes.edit', ['instituico' => $inst]) }}"
                               class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>

                            @if($inst->active)
                            <form method="POST" action="{{ route('instituicoes.destroy', ['instituico' => $inst]) }}"
                                  onsubmit="return confirm('Desativar {{ addslashes($inst->razao_social) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-secondary" title="Desativar" style="opacity:.7">
                                    <i class="bi bi-toggle-on"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('instituicoes.activate', ['instituico' => $inst]) }}"
                                  onsubmit="return confirm('Ativar {{ addslashes($inst->razao_social) }}?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-secondary" title="Ativar" style="opacity:.5">
                                    <i class="bi bi-toggle-off"></i>
                                </button>
                            </form>
                            @endif

                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('instituicoes.force-delete', ['instituico' => $inst]) }}"
                                  onsubmit="return confirm('EXCLUIR PERMANENTEMENTE {{ addslashes($inst->razao_social) }}?\n\nEsta ação não pode ser desfeita.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-secondary text-danger" title="Excluir permanentemente">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-building" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        Nenhuma instituição encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($institutions->hasPages())
    <div class="card-footer">
        {{ $institutions->links() }}
    </div>
    @endif
</div>

@endsection
