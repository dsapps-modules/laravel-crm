<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
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
        $this->assertSame(['webhook_token' => 'env-webhook-token'], $account->credentials);
        $this->assertArrayNotHasKey('credentials', $account->toArray());
    }
}
