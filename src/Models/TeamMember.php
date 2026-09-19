<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['eligible_for_round_robin' => 'boolean'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'team_members'; }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
}
