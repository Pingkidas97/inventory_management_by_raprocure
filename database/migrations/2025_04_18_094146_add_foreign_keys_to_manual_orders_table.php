<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alter the 'manual_orders' table
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->change();
            $table->unsignedBigInteger('buyer_id')->change();
            $table->unsignedBigInteger('buyer_user_id')->change();
        });

        // Add the foreign key constraints again after modifying the columns
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->foreign('vendor_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('buyer_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('buyer_user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
             // Change the comment on the 'order_status' column
            $table->integer('order_status')
                ->default(1)
                ->comment('1 => Order Generated, 2 => Order Cancelled')
                ->change();
        });
    }

    public function down(): void
    {
        // Revert the column types to bigInteger and drop foreign keys
        Schema::table('manual_orders', function (Blueprint $table) {
            // Drop foreign keys before changing column types
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['buyer_user_id']);

            // Revert the columns back to bigInteger
            $table->bigInteger('vendor_id')->change();
            $table->bigInteger('buyer_id')->change();
            $table->bigInteger('buyer_user_id')->change();

            // Revert to old comment
            $table->integer('order_status')
                  ->default(1)
                  ->comment('1-use, 2->not use')
                  ->change();
        });
    }
};
