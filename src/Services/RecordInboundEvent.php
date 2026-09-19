<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\InboundEvent;

final class RecordInboundEvent
{
    public function execute(ChannelAccount $account, string $providerEventId, array $payload): InboundEvent
    {
        return InboundEvent::firstOrCreate(['channel_account_id' => $account->id, 'provider_event_id' => $providerEventId], ['payload' => $payload, 'status' => 'pending']);
    }
}
