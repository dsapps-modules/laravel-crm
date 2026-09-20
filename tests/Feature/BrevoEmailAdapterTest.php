<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\EmailAdapterFactory;
use DsApps\LaravelCrm\Services\SendMessage;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrevoEmailAdapterTest extends TestCase
{
    public function test_brevo_adapter_sends_plain_text_email_with_api_key(): void
    {
        Http::fake(['https://api.brevo.com/*' => Http::response(['messageId' => '<brevo-1>'], 201)]);
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo', 'status' => 'connected',
            'credentials' => ['api_key' => 'secret', 'sender_email' => 'crm@example.com', 'sender_name' => 'CRM'],
        ]);
        $conversation = Conversation::create(['channel_account_id' => $account->id]);

        $message = app(SendMessage::class)->execute($conversation, [
            'recipient' => 'contact@example.com', 'subject' => 'Olá', 'body' => 'Mensagem de teste',
            'idempotency_key' => 'brevo-1',
        ], app(EmailAdapterFactory::class)->for($account));

        $this->assertSame('accepted', $message->status);
        $this->assertSame('<brevo-1>', $message->external_id);
        $this->assertSame('Olá', $message->metadata['subject']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.brevo.com/v3/smtp/email'
            && $request->header('api-key')[0] === 'secret'
            && $request['sender']['email'] === 'crm@example.com'
            && $request['to'][0]['email'] === 'contact@example.com'
            && $request['tags'] === ['laravel_crm']
            && $request['textContent'] === 'Mensagem de teste');
    }

    public function test_brevo_adapter_supports_html_messages(): void
    {
        Http::fake(['https://api.brevo.com/*' => Http::response(['messageId' => '<brevo-html>'], 201)]);
        $account = ChannelAccount::create([
            'channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo', 'status' => 'connected',
            'credentials' => ['api_key' => 'secret', 'sender_email' => 'crm@example.com'],
        ]);
        $conversation = Conversation::create(['channel_account_id' => $account->id]);

        app(SendMessage::class)->execute($conversation, [
            'message_type' => 'html', 'recipient' => 'contact@example.com', 'subject' => 'HTML', 'body' => '<p>Olá</p>',
            'idempotency_key' => 'brevo-html',
        ], app(EmailAdapterFactory::class)->for($account));

        Http::assertSent(fn ($request) => $request['htmlContent'] === '<p>Olá</p>' && ! isset($request['textContent']));
    }
}
