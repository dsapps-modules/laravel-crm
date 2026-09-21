<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        foreach (['contacts', 'companies'] as $tableName) {
            Schema::table($prefix.$tableName, function (Blueprint $table) use ($tableName): void {
                $table->string('document_type', 4)->nullable();
                $table->string('document', 14)->nullable();
                $table->string('postal_code', 8)->nullable();
                $table->string('street', 180)->nullable();
                $table->string('number', 30)->nullable();
                $table->string('complement', 120)->nullable();
                $table->string('district', 120)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('state', 2)->nullable();
                $table->string('country', 2)->default('BR');
                $table->unique(['document_type', 'document'], $tableName.'_document_unique');
                $table->index('postal_code');
            });
        }
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        foreach (['contacts', 'companies'] as $tableName) {
            Schema::table($prefix.$tableName, function (Blueprint $table) use ($prefix, $tableName): void {
                $table->dropUnique($tableName.'_document_unique');
                $table->dropIndex($prefix.$tableName.'_postal_code_index');
                $table->dropColumn(['document_type', 'document', 'postal_code', 'street', 'number', 'complement', 'district', 'city', 'state', 'country']);
            });
        }
    }
};
