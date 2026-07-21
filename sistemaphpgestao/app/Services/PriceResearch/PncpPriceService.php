<?php
namespace App\Services\PriceResearch;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integração com o PNCP — Portal Nacional de Contratações Públicas.
 *
 * Usa o endpoint público de busca usado pelo próprio portal pncp.gov.br.
 * Documentação dos endpoints abertos: https://pncp.gov.br/api/consulta/swagger-ui/index.html
 *
 * Estratégia:
 *  - Endpoint de busca (search) → retorna documentos por termo livre, com filtros
 *    por tipo (ata, contratação, contrato), data, UF e município.
 *  - Não há endpoint público padronizado para "preço unitário por item de ata"
 *    em busca livre — então mapeamos o documento agregador (ata/contrato) e
 *    apresentamos o "valor total" da ata/contrato como preço de referência.
 *  - O usuário sempre vê o link de origem para auditar a especificação.
 */
class PncpPriceService
{
    public const SOURCE = 'PNCP';

    public function isAvailable(): bool
    {
        return !empty($this->baseSearchUrl());
    }

    public function baseSearchUrl(): string
    {
        return rtrim(env('PNCP_SEARCH_URL', 'https://pncp.gov.br/api/search/'), '/');
    }

    public function publicAtasUrl(string $term): string
    {
        return 'https://pncp.gov.br/app/contratos?q=' . urlencode($term);
    }

    /**
     * @return array{results: array<int, array<string, mixed>>, error: ?string, meta: array<string, mixed>}
     */
    public function search(string $term, array $filters = []): array
    {
        $term = trim($term);
        if ($term === '') {
            return ['results' => [], 'error' => 'Termo de pesquisa vazio.', 'meta' => []];
        }

        $params = [
            'q'               => $term,
            'tipos_documento' => $filters['tipos_documento'] ?? 'contrato',
            'ordenacao'       => $filters['ordenacao'] ?? '-data',
            'pagina'          => (int)($filters['pagina'] ?? 1),
            'tam_pagina'      => (int)($filters['tam_pagina'] ?? 20),
            'status'          => $filters['status'] ?? 'todos',
        ];

        if (!empty($filters['state']))         $params['uf']           = $filters['state'];
        if (!empty($filters['city']))          $params['municipio']    = $filters['city'];
        if (!empty($filters['date_start']))    $params['data_inicial'] = $filters['date_start'];
        if (!empty($filters['date_end']))      $params['data_final']   = $filters['date_end'];

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($this->baseSearchUrl(), $params);

            if (!$response->successful()) {
                Log::warning('PNCP busca falhou', ['status' => $response->status(), 'params' => $params]);
                return [
                    'results' => [],
                    'error'   => 'PNCP retornou status HTTP ' . $response->status() . '.',
                    'meta'    => ['endpoint' => $this->baseSearchUrl(), 'params' => $params],
                ];
            }

            $json = $response->json();
            $hits = $json['items'] ?? $json['results'] ?? $json['hits'] ?? [];

            $results = [];
            foreach ($hits as $hit) {
                $results[] = $this->normalize($hit, $term);
            }

            return [
                'results' => $results,
                'error'   => null,
                'meta'    => [
                    'endpoint'   => $this->baseSearchUrl(),
                    'total'      => $json['total'] ?? count($results),
                    'pagina'     => $params['pagina'],
                    'tam_pagina' => $params['tam_pagina'],
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('PNCP exceção na busca', ['msg' => $e->getMessage()]);
            return [
                'results' => [],
                'error'   => 'Falha na conexão com o PNCP: ' . $e->getMessage(),
                'meta'    => ['endpoint' => $this->baseSearchUrl(), 'params' => $params],
            ];
        }
    }

    /**
     * Normaliza um hit do PNCP para o formato esperado pelo módulo.
     */
    private function normalize(array $hit, string $term): array
    {
        $description = $hit['description'] ?? $hit['descricao'] ?? $hit['objeto'] ?? $hit['title'] ?? '';
        $totalPrice  = $this->numeric($hit['valor_global'] ?? $hit['valorTotal'] ?? $hit['valor_total'] ?? null);
        $unitPrice   = $this->numeric($hit['valor_unitario'] ?? $hit['valor'] ?? null) ?? $totalPrice;
        $buyer       = $hit['orgao_nome'] ?? $hit['nome_orgao'] ?? $hit['orgao'] ?? $hit['unidade_orgao_nome'] ?? $hit['unidade_nome'] ?? null;
        $cnpj        = $hit['orgao_cnpj'] ?? $hit['cnpj'] ?? null;
        $city        = $hit['municipio_nome'] ?? $hit['municipio'] ?? null;
        $state       = $hit['uf'] ?? $hit['unidade_orgao_uf'] ?? null;
        $date        = $hit['data_assinatura'] ?? $hit['data_publicacao_pncp'] ?? $hit['data_publicacao'] ?? $hit['data'] ?? null;
        $itemPath    = $hit['item_url'] ?? null;
        $url         = $hit['url'] ?? null;

        if (!$url && $itemPath) {
            $url = 'https://pncp.gov.br/app' . $itemPath;
        }
        if (!$url && !empty($hit['numero_controle_pncp'])) {
            $url = 'https://pncp.gov.br/app/contratos/' . $hit['numero_controle_pncp'];
        }
        if (!$url && !empty($hit['numeroControlePNCP'])) {
            $url = 'https://pncp.gov.br/app/contratos/' . $hit['numeroControlePNCP'];
        }

        return [
            'source'               => self::SOURCE,
            'external_id'          => $hit['id'] ?? $hit['numero_controle_pncp'] ?? $hit['numeroControlePNCP'] ?? null,
            'original_description' => is_string($description) ? $description : json_encode($description),
            'unit_price'           => $unitPrice,
            'quantity'             => $this->numeric($hit['quantidade'] ?? null),
            'unit'                 => $hit['unidade_medida'] ?? $hit['unidade'] ?? null,
            'total_price'          => $totalPrice ?: $unitPrice,
            'buyer_name'           => $buyer,
            'buyer_cnpj'           => $cnpj,
            'city'                 => $city,
            'state'                => $state ? substr($state, 0, 2) : null,
            'process_number'       => $hit['numero_processo'] ?? null,
            'contract_number'      => $hit['numero_contrato'] ?? null,
            'bid_number'           => $hit['numero_edital'] ?? $hit['numero_licitacao'] ?? null,
            'ata_number'           => $hit['numero_ata'] ?? $hit['numero_documento'] ?? null,
            'purchase_date'        => $this->parseDate($date),
            'source_url'           => $url,
            'similarity_score'     => $this->similarity($term, is_string($description) ? $description : ''),
            'raw_payload'          => $hit,
        ];
    }

    private function numeric($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float)$v;
        $clean = preg_replace('/[^0-9,.\-]/', '', (string)$v);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float)$clean : null;
    }

    private function parseDate($v): ?string
    {
        if (!$v) return null;
        try {
            return \Carbon\Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function similarity(string $term, string $description): ?float
    {
        if ($description === '' || $term === '') return null;
        similar_text(mb_strtolower($term), mb_strtolower($description), $percent);
        return round($percent / 100, 4);
    }
}
