<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModificationFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('quantity_modified')->default(false)->after('order_amount');
            $table->text('modification_reason')->nullable()->after('quantity_modified');
            $table->timestamp('modified_at')->nullable()->after('modification_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['quantity_modified', 'modification_reason', 'modified_at']);
        });
    }
} 