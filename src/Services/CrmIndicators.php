<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\Task;

final class CrmIndicators
{
    public function summary(): array
    {
        $won = Opportunity::where('status', 'won')->count();
        $lost = Opportunity::where('status', 'lost')->count();
        return ['period' => 'all_time', 'leads_or_contacts' => config('crm.table_prefix', 'crm_').'contacts', 'open_opportunities' => Opportunity::where('status', 'open')->count(), 'won_opportunities' => $won, 'lost_opportunities' => $lost, 'win_rate' => ($won + $lost) > 0 ? round($won / ($won + $lost), 4) : null, 'overdue_tasks' => Task::pending()->whereNotNull('due_at')->where('due_at', '<', now())->count(), 'win_rate_denominator' => $won + $lost];
    }
}
