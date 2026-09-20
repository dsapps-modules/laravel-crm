<?php

namespace DsApps\LaravelCrm\Actions;

use DsApps\LaravelCrm\Exceptions\PipelineInUse;
use DsApps\LaravelCrm\Models\OpportunityStageHistory;
use DsApps\LaravelCrm\Models\Pipeline;
use Illuminate\Support\Collection;

final class SyncPipelineStages
{
    public function execute(Pipeline $pipeline, array $stages): Pipeline
    {
        $ordered = collect($stages)->sortBy('position')->values();
        $existing = $pipeline->stages()->get()->keyBy('id');
        $incomingIds = $ordered->pluck('id')->filter()->map(fn ($id) => (int) $id);

        foreach ($incomingIds as $id) {
            if (! $existing->has($id)) {
                throw new \InvalidArgumentException('A etapa informada não pertence ao funil.');
            }
        }

        $removed = $existing->except($incomingIds->all());
        $this->ensureRemovable($pipeline, $removed);

        $base = ((int) $existing->max('position')) + $ordered->count() + 1000;
        foreach ($existing as $stage) {
            $stage->update(['position' => $base + $stage->id]);
        }

        foreach ($removed as $stage) {
            $stage->delete();
        }

        foreach ($ordered as $index => $data) {
            $stage = isset($data['id']) ? $existing->get((int) $data['id']) : $pipeline->stages()->create(['name' => $data['name'], 'position' => $base + $index]);
            $stage->update(['name' => $data['name'], 'position' => $index]);
        }

        return $pipeline->load('stages');
    }

    private function ensureRemovable(Pipeline $pipeline, Collection $stages): void
    {
        if ($stages->isEmpty()) return;

        $stageIds = $stages->keys()->all();
        $hasOpportunity = $pipeline->opportunities()->whereIn('pipeline_stage_id', $stageIds)->exists();
        $hasHistory = OpportunityStageHistory::query()
            ->where(function ($query) use ($stageIds): void {
                $query->whereIn('from_stage_id', $stageIds)->orWhereIn('to_stage_id', $stageIds);
            })->exists();

        if ($hasOpportunity || $hasHistory) {
            throw new PipelineInUse('Não é possível remover uma etapa que possui oportunidades ou histórico comercial.');
        }
    }
}
