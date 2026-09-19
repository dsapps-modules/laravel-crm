<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['filters' => 'array'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'segments'; }
}
