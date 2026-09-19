<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelAccount extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['capabilities' => 'array'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'channel_accounts'; }
    public function conversations(): HasMany { return $this->hasMany(Conversation::class); }
}
