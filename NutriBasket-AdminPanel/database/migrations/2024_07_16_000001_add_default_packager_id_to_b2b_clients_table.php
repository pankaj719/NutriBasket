<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('default_packager_id')->nullable()->after('default_deliveryman_id');
            $table->foreign('default_packager_id')->references('id')->on('b2b_packagers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropForeign(['default_packager_id']);
            $table->dropColumn('default_packager_id');
        });
    }
}; 