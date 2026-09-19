<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('crm.table_prefix').'external_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained(config('crm.table_prefix').'contacts')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained(config('crm.table_prefix').'companies')->cascadeOnDelete();
            $table->string('source', 80);
            $table->string('entity_type', 80);
            $table->string('external_id', 190);
            $table->timestamps();
            $table->unique(['source', 'entity_type', 'external_id']);
            $table->index(['contact_id', 'company_id']);
        });
    }

    public function down(): void { Schema::dropIfExists(config('crm.table_prefix').'external_links'); }
};
