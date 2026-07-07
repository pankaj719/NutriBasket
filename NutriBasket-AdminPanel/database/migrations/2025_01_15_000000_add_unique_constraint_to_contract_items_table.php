<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate items in the same contract
            $table->unique(['contract_id', 'item_id'], 'contract_items_contract_id_item_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->dropUnique('contract_items_contract_id_item_id_unique');
        });
    }
}; 