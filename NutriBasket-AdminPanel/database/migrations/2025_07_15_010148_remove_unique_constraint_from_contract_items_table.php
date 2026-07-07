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
        // Drop foreign keys first
        \DB::statement('ALTER TABLE contract_items DROP FOREIGN KEY contract_items_contract_id_foreign');
        \DB::statement('ALTER TABLE contract_items DROP FOREIGN KEY contract_items_item_id_foreign');
        // Drop unique constraint
        \DB::statement('ALTER TABLE contract_items DROP INDEX contract_items_contract_id_item_id_unique');
        // Re-add foreign keys
        \DB::statement('ALTER TABLE contract_items ADD CONSTRAINT contract_items_contract_id_foreign FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE');
        \DB::statement('ALTER TABLE contract_items ADD CONSTRAINT contract_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        \DB::statement('ALTER TABLE contract_items DROP FOREIGN KEY contract_items_contract_id_foreign');
        \DB::statement('ALTER TABLE contract_items DROP FOREIGN KEY contract_items_item_id_foreign');
        // Re-add unique constraint
        \DB::statement('ALTER TABLE contract_items ADD UNIQUE KEY contract_items_contract_id_item_id_unique (contract_id, item_id)');
        // Re-add foreign keys
        \DB::statement('ALTER TABLE contract_items ADD CONSTRAINT contract_items_contract_id_foreign FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE');
        \DB::statement('ALTER TABLE contract_items ADD CONSTRAINT contract_items_item_id_foreign FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE');
    }
};
