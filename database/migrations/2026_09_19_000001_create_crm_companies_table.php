<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('crm.table_prefix').'companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index('name');
        });
    }

    public function down(): void { Schema::dropIfExists(config('crm.table_prefix').'companies'); }
};
