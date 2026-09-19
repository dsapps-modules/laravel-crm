<?php

namespace DsApps\LaravelCrm\Contracts;

final readonly class CalendarSyncResult
{
    public function __construct(public string $status, public ?string $externalId = null, public ?string $version = null) {}
}
