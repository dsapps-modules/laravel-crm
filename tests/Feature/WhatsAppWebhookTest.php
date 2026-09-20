<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Http\Controllers\WhatsAppWebhookController;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use Illuminate\Http\Request;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    public function test_meta_webhook_requires_valid_signature_and_deduplicates_event(): void
    {
        $secret = 'meta-app-secret';
        $account = ChannelAccount::create(['channel' => 'whatsapp', 'provider' => 'meta_cloud', 'name' => 'Meta', 'credentials' => ['app_secret' => $secret]]);
        $payload = ['id' => 'meta-event-1', 'entry' => []];
        $body = json_encode($payload);
        $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

        $request = Request::create('/webhook', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => $signature], $body);
        $controller = app(WhatsAppWebhookController::class);
        $controller($request, 'meta_cloud', app(RecordInboundEvent::class), app(\DsApps\LaravelCrm\Services\ConfiguredChannelResolver::class));
        $this->assertDatabaseCount('crm_inbound_events', 1);
    }

    public function test_uazapi_webhook_rejects_invalid_token(): void
    {
        $account = ChannelAccount::create(['channel' => 'whatsapp', 'provider' => 'uazapi', 'name' => 'Uazapi', 'credentials' => ['webhook_token' => 'valid']]);
        $request = Request::create('/webhook', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_TOKEN' => 'wrong'], json_encode(['id' => 'uazapi-event']));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(WhatsAppWebhookController::class)($request, 'uazapi', app(RecordInboundEvent::class), app(\DsApps\LaravelCrm\Services\ConfiguredChannelResolver::class));
        $this->assertDatabaseCount('crm_inbound_events', 0);
    }
}
