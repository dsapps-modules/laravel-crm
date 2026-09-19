<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        Schema::create($p.'channel_accounts', function (Blueprint $t): void { $t->id(); $t->string('channel', 30); $t->string('provider', 80); $t->string('name', 120); $t->string('status', 20)->default('disconnected'); $t->json('capabilities')->nullable(); $t->text('credentials')->nullable(); $t->timestamps(); });
        Schema::create($p.'conversations', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('channel_account_id')->constrained($p.'channel_accounts')->restrictOnDelete(); $t->foreignId('contact_id')->nullable()->constrained($p.'contacts')->nullOnDelete(); $t->unsignedBigInteger('assignee_id')->nullable()->index(); $t->string('external_thread_id', 190)->nullable(); $t->string('status', 20)->default('open'); $t->unsignedInteger('unread_count')->default(0); $t->timestamp('last_message_at')->nullable()->index(); $t->timestamps(); $t->unique(['channel_account_id', 'external_thread_id']); });
        Schema::create($p.'messages', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('conversation_id')->constrained($p.'conversations')->cascadeOnDelete(); $t->string('direction', 20); $t->string('message_type', 20)->default('text'); $t->string('status', 20)->default('pending'); $t->text('body')->nullable(); $t->string('sender', 190)->nullable(); $t->string('recipient', 190)->nullable(); $t->string('external_id', 190)->nullable(); $t->string('idempotency_key', 190); $t->timestamp('sent_at')->nullable(); $t->timestamps(); $t->unique('idempotency_key'); $t->unique(['conversation_id', 'external_id']); });
        Schema::create($p.'inbound_events', function (Blueprint $t) use ($p): void { $t->id(); $t->foreignId('channel_account_id')->constrained($p.'channel_accounts')->cascadeOnDelete(); $t->string('provider_event_id', 190); $t->json('payload'); $t->string('status', 20)->default('pending'); $t->timestamps(); $t->unique(['channel_account_id', 'provider_event_id']); });
    }

    public function down(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        foreach (['inbound_events', 'messages', 'conversations', 'channel_accounts'] as $table) Schema::dropIfExists($p.$table);
    }
};
