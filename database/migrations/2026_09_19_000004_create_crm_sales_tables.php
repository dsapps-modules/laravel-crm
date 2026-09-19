<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        Schema::create($prefix.'pipelines', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create($prefix.'pipeline_stages', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('pipeline_id')->constrained($prefix.'pipelines')->cascadeOnDelete();
            $table->string('name', 160);
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['pipeline_id', 'position']);
        });
        Schema::create($prefix.'opportunities', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('pipeline_id')->constrained($prefix.'pipelines')->restrictOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained($prefix.'pipeline_stages')->restrictOnDelete();
            $table->foreignId('contact_id')->constrained($prefix.'contacts')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained($prefix.'companies')->nullOnDelete();
            $table->string('title', 180);
            $table->decimal('amount', 19, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->date('expected_close_at')->nullable();
            $table->string('status', 20)->default('open');
            $table->string('loss_reason', 180)->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->index(['pipeline_id', 'pipeline_stage_id', 'status']);
        });
        Schema::create($prefix.'opportunity_stage_history', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('opportunity_id')->constrained($prefix.'opportunities')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained($prefix.'pipeline_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained($prefix.'pipeline_stages')->restrictOnDelete();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedInteger('from_version');
            $table->unsignedInteger('to_version');
            $table->timestamps();
            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        Schema::dropIfExists($prefix.'opportunity_stage_history');
        Schema::dropIfExists($prefix.'opportunities');
        Schema::dropIfExists($prefix.'pipeline_stages');
        Schema::dropIfExists($prefix.'pipelines');
    }
};
