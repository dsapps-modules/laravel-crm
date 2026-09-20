<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ConfiguredChannelResolver;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, RecordInboundEvent $events, ConfiguredChannelResolver $channels): array
    {
        abort_unless(in_array($provider, ['meta_cloud', 'uazapi'], true), 404);
        $channelAccount = $channels->resolve('whatsapp', $provider);
        abort_unless($this->authentic($request, $channelAccount, $provider), 401);
        $payload = $request->json()->all();
        $providerEventId = (string) ($payload['id'] ?? $payload['event_id'] ?? $request->header('X-Event-Id', hash('sha256', $request->getContent())));
        $event = $events->execute($channelAccount, $providerEventId, $payload);
        return ['accepted' => true, 'event_id' => $event->id];
    }

    private function authentic(Request $request, ChannelAccount $account, string $provider): bool
    {
        $credentials = $account->credentials ?? [];
        if ($provider === 'meta_cloud') {
            $secret = config('crm.whatsapp.meta.app_secret') ?: ($credentials['app_secret'] ?? null);
            $signature = $request->header('X-Hub-Signature-256');
            return $secret && $signature && hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $signature);
        }
        if ($provider === 'uazapi') {
            $expected = config('crm.whatsapp.uazapi.webhook_token') ?: ($credentials['webhook_token'] ?? $credentials['token'] ?? null);
            return $expected && hash_equals($expected, (string) $request->header('token'));
        }
        return false;
    }
}
