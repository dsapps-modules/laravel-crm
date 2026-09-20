<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('crm.table_prefix', 'crm_').'conversations', function (Blueprint $table): void {
            $table->string('reply_token', 80)->nullable()->unique()->after('external_thread_id');
        });
    }

    public function down(): void
    {
        Schema::table(config('crm.table_prefix', 'crm_').'conversations', function (Blueprint $table): void {
            $table->dropUnique([config('crm.table_prefix', 'crm_').'conversations_reply_token_unique']);
            $table->dropColumn('reply_token');
        });
    }
};
