<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['finished_at' => 'immutable_datetime'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'automation_runs'; }
    public function automation(): BelongsTo { return $this->belongsTo(Automation::class); }
}
