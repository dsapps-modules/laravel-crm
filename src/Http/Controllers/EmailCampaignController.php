<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Models\EmailCampaign;
use DsApps\LaravelCrm\Services\BrevoMarketingAdapterFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmailCampaignController extends Controller
{
    public function index(): mixed { return EmailCampaign::latest()->paginate(25); }

    public function store(Request $request): mixed
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'], 'subject' => ['required', 'string', 'max:998'],
            'sender_email' => ['required', 'email', 'max:190'], 'sender_name' => ['nullable', 'string', 'max:120'],
            'reply_to' => ['nullable', 'email', 'max:190'], 'html_content' => ['required', 'string', 'min:11', 'max:1000000'],
            'recipients' => ['required', 'array'], 'recipients.listIds' => ['sometimes', 'array'], 'recipients.listIds.*' => ['integer', 'min:1'],
            'recipients.segmentIds' => ['sometimes', 'array'], 'recipients.segmentIds.*' => ['integer', 'min:1'],
            'recipients.excludeListIds' => ['sometimes', 'array'], 'recipients.excludeListIds.*' => ['integer', 'min:1'],
            'tag' => ['nullable', 'string', 'max:80'], 'scheduled_at' => ['nullable', 'date'], 'idempotency_key' => ['required', 'string', 'max:190'],
        ]);
        $existing = EmailCampaign::where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing) return $existing;
        $account = ChannelAccount::where('channel', 'email')->where('provider', 'brevo')->firstOrFail();
        $campaign = EmailCampaign::create(array_merge($data, ['channel_account_id' => $account->id, 'tag' => $data['tag'] ?? config('crm.email.brevo.app_tag', 'laravel_crm'), 'status' => 'pending']));
        try {
            $campaign->update(['provider_campaign_id' => app(BrevoMarketingAdapterFactory::class)->for($account)->create($campaign), 'status' => 'draft']);
        } catch (\Throwable) {
            $campaign->update(['status' => 'unknown']);
        }
        return response()->json($campaign->refresh(), 201);
    }

    public function show(EmailCampaign $emailCampaign): EmailCampaign { return $emailCampaign; }

    public function send(EmailCampaign $emailCampaign): EmailCampaign
    {
        abort_unless($emailCampaign->provider_campaign_id && in_array($emailCampaign->status, ['draft', 'scheduled'], true), 409, 'Campanha não está pronta para disparo.');
        app(BrevoMarketingAdapterFactory::class)->for($emailCampaign->account)->sendNow($emailCampaign);
        $emailCampaign->update(['status' => 'scheduled']);
        return $emailCampaign->refresh();
    }

    public function report(EmailCampaign $emailCampaign): EmailCampaign
    {
        $stats = app(BrevoMarketingAdapterFactory::class)->for($emailCampaign->account)->report($emailCampaign);
        $emailCampaign->update(['stats' => $stats, 'status' => $stats['status'] ?? $emailCampaign->status]);
        return $emailCampaign->refresh();
    }
}
