<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqClient
{
    public function __construct(
        private string $apiKey = '',
        private string $model = 'llama-3.3-70b-versatile',
    ) {
        $this->apiKey = $apiKey ?: (string) env('GROQ_API_KEY', '');
        $this->model  = $model ?: (string) env('GROQ_MODEL', 'llama-3.3-70b-versatile');
    }

    public function isAvailable(): bool
    {
        return $this->apiKey !== '';
    }

    public function interpretBatch(string $text): array
    {
        if ($text === '' || !$this->isAvailable()) {
            return [];
        }

        $prompt = <<<PROMPT
Você é um assistente que interpreta pedidos de cotação de preços em português.
O usuário enviou um texto livre. Extraia a lista de itens a cotar, normalizando as descrições.

Texto do usuário:
"""
{$text}
"""

Responda EXCLUSIVAMENTE com um JSON válido (sem markdown), no formato:
{"itens": [{"descricao": "BOLA DE FUTEBOL MAX 200", "quantidade": 1}, ...]}

Regras:
- Se o usuário mencionar uma quantidade explícita para um item, use-a; caso contrário 1.
- Normalize as descrições em CAIXA ALTA, sem marcas irrelevantes.
- Se houver materiais ou especificações, inclua no campo "material" (ex: "material": "PU").
- Não invente itens.
- Se o texto for ambíguo, faça o melhor esforço.
PROMPT;

        return $this->chat($prompt, 0.1, 800)['itens'] ?? [];
    }

    public function suggestProductDetails(string $partial): array
    {
        if ($partial === '' || !$this->isAvailable()) {
            return ['descricao' => $partial, 'material' => '', 'categoria' => ''];
        }

        $prompt = <<<PROMPT
Você é um assistente especializado em catalogação de produtos para licitações públicas brasileiras.
O usuário digitou parcialmente o nome de um produto. Complete e padronize.

Produto parcial: "{$partial}"

Responda EXCLUSIVAMENTE com um JSON válido (sem markdown), com os campos:
- "descricao": descrição completa e padronizada do produto
- "material": tipo de material principal (ex: "POLIURETANO", "AÇO INOX", "PAPEL A4")
- "categoria": código CATMAT ou CATSER provável, ou string vazia se desconhecido

Se a entrada for ambígua, faça a melhor interpretação plausível. Não invente códigos CATMAT que não conheça.
PROMPT;

        $result = $this->chat($prompt, 0.2, 300);
        return [
            'descricao' => $result['descricao'] ?? $partial,
            'material'  => $result['material'] ?? '',
            'categoria' => $result['categoria'] ?? '',
        ];
    }

    private function chat(string $prompt, float $temperature, int $maxTokens): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Você é um assistente que responde apenas em JSON válido.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                ]);

            if (!$response->successful()) {
                Log::warning('Groq API falhou', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? '';
            if (preg_match('/\{.*\}/s', $content, $match)) {
                return json_decode($match[0], true) ?: [];
            }
            return [];
        } catch (\Throwable $e) {
            Log::error('Groq exceção', ['msg' => $e->getMessage()]);
            return [];
        }
    }
}
