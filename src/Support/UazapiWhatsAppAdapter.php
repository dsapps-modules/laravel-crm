<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Contracts\SendResult;
use DsApps\LaravelCrm\Models\Message;
use Illuminate\Support\Facades\Http;

final class UazapiWhatsAppAdapter implements ChannelAdapter
{
    public function __construct(private readonly array $credentials, private readonly array $config) {}

    public function send(Message $message): SendResult
    {
        $token = $this->config['token'] ?? $this->credentials['token'] ?? null;
        if (! $token) throw new \RuntimeException('Token Uazapi ausente.');
        $response = Http::timeout(config('crm.whatsapp.http_timeout', 15))->withHeaders(['token' => $token])->post(rtrim($this->config['base_url'], '/').'/send/text', ['number' => $message->recipient, 'text' => $message->body]);
        if ($response->failed()) throw new \RuntimeException('Uazapi recusou o envio WhatsApp.');
        return new SendResult('accepted', data_get($response->json(), 'id') ?? data_get($response->json(), 'message.id'));
    }

    public function capabilities(): array { return ['delivery_status' => true, 'read_status' => true, 'templates' => false]; }
}
