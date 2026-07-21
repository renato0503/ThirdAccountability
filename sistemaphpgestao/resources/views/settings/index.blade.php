@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Configurações do Sistema</h4>
        <p class="text-muted mb-0" style="font-size:13px">Gerencie integrações e chaves de API sem editar o servidor</p>
    </div>
</div>

<form method="POST" action="{{ route('configuracoes.update') }}">
    @csrf
    @method('PUT')

    @foreach($grupos as $grupoKey => $grupo)
    @php
        $itens = \App\Models\Setting::where('group', $grupoKey)->get();
    @endphp
    @if($itens->count())
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="{{ $grupo['icon'] }}" style="font-size:15px; color: var(--text-muted);"></i>
            {{ $grupo['label'] }}
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($itens as $setting)
                @php
                    $val = '';
                    if (isset($settings[$setting->key])) {
                        $s = $settings[$setting->key];
                        if (!$s->is_secret) {
                            $val = $s->value ?? '';
                        }
                    }
                @endphp
                <div class="col-md-6">
                    <label class="form-label">
                        {{ $setting->label }}
                        @if($setting->is_secret)
                        <span class="badge bg-secondary ms-1" style="font-size:10px;">secreto</span>
                        @endif
                    </label>
                    @if($setting->is_secret)
                    <input type="password" name="{{ $setting->key }}" class="form-control"
                        placeholder="Deixe em branco para manter o valor atual"
                        autocomplete="new-password">
                    @elseif($setting->key === 'mail_mailer')
                    <select name="{{ $setting->key }}" class="form-select">
                        @foreach(['log'=>'log (apenas registra, sem envio)','smtp'=>'smtp (envio real)','sendmail'=>'sendmail'] as $v => $l)
                        <option value="{{ $v }}" {{ $val === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @elseif($setting->key === 'asaas_env')
                    <select name="{{ $setting->key }}" class="form-select">
                        <option value="sandbox" {{ $val === 'sandbox' ? 'selected' : '' }}>sandbox (testes)</option>
                        <option value="production" {{ $val === 'production' ? 'selected' : '' }}>production (produção)</option>
                    </select>
                    @else
                    <input type="text" name="{{ $setting->key }}" value="{{ $val }}" class="form-control"
                        placeholder="{{ $setting->description }}">
                    @endif
                    @if($setting->description && !$setting->is_secret)
                    <div class="form-text">{{ $setting->description }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endforeach

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Salvar Configurações
        </button>
    </div>
</form>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-envelope-check" style="font-size:15px; color: var(--text-muted);"></i>
        Testar Configuração de E-mail
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('configuracoes.test-email') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Enviar e-mail de teste para</label>
                    <input type="email" name="email_teste" class="form-control"
                        placeholder="seuemail@exemplo.com" value="{{ auth()->user()->email }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-send"></i> Enviar Teste
                    </button>
                </div>
            </div>
            <div class="form-text mt-1">O e-mail será enviado usando as configurações SMTP salvas acima (precisa salvar primeiro).</div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-info-circle" style="font-size:15px; color: var(--text-muted);"></i>
        Status das Integrações
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Integração</th>
                    <th>Fase</th>
                    <th>Status</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $mailOk  = \App\Models\Setting::get('mail_mailer','log') !== 'log';
                    $asaasOk = \App\Models\Setting::get('asaas_token') !== '';
                    $zapiOk  = \App\Models\Setting::get('zapi_instance') !== '';
                    $d4Ok    = \App\Models\Setting::get('d4sign_token') !== '';
                    $mapsOk  = \App\Models\Setting::get('google_maps_key') !== '';
                    $pluggyOk= \App\Models\Setting::get('pluggy_client_id') !== '';
                @endphp
                <tr>
                    <td><i class="bi bi-envelope me-1"></i> E-mail Transacional</td>
                    <td><span class="badge bg-secondary">Fase 1</span></td>
                    <td>
                        @if($mailOk)
                        <span class="badge bg-success">Configurado</span>
                        @else
                        <span class="badge bg-warning">Modo log (sem envio)</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:12px">Driver: {{ \App\Models\Setting::get('mail_mailer','log') }}</td>
                </tr>
                <tr>
                    <td><i class="bi bi-search me-1"></i> Consulta CNPJ (BrasilAPI)</td>
                    <td><span class="badge bg-secondary">Fase 1</span></td>
                    <td><span class="badge bg-success">Ativo</span></td>
                    <td class="text-muted" style="font-size:12px">Gratuito, sem chave</td>
                </tr>
                <tr>
                    <td><i class="bi bi-geo-alt me-1"></i> Consulta CEP (ViaCEP)</td>
                    <td><span class="badge bg-secondary">Fase 1</span></td>
                    <td><span class="badge bg-success">Ativo</span></td>
                    <td class="text-muted" style="font-size:12px">Gratuito, sem chave</td>
                </tr>
                <tr>
                    <td><i class="bi bi-currency-dollar me-1"></i> Asaas (Pix/Boleto)</td>
                    <td><span class="badge bg-secondary">Fase 2</span></td>
                    <td>
                        @if($asaasOk)
                        <span class="badge bg-success">Token configurado</span>
                        @else
                        <span class="badge bg-secondary">Não configurado</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:12px">Ambiente: {{ \App\Models\Setting::get('asaas_env','sandbox') }}</td>
                </tr>
                <tr>
                    <td><i class="bi bi-whatsapp me-1"></i> Z-API (WhatsApp)</td>
                    <td><span class="badge bg-secondary">Fase 2</span></td>
                    <td>
                        @if($zapiOk)
                        <span class="badge bg-success">Configurado</span>
                        @else
                        <span class="badge bg-secondary">Não configurado</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td><i class="bi bi-pen me-1"></i> D4Sign (Assinatura)</td>
                    <td><span class="badge bg-secondary">Fase 2</span></td>
                    <td>
                        @if($d4Ok)
                        <span class="badge bg-success">Configurado</span>
                        @else
                        <span class="badge bg-secondary">Não configurado</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td><i class="bi bi-map me-1"></i> Google Maps</td>
                    <td><span class="badge bg-secondary">Fase 3</span></td>
                    <td>
                        @if($mapsOk)
                        <span class="badge bg-success">Configurado</span>
                        @else
                        <span class="badge bg-secondary">Não configurado</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td><i class="bi bi-bank me-1"></i> Pluggy (Open Finance)</td>
                    <td><span class="badge bg-secondary">Fase 3</span></td>
                    <td>
                        @if($pluggyOk)
                        <span class="badge bg-success">Configurado</span>
                        @else
                        <span class="badge bg-secondary">Não configurado</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
