<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\CnpjLookupProvider;
use DsApps\LaravelCrm\Contracts\PostalCodeLookupProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class BrasilApiLookupProvider implements CnpjLookupProvider, PostalCodeLookupProvider
{
    public function find(string $value): ?array
    {
        return strlen($value) === 14 ? $this->cnpj($value) : $this->postalCode($value);
    }

    public function cnpj(string $document): ?array
    {
        $response = $this->request()->get(rtrim(config('crm.lookups.cnpj_base_url'), '/').'/'.$document);
        if ($response->status() === 404) return null;
        $response->throw();
        $data = $response->json();
        if (! is_array($data)) return null;

        return [
            'document_type' => 'cnpj', 'document' => BrazilianDocument::digits((string) ($data['cnpj'] ?? $document)),
            'name' => $data['nome_fantasia'] ?? $data['razao_social'] ?? null,
            'legal_name' => $data['razao_social'] ?? null, 'email' => $data['email'] ?? null,
            'phone' => $data['ddd_telefone_1'] ?? $data['telefone'] ?? null,
            'postal_code' => BrazilianDocument::digits((string) ($data['cep'] ?? '')),
            'street' => $data['logradouro'] ?? null, 'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null, 'district' => $data['bairro'] ?? null,
            'city' => $data['municipio'] ?? null, 'state' => $data['uf'] ?? null, 'country' => 'BR',
        ];
    }

    public function postalCode(string $postalCode): ?array
    {
        $response = $this->request()->get(rtrim(config('crm.lookups.postal_code_base_url'), '/').'/'.$postalCode);
        if ($response->status() === 404) return null;
        $response->throw();
        $data = $response->json();
        if (! is_array($data)) return null;

        return [
            'postal_code' => BrazilianDocument::digits((string) ($data['cep'] ?? $postalCode)),
            'street' => $data['street'] ?? $data['logradouro'] ?? null,
            'district' => $data['neighborhood'] ?? $data['bairro'] ?? null,
            'city' => $data['city'] ?? $data['localidade'] ?? null,
            'state' => $data['state'] ?? $data['uf'] ?? null, 'country' => 'BR',
        ];
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()->timeout(config('crm.lookups.http_timeout', 8));
    }
}
