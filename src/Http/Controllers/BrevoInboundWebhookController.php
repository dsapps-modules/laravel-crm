<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\ProcessBrevoInboundEmail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrevoInboundWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBrevoInboundEmail $processor): array
    {
        $account = ChannelAccount::query()->where('channel', 'email')->where('provider', 'brevo')->first();
        abort_unless($account, 503, 'Brevo ainda não foi configurado.');
        abort_unless($this->authentic($request, $account), 401);
        $events = $processor->execute($account, $request->json()->all());
        return ['accepted' => true, 'event_ids' => collect($events)->pluck('id')->values()->all()];
    }

    private function authentic(Request $request, ChannelAccount $account): bool
    {
        $expected = $account->credentials['webhook_token'] ?? null;
        return $expected && hash_equals('Bearer '.$expected, (string) $request->header('Authorization'));
    }
}
