<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request, ChannelAccount $channelAccount, RecordInboundEvent $events): array
    {
        abort_unless($channelAccount->channel === 'whatsapp', 404);
        abort_unless($this->authentic($request, $channelAccount), 401);
        $payload = $request->json()->all();
        $providerEventId = (string) ($payload['id'] ?? $payload['event_id'] ?? $request->header('X-Event-Id', hash('sha256', $request->getContent())));
        $event = $events->execute($channelAccount, $providerEventId, $payload);
        return ['accepted' => true, 'event_id' => $event->id];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $credentials = $account->credentials ?? [];
        if ($account->provider === 'meta_cloud') {
            $secret = $credentials['app_secret'] ?? null;
            $signature = $request->header('X-Hub-Signature-256');
            return $secret && $signature && hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $signature);
        }
        if ($account->provider === 'uazapi') {
            $expected = $credentials['webhook_token'] ?? $credentials['token'] ?? null;
            return $expected && hash_equals($expected, (string) $request->header('token'));
        }
        return false;
    }
}
