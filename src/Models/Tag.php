<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $guarded = ['id'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'tags'; }
    public function taggings(): HasMany { return $this->hasMany(Tagging::class); }
}
