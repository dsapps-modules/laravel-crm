<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        Schema::create($prefix.'tasks', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained($prefix.'contacts')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained($prefix.'opportunities')->nullOnDelete();
            $table->unsignedBigInteger('assignee_id')->nullable()->index();
            $table->string('title', 180);
            $table->text('notes')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('pending');
            $table->timestamp('due_at')->nullable()->index();
            $table->string('timezone', 80)->default('UTC');
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_at']);
        });
        Schema::create($prefix.'calendar_events', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained($prefix.'contacts')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained($prefix.'opportunities')->nullOnDelete();
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->string('title', 180);
            $table->text('notes')->nullable();
            // DATETIME avoids MySQL's implicit TIMESTAMP default rules while
            // preserving the UTC instants normalized by CalendarEventRequest.
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('timezone', 80)->default('UTC');
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();
            $table->index(['start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        Schema::dropIfExists($prefix.'calendar_events');
        Schema::dropIfExists($prefix.'tasks');
    }
};
