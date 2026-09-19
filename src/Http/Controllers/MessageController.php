<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\SendMessage;
use DsApps\LaravelCrm\Services\WhatsAppAdapterFactory;
use DsApps\LaravelCrm\Services\EmailAdapterFactory;
use DsApps\LaravelCrm\Support\UnconfiguredChannelAdapter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation, SendMessage $send): mixed
    {
        $data = $request->validate(['direction' => ['sometimes', 'in:outbound,internal'], 'body' => ['nullable', 'string', 'max:10000'], 'recipient' => ['nullable', 'string', 'max:190'], 'message_type' => ['sometimes', 'in:text,html'], 'subject' => ['nullable', 'string', 'max:998'], 'idempotency_key' => ['required', 'string', 'max:190']]);
        if (($data['direction'] ?? 'outbound') !== 'internal' && $conversation->account->channel === 'email' && blank($data['subject'] ?? null)) {
            return response()->json(['message' => 'O assunto é obrigatório para e-mails.'], 422);
        }
        if (($data['direction'] ?? 'outbound') !== 'internal' && $conversation->account->status !== 'connected') return response()->json(['message' => 'Canal desconectado ou bloqueado.'], 503);
        if (isset($data['subject'])) $data['metadata'] = ['subject' => $data['subject']];
        $adapter = ($data['direction'] ?? 'outbound') === 'internal' ? new UnconfiguredChannelAdapter() : match ($conversation->account->channel) {
            'whatsapp' => app(WhatsAppAdapterFactory::class)->for($conversation->account),
            'email' => app(EmailAdapterFactory::class)->for($conversation->account),
            default => throw new \InvalidArgumentException('Canal não configurado.'),
        };
        $message = $send->execute($conversation, $data, $adapter);
        return response()->json($message, 201);
    }
}
