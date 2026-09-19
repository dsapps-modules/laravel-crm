<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Support\BrevoEmailAdapter;
use InvalidArgumentException;

final class EmailAdapterFactory
{
    public function for(ChannelAccount $account): ChannelAdapter
    {
        if ($account->channel !== 'email') {
            throw new InvalidArgumentException('A conta informada não é e-mail.');
        }

        return match ($account->provider) {
            'brevo' => new BrevoEmailAdapter($account->credentials ?? [], config('crm.email.brevo', [])),
            default => throw new InvalidArgumentException('Provedor de e-mail não configurado.'),
        };
    }
}
