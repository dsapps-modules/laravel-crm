<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\EmailCampaign;
use DsApps\LaravelCrm\Models\InboundEvent;

final class ProcessBrevoMarketingEvent
{
    public function __construct(private readonly RecordInboundEvent $events) {}

    public function execute(ChannelAccount $account, array $payload): InboundEvent
    {
        $providerEventId = hash('sha256', implode('|', [
            (string) ($payload['id'] ?? ''), (string) ($payload['camp_id'] ?? ''),
            (string) ($payload['event'] ?? ''), (string) ($payload['email'] ?? ''),
            (string) ($payload['ts_event'] ?? $payload['date_event'] ?? json_encode($payload)),
        ]));
        $event = $this->events->execute($account, $providerEventId, $payload);
        if (! $event->wasRecentlyCreated) return $event;

        $campaign = EmailCampaign::where('provider_campaign_id', $payload['camp_id'] ?? null)
            ->where('channel_account_id', $account->id)->first();
        if (! $campaign) return $event;

        $tag = $payload['tag'] ?? null;
        if (is_array($tag)) $tag = $tag[0] ?? null;
        if ($tag !== null && $tag !== '' && $tag !== $campaign->tag) return $event;

        $stats = $campaign->stats ?? [];
        $eventName = (string) ($payload['event'] ?? 'unknown');
        $stats['events'][$eventName] = ($stats['events'][$eventName] ?? 0) + 1;
        if (! empty($payload['email'])) $stats['recipients'][$payload['email']][$eventName] = true;
        $stats['last_event'] = $eventName;
        $stats['last_event_at'] = now()->toIso8601String();
        $campaign->update(['stats' => $stats, 'status' => in_array($eventName, ['delivered', 'opened', 'click'], true) ? 'sent' : $campaign->status]);
        return $event;
    }
}
