<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Models\Task;
use Illuminate\Database\Eloquent\Model;

final class NextActionResolver
{
    public function for(Model $entity): ?Task
    {
        return Task::pending()->where(function ($query) use ($entity): void {
            $column = $entity instanceof \DsApps\LaravelCrm\Models\Contact ? 'contact_id' : 'opportunity_id';
            $query->where($column, $entity->getKey());
        })->orderByRaw('due_at IS NULL')->orderBy('due_at')->orderBy('id')->first();
    }
}
