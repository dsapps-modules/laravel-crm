<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
use DsApps\LaravelCrm\Services\ConfiguredChannelResolver;
use Tests\TestCase;

class BrevoConfigurationTest extends TestCase
{
    public function test_brevo_configuration_is_loaded_from_environment_and_not_exposed_as_credentials(): void
    {
        config()->set('crm.email.brevo.api_key', 'env-api-key');
        config()->set('crm.email.brevo.sender_email', 'contato@example.com');
        config()->set('crm.email.brevo.sender_name', 'CRM');
        config()->set('crm.email.brevo.webhook_token', 'env-webhook-token');

        $account = app(BrevoChannelAccountResolver::class)->resolve();

        $this->assertSame('email', $account->channel);
        $this->assertSame('brevo', $account->provider);
        $this->assertSame('connected', $account->status);
        $this->assertSame([], $account->credentials);
        $this->assertArrayNotHasKey('credentials', $account->toArray());
    }

    public function test_uazapi_channel_is_resolved_from_environment_without_public_account_setup(): void
    {
        config()->set('crm.whatsapp.uazapi.token', 'uazapi-token');
        config()->set('crm.whatsapp.uazapi.webhook_token', 'uazapi-webhook-token');

        $account = app(ConfiguredChannelResolver::class)->resolve('whatsapp', 'uazapi');

        $this->assertSame('whatsapp', $account->channel);
        $this->assertSame('uazapi', $account->provider);
        $this->assertSame('connected', $account->status);
        $this->assertSame([], $account->credentials);
    }
}
