<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ProcessBrevoInboundEmail;
use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrevoInboundWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBrevoInboundEmail $processor, BrevoChannelAccountResolver $accounts): array
    {
        $account = $accounts->resolve();
        abort_unless($this->authentic($request, $account), 401);
        $events = $processor->execute($account, $request->json()->all());
        return ['accepted' => true, 'event_ids' => collect($events)->pluck('id')->values()->all()];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $expected = config('crm.email.brevo.webhook_token') ?: ($account->credentials['webhook_token'] ?? null);
        return $expected && hash_equals('Bearer '.$expected, (string) $request->header('Authorization'));
    }
}
