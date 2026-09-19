<?php

namespace Tests\Unit;

use DsApps\LaravelCrm\Models\CalendarEvent;
use DsApps\LaravelCrm\Testing\FakeCalendarProvider;
use Tests\TestCase;

class CalendarProviderTest extends TestCase
{
    public function test_fake_provider_exposes_contract_without_external_invites(): void
    {
        $event = new CalendarEvent(['id' => 9]);
        $provider = new FakeCalendarProvider();
        $result = $provider->create($event);

        $this->assertSame('accepted', $result->status);
        $this->assertSame(['create'], $provider->operations);
        $this->assertFalse($provider->capabilities()['external_invites']);
    }
}
