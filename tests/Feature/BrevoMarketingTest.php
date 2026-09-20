<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Http\Controllers\BrevoMarketingWebhookController;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\EmailCampaign;
use DsApps\LaravelCrm\Services\BrevoMarketingAdapterFactory;
use DsApps\LaravelCrm\Services\ProcessBrevoMarketingEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Tests\TestCase;

class BrevoMarketingTest extends TestCase
{
    public function test_campaign_is_created_sent_and_reported_using_brevo_api(): void
    {
        Http::fakeSequence()->push(['id' => 42], 201)->push([], 204)->push(['id' => 42, 'status' => 'sent', 'statistics' => ['delivered' => 1]], 200);
        $account = ChannelAccount::create(['channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo', 'status' => 'connected', 'credentials' => ['api_key' => 'secret']]);
        $campaign = EmailCampaign::create([
            'channel_account_id' => $account->id, 'name' => 'BPL Newsletter', 'subject' => 'Novidades', 'sender_email' => 'contato@example.com',
            'sender_name' => 'BPL', 'html_content' => '<p>Novidades da BPL</p>', 'recipients' => ['listIds' => [7]], 'tag' => 'bpl_marketing', 'idempotency_key' => 'campaign-1',
        ]);
        $adapter = app(BrevoMarketingAdapterFactory::class)->for($account);

        $campaign->update(['provider_campaign_id' => $adapter->create($campaign), 'status' => 'draft']);
        $adapter->sendNow($campaign->refresh());
        $report = $adapter->report($campaign->refresh());

        $this->assertSame(42, $campaign->provider_campaign_id);
        $this->assertSame('sent', $report['status']);
        Http::assertSentCount(3);
    }

    public function test_marketing_webhook_tracks_campaign_events_idempotently(): void
    {
        $account = ChannelAccount::create(['channel' => 'email', 'provider' => 'brevo', 'name' => 'Brevo', 'status' => 'connected', 'credentials' => ['webhook_token' => 'webhook-secret']]);
        $campaign = EmailCampaign::create([
            'channel_account_id' => $account->id, 'provider_campaign_id' => 42, 'name' => 'BPL Newsletter', 'subject' => 'Novidades',
            'sender_email' => 'contato@example.com', 'html_content' => '<p>Novidades da BPL</p>', 'recipients' => ['listIds' => [7]], 'tag' => 'bpl_marketing', 'idempotency_key' => 'campaign-2',
        ]);
        $payload = ['id' => 9001, 'camp_id' => 42, 'event' => 'opened', 'email' => 'cliente@example.com', 'tag' => 'bpl_marketing', 'ts_event' => 123];
        $request = Request::create('/webhooks/brevo/marketing', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer webhook-secret'], json_encode($payload));
        $controller = app(BrevoMarketingWebhookController::class);

        $controller($request, app(ProcessBrevoMarketingEvent::class));
        $controller($request, app(ProcessBrevoMarketingEvent::class));

        $this->assertDatabaseCount('crm_inbound_events', 1);
        $this->assertSame(1, $campaign->refresh()->stats['events']['opened']);
        $this->assertTrue($campaign->stats['recipients']['cliente@example.com']['opened']);
    }
}
