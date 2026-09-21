<?php

namespace DsApps\LaravelCrm\Contracts;

interface CnpjLookupProvider
{
    /** @return array<string, mixed>|null */
    public function find(string $document): ?array;
}
