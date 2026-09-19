<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmInstallationTest extends TestCase
{
    public function test_crm_tables_are_loaded_by_the_service_provider(): void
    {
        $this->assertTrue(Schema::hasTable('crm_companies'));
        $this->assertTrue(Schema::hasTable('crm_contacts'));
        $this->assertTrue(Schema::hasTable('crm_external_links'));
    }
}
