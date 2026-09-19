<?php

namespace DsApps\LaravelCrm\Contracts;

final readonly class SendResult
{
    public function __construct(public string $status, public ?string $externalId = null) {}
}
