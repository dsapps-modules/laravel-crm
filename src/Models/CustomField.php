<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['options' => 'array', 'required' => 'boolean', 'active' => 'boolean'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'custom_fields'; }
    public function values(): HasMany { return $this->hasMany(CustomFieldValue::class); }
}
