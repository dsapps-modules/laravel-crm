<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $guarded = ['id'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'teams'; }
    public function members(): HasMany { return $this->hasMany(TeamMember::class); }
}
