<?php
namespace App\Services\PriceResearch;

class PriceResearchAggregator
{
    public function __construct(
        private PncpPriceService $pncp,
        private RadarTceMtPriceService $radar,
    ) {}

    /**
     * Executa a busca nas fontes selecionadas e retorna resultados unificados.
     *
     * @param array<int, string> $sources  Lista com 'PNCP' e/ou 'RADAR_TCE_MT'.
     */
    public function search(string $term, array $sources, array $filters = []): array
    {
        $sources = array_values(array_unique(array_filter($sources)));
        if (empty($sources)) {
            $sources = ['PNCP'];
        }

        $all      = [];
        $errors   = [];
        $metaBySrc = [];

        if (in_array('PNCP', $sources, true)) {
            $r = $this->pncp->search($term, $filters);
            $all = array_merge($all, $r['results']);
            $metaBySrc['PNCP'] = $r['meta'];
            if (!empty($r['error'])) $errors['PNCP'] = $r['error'];
        }

        if (in_array('RADAR_TCE_MT', $sources, true)) {
            $r = $this->radar->search($term, $filters);
            $all = array_merge($all, $r['results']);
            $metaBySrc['RADAR_TCE_MT'] = $r['meta'];
            if (!empty($r['error'])) $errors['RADAR_TCE_MT'] = $r['error'];
        }

        return [
            'results'    => $all,
            'statistics' => $this->statistics($all),
            'errors'     => $errors,
            'meta'       => $metaBySrc,
            'sources'    => $sources,
        ];
    }

    /**
     * Calcula estatísticas de uma coleção de preços (arrays normalizados ou models).
     */
    public function statistics(iterable $items): array
    {
        $prices = [];
        foreach ($items as $item) {
            $p = is_array($item) ? ($item['unit_price'] ?? null) : ($item->unit_price ?? null);
            if ($p !== null && is_numeric($p) && (float)$p > 0) $prices[] = (float)$p;
        }

        $count = count($prices);
        if ($count === 0) {
            return ['count' => 0, 'min' => null, 'max' => null, 'avg' => null, 'median' => null];
        }

        sort($prices);
        $min = $prices[0];
        $max = $prices[$count - 1];
        $avg = array_sum($prices) / $count;
        $median = $count % 2 === 1
            ? $prices[(int) floor($count / 2)]
            : ($prices[$count / 2 - 1] + $prices[$count / 2]) / 2;

        return [
            'count'  => $count,
            'min'    => round($min, 4),
            'max'    => round($max, 4),
            'avg'    => round($avg, 4),
            'median' => round($median, 4),
        ];
    }
}
