<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\Automation;
use DsApps\LaravelCrm\Models\AutomationRun;
use DsApps\LaravelCrm\Models\Task;
use DsApps\LaravelCrm\Models\Opportunity;
use Illuminate\Support\Facades\DB;

final class AutomationRunner
{
    public function run(Automation $automation, string $occurrenceKey, array $context): AutomationRun
    {
        $existing = AutomationRun::where('automation_id', $automation->id)->where('occurrence_key', $occurrenceKey)->first();
        if ($existing) return $existing;
        $run = AutomationRun::create(['automation_id' => $automation->id, 'occurrence_key' => $occurrenceKey, 'status' => 'running']);
        try {
            foreach ($automation->conditions ?? [] as $key => $expected) if (($context[$key] ?? null) !== $expected) return $this->finish($run, 'skipped');
            DB::transaction(function () use ($automation, $context): void {
                foreach ($automation->actions as $action) {
                    $type = $action['type'] ?? null;
                    if ($type === 'create_task') Task::create(['contact_id' => $context['contact_id'] ?? null, 'opportunity_id' => $context['opportunity_id'] ?? null, 'title' => $action['title'] ?? 'Acompanhamento automático', 'due_at' => $action['due_at'] ?? now(), 'status' => 'pending']);
                    elseif ($type === 'move_stage' && isset($context['opportunity_id'], $action['pipeline_stage_id'])) Opportunity::whereKey($context['opportunity_id'])->update(['pipeline_stage_id' => $action['pipeline_stage_id']]);
                    elseif (! in_array($type, ['notify_internal'], true)) throw new \InvalidArgumentException('Ação de automação não permitida.');
                }
            });
            return $this->finish($run, 'completed');
        } catch (\Throwable $e) { $run->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]); throw $e; }
    }

    private function finish(AutomationRun $run, string $status): AutomationRun { $run->update(['status' => $status, 'finished_at' => now()]); return $run->refresh(); }
}
