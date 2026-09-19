<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFieldValue extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['value' => 'array'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'custom_field_values'; }
    public function field(): BelongsTo { return $this->belongsTo(CustomField::class, 'custom_field_id'); }
}
