<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        Schema::create($p.'teams', function (Blueprint $t): void { $t->id(); $t->string('name', 160); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create($p.'team_members', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('team_id')->constrained($p.'teams')->cascadeOnDelete(); $t->unsignedBigInteger('user_id'); $t->boolean('eligible_for_round_robin')->default(true); $t->timestamps(); $t->unique(['team_id', 'user_id']); });
        Schema::create($p.'tags', function (Blueprint $t): void { $t->id(); $t->string('name', 80); $t->string('color', 20)->nullable(); $t->timestamps(); $t->unique('name'); });
        Schema::create($p.'taggings', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('tag_id')->constrained($p.'tags')->cascadeOnDelete(); $t->string('entity_type', 80); $t->unsignedBigInteger('entity_id'); $t->timestamps(); $t->unique(['tag_id', 'entity_type', 'entity_id']); $t->index(['entity_type', 'entity_id']); });
        Schema::create($p.'custom_fields', function (Blueprint $t): void { $t->id(); $t->string('entity_type', 80); $t->string('key', 80); $t->string('label', 160); $t->string('type', 20); $t->json('options')->nullable(); $t->boolean('required')->default(false); $t->boolean('active')->default(true); $t->timestamps(); $t->unique(['entity_type', 'key']); });
        Schema::create($p.'custom_field_values', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('custom_field_id')->constrained($p.'custom_fields')->cascadeOnDelete(); $t->string('entity_type', 80); $t->unsignedBigInteger('entity_id'); $t->json('value')->nullable(); $t->timestamps(); $t->unique(['custom_field_id', 'entity_type', 'entity_id'], 'cfv_field_entity_unique'); });
        Schema::create($p.'segments', function (Blueprint $t): void { $t->id(); $t->string('entity_type', 80); $t->string('name', 160); $t->json('filters'); $t->unsignedBigInteger('owner_id')->nullable()->index(); $t->timestamps(); });
    }

    public function down(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        foreach (['segments', 'custom_field_values', 'custom_fields', 'taggings', 'tags', 'team_members', 'teams'] as $table) Schema::dropIfExists($p.$table);
    }
};
