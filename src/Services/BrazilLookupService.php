<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Contracts\CnpjLookupProvider;
use DsApps\LaravelCrm\Contracts\PostalCodeLookupProvider;
use DsApps\LaravelCrm\Support\BrazilianDocument;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class BrazilLookupService
{
    public function __construct(private readonly CnpjLookupProvider $cnpj, private readonly PostalCodeLookupProvider $postalCode) {}

    /** @return array<string, mixed>|null */
    public function cnpj(string $document): ?array
    {
        $normalized = BrazilianDocument::digits($document);
        if (! BrazilianDocument::valid('cnpj', $normalized)) throw new InvalidArgumentException('CNPJ inválido.');
        return Cache::remember('crm:lookup:cnpj:'.$normalized, config('crm.lookups.cache_ttl', 86400), fn () => $this->cnpj->find($normalized));
    }

    /** @return array<string, mixed>|null */
    public function postalCode(string $postalCode): ?array
    {
        $normalized = BrazilianDocument::digits($postalCode);
        if ($normalized === null || strlen($normalized) !== 8) throw new InvalidArgumentException('CEP inválido.');
        return Cache::remember('crm:lookup:cep:'.$normalized, config('crm.lookups.cache_ttl', 86400), fn () => $this->postalCode->find($normalized));
    }
}
