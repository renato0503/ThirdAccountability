<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #09090b; }
    .header { padding: 18px 24px 14px; border-bottom: 2px solid #18181b; margin-bottom: 18px; }
    .header h1 { font-size: 17px; font-weight: 700; }
    .header p  { font-size: 10px; color: #71717a; margin-top: 2px; }
    .header .meta { float: right; text-align: right; font-size: 10px; color: #71717a; }
    .stats { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
    .stats td { width: 25%; text-align: center; padding: 10px 6px; border: 1px solid #e4e4e7; }
    .stats .lbl { font-size: 9px; color: #71717a; text-transform: uppercase; letter-spacing: .04em; }
    .stats .val { font-size: 14px; font-weight: 700; margin-top: 3px; }
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    table.data th { font-size: 9px; font-weight: 700; color: #71717a; text-transform: uppercase; padding: 6px 7px; background: #f4f4f5; border-bottom: 1px solid #e4e4e7; text-align: left; }
    table.data td { font-size: 10px; padding: 6px 7px; border-bottom: 1px solid #f4f4f5; vertical-align: top; }
    table.data tr.sel td { background: #f0fdf4; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 9999px; font-size: 9px; font-weight: 600; border: 1px solid #d4d4d8; background: #f4f4f5; }
    .section-title { font-size: 12.5px; font-weight: 700; margin: 12px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #e4e4e7; }
    dl.kv { width: 100%; }
    dl.kv dt { float: left; clear: left; width: 32%; color: #71717a; font-size: 10px; padding: 3px 0; }
    dl.kv dd { margin-left: 32%; padding: 3px 0; font-size: 10.5px; }
    .ref-box { border: 1px solid #16a34a; background: #f0fdf4; padding: 10px 14px; border-radius: 4px; margin-bottom: 14px; }
    .ref-box .lbl { font-size: 9px; color: #15803d; text-transform: uppercase; letter-spacing: .04em; }
    .ref-box .val { font-size: 18px; font-weight: 700; color: #15803d; }
    .footer { padding: 8px 24px; font-size: 9px; color: #71717a; border-top: 1px solid #e4e4e7; text-align: center; margin-top: 14px; }
    .url { color: #2563eb; word-break: break-all; }
</style>
</head>
<body>

<div class="header">
    <div class="meta">
        Gerado em {{ now()->format('d/m/Y H:i') }}<br>
        {{ config('app.name') }}
    </div>
    <h1>Relatório de Pesquisa de Preços Públicos</h1>
    <p>Pesquisa #{{ $pesquisa->id }} — {{ $pesquisa->institution?->razao_social ?? '—' }}</p>
</div>

{{-- Identificação --}}
<div class="section-title">Identificação</div>
<dl class="kv">
    <dt>Instituição</dt><dd>{{ $pesquisa->institution?->razao_social ?? '—' }}</dd>
    <dt>Projeto</dt><dd>{{ $pesquisa->project?->nome ?? 'Sem vínculo' }}@if($pesquisa->project?->codigo) ({{ $pesquisa->project->codigo }}) @endif</dd>
    <dt>Termo pesquisado</dt><dd><strong>{{ $pesquisa->search_term }}</strong></dd>
    <dt>Categoria</dt><dd>{{ $pesquisa->category ?? '—' }}</dd>
    <dt>Quantidade</dt><dd>{{ $pesquisa->quantity ?? '—' }} {{ $pesquisa->unit }}</dd>
    <dt>Estado / Município</dt><dd>{{ trim(($pesquisa->city ?? '').' / '.($pesquisa->state ?? ''), ' /') ?: '—' }}</dd>
    <dt>Período</dt><dd>{{ $pesquisa->date_start?->format('d/m/Y') ?? '—' }} a {{ $pesquisa->date_end?->format('d/m/Y') ?? '—' }}</dd>
    <dt>Fontes consultadas</dt><dd>{{ implode(', ', $pesquisa->sources ?? []) ?: '—' }}</dd>
    <dt>Pesquisado em</dt><dd>{{ $pesquisa->searched_at?->format('d/m/Y H:i') ?? '—' }}</dd>
    <dt>Responsável</dt><dd>{{ $pesquisa->user?->name ?? '—' }}</dd>
    <dt>Status</dt><dd>{{ $pesquisa->status_label }}</dd>
</dl>

<div style="clear: both;"></div>

{{-- Estatísticas --}}
<div class="section-title">Estatísticas dos preços encontrados</div>
<table class="stats">
    <tr>
        <td>
            <div class="lbl">Menor preço</div>
            <div class="val">@if($stats['min'] !== null) R$ {{ number_format($stats['min'], 2, ',', '.') }} @else — @endif</div>
        </td>
        <td>
            <div class="lbl">Maior preço</div>
            <div class="val">@if($stats['max'] !== null) R$ {{ number_format($stats['max'], 2, ',', '.') }} @else — @endif</div>
        </td>
        <td>
            <div class="lbl">Média</div>
            <div class="val">@if($stats['avg'] !== null) R$ {{ number_format($stats['avg'], 2, ',', '.') }} @else — @endif</div>
        </td>
        <td>
            <div class="lbl">Mediana</div>
            <div class="val">@if($stats['median'] !== null) R$ {{ number_format($stats['median'], 2, ',', '.') }} @else — @endif</div>
        </td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center; font-size: 10px; color: #71717a; border-top: none;">
            {{ $stats['count'] }} resultado(s) considerado(s)
        </td>
    </tr>
</table>

{{-- Preço de referência --}}
@if($pesquisa->selected_reference_price !== null)
    <div class="ref-box">
        <div class="lbl">Preço de referência adotado ({{ $pesquisa->reference_type_label }})</div>
        <div class="val">R$ {{ number_format($pesquisa->selected_reference_price, 2, ',', '.') }}</div>
        @if($pesquisa->justification)
            <div style="margin-top: 6px; font-size: 10.5px;">
                <strong>Justificativa:</strong> {{ $pesquisa->justification }}
            </div>
        @endif
    </div>
@endif

{{-- Resultados --}}
<div class="section-title">Resultados encontrados</div>
@if($results->isEmpty())
    <p style="font-size: 10px; color: #71717a;">Nenhum resultado registrado para esta pesquisa.</p>
@else
    <table class="data">
        <thead>
            <tr>
                <th>Sel.</th>
                <th>Fonte</th>
                <th>Descrição</th>
                <th style="text-align:right;">Valor unit.</th>
                <th>Órgão / Município</th>
                <th>Data</th>
                <th>Refs.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $r)
                <tr class="{{ $r->selected ? 'sel' : '' }}">
                    <td>{{ $r->selected ? '✔' : '' }}</td>
                    <td><span class="badge">{{ $r->source_label }}</span></td>
                    <td>
                        {{ \Illuminate\Support\Str::limit($r->original_description, 240) }}
                        @if($r->source_url)
                            <div class="url" style="margin-top: 2px;">{{ $r->source_url }}</div>
                        @endif
                        @if($r->selected && $r->selection_justification)
                            <div style="margin-top: 3px; font-style: italic; color: #15803d;">
                                Justificativa: {{ $r->selection_justification }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 700;">R$ {{ number_format($r->unit_price, 2, ',', '.') }}</td>
                    <td>
                        {{ $r->buyer_name ?? '—' }}<br>
                        <span style="color: #71717a; font-size: 9px;">{{ trim(($r->city ?? '').' / '.($r->state ?? ''), ' /') }}</span>
                    </td>
                    <td>{{ $r->purchase_date?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size: 9px;">
                        @if($r->ata_number)      Ata: {{ $r->ata_number }}<br>      @endif
                        @if($r->bid_number)      Lic.: {{ $r->bid_number }}<br>     @endif
                        @if($r->contract_number) Contr.: {{ $r->contract_number }}<br> @endif
                        @if($r->process_number)  Proc.: {{ $r->process_number }}    @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Itens selecionados --}}
@php $selecionados = $results->where('selected', true); @endphp
@if($selecionados->count())
    <div class="section-title">Itens selecionados para compor a cotação</div>
    <table class="data">
        <thead>
            <tr>
                <th>Fonte</th>
                <th>Descrição</th>
                <th style="text-align: right;">Valor unit.</th>
                <th>Justificativa</th>
                <th>Origem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($selecionados as $r)
                <tr>
                    <td><span class="badge">{{ $r->source_label }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit($r->original_description, 220) }}</td>
                    <td style="text-align: right; font-weight: 700;">R$ {{ number_format($r->unit_price, 2, ',', '.') }}</td>
                    <td>{{ $r->selection_justification ?? '—' }}</td>
                    <td>
                        @if($r->source_url)
                            <span class="url">{{ $r->source_url }}</span>
                        @else — @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if($pesquisa->notes)
    <div class="section-title">Observações internas</div>
    <p style="font-size: 10.5px; line-height: 1.5;">{{ $pesquisa->notes }}</p>
@endif

<div class="footer">
    Documento gerado automaticamente pelo {{ config('app.name') }} para fins de prestação de contas e auditoria.
    Todos os preços encontrados pelas fontes públicas são exibidos integralmente — nenhum resultado é ocultado.
</div>

</body>
</html>
