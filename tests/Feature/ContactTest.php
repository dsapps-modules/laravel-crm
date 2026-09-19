<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\Contact;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function test_contact_can_be_created_and_archived_without_deleting_history(): void
    {
        $contact = Contact::create(['first_name' => 'Ana', 'email' => 'ana@example.test']);

        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id, 'email' => 'ana@example.test']);
        $contact->update(['archived_at' => now()]);
        $this->assertNotNull($contact->fresh()->archived_at);
        $this->assertDatabaseHas('crm_contacts', ['id' => $contact->id]);
    }
}
