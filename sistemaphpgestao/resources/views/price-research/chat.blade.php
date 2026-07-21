@extends('layouts.app')
@section('content')

<div class="d-flex align-items-start justify-content-between mb-3">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Chat IA — Pesquisa de Preços</h1>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Descreva os itens que precisa cotar em linguagem natural
        </div>
    </div>
    <a href="{{ route('pesquisa-precos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Pesquisas
    </a>
</div>

<div class="row g-3">
    {{-- Coluna esquerda: chat --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">Digite sua solicitação</div>
            <div class="card-body">
                <form id="chatIaForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Instituição</label>
                        <select name="institution_id" id="instSelect" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" @selected($inst->id === auth()->user()->institution_id)>{{ $inst->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Projeto (opcional)</label>
                        <select name="project_id" id="projectSelect" class="form-select">
                            <option value="">—</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UF (opcional)</label>
                        <input type="text" name="state" maxlength="2" class="form-control" style="text-transform:uppercase;width:80px" placeholder="MT">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">O que você precisa cotar?</label>
                        <textarea name="texto" id="chatInput" rows="5" class="form-control" required
                            placeholder="Ex.: Cota 5 cotações de cada item: Bola MAX 200, Bola de Vôlei Penalty VP500, Rede de Vôlei 4 faixas, Coletes salva-vidas, Cinta de natação"></textarea>
                    </div>
                    <button type="submit" id="processBtn" class="btn btn-primary w-100">
                        <i class="bi bi-stars"></i> Processar com IA
                    </button>
                </form>

                <div id="loadingIndicator" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted" style="font-size:13px;">Interpretando texto e buscando preços...</div>
                    <div class="text-muted" style="font-size:11px;">Isso pode levar alguns segundos</div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Instruções</div>
            <div class="card-body" style="font-size:13px;line-height:1.6;">
                <p class="mb-1"><strong>Exemplos de texto:</strong></p>
                <ul class="mb-3 ps-3">
                    <li>"Preciso de 5 cadeiras dobráveis e 3 mesas retangulares"</li>
                    <li>"Cota 10 unidades de uniforme escolar completo"</li>
                    <li>"Bola de futebol, rede de vôlei, apito, cronômetro"</li>
                </ul>
                <p class="mb-1"><strong>O que o sistema faz:</strong></p>
                <ul class="mb-0 ps-3">
                    <li>Interpreta o texto com IA (Groq/Llama 3.3)</li>
                    <li>Extrai cada item automaticamente</li>
                    <li>Busca preços no PNCP + Mercado Livre + Zoom</li>
                    <li>Você seleciona as cotações válidas</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Coluna direita: resultados --}}
    <div class="col-md-7">
        <div id="resultsArea">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-chat-dots" style="font-size:32px;"></i>
                    <div class="mt-2">Digite os itens no campo ao lado e clique em <strong>Processar com IA</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Orçamento Manual --}}
<div class="modal fade" id="manualModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Orçamento Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="manualForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pesquisa_id" id="manualPesquisaId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CNPJ do fornecedor *</label>
                            <input type="text" name="cnpj" id="manualCnpj" class="form-control" required maxlength="18" placeholder="00.000.000/0001-00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Razão Social</label>
                            <input type="text" id="manualRazao" class="form-control" readonly placeholder="Auto-preenchido via BrasilAPI">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição do item *</label>
                            <input type="text" name="descricao" class="form-control" required placeholder="Ex.: Bola de Futebol Campo Max 200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$) *</label>
                            <input type="number" step="0.01" min="0.01" name="valor" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Observações</label>
                            <input type="text" name="observacoes" class="form-control" placeholder="Nº do orçamento, contato, etc.">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Anexo (PDF, JPG, PNG — máx 10MB)</label>
                            <input type="file" name="anexo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="previewArea" class="mt-2" style="display:none;">
                                <img id="previewImg" class="img-thumbnail" style="max-height:150px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Adicionar Orçamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Finalização --}}
