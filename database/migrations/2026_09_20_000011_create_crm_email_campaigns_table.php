<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $p = config('crm.table_prefix', 'crm_');
        Schema::create($p.'email_campaigns', function (Blueprint $table) use ($p): void {
            $table->id();
            $table->foreignId('channel_account_id')->constrained($p.'channel_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('provider_campaign_id')->nullable()->unique();
            $table->string('name', 190);
            $table->string('subject', 998);
            $table->string('sender_email', 190);
            $table->string('sender_name', 120)->nullable();
            $table->string('reply_to', 190)->nullable();
            $table->text('html_content');
            $table->json('recipients');
            $table->string('tag', 80);
            $table->string('status', 30)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->json('stats')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('crm.table_prefix', 'crm_').'email_campaigns');
    }
};
