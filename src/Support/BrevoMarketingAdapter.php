<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Models\EmailCampaign;
use Illuminate\Support\Facades\Http;

final class BrevoMarketingAdapter
{
    public function __construct(private readonly array $credentials, private readonly array $config) {}

    public function create(EmailCampaign $campaign): int
    {
        $response = $this->request()->post($this->url('/v3/emailCampaigns'), array_filter([
            'name' => $campaign->name,
            'sender' => array_filter(['email' => $campaign->sender_email, 'name' => $campaign->sender_name]),
            'subject' => $campaign->subject,
            'htmlContent' => $campaign->html_content,
            'recipients' => $campaign->recipients,
            'replyTo' => $campaign->reply_to,
            'tag' => $campaign->tag,
            'scheduledAt' => $campaign->scheduled_at?->toIso8601String(),
        ], static fn ($value) => $value !== null));
        if ($response->failed() || ! is_numeric($response->json('id'))) throw new \RuntimeException('Brevo recusou a criação da campanha.');
        return (int) $response->json('id');
    }

    public function sendNow(EmailCampaign $campaign): void
    {
        $response = $this->request()->post($this->url('/v3/emailCampaigns/'.$campaign->provider_campaign_id.'/sendNow'));
        if ($response->failed()) throw new \RuntimeException('Brevo recusou o disparo da campanha.');
    }

    public function report(EmailCampaign $campaign): array
    {
        $response = $this->request()->get($this->url('/v3/emailCampaigns/'.$campaign->provider_campaign_id));
        if ($response->failed() || ! is_array($response->json())) throw new \RuntimeException('Brevo não retornou o relatório da campanha.');
        return $response->json();
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $apiKey = $this->config['api_key'] ?? $this->credentials['api_key'] ?? null;
        if (! $apiKey) throw new \RuntimeException('Credencial Brevo ausente.');
        return Http::timeout(config('crm.email.http_timeout', 15))->withHeaders(['api-key' => $apiKey, 'accept' => 'application/json']);
    }

    private function url(string $path): string { return rtrim($this->config['base_url'] ?? 'https://api.brevo.com', '/').$path; }
}
