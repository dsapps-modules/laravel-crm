<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Models\InboundEvent;
use DsApps\LaravelCrm\Models\Message;
use Illuminate\Support\Facades\DB;

final class ProcessBrevoInboundEmail
{
    public function __construct(private readonly RecordInboundEvent $events) {}

    /** @return list<InboundEvent> */
    public function execute(ChannelAccount $account, array $payload): array
    {
        $items = $payload['items'] ?? (array_is_list($payload) ? $payload : [$payload]);
        $recorded = [];
        foreach ($items as $item) {
            if (! is_array($item)) continue;
            $providerEventId = (string) ($item['Uuid'][0] ?? $item['UUID'][0] ?? $item['MessageId'] ?? hash('sha256', json_encode($item)));
            $event = $this->events->execute($account, $providerEventId, $item);
            $recorded[] = $event;
            if ($event->wasRecentlyCreated) $this->storeMessage($account, $item, $providerEventId);
        }
        return $recorded;
    }

    private function storeMessage(ChannelAccount $account, array $item, string $providerEventId): void
    {
        $externalId = (string) ($item['MessageId'] ?? $item['message-id'] ?? '');
        $inReplyTo = (string) ($item['InReplyTo'] ?? $item['in_reply_to'] ?? '');
        $recipient = (string) ($item['Recipients'][0] ?? $item['To'][0]['Address'] ?? '');
        $replyToken = str_contains($recipient, '@') ? explode('@', $recipient, 2)[0] : null;
        $conversation = null;
        if ($inReplyTo !== '') {
            $conversation = Conversation::whereHas('messages', fn ($query) => $query->where('external_id', $inReplyTo))
                ->whereHas('account', fn ($query) => $query->whereKey($account->id))->first();
        }
        if (! $conversation && $replyToken) {
            $conversation = Conversation::where('reply_token', $replyToken)
                ->whereHas('account', fn ($query) => $query->whereKey($account->id))->first();
        }
        if (! $conversation) {
            $conversation = Conversation::create([
                'channel_account_id' => $account->id,
                'external_thread_id' => $externalId !== '' ? 'brevo:'.$externalId : 'brevo:'.$providerEventId,
            ]);
        }

        $body = $item['ExtractedMarkdownMessage'] ?? $item['ExtractedHtmlMessage'] ?? $item['TextBody'] ?? $item['HtmlBody'] ?? $item['Body'] ?? null;
        $from = $item['From']['Address'] ?? $item['From'] ?? null;
        DB::transaction(function () use ($conversation, $item, $externalId, $providerEventId, $body, $from): void {
            $conversation->messages()->firstOrCreate(['idempotency_key' => 'brevo-inbound:'.$providerEventId], [
                'direction' => 'inbound',
                'message_type' => isset($item['ExtractedHtmlMessage']) ? 'html' : 'text',
                'status' => 'unread',
                'body' => is_string($body) ? $body : null,
                'sender' => is_string($from) ? $from : ($from['Address'] ?? null),
                'recipient' => $item['To'][0]['Address'] ?? $item['Recipients'][0] ?? null,
                'external_id' => $externalId ?: null,
                'metadata' => ['subject' => $item['Subject'] ?? null, 'brevo_message_id' => $externalId, 'in_reply_to' => $item['InReplyTo'] ?? null],
            ]);
            $conversation->update(['last_message_at' => now(), 'unread_count' => $conversation->unread_count + 1]);
        });
    }
}
