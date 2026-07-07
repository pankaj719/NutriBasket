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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('is_unavailable');
            $table->decimal('weighted_average_price', 15, 2)->default(0.00)->after('price');
        });

        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->decimal('weighted_average_price', 15, 2)->default(0.00)->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_unavailable')->default(false)->after('is_halal');
            $table->dropColumn('weighted_average_price');
        });

        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->dropColumn('weighted_average_price');
        });
    }
};

