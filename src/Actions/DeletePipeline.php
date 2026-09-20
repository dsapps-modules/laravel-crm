<?php

namespace DsApps\LaravelCrm\Actions;

use DsApps\LaravelCrm\Exceptions\PipelineInUse;
use DsApps\LaravelCrm\Models\OpportunityStageHistory;
use DsApps\LaravelCrm\Models\Pipeline;

final class DeletePipeline
{
    public function execute(Pipeline $pipeline): void
    {
        if ($pipeline->opportunities()->exists()) {
            throw new PipelineInUse('Não é possível excluir um funil que possui oportunidades.');
        }

        $stageIds = $pipeline->stages()->pluck('id');
        if (OpportunityStageHistory::query()->whereIn('from_stage_id', $stageIds)->orWhereIn('to_stage_id', $stageIds)->exists()) {
            throw new PipelineInUse('Não é possível excluir um funil que possui histórico comercial.');
        }

        $pipeline->delete();
    }
}
