<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Http\Controllers\BrevoWebhookController;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use Illuminate\Http\Request;
use Tests\TestCase;

class BrevoWebhookTest extends TestCase
{
    public function test_brevo_webhook_requires_bearer_token_and_deduplicates_message_events(): void
    {
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo',
            'credentials' => ['webhook_token' => 'webhook-secret'],
        ]);
        $payload = ['event' => 'delivered', 'message-id' => '<brevo-event-1>', 'email' => 'contact@example.com'];
        $body = json_encode($payload);
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer webhook-secret',
        ], $body);
        $controller = app(BrevoWebhookController::class);

        $controller($request, $account, app(RecordInboundEvent::class));
        $controller($request, $account, app(RecordInboundEvent::class));

        $this->assertDatabaseCount('crm_inbound_events', 1);
    }

    public function test_brevo_webhook_rejects_invalid_token(): void
    {
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo',
            'credentials' => ['webhook_token' => 'valid'],
        ]);
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer wrong',
        ], json_encode(['event' => 'delivered', 'message-id' => 'brevo-event']));

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(BrevoWebhookController::class)($request, $account, app(RecordInboundEvent::class));
    }
}
