<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    public function test_default_api_contract_is_versioned_and_prefixed(): void
    {
        $this->assertSame('api/crm/v1', config('crm.api.prefix'));
        $this->assertSame('crm_', config('crm.table_prefix'));
    }
}
