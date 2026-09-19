<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use DsApps\LaravelCrm\Models\CalendarEvent;
use DsApps\LaravelCrm\Models\Contact;
use DsApps\LaravelCrm\Models\Task;
use DsApps\LaravelCrm\Support\NextActionResolver;
use Tests\TestCase;

class TaskAndCalendarTest extends TestCase
{
    public function test_next_action_uses_the_first_pending_task_and_ignores_completed_tasks(): void
    {
        $contact = Contact::create(['first_name' => 'Maria']);
        Task::create(['contact_id' => $contact->id, 'title' => 'Mais tarde', 'due_at' => CarbonImmutable::parse('2026-09-21 10:00:00', 'UTC'), 'status' => 'pending']);
        $first = Task::create(['contact_id' => $contact->id, 'title' => 'Próxima ligação', 'due_at' => CarbonImmutable::parse('2026-09-20 10:00:00', 'UTC'), 'status' => 'pending']);
        Task::create(['contact_id' => $contact->id, 'title' => 'Concluída', 'due_at' => CarbonImmutable::parse('2026-09-19 10:00:00', 'UTC'), 'status' => 'completed']);

        $this->assertSame($first->id, app(NextActionResolver::class)->for($contact)->id);
    }

    public function test_calendar_event_persists_utc_instants_and_timezone_metadata(): void
    {
        $event = CalendarEvent::create(['title' => 'Retorno', 'start_at' => '2026-09-20 13:00:00', 'end_at' => '2026-09-20 14:00:00', 'all_day' => true, 'timezone' => 'America/Sao_Paulo']);

        $this->assertSame('America/Sao_Paulo', $event->timezone);
        $this->assertTrue($event->fresh()->all_day);
        $this->assertSame('2026-09-20T13:00:00.000000Z', $event->fresh()->start_at->toISOString());
    }
}
