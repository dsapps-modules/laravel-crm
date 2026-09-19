<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\Conversation;
use DsApps\LaravelCrm\Models\ChannelAccount;
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
    public function store(Request $request): Conversation
    {
        $data = $request->validate(['channel_account_id' => ['required', 'exists:'.config('crm.table_prefix').'channel_accounts,id'], 'contact_id' => ['nullable', 'exists:'.config('crm.table_prefix').'contacts,id'], 'external_thread_id' => ['nullable', 'string', 'max:190']]);
        return Conversation::create($data);
    }
    public function show(Conversation $conversation): Conversation { return $conversation->load(['account', 'messages']); }
}
