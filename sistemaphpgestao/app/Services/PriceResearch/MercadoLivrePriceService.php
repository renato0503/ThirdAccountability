<?php
namespace App\Services\PriceResearch;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoLivrePriceService
{
    public const SOURCE = 'MERCADO';

    private array $headers;

    public function __construct()
    {
        $this->headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ];
    }

    public function search(string $term, int $limit = 10): array
    {
        if ($term === '') return [];

        $results = [];
        $perSource = max(3, (int) ceil($limit / 3) + 1);

        $mlResults = $this->searchMercadoLivre($term, $perSource);
        $results = array_merge($results, $mlResults);

        $zoomResults = $this->searchZoom($term, $perSource);
        $results = array_merge($results, $zoomResults);

        $buscapeResults = $this->searchBuscape($term, $perSource);
        $results = array_merge($results, $buscapeResults);

        return array_slice($results, 0, $limit);
    }

    public function statistics(array $results): array
    {
        $prices = array_filter(array_column($results, 'unit_price'), fn($v) => $v !== null && $v > 0);
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
            'min'    => round($min, 2),
            'max'    => round($max, 2),
            'avg'    => round($avg, 2),
            'median' => round($median, 2),
        ];
    }

    private function searchMercadoLivre(string $term, int $limit): array
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders($this->headers)
                ->get('https://api.mercadolibre.com/sites/MLB/search', [
                    'q'     => $term,
                    'limit' => $limit,
                    'sort'  => 'relevance',
                ]);

            if (!$response->successful()) return [];

            $data = $response->json();
            $results = [];
            foreach ($data['results'] ?? [] as $item) {
                $price = $this->safeFloat($item['price'] ?? null);
                if ($price === null) continue;

                $results[] = $this->normalize(
                    title:    $item['title'] ?? '',
                    price:    $price,
                    source:   'Mercado Livre',
                    seller:   $item['seller']['nickname'] ?? null,
                    url:      $item['permalink'] ?? null,
                    shipping: $item['shipping']['free_shipping'] ?? false,
                );
            }
            return $results;
        } catch (\Throwable $e) {
            Log::warning('Mercado Livre API falhou', ['msg' => $e->getMessage()]);
            return [];
        }
    }

    private function searchZoom(string $term, int $limit): array
    {
        return $this->scrapeMosaico($term, $limit, 'https://www.zoom.com.br/search?q={q}', 'Zoom');
    }

    private function searchBuscape(string $term, int $limit): array
    {
        return $this->scrapeMosaico($term, $limit, 'https://www.buscape.com.br/search?q={q}', 'Buscapé');
    }

    private function scrapeMosaico(string $term, int $limit, string $baseUrl, string $sourceName): array
    {
        try {
            $url = str_replace('{q}', urlencode($term), $baseUrl);
            $response = Http::timeout(12)
                ->withHeaders($this->headers)
                ->get($url);

            if (!$response->successful()) return [];

            $html = $response->body();
            $results = [];
            $seen = [];

            // Pattern: <a href="..."><h2>title</h2>...R$ X.XXX,XX...
            if (preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/s', $html, $anchors, PREG_SET_ORDER)) {
                foreach ($anchors as $anchor) {
                    $href = $anchor[1];
                    $content = $anchor[2];

                    if (!preg_match('/<h2[^>]*>(.*?)<\/h2>/s', $content, $h2)) continue;
                    $title = trim(strip_tags(html_entity_decode($h2[1])));
                    if (strlen($title) < 5 || strlen($title) > 200) continue;

                    if (!preg_match('/R\$\s*([\d.]+),(\d{2})/', $content, $priceM)) continue;
                    $price = (float) str_replace('.', '', $priceM[1]) . '.' . $priceM[2];

                    $fullUrl = $href;
                    if (!str_starts_with($href, 'http')) {
                        $domain = stripos($sourceName, 'zoom') !== false ? 'zoom.com.br' : 'buscape.com.br';
                        $fullUrl = 'https://www.' . $domain . $href;
                    }
                    $fullUrl = str_replace('&amp;', '&', $fullUrl);

                    $key = md5($title . $price);
                    if (isset($seen[$key])) continue;
                    $seen[$key] = true;

                    $results[] = $this->normalize($title, $price, $sourceName, null, $fullUrl);

                    if (count($results) >= $limit) break;
                }
            }

            return $results;
        } catch (\Throwable $e) {
            Log::warning("$sourceName scraping falhou", ['msg' => $e->getMessage()]);
            return [];
        }
    }

    private function normalize(string $title, float $price, string $source, ?string $seller, ?string $url, bool $shipping = false): array
    {
        return [
            'source'               => self::SOURCE,
            'original_description' => $title,
            'unit_price'           => $price,
            'quantity'             => 1,
            'unit'                 => 'unidade',
            'total_price'          => $price,
            'buyer_name'           => $seller,
            'buyer_cnpj'           => null,
            'city'                 => null,
            'state'                => null,
            'source_url'           => $url,
            'similarity_score'     => null,
            'raw_payload'          => [
                'source'        => $source,
                'shipping_free' => $shipping,
            ],
        ];
    }

    private function safeFloat($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float) $v;
        return null;
    }
}
