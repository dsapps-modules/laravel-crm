<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\SendMessage;
use DsApps\LaravelCrm\Services\WhatsAppAdapterFactory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppAdaptersTest extends TestCase
{
    public function test_meta_cloud_adapter_uses_graph_messages_contract(): void
    {
        config()->set('crm.whatsapp.meta.api_version', 'v25.0');
        Http::fake(['https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.meta']]], 200)]);
        $account = ChannelAccount::create(['channel' => 'whatsapp', 'provider' => 'meta_cloud', 'name' => 'Meta', 'status' => 'connected', 'credentials' => ['phone_number_id' => '123', 'access_token' => 'secret']]);
        $conversation = Conversation::create(['channel_account_id' => $account->id]);
        $message = app(SendMessage::class)->execute($conversation, ['recipient' => '5511999999999', 'body' => 'Olá Meta', 'idempotency_key' => 'meta-1'], app(WhatsAppAdapterFactory::class)->for($account));

        $this->assertSame('accepted', $message->status);
        $this->assertSame('wamid.meta', $message->external_id);
        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v25.0/123/messages' && $request->header('Authorization')[0] === 'Bearer secret' && $request['text']['body'] === 'Olá Meta');
    }

    public function test_uazapi_adapter_uses_token_header_and_send_text_contract(): void
    {
        Http::fake(['https://api.uzapi.com.br/*' => Http::response(['id' => 'uazapi-1'], 200)]);
        $account = ChannelAccount::create(['channel' => 'whatsapp', 'provider' => 'uazapi', 'name' => 'Uazapi', 'status' => 'connected', 'credentials' => ['token' => 'instance-secret']]);
        $conversation = Conversation::create(['channel_account_id' => $account->id]);
        $message = app(SendMessage::class)->execute($conversation, ['recipient' => '5511999999999', 'body' => 'Olá Uazapi', 'idempotency_key' => 'uazapi-1'], app(WhatsAppAdapterFactory::class)->for($account));

        $this->assertSame('uazapi-1', $message->external_id);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.uzapi.com.br/send/text' && $request->header('token')[0] === 'instance-secret' && $request['number'] === '5511999999999');
    }
}
