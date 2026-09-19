<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['conditions' => 'array', 'actions' => 'array', 'active' => 'boolean'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'automations'; }
    public function runs(): HasMany { return $this->hasMany(AutomationRun::class); }
}
