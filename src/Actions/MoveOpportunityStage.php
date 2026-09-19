<?php

namespace DsApps\LaravelCrm\Actions;

use DsApps\LaravelCrm\Exceptions\InvalidPipelineStage;
use DsApps\LaravelCrm\Exceptions\OpportunityConflict;
use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\OpportunityStageHistory;
use DsApps\LaravelCrm\Models\PipelineStage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class MoveOpportunityStage
{
    public function execute(Opportunity $opportunity, PipelineStage $stage, int $expectedVersion, ?Authenticatable $actor = null): Opportunity
    {
        if ($stage->pipeline_id !== $opportunity->pipeline_id) {
            throw new InvalidPipelineStage('A etapa não pertence ao funil da oportunidade.');
        }

        return DB::transaction(function () use ($opportunity, $stage, $expectedVersion, $actor): Opportunity {
            $fromStageId = $opportunity->pipeline_stage_id;
            $nextVersion = $expectedVersion + 1;
            $updated = Opportunity::query()
                ->whereKey($opportunity->getKey())
                ->where('version', $expectedVersion)
                ->update(['pipeline_stage_id' => $stage->getKey(), 'version' => $nextVersion]);

            if ($updated !== 1) {
                throw new OpportunityConflict('A oportunidade foi alterada. Recarregue antes de mover novamente.');
            }

            OpportunityStageHistory::create([
                'opportunity_id' => $opportunity->getKey(),
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $stage->getKey(),
                'actor_type' => $actor?->getMorphClass(),
                'actor_id' => $actor?->getAuthIdentifier(),
                'from_version' => $expectedVersion,
                'to_version' => $nextVersion,
            ]);

            return $opportunity->refresh();
        });
    }
}
