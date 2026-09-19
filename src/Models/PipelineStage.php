<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $guarded = ['id'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'pipeline_stages'; }
    public function pipeline(): BelongsTo { return $this->belongsTo(Pipeline::class); }
    public function opportunities(): HasMany { return $this->hasMany(Opportunity::class); }
}
