<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Actions\DeletePipeline;
use DsApps\LaravelCrm\Actions\SyncPipelineStages;
use DsApps\LaravelCrm\Exceptions\PipelineInUse;
use DsApps\LaravelCrm\Models\Contact;
use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\OpportunityStageHistory;
use DsApps\LaravelCrm\Models\Pipeline;
use Tests\TestCase;

class PipelineManagementTest extends TestCase
{
    public function test_pipeline_stages_can_be_renamed_added_removed_and_reordered(): void
    {
        $pipeline = Pipeline::create(['name' => 'Vendas']);
        $first = $pipeline->stages()->create(['name' => 'Novo', 'position' => 0]);
        $second = $pipeline->stages()->create(['name' => 'Proposta', 'position' => 1]);

        $updated = app(SyncPipelineStages::class)->execute($pipeline, [
            ['id' => $second->id, 'name' => 'Negociação', 'position' => 0],
            ['name' => 'Ganho', 'position' => 1],
        ]);

        $this->assertSame(['Negociação', 'Ganho'], $updated->stages->pluck('name')->all());
        $this->assertSame([0, 1], $updated->stages->pluck('position')->all());
        $this->assertDatabaseMissing('crm_pipeline_stages', ['id' => $first->id]);
    }

    public function test_stage_removal_is_blocked_when_stage_has_opportunity_or_history(): void
    {
        $pipeline = Pipeline::create(['name' => 'Vendas']);
        $first = $pipeline->stages()->create(['name' => 'Novo', 'position' => 0]);
        $pipeline->stages()->create(['name' => 'Proposta', 'position' => 1]);
        $contact = Contact::create(['first_name' => 'João']);
        Opportunity::create(['pipeline_id' => $pipeline->id, 'pipeline_stage_id' => $first->id, 'contact_id' => $contact->id, 'title' => 'Contrato']);

        $this->expectException(PipelineInUse::class);
        app(SyncPipelineStages::class)->execute($pipeline, [['name' => 'Proposta', 'position' => 0]]);
    }

    public function test_pipeline_can_be_deleted_only_without_opportunities_or_history(): void
    {
        $pipeline = Pipeline::create(['name' => 'Temporário']);
        $pipeline->stages()->create(['name' => 'Novo', 'position' => 0]);

        app(DeletePipeline::class)->execute($pipeline);

        $this->assertDatabaseMissing('crm_pipelines', ['id' => $pipeline->id]);
    }

    public function test_pipeline_deletion_is_blocked_by_stage_history(): void
    {
        $pipeline = Pipeline::create(['name' => 'Com histórico']);
        $stage = $pipeline->stages()->create(['name' => 'Novo', 'position' => 0]);
        $opportunity = Opportunity::create(['pipeline_id' => $pipeline->id, 'pipeline_stage_id' => $stage->id, 'contact_id' => Contact::create(['first_name' => 'Ana'])->id, 'title' => 'Histórico']);
        OpportunityStageHistory::create(['opportunity_id' => $opportunity->id, 'to_stage_id' => $stage->id, 'from_version' => 0, 'to_version' => 1]);

        $this->expectException(PipelineInUse::class);
        app(DeletePipeline::class)->execute($pipeline);
    }
}
