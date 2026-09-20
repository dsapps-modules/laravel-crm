<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\RecordInboundEvent;
use DsApps\LaravelCrm\Services\ProcessBrevoTransactionalEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrevoWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBrevoTransactionalEvent $events): array
    {
        $channelAccount = ChannelAccount::query()
            ->where('channel', 'email')
            ->where('provider', 'brevo')
            ->first();
        abort_unless($channelAccount, 503, 'Brevo ainda não foi configurado.');
        abort_unless($this->authentic($request, $channelAccount), 401);

        $payload = $request->json()->all();
        $items = array_is_list($payload) ? $payload : [$payload];
        $eventIds = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                abort(422, 'Evento Brevo inválido.');
            }
            $eventId = (string) ($item['message-id'] ?? $item['messageId'] ?? $item['id'] ?? $item['event_id'] ?? hash('sha256', json_encode($item)));
            $event = $events->execute($channelAccount, $item);
            $eventIds[] = $event->id;
        }

        return ['accepted' => true, 'event_ids' => $eventIds];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $expected = $account->credentials['webhook_token'] ?? null;
        $authorization = (string) $request->header('Authorization');
        return $expected && hash_equals('Bearer '.$expected, $authorization);
    }
}
