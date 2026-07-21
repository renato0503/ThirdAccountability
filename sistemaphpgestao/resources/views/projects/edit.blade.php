@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Editar Projeto</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">{{ Str::limit($project->nome, 60) }}</p>
    </div>
    <a href="{{ route('projetos.show', ['projeto' => $project]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('projetos.update', ['projeto' => $project]) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">

        {{-- ═══════════════════════════════════════════════════
             COLUNA ESQUERDA
        ════════════════════════════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- ── Card: Identificação ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-card-text me-1"></i> Identificação
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Nome do Projeto --}}
                        <div class="col-8">
                            <label class="form-label">Nome do Projeto *</label>
                            <input type="text" name="nome"
                                   value="{{ old('nome', $project->nome) }}"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   required placeholder="Título completo do projeto">
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Código (somente edição manual no edit) --}}
                        <div class="col-4">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo"
                                   value="{{ old('codigo', $project->codigo) }}"
                                   class="form-control @error('codigo') is-invalid @enderror"
                                   placeholder="Ex: 2025-001">
                            @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Número da Proposta --}}
                        <div class="col-md-6">
                            <label class="form-label">Número da Proposta</label>
                            <input type="text" name="numero_proposta"
                                   value="{{ old('numero_proposta', $project->numero_proposta) }}"
                                   class="form-control @error('numero_proposta') is-invalid @enderror"
                                   placeholder="Ex: 2025/0001">
                            @error('numero_proposta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Instituição --}}
                        <div class="col-md-6">
                            <label class="form-label">Instituição *</label>
                            <select name="institution_id"
                                    class="form-select @error('institution_id') is-invalid @enderror"
                                    required>
                                <option value="">Selecione a instituição...</option>
                                @foreach($institutions as $institution)
                                    <option value="{{ $institution->id }}"
                                        {{ old('institution_id', $project->institution_id) == $institution->id ? 'selected' : '' }}>
                                        {{ $institution->nome_fantasia ?? $institution->razao_social }}
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Fonte de Financiamento --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Fonte de Financiamento
                                <span style="color: var(--text-muted); font-weight: 400;">(opcional)</span>
                            </label>
                            <select name="funding_source_id"
                                    class="form-select @error('funding_source_id') is-invalid @enderror">
                                <option value="">Nenhuma</option>
                                @foreach($fundingSources as $fs)
                                    <option value="{{ $fs->id }}"
                                        {{ old('funding_source_id', $project->funding_source_id) == $fs->id ? 'selected' : '' }}>
                                        {{ $fs->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('funding_source_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', $project->status) == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Responsável Técnico do Projeto --}}
                        <div class="col-md-6">
                            <label class="form-label">Responsável Técnico do Projeto</label>
                            <input type="text" name="responsavel"
                                   value="{{ old('responsavel', $project->responsavel) }}"
                                   class="form-control @error('responsavel') is-invalid @enderror"
                                   placeholder="Nome do responsável técnico">
                            @error('responsavel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Fonte do Projeto --}}
                        <div class="col-md-6">
                            <label class="form-label">Fonte do Projeto</label>
                            <select name="fonte"
                                    class="form-select @error('fonte') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                @foreach($fontes as $fonte)
                                    <option value="{{ $fonte }}"
                                        {{ old('fonte', $project->fonte) == $fonte ? 'selected' : '' }}>
                                        {{ $fonte }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fonte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Parlamentar --}}
                        <div class="col-md-6">
                            <label class="form-label">Parlamentar</label>
                            <input type="text" name="parlamentar"
                                   value="{{ old('parlamentar', $project->parlamentar) }}"
                                   class="form-control @error('parlamentar') is-invalid @enderror"
                                   placeholder="Nome do parlamentar">
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                Opcional — para emendas parlamentares
                            </div>
                            @error('parlamentar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Secretaria/Órgão --}}
                        @php
                            $secretariaAtual = old('secretaria', $project->secretaria);
                            $isOutroEdit = $secretariaAtual === 'Outro';
                        @endphp

                        <div class="col-md-6">
                            <label class="form-label">Secretaria/Órgão</label>
                            <select name="secretaria" id="secretariaSelect"
                                    class="form-select @error('secretaria') is-invalid @enderror">
                                <option value="">Selecione...</option>
                                <optgroup label="Estaduais (MT)">
                                    @foreach($secretariasEstaduais as $sec)
                                        <option value="{{ $sec }}"
                                            {{ $secretariaAtual == $sec ? 'selected' : '' }}>
                                            {{ $sec }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Municipais">
                                    @foreach($secretariasMunicipais as $sec)
                                        <option value="{{ $sec }}"
                                            {{ $secretariaAtual == $sec ? 'selected' : '' }}>
                                            {{ $sec }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <option value="Outro" {{ $isOutroEdit ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('secretaria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Secretaria Outro --}}
                        <div class="col-md-6" id="secretariaOutroArea"
                             style="{{ $isOutroEdit ? '' : 'display: none;' }}">
                            <label class="form-label">Especifique a Secretaria/Órgão</label>
                            <input type="text" name="secretaria_outro" id="secretariaOutroInput"
                                   value="{{ old('secretaria_outro', $project->secretaria_outro) }}"
                                   class="form-control @error('secretaria_outro') is-invalid @enderror"
                                   placeholder="Informe o nome do órgão"
                                   {{ $isOutroEdit ? 'required' : '' }}>
                            @error('secretaria_outro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Identificação --}}

            {{-- ── Card: Locais de Execução ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-geo-alt me-1"></i> Locais de Execução
                </div>
                <div class="card-body">

                    <div id="locationsList">
                        @php
                            $existingLocations = old('locations');
                            $locationsCollection = $existingLocations
                                ? collect($existingLocations)
                                : $project->executionLocations;
                            $locCount = $locationsCollection->count();
                        @endphp

                        @if($locCount > 0)
                            @foreach($locationsCollection as $locIdx => $loc)
                                @php
                                    $locCidade = is_array($loc) ? ($loc['cidade'] ?? '') : ($loc->cidade ?? '');
                                    $locEstado = is_array($loc) ? ($loc['estado'] ?? '') : ($loc->estado ?? '');
                                @endphp
                                <div class="location-row row g-2 align-items-center mb-2"
                                     data-index="{{ $locIdx }}">
                                    <div class="col-5">
                                        <input type="text"
                                               name="locations[{{ $locIdx }}][cidade]"
                                               value="{{ $locCidade }}"
                                               class="form-control" placeholder="Cidade">
                                    </div>
                                    <div class="col-3">
                                        <select name="locations[{{ $locIdx }}][estado]" class="form-select">
                                            <option value="">UF</option>
                                            @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                                <option value="{{ $uf }}"
                                                    {{ $locEstado == $uf ? 'selected' : '' }}>
                                                    {{ $uf }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeLocation(this)" title="Remover local">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- Linha inicial vazia quando não há locais cadastrados --}}
                            <div class="location-row row g-2 align-items-center mb-2" data-index="0">
                                <div class="col-5">
                                    <input type="text" name="locations[0][cidade]"
                                           class="form-control" placeholder="Cidade">
                                </div>
                                <div class="col-3">
                                    <select name="locations[0][estado]" class="form-select">
                                        <option value="">UF</option>
                                        @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                            <option value="{{ $uf }}">{{ $uf }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="removeLocation(this)" title="Remover local">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                            onclick="addLocation()">
                        <i class="bi bi-plus"></i> Adicionar Local
                    </button>

                </div>
            </div>{{-- /card Locais --}}

            {{-- ── Card: Objeto e Objetivos ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-file-text me-1"></i> Objeto e Objetivos
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Objeto --}}
                        <div class="col-12">
                            <label class="form-label">Objeto</label>
                            <textarea name="descricao" rows="3"
                                      class="form-control @error('descricao') is-invalid @enderror"
                                      placeholder="Descreva o objeto do projeto...">{{ old('descricao', $project->descricao) }}</textarea>
                            @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Objetivo Geral --}}
                        <div class="col-12">
                            <label class="form-label">Objetivo Geral</label>
                            <textarea name="objetivo_geral" rows="2"
                                      class="form-control @error('objetivo_geral') is-invalid @enderror"
                                      placeholder="Qual o objetivo principal do projeto?">{{ old('objetivo_geral', $project->objetivo_geral) }}</textarea>
                            @error('objetivo_geral')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Objetivos Específicos (dinâmico, pré-populado) --}}
                        <div class="col-12">
                            <label class="form-label">Objetivos Específicos</label>

                            <div id="objectivesList">
                                @php
                                    $existingObjectives = old('objectives');
                                    $objectivesSource   = $existingObjectives
                                        ? collect($existingObjectives)
                                        : $project->specificObjectives;
                                    $objCount = $objectivesSource->count();
                                @endphp

                                @if($objCount > 0)
                                    @foreach($objectivesSource as $objIdx => $obj)
                                        @php
                                            $objText = is_array($obj) ? $obj : (is_string($obj) ? $obj : ($obj->objetivo ?? ''));
                                        @endphp
                                        <div class="objective-row d-flex align-items-center gap-2 mb-2"
                                             data-index="{{ $objIdx }}">
                                            <input type="text"
                                                   name="objectives[{{ $objIdx }}]"
                                                   value="{{ $objText }}"
                                                   class="form-control"
                                                   placeholder="Descreva o objetivo específico">
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm flex-shrink-0"
                                                    onclick="removeObjective(this)" title="Remover">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Linha inicial vazia --}}
                                    <div class="objective-row d-flex align-items-center gap-2 mb-2" data-index="0">
                                        <input type="text" name="objectives[0]"
                                               class="form-control"
                                               placeholder="Descreva o objetivo específico">
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm flex-shrink-0"
                                                onclick="removeObjective(this)" title="Remover">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                                    onclick="addObjective()">
                                <i class="bi bi-plus"></i> Adicionar Objetivo
                            </button>
                        </div>

                        {{-- Metodologia --}}
                        <div class="col-12">
                            <label class="form-label">Metodologia</label>
                            <textarea name="metodologia" rows="3"
                                      class="form-control @error('metodologia') is-invalid @enderror"
                                      placeholder="Descreva a metodologia a ser utilizada...">{{ old('metodologia', $project->metodologia) }}</textarea>
                            @error('metodologia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Público-Alvo --}}
                        <div class="col-12">
                            <label class="form-label">Público-Alvo</label>
                            <textarea name="publico_alvo" rows="2"
                                      class="form-control @error('publico_alvo') is-invalid @enderror"
                                      placeholder="Quem será beneficiado pelo projeto?">{{ old('publico_alvo', $project->publico_alvo) }}</textarea>
                            @error('publico_alvo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Justificativa --}}
                        <div class="col-12">
                            <label class="form-label">Justificativa</label>
                            <textarea name="justificativa" rows="2"
                                      class="form-control @error('justificativa') is-invalid @enderror"
                                      placeholder="Por que este projeto é necessário?">{{ old('justificativa', $project->justificativa) }}</textarea>
                            @error('justificativa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Objeto --}}

            {{-- ── Card: Programação e Público ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-people me-1"></i> Programação e Público
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Data, Local e Horário</label>
                            <textarea name="data_local_horario" rows="2"
                                      class="form-control @error('data_local_horario') is-invalid @enderror"
                                      placeholder="Ex: 10/06/2025 — Centro Cultural — das 14h às 18h">{{ old('data_local_horario', $project->data_local_horario) }}</textarea>
                            @error('data_local_horario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantidade Estimada de Público</label>
                            <input type="number" name="quantidade_publico"
                                   value="{{ old('quantidade_publico', $project->quantidade_publico) }}"
                                   class="form-control @error('quantidade_publico') is-invalid @enderror"
                                   min="0" placeholder="0">
                            @error('quantidade_publico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Municípios Alcançados</label>
                            <textarea name="municipios_alcancados" rows="2"
                                      class="form-control @error('municipios_alcancados') is-invalid @enderror"
                                      placeholder="Liste os municípios beneficiados pelo projeto">{{ old('municipios_alcancados', $project->municipios_alcancados) }}</textarea>
                            @error('municipios_alcancados')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição do Serviço</label>
                            <textarea name="descricao_servico" rows="3"
                                      class="form-control @error('descricao_servico') is-invalid @enderror"
                                      placeholder="Descreva detalhadamente o serviço a ser executado">{{ old('descricao_servico', $project->descricao_servico) }}</textarea>
                            @error('descricao_servico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Programação --}}

            {{-- ── Card: Capacidade Técnica ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-tools me-1"></i> Capacidade Técnica
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Descrição da Capacidade Técnica</label>
                            <textarea name="capacidade_tecnica" rows="4"
                                      class="form-control @error('capacidade_tecnica') is-invalid @enderror"
                                      placeholder="Descreva a capacidade técnica e operacional da OSC para executar o projeto">{{ old('capacidade_tecnica', $project->capacidade_tecnica) }}</textarea>
                            @error('capacidade_tecnica')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @php $existingPhotos = $project->capabilityPhotos; @endphp

                        @if($existingPhotos->count())
                        <div class="col-12">
                            <label class="form-label">Fotos Cadastradas</label>
                            <div class="row g-2">
                                @foreach($existingPhotos as $photo)
                                <div class="col-md-3">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $photo->file_path) }}"
                                             class="card-img-top" style="height: 110px; object-fit: cover;"
                                             alt="Foto capacidade técnica">
                                        <div class="card-body p-2">
                                            <p style="font-size: 12px; margin: 0 0 6px;">{{ $photo->legenda ?: 'Sem legenda' }}</p>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm w-100"
                                                    onclick="deleteCapabilityPhoto({{ $photo->id }})">
                                                <i class="bi bi-trash"></i> Remover
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">
                                {{ $existingPhotos->count() }} de 5 fotos cadastradas.
                            </div>
                        </div>
                        @endif

                        @php $remaining = max(0, 5 - $existingPhotos->count()); @endphp
                        @if($remaining > 0)
                        <div class="col-12">
                            <label class="form-label">Adicionar Novas Fotos <span style="color: var(--text-muted); font-weight: 400;">(restam {{ $remaining }})</span></label>
                            <div class="row g-2">
                                @for($i = 0; $i < $remaining; $i++)
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-1">
                                        <input type="file" name="capability_photos[]" accept="image/*"
                                               class="form-control form-control-sm">
                                        <input type="text" name="capability_legendas[]"
                                               class="form-control form-control-sm"
                                               placeholder="Legenda (opcional)">
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>{{-- /card Capacidade Técnica --}}

            {{-- ── Card: Equipe ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-person-badge me-1"></i> Equipe
                </div>
                <div class="card-body">

                    <div id="teamMembersList">
                        @php
                            $existingTeam = old('team_members');
                            $teamSource   = $existingTeam ? collect($existingTeam) : $project->teamMembers;
                        @endphp

                        @if($teamSource->count() > 0)
                            @foreach($teamSource as $tIdx => $tm)
                                @php
                                    $tFuncao   = is_array($tm) ? ($tm['funcao']     ?? '') : ($tm->funcao     ?? '');
                                    $tQtd      = is_array($tm) ? ($tm['quantidade'] ?? 1)  : ($tm->quantidade ?? 1);
                                    $tDesc     = is_array($tm) ? ($tm['descricao']  ?? '') : ($tm->descricao  ?? '');
                                @endphp
                                <div class="team-row row g-2 align-items-start mb-2" data-index="{{ $tIdx }}">
                                    <div class="col-md-4">
                                        <input type="text" name="team_members[{{ $tIdx }}][funcao]"
                                               value="{{ $tFuncao }}"
                                               class="form-control" placeholder="Função / Cargo">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="team_members[{{ $tIdx }}][quantidade]"
                                               value="{{ $tQtd }}"
                                               class="form-control" min="1" placeholder="Qtd.">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="team_members[{{ $tIdx }}][descricao]"
                                               value="{{ $tDesc }}"
                                               class="form-control" placeholder="Descrição das atribuições">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeTeamMember(this)" title="Remover">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="team-row row g-2 align-items-start mb-2" data-index="0">
                                <div class="col-md-4">
                                    <input type="text" name="team_members[0][funcao]"
                                           class="form-control" placeholder="Função / Cargo">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="team_members[0][quantidade]" value="1"
                                           class="form-control" min="1" placeholder="Qtd.">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="team_members[0][descricao]"
                                           class="form-control" placeholder="Descrição das atribuições">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="removeTeamMember(this)" title="Remover">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                            onclick="addTeamMember()">
                        <i class="bi bi-plus"></i> Adicionar Membro
                    </button>

                </div>
            </div>{{-- /card Equipe --}}

            {{-- ── Card: Função da OSC e Tributação ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-bank me-1"></i> Função da OSC e Tributação
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Função da OSC no Projeto</label>
                            <textarea name="funcao_osc" rows="3"
                                      class="form-control @error('funcao_osc') is-invalid @enderror"
                                      placeholder="Descreva o papel da OSC na execução do projeto">{{ old('funcao_osc', $project->funcao_osc) }}</textarea>
                            @error('funcao_osc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Recolhimento de Impostos</label>
                            <textarea name="recolhimento_impostos" rows="3"
                                      class="form-control @error('recolhimento_impostos') is-invalid @enderror"
                                      placeholder="Descreva os impostos a serem recolhidos (INSS, ISS, IRRF etc.)">{{ old('recolhimento_impostos', $project->recolhimento_impostos) }}</textarea>
                            @error('recolhimento_impostos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Função da OSC --}}

            {{-- ── Card: Serviços Contratados ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-receipt me-1"></i> Descrição dos Serviços Contratados
                </div>
                <div class="card-body">

                    <div id="contractedServicesList">
                        @php
                            $existingServices = old('contracted_services');
                            $servicesSource   = $existingServices ? collect($existingServices) : $project->contractedServices;
                        @endphp

                        @if($servicesSource->count() > 0)
                            @foreach($servicesSource as $sIdx => $sv)
                                @php
                                    $sTipo  = is_array($sv) ? ($sv['tipo_contratacao'] ?? 'PF') : ($sv->tipo_contratacao ?? 'PF');
                                    $sDesc  = is_array($sv) ? ($sv['descricao']        ?? '')   : ($sv->descricao        ?? '');
                                    $sPer   = is_array($sv) ? ($sv['periodo_execucao'] ?? '')   : ($sv->periodo_execucao ?? '');
                                    $sUni   = is_array($sv) ? ($sv['unidade_periodo']  ?? 'mes'): ($sv->unidade_periodo  ?? 'mes');
                                    $sPag   = is_array($sv) ? ($sv['tipo_pagamento']   ?? 'mensal') : ($sv->tipo_pagamento ?? 'mensal');
                                    $sVal   = is_array($sv) ? ($sv['valor']            ?? '')   : ($sv->valor            ?? '');
                                @endphp
                                <div class="service-row row g-2 align-items-start mb-3 pb-3 border-bottom" data-index="{{ $sIdx }}">
                                    <div class="col-md-2">
                                        <label class="form-label small">Tipo</label>
                                        <select name="contracted_services[{{ $sIdx }}][tipo_contratacao]" class="form-select">
                                            <option value="PF" {{ $sTipo == 'PF' ? 'selected' : '' }}>Pessoa Física</option>
                                            <option value="PJ" {{ $sTipo == 'PJ' ? 'selected' : '' }}>Pessoa Jurídica</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Descrição do Serviço</label>
                                        <input type="text" name="contracted_services[{{ $sIdx }}][descricao]"
                                               value="{{ $sDesc }}"
                                               class="form-control" placeholder="Descreva o serviço">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Período</label>
                                        <input type="number" name="contracted_services[{{ $sIdx }}][periodo_execucao]"
                                               value="{{ $sPer }}"
                                               class="form-control" min="0" placeholder="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Unidade</label>
                                        <select name="contracted_services[{{ $sIdx }}][unidade_periodo]" class="form-select">
                                            <option value="dia"    {{ $sUni == 'dia'    ? 'selected' : '' }}>Dia(s)</option>
                                            <option value="semana" {{ $sUni == 'semana' ? 'selected' : '' }}>Semana(s)</option>
                                            <option value="mes"    {{ $sUni == 'mes'    ? 'selected' : '' }}>Mês(es)</option>
                                            <option value="ano"    {{ $sUni == 'ano'    ? 'selected' : '' }}>Ano(s)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small">Pagto.</label>
                                        <select name="contracted_services[{{ $sIdx }}][tipo_pagamento]" class="form-select">
                                            <option value="mensal" {{ $sPag == 'mensal' ? 'selected' : '' }}>Mensal</option>
                                            <option value="unico"  {{ $sPag == 'unico'  ? 'selected' : '' }}>Único</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                                onclick="removeContractedService(this)" title="Remover">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Valor (R$)</label>
                                        <input type="number" name="contracted_services[{{ $sIdx }}][valor]" step="0.01" min="0"
                                               value="{{ $sVal }}"
                                               class="form-control" placeholder="0,00">
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="service-row row g-2 align-items-start mb-3 pb-3 border-bottom" data-index="0">
                                <div class="col-md-2">
                                    <label class="form-label small">Tipo</label>
                                    <select name="contracted_services[0][tipo_contratacao]" class="form-select">
                                        <option value="PF">Pessoa Física</option>
                                        <option value="PJ">Pessoa Jurídica</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Descrição do Serviço</label>
                                    <input type="text" name="contracted_services[0][descricao]"
                                           class="form-control" placeholder="Descreva o serviço">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Período</label>
                                    <input type="number" name="contracted_services[0][periodo_execucao]"
                                           class="form-control" min="0" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Unidade</label>
                                    <select name="contracted_services[0][unidade_periodo]" class="form-select">
                                        <option value="dia">Dia(s)</option>
                                        <option value="semana">Semana(s)</option>
                                        <option value="mes" selected>Mês(es)</option>
                                        <option value="ano">Ano(s)</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">Pagto.</label>
                                    <select name="contracted_services[0][tipo_pagamento]" class="form-select">
                                        <option value="mensal">Mensal</option>
                                        <option value="unico">Único</option>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="removeContractedService(this)" title="Remover">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Valor (R$)</label>
                                    <input type="number" name="contracted_services[0][valor]" step="0.01" min="0"
                                           class="form-control" placeholder="0,00">
                                </div>
                            </div>
                        @endif
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                            onclick="addContractedService()">
                        <i class="bi bi-plus"></i> Adicionar Serviço
                    </button>

                </div>
            </div>{{-- /card Serviços Contratados --}}

            {{-- ── Card: Riscos e Mitigação ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-shield-exclamation me-1"></i> Riscos e Plano de Mitigação
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Riscos Identificados</label>
                            <textarea name="riscos_identificados" rows="3"
                                      class="form-control @error('riscos_identificados') is-invalid @enderror"
                                      placeholder="Liste os riscos identificados para a execução do projeto">{{ old('riscos_identificados', $project->riscos_identificados) }}</textarea>
                            @error('riscos_identificados')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Plano de Mitigação</label>
                            <textarea name="plano_mitigacao" rows="3"
                                      class="form-control @error('plano_mitigacao') is-invalid @enderror"
                                      placeholder="Descreva as medidas para mitigar os riscos identificados">{{ old('plano_mitigacao', $project->plano_mitigacao) }}</textarea>
                            @error('plano_mitigacao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Riscos --}}

            {{-- ── Card: Resultados e Divulgação ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-megaphone me-1"></i> Resultados e Divulgação
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Resultados Esperados</label>
                            <textarea name="resultados_esperados" rows="3"
                                      class="form-control @error('resultados_esperados') is-invalid @enderror"
                                      placeholder="Descreva os resultados esperados ao término do projeto">{{ old('resultados_esperados', $project->resultados_esperados) }}</textarea>
                            @error('resultados_esperados')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Plano de Divulgação</label>
                            <textarea name="plano_divulgacao" rows="3"
                                      class="form-control @error('plano_divulgacao') is-invalid @enderror"
                                      placeholder="Descreva como o projeto será divulgado (mídias, eventos, materiais)">{{ old('plano_divulgacao', $project->plano_divulgacao) }}</textarea>
                            @error('plano_divulgacao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Resultados --}}

            {{-- ── Card: Outros Patrocinadores ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-building me-1"></i> Outros Patrocinadores
                </div>
                <div class="card-body">
                    @php $outrosFlag = old('outros_patrocinadores', $project->outros_patrocinadores); @endphp
                    <div class="row g-3">

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="outrosPatrocinadoresCheck"
                                       name="outros_patrocinadores" value="1"
                                       {{ $outrosFlag ? 'checked' : '' }}>
                                <label class="form-check-label" for="outrosPatrocinadoresCheck">
                                    O projeto possui outros patrocinadores ou apoiadores?
                                </label>
                            </div>
                        </div>

                        <div class="col-12" id="quaisPatrocinadoresArea"
                             style="{{ $outrosFlag ? '' : 'display: none;' }}">
                            <label class="form-label">Quais patrocinadores/apoiadores?</label>
                            <textarea name="quais_patrocinadores" rows="3"
                                      class="form-control @error('quais_patrocinadores') is-invalid @enderror"
                                      placeholder="Liste os patrocinadores e apoiadores do projeto">{{ old('quais_patrocinadores', $project->quais_patrocinadores) }}</textarea>
                            @error('quais_patrocinadores')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>{{-- /card Outros Patrocinadores --}}

            {{-- ── Card: Assinatura ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-pen me-1"></i> Assinatura
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Data da Assinatura</label>
                            <input type="date" name="data_assinatura"
                                   value="{{ old('data_assinatura', $project->data_assinatura?->format('Y-m-d')) }}"
                                   class="form-control @error('data_assinatura') is-invalid @enderror">
                            @error('data_assinatura')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Nome do Presidente</label>
                            <input type="text" name="nome_presidente"
                                   value="{{ old('nome_presidente', $project->nome_presidente) }}"
                                   class="form-control @error('nome_presidente') is-invalid @enderror"
                                   placeholder="Nome completo do presidente da OSC">
                            @error('nome_presidente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($project->assinatura_path)
                        <div class="col-12">
                            <label class="form-label">Assinatura Atual</label>
                            <div>
                                <img src="{{ asset('storage/' . $project->assinatura_path) }}"
                                     alt="Assinatura"
                                     style="max-height: 90px; border: 1px solid var(--border); padding: 6px; border-radius: 4px;">
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label">{{ $project->assinatura_path ? 'Substituir Imagem da Assinatura' : 'Imagem da Assinatura' }} <span style="color: var(--text-muted); font-weight: 400;">(opcional)</span></label>
                            <input type="file" name="assinatura_image" accept="image/*"
                                   class="form-control @error('assinatura_image') is-invalid @enderror">
                            @error('assinatura_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                Envie uma imagem digitalizada da assinatura do presidente (PNG/JPG).
                            </div>
                        </div>

                    </div>
                </div>
            </div>{{-- /card Assinatura --}}

        </div>{{-- /col-lg-8 --}}

        {{-- ═══════════════════════════════════════════════════
             COLUNA DIREITA
        ════════════════════════════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Card: Cronograma Geral ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-calendar3 me-1"></i> Cronograma Geral
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Data de Início</label>
                            <input type="date" name="data_inicio"
                                   value="{{ old('data_inicio', $project->data_inicio?->format('Y-m-d')) }}"
                                   class="form-control @error('data_inicio') is-invalid @enderror">
                            @error('data_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Data de Término</label>
                            <input type="date" name="data_fim"
                                   value="{{ old('data_fim', $project->data_fim?->format('Y-m-d')) }}"
                                   class="form-control @error('data_fim') is-invalid @enderror">
                            @error('data_fim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Card: Valores ── --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-cash-stack me-1"></i> Valores (R$)
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Valor Total</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="valor_total"
                                       value="{{ old('valor_total', $project->valor_total) }}"
                                       class="form-control @error('valor_total') is-invalid @enderror"
                                       step="0.01" min="0" placeholder="0,00">
                                @error('valor_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Card: Ações ── --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('projetos.show', ['projeto' => $project]) }}"
                           class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}

    </div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Lista de UFs ───────────────────────────────────────────────────────────
    var UFS = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT',
               'PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

    function buildUFSelect(name, selectedValue) {
        var sel = document.createElement('select');
        sel.name = name;
        sel.className = 'form-select';
        var blank = new Option('UF', '');
        sel.appendChild(blank);
        UFS.forEach(function (uf) {
            var opt = new Option(uf, uf);
            if (uf === selectedValue) { opt.selected = true; }
            sel.appendChild(opt);
        });
        return sel;
    }

    // ── Secretaria: mostrar/ocultar campo "Outro" ──────────────────────────────
    var secretariaSelect     = document.getElementById('secretariaSelect');
    var secretariaOutroArea  = document.getElementById('secretariaOutroArea');
    var secretariaOutroInput = document.getElementById('secretariaOutroInput');

    function toggleSecretariaOutro() {
        var isOutro = secretariaSelect && secretariaSelect.value === 'Outro';
        if (secretariaOutroArea)  { secretariaOutroArea.style.display = isOutro ? '' : 'none'; }
        if (secretariaOutroInput) {
            secretariaOutroInput.required = isOutro;
            if (!isOutro) { secretariaOutroInput.value = ''; }
        }
    }

    if (secretariaSelect) {
        secretariaSelect.addEventListener('change', toggleSecretariaOutro);
        // Não chama toggleSecretariaOutro() no load: estado inicial já renderizado pelo Blade
    }

    // ── Locais de Execução ─────────────────────────────────────────────────────
    // Calcula o próximo índice a partir das linhas já existentes no DOM
    var locationIndex = (function () {
        var rows = document.querySelectorAll('#locationsList .location-row');
        var max  = -1;
        rows.forEach(function (r) {
            var idx = parseInt(r.dataset.index, 10);
            if (!isNaN(idx) && idx > max) { max = idx; }
        });
        return max + 1;
    }());

    window.addLocation = function () {
        var list = document.getElementById('locationsList');
        var idx  = locationIndex++;

        var row = document.createElement('div');
        row.className = 'location-row row g-2 align-items-center mb-2';
        row.dataset.index = idx;

        var colCidade = document.createElement('div');
        colCidade.className = 'col-5';
        var inputCidade = document.createElement('input');
        inputCidade.type = 'text';
        inputCidade.name = 'locations[' + idx + '][cidade]';
        inputCidade.className = 'form-control';
        inputCidade.placeholder = 'Cidade';
        colCidade.appendChild(inputCidade);

        var colEstado = document.createElement('div');
        colEstado.className = 'col-3';
        colEstado.appendChild(buildUFSelect('locations[' + idx + '][estado]', ''));

        var colBtn = document.createElement('div');
        colBtn.className = 'col-1';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-danger btn-sm';
        btn.title = 'Remover local';
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
        btn.onclick = function () { removeLocation(btn); };
        colBtn.appendChild(btn);

        row.appendChild(colCidade);
        row.appendChild(colEstado);
        row.appendChild(colBtn);
        list.appendChild(row);
    };

    window.removeLocation = function (btn) {
        var row  = btn.closest('.location-row');
        var list = document.getElementById('locationsList');
        if (list.querySelectorAll('.location-row').length > 1) {
            row.remove();
        } else {
            var inp = row.querySelector('input[type="text"]');
            var sel = row.querySelector('select');
            if (inp) { inp.value = ''; }
            if (sel) { sel.value = ''; }
        }
    };

    // ── Objetivos Específicos ──────────────────────────────────────────────────
    var objectiveIndex = (function () {
        var rows = document.querySelectorAll('#objectivesList .objective-row');
        var max  = -1;
        rows.forEach(function (r) {
            var idx = parseInt(r.dataset.index, 10);
            if (!isNaN(idx) && idx > max) { max = idx; }
        });
        return max + 1;
    }());

    window.addObjective = function () {
        var list = document.getElementById('objectivesList');
        var idx  = objectiveIndex++;

        var row = document.createElement('div');
        row.className = 'objective-row d-flex align-items-center gap-2 mb-2';
        row.dataset.index = idx;

        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'objectives[' + idx + ']';
        input.className = 'form-control';
        input.placeholder = 'Descreva o objetivo específico';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-danger btn-sm flex-shrink-0';
        btn.title = 'Remover';
        btn.innerHTML = '<i class="bi bi-x-lg"></i>';
        btn.onclick = function () { removeObjective(btn); };

        row.appendChild(input);
        row.appendChild(btn);
        list.appendChild(row);
    };

    window.removeObjective = function (btn) {
        var row  = btn.closest('.objective-row');
        var list = document.getElementById('objectivesList');
        if (list.querySelectorAll('.objective-row').length > 1) {
            row.remove();
        } else {
            var inp = row.querySelector('input');
            if (inp) { inp.value = ''; }
        }
    };

    // ── Equipe (Team Members) ──────────────────────────────────────────────────
    var teamIndex = (function () {
        var rows = document.querySelectorAll('#teamMembersList .team-row');
        var max  = -1;
        rows.forEach(function (r) {
            var idx = parseInt(r.dataset.index, 10);
            if (!isNaN(idx) && idx > max) { max = idx; }
        });
        return max + 1;
    }());

    window.addTeamMember = function () {
        var list = document.getElementById('teamMembersList');
        var idx  = teamIndex++;

        var row = document.createElement('div');
        row.className = 'team-row row g-2 align-items-start mb-2';
        row.dataset.index = idx;
        row.innerHTML =
            '<div class="col-md-4">' +
              '<input type="text" name="team_members[' + idx + '][funcao]" class="form-control" placeholder="Função / Cargo">' +
            '</div>' +
            '<div class="col-md-2">' +
              '<input type="number" name="team_members[' + idx + '][quantidade]" value="1" class="form-control" min="1" placeholder="Qtd.">' +
            '</div>' +
            '<div class="col-md-5">' +
              '<input type="text" name="team_members[' + idx + '][descricao]" class="form-control" placeholder="Descrição das atribuições">' +
            '</div>' +
            '<div class="col-md-1">' +
              '<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTeamMember(this)" title="Remover"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
        list.appendChild(row);
    };

    window.removeTeamMember = function (btn) {
        var row  = btn.closest('.team-row');
        var list = document.getElementById('teamMembersList');
        if (list.querySelectorAll('.team-row').length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(function(i){
                i.value = i.type === 'number' ? '1' : '';
            });
        }
    };

    // ── Serviços Contratados ───────────────────────────────────────────────────
    var serviceIndex = (function () {
        var rows = document.querySelectorAll('#contractedServicesList .service-row');
        var max  = -1;
        rows.forEach(function (r) {
            var idx = parseInt(r.dataset.index, 10);
            if (!isNaN(idx) && idx > max) { max = idx; }
        });
        return max + 1;
    }());

    window.addContractedService = function () {
        var list = document.getElementById('contractedServicesList');
        var idx  = serviceIndex++;

        var row = document.createElement('div');
        row.className = 'service-row row g-2 align-items-start mb-3 pb-3 border-bottom';
        row.dataset.index = idx;
        row.innerHTML =
            '<div class="col-md-2">' +
              '<label class="form-label small">Tipo</label>' +
              '<select name="contracted_services[' + idx + '][tipo_contratacao]" class="form-select">' +
                '<option value="PF">Pessoa Física</option>' +
                '<option value="PJ">Pessoa Jurídica</option>' +
              '</select>' +
            '</div>' +
            '<div class="col-md-4">' +
              '<label class="form-label small">Descrição do Serviço</label>' +
              '<input type="text" name="contracted_services[' + idx + '][descricao]" class="form-control" placeholder="Descreva o serviço">' +
            '</div>' +
            '<div class="col-md-2">' +
              '<label class="form-label small">Período</label>' +
              '<input type="number" name="contracted_services[' + idx + '][periodo_execucao]" class="form-control" min="0" placeholder="0">' +
            '</div>' +
            '<div class="col-md-2">' +
              '<label class="form-label small">Unidade</label>' +
              '<select name="contracted_services[' + idx + '][unidade_periodo]" class="form-select">' +
                '<option value="dia">Dia(s)</option>' +
                '<option value="semana">Semana(s)</option>' +
                '<option value="mes" selected>Mês(es)</option>' +
                '<option value="ano">Ano(s)</option>' +
              '</select>' +
            '</div>' +
            '<div class="col-md-1">' +
              '<label class="form-label small">Pagto.</label>' +
              '<select name="contracted_services[' + idx + '][tipo_pagamento]" class="form-select">' +
                '<option value="mensal">Mensal</option>' +
                '<option value="unico">Único</option>' +
              '</select>' +
            '</div>' +
            '<div class="col-md-1 d-flex align-items-end">' +
              '<button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeContractedService(this)" title="Remover"><i class="bi bi-x-lg"></i></button>' +
            '</div>' +
            '<div class="col-md-3">' +
              '<label class="form-label small">Valor (R$)</label>' +
              '<input type="number" name="contracted_services[' + idx + '][valor]" step="0.01" min="0" class="form-control" placeholder="0,00">' +
            '</div>';
        list.appendChild(row);
    };

    window.removeContractedService = function (btn) {
        var row  = btn.closest('.service-row');
        var list = document.getElementById('contractedServicesList');
        if (list.querySelectorAll('.service-row').length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(function(i){ i.value = ''; });
        }
    };

    // ── Outros Patrocinadores: toggle ──────────────────────────────────────────
    var outrosCheck = document.getElementById('outrosPatrocinadoresCheck');
    var quaisArea   = document.getElementById('quaisPatrocinadoresArea');

    if (outrosCheck && quaisArea) {
        outrosCheck.addEventListener('change', function () {
            quaisArea.style.display = this.checked ? '' : 'none';
        });
    }

    // ── Excluir foto da capacidade técnica ─────────────────────────────────────
    var deleteUrlBase = "{{ url('projetos/' . $project->id . '/fotos-capacidade') }}";
    var csrfToken     = document.querySelector('meta[name="csrf-token"]')
                          ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                          : "{{ csrf_token() }}";

    window.deleteCapabilityPhoto = function (photoId) {
        if (!confirm('Tem certeza que deseja remover esta foto?')) return;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrlBase + '/' + photoId;

        var csrf = document.createElement('input');
        csrf.type  = 'hidden';
        csrf.name  = '_token';
        csrf.value = csrfToken;
        form.appendChild(csrf);

        var method = document.createElement('input');
        method.type  = 'hidden';
        method.name  = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    };

})();
</script>
@endpush
