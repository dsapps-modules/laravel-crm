<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityStageHistory extends Model
{
    protected $guarded = ['id'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'opportunity_stage_history'; }
    public function opportunity(): BelongsTo { return $this->belongsTo(Opportunity::class); }
}
