<?php

namespace DsApps\LaravelCrm\Contracts;

use DsApps\LaravelCrm\Models\CalendarEvent;

interface CalendarProvider
{
    public function create(CalendarEvent $event): CalendarSyncResult;
    public function update(CalendarEvent $event): CalendarSyncResult;
    public function cancel(CalendarEvent $event): CalendarSyncResult;
    public function capabilities(): array;
}
