<?php

namespace Tests\Feature;

use DsApps\LaravelCrm\Models\Contact;
use DsApps\LaravelCrm\Models\CustomField;
use DsApps\LaravelCrm\Models\Tag;
use DsApps\LaravelCrm\Models\Team;
use DsApps\LaravelCrm\Services\AssignTeamMember;
use DsApps\LaravelCrm\Services\SetCustomFieldValue;
use DsApps\LaravelCrm\Services\TagEntity;
use InvalidArgumentException;
use Tests\TestCase;

class TeamsTagsFieldsTest extends TestCase
{
    public function test_team_assignment_is_idempotent(): void
    {
        $team = Team::create(['name' => 'Comercial']);
        app(AssignTeamMember::class)->execute($team->id, 42);
        app(AssignTeamMember::class)->execute($team->id, 42, false);

        $this->assertDatabaseCount('crm_team_members', 1);
        $this->assertDatabaseHas('crm_team_members', ['team_id' => $team->id, 'user_id' => 42, 'eligible_for_round_robin' => 0]);
    }

    public function test_tagging_is_idempotent_and_rejects_unknown_entity_types(): void
    {
        $contact = Contact::create(['first_name' => 'Lia']);
        $tag = Tag::create(['name' => 'vip']);
        app(TagEntity::class)->attach($tag->id, 'contact', $contact->id);
        app(TagEntity::class)->attach($tag->id, 'contact', $contact->id);

        $this->assertDatabaseCount('crm_taggings', 1);
        $this->expectException(InvalidArgumentException::class);
        app(TagEntity::class)->attach($tag->id, 'user', 42);
    }

    public function test_select_custom_field_only_accepts_configured_options(): void
    {
        $field = CustomField::create(['entity_type' => 'contact', 'key' => 'segment', 'label' => 'Segmento', 'type' => 'select', 'options' => ['A', 'B']]);
        app(SetCustomFieldValue::class)->execute($field, 'contact', 10, 'A');
        $this->assertDatabaseHas('crm_custom_field_values', ['custom_field_id' => $field->id, 'entity_id' => 10]);

        $this->expectException(InvalidArgumentException::class);
        app(SetCustomFieldValue::class)->execute($field, 'contact', 10, 'C');
    }
}
