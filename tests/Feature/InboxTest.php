<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Exceptions\ChannelDisconnected;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use DsApps\LaravelCrm\Services\SendMessage;
use DsApps\LaravelCrm\Testing\FakeChannelAdapter;
use Tests\TestCase;

class InboxTest extends TestCase
{
    public function test_outbound_message_is_idempotent_and_internal_note_is_never_sent(): void
    {
        $account = ChannelAccount::create(['channel' => 'email', 'provider' => 'fake', 'name' => 'Teste', 'status' => 'connected']);
        $conversation = Conversation::create(['channel_account_id' => $account->id, 'status' => 'open']);
        $adapter = new FakeChannelAdapter();
        $service = app(SendMessage::class);

        $message = $service->execute($conversation, ['body' => 'Olá', 'recipient' => 'cliente@example.test', 'idempotency_key' => 'msg-1'], $adapter);
        $same = $service->execute($conversation, ['body' => 'duplicada', 'idempotency_key' => 'msg-1'], $adapter);
        $note = $service->execute($conversation, ['direction' => 'internal', 'body' => 'Nota privada', 'idempotency_key' => 'note-1'], $adapter);

        $this->assertSame($message->id, $same->id);
        $this->assertSame('sent', $message->status);
        $this->assertSame('internal', $note->direction);
        $this->assertCount(1, $adapter->sent);
        $this->assertDatabaseCount('crm_messages', 2);
    }

    public function test_disconnected_channel_blocks_external_send(): void
    {
        $account = ChannelAccount::create(['channel' => 'whatsapp', 'provider' => 'unconfigured', 'name' => 'Desconectado', 'status' => 'disconnected']);
        $conversation = Conversation::create(['channel_account_id' => $account->id, 'status' => 'open']);

        $this->expectException(ChannelDisconnected::class);
        app(SendMessage::class)->execute($conversation, ['body' => 'Não enviar', 'idempotency_key' => 'blocked-1'], new FakeChannelAdapter());
        $this->assertDatabaseCount('crm_messages', 0);
    }

    public function test_inbound_events_are_deduplicated_by_provider_id(): void
    {
        $account = ChannelAccount::create(['channel' => 'email', 'provider' => 'fake', 'name' => 'Teste', 'status' => 'connected']);
        $service = app(RecordInboundEvent::class);
        $first = $service->execute($account, 'event-1', ['message' => 'oi']);
        $second = $service->execute($account, 'event-1', ['message' => 'duplicado']);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('crm_inbound_events', 1);
    }
}
