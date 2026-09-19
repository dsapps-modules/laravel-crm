<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Support\MetaCloudWhatsAppAdapter;
use DsApps\LaravelCrm\Support\UazapiWhatsAppAdapter;
use InvalidArgumentException;

final class WhatsAppAdapterFactory
{
    public function for(ChannelAccount $account): ChannelAdapter
    {
        if ($account->channel !== 'whatsapp') throw new InvalidArgumentException('A conta informada não é WhatsApp.');
        return match ($account->provider) {
            'meta_cloud' => new MetaCloudWhatsAppAdapter($account->credentials ?? [], config('crm.whatsapp.meta', [])),
            'uazapi' => new UazapiWhatsAppAdapter($account->credentials ?? [], config('crm.whatsapp.uazapi', [])),
            default => throw new InvalidArgumentException('Provedor WhatsApp não configurado.'),
        };
    }
}
