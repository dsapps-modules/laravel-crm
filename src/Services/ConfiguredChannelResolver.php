<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use InvalidArgumentException;
use RuntimeException;

final class ConfiguredChannelResolver
{
    public function resolve(string $channel, string $provider): ChannelAccount
    {
        $account = ChannelAccount::query()->where('channel', $channel)->where('provider', $provider)->first();
        if ($account) return $account;

        $definition = $this->definition($channel, $provider);
        foreach ($definition['required'] as $key => $value) {
            if (blank($value)) {
                throw new RuntimeException("Canal {$channel}/{$provider} não configurado: {$key}.");
            }
        }

        $account = ChannelAccount::query()->firstOrCreate(
            ['channel' => $channel, 'provider' => $provider],
            [
                'name' => $definition['name'],
                'status' => 'connected',
                'capabilities' => $definition['capabilities'],
                'credentials' => [],
            ],
        );

        if ($account->status !== 'connected') {
            $account->update(['status' => 'connected']);
        }

        return $account->refresh();
    }

    private function definition(string $channel, string $provider): array
    {
        return match ([$channel, $provider]) {
            ['email', 'brevo'] => [
                'name' => config('crm.email.brevo.sender_name') ?: config('crm.email.brevo.sender_email'),
                'required' => [
                    'CRM_EMAIL_BREVO_API_KEY' => config('crm.email.brevo.api_key'),
                    'CRM_EMAIL_BREVO_SENDER_EMAIL' => config('crm.email.brevo.sender_email'),
                ],
                'capabilities' => ['delivery_status' => true, 'read_status' => true, 'marketing' => true, 'inbound' => true],
            ],
            ['whatsapp', 'meta_cloud'] => [
                'name' => 'WhatsApp Meta Cloud',
                'required' => [
                    'CRM_WHATSAPP_META_PHONE_NUMBER_ID' => config('crm.whatsapp.meta.phone_number_id'),
                    'CRM_WHATSAPP_META_ACCESS_TOKEN' => config('crm.whatsapp.meta.access_token'),
                    'CRM_WHATSAPP_META_API_VERSION' => config('crm.whatsapp.meta.api_version'),
                    'CRM_WHATSAPP_META_APP_SECRET' => config('crm.whatsapp.meta.app_secret'),
                ],
                'capabilities' => ['delivery_status' => true, 'read_status' => true, 'inbound' => true],
            ],
            ['whatsapp', 'uazapi'] => [
                'name' => 'WhatsApp Uazapi',
                'required' => [
                    'CRM_WHATSAPP_UAZAPI_TOKEN' => config('crm.whatsapp.uazapi.token'),
                    'CRM_WHATSAPP_UAZAPI_WEBHOOK_TOKEN' => config('crm.whatsapp.uazapi.webhook_token'),
                ],
                'capabilities' => ['delivery_status' => true, 'read_status' => true, 'inbound' => true],
            ],
            default => throw new InvalidArgumentException("Canal/provedor não suportado: {$channel}/{$provider}."),
        };
    }
}
