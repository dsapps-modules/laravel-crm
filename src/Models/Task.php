<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['due_at' => 'immutable_datetime', 'reminder_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'tasks'; }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function opportunity(): BelongsTo { return $this->belongsTo(Opportunity::class); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
}
