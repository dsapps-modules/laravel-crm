<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ProcessBrevoMarketingEvent;
use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrevoMarketingWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBrevoMarketingEvent $processor, BrevoChannelAccountResolver $accounts): array
    {
        $account = $accounts->resolve();
        abort_unless($this->authentic($request, $account), 401);
        $payload = $request->json()->all();
        $items = array_is_list($payload) ? $payload : [$payload];
        $eventIds = [];
        foreach ($items as $item) {
            if (! is_array($item)) abort(422, 'Evento de marketing Brevo inválido.');
            $eventIds[] = $processor->execute($account, $item)->id;
        }
        return ['accepted' => true, 'event_ids' => $eventIds];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $expected = config('crm.email.brevo.webhook_token') ?: ($account->credentials['webhook_token'] ?? null);
        return $expected && hash_equals('Bearer '.$expected, (string) $request->header('Authorization'));
    }
}
