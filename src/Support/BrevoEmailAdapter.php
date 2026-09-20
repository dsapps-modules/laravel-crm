<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Contracts\SendResult;
use DsApps\LaravelCrm\Models\Message;
use Illuminate\Support\Facades\Http;

final class BrevoEmailAdapter implements ChannelAdapter
{
    public function __construct(private readonly array $credentials, private readonly array $config) {}

    public function send(Message $message): SendResult
    {
        $apiKey = $this->config['api_key'] ?? $this->credentials['api_key'] ?? null;
        $senderEmail = $this->config['sender_email'] ?? $this->credentials['sender_email'] ?? null;
        $subject = $message->metadata['subject'] ?? null;
        if (! $apiKey || ! $senderEmail || ! $subject || ! $message->recipient) {
            throw new \RuntimeException('Credenciais e dados do e-mail Brevo incompletos.');
        }

        $payload = [
            'sender' => array_filter(['email' => $senderEmail, 'name' => $this->config['sender_name'] ?? $this->credentials['sender_name'] ?? null]),
            'to' => [['email' => $message->recipient]],
            'subject' => $subject,
            'tags' => array_values(array_unique(array_merge(
                [$this->config['app_tag'] ?? 'laravel_crm'],
                $message->metadata['tags'] ?? [],
            ))),
        ];
        if (isset($message->metadata['reply_to'])) $payload['replyTo'] = $message->metadata['reply_to'];
        if ($message->message_type === 'html') {
            $payload['htmlContent'] = $message->body ?? '';
        } else {
            $payload['textContent'] = $message->body ?? '';
        }

        $response = Http::timeout(config('crm.email.http_timeout', 15))
            ->withHeaders(['api-key' => $apiKey, 'accept' => 'application/json'])
            ->post(rtrim($this->config['base_url'] ?? 'https://api.brevo.com', '/').'/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Brevo recusou o envio do e-mail.');
        }

        return new SendResult('accepted', data_get($response->json(), 'messageId'));
    }

    public function capabilities(): array
    {
        return ['delivery_status' => true, 'read_status' => true, 'templates' => false, 'html' => true];
    }
}
