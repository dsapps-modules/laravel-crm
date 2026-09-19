<?php

namespace DsApps\LaravelCrm\Testing;

use DsApps\LaravelCrm\Contracts\CalendarProvider;
use DsApps\LaravelCrm\Contracts\CalendarSyncResult;
use DsApps\LaravelCrm\Models\CalendarEvent;

class FakeCalendarProvider implements CalendarProvider
{
    /** @var list<string> */
    public array $operations = [];

    public function create(CalendarEvent $event): CalendarSyncResult { $this->operations[] = 'create'; return new CalendarSyncResult('accepted', 'fake-'.$event->id, '1'); }
    public function update(CalendarEvent $event): CalendarSyncResult { $this->operations[] = 'update'; return new CalendarSyncResult('accepted', 'fake-'.$event->id, '2'); }
    public function cancel(CalendarEvent $event): CalendarSyncResult { $this->operations[] = 'cancel'; return new CalendarSyncResult('accepted', 'fake-'.$event->id, '3'); }
    public function capabilities(): array { return ['incremental_read' => false, 'external_invites' => false]; }
}
