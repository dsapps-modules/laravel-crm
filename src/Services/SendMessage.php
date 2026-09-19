<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Exceptions\ChannelDisconnected;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Models\Message;
use Illuminate\Support\Facades\DB;

final class SendMessage
{
    public function execute(Conversation $conversation, array $data, ChannelAdapter $adapter): Message
    {
        $key = $data['idempotency_key'];
        $existing = Message::where('idempotency_key', $key)->first();
        if ($existing) return $existing;
        if (($data['direction'] ?? 'outbound') !== 'internal' && $conversation->account->status !== 'connected') {
            throw new ChannelDisconnected('O canal está desconectado.');
        }

        $message = DB::transaction(function () use ($conversation, $data): Message {
            $direction = $data['direction'] ?? 'outbound';
            return $conversation->messages()->create([
                'direction' => $direction, 'message_type' => $data['message_type'] ?? 'text',
                'status' => $direction === 'internal' ? 'sent' : 'pending', 'body' => $data['body'] ?? null,
                'sender' => $data['sender'] ?? null, 'recipient' => $data['recipient'] ?? null,
                'idempotency_key' => $data['idempotency_key'], 'sent_at' => $direction === 'internal' ? now() : null,
            ]);
        });

        if ($message->direction === 'internal') return $message;
        try {
            $result = $adapter->send($message);
            $message->update(['status' => $result->status, 'external_id' => $result->externalId, 'sent_at' => now()]);
        } catch (\Throwable) {
            $message->update(['status' => 'unknown']);
        }
        return $message->refresh();
    }
}