<div class="modal fade" id="finalizarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Pesquisa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Confirme os dados abaixo antes de finalizar:</p>
                <div id="finalizarResumo" class="mb-3"></div>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Após finalizar, o status mudará para <strong>FINALIZADA</strong> e o PDF será gerado automaticamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="finalizarLink" class="btn btn-primary" data-turbo="false">
                    <i class="bi bi-check-lg"></i> Finalizar e gerar PDF
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const institutions = @json($institutions);
const projectsByInst = @json($projectsByInst);

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chatIaForm');
    const input = document.getElementById('chatInput');
    const btn = document.getElementById('processBtn');
    const loading = document.getElementById('loadingIndicator');
    const resultsArea = document.getElementById('resultsArea');
    const instSelect = document.getElementById('instSelect');
    const projectSelect = document.getElementById('projectSelect');

    // Projetos por instituição
    instSelect.addEventListener('change', () => {
        const instId = instSelect.value;
        projectSelect.innerHTML = '<option value="">—</option>';
        if (instId && projectsByInst[instId]) {
            projectsByInst[instId].forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nome;
                projectSelect.appendChild(opt);
            });
        }
    });
    instSelect.dispatchEvent(new Event('change'));

    // Processar formulário
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const texto = input.value.trim();
        if (!texto || texto.length < 3) return;

        btn.disabled = true;
        loading.style.display = 'block';
        resultsArea.innerHTML = '';

        try {
            const fd = new FormData(form);
            const res = await fetch('{{ route('api.chat-ia.processar') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd,
            });

            const data = await res.json();

            if (!res.ok) {
                resultsArea.innerHTML = `<div class="alert alert-danger">${data.error || 'Erro ao processar'}</div>`;
                return;
            }

            renderResults(data);
        } catch (err) {
            resultsArea.innerHTML = `<div class="alert alert-danger">Erro de conexão: ${err.message}</div>`;
        } finally {
            btn.disabled = false;
            loading.style.display = 'none';
        }
    });

    // CNPJ mask e autocomplete
    const cnpjInput = document.getElementById('manualCnpj');
    cnpjInput?.addEventListener('blur', async () => {
        const cnpj = cnpjInput.value.replace(/\D/g, '');
        if (cnpj.length !== 14) return;
        try {
            const res = await fetch(`{{ url('api/cnpj') }}/${cnpj}`);
            if (res.ok) {
                const data = await res.json();
                document.getElementById('manualRazao').value = data.razao_social || data.nome || '';
            }
        } catch (e) {}
    });

    // Preview de anexo
    const anexoInput = document.querySelector('input[name="anexo"]');
    anexoInput?.addEventListener('change', () => {
        const file = anexoInput.files[0];
        const preview = document.getElementById('previewArea');
        const img = document.getElementById('previewImg');
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => { img.src = e.target.result; preview.style.display = ''; };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Orçamento manual form
    document.getElementById('manualForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const pesquisaId = document.getElementById('manualPesquisaId').value;
        if (!pesquisaId) return;

        const fd = new FormData(e.target);
        try {
            const res = await fetch('{{ route('api.chat-ia.orcamento-manual') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd,
            });
            const data = await res.json();
            if (res.ok) {
                bootstrap.Modal.getInstance(document.getElementById('manualModal')).hide();
                // Recarrega os resultados da pesquisa
                await refreshPesquisa(pesquisaId);
            } else {
                alert(data.error || 'Erro ao adicionar orçamento');
            }
        } catch (err) {
            alert('Erro de conexão');
        }
    });
});

