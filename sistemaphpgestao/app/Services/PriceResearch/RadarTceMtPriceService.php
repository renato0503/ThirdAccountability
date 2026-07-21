<?php
namespace App\Services\PriceResearch;

/**
 * Integração com o Radar de Preços do TCE-MT.
 *
 * O painel oficial em https://radardeprecos.tce.mt.gov.br/panel é uma SPA
 * protegida por requisições internas que não estão publicadas como API
 * pública estável e podem exigir autenticação ou validações que não temos
 * autorização para contornar.
 *
 * Compliance:
 *  - Não burlar captcha, login ou bloqueios.
 *  - Não fazer scraping agressivo.
 *
 * Por isso esta integração é entregue em modo "consulta assistida":
 *  - O service expõe a URL pública para abrir a pesquisa no Radar em nova aba;
 *  - O usuário traz os dados consultados pelo painel via importação manual
 *    (formulário ou CSV), com fonte = RADAR_TCE_MT.
 *
 * Quando/Se o TCE-MT publicar uma API REST estável, basta implementar
 * o método search() abaixo análogo ao PncpPriceService.
 */
class RadarTceMtPriceService
{
    public const SOURCE = 'RADAR_TCE_MT';

    public function isAvailable(): bool
    {
        return false; // sem API pública estável; manter manual por padrão
    }

    public function publicSearchUrl(string $term): string
    {
        return 'https://radardeprecos.tce.mt.gov.br/panel?q=' . urlencode($term);
    }

    /**
     * Reservado para integração futura. Hoje retorna lista vazia.
     */
    public function search(string $term, array $filters = []): array
    {
        return [
            'results' => [],
            'error'   => null,
            'meta'    => [
                'manual'      => true,
                'public_url'  => $this->publicSearchUrl($term),
                'instrucao'   => 'A integração automática com o Radar TCE-MT não está disponível. ' .
                                 'Use o link acima para consultar manualmente e importe os preços ' .
                                 'na tela de detalhes desta pesquisa.',
            ],
        ];
    }
}
