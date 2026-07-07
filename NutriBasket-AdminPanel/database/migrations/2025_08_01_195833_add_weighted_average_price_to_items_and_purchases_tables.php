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
        // Add weighted_average_price to items table
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'weighted_average_price')) {
                $table->decimal('weighted_average_price', 15, 2)->default(0.00)->after('price');
            }
        });

        // Add weighted_average_price to inventory_purchases table
        Schema::table('inventory_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_purchases', 'weighted_average_price')) {
                $table->decimal('weighted_average_price', 15, 2)->default(0.00)->after('total_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'weighted_average_price')) {
                $table->dropColumn('weighted_average_price');
            }
        });

        Schema::table('inventory_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_purchases', 'weighted_average_price')) {
                $table->dropColumn('weighted_average_price');
            }
        });
    }
};
