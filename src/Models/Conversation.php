<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['last_message_at' => 'immutable_datetime'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'conversations'; }
    public function account(): BelongsTo { return $this->belongsTo(ChannelAccount::class, 'channel_account_id'); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function messages(): HasMany { return $this->hasMany(Message::class); }
}
