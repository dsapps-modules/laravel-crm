<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ProcessBrevoTransactionalEvent;
use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrevoWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBrevoTransactionalEvent $events, BrevoChannelAccountResolver $accounts): array
    {
        $channelAccount = $accounts->resolve();
        abort_unless($channelAccount, 503, 'Brevo ainda não foi configurado.');
        abort_unless($this->authentic($request, $channelAccount), 401);

        $payload = $request->json()->all();
        $items = array_is_list($payload) ? $payload : [$payload];
        $eventIds = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                abort(422, 'Evento Brevo inválido.');
            }
            $event = $events->execute($channelAccount, $item);
            $eventIds[] = $event->id;
        }

        return ['accepted' => true, 'event_ids' => $eventIds];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $expected = config('crm.email.brevo.webhook_token') ?: ($account->credentials['webhook_token'] ?? null);
        $authorization = (string) $request->header('Authorization');
        return $expected && hash_equals('Bearer '.$expected, $authorization);
    }
}
