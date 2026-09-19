<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        Schema::create($p.'automations', function (Blueprint $t): void { $t->id(); $t->string('name', 160); $t->string('trigger', 80); $t->json('conditions')->nullable(); $t->json('actions'); $t->boolean('active')->default(false); $t->unsignedInteger('max_retries')->default(3); $t->timestamps(); });
        Schema::create($p.'automation_runs', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('automation_id')->constrained($p.'automations')->cascadeOnDelete(); $t->string('occurrence_key', 190); $t->string('status', 20)->default('running'); $t->text('error')->nullable(); $t->timestamp('finished_at')->nullable(); $t->timestamps(); $t->unique(['automation_id', 'occurrence_key']); });
    }

    public function down(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        Schema::dropIfExists($p.'automation_runs'); Schema::dropIfExists($p.'automations');
    }
};
