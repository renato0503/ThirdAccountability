<?php
namespace App\Http\Controllers;

use App\Services\CnpjService;
use App\Services\CepService;
use Illuminate\Http\JsonResponse;

class IntegrationController extends Controller
{
    public function cnpj(string $cnpj, CnpjService $service): JsonResponse
    {
        return response()->json($service->consultar($cnpj));
    }

    public function cep(string $cep, CepService $service): JsonResponse
    {
        return response()->json($service->consultar($cep));
    }
}
