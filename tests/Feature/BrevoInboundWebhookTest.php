<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Http\Controllers\BrevoInboundWebhookController;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\ProcessBrevoInboundEmail;
use Illuminate\Http\Request;
use Tests\TestCase;

class BrevoInboundWebhookTest extends TestCase
{
    public function test_inbound_webhook_creates_reply_in_existing_conversation_and_deduplicates(): void
    {
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo',
            'credentials' => ['webhook_token' => 'webhook-secret'],
        ]);
        $conversation = Conversation::create(['channel_account_id' => $account->id, 'reply_token' => 'reply-token']);
        $conversation->messages()->create([
            'direction' => 'outbound', 'status' => 'sent', 'body' => 'Olá', 'external_id' => '<outbound-1>', 'idempotency_key' => 'outbound-1',
        ]);
        $payload = ['items' => [[
            'Uuid' => ['inbound-1'], 'MessageId' => '<inbound-1>', 'InReplyTo' => '<outbound-1>',
            'From' => ['Name' => 'Cliente', 'Address' => 'cliente@example.com'],
            'To' => [['Address' => 'reply-token@reply.bplprodutos.com.br']], 'Subject' => 'Re: Olá',
            'ExtractedMarkdownMessage' => 'Resposta do cliente',
        ]]];
        $request = Request::create('/webhooks/brevo/inbound', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer webhook-secret',
        ], json_encode($payload));
        $controller = app(BrevoInboundWebhookController::class);

        $controller($request, app(ProcessBrevoInboundEmail::class));
        $controller($request, app(ProcessBrevoInboundEmail::class));

        $this->assertDatabaseCount('crm_inbound_events', 1);
        $this->assertDatabaseCount('crm_messages', 2);
        $this->assertDatabaseHas('crm_messages', ['direction' => 'inbound', 'body' => 'Resposta do cliente', 'status' => 'unread']);
        $this->assertSame(1, $conversation->refresh()->unread_count);
    }

    public function test_inbound_webhook_creates_new_conversation_for_unknown_sender(): void
    {
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo',
            'credentials' => ['webhook_token' => 'webhook-secret'],
        ]);
        $payload = ['items' => [[
            'Uuid' => ['inbound-unknown'], 'MessageId' => '<inbound-unknown>',
            'From' => ['Address' => 'novo@example.com'], 'To' => [['Address' => 'reply@reply.bplprodutos.com.br']],
            'Subject' => 'Novo contato', 'TextBody' => 'Olá, gostaria de saber mais.',
        ]]];
        $request = Request::create('/webhooks/brevo/inbound', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer webhook-secret',
        ], json_encode($payload));

        app(BrevoInboundWebhookController::class)($request, app(ProcessBrevoInboundEmail::class));

        $this->assertDatabaseCount('crm_conversations', 1);
        $this->assertDatabaseHas('crm_messages', ['direction' => 'inbound', 'sender' => 'novo@example.com']);
    }
}
