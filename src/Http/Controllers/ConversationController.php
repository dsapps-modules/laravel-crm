<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Services\ConfiguredChannelResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConversationController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = Conversation::with('account')->withCount(['messages as unread_messages_count' => fn ($q) => $q->where('status', 'unread')]);
        if ($request->filled('channel')) $query->whereHas('account', fn ($q) => $q->where('channel', $request->string('channel')));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        return $query->latest('last_message_at')->paginate(min($request->integer('per_page', 25), 100));
    }
    public function store(Request $request, ConfiguredChannelResolver $channels): Conversation
    {
        $data = $request->validate(['channel' => ['required', 'in:email,whatsapp'], 'provider' => ['required', 'string', 'in:brevo,meta_cloud,uazapi'], 'contact_id' => ['nullable', 'exists:'.config('crm.table_prefix').'contacts,id'], 'external_thread_id' => ['nullable', 'string', 'max:190']]);
        $data['channel_account_id'] = $channels->resolve($data['channel'], $data['provider'])->id;
        unset($data['channel'], $data['provider']);
        return Conversation::create($data);
    }
    public function show(Conversation $conversation): Conversation { return $conversation->load(['account', 'messages']); }
}
