<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Actions\MoveOpportunityStage;
use DsApps\LaravelCrm\Exceptions\InvalidPipelineStage;
use DsApps\LaravelCrm\Exceptions\OpportunityConflict;
use DsApps\LaravelCrm\Models\Contact;
use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\Pipeline;
use Tests\TestCase;

class OpportunityTest extends TestCase
{
    public function test_stage_move_is_audited_and_increments_version(): void
    {
        [$opportunity, $first, $second] = $this->opportunityFixture();

        $updated = app(MoveOpportunityStage::class)->execute($opportunity, $second, 1);

        $this->assertSame($second->id, $updated->pipeline_stage_id);
        $this->assertSame(2, $updated->version);
        $this->assertDatabaseHas('crm_opportunity_stage_history', ['opportunity_id' => $opportunity->id, 'from_stage_id' => $first->id, 'to_stage_id' => $second->id, 'from_version' => 1, 'to_version' => 2]);
    }

    public function test_stage_from_another_pipeline_is_rejected(): void
    {
        [$opportunity] = $this->opportunityFixture();
        $otherStage = Pipeline::create(['name' => 'Outro funil'])->stages()->create(['name' => 'Outra etapa', 'position' => 1]);

        $this->expectException(InvalidPipelineStage::class);
        app(MoveOpportunityStage::class)->execute($opportunity, $otherStage, 1);
    }

    public function test_stale_version_does_not_overwrite_opportunity(): void
    {
        [$opportunity, , $second] = $this->opportunityFixture();
        $opportunity->update(['version' => 2]);

        $this->expectException(OpportunityConflict::class);
        app(MoveOpportunityStage::class)->execute($opportunity->fresh(), $second, 1);
    }

    private function opportunityFixture(): array
    {
        $contact = Contact::create(['first_name' => 'João']);
        $pipeline = Pipeline::create(['name' => 'Vendas']);
        $first = $pipeline->stages()->create(['name' => 'Novo', 'position' => 1]);
        $second = $pipeline->stages()->create(['name' => 'Qualificação', 'position' => 2]);
        $opportunity = Opportunity::create(['pipeline_id' => $pipeline->id, 'pipeline_stage_id' => $first->id, 'contact_id' => $contact->id, 'title' => 'Contrato anual', 'amount' => '1000.0000', 'currency' => 'BRL']);
        return [$opportunity, $first, $second];
    }
}
