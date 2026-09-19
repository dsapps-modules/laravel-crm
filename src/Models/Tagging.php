<?php

namespace DsApps\LaravelCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tagging extends Model
{
    protected $guarded = ['id'];
    public function getTable(): string { return config('crm.table_prefix', 'crm_').'taggings'; }
    public function tag(): BelongsTo { return $this->belongsTo(Tag::class); }
}
