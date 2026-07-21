<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CepService
{
    public function consultar(string $cep): array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return ['erro' => 'CEP inválido.'];
        }

        return Cache::remember("cep_{$cep}", now()->addDays(7), function () use ($cep) {
            try {
                $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['erro'])) {
                        return ['erro' => 'CEP não encontrado.'];
                    }

                    return [
                        'logradouro'  => $data['logradouro'] ?? null,
                        'bairro'      => $data['bairro'] ?? null,
                        'municipio'   => $data['localidade'] ?? null,
                        'estado'      => $data['uf'] ?? null,
                        'ibge'        => $data['ibge'] ?? null,
                    ];
                }

                return ['erro' => 'CEP não encontrado.'];
            } catch (\Exception $e) {
                return ['erro' => 'Serviço de CEP indisponível.'];
            }
        });
    }
}
