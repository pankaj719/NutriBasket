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
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('default_deliveryman_id')->nullable()->after('name');
            $table->string('address')->nullable()->after('default_deliveryman_id');
            $table->foreign('default_deliveryman_id')->references('id')->on('delivery_men')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropForeign(['default_deliveryman_id']);
            $table->dropColumn('default_deliveryman_id');
            $table->dropColumn('address');
        });
    }
};
