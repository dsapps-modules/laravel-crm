<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\Automation;
use DsApps\LaravelCrm\Models\Contact;
use DsApps\LaravelCrm\Models\Opportunity;
use DsApps\LaravelCrm\Models\Pipeline;
use DsApps\LaravelCrm\Models\Task;
use DsApps\LaravelCrm\Services\AutomationRunner;
use DsApps\LaravelCrm\Services\CrmIndicators;
use Tests\TestCase;

class AutomationAndIndicatorsTest extends TestCase
{
    public function test_automation_occurrence_is_idempotent_and_creates_one_task(): void
    {
        $contact = Contact::create(['first_name' => 'Rui']);
        $automation = Automation::create(['name' => 'Acompanhar', 'trigger' => 'opportunity.stage_changed', 'conditions' => ['status' => 'open'], 'actions' => [['type' => 'create_task', 'title' => 'Ligar para o lead']], 'active' => true]);

        $first = app(AutomationRunner::class)->run($automation, 'event-1', ['status' => 'open', 'contact_id' => $contact->id]);
        $second = app(AutomationRunner::class)->run($automation, 'event-1', ['status' => 'open', 'contact_id' => $contact->id]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('completed', $first->status);
        $this->assertDatabaseCount('crm_tasks', 1);
        $this->assertDatabaseCount('crm_automation_runs', 1);
    }

    public function test_win_rate_is_null_without_closed_opportunities_and_uses_closed_denominator(): void
    {
        $summary = app(CrmIndicators::class)->summary();
        $this->assertNull($summary['win_rate']);
        $contact = Contact::create(['first_name' => 'Bia']);
        $pipeline = Pipeline::create(['name' => 'Indicadores']);
        $stage = $pipeline->stages()->create(['name' => 'Fechado', 'position' => 1]);
        $data = ['pipeline_id' => $pipeline->id, 'pipeline_stage_id' => $stage->id, 'contact_id' => $contact->id, 'title' => 'Ganha', 'status' => 'won'];
        Opportunity::create($data); Opportunity::create([...$data, 'title' => 'Perdida', 'status' => 'lost', 'loss_reason' => 'Preço']);

        $summary = app(CrmIndicators::class)->summary();
        $this->assertSame(0.5, $summary['win_rate']);
        $this->assertSame(2, $summary['win_rate_denominator']);
    }
}
