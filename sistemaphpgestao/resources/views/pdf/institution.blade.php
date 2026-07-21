<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório Institucional – {{ $institution->razao_social }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 20px 28px;
        }

        /* ── Cabeçalho ── */
        .report-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .report-razao {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }

        .report-fantasia {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .report-meta {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }

        hr.header-rule {
            border: none;
            border-top: 2px solid #333;
            margin: 10px 0 16px;
        }

        /* ── Títulos de seção ── */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            border-bottom: 2px solid #333;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            color: #111;
        }

        /* ── Tabelas ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table th {
            background: #f0f0f0;
            text-align: left;
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 600;
            border: 1px solid #ccc;
        }

        table td {
            padding: 4px 6px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table.striped tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        /* ── DL de dados ── */
        .data-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .data-grid td.label {
            color: #555;
            font-weight: 500;
            width: 38%;
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .data-grid td.value {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            background: #e2e8f0;
            color: #333;
        }

        .badge-ativo   { background: #d1fae5; color: #065f46; }
        .badge-inativo { background: #fee2e2; color: #991b1b; }

        /* ── Texto longo ── */
        .text-block {
            font-size: 10.5px;
            line-height: 1.6;
            text-align: left;
            white-space: pre-wrap;
            margin-top: 4px;
        }

        /* ── Quebra de página ── */
        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* ── Rodapé ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 4px 28px;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ccc;
            background: #fff;
            text-align: center;
        }

        /* ── Utilitários ── */
        .text-muted  { color: #666; }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 4px; }
        .mb-0 { margin-bottom: 0; }
        .no-data { color: #888; font-style: italic; }
    </style>
</head>
<body>

{{-- ─────────────────────────── RODAPÉ FIXO ─────────────────────────── --}}
<div class="footer">
    Relatório gerado em {{ now()->format('d/m/Y H:i') }} | {{ $institution->razao_social }}
</div>

{{-- ─────────────────────────── CABEÇALHO ─────────────────────────── --}}
<div class="report-header">
    <div class="report-title">Relatório Institucional</div>
    <div class="report-razao">{{ $institution->razao_social }}</div>
    @if($institution->nome_fantasia && $institution->nome_fantasia !== $institution->razao_social)
        <div class="report-fantasia">{{ $institution->nome_fantasia }}</div>
    @endif
    <div class="report-meta">
        CNPJ: {{ $institution->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', preg_replace('/\D/', '', $institution->cnpj)) : '—' }}
        &nbsp;&bull;&nbsp;
        Gerado em: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<hr class="header-rule">

@php $sec = 1; @endphp

{{-- ═══════════════════════════════════════════════════════════════════
     DADOS PRINCIPAIS
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Dados Principais da Entidade</div>

<div class="avoid-break">
    <table class="data-grid">
        <tbody>
            <tr>
                <td class="label">CNPJ</td>
                <td class="value">
                    {{ $institution->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', preg_replace('/\D/', '', $institution->cnpj)) : '—' }}
                </td>
                <td class="label">Situação</td>
                <td class="value">
                    @php
                        $sit = strtolower($institution->situacao ?? '');
                        $badgeCls = str_contains($sit, 'ativa') || str_contains($sit, 'ativo') ? 'badge-ativo' : (str_contains($sit, 'inativa') || str_contains($sit, 'inativo') ? 'badge-inativo' : '');
                    @endphp
                    @if($institution->situacao)
                        <span class="badge {{ $badgeCls }}">{{ $institution->situacao }}</span>
                    @else
                        <span class="no-data">—</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Razão Social</td>
                <td class="value" colspan="3">{{ $institution->razao_social ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Nome Fantasia</td>
                <td class="value" colspan="3">{{ $institution->nome_fantasia ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">E-mail</td>
                <td class="value">{{ $institution->email ?? '—' }}</td>
                <td class="label">Telefone</td>
                <td class="value">{{ $institution->telefone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Site</td>
                <td class="value">{{ $institution->site ?? '—' }}</td>
                <td class="label">Instagram</td>
                <td class="value">{{ $institution->instagram ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Endereço</td>
                <td class="value" colspan="3">
                    @php
                        $partes = array_filter([
                            $institution->endereco ?? null,
                            $institution->numero ?? null,
                            $institution->complemento ?? null,
                            $institution->bairro ?? null,
                        ]);
                    @endphp
                    {{ $partes ? implode(', ', $partes) : '—' }}
                </td>
            </tr>
            <tr>
                <td class="label">Município / Estado</td>
                <td class="value">
                    {{ $institution->municipio ?? $institution->cidade ?? '—' }}
                    @if($institution->estado ?? $institution->uf ?? null)
                        / {{ $institution->estado ?? $institution->uf }}
                    @endif
                </td>
                <td class="label">CEP</td>
                <td class="value">{{ $institution->cep ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Área de Atuação</td>
                <td class="value" colspan="3">{{ $institution->area_atuacao ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     2. DADOS BANCÁRIOS
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Dados Bancários</div>

@php
    $temBanco = $institution->banco || $institution->agencia || $institution->conta_corrente
        || $institution->tipo_conta || $institution->chave_pix;
@endphp

<div class="avoid-break">
    @if($temBanco)
        <table class="data-grid">
            <tbody>
                <tr>
                    <td class="label">Banco</td>
                    <td class="value">{{ $institution->banco ?? '—' }}</td>
                    <td class="label">Tipo de Conta</td>
                    <td class="value">{{ $institution->tipo_conta ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Agência</td>
                    <td class="value">{{ $institution->agencia ?? '—' }}</td>
                    <td class="label">Conta Corrente</td>
                    <td class="value">{{ $institution->conta_corrente ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Chave Pix</td>
                    <td class="value" colspan="3">{{ $institution->chave_pix ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    @elseif($institution->dados_bancarios)
        <div class="text-block">{{ $institution->dados_bancarios }}</div>
    @else
        <p class="no-data">Dados bancários não informados.</p>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     3. DADOS DO PRESIDENTE / REPRESENTANTE LEGAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Dados do Presidente</div>

<div class="avoid-break">
    <table class="data-grid">
        <tbody>
            <tr>
                <td class="label">Nome</td>
                <td class="value" colspan="3">{{ $institution->representante_legal ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">CPF</td>
                <td class="value">
                    @php
                        $cpfRep = preg_replace('/\D/', '', $institution->presidente_cpf ?? '');
                        echo $cpfRep ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfRep) : '—';
                    @endphp
                </td>
                <td class="label">RG</td>
                <td class="value">
                    {{ $institution->presidente_rg ?? '—' }}
                    @if($institution->presidente_rg_expedicao)
                        <span class="text-muted">(exp. {{ \Carbon\Carbon::parse($institution->presidente_rg_expedicao)->format('d/m/Y') }})</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Data de Nascimento</td>
                <td class="value">
                    @if($institution->presidente_nascimento)
                        {{ \Carbon\Carbon::parse($institution->presidente_nascimento)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td class="label">Telefone</td>
                <td class="value">{{ $institution->presidente_telefone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">E-mail</td>
                <td class="value" colspan="3">{{ $institution->presidente_email ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Endereço</td>
                <td class="value" colspan="3">{{ $institution->presidente_endereco ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     4. DIRETORIA
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Diretoria</div>

@if($institution->diretoria && $institution->diretoria->count() > 0)
    <div class="avoid-break">
        <table class="striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Mandato (Início)</th>
                    <th>Mandato (Fim)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institution->diretoria as $membro)
                <tr>
                    <td>{{ $membro->nome ?? '—' }}</td>
                    <td>
                        @php
                            $cpf = preg_replace('/\D/', '', $membro->cpf ?? '');
                            echo $cpf ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf) : '—';
                        @endphp
                    </td>
                    <td>{{ $membro->cargo ?? '—' }}</td>
                    <td>{{ $membro->telefone ?? '—' }}</td>
                    <td>{{ $membro->email ?? '—' }}</td>
                    <td>
                        @if($membro->mandato_inicio ?? $membro->data_inicio ?? null)
                            {{ \Carbon\Carbon::parse($membro->mandato_inicio ?? $membro->data_inicio)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($membro->mandato_fim ?? $membro->data_fim ?? null)
                            {{ \Carbon\Carbon::parse($membro->mandato_fim ?? $membro->data_fim)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="no-data">Nenhum membro de diretoria cadastrado.</p>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     5. CONSELHO FISCAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Conselho Fiscal</div>

@if($institution->conselhoFiscal && $institution->conselhoFiscal->count() > 0)
    <div class="avoid-break">
        <table class="striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Mandato (Início)</th>
                    <th>Mandato (Fim)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institution->conselhoFiscal as $conselheiro)
                <tr>
                    <td>{{ $conselheiro->nome ?? '—' }}</td>
                    <td>
                        @php
                            $cpf = preg_replace('/\D/', '', $conselheiro->cpf ?? '');
                            echo $cpf ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf) : '—';
                        @endphp
                    </td>
                    <td>{{ $conselheiro->cargo ?? '—' }}</td>
                    <td>{{ $conselheiro->telefone ?? '—' }}</td>
                    <td>{{ $conselheiro->email ?? '—' }}</td>
                    <td>
                        @if($conselheiro->mandato_inicio ?? $conselheiro->data_inicio ?? null)
                            {{ \Carbon\Carbon::parse($conselheiro->mandato_inicio ?? $conselheiro->data_inicio)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($conselheiro->mandato_fim ?? $conselheiro->data_fim ?? null)
                            {{ \Carbon\Carbon::parse($conselheiro->mandato_fim ?? $conselheiro->data_fim)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="no-data">Nenhum membro de conselho fiscal cadastrado.</p>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     6. HISTÓRICO DA ENTIDADE
     ═══════════════════════════════════════════════════════════════════ --}}
@if($institution->historico_institucional)
<div class="section-title">{{ $sec++ }}. Histórico da Entidade</div>
<div class="avoid-break">
    <div class="text-block">{{ $institution->historico_institucional }}</div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     7. ESTRUTURA DA ENTIDADE
     ═══════════════════════════════════════════════════════════════════ --}}
@if($institution->descricao_estrutura_fisica)
<div class="section-title">{{ $sec++ }}. Estrutura da Entidade</div>
<div class="avoid-break">
    <div class="text-block">{{ $institution->descricao_estrutura_fisica }}</div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     8. HISTÓRICO DE PROJETOS
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Histórico de Projetos</div>

@if($institution->projectHistories && $institution->projectHistories->count() > 0)
    <table class="striped page-break-inside-auto">
        <thead>
            <tr>
                <th>Nome do Projeto</th>
                <th>Programa</th>
                <th>Fonte</th>
                <th>Valor</th>
                <th>Nº Convênio</th>
                <th>Nº Processo</th>
                <th>Assinatura</th>
                <th>Vigência</th>
            </tr>
        </thead>
        <tbody>
            @foreach($institution->projectHistories as $hist)
            <tr>
                <td>{{ $hist->nome ?? '—' }}</td>
                <td>{{ $hist->programa ?? '—' }}</td>
                <td>{{ $hist->fonte ?? '—' }}</td>
                <td>
                    @if($hist->valor)
                        R$&nbsp;{{ number_format($hist->valor, 2, ',', '.') }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $hist->numero_convenio ?? $hist->nr_convenio ?? '—' }}</td>
                <td>{{ $hist->numero_processo ?? $hist->nr_processo ?? '—' }}</td>
                <td>
                    @if($hist->data_assinatura)
                        {{ \Carbon\Carbon::parse($hist->data_assinatura)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @php
                        $vigIni = $hist->vigencia_inicio ?? $hist->data_inicio ?? null;
                        $vigFim = $hist->vigencia_fim   ?? $hist->data_fim   ?? null;
                    @endphp
                    @if($vigIni || $vigFim)
                        {{ $vigIni ? \Carbon\Carbon::parse($vigIni)->format('d/m/Y') : '?' }}
                        –
                        {{ $vigFim ? \Carbon\Carbon::parse($vigFim)->format('d/m/Y') : '?' }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="no-data">Nenhum histórico de projetos cadastrado.</p>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     9. LEIS DE UTILIDADE PÚBLICA
     ═══════════════════════════════════════════════════════════════════ --}}
@php
    $temMunicipal = $institution->utilidade_publica_municipal ?? false;
    $temEstadual  = $institution->utilidade_publica_estadual  ?? false;
    $temFederal   = $institution->utilidade_publica_federal   ?? false;
    $temAlgumaLei = $temMunicipal || $temEstadual || $temFederal;
@endphp

@if($temAlgumaLei)
<div class="section-title">{{ $sec++ }}. Leis de Utilidade Pública</div>
<div class="avoid-break">
    <table class="striped">
        <thead>
            <tr>
                <th style="width: 20%;">Âmbito</th>
                <th style="width: 40%;">Número da Lei</th>
                <th style="width: 40%;">Data</th>
            </tr>
        </thead>
        <tbody>
            @if($temMunicipal)
            <tr>
                <td><strong>Municipal</strong></td>
                <td>{{ $institution->lei_municipal_numero ?? '—' }}</td>
                <td>
                    @if($institution->lei_municipal_data)
                        {{ \Carbon\Carbon::parse($institution->lei_municipal_data)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endif
            @if($temEstadual)
            <tr>
                <td><strong>Estadual</strong></td>
                <td>{{ $institution->lei_estadual_numero ?? '—' }}</td>
                <td>
                    @if($institution->lei_estadual_data)
                        {{ \Carbon\Carbon::parse($institution->lei_estadual_data)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endif
            @if($temFederal)
            <tr>
                <td><strong>Federal</strong></td>
                <td>{{ $institution->lei_federal_numero ?? '—' }}</td>
                <td>
                    @if($institution->lei_federal_data)
                        {{ \Carbon\Carbon::parse($institution->lei_federal_data)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     10. PROJETOS VINCULADOS
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="section-title">{{ $sec++ }}. Projetos Vinculados</div>

@if($institution->projects && $institution->projects->count() > 0)
    <table class="striped">
        <thead>
            <tr>
                <th>Nome do Projeto</th>
                <th>Status</th>
                <th>Valor Total</th>
                <th>Data Início</th>
                <th>Data Fim</th>
            </tr>
        </thead>
        <tbody>
            @foreach($institution->projects as $proj)
            @php
                $projColors = [
                    'EM_EXECUCAO'       => 'background:#d1fae5;color:#065f46;',
                    'APROVADO'          => 'background:#dbeafe;color:#1e40af;',
                    'FINALIZADO'        => 'background:#f3f4f6;color:#374151;',
                    'SUSPENSO'          => 'background:#fee2e2;color:#991b1b;',
                    'EM_ANALISE'        => 'background:#fef9c3;color:#713f12;',
                    'RASCUNHO'          => 'background:#f3f4f6;color:#374151;',
                    'PRESTACAO_CONTAS'  => 'background:#cffafe;color:#0e7490;',
                    'CONCLUIDO'         => 'background:#d1fae5;color:#065f46;',
                    'CANCELADO'         => 'background:#fee2e2;color:#991b1b;',
                ];
                $projStyle = $projColors[$proj->status] ?? 'background:#e2e8f0;color:#333;';
            @endphp
            <tr>
                <td>{{ $proj->nome ?? '—' }}</td>
                <td>
                    <span class="badge" style="{{ $projStyle }}">
                        {{ $proj->status_label ?? $proj->status ?? '—' }}
                    </span>
                </td>
                <td>
                    @if($proj->valor_total)
                        R$&nbsp;{{ number_format($proj->valor_total, 2, ',', '.') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($proj->data_inicio)
                        {{ \Carbon\Carbon::parse($proj->data_inicio)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($proj->data_fim)
                        {{ \Carbon\Carbon::parse($proj->data_fim)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="no-data">Nenhum projeto vinculado a esta entidade.</p>
@endif

</body>
</html>
