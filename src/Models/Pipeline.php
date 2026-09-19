<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    protected $guarded = ['id'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'pipelines'; }
    public function stages(): HasMany { return $this->hasMany(PipelineStage::class)->orderBy('position'); }
    public function opportunities(): HasMany { return $this->hasMany(Opportunity::class); }
}
