<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\InboundEvent;
use DsApps\LaravelCrm\Models\Message;

final class ProcessBrevoTransactionalEvent
{
    public function __construct(private readonly RecordInboundEvent $events) {}

    public function execute(ChannelAccount $account, array $payload): InboundEvent
    {
        $messageId = (string) ($payload['message-id'] ?? $payload['messageId'] ?? '');
        $eventName = (string) ($payload['event'] ?? $payload['event_name'] ?? 'unknown');
        $providerEventId = hash('sha256', implode('|', [$messageId, $eventName, (string) ($payload['ts_event'] ?? $payload['date'] ?? json_encode($payload))]));
        $event = $this->events->execute($account, $providerEventId, $payload);

        $tags = $payload['tags'] ?? $payload['tag'] ?? [];
        if (is_string($tags)) $tags = json_decode($tags, true) ?: [];
        $appTag = config('crm.email.brevo.app_tag', 'laravel_crm');
        if ($messageId === '' || ! in_array($appTag, is_array($tags) ? $tags : [], true)) return $event;

        $message = Message::query()
            ->where('external_id', $messageId)
            ->whereHas('conversation.account', fn ($query) => $query->whereKey($account->id))
            ->first();
        if (! $message) return $event;

        $status = match ($eventName) {
            'request', 'sent' => 'sent',
            'delivered' => 'delivered',
            'opened', 'uniqueOpened', 'proxyOpen', 'uniqueProxyOpen' => 'opened',
            'click' => 'clicked',
            'softBounce', 'soft_bounce', 'deferred' => 'soft_bounce',
            'hardBounce', 'hard_bounce', 'invalid' => 'hard_bounce',
            'blocked', 'spam', 'unsubscribed', 'error' => 'failed',
            default => null,
        };
        if ($status) $message->update(['status' => $status, 'metadata' => array_merge($message->metadata ?? [], ['brevo_last_event' => $eventName, 'brevo_last_event_at' => now()->toIso8601String()])]);
        return $event;
    }
}
