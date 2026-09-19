<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['start_at' => 'immutable_datetime', 'end_at' => 'immutable_datetime', 'all_day' => 'boolean'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'calendar_events'; }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function opportunity(): BelongsTo { return $this->belongsTo(Opportunity::class); }
}
