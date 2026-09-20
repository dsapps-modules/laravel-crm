<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Http\Controllers\BrevoWebhookController;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ProcessBrevoTransactionalEvent;
use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
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

        $controller($request, app(ProcessBrevoTransactionalEvent::class), app(BrevoChannelAccountResolver::class));
        $controller($request, app(ProcessBrevoTransactionalEvent::class), app(BrevoChannelAccountResolver::class));

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
        app(BrevoWebhookController::class)($request, app(ProcessBrevoTransactionalEvent::class), app(BrevoChannelAccountResolver::class));
    }

    public function test_transactional_event_updates_only_matching_tagged_message(): void
    {
        config()->set('crm.email.brevo.app_tag', 'bpl_crm');
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo',
            'credentials' => ['webhook_token' => 'webhook-secret'],
        ]);
        $conversation = \DsApps\LaravelCrm\Models\Conversation::create(['channel_account_id' => $account->id]);
        $message = $conversation->messages()->create([
            'direction' => 'outbound', 'status' => 'pending', 'body' => 'Teste', 'recipient' => 'contact@example.com',
            'external_id' => '<brevo-message-1>', 'idempotency_key' => 'message-1', 'metadata' => ['tags' => ['bpl_crm']],
        ]);
        $payload = ['event' => 'delivered', 'message-id' => '<brevo-message-1>', 'tags' => ['bpl_crm'], 'ts_event' => 123];
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer webhook-secret',
        ], json_encode($payload));

        app(BrevoWebhookController::class)($request, app(ProcessBrevoTransactionalEvent::class), app(BrevoChannelAccountResolver::class));

        $this->assertSame('delivered', $message->refresh()->status);
        $this->assertSame('delivered', $message->metadata['brevo_last_event']);
    }
}
