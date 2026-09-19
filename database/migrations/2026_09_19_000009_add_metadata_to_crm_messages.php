<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('crm.table_prefix', 'crm_').'messages', function (Blueprint $table): void {
            $table->json('metadata')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table(config('crm.table_prefix', 'crm_').'messages', function (Blueprint $table): void {
            $table->dropColumn('metadata');
        });
    }
};
