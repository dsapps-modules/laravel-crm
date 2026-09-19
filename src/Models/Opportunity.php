<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'decimal:4', 'expected_close_at' => 'date'];

    public function getTable(): string { return config('crm.table_prefix', 'crm_').'opportunities'; }
    public function pipeline(): BelongsTo { return $this->belongsTo(Pipeline::class); }
    public function stage(): BelongsTo { return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id'); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function stageHistory(): HasMany { return $this->hasMany(OpportunityStageHistory::class); }
}
