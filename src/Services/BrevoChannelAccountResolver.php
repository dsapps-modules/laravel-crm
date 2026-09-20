<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;

final class BrevoChannelAccountResolver
{
    public function __construct(private readonly ConfiguredChannelResolver $channels) {}

    public function resolve(): ChannelAccount
    {
        return $this->channels->resolve('email', 'brevo');
    }
}
