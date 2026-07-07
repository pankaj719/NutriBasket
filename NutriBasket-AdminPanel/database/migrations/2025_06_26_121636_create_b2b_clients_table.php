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
        Schema::create('b2b_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('b2b_client_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('b2b_client_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['b2b_client_id', 'user_id']);
            $table->foreign('b2b_client_id')->references('id')->on('b2b_clients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('default_deliveryman_id')->nullable()->after('name');
            $table->string('address')->nullable()->after('default_deliveryman_id');
            $table->foreign('default_deliveryman_id')->references('id')->on('delivery_men')->onDelete('set null');
            $table->unsignedBigInteger('default_packager_id')->nullable()->after('default_deliveryman_id');
            $table->foreign('default_packager_id')->references('id')->on('b2b_packagers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_client_user');
        Schema::dropIfExists('b2b_clients');

        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropForeign(['default_deliveryman_id']);
            $table->dropColumn('default_deliveryman_id');
            $table->dropColumn('address');
            $table->dropForeign(['default_packager_id']);
            $table->dropColumn('default_packager_id');
        });
    }
};
