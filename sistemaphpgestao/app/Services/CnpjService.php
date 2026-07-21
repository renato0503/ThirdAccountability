<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CnpjService
{
    public function consultar(string $cnpj): array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return ['erro' => 'CNPJ inválido.'];
        }

        return Cache::remember("cnpj_{$cnpj}", now()->addHours(24), function () use ($cnpj) {
            try {
                $response = Http::timeout(8)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'razao_social'   => $data['razao_social'] ?? null,
                        'nome_fantasia'  => $data['nome_fantasia'] ?? null,
                        'email'          => $data['email'] ?? null,
                        'telefone'       => $data['ddd_telefone_1'] ?? null,
                        'logradouro'     => $data['logradouro'] ?? null,
                        'numero'         => $data['numero'] ?? null,
                        'complemento'    => $data['complemento'] ?? null,
                        'bairro'         => $data['bairro'] ?? null,
                        'municipio'      => $data['municipio'] ?? null,
                        'estado'         => $data['uf'] ?? null,
                        'cep'            => $data['cep'] ?? null,
                        'situacao'       => $data['descricao_situacao_cadastral'] ?? null,
                        'porte'          => $data['porte'] ?? null,
                        'natureza'       => $data['natureza_juridica'] ?? null,
                    ];
                }

                return ['erro' => 'CNPJ não encontrado na Receita Federal.'];
            } catch (\Exception $e) {
                return ['erro' => 'Serviço de consulta indisponível.'];
            }
        });
    }
}
