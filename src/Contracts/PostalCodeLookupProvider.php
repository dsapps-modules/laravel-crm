<?php

namespace DsApps\LaravelCrm\Contracts;

interface PostalCodeLookupProvider
{
    /** @return array<string, mixed>|null */
    public function find(string $postalCode): ?array;
}
