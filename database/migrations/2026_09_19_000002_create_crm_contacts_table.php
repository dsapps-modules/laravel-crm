<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('crm.table_prefix').'contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained(config('crm.table_prefix').'companies')->nullOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 40)->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void { Schema::dropIfExists(config('crm.table_prefix').'contacts'); }
};
