<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['sent_at' => 'immutable_datetime', 'metadata' => 'array'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'messages'; }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
}
