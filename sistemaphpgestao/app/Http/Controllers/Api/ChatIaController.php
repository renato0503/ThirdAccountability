<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, PriceResearch, PriceResearchResult, Institution};
use App\Services\GroqClient;
use App\Services\PriceResearch\PncpPriceService;
use App\Services\PriceResearch\MercadoLivrePriceService;
use App\Services\PriceResearch\PriceResearchAggregator;
use App\Services\CnpjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatIaController extends Controller
{
    public function __construct(
        private GroqClient $groq,
        private PncpPriceService $pncp,
        private MercadoLivrePriceService $mercado,
        private PriceResearchAggregator $aggregator,
        private CnpjService $cnpj,
    ) {}

    public function processar(Request $req)
    {
        $req->validate(['texto' => 'required|string|min:3|max:5000']);

        $user = Auth::user();
        $texto = $req->input('texto');
        $institutionId = $user->isAdmin()
            ? $req->integer('institution_id') ?: null
            : $user->institution_id;

        if (!$institutionId) {
            return response()->json(['error' => 'Selecione uma instituição.'], 422);
        }

        if (!$this->groq->isAvailable()) {
            return response()->json(['error' => 'GROQ_API_KEY não configurada.'], 503);
        }

        // 1. Interpretar texto com IA
        $itens = $this->groq->interpretBatch($texto);
        if (empty($itens)) {
            return response()->json(['error' => 'Não foi possível interpretar o texto. Tente ser mais específico.'], 422);
        }

        if (count($itens) > 20) {
            return response()->json(['error' => 'Máximo de 20 itens por requisição.'], 422);
        }

        // 2. Criar pesquisa de preços para cada item
        $pesquisas = [];
        $errors = [];

        foreach ($itens as $item) {
            $descricao = $item['descricao'] ?? '';
            $quantidade = (float) ($item['quantidade'] ?? 1);
            $material = $item['material'] ?? '';

            if ($descricao === '') continue;

            try {
                $pesquisa = PriceResearch::create([
                    'institution_id' => $institutionId,
                    'project_id'     => $req->integer('project_id') ?: null,
                    'user_id'        => $user->id,
                    'search_term'    => $descricao,
                    'quantity'       => $quantidade,
                    'category'       => $material ?: null,
                    'unit'           => 'unidade',
                    'sources'        => ['PNCP', 'MERCADO'],
                    'status'         => 'BUSCADA',
                ]);

                // 3. Buscar PNCP + Mercado em paralelo
                $filters = [
                    'state'      => $req->input('state'),
                    'city'       => $req->input('city'),
                    'tam_pagina' => 20,
                ];

                $pncpResults = $this->pncp->search($descricao, $filters);
                $mercadoResults = $this->mercado->search($descricao, 6);

                $allResults = array_merge($pncpResults['results'], $mercadoResults);
                $pesquisa->results()->whereIn('source', ['PNCP', 'MERCADO'])->delete();

                foreach ($allResults as $row) {
                    $pesquisa->results()->create([
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
                        'source_url'           => $row['source_url'] ?? null,
                        'similarity_score'     => $row['similarity_score'] ?? null,
                        'raw_payload'          => $row['raw_payload'] ?? null,
                    ]);
                }

                $stats = $this->aggregator->statistics($pesquisa->results()->get());
                $pesquisa->update([
                    'min_price'     => $stats['min'],
                    'max_price'     => $stats['max'],
                    'average_price' => $stats['avg'],
                    'median_price'  => $stats['median'],
                    'searched_at'   => now(),
                    'status'        => $stats['count'] > 0 ? 'COM_RESULTADOS' : 'SEM_RESULTADOS',
                ]);

                $pesquisas[] = $pesquisa->load('results');

                if (!empty($pncpResults['error'])) {
                    $errors[$descricao]['pncp'] = $pncpResults['error'];
                }
            } catch (\Throwable $e) {
                $errors[$descricao] = $e->getMessage();
            }
        }

        // 4. AuditLog
        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => 'CHAT_IA_PROCESSAR',
            'entidade'    => 'PriceResearch',
            'entidade_id' => null,
            'dados'       => json_encode([
                'texto_original' => $texto,
                'itens_extraidos' => $itens,
                'pesquisas_criadas' => count($pesquisas),
                'errors' => $errors ?: null,
            ]),
            'ip'          => $req->ip(),
        ]);

        return response()->json([
            'pesquisas' => $pesquisas->map->toArray(),
            'estatisticas' => [
                'total_itens' => count($itens),
                'processados' => count($pesquisas),
                'com_resultados' => collect($pesquisas)->filter(fn($p) => $p->status === 'COM_RESULTADOS')->count(),
                'sem_resultados' => collect($pesquisas)->filter(fn($p) => $p->status === 'SEM_RESULTADOS')->count(),
            ],
            'errors' => $errors ?: null,
        ]);
    }

    public function selecionar(Request $req)
    {
        $req->validate([
            'resultado_id' => 'required|exists:price_research_results,id',
            'selected'     => 'required|boolean',
        ]);

        $resultado = PriceResearchResult::with('priceResearch')->findOrFail($req->integer('resultado_id'));
        $pesquisa = $resultado->priceResearch;

        $user = Auth::user();
        if (!$user->isAdmin() && $pesquisa->institution_id !== $user->institution_id) {
            return response()->json(['error' => 'Sem permissão.'], 403);
        }

        $resultado->update([
            'selected' => $req->boolean('selected'),
            'selection_justification' => $req->input('justificativa'),
        ]);

        if ($pesquisa->results()->where('selected', true)->exists() && $pesquisa->status === 'COM_RESULTADOS') {
            $pesquisa->update(['status' => 'SELECIONADA']);
        }

        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => $req->boolean('selected') ? 'CHAT_IA_SELECIONAR' : 'CHAT_IA_DESELECIONAR',
            'entidade'    => 'PriceResearchResult',
            'entidade_id' => $resultado->id,
            'ip'          => $req->ip(),
        ]);

        return response()->json(['success' => true, 'resultado' => $resultado->fresh()]);
    }

    public function orcamentoManual(Request $req)
    {
        $req->validate([
            'pesquisa_id' => 'required|exists:price_researches,id',
            'cnpj'        => 'required|string|max:20',
            'descricao'   => 'required|string|max:500',
            'valor'       => 'required|numeric|min:0.01',
            'observacoes' => 'nullable|string|max:1000',
            'anexo'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $pesquisa = PriceResearch::findOrFail($req->integer('pesquisa_id'));
        $user = Auth::user();

        if (!$user->isAdmin() && $pesquisa->institution_id !== $user->institution_id) {
            return response()->json(['error' => 'Sem permissão.'], 403);
        }

        $anexoPath = null;
        if ($req->hasFile('anexo')) {
            $anexoPath = $req->file('anexo')->store('orcamentos-manuais', 'public');
        }

        $cnpj = preg_replace('/\D/', '', (string) $req->input('cnpj'));

        $resultado = $pesquisa->results()->create([
            'source'               => 'MANUAL',
            'original_description' => $req->input('descricao'),
            'unit_price'           => $req->float('valor'),
            'quantity'             => 1,
            'unit'                 => 'unidade',
            'total_price'          => $req->float('valor'),
            'buyer_cnpj'           => $cnpj,
            'selected'             => false,
            'anexo_path'           => $anexoPath,
            'observacoes'          => $req->input('observacoes'),
        ]);

        $stats = $this->aggregator->statistics($pesquisa->results()->get());
        $pesquisa->update([
            'min_price'     => $stats['min'],
            'max_price'     => $stats['max'],
            'average_price' => $stats['avg'],
            'median_price'  => $stats['median'],
            'status'        => $pesquisa->status === 'RASCUNHO' ? 'COM_RESULTADOS' : $pesquisa->status,
        ]);

        AuditLog::create([
            'user_id'     => $user->id,
            'acao'        => 'CHAT_IA_ORCAMENTO_MANUAL',
            'entidade'    => 'PriceResearchResult',
            'entidade_id' => $resultado->id,
            'dados'       => json_encode(['cnpj' => $cnpj, 'valor' => $req->float('valor')]),
            'ip'          => $req->ip(),
        ]);

        return response()->json(['success' => true, 'resultado' => $resultado->fresh()]);
    }

    public function status(int $pesquisaId)
    {
        $pesquisa = PriceResearch::with(['results', 'institution', 'project'])->findOrFail($pesquisaId);
        $user = Auth::user();

        if (!$user->isAdmin() && $pesquisa->institution_id !== $user->institution_id) {
            return response()->json(['error' => 'Sem permissão.'], 403);
        }

        $stats = $this->aggregator->statistics($pesquisa->results);

        return response()->json([
            'pesquisa' => $pesquisa->toArray(),
            'stats'    => $stats,
        ]);
    }
}
