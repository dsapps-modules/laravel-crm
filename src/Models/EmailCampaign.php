<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['channel_account_id', 'account'];
    protected $casts = ['recipients' => 'array', 'stats' => 'array', 'scheduled_at' => 'immutable_datetime'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'email_campaigns'; }
    public function account(): BelongsTo { return $this->belongsTo(ChannelAccount::class, 'channel_account_id'); }
}
