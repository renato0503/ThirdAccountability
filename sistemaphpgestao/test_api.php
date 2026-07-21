<?php
/**
 * Teste headless do módulo Pesquisa de Preços.
 *
 * Uso:
 *   1) Via browser:  https://project.byrees.com/sistemaphpgestao/test_api.php?term=papel+a4
 *   2) Via CLI:      php test_api.php "papel a4"
 *
 * Saída em texto puro (sem layout). Cada bloco é independente — se um falhar,
 * os demais continuam.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

$term = $_GET['term'] ?? ($argv[1] ?? 'papel a4');

$out = function (string $label, $data) {
    echo "── {$label} ────────────────────────────────────────\n";
    if (is_string($data)) { echo $data . "\n\n"; return; }
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
};

echo "=== TESTE PESQUISA DE PREÇOS ===\nTermo: {$term}\nData: " . date('c') . "\n\n";

/* ────────────────────────── 1) Bootstrap Laravel ────────────────────────── */
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    $out('1) Laravel bootstrap', 'OK — env=' . $app->environment() . ' | debug=' . (config('app.debug') ? 'true' : 'false'));
} catch (\Throwable $e) {
    $out('1) Laravel bootstrap', 'FALHOU: ' . $e->getMessage());
    exit(1);
}

/* ────────────────────────── 2) Conexão com banco ────────────────────────── */
try {
    \DB::connection()->getPdo();
    $tables = ['price_researches', 'price_research_results', 'projects', 'institutions', 'users'];
    $info = [];
    foreach ($tables as $t) {
        $info[$t] = \Schema::hasTable($t)
            ? ('existe — rows=' . \DB::table($t)->count())
            : 'NÃO EXISTE';
    }
    $out('2) Banco e tabelas', $info);
} catch (\Throwable $e) {
    $out('2) Banco e tabelas', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 3) Model PriceResearch ────────────────────────── */
try {
    $m = new \App\Models\PriceResearch();
    $out('3) Model PriceResearch', [
        'tabela_resolvida' => $m->getTable(),
        'esperado'         => 'price_researches',
        'match'            => $m->getTable() === 'price_researches' ? 'OK' : 'DIVERGENTE',
    ]);
} catch (\Throwable $e) {
    $out('3) Model PriceResearch', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 4) PNCP — chamada direta HTTP ────────────────────────── */
try {
    $url = rtrim(env('PNCP_SEARCH_URL', 'https://pncp.gov.br/api/search/'), '/');
    $params = http_build_query([
        'q' => $term, 'tipos_documento' => 'ata', 'ordenacao' => '-data',
        'pagina' => 1, 'tam_pagina' => 3, 'status' => 'todos',
    ]);
    $ch = curl_init($url . '?' . $params);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $json = json_decode($body, true);
    $hits = $json['items'] ?? $json['results'] ?? $json['hits'] ?? [];
    $out('4) PNCP HTTP direto', [
        'url'         => $url . '?' . $params,
        'http_status' => $code,
        'curl_error'  => $err ?: null,
        'total_hits'  => is_array($hits) ? count($hits) : 'resposta sem array',
        'sample_keys' => is_array($hits) && $hits ? array_keys($hits[0]) : null,
    ]);
} catch (\Throwable $e) {
    $out('4) PNCP HTTP direto', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 5) PncpPriceService (integração interna) ────────────────────────── */
try {
    $svc = app(\App\Services\PriceResearch\PncpPriceService::class);
    $r = $svc->search($term, ['tam_pagina' => 3]);
    $out('5) PncpPriceService::search()', [
        'isAvailable'      => $svc->isAvailable(),
        'baseSearchUrl'    => $svc->baseSearchUrl(),
        'error'            => $r['error'],
        'meta'             => $r['meta'],
        'qtd_normalizados' => count($r['results']),
        'primeiro'         => $r['results'][0] ?? null,
    ]);
} catch (\Throwable $e) {
    $out('5) PncpPriceService::search()', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 6) Agregador ────────────────────────── */
try {
    $agg = app(\App\Services\PriceResearch\PriceResearchAggregator::class);
    $r = $agg->search($term, ['PNCP'], ['tam_pagina' => 3]);
    $out('6) PriceResearchAggregator::search()', [
        'errors'      => $r['errors'] ?? null,
        'qtd_results' => count($r['results']),
        'sources_run' => array_values(array_unique(array_map(fn($x) => $x['source'] ?? '?', $r['results']))),
    ]);
    if (!empty($r['results'])) {
        $stats = $agg->statistics(collect($r['results']));
        $out('6.1) Estatísticas', $stats);
    }
} catch (\Throwable $e) {
    $out('6) PriceResearchAggregator', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 7) RADAR TCE-MT (modo manual) ────────────────────────── */
try {
    $svc = app(\App\Services\PriceResearch\RadarTceMtPriceService::class);
    $out('7) RadarTceMtPriceService', [
        'isAvailable'    => $svc->isAvailable(),
        'publicSearchUrl' => $svc->publicSearchUrl($term),
        'comportamento'  => 'Esperado: isAvailable=false, retorna URL pública pra consulta manual.',
    ]);
} catch (\Throwable $e) {
    $out('7) RadarTceMtPriceService', 'FALHOU: ' . $e->getMessage());
}

/* ────────────────────────── 8) Rotas registradas ────────────────────────── */
try {
    $routes = collect(\Route::getRoutes())->filter(
        fn($r) => str_contains($r->uri(), 'pesquisa-precos') || str_contains($r->uri(), 'api/')
    )->map(fn($r) => implode('|', $r->methods()) . '  ' . $r->uri() . '  → ' . ($r->getName() ?: '(sem nome)'))->values()->all();
    $out('8) Rotas relacionadas', $routes);
} catch (\Throwable $e) {
    $out('8) Rotas', 'FALHOU: ' . $e->getMessage());
}

echo "=== FIM ===\n";