function renderResults(data) {
    const area = document.getElementById('resultsArea');
    const pesq = data.pesquisas || [];
    const stats = data.estatisticas || {};
    const errors = data.errors || {};

    if (pesq.length === 0) {
        area.innerHTML = `<div class="alert alert-warning">Nenhum item pôde ser processado.</div>`;
        return;
    }

    let html = `
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="badge bg-info">${stats.total_itens} itens</span>
            <span class="badge bg-success">${stats.com_resultados} com resultados</span>
            <span class="badge bg-warning">${stats.sem_resultados} sem resultados</span>
        </div>
    `;

    pesq.forEach(p => {
        const pResults = p.results || [];
        const selectedCount = pResults.filter(r => r.selected).length;

        html += `
            <div class="card mb-3" id="pesq-${p.id}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${p.search_term}</strong>
                        <span class="badge bg-${p.status_color} ms-2">${p.status_label}</span>
                        <span class="text-muted ms-2" style="font-size:12px;">Qtd: ${p.quantity || 1}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirManual(${p.id})">
                            <i class="bi bi-plus"></i> Orçamento manual
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="finalizar(${p.id})" ${selectedCount === 0 ? 'disabled' : ''}>
                            <i class="bi bi-check-lg"></i> Finalizar
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width:36px;">Sel.</th>
                                    <th>Fonte</th>
                                    <th>Descrição</th>
                                    <th class="text-end">Valor</th>
                                    <th>Fornecedor</th>
                                    <th style="width:60px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${pResults.length === 0 ? `
                                    <tr><td colspan="6" class="text-center text-muted py-3">Nenhum resultado encontrado</td></tr>
                                ` : pResults.map(r => `
                                    <tr class="${r.selected ? 'table-active' : ''}">
                                        <td>
                                            <button class="btn btn-sm btn-outline-${r.selected ? 'success' : 'secondary'} toggle-select"
                                                data-resultado-id="${r.id}"
                                                data-selected="${r.selected ? 1 : 0}"
                                                title="${r.selected ? 'Desmarcar' : 'Selecionar'}">
                                                <i class="bi bi-${r.selected ? 'check-square-fill' : 'square'}"></i>
                                            </button>
                                        </td>
                                        <td><span class="badge bg-light">${r.source}</span></td>
                                        <td style="font-size:13px;">${r.original_description || '—'}</td>
                                        <td class="text-end fw-semibold">R$ ${Number(r.unit_price).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                                        <td style="font-size:12px;">${r.buyer_name || r.cnpj_fornecedor || '—'}</td>
                                        <td class="text-end">
                                            ${r.source_url ? `<a href="${r.source_url}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Abrir"><i class="bi bi-box-arrow-up-right"></i></a>` : ''}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                ${p.status === 'BUSCADA' || p.status === 'RASCUNHO' ? `
                    <div class="card-footer text-center text-muted" style="font-size:12px;">
                        <i class="bi bi-info-circle"></i> Clique em "Orçamento manual" para adicionar cotações externas
                    </div>
                ` : ''}
            </div>
        `;
    });

    // Erros
    if (Object.keys(errors).length > 0) {
        html += `<div class="alert alert-warning"><strong>Avisos:</strong><ul class="mb-0 mt-1">`;
        for (const [item, msg] of Object.entries(errors)) {
            html += `<li>${item}: ${typeof msg === 'string' ? msg : JSON.stringify(msg)}</li>`;
        }
        html += `</ul></div>`;
    }

    area.innerHTML = html;

    // Event listeners para toggle select
    document.querySelectorAll('.toggle-select').forEach(btn => {
        btn.addEventListener('click', async () => {
            const resultadoId = btn.dataset.resultadoId;
            const selected = btn.dataset.selected === '1' ? 0 : 1;
            try {
                const fd = new FormData();
                fd.append('_token', CSRF_TOKEN);
                fd.append('resultado_id', resultadoId);
                fd.append('selected', selected);

                const res = await fetch('{{ route('api.chat-ia.selecionar') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
                if (res.ok) {
                    // Recarrega a pesquisa
                    const pesqId = btn.closest('[id^="pesq-"]').id.replace('pesq-', '');
                    await refreshPesquisa(pesqId);
                }
            } catch (e) {}
        });
    });
}

async function refreshPesquisa(pesquisaId) {
    try {
        const res = await fetch(`{{ url('api/chat-ia/status') }}/${pesquisaId}`);
        if (!res.ok) return;
        const data = await res.json();

        // Atualiza o card da pesquisa
        const card = document.getElementById(`pesq-${pesquisaId}`);
        if (!card) return;

        const p = data.pesquisa || {};
        const results = p.results || [];
        const selectedCount = results.filter(r => r.selected).length;

        // Atualiza badge de status
        const badge = card.querySelector('.card-header .badge');
        if (badge) {
            badge.className = `badge bg-${p.status_color} ms-2`;
            badge.textContent = p.status_label;
        }

        // Atualiza tabela
        const tbody = card.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = results.length === 0
                ? `<tr><td colspan="6" class="text-center text-muted py-3">Nenhum resultado encontrado</td></tr>`
                : results.map(r => `
                    <tr class="${r.selected ? 'table-active' : ''}">
                        <td>
                            <button class="btn btn-sm btn-outline-${r.selected ? 'success' : 'secondary'} toggle-select"
                                data-resultado-id="${r.id}"
                                data-selected="${r.selected ? 1 : 0}">
                                <i class="bi bi-${r.selected ? 'check-square-fill' : 'square'}"></i>
                            </button>
                        </td>
                        <td><span class="badge bg-light">${r.source}</span></td>
                        <td style="font-size:13px;">${r.original_description || '—'}</td>
                        <td class="text-end fw-semibold">R$ ${Number(r.unit_price).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td>
                        <td style="font-size:12px;">${r.buyer_name || r.cnpj_fornecedor || '—'}</td>
                        <td class="text-end">
                            ${r.source_url ? `<a href="${r.source_url}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i></a>` : ''}
                        </td>
                    </tr>
                `).join('');

            // Reatribuir eventos
            tbody.querySelectorAll('.toggle-select').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const resultadoId = btn.dataset.resultadoId;
                    const selected = btn.dataset.selected === '1' ? 0 : 1;
                    try {
                        const fd = new FormData();
                        fd.append('_token', CSRF_TOKEN);
                        fd.append('resultado_id', resultadoId);
                        fd.append('selected', selected);
                        const res = await fetch('{{ route('api.chat-ia.selecionar') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: fd,
                        });
                        if (res.ok) await refreshPesquisa(pesquisaId);
                    } catch (e) {}
                });
            });
        }

        // Atualiza botão finalizar
        const finalizarBtn = card.querySelector('.btn-outline-success');
        if (finalizarBtn) {
            finalizarBtn.disabled = selectedCount === 0;
        }
    } catch (e) {}
}

function abrirManual(pesquisaId) {
    document.getElementById('manualPesquisaId').value = pesquisaId;
    document.getElementById('manualCnpj').value = '';
    document.getElementById('manualRazao').value = '';
    document.querySelector('input[name="descricao"]').value = '';
    document.querySelector('input[name="valor"]').value = '';
    document.querySelector('input[name="observacoes"]').value = '';
    document.querySelector('input[name="anexo"]').value = '';
    document.getElementById('previewArea').style.display = 'none';
    new bootstrap.Modal(document.getElementById('manualModal')).show();
}

function finalizar(pesquisaId) {
    const card = document.getElementById(`pesq-${pesquisaId}`);
    const termo = card.querySelector('.card-header strong').textContent;
    const selected = card.querySelectorAll('.table-active').length;

    document.getElementById('finalizarResumo').innerHTML = `
        <p><strong>Item:</strong> ${termo}</p>
        <p><strong>Cotações selecionadas:</strong> ${selected}</p>
    `;
    document.getElementById('finalizarLink').href = `{{ url('pesquisa-precos') }}/${pesquisaId}/editar`;
    new bootstrap.Modal(document.getElementById('finalizarModal')).show();
}
</script>
@endpush

@endsection
