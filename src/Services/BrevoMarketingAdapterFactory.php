<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Support\BrevoMarketingAdapter;
use InvalidArgumentException;

final class BrevoMarketingAdapterFactory
{
    public function for(ChannelAccount $account): BrevoMarketingAdapter
    {
        if ($account->channel !== 'email' || $account->provider !== 'brevo') throw new InvalidArgumentException('A conta informada não é Brevo.');
        return new BrevoMarketingAdapter($account->credentials ?? [], config('crm.email.brevo', []));
    }
}
