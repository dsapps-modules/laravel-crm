<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Models\ChannelAccount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChannelAccountController extends Controller
{
    public function index(): mixed { return ChannelAccount::orderBy('name')->paginate(100); }
    public function store(Request $request): ChannelAccount
    {
        return ChannelAccount::create($request->validate(['channel' => ['required', 'in:whatsapp,email'], 'provider' => ['required', 'string', 'max:80'], 'name' => ['required', 'string', 'max:120'], 'status' => ['sometimes', 'in:connected,disconnected,blocked'], 'credentials' => ['sometimes', 'array']]));
    }
    public function show(ChannelAccount $channelAccount): ChannelAccount { return $channelAccount; }
}
