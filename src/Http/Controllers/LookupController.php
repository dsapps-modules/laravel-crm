<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Services\BrazilLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

class LookupController extends Controller
{
    public function cnpj(string $document, BrazilLookupService $lookups): JsonResponse
    {
        return $this->respond(fn () => $lookups->cnpj($document), 'CNPJ não encontrado.');
    }

    public function postalCode(string $postalCode, BrazilLookupService $lookups): JsonResponse
    {
        return $this->respond(fn () => $lookups->postalCode($postalCode), 'CEP não encontrado.');
    }

    private function respond(callable $lookup, string $notFound): JsonResponse
    {
        try {
            $data = $lookup();
            if ($data === null) return response()->json(['message' => $notFound], 404);
            return response()->json(['data' => $data]);
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);
            abort(502, 'Não foi possível consultar o serviço externo.');
        }
    }
}
