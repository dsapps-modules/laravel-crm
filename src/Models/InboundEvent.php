<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;

class InboundEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'inbound_events'; }
}
