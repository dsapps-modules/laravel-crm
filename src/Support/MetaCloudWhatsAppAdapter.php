<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Contracts\SendResult;
use DsApps\LaravelCrm\Models\Message;
use Illuminate\Support\Facades\Http;

final class MetaCloudWhatsAppAdapter implements ChannelAdapter
{
    public function __construct(private readonly array $credentials, private readonly array $config) {}

    public function send(Message $message): SendResult
    {
        $phoneNumberId = $this->config['phone_number_id'] ?? $this->credentials['phone_number_id'] ?? null;
        $token = $this->config['access_token'] ?? $this->credentials['access_token'] ?? null;
        $version = $this->config['api_version'] ?? null;
        if (! $phoneNumberId || ! $token || ! $version) throw new \RuntimeException('Credenciais Meta incompletas.');
        $response = Http::timeout(config('crm.whatsapp.http_timeout', 15))->withToken($token)->post(rtrim($this->config['base_url'], '/')."/{$version}/{$phoneNumberId}/messages", ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $message->recipient, 'type' => 'text', 'text' => ['preview_url' => false, 'body' => $message->body]]);
        if ($response->failed()) throw new \RuntimeException('Meta recusou o envio WhatsApp.');
        return new SendResult('accepted', data_get($response->json(), 'messages.0.id'));
    }

    public function capabilities(): array { return ['delivery_status' => true, 'read_status' => true, 'templates' => true]; }
}
