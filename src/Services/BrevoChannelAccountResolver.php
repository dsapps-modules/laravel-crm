<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use RuntimeException;

final class BrevoChannelAccountResolver
{
    public function resolve(): ChannelAccount
    {
        $account = ChannelAccount::query()->where('channel', 'email')->where('provider', 'brevo')->first();
        if ($account) return $account;

        if (! config('crm.email.brevo.api_key') || ! config('crm.email.brevo.sender_email')) {
            throw new RuntimeException('Brevo não configurado. Defina CRM_EMAIL_BREVO_API_KEY e CRM_EMAIL_BREVO_SENDER_EMAIL.');
        }

        return ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => config('crm.email.brevo.sender_name') ?: config('crm.email.brevo.sender_email'),
            'status' => 'connected', 'capabilities' => ['delivery_status' => true, 'read_status' => true, 'marketing' => true, 'inbound' => true],
            'credentials' => array_filter(['webhook_token' => config('crm.email.brevo.webhook_token')]),
        ]);
    }
}
