<?php

namespace DsApps\LaravelCrm\Models;

use DsApps\LaravelCrm\Contracts\ExternalLinkable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model implements ExternalLinkable
{
    use HasFactory;

    public function getTable(): string
    {
        return config('crm.table_prefix', 'crm_').'companies';
    }

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function externalLinks(): HasMany
    {
        return $this->hasMany(ExternalLink::class);
    }
}
