<?php
namespace App\Http\Controllers;

use App\Models\{PriceResearch, PriceResearchResult, Project, Institution, AuditLog};
use App\Services\PriceResearch\PriceResearchAggregator;
use App\Services\PriceResearch\PncpPriceService;
use App\Services\PriceResearch\RadarTceMtPriceService;
use App\Http\Controllers\Concerns\NormalizesBrlNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PriceResearchController extends Controller
{
    use NormalizesBrlNumbers;

    public function __construct(
        private PriceResearchAggregator $aggregator,
        private PncpPriceService $pncp,
        private RadarTceMtPriceService $radar,
    ) {}

    /* ─────────── Helpers de autorização e escopo ─────────── */

    private function scope() {
        $user = Auth::user();
        $q = PriceResearch::with(['project','institution','user'])
            ->withCount('results')
            ->withCount(['results as selected_results_count' => fn($r) => $r->where('selected', true)]);

        if (!$user->isAdmin() && $user->institution_id) {
            $q->where('institution_id', $user->institution_id);
        }
        return $q;
    }

    private function ensureAccess(PriceResearch $pesquisa): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        if ($user->institution_id && $pesquisa->institution_id === $user->institution_id) return;
        abort(403, 'Sem permissão para acessar esta pesquisa.');
    }

    private function ensureProjectInScope(?int $projectId, int $institutionId): void
    {
        if (!$projectId) return;
        $project = Project::find($projectId);
        if (!$project || $project->institution_id !== $institutionId) {
            abort(422, 'Projeto não pertence à instituição informada.');
        }
    }

    /* ─────────── CRUD ─────────── */

    public function index(Request $req)
    {
        $q = $this->scope();

        if ($s = $req->search) {
            $q->where('search_term', 'like', "%$s%");
        }
        if ($status = $req->status) $q->where('status', $status);
        if ($projectId = $req->project_id) $q->where('project_id', $projectId);

        $pesquisas = $q->orderBy('updated_at','desc')->paginate(15)->withQueryString();
        $statuses  = PriceResearch::statusList();

        return view('price-research.index', compact('pesquisas','statuses'));
    }

    public function create(Request $req)
    {
        $user = Auth::user();
        $institutions = $user->isAdmin()
            ? Institution::where('active', true)->orderBy('razao_social')->get()
            : Institution::where('id', $user->institution_id)->get();

        $projectsQuery = Project::query();
        if (!$user->isAdmin() && $user->institution_id) {
            $projectsQuery->where('institution_id', $user->institution_id);
        }
        $projects = $projectsQuery->orderBy('nome')->get();

        $projectId = $req->integer('project_id') ?: null;

        return view('price-research.create', [
            'institutions' => $institutions,
            'projects'     => $projects,
            'units'        => PriceResearch::unitList(),
            'sources'      => PriceResearch::sourceList(),
            'projectId'    => $projectId,
        ]);
    }

    public function store(Request $req)
    {
        $user = Auth::user();

        $this->normalizeBrl($req, ['quantity']);
        $data = $req->validate([
            'institution_id' => 'required|exists:institutions,id',
            'project_id'     => 'nullable|exists:projects,id',
            'search_term'    => 'required|string|max:255',
            'category'       => 'nullable|string|max:120',
            'quantity'       => 'nullable|numeric|min:0',
            'unit'           => 'nullable|string|max:60',
            'sources'        => 'nullable|array',
            'sources.*'      => 'in:PNCP,RADAR_TCE_MT',
            'state'          => 'nullable|string|size:2',
            'city'           => 'nullable|string|max:120',
            'date_start'     => 'nullable|date',
            'date_end'       => 'nullable|date|after_or_equal:date_start',
            'notes'          => 'nullable|string',
        ]);

        // ADMIN_INSTITUICAO/usuários só podem criar para a própria instituição
        if (!$user->isAdmin()) {
            $data['institution_id'] = $user->institution_id;
        }
        $this->ensureProjectInScope($data['project_id'] ?? null, (int) $data['institution_id']);

        $data['user_id'] = $user->id;
        $data['status']  = 'RASCUNHO';
        $data['sources'] = $data['sources'] ?? ['PNCP'];

        $pesquisa = PriceResearch::create($data);

        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => 'CREATE',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa->id,
            'dados'       => json_encode(['search_term' => $pesquisa->search_term]),
            'ip'          => $req->ip(),
        ]);

        if ($req->has('buscar_agora')) {
            return redirect()->route('pesquisa-precos.search', $pesquisa);
        }
        return redirect()->route('pesquisa-precos.show', $pesquisa)
            ->with('success', 'Pesquisa criada. Clique em "Buscar preços" para consultar as fontes.');
    }

    public function show(PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);

        $pesquisa_preco->load(['project.institution','institution','user','results']);

        $stats   = $this->aggregator->statistics($pesquisa_preco->results);
        $results = $this->applyFilters($pesquisa_preco->results, request());

        return view('price-research.show', [
            'pesquisa' => $pesquisa_preco,
            'results'  => $results,
            'stats'    => $stats,
            'units'    => PriceResearch::unitList(),
            'pncpUrl'  => $this->pncp->publicAtasUrl($pesquisa_preco->search_term),
            'radarUrl' => $this->radar->publicSearchUrl($pesquisa_preco->search_term),
        ]);
    }

    public function edit(PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);
        $user = Auth::user();
        $institutions = $user->isAdmin()
            ? Institution::where('active', true)->orderBy('razao_social')->get()
            : Institution::where('id', $user->institution_id)->get();
        $projects = Project::where('institution_id', $pesquisa_preco->institution_id)->orderBy('nome')->get();

        return view('price-research.edit', [
            'pesquisa'     => $pesquisa_preco,
            'institutions' => $institutions,
            'projects'     => $projects,
            'units'        => PriceResearch::unitList(),
            'sources'      => PriceResearch::sourceList(),
            'statuses'     => PriceResearch::statusList(),
        ]);
    }

    public function update(Request $req, PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);

        $this->normalizeBrl($req, ['quantity', 'selected_reference_price']);
        $data = $req->validate([
            'project_id'   => 'nullable|exists:projects,id',
            'search_term'  => 'required|string|max:255',
            'category'     => 'nullable|string|max:120',
            'quantity'     => 'nullable|numeric|min:0',
            'unit'         => 'nullable|string|max:60',
            'sources'      => 'nullable|array',
            'sources.*'    => 'in:PNCP,RADAR_TCE_MT',
            'state'        => 'nullable|string|size:2',
            'city'         => 'nullable|string|max:120',
            'date_start'   => 'nullable|date',
            'date_end'     => 'nullable|date|after_or_equal:date_start',
            'notes'        => 'nullable|string',
            'status'       => 'nullable|in:' . implode(',', PriceResearch::statusList()),
            'reference_type' => 'nullable|in:MENOR,MAIOR,MEDIA,MEDIANA,MANUAL,ITEM',
            'selected_reference_price' => 'nullable|numeric|min:0',
            'justification' => 'nullable|string',
        ]);

        $this->ensureProjectInScope($data['project_id'] ?? null, $pesquisa_preco->institution_id);

        $pesquisa_preco->update($data);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'UPDATE',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
            'ip'          => $req->ip(),
        ]);

        return redirect()->route('pesquisa-precos.show', $pesquisa_preco)
            ->with('success', 'Pesquisa atualizada.');
    }

    public function destroy(PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isInstAdmin() && $pesquisa_preco->user_id !== $user->id) {
            abort(403);
        }

        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => 'DELETE',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
        ]);

        $pesquisa_preco->delete();
        return redirect()->route('pesquisa-precos.index')->with('success', 'Pesquisa excluída.');
    }

    /* ─────────── Busca nas fontes públicas ─────────── */

    public function search(PriceResearch $pesquisa_preco, Request $req)
    {
        $this->ensureAccess($pesquisa_preco);

        $sources = $pesquisa_preco->sources ?: ['PNCP'];
        $filters = [
            'state'      => $pesquisa_preco->state,
            'city'       => $pesquisa_preco->city,
            'date_start' => $pesquisa_preco->date_start?->format('Y-m-d'),
            'date_end'   => $pesquisa_preco->date_end?->format('Y-m-d'),
            'tam_pagina' => 30,
        ];

        $payload = $this->aggregator->search($pesquisa_preco->search_term, $sources, $filters);

        // Limpa resultados anteriores que vieram de fontes (mantém manuais)
        $pesquisa_preco->results()
            ->whereIn('source', ['PNCP','RADAR_TCE_MT'])
            ->delete();

        foreach ($payload['results'] as $row) {
            $pesquisa_preco->results()->create([
                'source'               => $row['source'],
                'external_id'          => $row['external_id'] ?? null,
                'original_description' => mb_substr((string)($row['original_description'] ?? ''), 0, 4000),
                'unit_price'           => $row['unit_price'] ?? 0,
                'quantity'             => $row['quantity'] ?? null,
                'unit'                 => $row['unit'] ?? null,
                'total_price'          => $row['total_price'] ?? null,
                'buyer_name'           => $row['buyer_name'] ?? null,
                'buyer_cnpj'           => $row['buyer_cnpj'] ?? null,
                'city'                 => $row['city'] ?? null,
                'state'                => $row['state'] ?? null,
                'process_number'       => $row['process_number'] ?? null,
                'contract_number'      => $row['contract_number'] ?? null,
                'bid_number'           => $row['bid_number'] ?? null,
                'ata_number'           => $row['ata_number'] ?? null,
                'purchase_date'        => $row['purchase_date'] ?? null,
                'source_url'           => $row['source_url'] ?? null,
                'similarity_score'     => $row['similarity_score'] ?? null,
                'raw_payload'          => $row['raw_payload'] ?? null,
            ]);
        }

        $stats = $this->aggregator->statistics($pesquisa_preco->results()->get());
        $pesquisa_preco->update([
            'min_price'     => $stats['min'],
            'max_price'     => $stats['max'],
            'average_price' => $stats['avg'],
            'median_price'  => $stats['median'],
            'searched_at'   => now(),
            'status'        => $stats['count'] > 0 ? 'COM_RESULTADOS' : 'SEM_RESULTADOS',
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'SEARCH',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
            'dados'       => json_encode(['sources' => $sources, 'count' => $stats['count']]),
            'ip'          => $req->ip(),
        ]);

        $msgs = [];
        if (!empty($payload['errors'])) {
            foreach ($payload['errors'] as $src => $err) {
                $msgs[] = "$src: $err";
            }
        }

        $flash = $stats['count'] > 0
            ? "Busca concluída. {$stats['count']} resultados encontrados."
            : 'Nenhum resultado retornado pelas fontes selecionadas.';
        if ($msgs) $flash .= ' Avisos: ' . implode(' | ', $msgs);

        return redirect()->route('pesquisa-precos.show', $pesquisa_preco)->with('success', $flash);
    }

    /* ─────────── Seleção de resultados ─────────── */

    public function selectResult(Request $req, PriceResearch $pesquisa_preco, PriceResearchResult $resultado)
    {
        $this->ensureAccess($pesquisa_preco);
        if ($resultado->price_research_id !== $pesquisa_preco->id) abort(404);

        $data = $req->validate([
            'selected'                => 'required|boolean',
            'selection_justification' => 'nullable|string',
        ]);

        $resultado->update([
            'selected'                => $data['selected'],
            'selection_justification' => $data['selection_justification'] ?? $resultado->selection_justification,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => $data['selected'] ? 'SELECT_PRICE' : 'UNSELECT_PRICE',
            'entidade'    => 'PriceResearchResult',
            'entidade_id' => $resultado->id,
            'ip'          => $req->ip(),
        ]);

        if ($pesquisa_preco->results()->where('selected', true)->exists() && $pesquisa_preco->status === 'COM_RESULTADOS') {
            $pesquisa_preco->update(['status' => 'SELECIONADA']);
        }

        return back()->with('success', $data['selected'] ? 'Resultado selecionado para a cotação.' : 'Seleção removida.');
    }

    /* ─────────── Importação manual ─────────── */

    public function storeManualResult(Request $req, PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);

        $this->normalizeBrl($req, ['unit_price', 'quantity', 'total_price']);
        $data = $req->validate([
            'source'               => 'required|in:PNCP,RADAR_TCE_MT,MANUAL',
            'original_description' => 'required|string',
            'unit_price'           => 'required|numeric|min:0',
            'quantity'             => 'nullable|numeric|min:0',
            'unit'                 => 'nullable|string|max:60',
            'total_price'          => 'nullable|numeric|min:0',
            'buyer_name'           => 'nullable|string|max:255',
            'buyer_cnpj'           => 'nullable|string|max:20',
            'city'                 => 'nullable|string|max:120',
            'state'                => 'nullable|string|size:2',
            'process_number'       => 'nullable|string|max:60',
            'contract_number'      => 'nullable|string|max:60',
            'bid_number'           => 'nullable|string|max:60',
            'ata_number'           => 'nullable|string|max:60',
            'purchase_date'        => 'nullable|date',
            'source_url'           => 'nullable|url|max:1000',
            'selection_justification' => 'nullable|string',
        ]);

        $pesquisa_preco->results()->create($data + ['selected' => false]);

        // Recalcula estatísticas considerando o novo dado
        $stats = $this->aggregator->statistics($pesquisa_preco->results()->get());
        $pesquisa_preco->update([
            'min_price'     => $stats['min'],
            'max_price'     => $stats['max'],
            'average_price' => $stats['avg'],
            'median_price'  => $stats['median'],
            'status'        => $pesquisa_preco->status === 'RASCUNHO' ? 'COM_RESULTADOS' : $pesquisa_preco->status,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'IMPORT_MANUAL',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
            'ip'          => $req->ip(),
        ]);

        return back()->with('success', 'Resultado adicionado manualmente à pesquisa.');
    }

    public function destroyResult(PriceResearch $pesquisa_preco, PriceResearchResult $resultado)
    {
        $this->ensureAccess($pesquisa_preco);
        if ($resultado->price_research_id !== $pesquisa_preco->id) abort(404);

        $resultado->delete();

        $stats = $this->aggregator->statistics($pesquisa_preco->results()->get());
        $pesquisa_preco->update([
            'min_price'     => $stats['min'],
            'max_price'     => $stats['max'],
            'average_price' => $stats['avg'],
            'median_price'  => $stats['median'],
        ]);

        return back()->with('success', 'Resultado removido.');
    }

    /* ─────────── Definir preço de referência ─────────── */

    public function setReference(Request $req, PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);

        $this->normalizeBrl($req, ['selected_reference_price']);
        $data = $req->validate([
            'reference_type'           => 'required|in:MENOR,MAIOR,MEDIA,MEDIANA,MANUAL,ITEM',
            'selected_reference_price' => 'nullable|numeric|min:0',
            'reference_result_id'      => 'nullable|exists:price_research_results,id',
            'justification'            => 'required|string|min:10',
        ]);

        $value = match($data['reference_type']) {
            'MENOR'   => $pesquisa_preco->min_price,
            'MAIOR'   => $pesquisa_preco->max_price,
            'MEDIA'   => $pesquisa_preco->average_price,
            'MEDIANA' => $pesquisa_preco->median_price,
            'MANUAL'  => $data['selected_reference_price'] ?? null,
            'ITEM'    => optional(PriceResearchResult::find($data['reference_result_id'] ?? 0))->unit_price,
        };

        $pesquisa_preco->update([
            'reference_type'           => $data['reference_type'],
            'selected_reference_price' => $value,
            'justification'            => $data['justification'],
            'status'                   => $pesquisa_preco->status === 'CANCELADA' ? $pesquisa_preco->status : 'FINALIZADA',
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'SET_REFERENCE',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
            'dados'       => json_encode(['type' => $data['reference_type'], 'value' => $value]),
            'ip'          => $req->ip(),
        ]);

        return back()->with('success', 'Preço de referência definido.');
    }

    /* ─────────── PDF ─────────── */

    public function exportPdf(PriceResearch $pesquisa_preco)
    {
        $this->ensureAccess($pesquisa_preco);
        $pesquisa_preco->load(['project.institution','institution','user','results']);

        $stats = $this->aggregator->statistics($pesquisa_preco->results);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'acao'        => 'EXPORT_PDF',
            'entidade'    => 'PriceResearch',
            'entidade_id' => $pesquisa_preco->id,
        ]);

        $pdf = Pdf::loadView('pdf.price-research', [
            'pesquisa' => $pesquisa_preco,
            'stats'    => $stats,
            'results'  => $pesquisa_preco->results->sortBy('unit_price'),
        ])->setPaper('a4');

        $filename = 'pesquisa-precos-' . $pesquisa_preco->id . '.pdf';
        return $pdf->download($filename);
    }

    /* ─────────── Chat IA ─────────── */

    public function chat(Request $req)
    {
        $user = Auth::user();
        $institutions = $user->isAdmin()
            ? Institution::where('active', true)->orderBy('razao_social')->get()
            : Institution::where('id', $user->institution_id)->get();

        $projectsByInst = [];
        foreach ($institutions as $inst) {
            $projectsByInst[$inst->id] = Project::where('institution_id', $inst->id)
                ->orderBy('nome')->get(['id', 'nome'])->toArray();
        }

        return view('price-research.chat', [
            'institutions'   => $institutions,
            'projectsByInst' => $projectsByInst,
        ]);
    }

    /* ─────────── Filtros locais sobre a coleção ─────────── */

    private function applyFilters($collection, Request $req)
    {
        $coll = collect($collection);

        if ($f = $req->filter_source) {
            $coll = $coll->where('source', $f);
        }
        if ($f = $req->filter_state) {
            $coll = $coll->where('state', strtoupper($f));
        }
        if ($f = $req->filter_city) {
            $coll = $coll->filter(fn($r) => stripos($r->city ?? '', $f) !== false);
        }
        if ($f = $req->filter_text) {
            $coll = $coll->filter(fn($r) => stripos($r->original_description ?? '', $f) !== false);
        }

        $sort = $req->sort ?? 'unit_price_asc';
        return match($sort) {
            'unit_price_desc' => $coll->sortByDesc('unit_price')->values(),
            'date_desc'       => $coll->sortByDesc(fn($r) => $r->purchase_date?->timestamp ?? 0)->values(),
            'similarity_desc' => $coll->sortByDesc('similarity_score')->values(),
            default           => $coll->sortBy('unit_price')->values(),
        };
    }
}
