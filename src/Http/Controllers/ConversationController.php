<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Models\ChannelAccount;
use DsApps\LaravelCrm\Services\BrevoChannelAccountResolver;
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
    public function store(Request $request, BrevoChannelAccountResolver $brevoAccounts): Conversation
    {
        $data = $request->validate(['channel_account_id' => ['nullable', 'exists:'.config('crm.table_prefix').'channel_accounts,id'], 'channel' => ['required_without:channel_account_id', 'nullable', 'in:email'], 'provider' => ['required_with:channel', 'nullable', 'in:brevo'], 'contact_id' => ['nullable', 'exists:'.config('crm.table_prefix').'contacts,id'], 'external_thread_id' => ['nullable', 'string', 'max:190']]);
        if (empty($data['channel_account_id'])) $data['channel_account_id'] = $brevoAccounts->resolve()->id;
        unset($data['channel'], $data['provider']);
        return Conversation::create($data);
    }
    public function show(Conversation $conversation): Conversation { return $conversation->load(['account', 'messages']); }
}
